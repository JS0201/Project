<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\order\service;
use app\pay\model\Payment;
use think\Model;

class Order extends Model{

    public function initialize()
    {
       $this->model = model('order/Order');
       $this->skumodel = model('goods/Sku');
       $this->spumodel = model('goods/Spu');
       $this->member_model = model('member/Member');
       $this->cart_service = model('order/Cart','service');
       $this->sku_service = model('order/Sku','service');
       $this->order_log_service = model('order/Log','service');
       $this->order_track_service = model('order/Track','service');

    }

    public function get_list($sqlmap=[],$page=[]){

        $lists = $this->model->where($sqlmap)->order('id desc')->paginate($page);
        return $lists;

    }


    public function get_find($sqlmap,$field=""){

        return $this->model->getOne($sqlmap, $field);

    }

    /**
     * 生成查询条件
     * @param  $options['type'] (1:待付款|2:待确认|3:待发货|4:待收货|5:已完成|6:已取消|7:已回收|8:已删除)
     * @param  $options['keyword'] 	关键词(订单号|收货人姓名|收货人手机)
     * @return [$sqlmap]
     */
    public function build_sqlmap($options) {
        if(empty($options['type'])){
            $options['type'] = $options['map']['type'];
        }
        extract($options);
        $sqlmap = array();
        // 限制商户查询
        if(ADMIN_ID != 1){
            $sqlmap['admin_id'] = ADMIN_ID;
        }
        if (isset($type) && $type > 0) {
            switch ($type) {
                // 待付款
                case 1:
                    $sqlmap['pay_type']   = 1;
                    $sqlmap['status']     = 1;
                    $sqlmap['pay_status'] = 0;
                    break;
                // 待确认
                case 2:

                    $sqlmap = 'status=1 and (pay_type=2 or pay_type=1 and pay_status=1) and confirm_status<>2';

                    break;
                // 待发货
                case 3:
                    $sqlmap['status'] = 1;
                    $sqlmap['confirm_status'] = array('IN',array(1,2));
                    $sqlmap['delivery_status'] = array('IN',array(0,1));
                    break;
                // 待收货
                case 4:
                    $sqlmap['status'] = 1;
                    $sqlmap['delivery_status'] = 2;
                    $sqlmap['finish_status'] = 0;

                    break;
                // 已完成
                case 5:
                    $sqlmap['status'] = 1;
                    $sqlmap['finish_status'] = 2;
                    break;
                // 已取消
                case 6:
                    $sqlmap['status'] = 2;
                    break;
                // 已作废
                case 7:
                    $sqlmap['status'] = (defined('IN_ADMIN')) ? array('GT', 2) : 3;
                    break;
                // 前台已删除
                case 8:
                    $sqlmap['status'] = 4;
            }
        }
        if (isset($keywords) && !empty($keywords)) {

            $sqlmap['sn|address_name|address_mobile|pay_sn|user'] = array('LIKE','%'.$keywords.'%');
        }
        return $sqlmap;
    }

    /**
     * @param int $buyer_id 会员ID
     * @param $params 商品id 数量 规格
     * @param $address_id 地址ID
     * @param int $pay_type 支付方式
     * @param bool $submit 是否创建 (为false时 获取订单结算信息，为true时 创建订单)
     * @param int $type  类型 直接购买 或 购物车结算
     */
    public function temp_creates($user, $params, $address_id, $remark='', $pay_type = 1,$invoices = array(), $submit = false, $type = 0){
        $invoice_enabled = config('cache.invoice_enabled');
        $invoice_tax = config('cache.invoice_enabled');

        if(empty($params)) {
            $this->errors = lang('order_goods_not_exist');
            return false;
        }
        //获取商品数据
        $order_list = array();
        $ismeal = fales; //是否有套餐商品
        foreach($params as $k => $v) {
            $sku_arr = $this->skumodel->getOne(array('sku_id' => $v['sku_id']));
            // 查询主商品信息
            $goods_spu = $this->spumodel->where(array('id' => $sku_arr['spu_id']))->find();
            if($goods_spu) $goods_spu = $goods_spu->toArray();

            if(isset($order_list[$goods_spu['admin_id']]['sku_total'])) {
                $order_list[$goods_spu['admin_id']]['sku_total'] += $v['sum'] * $sku_arr['shop_price']; //商品总价
            }else{
                $order_list[$goods_spu['admin_id']]['sku_total'] = $v['sum'] * $sku_arr['shop_price'];
            }
            if(!isset($order_type)) {
                $order_type = config('configs.money_type')[$sku_arr['type']];
            }
            if($sku_arr['type'] == 2) {  // 2为套餐商品
                $ismeal = true;
            }
            $sku_arr['thumb'] = $v['thumb'];
            $sku_arr['sku_spec'] = $v['message'];
            $order_list[$goods_spu['admin_id']]['skus'][0]['sku_list'][$k]['admin_id'] = $goods_spu['admin_id'];
            $order_list[$goods_spu['admin_id']]['skus'][0]['sku_list'][$k]['sku_id'] = $v['sku_id'];
            $order_list[$goods_spu['admin_id']]['skus'][0]['sku_list'][$k]['number'] = $v['sum'];
            $order_list[$goods_spu['admin_id']]['skus'][0]['sku_list'][$k]['_sku_'] = $sku_arr;
            $order_list[$goods_spu['admin_id']]['skus'][0]['sku_list'][$k]['spec_id'] = $v['specid'];
        }

        if($submit === true) { // 写入订单表
            $real_amount = 0;
            $this->model->startTrans();
            // 循环拆分订单
            foreach ($order_list as $key => $data) {
                // 订单总运费
                $data['deliverys_total'] = sprintf("%.2f", 0);
                // 订单发票
                $data['invoice_tax'] = $invoice_tax;
                if ($invoice_enabled == 1 && $invoices['invoice'] == 1) {
                    $data['invoice_tax'] = (($data['sku_total'] + $data['deliverys_total']) * $invoice_tax) / 100;
                }
                $data['invoice_tax'] = sprintf("%.2f", $data['invoice_tax']);
                // 订单应付总额
                $data['real_amount'] = sprintf("%.2f", max(0, $data['sku_total'] + $data['deliverys_total'] + $data['invoice_tax']));
                $real_amount += $data['real_amount'];
                // 读取收货人信息,-1表示不需要收货地址
                if ($address_id !== -1) {
                    $member_address = model("member/Address")->where(array('id' => $address_id, 'mid' => $user['id']))->find();
                    if (!$member_address) {
                        $this->errors = lang('shipping_address_empty');
                        return FALSE;
                    }
                }
                $invoice_enabled = $invoices['invoice'];
                $invoice_title = remove_xss($invoices['title']);
                $invoice_content = ($invoice_enabled == 0) ? '' : remove_xss($invoices['content']);
                if ($invoice_enabled == 1 && empty($invoice_title)) {
                    $this->errors = lang('invoice_head_empty');
                    return FALSE;
                }
                if (!in_array($pay_type, array(1, 2))) {
                    $this->errors = lang('pay_way_empty');
                    return FALSE;
                }
                // 组装主订单信息
                $order_sn = $this->_build_order_sn();
                $source = defined('MOBILE') ? (defined('IS_WECHAT') ? 3 : 2) : 1;
                $username = db('member')->where(['id' => $user['id']])->value('username');
                $order_data = array(
                    'sn' => $order_sn,
                    'buyer_id' => $user['id'],
                    'admin_id' => $key,
                    'user' => $username,
                    'seller_ids' => 0,
                    'source' => $source,    // 订单来源(1：标准，2：移动端)
                    'pay_type' => $pay_type,        // 支付类型(1:在线支付 , 2:货到付款)
                    'sku_amount' => $data['sku_total'], // 商品总额
                    'real_amount' => $data['real_amount'],        // 应付总额
                    'delivery_amount' => $data['deliverys_total'],    // 总运费
                    'promot_amount' => 0,    // 所有优惠总额
                    'invoice_tax' => $data['invoice_tax'],    // 发票税额
                    'invoice_title' => $invoice_title,    // 发票抬头
                    'invoice_content' => $invoice_content,  // 发票内容
                    'remark' => $remark,
                    'address_name' => $member_address['name'] ? $member_address['name'] : '',
                    'address_mobile' => $member_address['mobile'] ? $member_address['mobile'] : '',
                    'address_detail' => $member_address['complete_address'] ? $member_address['complete_address'] : '',
                    'address_district_ids' => 0,
                    'confirm_status' => 1, //订单直接确认状态
                    'system_time' => time()
                );
                $this->model->isUpdate(false)->data($order_data)->save();
                $oid = $this->model->id;
                if (!$oid) {
                    $this->model->rollback();
                    $this->errors = lang('order_submit_error');
                    return FALSE;
                }
                //  生成子订单
                $result = $this->_create_sku($data, $order_sn, $oid, $pay_type, $user['id'], $type);
                if ($result == FALSE) {
                    // 回滚删除之前的订单信息
                    $this->model->rollback();
                    return FALSE;
                }
                //$this->model->commit();
                if ($type) { //立即购买
                    //支付
                    $finance_service = model('member/MoneyFinance', 'service');
                    $finance_re = $finance_service->setFinance($user['id'], $data['real_amount'], $order_type, lang('pay_log_text') . $order_sn, '', false, 1, 0);
                    // 判断是否金额不足
                    if (!$finance_re) { // 不足
                        $this->NotEnough = isset($this->not_enough) ? true : false; // 返回是否金额不足标志
                        $this->errors = $finance_service->errors;
                    }
                    else{
                        //支付成功。修改订单状态
                        $order_re = $this->model->updated("sn = {$order_sn}", array('pay_status' => 1, 'pay_time' => time()));
                        $sku_re = $this->sku_service->updated("order_sn = {$order_sn}", array('is_pay' => 1));
                        $member_re = true;
                    }
                    if ($ismeal && $user['group_id'] == 1) { //修改用户
                        $member_re = $this->member_model->updated("id = {$user['id']}", array('group_id' => 2));
                        //推荐人奖励100积分
                        $finance_re = $finance_service->setFinance($user['pid'], 100, 'shop_integral', '推荐奖励100积分', '', true, 2, $user['id']);
                        if (!$finance_re) {
                            $this->model->rollback();
                            $this->errors = $finance_service->errors;
                            return false;
                        }
                        if ($order_re === false || $sku_re === false || $member_re === false) {
                            $this->model->rollback();
                            return false;
                        }
                        $this->model->commit();
                        hook('promoteGrade', $user['id']);  // 统计团队人数组升级代理
                        hook('userPath', $user['id']); // 返利
                    }
                }
            }
            $this->model->commit();
            return true;
        }
        else {
            return true;
        }
    }

    /**
     * 创建订单
     * @param  integer $buyer_id    会员id
     * @param  integer $skuids      商品id及数量 (string , 必传参数, 多个sku用;分割。数量number可省略，代表购物车记录的件数。整个参数为空则获取购物车所有列表) eg ：sku_id1[,number1][;sku_id2[,number2]]
     * @param  integer $address_id 会员地址id
     * @param  integer $pay_type    支付方式 (1：在线支付 2：货到付款)
     * @param  array   $deliverys   物流详细 eg : array('seller_id1' => 'delivery_id1' [,'seller_id2' => 'delivery_id2'])
     * @param  array   $order_prom  订单促销 eg : array('seller_id1' => 'order_prom_id1'[,'seller_id2' => 'order_prom_id2'])
     * @param  array   $sku_prom    商品促销 eg : array('sku_id1' => 'sku_prom1'[,'sku_id2' => 'sku_prom2'])
     * @param  array   $remarks     订单留言 eg : array('seller_id1' => '内容1'[,'seller_id2' => '内容2'])
     * @param  array   $invoices    发票信息 eg : array('invoice' => '是否开发票 - 布尔值','title' => '发票抬头' , 'content' => '发票内容')
     * @param  boolean $submit      是否创建 (为false时 获取订单结算信息，为true时 创建订单)
     * @return mixed
     */
    public function creates($buyer_id = 0, $skuids = 0, $address_id, $message, $sum,$pay_type = 1,$deliverys = array(), $order_prom = array(), $sku_prom = array(), $remarks = array(), $invoices = array(), $submit = false) {
        /* 定义默认值 */
        $sub_total = 0;			//商品总价
        $deliverys_total = 0;	// 总运费
        $invoice_tax = 0;		// 总发票费
        $promot_total = 0;		// 总优惠金额

        $invoice_enabled = config('cache.invoice_enabled');
        $invoice_tax = config('cache.invoice_enabled');

        /* 第一步：获取购物车数据*/
        $carts = $this->cart_service->get_cart_lists($buyer_id, $skuids, TRUE);

        if(empty($carts["skus"])) {
            $this->errors = lang('shopping_cart_empty');
            return false;
        }
        /* 第二步：处理商品 */
        foreach ($carts['skus'] as $seller_id => $value) {
            /* 商家订单赠品 */

            $carts['skus'][$seller_id] = $value;
        }
        /* 商品总原价 */

        $carts['sku_total'] = sprintf("%.2f",$carts['all_prices']);
        /* 订单总运费 */
        $carts['deliverys_total'] = sprintf("%.2f", 0);
        /* 订单发票 */
        $carts['invoice_tax'] = $invoice_tax;
        if($invoice_enabled == 1 && $invoices['invoice'] == 1) {
            $carts['invoice_tax'] = (($carts['sku_total'] + $carts['deliverys_total']) * $invoice_tax) / 100;
        }
        $carts['invoice_tax'] = sprintf("%.2f", $carts['invoice_tax']);


        /* 订单应付总额 */
        $carts['real_amount'] = sprintf("%.2f", max(0,$carts['sku_total'] + $carts['deliverys_total'] + $carts['invoice_tax']));

        if($submit === true) { // 写入订单表

            // 读取收货人信息,-1表示不需要收货地址
            if($address_id!==-1){
                $member_address = model("member/Address")->where(array('id' => $address_id,'mid'=>$buyer_id))->find();
                if (!$member_address) {
                    $this->errors = lang('shipping_address_empty');
                    return FALSE;
                }
            }


            $invoice_enabled = $invoices['invoice'];
            $invoice_title = remove_xss($invoices['title']);
            $invoice_content = ($invoice_enabled == 0) ? '' : remove_xss($invoices['content']);
            if ($invoice_enabled == 1 && empty($invoice_title)) {
                $this->errors = lang('invoice_head_empty');
                return FALSE;
            }
            if (!in_array($pay_type, array(1,2))) {
                $this->errors = lang('pay_way_empty');
                return FALSE;
            }


            $username = db('member')->where(['id'=>$buyer_id])->value('username');
            /* 组装主订单信息 */
            $order_sn = $this->_build_order_sn();
            $source = defined('MOBILE') ? (defined('IS_WECHAT') ? 3 : 2) : 1;
            $order_data = array(
                'sn'              => $order_sn,
                'buyer_id'        => $buyer_id,
                'user'            => $username,
                'seller_ids'      => 0,
                'source'          => $source,	// 订单来源(1：标准，2：移动端)
                'pay_type'        => $pay_type, 		// 支付类型(1:在线支付 , 2:货到付款)
                'sku_amount'      => $carts['sku_total'], // 商品总额
                'real_amount'     => $carts['real_amount'], 		// 应付总额
                'delivery_amount' => $carts['deliverys_total'] ,	// 总运费
                'promot_amount'   => 0 ,	// 所有优惠总额
                'invoice_tax'     => $carts['invoice_tax'], 	// 发票税额
                'invoice_title'   => $invoice_title, 	// 发票抬头
                'invoice_content' => $invoice_content,  // 发票内容
                'address_name'    => $member_address['name']?$member_address['name']:'',
                'address_mobile'  => $member_address['mobile']?$member_address['mobile']:'',
                'address_detail'  => $member_address['complete_address']?$member_address['complete_address']:'',
                'address_district_ids' => 0,
                'system_time'=>time(),
            );

            $this->model->startTrans();
            $this->model->isUpdate(false)->save($order_data);
            $oid=$this->model->id;



            if (!$oid) {
                $this->model->rollback();
                $this->errors = lang('order_submit_error');
                return FALSE;
            }



            /* 生成子订单 */
            $result = $this->_create_sku($carts ,$order_sn ,$oid ,$pay_type ,$buyer_id, $type);
            if ($result == FALSE) {
                // 回滚删除之前的订单信息
                $this->model->rollback();
            }

            $this->model->commit();

            // 钩子：下单成功
            $member = array();
            $member['member'] = db('member')->where(array('id' => $buyer_id))->find();
            $member['order_sn'] = $order_sn;
            hook('create_order',$member);
            return $order_sn;
        } else {
            return $carts;
        }
    }

    /**
     * 创建sku商品
     * @param  array  $cart_skus 购物车分组信息
     * @param  string $order_sn  主订单号
     * @param  int 	  $id 		 主订单id
     * @param  int 	  $pay_type  支付方式
     * @param  int 	  $mid  用户id
     * @return [boolean]
     */
    private function _create_sku($cart_skus ,$order_sn ,$id ,$pay_type ,$mid = 0, $type){
        if (count($cart_skus['skus']) == 0) return FALSE;
        $operator = get_operator();	// 获取操作者信息
        $stock_change = config('cache.stock_change');

        foreach ($cart_skus['skus'] as $k => $val) {
            // 创建订单商品
            $skus = $result = $load_decs = array();
            foreach ($val['sku_list'] as $v) {
                $_data = array();
                $_data['order_sn']    = $order_sn;
                $_data['sub_sn']      = $this->_build_order_sn(true);
                $_data['buyer_id']    = $mid;
                $_data['admin_id']    = $v['admin_id'];
                $_data['seller_id']   = $k;
                $_data['spu_id']      = $v['_sku_']['spu_id'];
                $_data['sku_id']      = $v['_sku_']['sku_id'];
                $_data['sku_thumb']   = $v['_sku_']['thumb'];
                $_data['sku_name']    = $v['_sku_']['sku_name'];
                $_data['sku_spec']    = $v['_sku_']['spec'];
                $_data['sku_price']   = $v['_sku_']['shop_price'];
                $_data['real_price']  = $v['_sku_']['shop_price'] * $v['number'];
                $_data['buy_nums']    = $v['number'];
                $_data['sku_edition'] = $v['_sku_']['edition'];
                $_data['promotion']   = [];
                $_data['delivery_template_id']    = 0;
                $skus[]               = $_data;
                // 待减除购物车记录数量 组装  array('sku_id1' => number1 [,'sku_id2' => number2])
                if(isset($v['spec_id']) && $v['spec_id']) {
                    $load_decs[$v['spec_id']]['nums'] = $v['number'];
                    $load_decs[$v['spec_id']]['sku_id'] = $v['sku_id'];
                }

            }
             //减库存
            if (($stock_change != NULL && $stock_change == 0) || ($stock_change == 1 && $pay_type == 2)) {
                foreach ($val['sku_list'] as $cart) {
                    $no_stock = $this->sku_service->set_dec_number($cart['_sku_']['sku_id'], $cart['number']);
                    if(!$no_stock){
                        $this->errors = $this->sku_service->errors;
                        return false;
                        break;
                    }
                }
            }
            // 写入数据库
            $result = $this->sku_service->create_all($skus);
            if (!$result) {
                return FALSE;
                break;
            }
            // 清除购物车已购买数据
            if($load_decs) {
                $this->cart_service->dec_nums($load_decs ,$mid, $type);
            }
            // 订单日志
            $data = array();
            $data['order_sn']      = $order_sn;
            $data['sub_sn']        = $order_sn;
            $data['action']        = '创建订单';
            $data['operator_id']   = $operator['id'];
            $data['operator_name'] = $operator['username'];
            $data['operator_type'] = $operator['operator_type'];
            $data['msg']           = '提交购买商品并生成订单';

            $this->order_log_service->add($data);
            // 订单跟踪
            $track_msg = $pay_type == 1 ? '系统正在等待付款': '请等待系统确认';
            $this->order_track_service->add($order_sn ,$order_sn , '您提交了订单，'.$track_msg);

            return true;
        }


    }



    /**
     * 根据日期生成唯一订单号
     * @param boolean $refresh 	是否刷新再生成
     * @return string
     */
    private function _build_order_sn($refresh = FALSE) {
        if ($refresh == TRUE) {
            return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 12);
        }
        return date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
    }



    /**
     * 设置订单
     * @param  string 	$sn  		订单号(确认支付时传主订单号)
     * @param  string 	$action 	操作类型
     *         (order:订单 || pay:支付 || confirm:确认 || delivery:发货 || finish:完成)
     * @param  int 		$status 	状态(只有$action = 'order'时必填)
     * @param  array 	$options 	附加参数
     * @return [boolean]
     */
    public function set_order($sn = '',$action = '',$status = 0 ,$options = []) {
        $sn     = (string) trim($sn);
        $action = (string) trim($action);
        $status = (int) $status;
        $msg    = (string) trim($options['msg']);
        unset($options['msg']);
        if (empty($sn)) {
            $this->errors = lang('order_sn_not_null');
            return FALSE;
        }
        if (empty($action)) {
            $this->errors = lang('operate_type_empty');
            return FALSE;
        }
        if (!in_array($action, array('order','pay','confirm','delivery','finish'))) {
            $this->errors = lang('operate_type_error');
            return FALSE;
        }
        // 检测订单是否存在
        $this->order = $this->model->where(['sn' => $sn])->find();
        if (!$this->order) {
            $this->errors = lang('order_not_exist');
            return FALSE;
        }


        // 获取订单状态
        $this->order['_status'] = $this->model->get_status($this->order);

        switch ($action) {
            case 'order':	// (2：已取消，3：已回收，4：已删除)
                $result = $this->_order($status ,$options);
                // 后台删除订单直接返回
                if (IN_ADMIN && $status == 4 && $result !== FALSE) {
                    return TRUE;
                }
                break;
            case 'pay':	// 针对所有子订单操作
                $result = $this->_pay($sn);
                break;
            case 'confirm':
                $result = $this->_confirm();
                break;
            case 'delivery':
                $result = $this->_delivery($options);
                break;
            case 'finish':
                $result = $this->_finish($options);
                break;
        }
        if ($result === FALSE) return FALSE;
        // 订单日志
        $operator = get_operator();	// 获取操作者信息
        $data = [];

        $data['order_sn'] = $this->order['sn'];
        $data['sub_sn']   = $this->order['sn'];
        $data['action']        = $result;
        $data['operator_id']   = $operator['id'];
        $data['operator_name'] = $operator['username'];
        $data['operator_type'] = $operator['operator_type'];
        $data['msg']           = $msg;

        $this->order_log_service->add($data);
        return TRUE;
    }
    private function _pay($sn)
    {
        $order = $this->model->getOne("sn = {$sn}");
        $user = $this->member_model->getOne("id = {$order['buyer_id']}");
        $finance_service = model('member/MoneyFinance','service');
        $this->model->startTrans();
        $finance_re = $finance_service->setFinance($order['buyer_id'], $order['sku_amount'],'money', lang('pay_log_text').$sn, '', false, 1, 0);
        if(!$finance_re) {
            $this->model->rollback();
            $this->errors = $finance_service->errors;
            return FALSE;
        }
        //支付成功。修改订单状态
        $order_re = $this->model->updated("sn = {$sn}", array('pay_status' => 1, 'pay_time' => time()));
        $sku_re = $this->sku_service->updated("order_sn = {$sn}", array('is_pay' => 1));

        $sku_ids = $this->sku_service->getList("order_sn = {$sn}", "sku_id");
        $ismeal = false;
        foreach($sku_ids as $k => $v) {
            $sku = $this->skumodel->getOne("sku_id = {$v['sku_id']}");
            if($sku['type'] == 2) {
                $ismeal = true;
                break;
            }
        }
        $member_re = true;
        if($ismeal && $user['group_id'] == 1) { //修改用户
            $member_re = $this->member_model->updated("id = {$user['id']}",array('group_id' => 2));
            //推荐人奖励100积分
            $finance_re = $finance_service->setFinance($user['pid'], 100,'shop_integral', '推荐奖励100积分', '', true, 2, $user['id']);
            if(!$finance_re) {
                $this->model->rollback();
                $this->errors = $finance_service->errors;
                return FALSE;
            }
        }
        if($order_re === false || $sku_re === false || $member_re === false) {
            $this->model->rollback();
            return FALSE;
        }
        $this->model->commit();
        hook('promoteGrade', $user['id']);  // 统计团队人数组升级代理
        hook('userPath',$user['id']); // 返利
        return true;
    }




    /**
     * 支付操作 (针对所有子订单操作)
     * @param  array $options
     *         			paid_amount ：实付金额
     *         			pay_method  ：支付方式
     *         			pay_sn 		：支付流水号
     * @return [string]
     */
/*    private function _pay($options) {
        $order = $this->order;
        if ($order['pay_type'] != 1 || $order['pay_status'] != 0) {
            $this->errors = lang('_param_error_');
            return FALSE;
        }
        $data = [];
        $data['pay_status'] = 1;
        $data['pay_time']   = time();


        // 设置主订单表信息
        if (isset($options['paid_amount'])) {	// 实付金额
            $data['paid_amount'] = sprintf("%.2f", (float) $options['paid_amount']);
        } else {
            $data['paid_amount'] = $order['real_amount'];
        }
        if (isset($options['pay_method'])) {	// 支付方式
            $data['pay_method']  = (string) $options['pay_method'];
        }
        if (isset($options['pay_sn'])) {		// 支付流水号
            $data['pay_sn'] = (string) $options['pay_sn'];
        }


        $result = $this->model->isUpdate(true)->save($data,['sn'=>$order['sn']]);
        if (!$result){
            $this->errors = lang('_operation_fail_');
            return false;
        }

        // 钩子：支付成功
        hook('order_pay_success');
        return '支付订单';
    }*/


    /* 确认操作 */
    private function _confirm() {
        $order = $this->order;
        if ($order['confirm_status'] == 2 || $order['delivery_status'] != 0 || ($order['pay_type'] == 1 && $order['pay_status'] != 1)) {
            $this->errors = lang('_param_error_');
            return FALSE;
        }
        $data = [];
        $data['confirm_status'] = 2;
        $data['confirm_time']   = time();
        $result = $this->model->isUpdate(true)->save($data,['sn' => $order['sn']]);
        if (!$result) {
            $this->errors = lang('_operation_fail_');
            return FALSE;
        }

        // 钩子：确认订单
        hook('confirm_order',$order);
        return '确认订单';
    }



    /**
     * 发货操作
     * @param  array  $options
     *         				is_choise ：是否选择物流
     *         				delivery_id ：物流主键
     *         				delivery_sn ：快递单号
     *         				o_sku_ids ：要发货的订单商品ids(多个以 ，分割)
     * @return [string]
     */
    private function _delivery($options = []) {
        $order = $this->order;
        $is_choise     = (int) $options['is_choise'];	// 是否选择物流
        $delivery_id   = (int) $options['delivery_id'];
        $delivery_sn   = (string) trim($options['delivery_sn']);
        $sn 	   = (string) trim($options['sn']);


        if ($is_choise === 1) {
            if ($delivery_id < 1) {
                $this->errors = lang('logistics_empty');
                return FALSE;
            }
            if (empty($delivery_sn)) {

                $this->errors = lang('logistics_sn_not_exist');
                return FALSE;
            }
            $delivery_name = db('delivery')->where(['id'=>$delivery_id])->value('name');
        } else {
            $delivery_id   = 0;
            $delivery_name = '无需物流运输';
            $delivery_sn   = '';
        }



        //全部发货，获取sku_id
        $o_sku_ids=model('order/Sku')->where(['order_sn'=>$sn])->column('id');

        $data = $sqlmap = array();
        // 创建订单物流信息
        $data['o_sku_ids']     = implode(',',$o_sku_ids);
        $data['sn']        = $order['sn'];
        $data['sub_sn']        = $order['sn'];
        $data['delivery_id']   = $delivery_id;
        $data['delivery_name'] = $delivery_name;
        $data['delivery_sn']   = $delivery_sn;
        $data['delivery_time'] = time();

        db('order_delivery')->insert($data);

        $addid=db('order_delivery')->getLastInsID();

        if (!$addid) {
            $this->errors = lang('logistics_not_exist');
            return FALSE;
        }

        /* 标记订单商品为已发货状态，并关联订单物流id */
        $sqlmap['id'] = ['in' , $o_sku_ids];
        $data = [];
        $data['delivery_id'] = $addid;
        $data['delivery_status'] = 1;
        model('order/Sku')->where($sqlmap)->setField($data);
        $data=[];
        $data['delivery_status'] = 2;
        $data['delivery_time'] = time();
        $result = $this->model->isUpdate(true)->save($data,['sn' => $order['sn']]);
        if (!$result) {
            $this->errors = lang('_operation_fail_');
            return FALSE;
        }


        // 如果后台设置发货减库存 => 减库存
        $stock_change = config('cache.stock_change');
        if ($stock_change != NULL && $stock_change == 2) {
            foreach ($o_sku_ids as $k => $id) {

                $o_sku = model('order/Sku')->where(['id' => $id])->field('sku_id,buy_nums')->find();
                model('goods/Sku','service')->set_dec_number($o_sku['sku_id'],$o_sku['buy_nums']);
            }
        }
        // 物流跟踪
        $string = '';
        if($is_choise==1){
            $string = '快递单号：'.$delivery_sn;
        }

        $this->order_track_service->add($order['sn'] ,$order['sn'] , '您的订单配货完毕，已经发货。'.$string,0,$addid);
        // 钩子：订单商品已发货
        $order['delivery_sn'] = $delivery_sn;
        $order['delivery_name'] = $delivery_name;
        hook('skus_delivery',$order);
        return '订单发货';
    }


    /**
     * 确认收货(完成)
     * @param  array  $options
     *         			o_delivery_id ：订单发货主键id
     * @return [string]
     */
    private function _finish($options = array()) {
        $site_name = config('cache.site_name');
        $order = $this->order;
        if ($order['finish_status'] == 2 || $order['delivery_status'] == 0) {
            $this->errors = lang('_param_error_');
            return FALSE;
        }

        // 标记子订单为已完成
        $data = [];
        $data['finish_status'] = 2;
        $data['finish_time'] = time();
        $string = '感谢您在'.$site_name.'购物，欢迎您的再次光临';
        $result = $this->model->isUpdate(true)->save($data,['sn' => $order['sn']]);
        // 标记所有订单物流为已收货 (order_delivery & order_sku)


        $sqlmap = $data = [];
        $sqlmap['sub_sn'] = $order['sn'];
        $data['isreceive'] = 1;
        $data['receive_time'] = time();
        $result = db('order_delivery')->where($sqlmap)->data($data)->update();
        model('order/Sku')->where($sqlmap)->setField('delivery_status' ,2);


        // 订单跟踪
        foreach($order['skus_list'] as $k => $v){

                if($v['delivery_status'] != 2 ){
                    $this->order_track_service->add($order['sn'] ,$order['sn'] , $string,0,$v['delivery_id']);
                }

        }

        // 钩子：确认收货(完成)
        hook('delivery_finish');
        return $string;
    }



    /* 订单操作 */
    private function _order($status ,$options) {
        $order = $this->order;
        $data = $sqlmap =[];
        switch ($status) {
            case 2:	// 取消订单
                $string = '您的订单已取消';
                if ($order['status'] != 1 || $order['delivery_status'] != 0) {
                    $this->errors = lang('order_dont_operate');
                    return FALSE;
                }
                /* 在线支付：取消整个订单，货到付款：取消当前子订单 */
                $data['status'] = 2;
                $data['cancel_time'] = time();
                if ($order['pay_type'] == 1) {
                    // 标记所有子订单为已取消
                    $this->model->save($data,['sn' => $order['sn']]);
                    // 主订单信息
                    $order_main = $this->model->where(['sn' =>$order['sn']])->find();
                    /* 未发货&已付款的&是否退款到账户余额 ==> 退款到账户余额 */
                    if ($order['delivery_status'] == 0 && $order['pay_status'] == 1 && $options['isrefund'] == 1) {

                        model('member/Member','service')->change_account($order['buyer_id'],'money',$order['paid_amount'],'取消订单退款,订单号:'.$order['sn']);

                        $string = '您的订单已取消，已退款到您的账户余额，请查收';
                    }

                } else {
                   //货到付款==>直接取消订单
                    $this->model->where(array('sn' => $order['sn']))->save($data);
                }

                /* 后台设置为下单减库存 ==> goods_sku加上库存 */
                $stock_change = input('cache.stock_change');
                if (isset($stock_change) && $stock_change == 0) {

                    $skuids = model('order/Sku')->where(['order_sn' => $order['sn'],'buyer_id' => $order['buyer_id']])->column('sku_id,buy_nums');
                    if ($skuids) {
                        foreach ($skuids as $skuid => $num) {
                            model('goods/Sku','service')->set_inc_number($skuid ,$num);
                        }
                    }
                }
                return $string;
                break;

            case 3:	// 订单回收站
                if ($order['status'] != 2) {
                    $this->errors = lang('_valid_access_');
                    return FALSE;
                }
                // 标记当前子订单为已回收
                $data['status'] = 3;
                $data['cancel_time'] = time();
                $result = $this->model->save($data,['sn' => $order['sn']]);
                if (!$result) {
                    $this->errors = lang('_operation_fail_');
                    return FALSE;
                }
                return '您的订单已放入回收站';
                break;

            /* 订单删除 */
            case 4:
                if ($order['status'] != 3) {
                    $this->errors = lang('_valid_access_');
                    return FALSE;
                }
                // 前台用户删除的只更改状态，管理员删除的需删除所有订单相关的信息
                if (defined('IN_ADMIN')) {
                    // 删除子订单
                    $sqlmap['sn'] = $order['sn'];
                    $sqlmap['status'] = 3;
                    $result = $this->model->where($sqlmap)->delete();
                    if (!$result) {
                        $this->errors = lang('delete_order_error');
                        return FALSE;
                    }
                    // 删除订单商品
                    db('order_sku')->where(array('sub_sn' => $order['sn']))->delete();
                    // 删除订单日志
                    db('order_log')->where(array('sub_sn' => $order['sn']))->delete();
                    // 删除订单跟踪
                    db('order_track')->where(array('sub_sn' => $order['sn']))->delete();
                    //删除物流
                    db('order_delivery')->where(array('sub_sn' => $order['sn']))->delete();

                    return '订单删除成功';
                } else {
                    // 标记当前订单为删除
                    $data['status'] = 4;
                    $data['cancel_time'] = time();
                    $result = $this->model->save($data,['sn' => $order['sn']]);
                    if (!$result) {
                        $this->errors = lang('_operation_fail_');
                        return FALSE;
                    }

                    return '您的订单已从回收站删除';
                }
                break;
        }
    }





}
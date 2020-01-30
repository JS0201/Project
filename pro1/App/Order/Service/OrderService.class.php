<?php

namespace Order\Service;

use Think\Model;

class OrderService extends Model {

    public function __construct() {
        $this->model = D('Order/Order');
        $this->sub_model = D('Order/Order_sub');
        $this->cart_service = D('Order/Cart', 'Service');
        $this->member_address_service = D('Member/MemberAddress', 'Service');
        $this->sku_service = D('Order/Order_sku', 'Service');
        $this->goods_sku_service = D('Goods/Goods_sku', 'Service');
    }

    /**
     * 创建订单
     * @param  integer $buyer_id    会员id
     * @param  integer $pay_method  支付方式(1：ACNY 2：USDT)
     * @param  integer $district_id 物流配置地区id
     * @param  integer $pay_type    支付方式 (1：在线支付 2：货到付款)
     * @param  array   $deliverys   物流详细 eg : array('seller_id1' => 'delivery_id1' [,'seller_id2' => 'delivery_id2'])
     * @param  array   $order_prom  订单促销 eg : array('seller_id1' => 'order_prom_id1'[,'seller_id2' => 'order_prom_id2'])
     * @param  array   $sku_prom    商品促销 eg : array('sku_id1' => 'sku_prom1'[,'sku_id2' => 'sku_prom2'])
     * @param  array   $remarks     订单留言 eg : array('seller_id1' => '内容1'[,'seller_id2' => '内容2'])
     * @param  array   $invoices    发票信息 eg : array('invoice' => '是否开发票 - 布尔值','title' => '发票抬头' , 'content' => '发票内容')
     * @return mixed
     */
    public function create($buyer_id = 0,$pay_method=1, $district_id = 0, $pay_type = 1, $deliverys = array(), $order_prom = array(), $sku_prom = array(), $remarks = array(), $invoices = array()) {


        /* 定义默认值 */
        $sub_total = 0;   //商品总价
        $deliverys_total = 0; // 总运费
        $invoice_tax = 0;  // 总发票费
        $promot_total = 0;  // 总优惠金额

        /* 第一步：获取购物车数据 */


        if ($_SESSION['cart_type'] == 'cart_info') {

            $order_type = 0;
        } else {
            $order_type = 1;
        }

        $carts = $this->cart_service->get_cart_lists($_SESSION['cart_type']);
        //print_r($carts);
        if (empty($carts["goods_info"])) {

            $this->errors = L('shopping_cart_empty');
            return false;
        }

     

        $source = defined('MOBILE') ? (defined('IS_WECHAT') ? 3 : 2) : 1;

        $member_address = $this->member_address_service->uid($buyer_id)->fetch_by_id($district_id);

        if (!$member_address) {

            $this->errors = L('shipping_address_empty');
            return false;
        }

        $usdt_price=M('timeprice')->where(array('id'=>1))->getField('price');

        foreach ($carts['goods_info'] as $seller_id => $value) {
            $order_sn = $this->_build_order_sn();
            $order_data = array();
            $order_data['order_type'] = $order_type;
            $order_data['sn'] = $order_sn;
            $order_data['buyer_id'] = $buyer_id;
            $order_data['seller_ids'] = $seller_id;
            $order_data['source'] = $source;
            $order_data['pay_type'] = $pay_type;
            $order_data['sku_amount'] = $value['sku_price'];  //商品总额
            $order_data['delivery_amount'] = '0';  //物流总额
            $order_data['real_amount'] = $value['sku_price'];  //应付金额
            $order_data['paid_amount'] = $value['sku_price'];  //实付金额
            $order_data['balance_amount'] = '0';  //余额付款总额
            $order_data['pay_method'] =$pay_method; //支付方式(1：ACNY 2：USDT)
            $order_data['pay_sn'] = '';  //第三方交易号
            $order_data['address_name'] = $member_address['name'];  //收货人姓名
            $order_data['address_mobile'] = $member_address['mobile'];  //收货人手机号
            $order_data['address_detail'] = $member_address['province'].$member_address['city'].$member_address['county'].$member_address['address']; //收货人详细地址
            $order_data['invoice_tax'] = '0';  //发票税率
            $order_data['invoice_title'] = '';  //发票标题
            $order_data['invoice_content'] = '';  //发票内容
            $order_data['status'] = '1';  //订单状态(1：正常，2：全部取消，3：全部回收，4：全部删除)
            $order_data['pay_status'] = '0'; //是否支付
            $order_data['confirm_status'] = '0';  //确认状态(0：待确认，1：部分确认，2：已确认)
            $order_data['delivery_status'] = '0';  //发货状态(0：待发货，1：部分发货，2：已发货)
            $order_data['finish_status'] = '0';  //完成状态(0：待完成，1：部分完成，2：已完成)
            $order_data['pay_time'] = '0'; //支付时间
            $order_data['system_time'] = time(); //下单时间
            $order_data['promot_amount'] = '0'; //所有优惠总额
            $order_data['address_district_ids'] = '';  //地区ID索引

            if($pay_method == 2){
                $order_data['usdt_amount']= sprintf("%0.4f", ( $value['sku_price'] / $usdt_price));

            }

            $oid = $this->model->data($order_data)->add();

            if (!$oid) {
                $this->errors = L('order_create_error');
                return false;
            }
            
            
            $carts['remark'] = $remarks;
            $result = $this->_create_sub($carts, $order_sn, $oid, $pay_type, $buyer_id);


            if ($result == FALSE) {
                // 回滚删除之前的订单信息
                $this->model->where(array('id' => $oid))->delete();

                $this->errors = L('order_create_error');

                return false;
            }

            return $order_sn;
        }
    }

    /**
     * 创建子订单
     * @param  array  $cart_skus 购物车分组信息
     * @param  string $order_sn  主订单号
     * @param  int 	  $id 		 主订单id
     * @param  int 	  $pay_type  支付方式
     * @param  int 	  $mid  用户id
     * @return [boolean]
     */
    private function _create_sub($cart_skus, $order_sn, $id, $pay_type, $mid = 0) {
        if (!$mid)
            return FALSE;

        if (count($cart_skus['goods_info']) == 0)
            return FALSE;


        $data = array();

        foreach ($cart_skus['goods_info'] as $k => $val) {

            $sub_sn = $this->_build_order_sn(TRUE);
            $data['sub_sn'] = $sub_sn;
            $data['order_id'] = $id;
            $data['order_sn'] = $order_sn;
            $data['pay_type'] = $pay_type;
            $data['buyer_id'] = $mid;
            $data['seller_id'] = $k;
            $data['remark'] = (string) $val['remarks'];
            $data['delivery_name'] = '';
            $data['sku_price'] = $val['sku_price']; //商品总额
            $data['delivery_price'] = '0'; //物流费用
            $data['real_price'] = $val['sku_price'] + $val['delivery_price'];  //应付总额

            $data['promotion'] = ''; //促销活动
            $data['system_time'] = time();

            $result = $this->sub_model->add($data);
            if (!$result) {

                return false;
                break;
            }
            // 创建订单商品
            $skus = $result = $load_decs = array();
            foreach ($val['sku_list'] as $v) {

                $_data = array();
                $_data['order_sn'] = $order_sn;
                $_data['sub_sn'] = $sub_sn;
                $_data['buyer_id'] = $mid;
                $_data['seller_id'] = $k;
                $_data['sku_id'] = $v['_sku_']['sku_id'];
                $_data['sku_thumb'] = $v['_sku_']['thumb'];
                $_data['sku_barcode'] = $v['_sku_']['barcode'];
                $_data['sku_name'] = $v['_sku_']['sku_name'];
                $_data['sku_spec'] = '';
                $_data['sku_price'] = $v['_sku_']['shop_price'];
                $_data['real_price'] = $v['prices'];
                $_data['buy_nums'] = $v['number'];
                $_data['sku_edition'] = $v['_sku_']['edition'];
                $_data['promotion'] = '';
                $_data['delivery_template_id'] = '';
                $skus[] = $_data;
            }


            $result = $this->sku_service->create_all($skus);
            if (!$result) {
                $this->errors = $this->sku_service->errors;
                return FALSE;
                break;
            }
            /* 减库存 */
            foreach ($val['sku_list'] as $k => $cart) {
               $this->goods_sku_service->set_dec_number($k, $cart['number']);
            }

            /*
              // 订单日志
              $data = array();
              $data['order_sn'] = $order_sn;
              $data['sub_sn'] = $sub_sn;
              $data['action'] = '创建订单';
              $data['operator_id'] = $operator['id'];
              $data['operator_name'] = $operator['username'];
              $data['operator_type'] = $operator['operator_type'];
              $data['msg'] = '提交购买商品并生成订单';
              $this->service_order_log->add($data);
              // 订单跟踪
              $track_msg = $pay_type == 1 ? '系统正在等待付款' : '请等待系统确认';
              $this->service_track->add($order_sn, $sub_sn, '您提交了订单，' . $track_msg);
             * *
             */
        }
        return TRUE;
    }

    /**
     * 根据日期生成唯一订单号
     * @param boolean $refresh 	是否刷新再生成
     * @return string
     */
    private function _build_order_sn($refresh = FALSE) {
        if ($refresh == TRUE) {
            return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 12);
        }
        return date('YmdHis') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
    }

    /**
     * 生成查询条件
     * @param  $options['type'] (1:待付款|2:待确认|3:待发货|4:待收货|5:已完成|6:已取消|7:已回收|8:已删除)
     * @param  $options['keyword'] 	关键词(订单号|收货人姓名|收货人手机)
     * @return [$sqlmap]
     */
    public function build_sqlmap($options) {
        if (empty($options['type'])) {
            $options['type'] = $options['map']['type'];
        }
        extract($options);
        $sqlmap = array();
        if($options['start'] &&  $options['end']){
            $start_time=strtotime($options['start']);
            $end_time=strtotime($options['end']);
            $sqlmap['system_time']=array('between',array($start_time,$end_time));
        }elseif($options['start'] &&  !$options['end']){
            $start_time=strtotime($options['start']);
            $sqlmap['system_time']=array('egt',$start_time);
        }elseif(!$options['start'] &&  $options['end']){
            $end_time=strtotime($options['end']);
            $sqlmap['system_time']=array('elt',$end_time);
        }

        if (isset($type) && $type > 0) {
            switch ($type) {
                // 待付款
                case 1:
                    $sqlmap['pay_type'] = 1;
                    $sqlmap['status'] = 1;
                    $sqlmap['pay_status'] = 0;
                    break;
                // 待确认
                case 2:
                    $sqlmap['status'] = 1;
                    $sqlmap['_string'] = '(pay_type=2 or pay_type=1 and pay_status=1) and confirm_status<>2';
                    break;
                // 待发货
                case 3:
                    $sqlmap['status'] = 1;
                    $sqlmap['confirm_status'] = array('IN', array(1, 2));
                    $sqlmap['delivery_status'] = array('IN', array(0, 1));
                    break;
                // 待收货
                case 4:
                    $sqlmap['status'] = 1;
                    // 获取所有待收货的主订单号
                    $sub_sns = D('Order/Order_delivery')->where(array('isreceive' => 0))->getField('sub_sn', TRUE);
                    $map = array();
                    if (!$sub_sns) {
                        $sub_sns = '0';
                    }
                    $map['sub_sn'] = array('IN', $sub_sns);
                    $order_sns = $this->sub_model->where($map)->getField('order_sn', TRUE);

                    if (!$order_sns) {
                        $order_sns = '0';
                    }

                    $sqlmap['sn'] = array('IN', $order_sns);
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
        if (isset($keyword) && !empty($keyword)) {
            $buyer_ids = D('Member/Member')->where(array('username' => array('LIKE', '%' . $keyword . '%')))->getField('id', TRUE);
            $sn = D('Order/Order_trade')->where(array('trade_no' => array('LIKE', '%' . $keyword . '%')))->getField('order_sn', TRUE);
            if ($buyer_ids) {
                $sqlmap['buyer_id'] = array('IN', $buyer_ids);
            }
            if ($sn) {
                $sqlmap['sn'] = array('IN', $sn);
            }
            if (!$buyer_ids && !$sn) {
                $sqlmap['sn|address_name|address_mobile|pay_sn'] = array('LIKE', '%' . $keyword . '%');
            }
        }
        return $sqlmap;
    }

    /**
     * [select 列表]
     * @return [type]            [description]
     */
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'id desc') {

        $count = $this->model->where($sqlmap)->count();
        $pages = new \Think\Page($count, $limit);
        $page = $page ? $page : 1;

        if (isset($_GET['p'])) {
            $page = (int) $_GET['p'];
        }


        if ($limit != '') {

            $limits = (($page - 1) * $limit) . ',' . $limit;
        }

        $lists = $this->model->where($sqlmap)->order($order)->limit($limits)->select();


        return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
    }

    //
    public function get_order_lists($sqlmap = array(), $page, $limit) {
        $orders = $this->model->where($sqlmap)->order('id desc')->page($page)->limit($limit)->select();
        $lists = array();
        foreach ($orders AS $order) {
            //print_r($order['_buyer']['report_id']);
            switch($order['_buyer']['report_id']){
                case 0:
                    $report_id='是';//普通会员
                break;  
                case 1:
                    $report_id='否';//vip会员
                break;  
            }
			 switch($order['pay_method']){
                case 1:
                    $pay_method='ACNY';//普通会员
                    $payment=$order['real_amount'];
                    break;
                case 2:
                    $pay_method='USDT';//vip会员
                    $payment=$order['usdt_amount'];
                    break;
            }
            $arr['order_sn']=$order['sn'];
            $ordername=$this->sku_service->find($arr,'sku_name');
            $lists[] = array('id' => $order['id'],
                'sn' => $order['sn'],
                'ordername'=>$ordername['sku_name'],
                'username' => $order['_buyer']['username'],
                'address_name' => $order['address_name'],
                'address_mobile' => $order['address_mobile'],
                'yes_no' => $report_id,
                'system_time' => $order['system_time'],
                'real_amount' => $order['real_amount'],
				 '_pay_type' => $order['_pay_type'],
                'pay_method'=>$pay_method,
                'payment'=>$payment,
               
                'source' => $order['source'],
                'seller_ids' => $order['seller_ids'],
                 'order_type' => $order['order_type'],
                '_status' => $order['_status']['now'],
                '_showsubs' => $order['_showsubs'],
                'sub_sn' => $order['_subs']['0']['sub_sn'],
            );
        }
        return $lists;
    }
//    导出excel表中用
    public function get_order_lists2($sqlmap = array()) {
        $orders = $this->model->where($sqlmap)->order('id desc')->select();


        $lists = array();
        foreach ($orders AS $order) {
            //print_r($order['_buyer']['report_id']);
            switch($order['_buyer']['report_id']){
                case 0:
                    $report_id='是';//普通会员
                    break;
                case 1:
                    $report_id='否';//vip会员
                    break;
            }
            switch($order['pay_method']){
                case 1:
                    $pay_method='ACNY';//普通会员
                    $payment=$order['real_amount'];
                    break;
                case 2:
                    $pay_method='USDT';//vip会员
                    $payment=$order['usdt_amount'];
                    break;
            }
            $arr['order_sn']=$order['sn'];
            $ordername=$this->sku_service->find($arr,'sku_name');
            $lists[] = array(
                'id' => $order['id'],
                'sn' => $order['sn'],
                'ordername'=>$ordername['sku_name'],
                'username' => $order['_buyer']['username'],
                'address_name' => $order['address_name'],
                'address_mobile' => $order['address_mobile'],
                'yes_no' => $report_id,
                'system_time' => $order['system_time'],
                'real_amount' => $order['real_amount'],
                '_pay_type' => $order['_pay_type'],
                'pay_method'=>$pay_method,
                'payment'=>$payment,

                'source' => $order['source'],
                'seller_ids' => $order['seller_ids'],
                'order_type' => $order['order_type'],
                '_status' => $order['_status']['now'],
                '_showsubs' => $order['_showsubs'],
                'sub_sn' => $order['_subs']['0']['sub_sn'],
            );
        }
        return $lists;
    }

    /**
     * @param  array 	sql条件
     * @param  integer 	读取的字段
     * @return [type]
     */
    public function find($sqlmap = array(), $field = "") {
        $result = $this->model->where($sqlmap)->field($field)->find();

        return $result;
    }

    /**
     * 条数
     * @param  [arra]   sql条件
     * @return [type]
     */
    public function count($sqlmap = array()) {
        $result = $this->model->where($sqlmap)->count();

        return $result;
    }

}

?>
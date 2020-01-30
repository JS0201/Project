<?php

namespace Order\Service;

use Think\Model;

class OrderSubService extends Model {

    public function __construct() {
        $this->order_model = D('Order/Order');
        $this->model = D('Order/Order_sub');
        $this->sku_model = D('Order/Order_sku');
        $this->order_trade_model = D('Order/order_trade');
        $this->delivery_model = D('Order/Delivery');
        $this->goods_sku_service = D('Goods/Goods_sku', 'Service');
        $this->order_delivery_model = D('Order/Order_delivery');
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
     * 根据主订单号获取子订单信息
     * @param  string 	$sn 	主订单号
     * @param  boolean 	$skus 	是否查询订单商品
     * @param  boolean 	$track 	是否查询订单跟踪
     * @param  boolean 	$group 	是否根据物流分组
     * @return [result]
     */
    public function get_subs($sn, $skus = FALSE, $track = FALSE, $group = FALSE) {
        $subs = $this->model->where(array('order_sn' => $sn))->select();
        foreach ($subs as $key => $sub) {
            $_skus = array();
            if ($track == TRUE) {
               // $subs[$key]['_track'] = $this->service_track->get_tracks_by_sn($sub['sub_sn']);
                $subs[$key]['_track'] = '';
            }
            if ($skus == TRUE) {
                $_skus = $this->sku_model->where(array('sub_sn' => $sub['sub_sn']))->select();
                foreach ($_skus as $k => $_sku) {
                   // $goods_sku = $this->goods_sku_service->fetch_by_id($_sku['sku_id']);
                    $goods_sku = $this->goods_sku_service->detail($_sku['sku_id']);
                    if ($goods_sku['edition'] == $_sku['sku_edition']) {
                        $_skus[$k]['url'] = U('goods/index/detail', array('sku_id' => $_skus[$k]['sku_id']));
                    } else {
                        $_skus[$k]['url'] = U('goods/index/snapshot', array('sku_id' => $_skus[$K]['sku_id'], 'order_sku_id' => $_skus[$k]['id']));
                    }
                }
                $subs[$key]['_skus'] = $_skus;
            }
            if ($group == TRUE) {
                if ($skus == FALSE) {
                    $_skus = $this->sku_model->where(array('sub_sn' => $sub['sub_sn']))->select();
                    $subs[$key]['_skus'] = $_skus;
                }
                $_group = array();
                foreach ($_skus as $sku) {
                    $_group[$sku['delivery_id']]['lists'][] = $sku;
                }
                // 组装物流信息
                foreach ($_group as $o_d_id => $v) {
                    // 分组订单商品总额
                    $total_amount = 0;
                    foreach ($v['lists'] as $sku) {
                        $total_amount += $sku['real_price'];
                    }
                    $_group[$o_d_id]['total_amount'] = sprintf("%.2f", (float) $total_amount);
                    // 赋值状态
                    $_status = $sub['_status']['wait'];
                    if ($sub['delivery_status'] != 0) { // 发过货的为以下状态
                        if ($o_d_id > 0) {
                            $_group[$o_d_id]['delivery'] = $this->delivery_model->find($o_d_id);
                            $_status = ($_group[$o_d_id]['delivery']['isreceive'] == 1) ? 'all_finish' : 'load_finish';
                        } else {
                            $_status = 'load_delivery';
                        }
                    }
                    $_group[$o_d_id]['_status'] = $_status;
                }
                $subs[$key]['_group'] = $_group;
            }
        }
        return $subs;
    }

    /**
     * 设置订单
     * @param  string 	$sn  		订单号(确认支付时传主订单号，其它传子订单号)
     * @param  string 	$action 	操作类型
     *         (order:订单 || pay:支付 || confirm:确认 || delivery:发货 || finish:完成)
     * @param  int 		$status 	状态(只有$action = 'order'时必填)
     * @param  array 	$options 	附加参数
     * @return [boolean]
     */
    public function set_order($sn = '', $action = '', $status = 0, $options = array()) {
        $sn = (string) trim($sn);
        $action = (string) trim($action);
        $status = (int) $status;
        $msg = (string) trim($options['msg']);
        unset($options['msg']);
    
        if (empty($sn)) {
            $this->errors = L('order_sn_not_null');
            return FALSE;
        }
        if (empty($action)) {
            $this->errors = L('operate_type_empty');
            return FALSE;
        }
        if (!in_array($action, array('order', 'pay', 'confirm', 'delivery', 'finish'))) {
            $this->errors = L('operate_type_error');
            return FALSE;
        }
        // 检测订单是否存在
        $this->order = $this->order_model->where(array('sn' => $sn))->find();
        if (!$this->order) {
            $this->order = $this->model->where(array('sub_sn' => $sn))->find();
        }
        if (!$this->order) {
            $this->errors = L('order_not_exist');
            return FALSE;
        }
        // 获取订单状态
        $this->order['_status'] = $this->model->get_status($this->order);
        switch ($action) {
            case 'order': // (2：已取消，3：已回收，4：已删除)
                $result = $this->_order($status, $options);
          
                // 后台删除订单直接返回
                if (IN_ADMIN && $status == 4 && $result !== FALSE) {
                    return TRUE;
                }
                break;
            case 'pay': // 针对所有子订单操作
                $result = $this->_pay($options);
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
        if ($result === FALSE)
            return FALSE;
        // 订单日志
        /*
          $operator = get_operator(); // 获取操作者信息
          $data = array();
          if ($action == 'pay') {
          $data['order_sn'] = $sn;
          $data['action'] = $result['action'];
          $data['operator_id'] = $operator['id'];
          $data['operator_name'] = $operator['username'];
          $data['operator_type'] = $operator['operator_type'];
          $data['msg'] = $msg;
          foreach ($result['sub_sns'] as $sub_sn) {
          $data['sub_sn'] = $sub_sn;
          $this->service_order_log->add($data);
          }
          } else {
          $data['order_sn'] = $this->order['order_sn'];
          $data['sub_sn'] = $sn;
          $data['action'] = $result;
          $data['operator_id'] = $operator['id'];
          $data['operator_name'] = $operator['username'];
          $data['operator_type'] = $operator['operator_type'];
          $data['msg'] = $msg;
          $this->service_order_log->add($data);
          }
         */
        return TRUE;
    }

    /**
     * 支付操作 (针对所有子订单操作)
     * @param  array $options
     *         			paid_amount ：实付金额
     *         			pay_method  ：支付方式
     *         			pay_sn 		：支付流水号
     * @return [string]
     */
    private function _pay($options) {
        $order = $this->order;
        if ($order['pay_type'] != 1 || $order['pay_status'] != 0) {
            $this->errors = L('_params_error_');
            return FALSE;
        }
        $data = array();
        $data['pay_status'] = 1;
        $data['pay_time'] = time();

        // 设置子订单表
        $result = $this->model->where(array('order_sn' => $order['sn']))->save($data);
        if (!$result)
            return FALSE;
        // 设置主订单表信息
        if (isset($options['paid_amount'])) { // 实付金额
            $data['paid_amount'] = sprintf("%.2f", (float) $options['paid_amount']);
        } else {
            $data['paid_amount'] = $order['real_amount'];
        }
        if (isset($options['pay_method'])) { // 支付方式
            $data['pay_method'] = (string) $options['pay_method'];
        }
        if (isset($options['pay_sn'])) {  // 支付流水号
            $data['pay_sn'] = (string) $options['pay_sn'];
        }
        $result = $this->order_model->where(array('sn' => $order['sn']))->save($data);
        if (!$result)
            return FALSE;

        if (isset($options['paid_amount']) && isset($options['pay_method']) && isset($options['pay_sn'])) {
            $_map = array();
            $_map['order_sn'] = $order['sn'];
            $_map['trade_no'] = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 12);
            $_map['total_fee'] = $options['paid_amount'];
            $_map['status'] = 1;
            $_map['time'] = time();
            $_map['method'] = $options['pay_method'];
            $_map['pay_sn'] = $options['pay_sn'];
            $set_pay_sn = $this->order_trade_model->add($_map);
        } else {
            $set_pay_sn = $this->order_trade_model->where(array('order_sn' => $order['sn']))->setField('pay_sn', $data['pay_sn']);
        }
        if ($set_pay_sn === FALSE) {
            $this->errors = L('_os_error_');
            return FALSE;
        }
        // 获取主订单号下的所有子订单号
        $sub_sns = $this->model->where(array('order_sn' => $order['sn']))->getField('sub_sn', TRUE);
        foreach ($sub_sns as $sub_sn) {
            // 订单跟踪
            // $this->service_track->add($order['sn'], $sub_sn, '您的订单已付款，请等待系统确认');
        }
        // 钩子：支付成功

        return array('action' => '支付订单', 'sub_sns' => $sub_sns);
    }

    /* 确认操作 */

    private function _confirm() {
        $order = $this->order;
        if ($order['confirm_status'] == 2 || $order['delivery_status'] != 0 || ($order['pay_type'] == 1 && $order['pay_status'] != 1)) {
            $this->errors = L('_params_error_');
            return FALSE;
        }
        $data = $sqlmap = array();
        $data['confirm_status'] = 2;
        $data['confirm_time'] = time();
        $result = $this->model->where(array('sub_sn' => $order['sub_sn']))->save($data);

        /* 检测标记主订单确认状态 */
        $sqlmap['order_sn'] = $order['order_sn'];
        $all_count = $this->model->where($sqlmap)->count(); // 当前主订单下子订单个数
        // 当前已确认的订单个数
        $sqlmap['confirm_status'] = 2;
        $already_count = $this->model->where($sqlmap)->count();
        if ($all_count == $already_count) { // 已确认
            $this->order_model->where(array('sn' => $order['order_sn']))->setField('confirm_status', 2);
        } else { // 部分确认
            $this->order_model->where(array('sn' => $order['order_sn']))->setField('confirm_status', 1);
        }
        /* 生成发货单 */
        // $this->service_parcel->create($order);
        /* 物流跟踪 */
        //$this->service_track->add($order['order_sn'], $order['sub_sn'], '您的订单已确认，正在配货');
        // 钩子：确认订单

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
    private function _delivery($options = array()) {

        $order = $this->order;
        $is_choise = (int) $options['is_choise']; // 是否选择物流
        $delivery_id = (int) $options['delivery_id'];
        $delivery_sn = (string) trim($options['delivery_sn']);
        $sub_sn = (string) trim($options['sub_sn']);
        $o_sku_ids = array_filter(explode(',', $options['o_sku_ids'])); // 要发货的订单商品ids


        if ($is_choise === 1) {
            if ($delivery_id < 1) {
                $this->errors = L('logistics_empty');
                return FALSE;
            }
            if (empty($delivery_sn)) {
                $this->errors = L('logistics_not_exist');
                return FALSE;
            }
            $delivery_name = M('delivery')->getfield('name', $delivery_id);
        } else {
            $delivery_id = 0;
            $delivery_name = '无需物流运输';
            $delivery_sn = '';
        }


        // 检测订单商品是否已发货，已发货的注销变量值
        foreach ($o_sku_ids as $k => $id) {

            $ret = '';
            $ret = $this->sku_model->where(array('id' => $id))->getfield('delivery_status');
            if ($ret != 0) {
                unset($o_sku_ids[$k]);
            }
        }

        if (count($o_sku_ids) == 0) {
            $this->errors = L('order_goods_empty');
            return FALSE;
        }


        $data = $sqlmap = array();
        // 创建订单物流信息
        $data['o_sku_ids'] = implode(',', $o_sku_ids);
        $data['sub_sn'] = $order['sub_sn'];
        $data['delivery_id'] = $delivery_id;
        $data['delivery_name'] = $delivery_name;
        $data['delivery_sn'] = $delivery_sn;
        $data['delivery_time'] = time();
        $addid = $this->order_delivery_model->add($data);
        if (!$addid) {
            $this->errors = L('_os_error_');
            return FALSE;
        }

        /* 标记订单商品为已发货状态，并关联订单物流id */
        $sqlmap['id'] = array('IN', $o_sku_ids);
        $data = array();
        $data['delivery_id'] = $addid;
        $data['delivery_status'] = 1;
        $this->sku_model->where($sqlmap)->save($data);
        /* 标记子订单发货状态 */
        $sku_count = $already_count = 0;
        $sqlmap = $data = array();
        // 子订单的商品总数
        $sqlmap['sub_sn'] = $order['sub_sn'];
        $sku_count = $this->sku_model->where($sqlmap)->count();
        // 子订单已发货的商品总数
        $sqlmap['delivery_status'] = array('GT', 0);
        $already_count = $this->sku_model->where($sqlmap)->count();
        if ($sku_count == $already_count) { // 已发货
            $data['delivery_status'] = 2;
        } else { // 部分发货
            $data['delivery_status'] = 1;
        }
        $data['delivery_time'] = time();
        $result = $this->model->where(array('sub_sn' => $order['sub_sn']))->save($data);

        /* 标记主订单发货状态 */
        $main_count = $already_count = 0;
        $sqlmap = $data = array();
        // 主订单的商品总数
        $sqlmap['order_sn'] = $order['order_sn'];
        $main_count = $this->sku_model->where($sqlmap)->count();
        // 主订单已发货的商品总数
        $sqlmap['delivery_status'] = array('GT', 0);
        $already_count = $this->sku_model->where($sqlmap)->count();
        if ($main_count == $already_count) { // 已发货
            $data['delivery_status'] = 2;
        } else { // 部分发货
            $data['delivery_status'] = 1;
        }
        $result = $this->order_model->where(array('sn' => $order['order_sn']))->save($data);

        // 如果后台设置发货减库存 => 减库存
        /*
          $stock_change = $this->service_setting->get('stock_change');
          if ($stock_change != NULL && $stock_change == 2) {
          foreach ($o_sku_ids as $k => $id) {
          $o_sku = $this->table_sku->where(array('id' => $id))->field($id, 'sku_id,buy_nums')->find();
          $this->load->service('goods/goods_sku')->set_dec_number($o_sku['sku_id'], $o_sku['buy_nums']);
          }
          }
         * 
         */
        /*
          // 物流跟踪
          $string = '';
          if ($order['delivery_name'] == $delivery_name || empty($order['delivery_name'])) {
          $string = '快递单号：' . $delivery_sn;
          } else {
          $string = '从「' . $order['delivery_name'] . '」修改到「' . $delivery_name . '」';
          if ($is_choise === 1) {
          $string .= '; 快递单号：' . $delivery_sn;
          }
          }


          $this->service_track->add($order['order_sn'], $order['sub_sn'], '您的订单配货完毕，已经发货。' . $string, 0, $addid);
          // 钩子：订单商品已发货
          $order['delivery_sn'] = $delivery_sn;
          $order['delivery_name'] = $delivery_name;
          $order['order_sn'] = $order['order_sn'];
         */
        return '订单发货';
    }

    /**
     * 确认收货(完成)
     * @param  array  $options
     *         			o_delivery_id ：订单发货主键id
     * @return [string]
     */
    private function _finish($options = array()) {
        // $site_name = $this->load->service('admin/setting')->get('site_name');
        $order = $this->order;
        if ($order['finish_status'] == 2 || $order['delivery_status'] == 0) {
            $this->errors = L('_params_error_');
            return FALSE;
        }
        $o_delivery_id = (int) remove_xss($options['o_delivery_id']);
        if (!defined('IN_ADMIN')) {
            if ($o_delivery_id == 0) {
                $this->errors = L('_param_error_');
                return FALSE;
            }



            // 检测该订单物流操作合法性
            $o_delivery = $this->order_delivery_model->find($o_delivery_id);
            if (!$o_delivery || $o_delivery['isreceive'] == 1) {
                $this->error = L('_params_error_');
                return FALSE;
            }
            // 标记当前订单物流为已收货 (order_delivery & order_sku)
            $data = $sqlmap = array();
            $data['id'] = $o_delivery_id;
            $data['isreceive'] = 1;
            $data['receive_time'] = time();
            $result = $this->order_delivery_model->save($data);

            $sqlmap['sub_sn'] = $order['sub_sn'];
            $sqlmap['delivery_id'] = $o_delivery_id;
            $this->sku_model->where($sqlmap)->setField('delivery_status', 2);
            /* 标记子订单完成状态 */
            $sqlmap = $receives = $arr = $sku_ids = array();
            $sku_count = 0;
            $sqlmap['sub_sn'] = $order['sub_sn'];
            $sku_count = $this->sku_model->where($sqlmap)->count(); // 统计该子订单下的所有订单商品数
            // 查询已确认收货订单物流的sku_ids
            $sqlmap['isreceive'] = 1;
            $receives = $this->order_delivery_model->where($sqlmap)->getField('o_sku_ids', TRUE);
            foreach ($receives as $val) {
                $arr = array_filter(explode(',', $val));
                foreach ($arr as $id) {
                    $sku_ids[] = $id;
                }
            }
            $data = array();
            $string = '';
            if (count($sku_ids) == $sku_count) { // 所有订单物流已确认收货
                $data['finish_status'] = 2;
                $string = '感谢您在' . $site_name . '购物，欢迎您的再次光临';
            } else { // 部分订单物流已确认收货
                $data['finish_status'] = 1;
                $string = '感谢您在' . $site_name . '购物，欢迎您的再次光临';
            }
            $data['finish_time'] = time();
            $result = $this->model->where(array('sub_sn' => $order['sub_sn']))->save($data);
        } else {
            // 标记子订单为已完成
            $data = array();
            $data['finish_status'] = 2;
            $data['finish_time'] = time();
            $string = '感谢您在' . $site_name . '购物，欢迎您的再次光临';
            $result = $this->model->where(array('sub_sn' => $order['sub_sn']))->save($data);
            // 标记所有订单物流为已收货 (order_delivery & order_sku)
            $sqlmap = $data = array();
            $sqlmap['sub_sn'] = $order['sub_sn'];
            $data['isreceive'] = 1;
            $data['receive_time'] = time();
            $result = $this->order_delivery_model->where($sqlmap)->save($data);
            $this->sku_model->where($sqlmap)->setField('delivery_status', 2);
        }
        /* 标记主订单的完成状态 */
        // 统计子订单总数
        $sqlmap = $data = array();
        $sub_count = $already_count = 0;
        $sqlmap['sub_sn'] = $order['sub_sn'];
        $sub_count = $this->model->where($sqlmap)->count();
        // 统计子订单已完成的总数
        $sqlmap['finish_status'] = 2;
        $already_count = $this->model->where($sqlmap)->count();
        if ($sub_count == $already_count) { // 所有子订单已确认收货
            $data['finish_status'] = 2;
            // 钩子：整个订单完成
            $order_finish_sn = $order['order_sn'];
            hook('order_finish', $order_finish_sn);
        } else { // 部分子订单已确认收货
            $data['finish_status'] = 1;
        }
        $result = $this->order_model->where(array('sn' => $order['order_sn']))->save($data);

        /*
          // 订单跟踪
          if ($o_delivery_id) {
          $this->service_track->add($order['order_sn'], $order['sub_sn'], $string, 0, $o_delivery_id);
          } else {
          foreach ($order['_skus'] as $k => $v) {
          foreach ($v as $r) {
          if ($r['delivery_status'] != 2) {
          $this->service_track->add($order['order_sn'], $order['sub_sn'], $string, 0, $r['delivery_id']);
          }
          }
          }
          }
         * 
         */
        // 钩子：确认收货(完成)
        hook('delivery_finish');
        return $string;
    }

    /* 订单操作 */

    private function _order($status, $options) {
        $order = $this->order;
        $data = $sqlmap = array();
        switch ($status) {
            case 2: // 取消订单
                $string = '您的订单已取消';
                if ($order['status'] != 1 || $order['delivery_status'] != 0) {
                    $this->errors = L('order_dont_operate');
                    return FALSE;
                }
           

                /* 在线支付：取消整个订单，货到付款：取消当前子订单 */
                $data['status'] = 2;
                $data['system_time'] = time();
                if ($order['pay_type'] == 1) {
                    // 标记所有子订单为已取消
                    $this->model->where(array('order_sn' => $order['order_sn']))->save($data);
                    // 主订单信息
                    $order_main = $this->order_model->where(array('sn' => $order['order_sn']))->find();
                    /* 未发货&已付款的&是否退款到账户余额 ==> 退款到账户余额 */
                    if ($order['delivery_status'] == 0 && $order['pay_status'] == 1 && $options['isrefund'] == 1) {
                        /*
                          $this->load->service('member/member')->change_account($order_main['buyer_id'], 'money', $order_main['paid_amount'], '取消订单退款,订单号:' . $order['order_sn']);
                          // 解冻余额支付的金额
                          if ($order_main['balance_amount'] > 0) {
                          $this->load->service('member/member')->action_frozen($order_main['buyer_id'], $order_main['balance_amount'], false);
                          }
                          $string = '您的订单已取消，已退款到您的账户余额，请查收';
                         * 
                         */
                    }
                    /* 未发货&未付款的&是否退款到账户余额 ==> 退款到账户余额 */
                    if ($order['delivery_status'] == 0 && $order['pay_status'] == 0 && $options['isrefund'] == 1) {
                        /*
                          $this->load->service('member/member')->change_account($order_main['buyer_id'], 'money', $order_main['balance_amount'], '取消订单退款,订单号:' . $order['order_sn']);
                          // 解冻余额支付的金额
                          if ($order_main['balance_amount'] > 0) {
                          $this->load->service('member/member')->action_frozen($order_main['buyer_id'], $order_main['balance_amount'], false);
                          }
                          $string = '您的订单已取消，已退款到您的账户余额，请查收';
                         * 
                         */
                    }
                } else {
                    // 标记当前子订单为已取消
                    $this->model->where(array('sub_sn' => $order['sub_sn']))->save($data);
                }
                /* 检测是否标记主订单状态 */
                $sub_count = $already_count = 0;
                // 统计子订单总数
                $sqlmap['order_sn'] = $order['order_sn'];
                $sub_count = $this->model->where($sqlmap)->count();
                // 统计子订单已取消的总数
                $sqlmap['status'] = 2;
                $already_count = $this->model->where($sqlmap)->count();
                // 所有子订单都已取消 ==> 标记主订单为全部取消
                if ($sub_count == $already_count) {
                    $result = $this->order_model->where(array('sn' => $order['order_sn']))->save($data);
                }
                /* 后台设置为下单减库存 ==> goods_sku加上库存 */
                // $stock_change = $this->service_setting->get('stock_change');

                $skuids = array();
                if ($order['pay_type'] == 1) {
                    $skuids = $this->sku_model->where(array('order_sn' => $order['order_sn'], 'buyer_id' => $order['buyer_id']))->getField('sku_id,buy_nums', TRUE);
                } else {
                    $skuids = $this->sku_model->where(array('sub_sn' => $order['sub_sn'], 'buyer_id' => $order['buyer_id']))->getField('sku_id,buy_nums', TRUE);
                }
                if ($skuids) {
                    foreach ($skuids as $skuid => $num) {
                        $this->goods_sku_service->set_inc_number($skuid, $num);
                    }
                }

                return $string;
                break;

            case 3: // 订单回收站
                if ($order['status'] != 2) {
                    $this->error = L('_params_error_');
                    return FALSE;
                }
                // 标记当前子订单为已回收
                $data['status'] = 3;
                $data['system_time'] = time();
                $result = $this->model->where(array('sub_sn' => $order['sub_sn']))->save($data);

                /* 检测是否标记主订单状态 */
                $sub_count = $already_count = 0;
                // 统计子订单总数
                $sqlmap['order_sn'] = $order['order_sn'];
                $sub_count = $this->model->where($sqlmap)->count();
                // 统计子订单已取消的总数
                $sqlmap['status'] = 3;
                $already_count = $this->model->where($sqlmap)->count();
                if ($sub_count == $already_count) { // 全部子订单都已取消，标记主订单为全部回收
                    $this->order_model->where(array('sn' => $order['order_sn']))->save($data);
                }
                return '您的订单已放入回收站';
                break;

            /* 订单删除 */
            case 4:
                if ($order['status'] != 3) {
                    $this->error = L('_params_error_');
                    return FALSE;
                }
                // 前台用户删除的只更改状态，管理员删除的需删除所有订单相关的信息
                if (defined('IN_ADMIN')) {
                    // 删除子订单
                    $sqlmap['sub_sn'] = $order['sub_sn'];
                    $sqlmap['status'] = 3;
                    $result = $this->model->where($sqlmap)->delete();
                    if (!$result) {
                        $this->errors = L('delete_order_error');
                        return FALSE;
                    }
                    // 删除主订单
                    $this->order_model->where(array('sn' => $order['order_sn']))->delete();

                    // 删除订单商品
                    $this->sku_model->where(array('sub_sn' => $order['sub_sn']))->delete();
                    // 删除订单日志
                    // $this->load->table('order/order_log')->where(array('sub_sn' => $order['sub_sn']))->delete();
                    // 删除订单跟踪
                    // $this->load->table('order/order_track')->where(array('sub_sn' => $order['sub_sn']))->delete();
                    // 删除订单发货单
                    // $this->load->table('order/order_parcel')->where(array('sub_sn' => $order['sub_sn']))->delete();
                    // 删除订单发货单日志
                    // $this->load->table('order/order_parcel_log')->where(array('sub_sn' => $order['sub_sn']))->delete();
                    // 删除订单售后服务表信息
                    //  $this->load->table('order/order_server')->where(array('sub_sn' => $order['sub_sn']))->delete();
                    // 删除订单退货
                    // $this->load->table('order/order_return')->where(array('sub_sn' => $order['sub_sn']))->delete();
                    // 删除订单退货日志
                    //$this->load->table('order/order_return_log')->where(array('sub_sn' => $order['sub_sn']))->delete();
                    // 删除订单退款
                    // $this->load->table('order/order_refund')->where(array('sub_sn' => $order['sub_sn']))->delete();
                    // 删除订单退款日志
                    //$this->load->table('order/order_refund_log')->where(array('sub_sn' => $order['sub_sn']))->delete();
                    return '订单删除成功';
                } else {
                    // 标记当前子订单为删除
                    $data['status'] = 4;
                    $data['system_time'] = time();
                    $result = $this->model->where(array('sub_sn' => $order['sub_sn']))->save($data);

                    /* 检测是否标记主订单状态 */
                    $sub_count = $already_count = 0;
                    // 统计子订单总数
                    $sqlmap['order_sn'] = $order['order_sn'];
                    $sub_count = $this->model->where($sqlmap)->count();
                    // 统计子订单已回收的总数
                    $sqlmap['status'] = 4;
                    $already_count = $this->model->where($sqlmap)->count();
                    if ($sub_count == $already_count) { // 全部子订单都已回收，标记主订单为全部删除
                        $this->order_model->where(array('sn' => $order['order_sn']))->save($data);
                    }
                    return '您的订单已从回收站删除';
                }
                break;
        }
    }

    /**
     * 确认发货弹窗：根据子订单号获取skus发货信息
     * @param  string $sub_sn 子订单号
     * @return [result]
     */
    public function sub_delivery_skus($sub_sn) {
        return $this->sku_model->where(array('sub_sn' => $sub_sn))->order('delivery_status asc')->select();
    }

}

?>
<?php

namespace Pay\Model;

use Think\Model;

class PayModel extends Model {

    protected $result = array();
    protected $_validate = array(
            //array('name','require','请输入规格名称',1), 
    );

    public function _after_select(&$orders, $options) {
        $_subs = array();


        foreach ($orders as $k => $order) {
            // 获取子订单
            $_subs = $this->detail($order['sn'])->subs(TRUE, TRUE, TRUE)->output();
            $orders[$k] = $_subs;
            // 买家信息
            $orders[$k]['_buyer'] = D('Member/Member')->fetch_by_id($order['buyer_id']);

            // 是否显示子订单号信息
            $orders[$k]['_showsubs'] = (count($_subs['_subs']) > 1) ? TRUE : FALSE;
        }


        return $orders;
    }

    /**
     * 获取订单当前状态 [now:当前状态，wait：待操作状态]
     * @param  $order    : 订单信息
     * @return [result]
     */
    public function get_status($order = array()) {
        if (empty($order)) {
            $this->errors = L('order_parame_empty');
            return FALSE;
        }
        $arr = array();
        switch ($order['status']) {
            case 2: // 已取消
                $arr['now'] = 'cancel';    // 已取消
                $arr['wait'] = 'recycle';   // 已回收
                break;
            case 3: // 已回收
                $arr['now'] = 'recycle';
                $arr['wait'] = 'delete';
                break;
            case 4: // 前台用户已删除
                $arr['now'] = 'delete';
                $arr['wait'] = '';
                break;
            default:    // 正常状态
                if ($order['pay_type'] == 1 && $order['pay_status'] == 0) {
                    $arr['now'] = 'create';
                    $arr['wait'] = 'pay';
                } else if ($order['finish_status'] == 2) {
                    $arr['now'] = 'all_finish';
                    $arr['wait'] = 'aftermarket';
                } else if (($order['pay_type'] == 1 && $order['pay_status'] == 1 && $order['confirm_status'] == 0) || ($order['pay_type'] == 2 && $order['confirm_status'] == 0)) {
                    $arr['now'] = ($order['pay_type'] == 1) ? 'pay' : 'create';
                    $arr['wait'] = 'load_confirm';  // 待确认
                } else if ($order['confirm_status'] == 1) {
                    $arr['now'] = 'part_confirm';  // 部分确认
                    $arr['wait'] = 'all_confirm';   // 已确认
                } else if ($order['finish_status'] == 0 && $order['confirm_status'] == 2) {
                    if ($order['delivery_status'] == 1) {
                        $arr['now'] = 'part_delivery'; // 部分发货
                        $arr['wait'] = 'all_delivery';  // 已发货
                    } else if ($order['delivery_status'] == 2) {
                        $arr['now'] = 'all_delivery';  // 已发货
                        $arr['wait'] = 'load_finish';        // 确认完成
                    } else {
                        $arr['now'] = 'all_confirm';
                        $arr['wait'] = 'load_delivery';
                    }
                } else if ($order['finish_status'] == 1) {
                    $arr['now'] = 'part_finish';   // 部分完成
                    $arr['wait'] = 'all_finish';    //  已完成
                }
                break;
        }
        return $arr;
    }

    /* 根据订单号获取订单信息(连贯操作) */

    // 主订单信息
    public function detail($sn) {
        $this->result = $this->where(array('sn' => $sn))->find();
        if (!empty($this->result)) {
            // 状态标识
            $this->result['_status'] = D('Order/OrderSub')->get_status($this->result);
            
            $this->result['_pay_type'] = ($this->result['pay_type'] == 2) ? '货到付款' : '在线支付';
        }
        return $this;
    }

    // 子订单信息(skus : 获取订单skus信息，track ：订单跟踪信息 ,group : 对订单商品分组)
    public function subs($skus = FALSE, $track = FALSE, $group = FALSE) {
        if ($this->result['sn']) {
            $this->result['_subs'] = '';
        }
        return $this;
    }

    // 买家信息
    public function buyer() {
        if ($this->result['buyer_id']) {
            $this->result['_buyer'] = D('Member/Member')->fetch_by_id($this->result['buyer_id']);
        }
        return $this;
    }

    // 售后信息
    public function after() {
        if ($this->result['sn']) {
            $this->result['_after'] = '';
        }
        return $this;
    }

    // 输出信息,(默认只输出订单信息,$field='all'时，输出所有信息,$field为其他值时输出该参数的方法值)
    public function output($field = '') {
        if ($field) {
            if ($field == 'all') {
                $this->subs(TRUE, TRUE)->buyer()->after();
                return $this->result;
            } else {
                return $this->result['_' . $field];
            }
        } else {
            return $this->result;
        }
    }

}

?>
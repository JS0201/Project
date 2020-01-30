<?php

namespace Order\Model;

use Think\Model;

class OrderSubModel extends Model {

    protected $result = array();
    protected $_validate = array(
    );

    public function _after_find(&$sub, $options) {
        // 根据发货物流分组
        $o_skus = $info = array();
        // 订单状态信息
        $sub['_status'] = $this->get_status($sub);

        $o_skus = D('Order/Order_sku')->where(array('sub_sn' => $sub['sub_sn']))->order('id DESC')->select();
        foreach ($o_skus as $k => $val) {
            $val['delivery_template_name'] = '';
            $info[$val['delivery_id']][] = $val;
        }
        krsort($info);
        $sub['_skus'] = $info;

        $this->data = $sub;
        return $this->data;
    }

    /**
     * 获取订单状态
     * @param  $order    : 订单信息
     * @return [string]
     */
    public function get_status($order = array()) {
        if (empty($order)) {
            $this->errors = L('order_parame_empty');
            return FALSE;
        }
        $arr = array();
        switch ($order['status']) {
            case 2: // 已取消
                $arr['now'] = 'cancel';
                $arr['wait'] = 'cancel';
                $arr['wait_ch'] = ch_status($arr['wait']);
                break;
            case 3: // 已回收
                $arr['now'] = 'recycle';
                $arr['wait'] = 'recycle';
                $arr['wait_ch'] = ch_status($arr['wait']);
                break;
            case 4: // 前台用户已删除
                $arr['now'] = 'delete';
                break;
            default:    // 正常状态
                if (($order['pay_type'] == 1 && $order['pay_status'] == 0 || ($order['pay_type'] == 2 && $order['confirm_status'] == 0))) {
                    $arr['now'] = 'create'; // 创建订单
                    $arr['wait'] = ($order['pay_type'] == 1) ? 'load_pay' : 'load_confirm';
                } else if ($order['pay_type'] == 1 && $order['pay_status'] == 1 && $order['confirm_status'] == 0) {
                    $arr['now'] = 'pay';    // 已支付
                    $arr['wait'] = 'load_confirm';
                } else if ($order['confirm_status'] == 1 && $order['delivery_status'] == 0) {
                    $arr['now'] = 'part_confirm';  // 部分确认
                    $arr['wait'] = 'load_delivery';
                } else if ($order['confirm_status'] == 2 && $order['delivery_status'] == 0) {
                    $arr['now'] = 'all_confirm';  // 已确认
                    $arr['wait'] = 'load_delivery';
                } else if ($order['delivery_status'] == 1 && $order['finish_status'] == 0) {
                    $arr['now'] = 'part_delivery';   // 部分发货
                } else if ($order['delivery_status'] == 2 && $order['finish_status'] == 0) {
                    $arr['now'] = 'all_delivery';   // 已发货
                    $arr['wait'] = 'load_finish';
                } else if ($order['delivery_status'] != 0 && $order['finish_status'] == 1) {
                    $arr['now'] = 'part_finish';   // 部分完成
                    $arr['wait'] = 'part_delivery';
                } else if ($order['delivery_status'] == 2 && $order['finish_status'] == 2) {
                    $arr['now'] = 'all_finish';   // 已完成
                    $arr['wait'] = 'all_finish';
                }
                $arr['wait_ch'] = ch_status($arr['wait']);
                break;
        }
        return $arr;
    }

}

?>
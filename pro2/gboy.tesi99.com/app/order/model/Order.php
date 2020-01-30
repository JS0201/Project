<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\order\model;
use think\Model;
use think\Db;
class Order extends Model{


    protected $append=['pay_type_text','order_status','skus_list'];
    protected $table = 'gboy_order';
    protected function getsystemTimeAttr($value){

        return getdatetime($value);
    }

    protected function getpayTypeTextAttr($value,$data){

        return $data['pay_type']==1?'在线支付':'货到付款';

    }


    protected function getskusListAttr($value,$order){


        return model('order/Sku')->where(['order_sn'=>$order['sn']])->order('id asc')->select();


    }


    protected function getpayTimeAttr($value){

        return $value?getdatetime($value):'';

    }
    public function getOne($where, $field)
    {
        $re = array();
        $re = DB::table($this->table)->field($field)->where($where)->find();
        return $re;
    }
    public function deleted($where)
    {
        $re = false;
        $re=DB::table($this->table)->where($where)->delete();
        return $re;
    }
    public function updated($where, $data)
    {
        $re = false;
        $re = DB::table($this->table)->where($where)->update($data);
        return $re;
    }

    protected function getorderStatusAttr($value,$order) {

        $arr = [];

        switch ($order['status']) {
            case 2: // 已取消
                $arr['now']  = 'cancel';    // 已取消
                $arr['wait'] = 'recycle';   // 已回收
                break;
            case 3: // 已回收
                $arr['now']  = 'recycle';
                $arr['wait'] = 'delete';
                break;
            case 4: // 前台用户已删除
                $arr['now']  = 'delete';
                $arr['wait'] = '';
                break;
            default:    // 正常状态


                if ($order['pay_type'] == 1 && $order['pay_status'] == 0) {
                    $arr['now']  = 'create';
                    $arr['wait'] = 'pay';
                } else if ($order['finish_status'] == 2){
                    $arr['now']  = 'all_finish';
                    $arr['wait'] = 'aftermarket';
                } else if (($order['pay_type'] == 1 && $order['pay_status'] == 1 && $order['confirm_status'] == 0) || ($order['pay_type'] == 2 && $order['confirm_status'] == 0)) {
                    $arr['now']  = ($order['pay_type'] == 1) ? 'pay' :'create';
                    $arr['wait'] = 'load_confirm';  // 待确认
                }else if ($order['confirm_status'] == 1) {
                    $arr['now']  = 'part_confirm';  // 部分确认
                    $arr['wait'] = 'all_confirm';   // 已确认
                }else if ($order['finish_status'] == 0 && $order['confirm_status'] == 2) {
                    if ($order['delivery_status'] == 1) {
                        $arr['now']  = 'part_delivery'; // 部分发货
                        $arr['wait'] = 'all_delivery';  // 已发货
                    } else if ($order['delivery_status'] == 2) {
                        $arr['now']  = 'all_delivery';  // 已发货
                        $arr['wait'] = 'load_finish';        // 确认完成
                    } else {
                        $arr['now']  = 'all_confirm';
                        $arr['wait'] = 'load_delivery';
                    }
                } else if ($order['finish_status'] == 1) {
                    $arr['now']  = 'part_finish';   // 部分完成
                    $arr['wait'] = 'all_finish';    //  已完成
                }
                break;
        }



        return $arr;
    }



    public function get_status($order){


        return $this->getorderStatusAttr('',$order);

    }

    /*
    protected function getorderStatusAttr($value,$order) {
        $arr = array();
        switch ($value) {
            case 2: // 已取消
                $text='全部取消';
                break;
            case 3: // 已回收
                $text='全部回收';
                break;
            case 4: // 前台用户已删除
                $text='全部删除';
                break;
            default:    // 正常状态
                if ($order['pay_type'] == 1 && $order['pay_status'] == 0) {
                    $text='创建订单';
                } else if ($order['finish_status'] == 2){
                    $text='已完成';
                } else if (($order['pay_type'] == 1 && $order['pay_status'] == 1 && $order['confirm_status'] == 0) || ($order['pay_type'] == 2 && $order['confirm_status'] == 0)) {
                    $text='未确认';
                }else if ($order['confirm_status'] == 1) {
                    $text='部分确认';
                }else if ($order['finish_status'] == 0 && $order['confirm_status'] == 2) {
                    if ($order['delivery_status'] == 1) {
                        $text='部分发货';
                    } else if ($order['delivery_status'] == 2) {
                        $text='已发货';
                    } else {
                        $text='待发货';
                    }
                } else if ($order['finish_status'] == 1) {
                    $text='部分完成';
                }
                break;
        }
        return $text;
    }
    */



}
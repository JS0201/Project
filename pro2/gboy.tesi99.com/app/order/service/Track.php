<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\order\service;
use think\Model;

class Track extends Model{

    public function initialize()
    {
       $this->model = model('order/Track');
    }


    /**
     * 添加订单跟踪
     * @param  string 	$order_sn  	主订单号 (必传)
     * @param  string 	$sub_sn  	子订单号 (必传)
     * @param  string 	$msg  		跟踪内容
     * @param  int 		$time  		时间戳 (默认当前时间戳)
     * @param  int 		$delivery_id 订单物流关联id (默认0)
     * @return [boolean]
     */
    public function add($order_sn ,$sub_sn ,$msg = '' ,$time = 0 ,$delivery_id = 0) {
        $order_sn = (string) remove_xss($order_sn);
        $sub_sn = (string) remove_xss($sub_sn);
        if (!$order_sn) {
            $this->errors = lang('parent_order_sn_not_exist');
            return FALSE;
        }
        if (!$sub_sn) {
            $this->errors = lang('child_order_sn_empty');
            return FALSE;
        }
        $data = array();
        $data['order_sn'] = $order_sn;
        $data['sub_sn']   = $sub_sn;
        $data['msg']      = (string) remove_xss($msg);
        $data['time']     = ((int) $time == 0) ? time() : $time;
        $data['add_time'] = time();
        $data['clientip'] = getip();
        if ($delivery_id > 0) $data['delivery_id'] = (int) remove_xss($delivery_id);
        $result = $this->model->isUpdate(false)->data($data)->save();
        if (!$this->model->id) {
            return FALSE;
        }
        return true;
    }


}
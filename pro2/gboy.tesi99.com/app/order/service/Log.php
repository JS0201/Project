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

class Log extends Model{

    public function initialize()
    {
       $this->model = model('order/Log');
    }


    /**
     * 写入订单日志
     * @param $params 日志相关参数
     * @return [boolean]
     */
    public function add($params = array(),$extra = FALSE) {
        $params = array_filter($params);
        if (empty($params)) {
            $this->errors = lang('order_log_empty');
            return FALSE;
        }
        $params['system_time']        = time();
        $params['clientip']           = getip();
        $result = $this->model->isUpdate(false)->data($params)->save();
        if (!$this->model->id) {
            return FALSE;
        }
        return $result;
    }


    /**
     * 根据子订单号获取日志
     * @param $sn : 订单号(默认空)
     * @param $order  : 排序(默认主键升序)
     * @return [result]
     */
    public function get_by_order_sn($sn = '' , $order = 'id ASC') {
        $sn = (string) remove_xss($sn);

        $order = (string) remove_xss($order);
        $sqlmap = array();
        $sqlmap['order_sn'] = $sn;
        return $this->model->where($sqlmap)->order($order)->select();
    }

}
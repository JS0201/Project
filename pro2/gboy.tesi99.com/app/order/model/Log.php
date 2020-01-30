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

class Log extends Model{

    protected $table='gboy_order_log';

    protected $append=['info'];

    protected function getinfoAttr($value,$data){


        if($data['operator_type']==1){
            $operator_user='系统';
        }else{
            $operator_user='买家';
        }

        if($data['msg']) $msg='操作备注：'.$data['msg'];
        $info=$operator_user.'&emsp;'.$data['operator_name'].'&emsp;于&emsp;'.getdatetime($data['system_time']).'&emsp;「'.$data['action'].'」'.$msg;

        return $info;
    }
}
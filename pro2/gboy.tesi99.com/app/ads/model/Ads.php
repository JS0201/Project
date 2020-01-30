<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\ads\model;
use think\Model;
class Ads extends Model{

    protected $append=['position_name'];

    protected $insert=['add_time'];


    protected function getPositionNameAttr($value,$data){

        return  model('ads/AdsPosition')->where('id',$data['position_id'])->value('name');
    }

    protected function setAddtimeAttr(){
        return time();
    }

    protected function setStarttimeAttr($value,$data){


         return $value?strtotime($value):0;

    }

    protected function setEndtimeAttr($value,$data){

        return $value?strtotime($value):0;

    }

    protected function getStarttimeAttr($value,$data){

        if($value){
            return getdatetime($value,'Y-m-d');
        }

    }

    protected function getEndtimeAttr($value,$data){

        if($value){
            return getdatetime($value,'Y-m-d');
        }

    }

}
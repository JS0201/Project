<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\notify\model;
use think\Model;

class Template extends Model
{
    protected $table='gboy_notify_template';






    protected function getEnabledAttr($value,$data){

        if($value) return json_decode($value,true);

    }

    protected function getTemplateAttr($value,$data){

        if($value) return json_decode($value,true);

    }

    protected function setEnabledAttr($value,$data){

        if($value) return json_encode($value);

    }



}
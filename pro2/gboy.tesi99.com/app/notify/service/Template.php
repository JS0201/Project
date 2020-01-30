<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\notify\service;
use think\Model;

class Template extends Model
{

    public function initialize()
    {

        $this->model = model('notify/Template');
    }


    public function fetch_code($code) {
        $data = $this->model->where(['id'=>$code])->find();
        if(is_null($data))return FALSE;
        return $data;
    }


    public function edit($data){

        if(empty($data)) {
            $this->errors = lang('_param_error_');
            return false;
        }
        $result=$this->model->allowField(true)->isupdate(true)->save($data);

        if($result===false){
            $this->errors=$this->model->getError();
            return false;
        }

        return true;

    }

    public function del($code) {
        if(empty($code)) {
            $this->errors = lang('_param_error_');
            return false;
        }
        $result = $this->model->where(['id'=>$code])->delete();

        return true;
    }

}
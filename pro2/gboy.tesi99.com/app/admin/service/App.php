<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\admin\service;
use think\Model;

class App extends Model{

    public function initialize()
    {

        $this->model = model('admin/App');

    }


    public function lists($status = 1 , $type='modeule'){

        //$app_path = $type == 'plugin' ? PLUGIN_PATH : MODULES_PATH;
        $lists = $this->model->where(['identifier' => ['like',$type.'.%'],'available'=>$status])->select()->toArray();
        return $lists;

    }

    public function get_find($module_name) {

        $module_name='module.'.$module_name;

        return $this->model->where(['identifier'=>$module_name])->find();
    }



    public function config($data){

        $result = $this->model->validate(true)->isupdate(true)->save($data);

        if ($result === false) {
            $this->errors = $this->model->getError();
            return false;
        }
        // 生成log记录
        $app = $this->model->where(array('id' => $this->model->data['id']))->field('name')->find();
        $name = lang('admin_log_app_edit');
        $contetn = '修改：' . $app['name'];
        AdminLog(2, $contetn, lang("admin_log_app"), $name, $this->model->data['id']);

        return $this->model;
    }






    public function toggle($module_name){


        $module = $this->get_find($module_name);

        $status=(int)!$module['available'];

        $this->model->where(['identifier'=>$module['identifier']])->data(['available'=>$status])->update();

        return true;

    }



}
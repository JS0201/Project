<?php

namespace app\admin\controller;

use app\common\controller\Init;

class Site extends Init
{
    //初始化
    public $data = array('peer'=>13);

    public function _initialize()
    {
        parent::_initialize();
        $this->service = model('admin/Site', 'service');
        $this->attachment_service = model('attachment/Attachment', 'service');
    }


    public function base()
    {
        if (request()->isPost()) {
            if (!empty($_FILES['watermark_logo']['name'])) {
                $file = request()->file('watermark_logo');
                if($file){
                    $info = $file->move(ROOT_PATH . 'data' . DS . 'watermark','watermark.png');
                    if($info){
                        $_POST['watermark_logo'] = $info->getFilename();
                    }else{
                        showmessage($file->getError());
                    }
                }
            }

            if (!$this->service->submit($_POST, 'cache.php', 'base')) {
                showmessage($this->service->errors);
            }
            showmessage(lang('_operation_success_'), url('base'), 1);

        } else {
            $base = array(1, 4, 12,7, 0);
            $data = $this->data;
            foreach($data as $k => $v) {
                if(config('plug.'.$k)) {
                    $base[] = $v;
                }
            }
            $group_list = $this->service->group($base);
            $list = $this->get_list($group_list);

            $this->assign('list', $list);
            $this->assign('group_list', $group_list);
            return $this->fetch();
        }


    }

    public function shop()
    {

        if (request()->isPost()) {

            if (!empty($_FILES['logo']['name'])) {

                if (!$picurl = $this->attachment_service->config(['mid' => 1])->upload('logo')) {
                    showmessage($this->attachment_service->errors);
                }

                $_POST['logo'] = $picurl;
            }

            if (!$this->service->submit($_POST, 'cache.php', 'shop')) {
                showmessage($this->service->errors);
            }

            showmessage(lang('_operation_success_'), url('shop'), 1);

        } else {

            $group_list = $this->service->group([2, 3, 5, 8]);

            $list = $this->get_list($group_list);

            $this->assign('list', $list);
            $this->assign('group_list', $group_list);
            return $this->fetch('base');
        }


    }


    public function member()
    {

        if (request()->isPost()) {

            if(!$_POST['reg_pass_complex']){
                $_POST['reg_pass_complex']='';
            }

            if(!$_POST['reg_user_fields']){
                $_POST['reg_user_fields']='';
            }

            if (!$this->service->submit($_POST, 'cache.php', 'member')) {
                showmessage($this->service->errors);
            }

            showmessage(lang('_operation_success_'), url('member'), 1);

        } else {

            $group_list = $this->service->group([6]);

            $list = $this->get_list($group_list);


            $this->assign('list', $list);
            $this->assign('group_list', $group_list);
            return $this->fetch('base');
        }


    }




    //安全配置
    public function safe(){


        if (request()->isPost()) {

            $data=input('post.');
            if(empty($data['web_app_trace'])){
                set_conifg(['app_trace'=>false]);
            }else{
                set_conifg(['app_trace'=>true]);
            }


            if(empty($data['web_app_debug'])){
                set_conifg(['app_debug'=>false]);
            }else{
                set_conifg(['app_debug'=>true]);
            }

            if($data['web_admin_url']){
                $admin_url=config('cache.web_admin_url');
                $admin_url=$admin_url?$admin_url:'admin';
                rename($admin_url,$data['web_admin_url']);
            }

            if (!$this->service->submit($data, 'cache.php', 'safe')) {
                showmessage($this->service->errors);
            }

            showmessage(lang('_operation_success_'), url('safe'), 1);

        }else{

            $group_list = $this->service->group([10]);
            $list = $this->get_list($group_list);

            $this->assign('list', $list);
            $this->assign('group_list', $group_list);
            return $this->fetch('base');
        }


    }


    //性能配置
    public function performance(){

        if (request()->isPost()) {

            if (!extension_loaded('redis') && input('post.cache_type')=='redis') {
                showmessage('redis扩展不存在');
            }

            if (!$this->service->submit($_POST, 'cache.php', 'performance')) {
                showmessage($this->service->errors);
            }

            showmessage(lang('_operation_success_'), url('performance'), 1);

        } else {

            $group_list = $this->service->group([11]);

            $list = $this->get_list($group_list);

            $this->assign('list', $list);
            $this->assign('group_list', $group_list);
            return $this->fetch('base');
        }
    }







    private function get_list($group_list)
    {

        foreach ($group_list as $k => $v) {

            $list[$k] = $this->service->setting($k);

            foreach ($list[$k] as $key => $val) {

                if ($val['type'] == 'radio' || $val['type'] == 'select' || $val['type'] == 'checkbox') {
                    $default_value = $val['default_value'];

                    eval("\$default_value_eval = " . $default_value . '; ');
                    $list[$k][$key]['default_value'] = array('items' => $default_value_eval);

                }


                if ($val['type'] == 'radio' && !is_numeric($val['value'])) {

                    if (is_array(unserialize($val['value']))) {
                        $list[$k][$key]['value'] = unserialize($val['value']);
                    }


                }

                if ($val['type'] == 'checkbox') {
                    $list[$k][$key]['value'] = unserialize($list[$k][$key]['value']);
                    $list[$k][$key]['key'] = $val['key'] . '[]';
                }


            }
        }

        return $list;
    }











}
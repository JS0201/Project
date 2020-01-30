<?php

namespace app\weixin\controller;

use app\common\controller\Init;

class Site extends Init
{
    //初始化


    public function _initialize()
    {
        parent::_initialize();

        $this->service = model('admin/Site', 'service');
        $this->attachment_service = model('attachment/Attachment', 'service');

    }


    public function base()
    {


        if (request()->isPost()) {


            if (!empty($_FILES['wx_code']['name'])) {

                if (!$picurl = $this->attachment_service->config(['mid' => ADMIN_ID])->upload('wx_code')) {
                    showmessage($this->attachment_service->errors);
                }
                $_POST['wx_code'] = $picurl;
            }


            if (!$this->service->submit($_POST, 'cache.php', 'weixin')) {
                showmessage($this->service->errors);
            }

            showmessage(lang('_operation_success_'), url('base'), 1);

        } else {

            $group_list = $this->service->group([9]);

            $list = $this->get_list($group_list);

			multi_array_value_del($list,'key','wx_welcome');
			multi_array_value_del($list,'key','wx_default_welcome');

			
			$server=input('server.');
			$authorize_url=$server['HTTP_HOST'];
			
			
            $this->assign('list', $list);
            $this->assign('group_list', $group_list)->assign('authorize_url',$authorize_url);
            return $this->fetch();
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




    public function reply(){

        if(is_post()){
            $this->service->submit(input('post.'),'cache.php', 'weixin_reply');
            showmessage(lang('_operation_success_'), url('reply'), 1);

        }else{

            return $this->fetch();
        }


    }

}
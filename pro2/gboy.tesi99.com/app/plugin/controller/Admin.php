<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\plugin\controller;
use app\common\controller\Init;
class Admin extends Init{

    public function _initialize()
    {
        parent::_initialize();
        $this->service = model('plugin/Plugin','service');
    }




    public function index(){
        $plugins = $this->service->fetch_all();
        $this->assign('plugins',$plugins);
        return $this->fetch();
    }


    public function install(){
        $name=input('name');
        if(!$plugin = $this->service->install($name)) {
            showmessage($this->service->errors);
        }
        showmessage(lang('plugin_install_success'), url('index'), 1);
    }

    public function config(){

        if(!$plugin = $this->service->fetch_name(input('name'))) {
            showmessage($this->service->errors);
        }

        if(is_post()){


            if(!$this->service->config(input('post.vars/a'),input('name/s'))){
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'),1);
        }else{


            $_setting = $this->service->get_fech_all();

            $_config = $_setting[$plugin['name']]['config'];

            $this->assign('plugin',$plugin)->assign('_config',$_config);
            return $this->fetch();
        }

    }


    public function update(){
        $name = input('name');
        if(!$this->service->updated($name)){
            showmessage($this->service->errors);
        }
        showmessage(lang('plugin_update_success'), url('index'), 1);
    }

    public function uninstall() {
        $name = input('name');

        if(!$this->service->uninstall($name)){
            showmessage($this->service->errors);
        }
        showmessage(lang('uninstall_success'), url('index'), 1);
    }


    public function toggle(){

        $name = input('name');

        if(!$this->service->toggle($name)){
            showmessage($this->service->errors);
        }
        showmessage(Lang('_operation_success_'), url('index'),1);

    }

}
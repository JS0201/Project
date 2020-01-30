<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\weixin\controller;
use app\common\controller\Init;

class Menu extends Init{

    public function _initialize()
    {

        parent::_initialize();
        $this->service = model('weixin/Menu','service');
    }




    public function index(){

        $list=$this->service->get_list([]);
        $this->assign('list',$list);
        return $this->fetch();
    }


    public function add(){
        if(is_post()){
            if(false===$this->service->edit(input('post.'))){
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'), 1);
        }else{
            return $this->fetch('edit');
        }


    }


    public function edit(){

        if(!$info=$this->service->get_find(['id'=>input('id/d')])) {
            showmessage(lang('_param_error_'));
        }



        if(is_post()){

            if(false===$this->service->edit(input('post.'),true)){
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'), 1);

        }else{

            $this->assign('info',$info);
            return $this->fetch();
        }

    }


    public function delete()
    {

        $ids = input('param.id/a');

        if(!$this->service->del($ids)) {
            showmessage($this->service->errors);
        }

        showmessage(lang('_operation_success_'), url('index'), 1);
    }



}
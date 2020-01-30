<?php

namespace app\admin\controller;

use app\common\controller\Init;
use think\Lang;
use think\Request;

class AdminGroup extends Init
{
    //初始化

    public function _initialize()
    {
        parent::_initialize();

        $this->service = model('admin/AdminGroup', 'service');

        $this->node_service = model('admin/Node','service');

    }


    public function index()
    {


        $list = $this->service->get_lists();

        $this->assign('list', $list);
        return $this->fetch();
    }


    public function add()
    {
        if (is_post()) {
            if (!$this->service->edit(Request::instance()->post())) {
                showmessage($this->service->errors);
            }
            showmessage(lang('_operation_success_'), url('index'),1);
        } else {
            $nodes=$this->node_service->ztree();
            $nodes = list_to_tree($nodes,'id', 'parent_id','children');
            multi_array_reset_key($nodes);
            $nodes=array_values($nodes);
            $nodes=json_encode($nodes);
            $this->assign('nodes',$nodes);
            return $this->fetch('edit');
        }


    }


    public function edit()
    {


        
        if(!$info = current($this->service->get_lists(array('id'=>request()->param('id/d'))))){
            showmessage(Lang('_param_error_'));
        }

        if($info['id']==1){

            showmessage(Lang('_operation_fail_'));
        }


        if (Request()->isPost()) {

            if (!$this->service->edit(Request::instance()->post(),true)) {
                showmessage($this->service->errors, url('index'), 1);
            }
            showmessage(Lang('_operation_success_'), url('index'));

        }else{


            $nodes=$this->node_service->ztree();
            $nodes = list_to_tree($nodes,'id', 'parent_id','children');
            multi_array_reset_key($nodes);
            $nodes=array_values($nodes);
            $nodes=json_encode($nodes);
            $this->assign('nodes',$nodes);
            $this->assign('info', $info);
            return $this->fetch();

        }

    }


    public function delete()
    {

           if(!$this->service->del(Request::instance()->param('id/a'))){
                showmessage($this->service->errors);
           }

           showmessage(lang('_operation_success_'),url('index'),1);
    }


}
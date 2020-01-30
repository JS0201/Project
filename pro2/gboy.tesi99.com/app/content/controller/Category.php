<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\content\controller;
use app\common\controller\Init;

class Category extends Init{


    public function _initialize()
    {
        parent::_initialize();

        $this->service = model('content/Category','service');
        $this->model = model('content/Category');
        //$this->attachment_service = model('attachment/Attachment','service');
    }



    public function index(){

        $list=$this->service->category_lists();
        $this->assign('list',$list);
        return $this->fetch();

    }


    public function add(){
        if(is_post()){
            if (!$this->service->edit(input('post.'))) {
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'),1);
        }else{
            $parent_id=input('param.parent_id/d');
            if($parent_id){
                $parent_info=$this->service->get_by_id(['id'=>$parent_id]);
                $this->assign('parent_info',$parent_info);
            }
            $category_node=$this->service->category_node();

            $this->assign('model',$this->model->model());
            $this->assign('category_node',$category_node);


            return $this->fetch('edit');


        }


    }


    public function edit(){

        if(is_post()){
            if (!$this->service->edit(input('post.'),true)) {
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'),1);

        }else{

            $id=input('id/d');
            $info=$this->service->get_by_id(['id'=>$id]);
            $category_node=$this->service->category_node($info['id']);

            $this->assign('parent_info',$info);
            $this->assign('model',$this->model->model());
            $this->assign('category_node',$category_node);
            $this->assign('info',$info);
            return $this->fetch();

        }

    }



    public function delete()
    {

        if(!$this->service->del(input('param.id/a'))) {
            showmessage($this->service->errors);
        }

        showmessage(lang('_operation_success_'), url('index'), 1);
    }





    public function ajax_select_tpl(){

        $modelid=input('get.modelid/d');
        $tpl_list=$this->service->select_tpl($modelid);

        echo  json_encode($tpl_list);

    }
}
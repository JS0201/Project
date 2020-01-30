<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\goods\controller;
use app\common\controller\Init;

class Category extends Init{

    public function _initialize()
    {
        parent::_initialize();

        $this->service = model('goods/Category','service');

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

                $parent_info=$this->service->get_by_id(['id'=>$parent_id],true,true);

                $info=[];
                $info['parent_id']=$parent_info['id'];
                $info['path_id']=$parent_info['path_id'];
                $info['parent_name']=$parent_info['parent_name'];
                $this->assign('info',$info);

            }
            return $this->fetch('edit');
        }

    }



    public function edit(){
        $sqlmap=[];
        $sqlmap['id']=input('id/d');
        if(!$info=$this->service->get_by_id($sqlmap,false,true)){
            showmessage(lang('_param_error_'));
        }

        if(is_post()){
            if (!$this->service->edit(input('post.'),true)) {
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'),1);

        }else{

            $this->assign('info',$info);
            return $this->fetch();
        }




    }



    public function delete()
    {




        $ids=input('param.id/a');

        foreach($ids as $id){
            $sqlmap=[];
            $sqlmap['path_id']=['like','%,'.$id.',%'];

            if(!$this->service->del($sqlmap)) {
                showmessage($this->service->errors);
            }
        }


        showmessage(lang('_operation_success_'), url('index'), 1);
    }



    public function popup(){

        $top_arr[0]= array('id' => 0,'name' => '顶级分类','level' => 0,'parent_id' => -1);
        $category=$this->service->category_lists();

        $category=array_merge($top_arr,$category);

        $this->assign('category',$category);
        return $this->fetch();
    }


}
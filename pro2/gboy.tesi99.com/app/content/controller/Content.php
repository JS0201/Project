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
use think\Lang;

class Content extends Init{

    public function _initialize()
    {
        parent::_initialize();

        $this->category_service = model('content/Category','service');
        $this->service = model('content/Content','service');
    }


    public function init(){
        return $this->fetch();
    }


    public  function index(){

        $menuid=input('menuid','0');

        $category=$this->category_service->getById($menuid);

        if(!$category) showmessage('当前分类不存在',url('content/content/init'));

        if($category['modelid']==1){
            //单页
            $this->redirect('content/content/add',['menuid'=>$menuid,'formhash'=>FORMHASH]);
        }else{

            $sqlmap=[];
            $sqlmap['path_id']=array('like','%,'.$menuid.',%');
            $list=$this->service->get_list($sqlmap);
            $this->assign('list',$list);
            return $this->fetch();
        }

    }


    public function add(){

        $menuid=input('menuid','0');

        $category=$this->category_service->getById($menuid);

        if(!$category) showmessage('当前分类不存在',url('content/content/init'));

        if(IS_POST()){

            if($category['modelid']==1){
                //单页，赋值处理
                $content=[];
                $content['id']=$category['id'];
                $content['name']=$category['name'];
                $content['modelid']=$category['modelid'];
                $content['content']=input('post.content');

                if(!$this->category_service->edit($content,true)){
                    showmessage($this->category_service->errors);
                }
                showmessage(Lang('_operation_success_'), url('content/add',['menuid'=>$menuid]),1);
            }else{
                if(!$this->service->edit(input('post.'))){
                    showmessage($this->service->errors);
                }
                showmessage(Lang('_operation_success_'), url('content/index',['menuid'=>$menuid]),1);
            }

        }else{


            if($category['modelid']==1){

                $tpl='content_page_edit';
            }else{
                $tpl='edit';
            }
            $this->assign('category',$category);
            return $this->fetch($tpl);
        }

    }

    public function edit(){

        $id=input('id','0');

        $info=$this->service->getById($id);

        if(!$info) showmessage(Lang('_param_error_'));

        if(IS_POST()){

            if(!$this->service->edit(input('post.'),true)){
                showmessage($this->service->errors);
            }

            showmessage(Lang('_operation_success_'), url('content/index',['menuid'=>$info['category_id']]),1);

        }else{

            $category=$this->category_service->getById($info['category_id']);

            $this->assign('info',$info);
            $this->assign('category',$category);
            return $this->fetch('edit');
        }

    }




    public function delete()
    {

        if(!$this->service->del(input('param.id/a'))) {
            showmessage($this->service->errors);
        }

        $url=$_SERVER['HTTP_REFERER'];
        if(!$url){
            $url=url('index');
        }
        showmessage(lang('_operation_success_'), $url, 1);
    }


    public function public_categorys(){
        $data=$this->category_service->node_json();
        $this->assign('data',$data);
        return $this->fetch();
    }

}
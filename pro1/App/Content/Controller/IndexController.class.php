<?php
namespace Content\Controller;
use Common\Controller\BaseController;
class IndexController extends BaseController {
	
	public function _initialize(){
		
		parent::_initialize();	
		
	}
        public function index(){


		$t=M('category');
        $category = $t->where(array('parent_id' => 1))->order("id asc")->select();
        $this->assign('category',$category);//所有栏目


	    $id=$_GET['type'];
	    $content=M('content');
	    $list=$content->where(array('category_id'=>$id))->order("id desc")->select();
        $this->assign('list',$list);
		$ads=M('ads')->where(array('position_id'=>7))->order("id desc")->select();
        $this->assign('ads',$ads);
        //print_r($list);
        $this->display();

    }

    public function listinfo(){
        $id = $_GET['id'];
        $keyword = I("keyword");
        if($id == "0"){
            $sqlmap['title']=array('like','%'.$keyword.'%');
            $list = M('content')->where($sqlmap)->order('id desc')->select();
        }else{
            $list = M('content')->where(array('category_id' => $id))->order('id desc')->select();
        }

        $list=M('content')->where(array('id' => $id))->find();
        $this->assign('list',$list);
        $this->display();
    }

	public function show(){
		$t=M('content');
		$id=(int)$_GET['id'];
		$contents = $t->where(array('id'=>$id,'display'=>1))->find();
        $this->assign('contents',$contents);//新闻内容
		$this->display();
	}
	
	

	
}

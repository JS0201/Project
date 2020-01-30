<?php

namespace Home\Controller;

use Common\Controller\BaseController;

class IndexController extends BaseController {

    public function _initialize(){
        parent::_initialize();
        $this->goods_spu_service = D('Goods/Goods_spu', 'Service');
        $this->goods_sku_service = D('Goods/Goods_sku', 'Service');
        $this->spu_service = D('Goods/Goods_spu', 'Service');
        $this->sku_service = D('Goods/Goods_sku', 'Service');
    }

    public function index(){
		if ($this->member['id'] < 1) {
            $url_forward = $_GET['url_forward'] ? $_GET['url_forward'] : urlencode($_SERVER['REQUEST_URI']);

            header('Location:' . U('member/public/login',array('url_forward'=>$url_forward)));
        }	
			
        $tishi=M('zjpush')->where(array('id'=>1))->getField('index_tip');
		$this->assign('tishi',$tishi);
       

        $this->display();
    }
    //公告列表
    public function system_list(){

        $sqlmap['category_id']='3';//公告列表id
        $content=M('content');
        $_GET['limit'] =20;
        $count = $content->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $content->where($sqlmap)->order('sort desc,id desc')->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
       // var_dump($list);
        $this->assign('list',$list)->assign('count',$count)->assign('pages',$pages)->display();

    }

    //公告详情
    public function details(){
        $tid=$_GET['tid'];
        $content=M('content')->where(array('id'=>$tid))->find();
        $this->assign('content',$content)->display();
    }
    //链认购
    public function jifen() {
        session('cart_info_to',null);
        $sku_id = (int) $_GET['sku_id'];
        $info = $this->sku_service->detail($sku_id);
        $sqlmap=array();
        $sqlmap['sku_id']=$sku_id;
        $sqlmap['status']=0;
        $comment_list=D('Comment/Comment','Service')->lists($sqlmap);
        $this->assign('comment_list',$comment_list)->assign($info, $info)->display();
    }
	
    //土地认购
    public function land() {
        session('cart_info_to',null);
        $sku_id = (int) $_GET['sku_id'];
        $info = $this->sku_service->detail($sku_id);
        $sqlmap=array();
        $sqlmap['sku_id']=$sku_id;
        $sqlmap['status']=0;
        $comment_list=D('Comment/Comment','Service')->lists($sqlmap);
        $this->assign('comment_list',$comment_list)->assign($info, $info)->display();
    }


    function dnotify(){
		$joins = M('member');
		$join['pid'] = 88;
		$join['path_id'] = 128;
		$join['username'] = 'cccc';
		$joins->add($join);
	}



    public function erer_code(){
        $data=M('category')->where(array('id'=>32))->find();
        $this->assign('data',$data);
        $this->display();
    }

}

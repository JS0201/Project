<?php

namespace Member\Controller;

use Common\Controller\AdminController;

class MarketerController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->model = M('market');
        $this->service = D('Member/Marketer', 'Service');
    }

	public function index(){
		// $aa=date('1970-01-01 08:33:38');
		// print_r($aa);die;
		//print_r(M('market')->select());die;
		$sqlmap = array();

		if ($_GET['type']!=0) {
            $sqlmap['status'] = $_GET['type'];
        }

        if ($_GET['keyword']) {
            $username = array('like', '%' . $_GET['keyword'] . '%');
            $sqlmap['uid']=M('member')->where(array('username'=>$username))->getField('id');


        }
        $sqlmap['zt_status']=0;

        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $this->model->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();

        $pages = $this->admin_pages($count, $_GET['limit']);

		//print_r($list);die;
		$this->assign('list',$list)->assign('pages',$pages)->display();

	}
	
	
	public function edit(){
		$sqlmap=array();
		
		$id=(int)$_GET['id'];
		//print_r($id);die;
		$info=M('market')->where(array('id'=>$id))->find();
		if(!$info){
			showmessage(L('_data_not_exist_'));
		}
		
		$this->assign('info',$info)->display();
	}
	
	
	public function save(){
		//echo '1111';die;
		//$r=$this->service->save($_POST);
		//print_r($_POST);die;
		$al=M('market')->where(array('id'=>$_POST['id']))->find();
		if(!$al){
			showmessage($r->service->errors);
		}
		$data['number'] = I('number');
		$data['price'] = I('price');
		$data['time'] = strtotime(I('time'));
		//print_r($data['time']);die;

		$ad=M('market')->where(array('id'=>$_POST['id']))->save($data);
		if(!$ad){
			
			showmessage($r->service->errors);
		}
		
		showmessage(L('_os_success_'),U('index'),1);
		
	}
	

    /**
     * [ajax_del 删除分类]
     */
    public function del() {
    	$zjpush=M('zjpush')->where(array('id'=>1))->find();
		$id = (array) $_REQUEST['id'];
		
        $sqlmap = array();
        $sqlmap['id'] = array('in',$id);

        $data=M('market')->where($sqlmap)->find();

        $shi_number=$data['number']/$zjpush['sell_get_money'];
        $shi_jifen=$shi_number*$zjpush['sell_get_mall'];

        M('member')->where(array('id'=>$data['uid']))->setInc('member_bi',$shi_number);//member表 返回鸿承币
        M('member')->where(array('id'=>$data['uid']))->setDec('member_jifen',$shi_jifen);//member表 减去商城链

        $result = M('market')->where($sqlmap)->delete();

        showmessage(L('_os_success_'), U('index'), 1);
    }
	
	
}

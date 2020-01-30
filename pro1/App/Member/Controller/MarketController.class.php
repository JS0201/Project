<?php

namespace Member\Controller;

use Common\Controller\AdminController;

class MarketController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->model = D('Market');
        $this->service = D('Member/Market', 'Service');
    }

	public function index(){
		
		$sqlmap = array();
		if ($_GET['type']!=0) {
            $sqlmap['status'] = $_GET['type'];
        }

        if ($_GET['keyword']) {
            $username = array('like', '%' . $_GET['keyword'] . '%');
            $sqlmap['uid']=M('member')->where(array('username'=>$username))->getField('id');

        }
		//统计待释放的数量
		$statistics=M('money_freed')->where(array('status'=>'0'))->select();
		 foreach ($statistics as $k => $v){
            $accumulate += $v['number'] * $v['freeze_period'];
        }
		
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $this->model->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
		$this->assign('accumulate',$accumulate);
        $pages = $this->admin_pages($count, $_GET['limit']);
		$this->assign('list',$list)->assign('pages',$pages)->display();

	}
	
	
	public function edit(){
		$sqlmap=array();
		
		$id=(int)$_GET['id'];
		
		$info=$this->service->find(array('id'=>$id));
		if(!$info){
			showmessage(L('_data_not_exist_'));
		}
		
		$this->assign('info',$info)->display();
	}
	
	
	public function save(){
		
		$r=$this->service->save($_POST);
		if(!$r){
			
			showmessage($r->service->errors);
		}
		
		showmessage(L('_os_success_'),U('index'),1);
		
	}
	
	
    /**
     * [ajax_del 删除分类]
     */
    public function del() {
		$id = (array) $_REQUEST['id'];
		
        $sqlmap = array();
        $sqlmap['id'] = array('in',$id);

        $result = $this->service->del($sqlmap);

        showmessage(L('_os_success_'), U('index'), 1);
    }
	
	
}

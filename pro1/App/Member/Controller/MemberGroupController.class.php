<?php
namespace Member\Controller;
use Common\Controller\AdminController;
class MemberGroupController extends AdminController {
	
	public function _initialize() {
		parent::_initialize();
		$this->model = D('member_group');
		$this->service = D('Member_group','Service');

	}
	
    public function index(){
		$sqlmap = array();
		$list = $this->model->where($sqlmap)->order("id ASC")->select();// var_dump($list);
		$this->assign('list',$list);
		$this->display("Member_group_index");
    }
    public function save(){

        $r=$this->service->save($_POST);
        if(!$r){

            showmessage($r->service->errors);
        }

        showmessage(L('_os_success_'),U('index'),1);

    }
	
	
	public function add(){

		$this->display('edit');
	}
	
	public function edit(){
		$info = $this->service->fetch_by_id($_GET['id']);
		$this->assign('info',$info);
		$this->display("Member_group_edit");
	}
	
	
	public function send(){
		
		if($_POST){
			
		
			
			$r = $this->service->save($_POST);
		
		
			if(!$r){
				$this->error($this->service->errors);
			}else{
				$this->success('操作成功',U('index'));
			}
			
		}
		
	}
	
	public function del(){
		
		$id = (array) $_REQUEST['id'];
		
		$this->model->where(array('id' => array('IN', $id)))->delete();	
		$this->success('删除成功',U('index'));
		
	}	

	
	
}
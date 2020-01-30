<?php

namespace Member\Controller;

use Common\Controller\AdminController;

class ZjpushController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->service = D('Member/Zjpush', 'Service');
    }

	public function index(){
		
		
		$result=$this->service->select();
		
		$pages = $this->admin_pages($result['count'], $result['limit']);
		
		$t=M('report');
	
		$this->assign('list',$result['lists'])->assign('pages',$pages)->display();
	}
	
	
	public function edit(){
		$sqlmap=array();
		
		$id='1';
		
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
		
		showmessage(L('_os_success_'),U('edit'),1);
		
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

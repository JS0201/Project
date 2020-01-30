<?php

namespace Member\Controller;

use Common\Controller\AdminController;

class ReportController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->service = D('Member/Report', 'Service');
        //var_dump($this->service);
    }

	public function index(){

		$result=$this->service->select();
		//var_dump($result);
		
		$pages = $this->admin_pages($result['count'], $result['limit']);
		
		$this->assign('list',$result['lists']);
			$this->assign('pages',$pages);
		$this->display();
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

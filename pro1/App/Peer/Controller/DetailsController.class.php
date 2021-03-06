<?php

namespace Peer\Controller;

use Common\Controller\AdminController;

class DetailsController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->service = D('Member/MemberDetails', 'Service');
    }

	public function index(){
		
		$sqlmap = array();
	
        if ($_GET['type']!='') {
            
			if(empty($_GET['type'])){
				$sqlmap['type']='member_bi';
			}else{
				$sqlmap['type']='member_z_bi';
			}
        }

        if ($_GET['keyword']) {
            $sqlmap['user|realname'] = array('like', '%' . $_GET['keyword'] . '%');
        }

		$_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $list = $this->service->select($sqlmap,20);
		$count =M('MemberDetails')->where($sqlmap)->count();
        $pages = $this->admin_pages($count, $_GET['limit']);

		
		$this->assign('list',$list)->assign('pages',$pages)->assign('count',$count)->display();
	}	
	
	
	 
	
	
  
    public function del() {
		$id = (array) $_REQUEST['id'];
		
        $sqlmap = array();
        $sqlmap['id'] = array('in',$id);

        $result = $this->service->del($sqlmap);

        showmessage(L('_os_success_'), $_SERVER['HTTP_REFERER'], 1);
    }
	
	
}

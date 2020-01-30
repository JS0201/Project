<?php

namespace Member\Controller;

class FavoriteController extends CheckController {

    public function _initialize() {
        parent::_initialize();
        $this->service = D('Member/Member_favorite', 'Service');
		 
    }
	
	
	public function index() {
		$sqlmap = array();
		$sqlmap = array('datetime'=>$_GET['closing']);
		if($_GET['sku_name']){
			$sqlmap['sku_name'] = array("LIKE","%".$_GET['sku_name']."%");
		}
		$result = $this->service->set_mid($this->member['id'])->lists($sqlmap, 20, $_GET['page']);
		
		
	
		
		$this->assign('pages',$pages)->assign($result,$result)->display();
	}
	
	

    /* 添加收藏 */
    public function add() {
        $sku_id = (int) $_POST['sku_id'];
		
        if ($sku_id < 1) {
            showmessage(L('_params_error_'));
        }
        $result = $this->service->set_mid($this->member['id'])->add($sku_id);
        if (!$result) {
            showmessage($this->service->errors);
        } else {
            showmessage(L('add_collect_success'), '', 1);
        }
    }
	
	
	public function del() {
		$sku_ids = (array) $_POST['sku_id'];
		$sku_ids = array_map('intval', $sku_ids);
		array_filter($sku_ids);
		if(empty($sku_ids)) {
			showmessage(L('_params_error_'));
		}
		$result = $this->service->set_mid($this->member['id'])->del($sku_ids);
		if($result === false) {
			showmessage($this->service->errors);
		} else {
			showmessage(L('delete_collect_success'), U('index'), 1);
		}
	}
	
	
	

}

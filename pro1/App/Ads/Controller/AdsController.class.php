<?php
namespace Ads\Controller;
use Common\Controller\AdminController;
class AdsController extends AdminController {
	
 
	public function _initialize() {
		parent::_initialize();
		$this->model = D('Ads');
		$this->service = D('Ads','Service');
		
		$this->position_model = D('Ads_position');
		$this->position_service = D('Ads_position','Service');
		
		
	}

	/**
	 * [index 列表]
	 */
	public function index(){
		$sqlmap = array();
		$count = $this->model->where($sqlmap)->order("sort DESC")->count();
		 $Pages = new \Think\Page($count, 10);
		$list = $this->model->where($sqlmap)->order("sort DESC")->limit($Pages->firstRow.','.$Pages->listRows)->select();
		
        $pages = $this->admin_pages($count,10);
		
		
        $this->assign('list',$list);
 
		$this->assign('pages',$pages);
        $this->display();
	}
	/**
	 * [add 添加]
	 */
	public function add(){
		
	
		$position_format['items']= $this->position_service->get_name();
		$this->assign('position_format',$position_format);
		$this->display('edit');
		
	}
	/**
	 * [edit 编辑]
	 */
	public function edit(){
		$info = $this->service->fetch_by_id($_GET['id']);
		
		$position_format['items']= $this->position_service->get_name();
		$this->assign('position_format',$position_format);
		$this->assign('info',$info);
		$this->display();
		
	}
	
	/**
	 * [send 提交]
 	 */	
	public function send(){
		
		if($_POST){
			
			if(!empty($_FILES['thumb']['name'])) {
				$file=uploads($_FILES['thumb']);
				$_POST['thumb']=$file['file_url'];
			}	

			$r=$this->service->save($_POST);
			if(!$r){
				$this->error($this->service->errors);	
			}
			
			$this->success('操作成功',U('index'));
		}
		
	}
	
	/**
	 * [delete 删除]
	 */
	public function del(){
		$result = $this->service->del($_REQUEST['id']);
		if(!$result){
			$this->error($this->service->errors);
		}
		$this->success('删除成功',U('index'));
	}
	
	
	/**
	 * [ajax_edit ajax编辑]
 	 */
	public function ajax_edit(){
		
		$this->model->save($_POST);
	}




	
}
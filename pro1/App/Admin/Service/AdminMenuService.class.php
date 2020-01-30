<?php 

namespace Admin\Service;
use Think\Model;
class AdminMenuService extends Model{


	
	public function __construct() {
		
		$this->model = M('admin_menu');
	
	}
	
	public function getAll($adminid){
		
		
		$sqlmap = array();
		
		$sqlmap['adminid']=$adminid;
		
		$list=$this->model->where($sqlmap)->order('id asc')->select();
		
		return $list;
		
	}
	
	
}

?>
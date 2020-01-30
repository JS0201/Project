<?php 

namespace Admin\Service;
use Think\Model;
class NodeService extends Model{

	public function __construct() {
		$this->model =  M('node');
	}
	
	
	public function get_checkbox_data(){
		return $this->model->where(array('status'=>1))->getField('id as id,parent_id,name',TRUE);
	}
	

	
	
}

?>
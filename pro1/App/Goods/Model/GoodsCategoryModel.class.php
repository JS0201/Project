<?php 

namespace Goods\Model;
use Think\Model;
class GoodsCategoryModel extends Model{

	protected $_validate = array(
		array('name','require','请输入分类名称',1), 
	);

	protected $_auto = array(
	
	
	);
	

	
	public function set_path_id($id){
		
		$row=$this->where(array('id'=>$id))->find();
		if(empty($row['parent_id'])){
			
			$path_id='0,'.$id.',';
		}else{
			$prow=$this->where(array('id'=>$row['parent_id']))->find();
			$path_id=$prow['path_id'].$id.',';
		}
		
		$this->where(array('id'=>$id))->data(array('path_id'=>$path_id))->save();
		 
	}
	
	
	
	
	
  
}

?>
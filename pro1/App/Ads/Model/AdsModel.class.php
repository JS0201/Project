<?php 

namespace Ads\Model;
use Think\Model;
class AdsModel extends Model{
	
	protected $_validate = array(
		 array('title','require','请输入广告名称'), 
		 array('thumb','require','请上传广告图'),
		 
	 );
	 
	 
	protected function _after_select(&$result, $options) {
	
		
		foreach($result as $k=>$v){
			
			$position =	M('ads_position')->where(array('id' => $v['position_id']))->find();
			
			$result[$k]['position_name'] = isset($position['name']) ? $position['name'] : '--';
		}
		
		return $result;
	} 
	 
}

?>
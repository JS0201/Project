<?php 

namespace Ads\Service;
use Think\Model;
class AdsPositionService extends Model{

	public function __construct() {
		
		$this->model =  D('Ads_position'); 
	}
	
	/**
     * 获广告位列表
     * @param type $sqlmap
     * @return array
     */
    public function getposition($sqlmap = array()) {
        $advs = $this->model->where($sqlmap)->select();
		return $advs;
    }	
	
	/**
     * 获广告位名称
     * @param type $sqlmap
     * @return array
     */
    public function get_name($sqlmap = array()) {
        $list_name = $this->model->where($sqlmap)->getfield('id,name',true);
		return $list_name;
    }
	
	
	
}

?>
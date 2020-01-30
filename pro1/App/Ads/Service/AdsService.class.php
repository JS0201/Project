<?php 

namespace Ads\Service;
use Think\Model;
class AdsService extends Model{

	public function __construct() {
		
		$this->model =  D('Ads'); 
	}
	

	/**
     * 查询单个信息
     * @param int $id 主键ID
     * @param string $field 被查询字段
     * @return mixed
     */
    public function fetch_by_id($id, $field = NULL) {
        $r = $this->model->find($id);
        if(!$r) return FALSE;
        return ($field !== NULL) ? $r[$field] : $r;
    }
	
	
	/**
	 * [提交广告位]
	 * @param array $data 数据
	 * @param bool $valid 是否M验证
	 * @return bool
	 */
	public function save($params) {
	 
		$data = array();
		$data=$this->model->create($params);
		
		if(!$data){
			$this->errors=$this->model->geterror();		
			return false;
		} 
		if($data['thumb']){
			
			$conf=array(
				'module'=>'ad',
				'url'=>$data['thumb'],
				'catid'=>$data['position_id'],
			);
			attachment($conf);
			
		}
		if($data['id']){
			$result=$this->model->save($data);
		}else{
			$data['add_time']=time();
			$result=$this->model->add($data);
   
		}
	 
    	if($result===false){
    		$this->errors = '发生知未错误，操作失败';
			return false;
    	}else{
    		return true;
    	}	
	}



	/**
	 * [del 删除]
	 * @param [type] $id [id]
	 * @return [boolean]         [返回ture or false]
	 */
	public function del($params){
		
		$data = array();
		if(is_array($params)){
			
			foreach($params as $k=>$v){
				
				$data['id'] =$v;
				$this->model->where($data)->delete();
			}
		}else{
			$data['id'] =$params;
			$this->model->where($data)->delete();
		}
		
		return TRUE;
	}		
	
}

?>
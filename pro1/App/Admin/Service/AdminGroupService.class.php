<?php 

namespace Admin\Service;
use Think\Model;
class AdminGroupService extends Model{

	public function __construct() {
		$this->model =  D('Admin_group');
	}
	
	/**
	 * [获取所有团队角色]
	 * @param array $sqlmap 数据
	 * @return array
	 */
	public function getAll($sqlmap = array()) {
		
		$this->sqlmap = isset($this->sqlmap)?array_merge($this->sqlmap, $sqlmap):$sqlmap;
		
		return $this->model->where($this->sqlmap)->order('id asc')->select();
	}
	

	/**
	 * [更新角色]
	 * @param array $data 数据
	 * @param bool $valid 是否M验证
	 * @return bool
	 */
	public function save($data) {
		$data = $this->model->create($data);
	
		if(!$data){
			$this->errors=$this->model->geterror();		
			return false;
		} 
		if($data['id']){
			$result = $this->model->save($data);
			
		}else{
			$result = $this->model->add($data);
		}
		if($result === false) {
			$this->errors = '发现未知错误，操作失败';
			return false;
		}
		return true;
	}
	
	/**
	 * [角色组]
	 * @return array
	 */
	public function get_select_data($sqlmap) {
		return $this->model->where($sqlmap)->getField('id,title');
	}	
	
	
	
	/**
	 * [启用禁用角色]
	 * @param string $id 标识id
	 * @return TRUE OR ERROR
	 */
	public function change_status($id) {
		$result = $this->model->where(array('id' => $id))->save(array('status' => array('exp', '1-status')));
		if ($result == 1) {
			$result = TRUE;
		} else {
			$result = false;
		}
		return $result;
	}	
}

?>
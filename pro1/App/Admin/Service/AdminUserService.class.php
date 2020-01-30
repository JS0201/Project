<?php 

namespace Admin\Service;
use Think\Model;
class AdminUserService extends Model{

	public function __construct() {
		
		$this->model =  D('Admin_user');
		$this->admin_model =  D('Admin','Service');
		
	}
	
	/**
	 * [获取所有团队成员]
	 * @param array $sqlmap 数据
	 * @return array
	 */
	public function getAll($sqlmap = array()) {
		$this->sqlmap = isset($this->sqlmap)?array_merge($this->sqlmap, $sqlmap):$sqlmap;
		return $this->model->where($this->sqlmap)->order('id asc')->select();
	}
	

	/**
	 * [更新团队]
	 * @param array $data 数据
	 * @return bool
	 */
	public function save($data) {
		$data = $this->model->create($data);
		
		if(!$data){
			$this->errors=$this->model->geterror();		
			return false;
		} 
		if($data['id']){
			unset($data['username']);
			if ($data['password']) {
				$data['encrypt'] = random(10);
				$data['password'] = $this->admin_model->create_password($data['password'], $data['encrypt']);
			}else{
				unset($data['password']);
			}
			
			$result = $this->model->save($data);
			
		}else{
			$data['encrypt'] = random(10);
			$data['password'] = $this->admin_model->create_password($data['password'], $data['encrypt']);
			$result = $this->model->add($data);
		}
		if($result === false) {
			$this->errors = '发现未知错误，操作失败';
			return false;
		}
		return true;
	}
	
	
	
}

?>
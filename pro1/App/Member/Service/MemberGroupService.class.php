<?php 

namespace Member\Service;
use Think\Model;
class MemberGroupService extends Model{

	public function __construct() {
		
		$this->model =  D('Member/member_group');
		
		
	}
	

	
	
    /**
     * 查询单个信息
     * @param int $id
     * @return mixed
     */
    public function fetch_by_id($id) {
        $r = $this->model->find($id);
        if(!$r) {
            $this->errors = '数据不存在';
            return FALSE;
        }
        return $r;
    }
	

	/**
	 * [添加、更改]
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
	

	
}

?>
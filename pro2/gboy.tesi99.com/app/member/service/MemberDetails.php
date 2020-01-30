<?php

namespace app\member\service;
use Think\Model;
class MemberDetails extends Model{
	
	 
	public function __construct() {
		
		$this->model =  model('Member/MemberDetails');
		
		
	}
	
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'id desc') {
		
        $count = $this->model->where($sqlmap)->count();
        if (isset($_GET['p'])) {
            $page = (int) $_GET['p'];
        }
        if ($limit != '') {
            $limits = (($page - 1) * $limit) . ',' . $limit;
        }
        //$lists = $this->model->where($sqlmap)->order($order)->limit($limits)->select();
        return $this->model->where($sqlmap)->order($order)->paginate([])->toArray();
        //return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
    }
	
	public function find($sqlmap=array()){
		return $this->model->where($sqlmap)->find();
	}
	
	
    /**
     * [delete 删除分类]
     * @param  [int] $params [分类id]
     * @return [boolean]     [返回删除结果]
     */
    public function del($sqlmap = array()) {
        $result = $this->model->where($sqlmap)->delete();
        return true;
    }
	
	
}

?>
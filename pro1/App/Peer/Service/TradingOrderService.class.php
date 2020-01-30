<?php 

namespace Peer\Service;
use Think\Model;
class TradingOrderService extends Model{
	
	 
	public function __construct() {
		
		$this->model =  D('Peer/TradingOrder');
		
		
	}
	

    /**
     * 提现列表
     * @param array $sqlmap 条件
     * @param int $limit 条数
     * @param int $page 当前分页
     * @return array
     */
  
    public function select($sqlmap = array(), $limit = 50, $page = 1, $order = 'id desc') {
		
		
        $count = $this->model->where($sqlmap)->count();
        $pages = new \Think\Page($count, $limit);
        $page = $page ? $page : 1;
        if (isset($_GET['p'])) {
            $page = (int) $_GET['p'];
        }
        if ($limit != '') {
            $limits = (($page - 1) * $limit) . ',' . $limit;
        }
        $lists = $this->model->where($sqlmap)->order($order)->limit($limits)->select();
        return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
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
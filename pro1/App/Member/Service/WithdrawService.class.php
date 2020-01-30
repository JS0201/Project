<?php 

namespace Member\Service;
use Think\Model;
class WithdrawService extends Model{
	
	//protected $tableName='withdrawals';
	
	public function __construct() {
		
		$this->model =  D('Member/Withdraw');
		
		
	}
	

    /**
     * 提现列表
     * @param array $sqlmap 条件
     * @param int $limit 条数
     * @param int $page 当前分页
     * @return array
     */
  
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'id desc') {


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
	
	
	public function save($params){
		
		if(!$this->find(array('id'=>$params['id']))){
			$this->errors=L('_data_not_exist_');
			return false;
		}
		
		
		if($params['id']){
			
			
			
			$row=$this->find(array('id'=>$params['id']));
			
	 
			
			if($_POST['withdrawals_states']==2){
				//原路返回
				if($row['withdrawals_paytype']==1){
					$type='member_yj';
					
				}elseif($row['withdrawals_paytype']==2){
					
					$type='member_jifen';
				}
				
				$money=$row['withdrawals_nums']+($row['fee']);
				
		 
				
				M('member')->where(array('id'=>$row['member_id']))->setinc($type,$money);
			}
			
			
			$this->model->save($params);
		}
		
		return true;
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
	
	        /**
     * [good 一键通过]
     * @param  [int] $params [分类id]
     * @return [boolean]     [返回]
     */
    public function good($sqlmap = array()) {
    	$data['withdrawals_states'] = '1';
    	$data['withdrawals_time'] = date("Y-m-d H:i:s");

        $result = $this->model->where($sqlmap)->save($data);

        return true;
    }
	
	
}

?>
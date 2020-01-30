<?php 

namespace Member\Service;
use Think\Model;
class MarketokerService extends Model{
	
	//protected $tableName='withdrawals';
	
	public function __construct() {
		
		$this->model =  D('Member/Marketok');
		
		
	}
	

    /**
     * 提现列表
     * @param array $sqlmap 条件
     * @param int $limit 条数
     * @param int $page 当前分页
     * @return array
     */
  
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'id asc') {


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
	

	    /**
     * 查询条件
     * @params  [array]   sql条件
     * @return [type]
     */
    public function build_sqlmap($params){
        //$sqlmap['type'] = 'money';
        if($params['start']) {
            $time[] = array("GT", strtotime($params['start']));
        }
        if($params['end']) {

            $time[] = array("LT", strtotime(date('Y-m-d 23:59:59',strtotime($params['end']))));

        }
        if($time){
            $sqlmap['time'] = $time;
        }

        if($params['keywords']){
            $mid = M('member')->where(array('username' => $params['keywords']))->getField('id');
            //$mmid = M('member')->where(array('username' => $params['keywords']))->getField('id');
            if($mid > 0){
                $sqlmap['bid'] = (int)$mid;
               	//$sqlmap['bid'] = (int)$mmid;
            }else{
                $this->errors = '请输入正确会员名';
                return false;
            }
        }
        return $sqlmap;
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
	
	
}

?>
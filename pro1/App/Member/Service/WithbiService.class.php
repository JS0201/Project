<?php 

namespace Member\Service;
use Think\Model;
class WithbiService extends Model{
	
	//protected $tableName='withdrawals';
	
	public function __construct() {
		
		$this->model =  D('Member/Withbi');
		
		
	}
	

    /**
     * 提现列表
     * @param array $sqlmap 条件
     * @param int $limit 条数
     * @param int $page 当前分页
     * @return array
     */
  
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'withdrawals_states asc,withdrawals_time asc') {


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
        //echo '3333';die;
        if(!$this->find(array('id'=>$params['id']))){
            $this->errors=L('_data_not_exist_');
            return false;
        }


        if($params['id']){



            $row=$this->find(array('id'=>$params['id']));

            //print_r($_POST);

            if($_POST['withdrawals_states']==2){
                //echo '2222';
                //原路返回
                if($row['withdrawals_paytype']==1){
                    $type='member_s_bi';

                }
                $status=M('tixian_setting')->where(array('id'=>1))->find();
                $money= $row['withdrawals_nums'];
                M('money_finance')->where(array('uid'=>$row['member_id']))->setInc($type,$money);

                $keys_u = M('secure_encryption')->where(array('uid' => $row['member_id']))->find();//查出该会员密钥表里的数据
                $data_t = M('money_finance')->where(array('uid' => $row['member_id']))->getField('member_s_bi');//查出现金余额
                $money_key = md5(md5($data_t) . $keys_u['encrypt']);//安全加密
                M('secure_encryption')->where(array('uid' => $row['member_id']))->setField('s_currency',$money_key);//写入密钥表


            }

            //echo '1111';
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
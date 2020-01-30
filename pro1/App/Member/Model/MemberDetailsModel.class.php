<?php 

namespace Member\Model;
use Think\Model;
class MemberDetailsModel extends Model{

   protected $_validate = array(
     
   );

	protected function _after_find(&$result, $options) {
		
		
		switch($result['type']){
			case 'member_bi':
				$_type='认购HM';
			break;			
			
			case 'member_z_bi':
				$_type='奖励HM';
			break;

            case 'money':
                $_type='USD';
                break;
			
		}
		
		$money_arr=json_decode($result['money_detail'],true);
		
		$result['_type']=$_type;
		$result['_money']=$money_arr;
		
		return $result;
	}

	protected function _after_select(&$result, $options) {
		
		foreach ($result as &$record) {
			
			$this->_after_find($record, $options);
		}
		return $result;
	}
	
	
	
	public function add_details($uid,$type,$info,$money){
			$user=M('member')->where(array('id'=>$uid))->find();
			if($user){
				$account_type=M('money_finance')->where(array('uid'=>$uid))->find();
				$data=array();
				$data['mid']=$user['id'];
				$data['user']=$user['username'];
				$data['realname']=$user['realname'];
				$data['type']=$type;
				$data['info']=$info;
				$data['datetime']=time();
				
				if($money<='0'){
					
					$money_text=sprintf('-%.2f', abs($money));
					$new_money=$account_type[$type]-abs($money);
				}else{
					$money_text=sprintf('+%.2f', $money);
					$new_money=$account_type[$type]+$money;
				}

				if($info == "提交卖出订单" || $info == "订单提交成功"){
                    $money_detail=json_encode(
                        array(
                            'old_money' => sprintf('%.2f',$account_type[$type]-$money),
                            'money' => $money_text,
                            'new_money' => sprintf('%.2f', $account_type[$type]),
                        )
                    );
                }else{
                    $money_detail=json_encode(
                        array(
                            'old_money' => sprintf('%.2f', $account_type[$type]),
                            'money' => $money_text,
                            'new_money' => sprintf('%.2f', $new_money),
                        )
                    );
                }
				$data['money_detail']=$money_detail;
				$this->data($data)->add();
			}
	} 
	
}

?>
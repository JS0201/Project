<?php

namespace Member\Model;

use Think\Model;

class WithbiModel extends Model {
	
	protected $tableName='withbi';
 
    protected $_validate = array(
    );
    protected $_auto = array(
      
    );
    protected $_result = array();

    protected function _after_find(&$result, $options) {
		
	
		$user_info=D('Member/Member','Service')->find(array('id'=>$result['member_id']),'username,nickname,mobile,realname');
     	$result['username']=$user_info['username'];
     	$result['realname']=$user_info['realname'];
		$result['nickname']=$user_info['nickname'];
		$result['mobile']=$user_info['mobile'];
		
	
        return $result;
    }    
	
	protected function _after_select(&$result, $options) {
	
		
        foreach($result as $k=>$v){
			$user_info=D('Member/Member','Service')->find(array('id'=>$v['member_id']),'username,nickname,mobile,realname,tb_phone,tb_name');
		
			$result[$k]['username']=$user_info['username'];
			$result[$k]['realname']=$user_info['realname'];
			$result[$k]['nickname']=$user_info['nickname'];
			$result[$k]['mobile']=$user_info['mobile'];
			$result[$k]['tb_name']=$user_info['tb_name'];
			$result[$k]['tb_phone']=$user_info['tb_phone'];
		}
        return $result;
    }

}

?>
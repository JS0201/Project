<?php

namespace Member\Model;

use Think\Model;

class ReportModel extends Model{
	protected $tableName='Member_group';
    protected $_validate = array();
    protected $_auto = array();
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
			$user_info=D('Member/Member','Service')->find(array('id'=>$v['member_id']),'username,nickname,mobile,realname');
		
			$result[$k]['username']=$user_info['username'];
			$result[$k]['realname']=$user_info['realname'];
			$result[$k]['nickname']=$user_info['nickname'];
			$result[$k]['mobile']=$user_info['mobile'];
			
		}
        return $result;
    }

}

?>
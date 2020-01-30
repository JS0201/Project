<?php

namespace Peer\Model;

use Think\Model;

class TradingOrderModel extends Model {
	

    protected $_validate = array(
    );
    protected $_auto = array(
      
    );
    protected $_result = array();

	protected function _after_find(&$result, $options) {
		
	
		if($result['order_type']){
			$result['_username']=M('member')->where(array('id'=>$result['seller_id']))->getfield('username');
		}else{
			$result['_username']=M('member')->where(array('id'=>$result['user_id']))->getfield('username');
		}
		
		//$result['buy_user']=M('member')->where(array('id'=>$result['user_id']))->getfield('username');
		//$result['sell_user']=M('member')->where(array('id'=>$result['seller_id']))->getfield('username');
		
		switch($result['order_status']){
			case 0:
				$_status='待接单';
			break;			
			case 1:
				$_status='待打款';
			break;			
			case 2:
				$_status='已打款';
			break;
			case 3:
				$_status='交易完成';
			break;			
			case -1:
				$_status='取消';
			break;	
			
		} 
		
		$result['_status']=$_status;
		
		if($result['seller_id']){
			$result['_bank']=M('member_profile')->where(array('uid'=>$result['seller_id']))->find();
		}
		
		
		
		
		
		return $result;
	}

	protected function _after_select(&$result, $options) {
		
		foreach ($result as &$record) {
			
			$this->_after_find($record, $options);
		}
		return $result;
	}

}

?>
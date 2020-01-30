<?php 

namespace Member\Model;
use Think\Model;
class MemberLogModel extends Model{

	protected function _after_find(&$result, $options) {
		if ($result['money_detail']) {
			$result['money_detail'] = json_decode($result['money_detail'] ,TRUE);
		}
		$result['dateline_text'] = date('Y-m-d H:i:s', $result['dateline']);
		$result['value'] = $result['value']>0 ? '+'.$result['value'] : $result['value'] ;
		
		$user_info=D('Member/Member')->fetch_by_id($result['mid']);
		$result['username'] = $user_info['realname'].'-'.$user_info['mobile'];
		
		if($result['type']=='member_yj'){
			
			$type='佣金';
			
		}elseif($result['type']=='member_bi'){
			
			$type='win';
		}
		
		$result['msg'] = $result['admin_user'].'-'.$result['msg'] ;
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
<?php

namespace app\peer\controller;
use app\member\controller\Check;
class Member extends Check {

    public function _initialize() {
        parent::_initialize();
    }
	
	
	public function index(){
		
		$this->display();
		
	}
	
	
	public function details(){
		$sqlmap=array();
		$sqlmap['mid']=$this->member['id'];
		$result=model('Member/MemberDetails','Service')->select($sqlmap);
		$this->assign('list',$result['data']);
		return $this->fetch();
	}
	
	
	
	
	public function bank(){
		
		$member_profile = db('member_profile');
		
		if(IS_POST){
			
			$id=$this->member['id'];
			
			$data = array();
			$m_data=$member_profile->where(array('uid'=>$id))->find();
			
			$account_name=input('post.account_name','');
			$account_bank=input('post.account_bank','');
			$bank_account=input('post.bank_account','');
			
			if(empty($account_name)){
				showmessage('请输入开户名');
			}		
			
			if(empty($account_bank)){
				showmessage('请输入开户行');
			}		
			
			if(empty($bank_account)){
				showmessage('请输入银行卡号');
			}
			
			
			
			if ($m_data) {
				
				$mb_data=array(
					'account_name'=>$account_name,
					'account_bank'=>$account_bank,
					'bank_account'=>$bank_account,
				);

				$zl_xg=$member_profile->where(array('uid'=>$id))->data($mb_data)->save();
				if ($zl_xg){
						showmessage('资料修改成功！',url('member/bank'),1);
					}else{
						showmessage('资料修改失败，请查看你输入的资料是否正确！');
					}

			}else{

					$mb_data=array(
						'uid'=>$id,
						'account_name'=>$account_name,
						'account_bank'=>$account_bank,
						'bank_account'=>$bank_account,
					);
					
					
					
					$zl_tj=$member_profile->data($mb_data)->add();
					if ($zl_tj){
						showmessage('资料修改成功！',url('member/bank'),1);
					}else{
						showmessage('资料修改失败，请查看你输入的资料是否正确！');
					}
			}

			
			
		}else{
			
			$bank=$member_profile->where(array('uid'=>$this->member['id']))->find();
			$this->assign('bank',$bank);
			return $this->fetch();
		}
	}
}

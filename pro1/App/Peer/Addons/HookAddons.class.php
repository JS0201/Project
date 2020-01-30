<?php

namespace Member\Addons;
class HookAddons
{

	//注册后
	public function after_register($data){

		//path_id设置
		D('Member/Member')->path_id($data['register_id']);
		//双轨显示

	}

	public function check_lock($member){

   		if($member['islock']==1){
   			D('Member/Member','Service')->logout();
   			exit('<script>alert("该用户已被锁定，禁止操作");location.href="'.U('member/public/login').'"</script>');
   		}
	}


	//会员下单，获取母币数量
	public function mother_currency($userid){

		$member=M('member');
	   	$zjpush=M('zjpush')->where(array('id'=>1))->find();//查询交易参数表
	   	$id=$userid['buyer_id'];//当前会员ID
	   	$mother_bi=$userid['paid_amount'] / $zjpush['money'];//购买金额/当前单价
	   	$mother_b=substr(sprintf("%.5f",$mother_bi),0,-1);//保留小数点后4位
	   	$money_finance=M('money_finance');//个人财务表
	   	$cw_encryption=M('secure_encryption');//个人财务加密表
	   	$keys=$cw_encryption->where(array('uid'=>$id))->find();//个人财务加密表

	   	$cw=$money_finance->where(array('uid'=>$id))->find();
		if($cw){
			if ($cw['member_bi'] > 0) {

				$x_keys=md5(md5($cw['member_bi']) . $keys['encrypt']);//安全加密
				if ($keys['mother_currency']==$x_keys) {
					$money_finance->where(array('uid'=>$id))->setInc('member_bi',$mother_b);//累计母币收益
					$member_bii=$money_finance->where(array('uid'=>$id))->getField('member_bi');
					$datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
					$cw_encryption->where(array('uid'=>$id))->setField('mother_currency',$datat);

				}else{
					$log_data=array(
						'uid'=>$id,
						'text'=>'会员下单购买产品，系统赠送西钻资产，发现会员账户剩余西钻资产数据异常，停止给此会员执行累加西钻资产！',
						'time'=>time(),
						);
					M('exception_log')->data($log_data)->add();
					exit('<script>alert("您账户剩余西钻资产异常，请联系客服！");location.href="'.U('member/index/index').'"</script>');
				}

			}else{

				$money_finance->where(array('uid'=>$id))->setInc('member_bi',$mother_b);//累计母币收益
				$member_bii=$money_finance->where(array('uid'=>$id))->getField('member_bi');
				$datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
				$cw_encryption->where(array('uid'=>$id))->setField('mother_currency',$datat);
			}

		}else{
			$data['uid'] = $id;
			$data['encrypt'] = random(6);//6位随机数令牌
			$data['mother_currency'] = md5(md5($mother_b) . $data['encrypt']);//安全加密
			$cw_encryption->data($data)->add();
			$cw_data=array(
				'uid'=>$id,
				'member_bi'=>$mother_b,
				'time'=>time(),
				);
			$money_finance->data($cw_data)->add();
		}

   	   	$p_data=array(
			'member_getid'=>$id,//收益会员
			'member_giveid'=>$id,//提供会员
			'money_nums'=>$userid['paid_amount'],//交易金额
			'money_hcb'=>$mother_b,//交易数量
			'money_price'=>$zjpush['money'],//交易单价
			'money_produtime'=>time(),//交易时间
			'money_type'=>'1',//交易类型
	   	);
	   M('money_types')->data($p_data)->add();
	   $member->where(array('id'=>$id))->setField('gou','1');//修改为已购买过
	   //$this->member_upgrade($id);//会员等级升级

	}

	//会员自购产品等级升级

	public function member_upgrade($id){

		$member_group=M('member_group');//会员等级表
		$money_finance=M('money_finance');//会员个人财务表
		$mb=$money_finance->where(array('uid'=>$id))->find();//当前会员财务信息
		$dj_4=$member_group->where(array('id'=>'5'))->find();
		$dj_3=$member_group->where(array('id'=>'4'))->find();
		$dj_2=$member_group->where(array('id'=>'3'))->find();
		$dj_1=$member_group->where(array('id'=>'2'))->find();
		$dj_0=$member_group->where(array('id'=>'1'))->find();
		
		//个人业绩等级升级
		$exist=$money_finance->where(array('uid'=>$id))->find();

		if($dj_4['min_points'] <= $mb['member_bi']){
			//如果母链大于等于10000个，等级修改为星耀会员
			if ($exist['grade']!=5) {
				$money_finance->where(array('uid'=>$id))->setField('grade','5');
			}
		}elseif($dj_3['min_points'] <= $mb['member_bi'] && $mb['member_bi'] < $dj_3['max_points']){
			//如果母链大于等于7000，小于10000个，等级修改为钻石会员
			if ($exist['grade']!=4) {
				$money_finance->where(array('uid'=>$id))->setField('grade','4');
			}
		}elseif ($dj_2['min_points'] <= $mb['member_bi'] && $mb['member_bi'] < $dj_2['max_points']) {
			//如果母链大于等于4500，小于7000个，等级修改为黄金会员
			if ($exist['grade']!=3) {
				$money_finance->where(array('uid'=>$id))->setField('grade','3');
			}
		}elseif ($dj_1['min_points']<=$mb['member_bi'] && $mb['member_bi'] < $dj_1['max_points']) {
			//如果母链大于等于2000，小于4500个，等级修改为白银会员
			if ($exist['grade']!=2){
				$money_finance->where(array('uid'=>$id))->setField('grade','2');
			}
		}elseif ($dj_0['min_points']<=$mb['member_bi'] && $mb['member_bi'] < $dj_0['max_points']) {
			//如果母链大于等于500，小于2000个，等级修改为普通会员
			if ($exist['grade']!=1){
				$money_finance->where(array('uid'=>$id))->setField('grade','1');
			}
		}



	}



	//循环用户表里面的path_id路径
/* 	public function user_path($user_data){

		$member=M('member');
		$pid=$member->where(array('id'=>$user_data['buyer_id']))->getField('path_id');
		$pid = rtrim($pid, ',');//去除逗号
		$prev_path = explode(',', $pid);//组成数组
		rsort($prev_path);//rsort() 函数对数值数组进行降序排序
		$prev_path=array_splice($prev_path,1); //去除自己的ID

		foreach ($prev_path as $k => $v) {
	    			
		}

	} */
	
	//一代二代三代
	public function shang_dai($uu){
		//echo '11111';die;
		$money_finance=M('money_finance');//个人财务表
	   	$cw_encryption=M('secure_encryption');//个人财务加密表

		
   		$member=M('member');
   		$money_types=M('money_types');
        $tixian_setting=M('tixian_setting')->where(array('id'=>1))->find();

   		$one_pid=$member->where(array('id'=>$uu['buyer_id']))->getField('pid');//上级  一代
		if($one_pid!=0){
			
			$gou_1=$money_finance->where(array('uid'=>$one_pid))->getField('grade');//等级大于0
			if($gou_1>=$tixian_setting['one_group']){
				$sy_one=$tixian_setting['one_dai']*$uu['paid_amount'];
				if($sy_one>0){
				
					$keys=$cw_encryption->where(array('uid'=>$one_pid))->find();//个人财务加密表
					$cw=$money_finance->where(array('uid'=>$one_pid))->find();
					
					if($cw){
						if ($cw['member_z_bi'] > 0) {

							$x_keys=md5(md5($cw['member_z_bi']) . $keys['encrypt']);//安全加密
							if ($keys['currency']==$x_keys) {
								$money_finance->where(array('uid'=>$one_pid))->setInc('member_z_bi',$sy_one);//累计子币收益
								$member_bii=$money_finance->where(array('uid'=>$one_pid))->getField('member_z_bi');
								$datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
								$cw_encryption->where(array('uid'=>$one_pid))->setField('currency',$datat);

							}else{
								$log_data=array(
									'uid'=>$one_pid,
									'text'=>'系统执行每日生息时，会员账户剩余SKV数据异常，停止给此会员执行上级奖励！',
									'time'=>time(),
									);
								M('exception_log')->data($log_data)->add();
								//exit('<script>alert("您账户剩余母链异常，请联系客服！");location.href="'.U('member/index/index').'"</script>');
							}

						}else{

							$money_finance->where(array('uid'=>$one_pid))->setInc('member_z_bi',$sy_one);//累计母币收益
							$member_bii=$money_finance->where(array('uid'=>$one_pid))->getField('member_z_bi');
							$datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
							$cw_encryption->where(array('uid'=>$one_pid))->setField('currency',$datat);
						}

					}
				
				
				
					$p_data=array(
						'member_getid'=>$one_pid,//收益会员
						'member_giveid'=>$uu['buyer_id'],//提供会员
						'money_nums'=>$uu['paid_amount'],//交易金额
						'money_hcb'=>$sy_one,//交易数量
						'money_price'=>$tixian_setting['one_dai'],//奖励的比例
						'money_produtime'=>time(),//交易时间
						'money_type'=>'6',//交易类型
					);
				   $money_types->data($p_data)->add();			
					


				
				}					
			}
			
		
		}
		
		

   		$two_pid=$member->where(array('id'=>$one_pid))->getField('pid');//上级  二代
   		//print_r($uu);die;
		if($two_pid!=0){
			$gou_2=$money_finance->where(array('uid'=>$two_pid))->getField('grade');//等级大于0
			if($gou_2>=$tixian_setting['two_group']){
				$sy_two=$tixian_setting['two_dai']*$uu['paid_amount'];
				if($sy_two>0){
				
					$keys=$cw_encryption->where(array('uid'=>$two_pid))->find();//个人财务加密表
					$cw=$money_finance->where(array('uid'=>$two_pid))->find();
					
					if($cw){
						if ($cw['member_z_bi'] > 0) {

							$x_keys=md5(md5($cw['member_z_bi']) . $keys['encrypt']);//安全加密
							if ($keys['currency']==$x_keys) {
								$money_finance->where(array('uid'=>$two_pid))->setInc('member_z_bi',$sy_two);//累计子币收益
								$member_bii=$money_finance->where(array('uid'=>$two_pid))->getField('member_z_bi');
								$datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
								$cw_encryption->where(array('uid'=>$two_pid))->setField('currency',$datat);

							}else{
								$log_data=array(
									'uid'=>$two_pid,
									'text'=>'系统执行每日生息时，会员账户剩余SKV数据异常，停止给此会员执行上级奖励！',
									'time'=>time(),
									);
								M('exception_log')->data($log_data)->add();
								//exit('<script>alert("您账户剩余母链异常，请联系客服！");location.href="'.U('member/index/index').'"</script>');
							}

						}else{

							$money_finance->where(array('uid'=>$two_pid))->setInc('member_z_bi',$sy_two);//累计母币收益
							$member_bii=$money_finance->where(array('uid'=>$two_pid))->getField('member_z_bi');
							$datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
							$cw_encryption->where(array('uid'=>$two_pid))->setField('currency',$datat);
						}

					}
				
				
				
					$p_data=array(
						'member_getid'=>$two_pid,//收益会员
						'member_giveid'=>$uu['buyer_id'],//提供会员
						'money_nums'=>$uu['paid_amount'],//交易金额
						'money_hcb'=>$sy_two,//交易数量
						'money_price'=>$tixian_setting['two_dai'],//奖励的比例
						'money_produtime'=>time(),//交易时间
						'money_type'=>'7',//交易类型
					);
				   $money_types->data($p_data)->add();			
					


				
				}					
			}
			
			
			
		}
  		
		
   		$three_pid=$member->where(array('id'=>$two_pid))->getField('pid');//上级  三代
		if($three_pid!=0){			
			$gou_3=$money_finance->where(array('uid'=>$three_pid))->getField('grade');//等级大于0
			if($gou_3>=$tixian_setting['three_group']){
				$sy_three=$tixian_setting['three_dai']*$uu['paid_amount'];
				if($sy_three>0){
				
					$keys=$cw_encryption->where(array('uid'=>$three_pid))->find();//个人财务加密表
					$cw=$money_finance->where(array('uid'=>$three_pid))->find();
					
					if($cw){
						if ($cw['member_z_bi'] > 0) {

							$x_keys=md5(md5($cw['member_z_bi']) . $keys['encrypt']);//安全加密
							if ($keys['currency']==$x_keys) {
								$money_finance->where(array('uid'=>$three_pid))->setInc('member_z_bi',$sy_three);//累计子币收益
								$member_bii=$money_finance->where(array('uid'=>$three_pid))->getField('member_z_bi');
								$datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
								$cw_encryption->where(array('uid'=>$three_pid))->setField('currency',$datat);

							}else{
								$log_data=array(
									'uid'=>$three_pid,
									'text'=>'系统执行每日生息时，会员账户剩余SKV数据异常，停止给此会员执行上级奖励！',
									'time'=>time(),
									);
								M('exception_log')->data($log_data)->add();
								//exit('<script>alert("您账户剩余子链异常，请联系客服！");location.href="'.U('member/index/index').'"</script>');
							}

						}else{

							$money_finance->where(array('uid'=>$three_pid))->setInc('member_z_bi',$sy_three);//累计母币收益
							$member_bii=$money_finance->where(array('uid'=>$three_pid))->getField('member_z_bi');
							$datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
							$cw_encryption->where(array('uid'=>$three_pid))->setField('currency',$datat);
						}

					}
				
				
				
					$p_data=array(
						'member_getid'=>$three_pid,//收益会员
						'member_giveid'=>$uu['buyer_id'],//提供会员
						'money_nums'=>$uu['paid_amount'],//交易金额
						'money_hcb'=>$sy_three,//交易数量
						'money_price'=>$tixian_setting['three_dai'],//奖励的比例
						'money_produtime'=>time(),//交易时间
						'money_type'=>'8',//交易类型
					);
				   $money_types->data($p_data)->add();			
					


				
				}					
			}			
			
			
			
			
		}
		
	}
	
	
  	public function admin_level($userid){

  		$member = M('member');
  		$sorts=M('member_sorts');
  		$uid = $userid['register_id'];//新注册会员id

		$data1=array(
			'uid'=>$uid,
			'pid'=>$userid['member_jd'],//接点人排位系统的主id
			'member_bigposition'=>$userid['bigposition'],//1左2右
			'member_regtime'=>time(),
		);
		$sorts->data($data1)->add();
		$t_id=$sorts->where(array('uid'=>$uid))->find();//排位系统新注册会员的主id
		$path_id=$userid['member_path'].$t_id['id'].',';//$userid['member_path']接点人排位系统的path路径
		$sorts->where(array('uid'=>$uid))->setField('path_id',$path_id);//修改path路径
				
  	}	
	


	
}
?>
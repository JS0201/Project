<?php 

/**
 * 会员权限
 */
function rule_auth_list() {
	
	$arr=array(
		 '打折'=>'goods:edit',
		 '赠送'=>'goods:zs',
		 '退菜'=>'goods:out',
		 '结账'=>'goods:order',
		 '报表'=>'goods:count',
	);
	
	return $arr;
}
/*判断是第几层拿的分红*/
function findmsg($pid,$img){
	$users = M('member');
	if(!empty($pid) && $img=='nick'){
		$msg = $users ->where(array('id'=>$pid))->getField('nickname');
	}
	if(!empty($pid) && $img=='img'){
		$msg = $users ->where(array('id'=>$pid))->getField('face');
	}
	return $msg;
}

function findimg($pid){
	$users = M('member');
	if(!empty($pid)){
		$msg = $users ->where(array('id'=>$pid))->getField('username');
		return $msg;
	}
}

function findcs($sortid){
	$joins = M('member_joinsorts');
	$layers = $joins->where(array('member_sortid'=>$sortid))->getField('member_enougulayer');
	return $layers;
}

//用户头衔
function userslevel($status=''){
	$status_array=array('0'=>'粉丝','1'=>'vip','2'=>'高级vip','6'=>'准合伙人','3'=>'合伙人','4'=>'高级合伙人',5=>'股东');
	if($status==''){
		return $status_array;
	}else{
		return $status_array[$status];
	}
}



// 短信发送接口
function setMsg($mobile,$code){
	//发送短信
	$flag = 0;
	$params='';//要post的数据
//	$verify = rand(123456, 999999);//获取随机验证码
	$verify = $code;//获取随机验证码

	//以下信息自己填以下
	$mobile=$mobile;//手机号
	$argv = array(
		'name'=>'tdrhbl',     //必填参数。用户账号
		'pwd'=>'AA1E40B564B14B61BC92D642A367',     //必填参数。（web平台：基本资料中的接口密码）

		'content'=>"【SKV】您本次的验证码为".$code.",请在5分钟内完成验证，验证码打死也不要告诉别人哦！",//必填参数。发送内容（1-500 个汉字）UTF-8编码
//		'content'=>'短信验证码为：'.$verify.'，请勿将验证码提供给他人。',//必填参数。发送内容（1-500 个汉字）UTF-8编码
		'mobile'=>$mobile,   //必填参数。手机号码。多个以英文逗号隔开
		'stime'=>'',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
		'sign'=>'【SKV】',    //必填参数。用户签名。
		'type'=>'pt',  //必填参数。固定值 pt
		'extno'=>''    //可选参数，扩展码，用户定义扩展码，只能为数字
	);
	//print_r($argv);exit;
	//构造要post的字符串
	//echo $argv['content'];
	foreach ($argv as $key=>$value) {
		if ($flag!=0) {
			$params .= "&";
			$flag = 1;
		}
		$params.= $key."="; $params.= urlencode($value);// urlencode($value);
		$flag = 1;
	}
	$url = "http://210.5.152.195:1860/asmx/smsservice.aspx?".$params; //提交的url地址
	$con= substr( file_get_contents($url), 0, 1 );  //获取信息发送后的状态
	return $con;
}

?>
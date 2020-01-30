<?PHP 
session_start();
if($_POST){  
	header("Content-Type: text/html; charset=UTF-8");

	$flag = 0; 
	$params='';//要post的数据 
	$verify = rand(123456, 999999);//获取随机验证码		

	$statusStr = array(
		"0" => "短信发送成功",
		"1" => "含有敏感词汇",
		"2" => "余额不足",
		"3" => "没有号码",
		"4" => "包含sql语句",
		"10" => "账号不存在",
		"11" => "账号注销",
		"12" => "账号停用",
		"13" => "IP鉴权失败",
		"14" => "格式错误",
		"-1" => "系统异常"
	);

	//是否为第1+N次操作
	if($_SESSION['sms_code']){
		$prev_send_time = $_SESSION['sms_code_time'];	//上次发送时间
		if ((time()-$prev_send_time)<=60) {
			echo "操作过于频繁,请稍后再试。";
			return;
		}
		
	}


	//参数验证
	$phone = $_POST["phone"];//手机号码

	$reg = "/^1[3|4|5|7|8][0-9]{9}$/";
	if(!preg_match($reg,$phone)){
		echo "手机号码不正确";
		return;
	}


	//以下信息自己填以下
	$mobile=$phone;//手机号
	$argv = array( 
		'name'=>'tdrhbl',     //必填参数。用户账号
		'pwd'=>'TDR86521506',     //必填参数。（web平台：基本资料中的接口密码）
		'content'=>'短信验证码为：'.$verify.'，请勿将验证码提供给他人。',   //必填参数。发送内容（1-500 个汉字）UTF-8编码
		'mobile'=>$mobile,   //必填参数。手机号码。多个以英文逗号隔开
		'stime'=>'',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
		'sign'=>'win',    //必填参数。用户签名。
		'type'=>'pt',  //必填参数。固定值 pt
		'extno'=>time();    //可选参数，扩展码，用户定义扩展码，只能为数字
	); 
	
	
	//会话存取
	$_SESSION["sms_code"] = $sms_code;		//验证码
	$_SESSION["sms_code_time"] = time();	//发送时间
	$_SESSION["sms_phone"] = $phone;		//发送手机号码


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

	echo $statusStr[$con];
}	
?>
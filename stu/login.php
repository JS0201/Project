<!DOCTYPE html>
<html>
<?php 
header("Content-Type: text/html;charset=utf-8"); 
session_start();
if(isset($_SESSION['username'])){
    echo "<script>alert('您已登录，即将为您跳到转首页！');location.href='index.php'</script>";
}
?>
<head>
	<title>登陆</title>
	<style type="text/css" media="screen">
		*{
				margin: 0;
	padding: 0;
		}
		#head{
			height: 120px;
			width: 100;
			background-color: #96B97D;
			position: relative;
		}
		#body{

			height: 719px;
			width: 100;
			background-image: url(4.jpg);
			background-repeat: no-repeat;
			background-position: center center;
			position: relative;
			background-color: #F6F6F6;
		}
		#login{
			margin: 30px auto;
 			padding:35px 50px;
 			box-shadow: 1px 1px 2px 1px #aaaaaa;
 			border-radius: 3px;
 			width:350px;
 			height:500px;
 			position: absolute;
 			left: 60%;
 			background-color: #F7F6F6;
		}

		h1{
			font-size:25px;font-family:微软雅黑;color:#000000FF;
	}
		#login p{
			font-size:16px;font-family:微软雅黑;color:rgb(0, 0, 0);font-weight:bold;
		}
		input{
			border-radius: 5px; color: rgb(0, 0, 0);line-height: 25px;width:240px;
		}
		#title{
			position: absolute;
			top:43px;
		    left:200px;
		}
		#title p{
			font-size: 35px;
			font-family:MingLiU;
			color: #FFFFFFFF; 
		}
		#img img{
			width: 70px;
			height: 70px;
			top:31px;
			left: 120px;
			position: absolute;
		}

	</style>
</head>
<body>
	<div id="head">
		<div id="img">
			<img src=\public\images\hat.jpg>
		</div>
		<div id="title">
	<p>
		学生信息管理系统	
	</p>
	</div>
</div>
	<div id="body">
		<div align="center" id="login">
	<p style="font-size:36px;font-family:楷书;color:rgb(0, 0, 0);"">用户登录</p><br><br><br>
 <form action="login1.php" method="post">
     <p>用户名：<input  type="text" name="username"  style = "background-color: #FFFFFF80 ; " placeholder="Name"></p><br><br>
     <p>密&nbsp;&nbsp;&nbsp;码：<input  type="password" name="password"  style = "background-color: #FFFFFF80 ;" placeholder="Password"></p><br><br>
     <input type="submit" value="登录" style ="height : 28px;border:1px;background-color:#00bee7;color:#fff;width:55px;border-radius: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     <input type="button" value="注册" style ="height : 28px;border:1px;background-color:#00bee7;color:#fff;width:55px;border-radius: 5px;" onclick="javascrtpt:window.location.href='reg.php'">
 </form>
 </div>
</div>
</body>
</html>


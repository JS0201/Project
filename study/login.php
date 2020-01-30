<!DOCTYPE html>
<?php header("Content-Type: text/html;charset=utf-8"); ?>
<head>
	<title>登陆</title>
	<style type="text/css" media="screen">
		h1{
			font-size:25px;font-family:微软雅黑;color:rgb(98,94,91);
	}
	</style>
	
</head>
<body>
	<div align="center">
	<h1>登录页面</h1>
 <form action="login1.php" method="post">
     <p>用户名：<input  type="text" name="username"  style = "background-color: rgb(250, 255, 189) ; border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;" placeholder="username"></p>
     <p>密&nbsp;&nbsp;&nbsp;码：<input  type="password" name="password"  style = "background-color: rgb(250, 255, 189) ; border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;" placeholder="password"></p>
     <input type="submit" value="登录" style ="height : 25px;border:1px;background-color:#00bee7;color:#fff;width:72px;border-radius: 5px;">
     <input type="button" value="注册" style ="height : 25px;border:1px;background-color:#00bee7;color:#fff;width:72px;border-radius: 5px;" onclick="javascrtpt:window.location.href='reg.php'">
 </form>
 </div>

</body>

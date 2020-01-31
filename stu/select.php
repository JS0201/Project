<!DOCTYPE html>
<html lang="en">
<html >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>信息查询界面</title>
<?php
    require ("public/menu.php");
 ?>
</head>
 
<body>
<div style="font-size:25px;font-family:微软雅黑;color:rgb(98,94,91);" align="center">
	<p>请输入学生的学号进行查询</p>
</div>

<form action="select1.php" method="post" style="margin: 35px auto;padding:30px;box-shadow: 1px 1px 2px 1px #aaaaaa;border-radius: 3px;width:380px;">
	<input  type="text" name="check"  style = "background-color: rgb(250, 255, 189) !important; border-radius: 3px;box-shadow: none; color: rgb(0, 0, 0);line-height: 26px;width:240px;" placeholder="请输入要查询学生的学号">

	<input type="submit" value="查询" style ="height : 36px;border:1px;background-color:#00bee7;color:#fff;width:72px;border-radius: 3px;">
</form>
</body>
	</html>
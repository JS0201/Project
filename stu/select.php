<!DOCTYPE html>
<html lang="en">
<html >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>信息查询界面</title>
<?php
    require ("public/menu.php");
 ?>
 <style type="text/css">
 	p{
 		font-size:25px;
 		font-family:微软雅黑;
 		color:rgb(98,94,91);
 		text-align:center;
 	}
 	#body{
 		margin: 50px auto;
 		padding:35px 50px;
 		box-shadow: 1px 1px 2px 1px #aaaaaa;
 		border-radius: 3px;
 		width:380px;
 	}
 	#check{
 		background-color: rgb(250, 255, 189) !important;
 		border-radius: 3px;
 		box-shadow: none; 
 		color: rgb(0, 0, 0);
 		line-height: 26px;
 		width:240px;
 	}
 	#submit{
 		height : 31px;
 		border:1px;
 		background-color:#00bee7;
 		color:#fff;
 		width:72px;
 		border-radius: 3px;
 		float: right;
 	}
 </style>
</head>
 
<body>
<div>
	<p>请输入学生的学号进行查询</p>
</div>
<div id=body>
<form action="select1.php" method="post">
	<input  type="text" name="check"  id="check" placeholder="请输入要查询学生的学号">

	<input type="submit" value="查询" id="submit" >
</form>
<div>
</body>
	</html>
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>登陆界面</title>
    <style type="text/css" media="screen">
 #top{
 font-size: 14px;
 width: 100%;
 height: 90px;
 background-color: #f1f1f1;;
 border-bottom: 1px solid #bbbbbb;
 }
 #menu{
 width: 1000px;
 overflow: hidden;
 margin: 0 auto;
 }
 #menu img{
 height: 90px;
 }
 #menu ul{
 list-style-type: none;
 }
 #menu ul li{
 float: left;
 height: 70px;
 line-height: 70px;
 margin-right: 50px;
 }
 #menu ul li a{
 color: black;text-decoration: none;
 display: inline-block;
 }
 #menu ul li a:hover{color: #65b5ff;text-decoration: none}
</style>
</head>
<body>
<div id="top">
 <div id="menu">
 <ul>
  <li><img src=758fb76833062c094babc0c7c4250a4b.jpg> </li>
  <li><a class="active" href="index.php">主页</a></li>
  <li><a href="select.php"> 浏览学生</a></li>
  <li><a href="add.php"> 添加学生</a></li>
  <li><a href="exit.php"> 退出登陆</a></li>
  <li style="float:right"><a href="#about"><?php     
    echo '欢迎你'.$_SESSION['username'];   
?></a></li>
</ul>
 </div>
</div>

</body>
</html>
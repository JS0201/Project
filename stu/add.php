<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <title>学生管理系统</title>
 <?php include ("public/menu.php"); ?>
 <style type="text/css" media="screen">
 p{
 	color:#666666;
 	font-size:25px;
 	font-family:微软雅黑;
 }
 </style>
</head>
<body>
<div align="center">
 <p>增加学生信息</p><br>
 <form action="add1.php?action=add" method="post">
 <table>
 	<tr>
 <td>学号</td>
 <td><input  type="text" name="sid"  style = "background-color: rgb(250, 255, 189) ; border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;" placeholder="sid"></td>
 </tr>
 <tr>
 <td>姓名</td>
 <td><input  type="text" name="name"  style = "background-color: rgb(250, 255, 189) ; border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;" placeholder="name"></td>
 </tr>
 <tr>
 <td>年龄</td>
 <td><input  type="text" name="age"  style = "background-color: rgb(250, 255, 189) ; border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;" placeholder="age"></td>
 </tr>
 <tr>
 <td>性别</td>
 <td><input type="radio" name="sex" value="男">男</td>
 <td><input type="radio" name="sex" value="女">女</td>
 </tr>
 <tr>
 <td>年级</td>
 <td><input  type="text" name="class"  style = "background-color: rgb(250, 255, 189) ; border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;" placeholder="class"></td>
 </tr>
 <tr>
 <td>语文</td>
 <td><input  type="text" name="chinese"  style = "background-color: rgb(250, 255, 189) ; border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;" placeholder="chinese"></td>
 </tr>
 <tr>
 <td>数学</td>
 <td><input  type="text" name="math"  style = "background-color: rgb(250, 255, 189) ; border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;" placeholder="math"></td>
 </tr>
 <tr>
 <td>英语</td>
 <td><input  type="text" name="english"  style = "background-color: rgb(250, 255, 189) ; border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;" placeholder="english"></td>
 </tr>
 <tr>
 <!-- <td> </td>-->
 <td>
 <input type="button" value="返回" style ="height : 25px;border:1px;background-color:#00bee7;color:#fff;width:72px;border-radius: 5px;" onclick="javascrtpt:window.location.href='index.php'"></td>
 <td>
 	<input type="submit" value="添加" style ="height : 25px;border:1px;background-color:#00bee7;color:#fff;width:72px;border-radius: 5px;">
</td>
<td>
 <input type="reset" value="重置" style ="height : 25px;border:1px;background-color:#00bee7;color:#fff;width:72px;border-radius: 5px;">
</td>
 </tr>
 </table>
 </form>
	</div>
</body>
</html>
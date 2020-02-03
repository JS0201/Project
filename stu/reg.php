<!DOCTYPE html>
<head>
	<title>注册</title>

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
     <p style="font-size:36px;font-family:楷书;color:rgb(0, 0, 0);"">用户注册</p><br><br><br>
<form action="reg1.php" method="post">
     <p>用户名：&nbsp;&nbsp;&nbsp;<input  type="text" name="username"  style = " border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;"></p><br>
     <p>密&nbsp;&nbsp;&nbsp;&nbsp;码：&nbsp;&nbsp;<input  type="password" name="password"  style = " border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;"></p><br>
     <p>确认密码：<input  type="password" name="repassword"  style = " border-radius: 3px; color: rgb(0, 0, 0);line-height: 20px;width:240px;"></p><br>
      <p>
          <input type="submit" value="注册" style ="height : 25px;border:1px;background-color:#00bee7;color:#fff;width:72px;border-radius: 5px;">
          <input type="reset" value="重置" style ="height : 25px;border:1px;background-color:#00bee7;color:#fff;width:72px;border-radius: 5px;">
          <input type="button" value="返回" style ="height : 25px;border:1px;background-color:#00bee7;color:#fff;width:72px;border-radius: 5px;" onclick="javascrtpt:window.location.href='login.php'">
     </p>
 </form>
 </div>
</div>
 </form>
 </div>
</body>

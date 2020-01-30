<?php
if (empty($_POST["username"])) {
    echo "<script>alert('用户名不能为空！');location.href='reg.php';</script>";
} else {
    $username = $_POST["username"];
}
if (empty($_POST["password"])) {
    echo "<script>alert('密码不能为空！');location.href='reg.php';</script>";
} else {
    $password = sha1($_POST["password"]);
    $repassword = sha1($_POST["repassword"]);
}
if ($password != $repassword) {
    echo "<script>alert('两次密码不一致！');location.href='reg.php';</script>";
}

$con = mysqli_connect("localhost","root","root","study");
$result = $con->query("SELECT password FROM user WHERE username = "."'$username'");
$rs=$result->fetch_row();
if(!empty($rs)){
 echo "<script>alert('用户已存在！');location.href='reg.php';</script>";
}else {
 //1.造连接对象
$conn=mysqli_connect("localhost","root","root","study");
//2.写SQL语句
//根据{$uid}用户名查密码password
$sql = "INSERT INTO user (username, password) VALUES ('{$username}','{$password}')";
if (mysqli_query($conn, $sql)) {
 echo "<script>alert('注册成功！返回登陆');location.href='login.php';</script>";}
  else {
    echo '登录失败';
}
 }

<?php
session_start();
if(isset($_SESSION['username'])){
    echo "<script>alert('您已登录，请不要重复登录！');location.href='index.php'</script>";
}
if (empty($_POST["username"])) {
    echo "<script>alert('用户名不能为空！');location.href='login.php';</script>";
} else {
    $username = $_POST["username"];
}
if (empty($_POST["password"])) {
    echo "<script>alert('密码不能为空！');location.href='login.php';</script>";
} else {
    $password = sha1($_POST["password"]);
}
$conn=mysqli_connect("localhost","root","root","study");
$sql = "select * from user where username='{$username}'";
$reslut = mysqli_query($conn,$sql);
$row = mysqli_fetch_array($reslut);
if($row['password']==$password && !empty($password))
{	
	$_SESSION['username'] = $username;
	$_SESSION['is_admin'] = $row['is_admin'];
    echo "<script>alert('登陆成功！');location.href='index.php'</script>"; 
}
else
{
    echo "<script>alert('账号或密码错误！');location.href='login.php'</script>";
}
?>
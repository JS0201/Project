<?php
header("content-type:text/html;charset=utf8");
$id = $_GET['id'];
$conn=mysqli_connect("localhost","root","root","study");
mysqli_set_charset($conn,"utf8");
$sql = "delete from stu where id='$id'";
$rw = mysqli_query($conn,$sql);
if ($rw > 0){
    echo "<script>alter('删除成功');</script>";
}else{
    echo "<script>alter('删除失败');</script>";
}
header('Location: index.php');
?>
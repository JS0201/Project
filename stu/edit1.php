<?php
header("content-type:text/html;charset=utf8");
$conn=mysqli_connect("localhost","root","root","study");
mysqli_set_charset($conn,"utf8");
            $id = $_POST['id'];
            $name = $_POST['name'];
            $age = $_POST['age'];
            $class = $_POST['class'];
            $sex = $_POST['sex'];
            $sql = "update stu set name='$name', age='$age',sex='$sex',class='$class' where id='$id';";
            $rw = mysqli_query($conn,$sql);
            if ($rw > 0){
                echo "<script>alert('修改成功');</script>";
            }else{
                echo "<script>alert('修改失败');</script>";
            }
            header('Location: index.php');
?>
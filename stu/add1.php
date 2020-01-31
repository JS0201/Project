<?php
header("content-type:text/html;charset=utf8");
$conn=mysqli_connect("localhost","root","root","study");
mysqli_set_charset($conn,"utf8");
if($conn){
    if($_GET['action']){
            $name = $_POST['name'];
            $sex = $_POST['sex'];
            $age = $_POST['age'];
            $class = $_POST['class'];
             $chinese = $_POST['chinese'];
              $math = $_POST['math'];
               $english = $_POST['english'];
            $sql = "insert into stu (name, sex, age, class,chinese,math,english) values ('$name', '$sex','$age','$class','$chinese','$math','$english')";
            $rw = mysqli_query($conn,$sql);
            if ($rw > 0){
                echo "<script>alert('添加成功');</script>";
            }else{
                echo "<script>alert('添加失败');</script>";
            }
            }
            }
            header('Location: index.php');
            ?>
            
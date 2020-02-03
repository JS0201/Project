<!DOCTYPE html>
<html lang="en">
 <head>
 <?php
    require ("public/menu.php");
 ?>
 <style type="text/css">
    .table{
        position: relative;
        left:22%;
    }
    .table table,th,tr,td{
            border:1px solid black;
            border-collapse: collapse;
            border-color:#09F;
            text-align:center;
            font-family:楷书;
        }
        .table th{
            height: 39px;
            width:100px;
            font-size:20px;
            color:rgb(98,94,91);'
        }
        .table td{
            padding:6px;
            width:100px;
            font-size:20px;
            color:rgb(98,94,91);'
        }

 </style>
</head>
 <body>
<div style="font-size:25px;font-family:微软雅黑;color:rgb(98,94,91);" align="center">
    <p>学生<?php
    $sid=$_POST['check'];
    $conn=mysqli_connect("localhost","root","root","study"); 
    $sql_select = "select name from stu where sid=$sid";
    $result=mysqli_query($conn,$sql_select);
            if (!$sid){
                echo "<script>alert('学号不能为空！');location.href='select.php'</script>";
            }
    $row = mysqli_fetch_array($result);
            if (!$row){
                echo "<script>alert('输入的学号错误，请检查！');location.href='select.php'</script>";
            }
    echo "{$row['name']}";
    ?>的信息</p><br></div>
 <div class="table">
 <table>
        <tr>
            <th>ID</th>
            <th>姓名</th>
            <th>性别</th>
            <th>年龄</th>
            <th>班级</th>
            <th>语文</th>
            <th>数学</th>
            <th>英语</th>

        </tr>
        <?php
        $sid=$_POST['check'];
 $conn=mysqli_connect("localhost","root","root","study"); 
 $sql_select = "select * from stu where sid=$sid";
 $result=mysqli_query($conn,$sql_select);
 foreach ( $result as $row) {
 echo "<tr>";
 echo "<th>{$row['id']} </th>";
 echo "<th>{$row['name']}</th>";
 echo "<th>{$row['sex']} </th>";
 echo "<th>{$row['age']} </th>";
 echo "<th>{$row['class']}</th>";
 echo "<th>{$row['chinese']} </th>";
 echo "<th>{$row['math']} </th>";
 echo "<th>{$row['english']}</th>";
 echo "</tr>";
        }
 ?>
 </table> 
 </div>
 </body>

</html>
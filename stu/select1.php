<!DOCTYPE html>
<html lang="en">
 <head>
 <?php
    require ("public/menu.php");
 ?>
</head>
 <body>
<div style="font-size:25px;font-family:微软雅黑;color:rgb(98,94,91);" align="center"><p><?php
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
 ?>的信息</p></div>
 <table style="border: solid 1px;border-color:#09F;" align ="center" width=60% border="1" cellspacing="0">
        <tr>
            <th width=90px style="font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);">ID</th>
            <th width=90px style="font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);">姓名</th>
            <th width=90px style="font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);">性别</th>
            <th width=90px style="font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);">年龄</th>
            <th width=90px style="font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);">班级</th>
            <th width=90px style="font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);">语文</th>
            <th width=90px style="font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);">数学</th>
            <th width=90px style="font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);">英语</th>

        </tr>
        <?php
        $sid=$_POST['check'];
 $conn=mysqli_connect("localhost","root","root","study"); 
 $sql_select = "select * from stu where sid=$sid";
 $result=mysqli_query($conn,$sql_select);
 foreach ( $result as $row) {
 echo "<tr>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['id']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['name']}</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['sex']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['age']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['class']}</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['chinese']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['math']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['english']}</th>";
 echo "</tr>";
        }
 ?>
 </table> 
 </body>

</html>
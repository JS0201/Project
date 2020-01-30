<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>学生信息管理</title>
</head>
<body>
    <div align="center">
    <?php
    require ("public/menu.php");
 ?>
 <div style="font-size:25px;font-family:微软雅黑;color:rgb(98,94,91);" align="center"><p>全部学生信息</p></div>
  <table style="border: solid 1px;border-color:#09F;" align ="center" width=60% border="1" cellspacing="0">
  <?php
  $is_admin=$_SESSION['is_admin'];
  if ($is_admin == 1){
        echo "<tr>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>ID</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>学号</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>姓名</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>性别</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>年龄</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>班级</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>语文</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>数学</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>英语</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>操作</th>";
 echo "</tr>";
 $conn=mysqli_connect("localhost","root","root","study"); 
    $page=isset($_GET['p'])? $_GET['p']:1;//定义变量由浏览器传入 
 $sql_select = "select * from stu order by id desc limit ".($page-1) * 5 .",5 ";
 $result=mysqli_query($conn,$sql_select);
 foreach ( $result as $row) {
 echo "<tr>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['id']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['sid']}</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['name']}</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['sex']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['age']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['class']}</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['chinese']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['math']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['english']}</th>";
 echo "<td width=150px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>
          <a href='edit.php?id={$row['id']}'>修改</a>
          <a href='delete.php?id={$row['id']}' onclick='doDel()'>删除</a>
        </td>";
 echo "</tr>";
        }
    }
 
  else
{
    echo "<tr>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>ID</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>学号</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>姓名</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>性别</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>年龄</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>班级</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>语文</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>数学</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>英语</th>";
 echo "</tr>";
 $is_admin=$_SESSION['is_admin'];
 $conn=mysqli_connect("localhost","root","root","study"); 
    $page=isset($_GET['p'])? $_GET['p']:1;//定义变量由浏览器传入 
 $sql_select = "select * from stu order by id desc limit ".($page-1) * 5 .",5 ";
 $result=mysqli_query($conn,$sql_select);
    foreach ( $result as $row) {
 echo "<tr>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['id']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['sid']}</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['name']}</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['sex']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['age']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['class']}</th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['chinese']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['math']} </th>";
 echo "<th width=90px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>{$row['english']}</th>";
 echo "</tr>";
        }
}
 ?> 
</table>
 <div align='center'>
 <?php
    $to_sql="SELECT COUNT(*)FROM stu";
    $result= mysqli_query($conn,$to_sql);
    $row=mysqli_fetch_array($result);
    $count=$row[0];
    $to_pages=ceil($count/5);
     if($page<=1){
        echo "
            <a href='".$_SERVER['PHP_SELF']."?p=1'>上一页</a>";
        
        }else{
        echo "<a href='".$_SERVER['PHP_SELF']."?p=".($page-1)."'>上一页</a>";
    }
    if ($page<$to_pages){
        echo "<a href='".$_SERVER['PHP_SELF']."?p=".($page+1)."'>下一页</a>";
     
     
    }else{
        echo "<a href='".$_SERVER['PHP_SELF']."?p=".($to_pages)."'>下一页</a>";
    }
    ?>
</div>
</div>
<script>
        function doDel(id) {
            confirm('确认删除?')
        }
    </script>
</body>

</html>
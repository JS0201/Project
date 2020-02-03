<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>学生信息管理</title>
    <style type="text/css">
        #ta table,th,tr,td{
            border:1px solid black;
            border-collapse: collapse;
            border-color:#09F;
            text-align:center;
            font-family:楷书;
        }
        #ta th{
            height: 39px;
            width:100px;
            font-size:20px;
            color:rgb(98,94,91);'
        }
        #ta td{
            padding:6px;
            width:100px;
            font-size:20px;
            color:rgb(98,94,91);'
        }
        #ta td a{

        }
        #test p{
             font-size:25px;
             font-family:楷书;
             color:rgb(98,94,91);
             text-align:center;
        }
        #page button{
            height : 28px;
            border:1px;
            background-color:#70C9FF;
            color:#fff;
            width:55px;
            border-radius: 5px;
        }
        #ta td button{
            border:1px;
            background-color:#C0C0C0;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div align="center" id="body">
    <?php
    require ("public/menu.php");
 ?>
 <div id="test"><p>全部学生信息</p></div>
  <div id="ta">
  <table>
  <?php
  $is_admin=$_SESSION['is_admin'];
  if ($is_admin == 1){
        echo "<tr>";
 echo "<th>ID</th>";
 echo "<th>学号</th>";
 echo "<th>姓名</th>";
 echo "<th>性别</th>";
 echo "<th>年龄</th>";
 echo "<th>班级</th>";
 echo "<th>语文</th>";
 echo "<th>数学</th>";
 echo "<th>英语</th>";
 echo "<th>操作</th>";
 echo "</tr>";
 $conn=mysqli_connect("localhost","root","root","study"); 
 $page=isset($_GET['p'])? $_GET['p']:1;//定义变量由浏览器传入 
 $sql_select = "select * from stu order by id desc limit ".($page-1) * 5 .",5 ";
 $result=mysqli_query($conn,$sql_select);
 foreach ( $result as $row) {
 echo "<tr>";
 echo "<td>{$row['id']} </td>";
 echo "<td>{$row['sid']}</td>";
 echo "<td>{$row['name']}</td>";
 echo "<td>{$row['sex']} </td>";
 echo "<td>{$row['age']} </td>";
 echo "<td>{$row['class']}</td>";
 echo "<td>{$row['chinese']} </td>";
 echo "<td>{$row['math']} </td>";
 echo "<td>{$row['english']}</td>";
 echo "<td width=150px style='font-size:20px;font-family:微软雅黑;color:rgb(98,94,91);'>
          <a href='edit.php?id={$row['id']}'><button>修改</button></a>&nbsp;
          <a href='delete.php?id={$row['id']}' onclick='doDel()'><button>删除</button></a>
        </td>";
 echo "</tr>";
        }
    }
 
  else
{
    echo "<tr>";
 echo "<th>ID</th>";
 echo "<th>学号</th>";
 echo "<th>姓名</th>";
 echo "<th>性别</th>";
 echo "<th>年龄</th>";
 echo "<th>班级</th>";
 echo "<th>语文</th>";
 echo "<th>数学</th>";
 echo "<th>英语</th>";
 echo "</tr>";
 $is_admin=$_SESSION['is_admin'];
 $conn=mysqli_connect("localhost","root","root","study"); 
    $page=isset($_GET['p'])? $_GET['p']:1;//定义变量由浏览器传入 
 $sql_select = "select * from stu order by id desc limit ".($page-1) * 5 .",5 ";
 $result=mysqli_query($conn,$sql_select);
    foreach ( $result as $row) {
 echo "<tr>";
 echo "<th>{$row['id']} </th>";
 echo "<th>{$row['sid']}</th>";
 echo "<th>{$row['name']}</th>";
 echo "<th>{$row['sex']} </th>";
 echo "<th>{$row['age']} </th>";
 echo "<th>{$row['class']}</th>";
 echo "<th>{$row['chinese']} </th>";
 echo "<th>{$row['math']} </th>";
 echo "<th>{$row['english']}</th>";
 echo "</tr>";
        }
}
 ?> 
</table>
 <div align='center' id='page'>
 <?php
    $to_sql="SELECT COUNT(*)FROM stu";
    $result= mysqli_query($conn,$to_sql);
    $row=mysqli_fetch_array($result);
    $count=$row[0];
    $to_pages=ceil($count/5);
     if($page<=1){
        echo "
            <a href='".$_SERVER['PHP_SELF']."?p=1'><button>上一页</button></a>&nbsp;&nbsp;";
        
        }else{
        echo "<a href='".$_SERVER['PHP_SELF']."?p=".($page-1)."'><button>上一页</button></a>&nbsp;&nbsp;";
    }
    if ($page<$to_pages){
        echo "<a href='".$_SERVER['PHP_SELF']."?p=".($page+1)."'><button>下一页</button></a>";
     
     
    }else{
        echo "<a href='".$_SERVER['PHP_SELF']."?p=".($to_pages)."'><button>下一页</button></a>";
    }
    ?>
</div>
</div>
</div>
<script>
        function doDel(id) {
            confirm('确认删除?')
        }
    </script>
</body>

</html>

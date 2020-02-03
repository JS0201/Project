<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>学生管理系统</title>
    <style>
    .body p{
             font-size:25px;
             font-family:楷书;
             color:rgb(98,94,91);
             text-align:center;

</style>
</head>
<body>
    <?php include ("public/menu.php");
 $id=$_GET['id'];
 header("content-type:text/html;charset=utf8");
 $conn=mysqli_connect("localhost","root","root","study");
    mysqli_set_charset($conn,"utf8");
 $sql_select = "select * from stu where id='$id'";
 $stmt = mysqli_query($conn,$sql_select);
 $stu = mysqli_fetch_assoc($stmt); 
 ?>
 <div align="center" class="body">
 <p>修改学生信息</p><br>
    <form action="edit1.php" method="post">
        <input type="hidden" name="id" value="<?php echo $stu['id'];?>">
        <table>
            <tr>
                <td>姓名</td>
                <td><input type="text" name="name" value="<?php echo $stu['name'];?>"></td>
            </tr>
            <tr>
                <td>年龄</td>
                <td><input type="text" name="age" value="<?php echo $stu['age'];?>"></td>
            </tr>
            <tr>
                <td>性别</td>
                <td>
                    <input type="radio" name="sex" value="男" <?php echo ($stu['sex'] == "男")? "checked":"";?> >男
 </td>
                <td>
                    <input type="radio" name="sex" value="女" <?php echo ($stu['sex'] == "女")? "checked":"";?> >女
 </td>
            </tr>
            <tr>
                <td>班级</td>
                <td><input type="text" name="class" value="<?php echo $stu['class']?>"></td>
            </tr>
            <tr>
                <td> </td>
                <td><input type="submit" value="修改"></td>
                <td><input type="reset" value="重置"></td>
            </tr>
        </table>
    </form>
</div>
</body>
</html>

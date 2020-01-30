<?php
      public function checkcode(){

        //获取提交过来的参数
          $verify_phone = ;   //手机号码
          $verify_sms_code = ;   //验证码
          
        //判断是否为当前发送验证码的手机号码
       if($verify_phone!==$_SESSION["sms_phone"]){
          //根据自己业务功能来提示或者跳转
          echo "<script>alert('要改密的手机与原先手机不一致!');window.location.href='register.php';</script>";
          return;
        }


        //判断验证码是否正确
        if(trim($verify_sms_code)!==$_SESSION['sms_code']){
            //根据自己业务功能来提示或者跳转
            echo "<script>alert('验证码输入错误!');window.location.href='register.php';</script>";
          return;
        }

            //业务操作成功之后进行验证码等清空操作
            unset($_SESSION['sms_code']);
            unset($_SESSION['sms_code_time']);
            unset($_SESSION['sms_phone']);
}


?>

<script type="text/javascript"> 

var wait=60;  
function time(o) {  
        if (wait == 0) {  
            o.removeAttribute("disabled");            
            o.value="获取验证码";  
            wait = 60;  
        } else {  
            o.setAttribute("disabled", true);  
            o.value="重新获取(" + wait + ")";  
            wait--;  
            setTimeout(function() {  
                time(o)  
            },  
            1000)  
        }  
    } 



    //短信发送点击事件
    //params
    //e：当前点击发送对象按钮
    function get_sms_code(e){   
      var phone = ;           //获取手机号码(必填)

      var params = {"phone":phone};//构造请求所需参数

      //手机号码健壮性验证
      var reg = /^1[3|4|5|7|8][0-9]{9}$/;
      if(!reg.test(phone)){

        //这里可做手机号码错误提示

        return false;
      }

      time(e);  //执行时间计算 60s

      //发送验证码 
      $.post("sms.php",params,function(data){
        alert(data);
      });
    }

</script>


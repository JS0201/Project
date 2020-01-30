<?php 

namespace Admin\Model;
use Think\Model;
class AdminUserModel extends Model{

   protected $_validate = array(
     array('username','require','请输入用户名',1,'',1), 
     array('username','','用户名不能重复',1,'unique',1), 
     array('password','require','请输入密码',1,'',1), 
   );

   
  private function check_username(){
	  $username=$_POST['username'];
	  
	  if(M('admin_user')->where(array('username'=>$username))->getfield('username')){
		  return false;
	  }
	  
  }
  
  
}

?>
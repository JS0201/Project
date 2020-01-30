<?php 

namespace Admin\Model;
use Think\Model;
class AdminModel extends Model{
	protected $tableName = 'admin_user'; 
	
	
	protected $_validate = array(
		 array('username','require','请输入用户名'), 
		 array('password','require','请输入密码'),
		 
	 );
	 
}

?>
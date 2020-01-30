<?php 

namespace Admin\Service;
use Think\Model;
class AdminService extends Model{
	protected $tableName = 'Admin_user'; 
	
	
	public function __construct() {
		$this->model = D('admin_user');
	
	}
	
	/**
     * 后台验证
     * @return string
     */
    public function init() {
		
		
        $authkey = session('authkey');
		
 
        if($authkey) {
			
			$admin_login=session('gboy_admin_login');
			
            list($admin_id, $authkey) = explode("\t", authcode($authkey, 'DECODE'));
			
			$_admin = $this->fetch_by_username($admin_login['username']);
			 
			 if(isset( $admin_login['id']) && isset($admin_login['username']) && isset($admin_login['encrypt'])){
				 
				if($_admin['id']== $admin_login['id'] &&  $_admin['username']== $admin_login['username'] && $_admin['encrypt']== $admin_login['encrypt']) {
						
						$admin_login['avatar'] = '';
						$admin_login['rules'] = M('admin_group')->where(array('id' => $admin_login['group_id']))->getField('rules');
						$admin_login['formhash'] = $authkey;
					
				}else{
					
					$admin_login='';
				}
				
			 }else{
				 $admin_login='';
				 
			 }
		
        }
       
        return $admin_login;
    }
	
	
	
	/* 权限验证 */
	public function auth($rules) {
		$rules = explode(",", $rules);		
		$_map = array();
		$_map['m'] = strtolower(MODULE_NAME);
		$_map['c'] = strtolower(CONTROLLER_NAME);
		$_map['a'] = strtolower(ACTION_NAME);
		
	
		
		$rule_id = M('node')->where($_map)->getField('id');
	
		if($rule_id && !in_array($rule_id, $rules)) {
			return false;
		}
		return true;
	}
	
	
    /**
     * 登录
     * @param string $username
     * @param string $password
     */
    public function login($username, $password) {
		
	
       if(empty($username)) {
            $this->errors = '用户名不能为空';
            return false;
        }
        if(empty($password)) {
            $this->errors = '密码不能为空';
            return false;
        }
		
        $admin_user = $this->fetch_by_username($username);
		
        if(!$admin_user) {
            $this->errors = '管理员不存在';
            return false;
        }
		
		
	 
		
        if($admin_user['password'] !== $this->create_password($password, $admin_user['encrypt'])) {
            $this->errors = '登陆密码验证失败';
            return FALSE;
        }
		
		$admin_user_info=array(
			
			'id'=>$admin_user['id'],
			'username'=>$admin_user['username'],
			'group_id'=>$admin_user['group_id'],
			'encrypt'=>$admin_user['encrypt'],
			'rules'=>'',
			'formhash'=>'',
			
		);
	 
		session('gboy_admin_login',$admin_user_info);	
        return $this->dologin($admin_user['id']);
    }

    /**
     * 登录
     * @param string $username
     * @param string $password
     */
    private function dologin($id) {		
        $auth = authcode($id."\t".  random(6), 'ENCODE');
		$this->model->where(array('id'=>$id))->data(array('last_login_time'=>time(),'login_num'=>array('exp','`login_num`+1')))->save();
        session('authkey', $auth);
        return true;
    }	
	

    /**
     * 退出登录
     * @return boolean
     */
    public function logout() {
        session('authkey', NULL);
        return TRUE;
    }
	
	
	/**
     * 获取管理员信息
     * @param string $username 用户名
     * @return array
     */
 	public function fetch_by_username($username) {
		
		return M('admin_user')->where(array("username" => $username))->find();
	}
	
	
	/**
     * 生成登陆密码
     * @param string $pwd 原始密码
     * @param string $encrypt 混淆字符
     * @return string
     */
    public function create_password($pwd, $encrypt = '') {
        if(empty($encrypt)) $encrypt = random (6);
		
		
		
		
        return md5($pwd.$encrypt);
    }
	
	
}

?>
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\admin\service;
use think\Model;
//use think\Session;
use think\Request;
use think\Loader;
class Admin extends Model{

    public function initialize(){

        $this->model=model('admin/Admin');
        $this->admin_user_service=model('admin/AdminUser','service');

    }

    public static function init() {
        $_admin = [
            'id' => 0,
            'group_id' => 0,
            'username'	=> '',
            'avatar'	=> '',
            'rules'		=> '',
            'formhash'	=> random(6)
        ];
        session_start();
        $authkey = session('gboy_admins_authkey');
        if($authkey) {
            list($admin_id, $authkey) = explode("\t", authcode($authkey, 'DECODE'));
            $_admin = model('admin/AdminUser')->find($admin_id)->toArray();
            if($_admin) {
                //$_admin['avatar'] = $this->getthumb($_admin['id']);
            }
            $_admin['rules'] = model('admin/AdminGroup')->where(array('id' => $_admin['group_id']))->value('rules');
            $_admin['formhash'] = $authkey;
        }
        return $_admin;

    }


    /**
     * @param $rules
     * @return bool
     */
    public function auth($rules) {


        
        $rules = explode(",", $rules);
        $module_name=Request::instance()->module();
        $controller_name=Request::instance()->controller();
        $action_name=Request::instance()->action();
        $_map = [];
        $_map['m'] = strtolower($module_name);
        $_map['c'] = strtolower(Loader::parseName($controller_name, 0, true));
        $_map['a'] = strtolower(Loader::parseName($action_name, 0, true));

        $rule_id = model('admin/Node')->where($_map)->value('id');

        if($rule_id && !in_array($rule_id, $rules)) {
            return false;
        }
        return true;
    }



    public function check_safe(){

        $auth=true;

        //计算机名验证

        /*
        $auth_computer_name=config('cache.auth_computer_name');
        if($auth_computer_name){

            $uname_arr=explode(' ',php_uname());
            $computer_name=$uname_arr[2];

            $computer_arr=explode(',',$auth_computer_name);
            if(!in_array($computer_name,$computer_arr)){
                    $auth=false;
            }
        }*/





        //IP验证
        $auth_computer_ip=config('cache.auth_computer_ip');
        if($auth_computer_ip){
            $computer_ip_arr=explode(',',$auth_computer_ip);
            if(!in_array(getip(),$computer_ip_arr)){
                    $auth=false;
            }
        }


        return $auth;

    }


    /**
     * 登录
     * @param string $username
     * @param string $password
     * @param string $code
     */
    public function login($username, $password,$code) {


        $data=[];
        $data['username']=$username;
        $data['password']=$password;
        $data['code']=$code;

        $admin_user = $this->model->getByUsername($username);

        if(!$admin_user) {
            $this->errors = lang('admin_user_not_exist');
            return FALSE;
        }

        if($admin_user['password'] !== $this->admin_user_service->create_password($password, $admin_user['encrypt'])) {
            $this->errors = lang('password_checked_error');
            return FALSE;
        }

        $this->admin_user = $admin_user;
        return $this->_dologin($admin_user['id']);
    }



    private  function _dologin($id) {

        $auth = authcode($id."\t".  random(6), 'ENCODE');
        $this->model->save(['last_login_time'=>time(),'last_login_ip'=>getip(),'login_num'=>['exp','`login_num`+1']],['id'=>$id]);
        session('gboy_admins_authkey', $auth);
        return true;
    }


    /**
     * 退出登录
     * @return boolean
     */
    public function logout() {
        session('gboy_admins_authkey', NULL);
        return TRUE;
    }


}
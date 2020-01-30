<?php

namespace Member\Service;

use Think\Model;

class MemberService extends Model {

    public function __construct() {

        $this->model = D('Member/Member');
    }

    /**
     * 初始化
     * @return [type] [description]
     */
    public function init(){
        $_member = array(
            'id' => 0,
            'username' => '游客',
            'group_id' => 0,
            'email' => '',
            'mobile' => '',
            'money' => 0,
            'integral' => 0,
            'exp' => 0
        );
        $authkey = cookie('member_auth');
        if ($authkey) {

            list($uid, $rand) = explode("\t", authcode($authkey));
            $_member = $this->model->uid($uid)->push()->address()->group()->output();
        }

        //runhook('member_init', $_member);
        return $_member;
    }

    /**
     * 注册
     * @param  array  $params 表单信息
     * @return mixed
     */
    public function register($params = array()) {

        if (empty($params)) {
            $this->errors = L('_error_action_');
            return false;
        }
        foreach ($params as $k => $v) {
            $method = '_valid_' . $k;
            if ($k == 'vcode') {
            } else {
                if (method_exists($this, $method) && $this->$method($v) === false)
                    return false;
            }
            $params[$k] = trim($v);
        }
        $data = array();
        $data['username'] = $params['username'];
        $data['mobile'] = $params['username'];
        $data['realname'] = $params['realname'];
        $data['pid'] = $params['pid'];
        $data['encrypt'] = random(6);
        $data['password'] = md5(md5($params['password']) . $data['encrypt']);
        $data['twopassword'] = md5(md5($params['twopassword']) . $data['encrypt']);
        $data['group_id'] = 0;
        $data['islock'] = 0;
        $data['card'] = $params['card'];//身份证
        $data['helpRememberWords'] = md5($params['helpRememberWords']);//助记词
        
        $data['quhao'] = $params['quhao'];//区号
        $data['path_id']=$params['path_id'];

		$data['recharge_code'] =md5($params['username'] . $data['encrypt']);   //助记词
        if ($data['pid']) {
            if (!$p_user = $this->find(array('id' => $data['pid'], 'islock' => 0))) {
                $this->errors = L('push_user_not_exits');
                return false;
            }
            $data['pid'] = $p_user['id'];
        }
        $map = array_merge($data, $this->sqlmap);
        $result = $this->model->create($map);
        if ($result === false) {
            $this->errors = L('_data_create_error_');
            return false;
        }
        if (!$insert_id = $this->model->add($result)) {
            $this->errors = L('register_error');
            return false;
        }
        $map['member_jd'] = $params['member_jd'];//接点人排位系统的主id
        $map['member_path'] = $params['member_path'];//接点人排位系统的path路径
        $map['bigposition'] = $params['member_sortid'];//1左2右
        $map['register_id'] = $insert_id;

        //调用钩子方法
        hook('after_register', $map);
       // hook('admin_level', $map);
        $this->dologin($insert_id);   //自动登录信息
        return true;
    }

    /**
     * 登录
     * @param string  $account 
     * @param string $password 
     * @return mixed
     */
    public function login($account, $password) {

        if (empty($account)) {
            $this->errors = L('login_username_empty');
            return false;
        }
        if (empty($password)) {
            $this->errors = L('login_password_empty');
            return false;
        }
        $member = array();
        hook('before_login', $member);
        if (empty($member)) {
            $sqlmap = array();
            if (is_mobile($account)) {
                $sqlmap['mobile'] = $account;
                $sqlmap['mobile_status'] = 1;
            } elseif (is_email($account)) {
                $sqlmap['email'] = $account;
                $sqlmap['email_status'] = 1;
            } else {
                $sqlmap['username'] = $account;
            }
            $sqlmap = array_merge($sqlmap, $this->sqlmap);
            $member = $this->model->where($sqlmap)->find();
            if (!$member || md5(md5($password) . $member['encrypt']) != $member['password']) {
                $this->errors = L('username_not_exist');
                return false;
            }
            if ($member['islock'] == 1) {
                $this->errors = L('user_ban_login');
                return false;
            }
        }
        $this->dologin($member['id']);
        hook('after_login', $member);
        return true;
    }

    /**
     * 自动登录信息
     * @param  int $mid 会员主键
     * @return [bool]
     */
    public function dologin($mid) {
        $rand = random(6);
        $auth = authcode($mid . "\t" . $rand, 'ENCODE');
        cookie('member_auth', $auth, 86400);
        $login_info = array(
            'id' => $mid,
            'login_time' => time(),
            'login_ip' => getip(),
            'login_num' => login_num + 1,
            'member_auth' => $auth,
        );
        $this->model->save($login_info);

        return true;
    }

    /* 校验用户名 */

    private function _valid_username($value) {

        if (strlen($value) < 3 || strlen($value) > 15) {
            $this->errors = L('username_length_require');
            return false;
        }

        $reg_user_censor = C('reg_user_censor');
        $censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($reg_user_censor = trim($reg_user_censor)), '/')) . ')$/i';
        if ($reg_user_censor && @preg_match($censorexp, $value)) {
            $this->errors = L('username_disable_keyword');
            return false;
        }

        /* 检测重名 */
        if ($this->model->where(array("username" => $value))->count() || $this->model->where(array("mobile" => $value,))->count()) {
            $this->errors = L('username_exist');
            return false;
        }
        return true;
    }

    /* 校验密码 */

    private function _valid_password($value) {

        $reg_pass_lenght = max(3, (int) C('reg_pass_lenght'));
        $reg_pass_complex = unserialize(C('reg_pass_complex'));
        if (strlen($value) < $reg_pass_lenght) {
            $this->errors = '密码至少为 ' . $reg_pass_lenght . ' 位字符';
            return false;
        }
        if ($reg_pass_complex) {
            $strongpws = array();
            if (in_array('num', $reg_pass_complex) && !preg_match("/\d/", $value)) {
                $strongpws[] = '数字';
            }
            if (in_array('small', $reg_pass_complex) && !preg_match("/[a-z]/", $value)) {
                $strongpws[] = '小写字母';
            }
            if (in_array('big', $reg_pass_complex) && !preg_match("/[A-Z]/", $value)) {
                $strongpws[] = '大写字母';
            }
            if (in_array('sym', $reg_pass_complex) && !preg_match("/[^a-zA-z0-9]+/", $value)) {
                $strongpws[] = '特殊字符 ';
            }
            if ($strongpws) {
                $this->errors = '密码必须包含' . implode(',', $strongpws);
                return false;
            }
        }
        return true;
    }


        /**
     * 修改登陆密码
     * @param int $mid
     * @param string $old_pwd
     * @param string $new_pwd
     */
    public function updata_pwd($mid, $old_pwd , $new_pwd) {

        if($old_pwd==''){

            $this->error('请输入原始密码');
            return false;

        }

        $user=$this->find(array('id'=>$mid),'id,password,encrypt');
          // print_r($user);die;

        $old_pwd=md5(md5($old_pwd) . $user['encrypt']);

        if($old_pwd!=$user['password']){

            $this->errors = L('password_newpassword');
            return false;

        }else{

            $updata=md5(md5($new_pwd) . $user['encrypt']);//post过来的新密码

            M('member')->where(array('id'=>$mid))->setField('password',$updata);

        }
        return true;

    }

    /**
     * @param  array    sql条件
     * @param  integer  读取的字段
     * @return [type]
     */
    public function find($sqlmap = array(), $field = "") {
        $result = $this->model->where($sqlmap)->field($field)->find();

        if (!$result) {

            return false;
        }
        return $result;
    }

    /**
     * [save 更新数据]
     * @param  [type] $params [参数]
     * @return [type]         [description]
     */
    public function save($params) {
        if (empty($params)) {
            $this->errors = L('_params_error_');
            return false;
        }
        
        if(!$info=$this->find(array('id'=>$params['id']))){
            $this->errors = L('_params_error_');
        }
            //如果传过来的用户名和 查出来的用户名一样
        if($params['username'] == $info['username']){

            unset($params['username']);

            if(empty($params['password'])){
                unset($params['password']);
            }else{
                $params['password']=md5(md5($params['password']) . $info['encrypt']);
            }

            if(empty($params['twopassword'])){
                unset($params['twopassword']);
            }else{
                $params['twopassword']=md5(md5($params['twopassword']) . $info['encrypt']);
            }

            if(empty($params['group_id'])){
                $params['group_id'] = 0;
                $data=array(
                    'uid'=>$params['id'],
                    'old_grade'=>$info['group_id'],
                    'new_grade'=>$params['group_id'],
                    'set_time'=>time(),
                );
                M('setgrade')->add($data);
            }else{
                $data=array(
                    'uid'=>$params['id'],
                    'old_grade'=>$info['group_id'],
                    'new_grade'=>$params['group_id'],
                    'set_time'=>time(),
                );
                M('setgrade')->add($data);
            }
        }else{
            $username=$this->find(array('username'=>$params['username']));
            if($params['username'] == $username['username']){    //判断是否有同样的用户
                $this->errors = L('_params_username_error_');
                return false;

            }
            $params['username']=$params['username'];
            $params['mobile']=$params['username'];
            $params['update_time']=time();


            if(empty($params['password'])){
                unset($params['password']);
            }else{
                $params['password']=md5(md5($params['password']) . $info['encrypt']);
            }

            if(empty($params['twopassword'])){
                unset($params['twopassword']);
            }else{
                $params['twopassword']=md5(md5($params['twopassword']) . $info['encrypt']);
            }

            if(empty($params['group_id'])){
                $params['group_id'] = 0;
                $data=array(
                    'uid'=>$params['id'],
                    'old_grade'=>$info['group_id'],
                    'new_grade'=>$params['group_id'],
                    'set_time'=>time(),
                );
                M('setgrade')->add($data);
            }else{
                $data=array(
                    'uid'=>$params['id'],
                    'old_grade'=>$info['group_id'],
                    'new_grade'=>$params['group_id'],
                    'set_time'=>time(),
                );
                M('setgrade')->add($data);
            }
			 $data=array(
                'uid'=>$params['id'],
                'old_phone'=>$info['username'],
                'new_phone'=>$params['username'],
                'set_time'=>time(),
            );
            M('setphone')->add($data);
        }



        $result = $this->model->save($params);
        if ($result === false) {
            $this->errors = $this->model->getError();
            return false;
        }
        return $result;
    }



        /**
     * 变更用户账户
     * @param int $mid
     * @param string $type
     * @param int $num
     * @param boolean $iswritelog
     * @return boolean 状态
     */
    public function change_account($mid, $field = 'money', $num, $msg = '', $iswritelog = TRUE) {
        $fields = array('money','member_z_bi','member_bi','usdt_wallet');
	
        if (!in_array($field, $fields)) {
            $this->errors =L('_params_error_');
            return FALSE;
        }
		
        switch($field){
            case  'money':
                $types = 'money';
                break;
            case  'member_bi':
                $types = 'member_bi';
                break;
            case  'member_z_bi':
                $types = 'member_z_bi';
                break;
            case  'usdt_wallet':
                $types = 'usdt_wallet';
                break;
        }

        switch($field){
            case  'money':
                $types_mi = 'money_keys';
                break;
            case  'member_bi':
                $types_mi = 'mother_currency';
                break;
            case  'member_z_bi':
                $types_mi = 'currency';
                break;
            case  'usdt':
                $types_mi = 'u_currency';
                break;
        }


        $finance=M('money_finance');//财务报表
        $encryption=M('secure_encryption');//财务密钥表
        $finance_d=$finance->where(array('uid'=>$mid))->find();
        if ($finance_d) {
            if ($finance_d[$types] > 0 ) {//如果里面的金额 > 0
                $keys=$encryption->where(array('uid'=>$mid))->find();//查出该会员密钥表里的数据
                $x_keys=md5(md5($finance_d[$types]) . $keys['encrypt']);//安全加密

                if (true) {
                    //$finance->where(array('uid'=>$mid))->setInc('money',$row['money']);//累加现金账户余额
                    if (strpos($num, '-') === false && strpos($num, '+') === false)
                        $num = '+' . $num;
                    if (strpos($num, '-') === false) {
                        $result = $finance->where(array('uid' => $mid))->setInc($field, $num);
                    } else {
                        $value = $finance->where(array('uid' => $mid))->getField($field);
                        if (abs($num) > $value) {
                            $result = $finance->where(array('uid' => $mid))->setField($field, 0);
                        } else {
                            $result = $finance->where(array('uid' => $mid))->setInc($field, $num);
                        }
                    }
                    $data_t=$finance->where(array('uid'=>$mid))->getField($types);//查出现金余额
                    $keys_u=$encryption->where(array('uid'=>$mid))->find();//查出该会员密钥表里的数据
                    $money_key = md5(md5($data_t) . $keys_u['encrypt']);//安全加密
                    $encryption->where(array('uid'=>$mid))->setField(array($types_mi=>$money_key));//写入密钥表
                    //$this->member_upgrade($mid);//会员等级升级
                    //print_r($num);exit;
                }else{

                    switch($field){
                        case  'money':
                            $msgt = 'ACNY';
                            break;
                        case  'member_bi':
                            $msgt = 'HMK';
                            break;
                        case  'member_z_bi':
                            $msgt = 'VSC';
                            break;
                        case  'usdt':
                            $msgt = 'USDT';
                            break;

                    }
                    $log_data=array(
                        'uid'=>$row['uid'],
                        'text'=>'后台执行会员'.$msgt.'充值操作时，发现此会员账户余额数据异常，停止给此会员执行充值操作！',
                        'time'=>time(),
                    );
                    M('exception_log')->data($log_data)->add();
                    exit('<script>alert("此会员"'.$msgt.'"账户余额数据异常，请联系此会员了解情况！");location.href="'.U('Member/Member/index').'"</script>');
                }

            }else{
                //$finance->where(array('uid'=>$mid))->setInc('money',$row['money']);//累加现金账户余额
                if (strpos($num, '-') === false && strpos($num, '+') === false)
                    $num = '+' . $num;
                if (strpos($num, '-') === false) {
                    $result = $finance->where(array('uid' => $mid))->setInc($field, $num);
                } else {
                    $value = $finance->where(array('uid' => $mid))->getField($field);
                    if (abs($num) > $value) {
                        $result = $finance->where(array('uid' => $mid))->setField($field, 0);
                    } else {
                        $result = $finance->where(array('uid' => $mid))->setInc($field, $num);
                    }
                }

                $keys_u=$encryption->where(array('uid'=>$mid))->find();//查出该会员密钥表里的数据
                $data_t=$finance->where(array('uid'=>$mid))->getField($types);//查出现金余额
                $money_key = md5(md5($data_t) . $keys_u['encrypt']);//安全加密
                $encryption->where(array('uid'=>$mid))->setField(array($types_mi=>$money_key));//写入密钥表
                //$this->member_upgrade($mid);//会员等级升级
            }

        }else{

            $data_x['uid']=$mid;
            $data_x['time']=time();
            $finance->data($data_x)->add();//写入密钥表
            //$finance->where(array('uid'=>$mid))->setInc($types,$row['money']);//累加现金账户余额
            if (strpos($num, '-') === false && strpos($num, '+') === false)
                $num = '+' . $num;
            if (strpos($num, '-') === false) {
                $result = $finance->where(array('uid' => $mid))->setInc($field, $num);
            } else {
                $value = $finance->where(array('uid' => $mid))->getField($field);
                if (abs($num) > $value) {
                    $result = $finance->where(array('uid' => $mid))->setField($field, 0);
                } else {
                    $result = $finance->where(array('uid' => $mid))->setInc($field, $num);
                }
            }

            $data_i=$finance->where(array('uid'=>$mid))->getField($types);//查出现金余额
            $data['uid'] = $mid;
            $data['encrypt'] = random(6);//6位随机数令牌
            $data[$types_mi] = md5(md5($data_i) . $data['encrypt']);//安全加密
            $encryption->data($data)->add();//写入密钥表
            //$this->member_upgrade($mid);//会员等级升级
        }


        if ($result === FALSE) {
            $this->errors = L('_os_error_');
            return FALSE;
        }

        switch($field){
            case  'money':
                $msgg = 'ACNY';
                break;
            case  'member_bi':
                $msgg = 'HMK';
                break;
            case  'member_z_bi':
                $msgg = 'VSC';
                break;
            case  'usdt':
                $msgg = 'USDT';
                break;

        }

        $text=$msgg.','.$msg;//充值说明

        if ($iswritelog === TRUE) {
            $_member = $this->model->uid($mid)->output();
            $log_info = array(
                'mid' => $mid,
                'value' => $num,
                'type' => $field,
                'msg' => $text,
                'dateline' => time(),
                'admin_id' => (defined('IN_ADMIN')) ? ADMIN_ID : 0,
                'admin_user' => (defined('IN_ADMIN')) ? ADMIN_USER : '',
                'money_detail' => json_encode(array($field => sprintf('%.2f', $_member[$field])))
            );
            M('member_log')->data($log_info)->add();
        }

        return TRUE;
    }


  

	 //会员子链转母链升级
    public function member_upgrade($id){

        $member_group=M('member_group');//会员等级表
        $money_finance=M('money_finance');//会员个人财务表
        $mb=$money_finance->where(array('uid'=>$id))->find();//当前会员财务信息
        $dj_4=$member_group->where(array('id'=>'5'))->find();
        $dj_3=$member_group->where(array('id'=>'4'))->find();
        $dj_2=$member_group->where(array('id'=>'3'))->find();
        $dj_1=$member_group->where(array('id'=>'2'))->find();
        $dj_0=$member_group->where(array('id'=>'1'))->find();
        $pid_nunber=M('member')->where(array('pid'=>$id,'gou'=>'1'))->count();//统计直推人数
        //个人业绩等级升级
        $exist=$money_finance->where(array('uid'=>$id))->find();
        
        if($dj_4['min_points'] <= $mb['member_bi']){
            //如果母积分大于等于10000个，等级修改为星耀会员
            if ($exist['grade']!=5) {
                $money_finance->where(array('uid'=>$id))->setField('grade','5');
            }
        }elseif($dj_3['min_points'] <= $mb['member_bi'] && $mb['member_bi'] < $dj_3['max_points']){
            //如果母积分大于等于7000，小于10000个，等级修改为钻石会员
            if ($exist['grade']!=4) {
                $money_finance->where(array('uid'=>$id))->setField('grade','4');
            }
        }elseif ($dj_2['min_points'] <= $mb['member_bi'] && $mb['member_bi'] < $dj_2['max_points']) {
            //如果母积分大于等于4500，小于7000个，等级修改为黄金会员
            if ($exist['grade']!=3) {
                $money_finance->where(array('uid'=>$id))->setField('grade','3');
            }
        }elseif ($dj_1['min_points']<=$mb['member_bi'] && $mb['member_bi'] < $dj_1['max_points']) {
            //如果母积分大于等于2000，小于4500个，等级修改为白银会员
            if ($exist['grade']!=2){
                $money_finance->where(array('uid'=>$id))->setField('grade','2');
            }
        }elseif ($dj_0['min_points']<=$mb['member_bi'] && $mb['member_bi'] < $dj_0['max_points']) {
            //如果母积分大于等于500，小于2000个，等级修改为普通会员
            if ($exist['grade']!=1){
                $money_finance->where(array('uid'=>$id))->setField('grade','1');
            }
        }else{
            //降为普通会员等级
            if ($exist['grade']!=0){
                $money_finance->where(array('uid'=>$id))->setField('grade','0');
                M('member')->where(array('id'=>$id))->setField('gou','0');//把会员修改为游客
            }
            
        }
        





    }
	
	
    /**
     * 删除指定会员
     * @param type $id
     */
    public function del($sqlmap) {
        /*
          $ids = (array) $id;
          hook('member_info_del',$ids);
          foreach($ids AS $id) {

          $this->model->where($sqlmap)->delete();

          }
         */
        $this->model->where($sqlmap)->delete();
        return TRUE;
    }

    /**
     * 退出登录
     */
    public function logout() {
        hook('after_logout');
        return cookie('member_auth', null);
    }

         /**
     * 锁定[解锁]指定会员
     * @param type $id
     * $type int [是否锁定 1:锁定 0: 解锁]
     */
     public function togglelock_by_id($id,$type) {
         $ids = (array) $id;
         $data = array();
         $data['islock'] = ((int)$type) > 1 ? 1 : $type;
         $result = $this->model->where(array('id'=>array('in',$ids)))->save($data);
         if($result == false){
               $this->errors = L('_os_error_');
             return FALSE;
         }
         return TRUE;
     }

}

?>
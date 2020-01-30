<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\service;
use think\Db;
use think\Hook;
use think\Model;
class Member extends Model{


    protected  $reg_password='';
    protected  $pid=0;

    public function initialize()
    {
       $this->model = model('member/Member');
       $this->moneyFinance = model('member/Finance');
       $this->MemberLog = model('member/MemberLog');
       $this->recharge = model('member/RechangeCheck');
    }


    /**
     * 用户初始化
     * @return array
     */
    public function inits() {

        $_member = [
            'id' => 0,
            'username' => '游客',
            'group_id' => 0,
            'email' => '',
            'mobile' => '',
            'money' => 0,
            'integral' => 0,
            'exp' => 0
        ];
        $authkey = cookie('gboy_member_auth');
        if ($authkey) {
            list($uid, $rand, $login_key) = explode("\t", authcode($authkey));
            $_member = $this->get_find(['id'=>$uid]);
            if($_member){
                $_member=$_member->toArray();
            }
            $finance = DB::table('gboy_money_finance')->where(array('uid' => $_member['id']))->find();
            $_member['grade'] = $finance['grade'];
            $_member['group_name'] = config('configs.group_id')[$_member['group_id']];
            $_member['agency_level'] = $finance['agency_level'];
            $_member['finance_money'] = $finance['money'];
            $_member['shop_integral'] = $finance['shop_integral'];
        }
        //hook('memberInit',$_member);
        return $_member;
    }
    public function sqlmap($params)
    {
        $sqlmap=[];

        if($params['group_id']){
            $sqlmap['group_id'] = $params['group_id'];
        }
        if($params['keywords']){
            $keywords=trim($params['keywords']);
            $sqlmap['username'] = ['like','%'.$keywords.'%'] ;
        }
        return $sqlmap;
    }

    public function get_list($sqlmap,$page=[]){

        $lists = $this->model->where($sqlmap)->order('id desc')->paginate($page);
        return $lists;
    }
    public function updated($where, $data)
    {
        return $this->model->updated($where, $data);
    }

    public function get_find($sqlmap=[],$field=''){

        if(!$result=$this->model->where($sqlmap)->field($field)->find()){
            $this->errors=lang('_param_error_');
            return false;
        }


        return $result;

    }


    public function register(array $params){
        Hook::listen('registerInit',$params);
        if($params['reg_status']===0){
            $this->errors = $params['reg_error'];
            return false;
        }

        if(empty($params)) {
            $this->errors = lang('_param_error_');
            return false;
        }

        foreach( $params as $k => $v ) {
            $method = '_valid_'.$k;
            if($k !== 'vcode'){
                if(method_exists($this,$method) && $this->$method($v) === false) return false;
            }
            $params[$k] = trim($v);
        }

        $reg_user_fields = unserialize(config('cache.reg_user_fields'));
        $data = $params;
        if(in_array('phone',$reg_user_fields)){
            $sqlmap = [];
            $sqlmap['mobile'] = $params['mobile'];
            $sqlmap['dateline'] = ['EGT',time()-300];
           /* $vcode = db('vcode')->where($sqlmap)->order('dateline desc')->value('vcode');
            if($vcode != $params['vcode']){
                $this->errors = lang('captcha_error');
                return false;
            }else{
                $data['mobile_status'] = 1;
            }*/
            $data['mobile_status'] = 1;
        }
        if(!isset($data['username']) && $data['mobile']){
            $data['username']=$data['mobile'];
        }
        $data['pid']=$this->pid;
        $data['encrypt'] = random(6);
        $data['register_ip']=getip();
        $data['register_time']=time();
        $data['password'] = $this->create_password($data['password'],$data['encrypt']);
        $data['face'] = '/static/images/face/def.jpg'; //默认头像
        hook('before_register',$data);

        if($data['_callback'] === false){
            $this->errors = $params['_message'];
            return false;
        }
        $this->model->startTrans();
        $this->model->allowField(true)->isUpdate(false)->data($data)->save();
        $data['reg_id']=$this->model->getLastInsID();
        $moneyData = array('uid' => $data['reg_id'], 'grade' => 1, 'agency_level' => 1, 'time' => time());
        $money_id = $this->moneyFinance->allowField(true)->isUpdate(false)->data($moneyData)->save();
        $encry_id = $this->encryption($data['reg_id']);
        if(!$data['reg_id'] && !$money_id && !$encry_id){
            $this->model->rollBack();
            $this->errors='注册失败，重新再试';
            return false;
        }
        $this->model->commit();
        hook('afterRegister',$data);
        hook('checkCart',$data['reg_id']);
        $this->dologin($data['reg_id']);
        return true;
    }


    public function login($account, $password, $captch) {
        if (empty($account)) {
            $this->errors = lang('userphone_not_exist');
            return false;
        }
        if(!is_mobile($account)) {
            $this->errors = lang('userphone_error');
            return false;
        }
        if (empty($password)) {
            $this->errors = lang('login_password_empty');
            return false;
        }
        if(empty($captch)) {
            $this->errors = lang('code_not_exist');
            return false;
        }
        /*if(!captcha_check($captch)) {
            $this->errors = lang('code_checked_error');
            return false;
        }*/
        // hook('before_login', $member);
        $member = $this->model->where("username = {$account}")->find();

        if (!$member || $this->create_password($password,$member['encrypt']) != $member['password']) {
            $this->errors = lang('username_not_exist');
            return false;
        }
        if ($member['islock'] == 1) {
            $this->errors = lang('user_ban_login');
            return false;
        }
        hook('checkCart', $member['id']);
        $this->dologin($member['id']);
        return true;
    }
    /**
     * 生成用户资产加密数据
     */
    public function encryption($uid)
    {
        if(!$uid) return false;
        $encrypt = random(6);
        $str = md5(md5(0.00) . $encrypt);
        $data['uid'] = $uid;
        $data['encrypt'] = $encrypt;
        $data['money'] = $data['mother_currency'] = $data['currency'] = $data['shop_integral'] = $data['other_account_key'] = $str;
        model('member/Encryption')->add($data);
        return true;
    }

    /**
     * 判断并实现会员自动升级
     * @param  int $mid 会员主键
     * @return [bool]
     */
    public function dologin($mid) {
        if((int) $mid < 1){
            $this->errors = lang('_param_error_');
            return FALSE;
        }

        $rand = random(6);
        $login_key=random(32);
        $auth = authcode($mid."\t".$rand."\t".$login_key, 'ENCODE');

        $out_time=config('cache.member_login_out');

        $out_time=$out_time ? $out_time : null;


        cookie('gboy_member_auth', $auth, $out_time);
        $login_info = array(
            'id' => $mid,
            'login_key'=>$login_key,
            'login_time' => time(),
            'login_ip'	=> getip(),
            'login_num' => 'login_num' + 1
        );
        $this->model->isUpdate(true)->save($login_info);
        return true;
    }


    public function edit($data, $isupdate = false, $valid = true, $msg = []){

        if($isupdate){
            $encrypt=$this->where(['id'=>$data['id']])->value('encrypt');
            unset($data['username']);
            if(!$data['password']){
                unset($data['password']);
            }else{
                $data['password']=$this->create_password($data['password'],$encrypt);
            }
        }else{
            $data['encrypt']=random(6);
            $data['password']=$this->create_password($data['password'],$data['encrypt']);

        }

        $result = $this->model->except('id')->validate($valid, $msg)->isupdate($isupdate)->save($data);

        if ($result === false) {
            $this->errors = $this->model->getError();
            return false;
        }
        // 生成log记录
        $code = $isupdate ? $data['id'] : $this->model->id;
        $name = $isupdate ? lang('admin_log_member_edit') :  lang('admin_log_member_add');
        $contetn = $data['username'] ? ('用户名：' . $data['username']) : '';
        AdminLog(2, $contetn, lang("admin_log_member"), $name, $code);

        return true;
    }

    public function setMoney($data, $admin_id)
    {
        if(!$data) {
            $this->errors = lang('_param_error_');
            return false;
        }
        if(intval($data['set_money']) != $data['set_money']) {
            $this->errors = '更改现金积分必需为整数';
            return false;
        }
        if(intval($data['set_shop']) != $data['set_shop']) {
            $this->errors = '更改消费积分必需为整数';
            return false;
        }
        $ismoney = $isshop = false;
        if($data['set_money'] != 0) { //有现金积分改动
            $ismoney = true;
            $moneySeyType = $data['set_money'] > 0 ? true : false;
        }
        if($data['set_shop'] != 0) { //有消费积分改动
            $isshop = true;
            $shopSetType = $data['set_shop'] > 0 ? true : false;
        }
        $service = new MoneyFinance();
        $re_money = $re_shop = true;
        if($ismoney) {
            $transaction_type = $moneySeyType ? 10 : 11;
            $re_money = $service->setFinance($data['id'], abs($data['set_money']), 'money', '后台更改现金积分',$admin_id, $moneySeyType, $transaction_type, '', 0);
        }
        if($isshop) {
            $transaction_type = $shopSetType ? 10 : 11;
            $re_shop = $service->setFinance($data['id'], abs($data['set_shop']), 'shop_integral', '后台更改消费积分',$admin_id, $shopSetType, $transaction_type, '', 0);
        }
        if(!$re_money || !$re_shop) {
            $this->errors = $service->errors;
            return false;
        }
        return true;
    }
    /**
     * 退出登录
     * @return mixed
     */
    public function logout() {
        hook('after_logout');
        return cookie('gboy_member_auth', null);
    }



    /**
     * @param array $ids id主键
     * @return bool
     */
    public function del($sqlmap){

        if(empty($sqlmap)){
            $this->errors = lang('_param_error_');
            return false;
        }

        $this->model->destroy($sqlmap);
        // 生成log记录
        $code = AdminLoginFormat($sqlmap);
        AdminLog(2, '', lang("admin_log_member"), lang('admin_log_member_delete'), $code);

        return true;
    }


    /**
     * 登录密码
     * @param $password 密码
     * @param $encrypt 令牌
     * @return bool|string
     */
    public function create_password($password,$encrypt) {


        $salt = '$2a$11$' . substr(md5($password.$encrypt), 5, 23);
        return substr(crypt($password, $salt),7);

    }



    /* 校验用户名 */
    private function _valid_username($value) {
        if(strlen($value) < 3 || strlen($value) > 15) {
            $this->errors = lang('username_length_require');
            return false;
        }

        $setting = config('cache');

        $censorexp = '/^('.str_replace(['\\*', "\r\n", ' '], ['.*', '|', ''], preg_quote(($setting['reg_user_censor'] = trim($setting['reg_user_censor'])), '/')).')$/i';
        if($setting['reg_user_censor'] && @preg_match($censorexp, $value)) {
            $this->errors = lang('username_disable_keyword');
            return false;
        }

        /* 检测重名 */
        if($this->model->where(["username" => $value])->count() || $this->model->where(["email" => $value])->count() || $this->model->where(["mobile" => $value])->count()) {
            $this->errors = lang('username_exist');
            return false;
        }
        return true;
    }


    private function _valid_password($value) {
        $setting = config('cache');
        $reg_pass_lenght = max(3, (int) $setting['reg_pass_lenght']);
        $reg_pass_complex = $setting['reg_pass_complex'];
        if(strlen($value) < $reg_pass_lenght ) {
            $this->errors = '密码至少为 '. $reg_pass_lenght. ' 位字符';
            return false;
        }
        if($reg_pass_complex) {
            $strongpws = [];
            if(in_array('num',$reg_pass_complex) && !preg_match("/\d/",$value)){
                $strongpws[] = '数字';
            }
            if(in_array('small',$reg_pass_complex) && !preg_match("/[a-z]/",$value)){
                $strongpws[] = '小写字母';
            }
            if(in_array('big',$reg_pass_complex) && !preg_match("/[A-Z]/",$value)){
                $strongpws[] = '大写字母';
            }
            if(in_array('sym',$reg_pass_complex) && !preg_match("/[^a-zA-z0-9]+/",$value)){
                $strongpws[] = '特殊字符 ';
            }
            if($strongpws){
                $this->errors = '密码必须包含'.implode(',', $strongpws);
                return false;
            }
        }

        $this->reg_password=$value;

        return true;
    }


    private function _valid_repassword($value) {

        if($value==''){
            $this->errors='请输入确认密码';
            return false;
        }

        if($value!==$this->reg_password){
            $this->errors='确认密码不一致';
            return false;
        }

        return true;
    }


    public function _valid_pid($value){

        if(empty($value)){
            $this->errors='请输入推荐人';
            return false;
        }
        $sqlmap=[];
        if (is_mobile($value)) {
            $sqlmap['mobile'] = $value;
            $sqlmap['mobile_status'] = 1;
        }  else {
            $sqlmap['username'] = $value;
        }

        $sqlmap['islock']=0;
        $pid=$this->where($sqlmap)->value('id');

        if(!$pid){
            $this->errors='推荐人不存在';
            return false;
        }

        $this->pid=$pid;


        return true;

    }


    public function _valid_email($value) {
        if(!is_email($value)) {
            $this->errors = lang('email_format_error');
            return false;
        }

        if($this->model->where(['email'=>$value])->count()) {
            $this->errors = lang('email_format_exist');
            return false;
        }
        return true;
    }

    public function _valid_mobile($value) {
        if(!is_mobile($value)) {
            $this->errors = lang('mobile_format_error');
            return false;
        }
        if($this->model->where(['mobile'=>$value])->count(1)) {
            $this->errors = lang('mobile_format_exist');
            return false;
        }
        return true;
    }



    /**
     * 变更用户账户
     * @param int $mid
     * @param string $type
     * @param int $num
     * @param boolean $iswritelog
     * @return boolean 状态
     */
    public function change_account($mid, $field ,$num, $msg = '',$iswritelog = TRUE) {
        $fields = ['money', 'exp', 'integral'];
        if(!in_array($field, $fields)) {
            $this->errors=lang('_param_error_');
            return false;
        }
        if(strpos($num, '-') === false && strpos($num, '+') === false) $num = '+'.$num;
        if(strpos($num, '-') === false){
            //累计
            $result = $this->model->where(['id' => $mid])->setInc($field,$num);
        }else{

            $value = $this->model->where(['id'=>$mid])->value($field);
            if(abs($num) > $value){
                //不足归0
                $result = $this->model->where(['id' => $mid])->setField($field,0);
            }else{
                //递减
                $result = $this->model->where(['id' => $mid])->setInc($field,$num);
            }
        }

        if($result === false) {
            $this->errors = lang('_operation_fail_');
            return FALSE;
        }
        if($iswritelog === true) {

            $_member=$this->model->where(['id'=>$mid])->field($fields)->find()->toArray();


            $log_info = array(
                'mid'      => $mid,
                'value'    => $num,
                'type'     => $field,
                'msg'      => $msg,
                'dateline' => time(),
                'admin_id' => (defined('IN_ADMIN')) ? ADMIN_ID : 0,
                'money_detail' => json_encode(array($field => sprintf('%.2f' ,$_member[$field])))
            );
            model('member/MemberLog')->data($log_info)->save();
        }

        return TRUE;
    }

    //用户交易详情
    public function tradedetail($userid, $start, $limit)
    {
        $return = array();
        $transaction_style = config('configs.transaction_style');
        $list = model('member/MemberLog')->getList("mid = {$userid} and style in {$transaction_style}",$start,$limit);
        if($list) {
            foreach($list as $k => $v) {
                $list[$k]['dateline'] = date('Y-m-d H:i:s', $v['dateline']);
                if($v['fromid']) {
                    $user = $this->model->getOne("id = {$v['fromid']}");
                    $list[$k]['username'] = $user['realname'];
                }else{
                    $list[$k]['username'] = '';
                }
                if($v['style'] == 2) { //直推收益
                    $return['recomment'][$k] = $list[$k];
                }
                if($v['style'] == 3) { //团队收益
                    $return['team'][$k] = $list[$k];
                }
            }
            $return['all'] = $list;
        }

        return $return;
    }

    //充值互转明细
    public function rechargeDetail($uid)
    {
        $return['list'] = $return['recharge'] = array();
        $list = $this->MemberLog->getAll("mid = {$uid} and style in (5,6,7)", '*');
        if($list) { //交易日志表
            $transaction_type = config('configs.transaction_type');
            foreach($list as $k => $v) {
                $list[$k]['created'] = date('Y-m-d H:i:s', $v['dateline']); //到账时间
                if($v['signid']) {
                    $recharge = $this->recharge->getOne("id = {$v['signid']} and status > 0");
                    if(!$recharge) {
                        unset($list[$k]);
                        continue;
                    }
                    $list[$k]['apply_time'] = date('Y-m-d H:i:s' ,$recharge['created']); //申请时间
                    $list[$k]['transaction_text'] = $transaction_type[$v['style']];
                }else{
                    $list[$k]['apply_time'] = $list[$k]['created']; //申请时间
                    $list[$k]['transaction_text'] = $transaction_type[$v['style']];
                }
                if($v['style'] < 7) { //互转
                    $return['turn'][$k] = $list[$k];
                }else{  //充值
                    $return['recharge'][$k] = $list[$k];
                }
                $return['list'][$k] = $list[$k];
            }
        }
        $recharge_list = $this->recharge->getAll("uid = {$uid} and status in (1,3) and type = 1"); //充值申请
        if($recharge_list) {
            foreach($recharge_list as $k => $v) {
                $recharge_list[$k]['transaction_text'] = $v['status'] == 1 ? '审核中' : '审核失败';
                $recharge_list[$k]['value'] = $v['money'];
                $recharge_list[$k]['created'] = $v['checktime'] ? date('Y-m-d H:i:s', $v['checktime']) : '';
                $recharge_list[$k]['apply_time'] = date('Y-m-d H:i:s' ,$v['created']); //申请时间
            }
        }
        //按申请时间排序
        $return['list'] = array_merge($recharge_list, $return['list']);
        $return['recharge'] = array_merge($recharge_list, $return['recharge']);
        array_multisort(array_column($return['list'],'apply_time'),SORT_DESC,$return['list']);
        array_multisort(array_column($return['recharge'],'apply_time'),SORT_DESC,$return['recharge']);
        return $return;
    }

    //获得团队信息
    public function getTeam($id,$uid)
    {
        $result = array();
        if(!$id) {
            $user = $this->model->getOne("id = {$uid}");
            $isParent = $this->model->count("pid = {$uid}");
            $result[0]['name'] = $user['username'].'|'.$user['realname'];
            $result[0]['id'] = 1;
            $result[0]['pid'] = 0;
            $result[0]['ids'] = $uid;
            $result[0]['isParent'] = $isParent;
            $result[0]['icon'] = $isParent ? '/template/wap/statics/js/ztree/images/user_business.png' : '/template/wap/statics/js/ztree/images/user.png';
        }else{
            $list = $this->model->getAll("pid = {$uid}", 'id,pid,username, realname');
            if($list) {
                $i = 1;
                foreach($list as $k => $v) {
                    $isParent = $this->model->count("pid = {$v['id']}");
                    $result[$k]['name'] = $v['username'].'|'.$v['realname'];
                    $result[$k]['id'] = $id.$i++;
                    $result[$k]['pid'] = $uid;
                    $result[$k]['ids'] = $v['id'];
                    $result[$k]['isParent'] = $isParent;
                    $result[$k]['icon'] = $isParent ? '/template/wap/statics/js/ztree/images/user_business.png' : '/template/wap/statics/js/ztree/images/user.png';
                }
            }
        }
        return $result;
    }
    //团队人数
    public function getCount($uid)
    {
        return $this->model->where("path_id like '%,{$uid},%'")->count();
    }
    //重置密码
    public function resetpw($uid, $request)
    {
        if(!$request) {
            $this->errors = lang('_param_error_');
            return false;
        }
        if(!$request['newpassword']) {
            $this->errors = '新密码不能为空';
            return false;
        }
        if(strlen($request['newpassword']) < 6 || strlen($request['newpassword']) > 16) {
            $this->errors = lang('password_error_length');
            return false;
        }
        if($request['newpassword'] !== $request['repassword']) {
            $this->errors = '两次密码不一至';
            return false;
        }
        $encrypt = $this->model->getValue("id = {$uid}", 'encrypt');
        $password = $this->create_password($request['password'], $encrypt);
        if(!$this->model->getValue("id = {$uid} and password = '{$password}'",'id')) {
            $this->errors = '原始密码错误';
            return false;
        }
        $newpassword = $this->create_password($request['newpassword'], $encrypt);
        $re = $this->model->updated("id = {$uid}",array('password'=>$newpassword));
        if($re === false) {
            $this->errors = lang('_operation_fail_');
            return false;
        }
        return true;
    }
}
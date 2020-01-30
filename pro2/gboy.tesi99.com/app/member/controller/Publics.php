<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\controller;
use app\common\controller\Base;
use think\Cookie;
class Publics extends Base{

    public function __construct()
    {
        parent::__construct();
        if(cookie('gboy_member_auth') && ACTION_NAME != 'admin_login' ) {
            $this->redirect('/member/index/order');
        }
        $this->service=model('member/Member','service');
    }



    public function login(){

        if(is_ajax()){
            $phone = input('post.phone');
            $password = input('post.password');
            $captch = input('post.captcha');
            if(!$this->service->login($phone, $password, $captch)){
                showmessage($this->service->errors);
            }
            showmessage('登录成功',url('member/index/index'),1);
        }else{
            return $this->fetch();
        }


    }
    public function admin_login()
    {
        if($_GET['id']) {
            Cookie::delete('gboy_member_auth');
            $this->service->dologin($_GET['id']);
            $this->redirect('/member/index/order');
        }
    }

    public function register(){

        if(is_ajax()){

            $post=input('post.','');
            $data=[];
            $data['mobile']=trim($post['mobile']);
            $data['password']=trim($post['password']);
            $data['repassword']=trim($post['repassword']);
            $data['pid']=trim($post['pid']);
            //$data['vcode']=trim($post['vcode']);
            if(!$this->service->register($data)){
                showmessage($this->service->errors);
            }
            showmessage('注册成功',url('member/index/index'),1);

        }else{
            $mobile = input('get.mobile');
            if($mobile) {
                $this->assign('mobile', $mobile);
            }
            return $this->fetch();
        }
    }

    public function code(){

        return captcha('member');
    }

    public function getcode() {
        if(is_post()) {
            $mobile = input('post.mobile');
            if(!is_mobile($mobile)) {
                showmessage(lang('userphone_error'));
            }
            $code1=mt_rand(1000,9999);
            $code=sha1(md5(trim($code1)));
            $config = model('notify/Notify')->where("code = 'sms' and enabled = 1")->value('config');
            $config = json_decode($config, true);
            $id = DB('vcode')->insertGetId(array(
                'mid' => 0,
                'mobile' => $mobile,
                'vcode' => $code1,
                'action' => '注册',
                'dateline' => time()
            ));
            if(!$id) {
                showmessage(lang('验证码发送失败'));
            }
            $re = send_sms($mobile,"【{$config['sms_sign']}】您注册的验证码为：{$code1}，请不要把短信验证码泄露给他人，如非本人操作可不用理会",
                                                                                    $config['sms_id'], $config['sms_account'], $config['sms_password']);
            if(!$re) {
                showmessage(lang('验证码发送失败'));
            }
            showmessage(lang('_operation_success_'), '',1);
        }
    }
}
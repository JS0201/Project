<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\admin\controller;
use app\common\controller\Init;
use think\Session;
class Login extends Init{

    public function _initialize()
    {

        if(request()->action()!='captcha'){

            if(input('go')!=='adminqcgs'){
                error_status(404);
            }
        }

        define('IN_ADMIN',true);

        $this->service = model('admin/Admin','service');

        if(request()->action()!='logout'){
            $admin=$this->service->init();
            if($admin['id'] > 0) {
                $this->redirect('admin/index/index');
            }
        }
    }

    public function index(){
        if(IS_POST()){

            $data=input('post.');
            $result = $this->service->login($data['username'], $data['password'],$data['code']);
            if($result === false) {
                showmessage($this->service->errors);
            } else {
                //session::set('gboy_admins_authkey',1);
                $admin=$this->service->init();
                $this->redirect('admin/index/index',['formhash'=>$admin['formhash']]);
            }
        }else{
            return $this->fetch();
        }


    }


    public function logout(){



        $this->service->logout();
        header("Cache-control:no-cache,no-store,must-revalidate");
        header("Pragma:no-cache");
        header("Expires:0");
        $this->redirect(url('admin/login/index',['go'=>'adminqcgs']));
    }


    public function captcha(){
        ob_clean();
        $captcha = new \think\captcha\Captcha();
        $captcha->imageW=290;
        $captcha->imageH = 60;  //图片高
        $captcha->fontSize =30;  //字体大小
        $captcha->length   = 5;  //字符数
        $captcha->fontttf = '5.ttf';  //字体
        $captcha->expire = 30;  //有效期
        $captcha->useNoise = true;  //不添加杂点
        return $captcha->entry();
    }
}
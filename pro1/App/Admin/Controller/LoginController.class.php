<?php

namespace Admin\Controller;

use Common\Controller\AdminController;

class LoginController extends AdminController {

    public function _initialize() {
           define('IN_ADMIN','true');
        if ($_GET['go'] != 'qcgs') {
            //验证是否从后台入口文件登录
            _404();
        }
    }

    public function index() {

        if (IS_POST) {

            $this->service = D('Admin/Admin', 'Service');

            if (!check_verify(I('post.code'), 1)) {
                showmessage('验证码不正确',U('index',array('go'=>'qcgs')));
            }


            $result = $this->service->login($_POST['username'], $_POST['password']);

            if (!$result) {
                showmessage($this->service->errors,U('index',array('go'=>'qcgs')));
            } else {

                $admin_info = $this->service->init();

                redirect(U('index/index', array('formhash' => $admin_info['formhash'])));
            }
        } else {

            $this->display();
        }
    }

    //验证码
    public function code() {
        $config = array(
            'fontSize' => 30, // 验证码字体大小
            'length' => 4, // 验证码位数
            'useNoise' => true, // 关闭验证码杂点
            'useImgBg' => false,
            'fontttf' => '5.ttf',
        );
        $Verify = new \Think\Verify($config);

        $Verify->entry(1);
    }

}

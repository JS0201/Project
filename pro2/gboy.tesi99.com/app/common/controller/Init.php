<?php
namespace app\common\controller;
//use think\Session;
class Init extends Common
{

	public function _initialize()
    {

        //config('url_html_suffix','');
        //config('pathinfo_depr','-');

        //Route::domain('admin.sys.demo.com','admin');

       /* if(!session::get("gboy_adminislogin")){
            error_status(404);
        }*/

        parent::_initialize();



        $admin_model=model('admin/Admin','service');
        if(false==$admin_model->check_safe()){
           exit('ACCESS');
        }

        $this->admin =$admin_model->init();
        if(!$this->admin['id']) {
            session('gboy_admins_authkey', null);
            $this->redirect(url('admin/login/index',['go'=>'adminqcgs']));
        }
        define('IN_ADMIN',true);
        define('ADMIN_ID', $this->admin['id']);
        define('FORMHASH', $this->admin['formhash']);
        $this->assign('admin',$this->admin);

/*
        if(empty($this->admin['id'])) {
            //$this->redirect('admin/login/logout');
            showmessage(lang('no_login'),url('admin/login/logout'));
        }*/

		//权限菜单
      /*  if(!$admin_model->auth($this->admin['rules']) && $this->admin['group_id']!==1){
            exit('权限不足');
        }*/


        if(input('formhash')!==FORMHASH) {

           // exit('asdf');
           //showmessage('formhash不正确');
        }



	}	
   
}

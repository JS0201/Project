<?php

namespace app\admin\controller;

use app\common\controller\Init;
use think\Request;

class AdminUser extends Init
{
    //初始化

    public function _initialize()
    {
        parent::_initialize();

        $this->service = model('admin/AdminUser', 'service');
        $this->group_service = model('admin/AdminGroup', 'service');

    }


    public function index()
    {

        $list = $this->service->get_lists();

        $this->assign('list', $list);
        return $this->fetch();
    }


    public function add()
    {


        if(Request()->isPost()) {


            if(!$this->service->allowField(['username'])->edit(Request::instance()->post())) {
                showmessage($this->service->errors, url('index'), 1);
            }
            showmessage(L('update_admin_group_success', 'admin'), url('index'));


        } else {

            $group = $this->group_service->get_select_data();
            $this->assign('group', $group);
            return $this->fetch('edit');
        }


    }


    public function edit()
    {


        if(!$info = $this->service->fetch_by_id(request()->param('id/d'))) {
            showmessage($this->service->errors);
        }


        if(Request()->isPost()) {

            $validate = new \app\admin\validate\AdminUser();


            if(!$validate->scene('edit')->check(Request::instance()->post())) {
                showmessage($validate->getError());
            }


            if(!$this->service->edit(Request::instance()->post(), true, false)) {

                showmessage($this->service->errors);

            }
            showmessage(Lang('_operation_success_'), url('index'), 1);

        } else {

            $group = $this->group_service->get_select_data();
            $this->assign('info', $info);
            $this->assign('group', $group);
            return $this->fetch();

        }

    }


    public function delete()
    {

        if(!$this->service->del(Request::instance()->param('id/a'))) {
            showmessage($this->service->errors);
        }

        showmessage(lang('_operation_success_'), url('index'), 1);
    }


}
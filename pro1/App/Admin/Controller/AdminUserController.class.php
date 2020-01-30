<?php

namespace Admin\Controller;

use Common\Controller\AdminController;

class AdminUserController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->model = D('Admin_user');
        $this->service = D('Admin_user', 'Service');
        $this->admin_service = D('Admin', 'Service');
        $this->group_service = D('Admin_group', 'Service');
    }

    public function index() {
        $uid = $_SESSION['gboy_admin_login']['id'];
        $gid = M("admin_user")->where(array('id'=>$uid))->getField('group_id');
        $sqlmap["group_id"]=array("egt",$gid);
        $list = $this->service->getAll($sqlmap);

        $group_array = $this->group_service->get_select_data();
        foreach ($list as $k => $v) {
            $list[$k]['group_name'] = $group_array[$v['group_id']];
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function add() {
        $uid = $_SESSION['gboy_admin_login']['id'];

        $gid = M("admin_user")->where(array('id'=>$uid))->getField('group_id');
        $sqlmap["id"]=array("gt",$gid);   //id>当前登录管理员group_id
        $sqlmap["status"]=1;
        $group = $this->group_service->get_select_data($sqlmap);

        $this->assign('group', $group);
        $this->display('edit');
    }

    public function edit() {
        $group = $this->group_service->get_select_data();
        $data = current($this->service->getAll(array('id' => $_GET['id'])));
        $this->assign('data', $data);
        $this->assign('group', $group);
        $this->display('edit');
    }

    public function send() {

        if ($_POST) {

            $r = $this->service->save($_POST);
            if (!$r) {
                showmessage($this->service->errors);
            } else {
                showmessage('设置角色成功', U('index'), 1);
            }
        }
    }

    public function del() {

        $id = (array) $_REQUEST['id'];

        if (in_array(1, $id))
            showmessage('admin帐号不能删除');
        $this->model->where(array('id' => array('IN', $id)))->delete();
        showmessage('删除成功', U('index'), 1);
    }

    public function logout() {
        $this->admin_service->logout();

        showmessage('安全退出', U('login/index', array('go' => $_GET['go'])), 1);
    }

}

<?php

namespace Admin\Controller;

use Common\Controller\AdminController;

class AdminGroupController extends AdminController {

    public function _initialize() {
        parent::_initialize();

        $this->model = D('Admin_group');
        $this->service = D('Admin_group', 'Service');

        //节点
        $this->node = D('Node', 'Service');
    }

    public function index() {
        $uid = $_SESSION['gboy_admin_login']['id'];
        $gid = M("admin_user")->where(array('id'=>$uid))->getField('group_id');
        $sqlmap["id"]=array("egt",$gid);
        $list = $this->service->getAll($sqlmap);
        $this->assign('list', $list);
        $this->display();
    }

    public function add() {

        $nodes = list_to_tree($this->node->get_checkbox_data());
        $this->assign('nodes', $nodes);
        $this->display('edit');
    }

    public function edit() {
        $nodes = list_to_tree($this->node->get_checkbox_data());
       // var_dump($nodes);
        $data = current($this->service->getAll(array('id' => $_GET['id'])));
        $data['rules'] = explode(',', $data['rules']);
        $this->assign('data', $data);
        $this->assign('nodes', $nodes);
        $this->display();
    }

    public function send() {

        if ($_POST) {

            if (array_key_exists("rules", $_POST))
                $_POST['rules'] = implode($_POST['rules'], ',');
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
            showmessage('超级管理员不能删除');
        $this->model->where(array('id' => array('IN', $id)))->delete();
        showmessage('删除成功', U('index'), 1);
    }

    public function ajax_status() {
        $id = $_REQUEST['id'];

//        if ((int) $id == 1)
//            showmessage('超级管理员不能操作');
        if ($this->service->change_status($id)) {
            echo 1;
        } else {
            echo 0;
        }
    }

}

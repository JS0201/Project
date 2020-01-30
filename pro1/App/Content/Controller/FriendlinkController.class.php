<?php

namespace Content\Controller;

use Common\Controller\AdminController;

class FriendlinkController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->model = D('Friendlink');
        $this->service = D('Friendlink', 'Service');
    }

    /**
     * [index 列表]
     */
    public function index() {
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $count = $this->model->where($sqlmap)->order("sort DESC")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("sort DESC")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();

        $pages = $this->admin_pages($count, $_GET['limit']);
        $this->assign('list', $list);

        $this->assign('pages', $pages);
        $this->display();
    }

    /**
     * [add 添加]
     */
    public function add() {

        $this->display('edit');
    }

    /**
     * [edit 编辑]
     */
    public function edit() {
        $info = $this->service->get_by_id($_GET['id']);


        $this->assign('info', $info);
        $this->display();
    }

    /**
     * [send 提交]
     */
    public function send() {

        if ($_POST) {

            if (!empty($_FILES['logo']['name'])) {
                $file = uploads($_FILES['logo']);
                $_POST['logo'] = $file['file_url'];
            }

            $r = $this->service->save($_POST);
            if (!$r) {
                showmessage($this->service->errors);
            }

            showmessage('操作成功', U('index'), 1);
        }
    }

    /**
     * [delete 删除]
     */
    public function del() {

        $result = $this->service->del($_REQUEST['id[]']);
        if (!$result) {
            showmessage($this->service->errors);
        }
        showmessage('删除成功', U('index'), 1);
    }

    /**
     * [ajax_edit ajax编辑]
     */
    public function ajax_edit() {
        $result = $this->service->ajax_edit($_POST);
    }

}

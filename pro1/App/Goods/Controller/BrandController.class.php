<?php

namespace Goods\Controller;

use Common\Controller\AdminController;

class BrandController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->service = D('Goods/Brand', 'Service');

        $this->attachment_service = D('Attachment/Attachment', 'Service');
    }

    public function index() {

        $result = $this->service->select();

        $lists = array(
            'th' => array('name' => array('title' => '品牌名称', 'length' => 20, 'style' => 'ident'), 'descript' => array('title' => '品牌描述', 'length' => 50), 'sort' => array('style' => 'double_click', 'length' => 10, 'title' => '排序'), 'isrecommend' => array('title' => '推荐', 'style' => 'ico_up_rack', 'length' => 10)),
            'lists' => $result['lists'],
            'pages' => $this->admin_pages($result['count'], $result['limit']),
        );


        $this->assign('lists', $lists)->display();
    }

    public function add() {


        $this->display('edit');
    }

    public function edit() {
        $info = $this->service->find(array('id' => $_GET['id']));

        $this->assign('info', $info)->display();
    }

    public function save() {

        if (!empty($_FILES['logo']['name'])) {

            $_POST['logo'] = $this->attachment_service->upload($_FILES['logo']);
        }

        if (!$this->service->save(I('post.'))) {
            showmessage($this->service->errors);
        } else {
            showmessage(L('_os_success_'), U('index'), 1);
        }
    }

    /**
     * [ajax_status 品牌列表内更改规格状态]
     */
    public function ajax_recommend() {
        $result = $this->service->change_recommend($_REQUEST['id']);
        if (!$result) {
            showmessage($this->service->errors, '', 0, '', 'json');
        } else {
            showmessage(L('_os_success_'), '', 1, '', 'json');
        }
    }

    /**
     * [ajax_sort 改变排序]
     */
    public function ajax_sort() {
        $result = $this->service->change_sort($_REQUEST);
        if (!$result) {
            showmessage($this->service->error, '', 0, '', 'json');
        } else {
            showmessage(L('_os_success_'), '', 1, '', 'json');
        }
    }

    /**
     * [ajax_sort 改变名称]
     */
    public function ajax_name() {
        $result = $this->service->change_name($_REQUEST);
        if (!$result) {
            showmessage($this->service->errors, '', 0, '', 'json');
        } else {
            showmessage(L('_os_success_'), '', 1, '', 'json');
        }
    }

    /**
     * [ajax_del 删除]
     */
    public function ajax_del() {
        $id = (array) $_REQUEST['id'];
        $sqlmap = array();
        $sqlmap['id'] = array('in', $id);
        if (!$this->service->del($sqlmap)) {
            showmessage($this->service->errors);
        }

        showmessage(L('_os_success_'), U('index'), 1);
    }

}

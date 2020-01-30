<?php

namespace Goods\Controller;

use Common\Controller\AdminController;

class SpecController extends AdminController {

    public function _initialize() {
        parent::_initialize();


        $this->service = D('Goods/Spec', 'Service');
    }

    public function index() {

        $result = $this->service->select();

        $lists = array(
            'th' => array('name' => array('style' => 'double_click', 'title' => '规格名称', 'length' => 20), 'value' => array('title' => '规格属性', 'length' => 50), 'sort' => array('style' => 'double_click', 'length' => 10, 'title' => '排序'), 'status' => array('title' => '启用', 'style' => 'ico_up_rack', 'length' => 10)),
            'lists' => $result['lists'],
            'pages' => $this->admin_pages($result['count'], $result['limit']),
        );

        $this->assign('lists', $lists)->assign('pages', $lists['pages'])->display();
    }

    public function add() {


        $this->display('edit');
    }

    public function edit() {
        $spec = $this->service->find(array('id' => $_GET['id']));

        $this->assign('spec', $spec)->display();
    }

    public function save() {
        if (!$this->service->save($_POST, array('id' => $_POST['id']))) {
            showmessage($this->service->errors);
        } else {
            showmessage(L('_os_success_'), U('index'), 1);
        }
    }

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

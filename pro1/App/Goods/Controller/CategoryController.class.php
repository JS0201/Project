<?php

namespace Goods\Controller;

use Common\Controller\AdminController;

class CategoryController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->service = D('Goods/Goods_category', 'Service');
    }

    /**
     * [index 分类列表]
     */
    public function index() {

        $sqlmap = array();
        $sqlmap['parent_id'] = 0;

        $result = $this->service->select($sqlmap);

        $lists = array(
            'th' => array('sort' => array('title' => '排序', 'length' => 10, 'style' => 'level_sort'), 'name' => array('title' => '名称', 'length' => 55, 'style' => 'level_name'), 'type_name' => array('length' => 15, 'title' => '关联属性'), 'status' => array('title' => '启用', 'style' => 'ico_up_rack', 'length' => 10)),
            'lists' => $result['lists'],
            'pages' => $this->admin_pages($result['count'], $result['limit']),
        );


        $this->assign('lists', $lists)->display();
    }

    /**
     * [add 添加]
     */
    public function add() {

        if ($_GET['pid']) {
            //$categorys = $this->service->get_parent($_GET['pid']);
            //array_unshift($categorys,$_GET['pid']);
            //$parent_name = $this->service->create_cat_format($categorys,TRUE);
            //$cat_format = $this->service->create_format_id($categorys,TRUE);

            $sqlmap = array();
            $sqlmap['id'] = $_GET['pid'];

            if (!$data = $this->service->find($sqlmap)) {

                showmessage($this->service->errors);
            }
            $parent_name = $this->service->get_parent_name($data['id']);


            $this->assign('parent_name', $parent_name);
            $this->assign('cat_format', $data['path_id']);
        }

        $this->display('edit');
    }

    public function edit() {
        $sqlmap = array();
        $sqlmap['id'] = $_GET['id'];
        if (!$data = $this->service->find($sqlmap)) {

            showmessage($this->service->errors);
        }

        $parent_row = $this->service->find(array('id' => $data['parent_id']));

        $cat_format = $parent_row['path_id'] ? $parent_row['path_id'] : 0;

        $parent_name = $this->service->get_parent_name($data['parent_id'], true);

        $this->assign('cate', $data)->assign('parent_name', $parent_name)->assign('cat_format', $cat_format)->display();
    }

    /**
     * [ajax_category ajax获取分类]
     * @return [type] [description]
     */
    public function ajax_category() {
        $result = $this->service->ajax_category($_GET['id']);
        if (!$result) {
            showmessage($this->service->errors, '', 0, '', 'json');
        } else {

            showmessage(L('_os_success_'), '', 1, $result, 'json');
        }
    }

    /**
     * [category_choose 分类列表]
     */
    public function choose() {

        $category = $this->service->get_category_tree();
        $this->assign('category', $category)->display();
    }

    public function send() {

        if (!empty($_FILES['img']['name'])) {
            $file = uploads($_FILES['img']);
            
        
            $_POST['img'] = $file['file_url'];
        }
        
        if (!$this->service->save($_POST)) {
            showmessage($this->service->errors);
        }

        showmessage(L('_os_success_'), U('index'), 1);
    }

    /**
     * [ajax_del 删除分类]
     */
    public function ajax_del() {

        $sqlmap = array();

        $id = $_GET['id'];
        $sqlmap['path_id'] = array('like', '%,' . $id . ',%');

        $result = $this->service->del($sqlmap);

        showmessage(L('_os_success_'), U('index'), 1);
    }

    /**
     * [ajax_name ajax更改名称]
     * @return [type] [description]
     */
    public function ajax_name() {

        $result = $this->service->change_info($_POST);
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

        $result = $this->service->change_info($_POST);
        if (!$result) {
            showmessage($this->service->errors);
        } else {
            showmessage(L('_os_success_'));
        }
    }

    /**
     * [ajax_status 分类列表内更改规格状态]
     */
    public function ajax_status() {
        $result = $this->service->change_status($_POST['id']);
        if (!$result) {
            showmessage($this->service->errors, '', 0, '', 'json');
        } else {
            showmessage(L('_os_success_'), '', 1, '', 'json');
        }
    }

}

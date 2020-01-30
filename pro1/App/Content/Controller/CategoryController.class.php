<?php

namespace Content\Controller;

use Common\Controller\AdminController;

class CategoryController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->model = D('Category');
        $this->service = D('Category', 'Service');
    }

    /**
     * [index 分类列表]
     */
    public function index() {

        $sqlmap = array();
        $sqlmap['parent_id'] = 0;

        $category = $this->model->where($sqlmap)->order("sort DESC")->select();

        $this->assign('category', $category);
        $this->display();
    }

    /**
     * [ajax_sun_class ajax获取分类]
     */
    public function ajax_son_class() {

        $result = $this->service->ajax_son_class($_GET);
		
	
        if (!$result) {
            showmessage($this->service->errors, '', 0, '', 'json');
        } else {
            showmessage('', '', 1, $result, 'json');
        }
    }

    /**
     * [add 添加分类]
     */
    public function add() {

        if (!empty($_GET['parent_id'])) {
            $parent_info = $this->service->get_category_by_id($_GET['parent_id']);
            $info['parent_name'] = $parent_info['parent_name'];
            $info['modelid'] = $parent_info['modelid'];
            $info['category_template'] = $parent_info['category_template'];
            $info['list_template'] = $parent_info['list_template'];
            $info['show_template'] = $parent_info['show_template'];
            $this->assign('info', $info);
        }
        $this->display('edit');
    }

    /**
     * [edit_category 编辑分类]
     */
    public function edit() {


        $info = $this->service->get_category_by($_GET['id']);
        $parent_name = $this->service->get_category_by_id($_GET['id'], 'parent_name');
        if (empty($info['parent_id'])) {
            $parent_name = '顶级分类';
        }
        $info['parent_name'] = $parent_name;
        $this->assign('info', $info);

        $this->display();
    }

    /**
     * [send 提交]
     */
    public function send() {

        if ($_POST) {

            if (!empty($_FILES['thumb']['name'])) {
                $file = uploads($_FILES['thumb']);
                $_POST['thumb'] = $file['file_url'];
            }


            $r = $this->service->save($_POST);
            if (!$r) {
                showmessage($this->service->errors);
            }

            showmessage('操作成功', U('index'), 1);
        }
    }

    /**
     * [category_choose 分类列表]
     */
    public function category_choose() {

        $category = $this->service->get_category_tree();
        $this->assign('category', $category);
        $this->display();
    }

    /**
     * [del 删除分类]
     */
    public function del() {

        $result = $this->service->del($_REQUEST['id']);
        if (!$result) {
            showmessage($this->service->errors);
        }

        showmessage('删除成功', U('index'), 1);
    }

    /**
     * [ajax_edit 编辑分类名称]
     */
    public function ajax_edit() {
        $result = $this->service->ajax_edit($_POST);
    }

    /**
     * [select_tpl 选择模板]
     */
    public function select_tpl() {

        $result = $this->service->select_tpl($_POST['modelid'], $_POST['id']);

        echo json_encode($result);
    }

}

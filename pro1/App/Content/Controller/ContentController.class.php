<?php

namespace Content\Controller;

use Common\Controller\AdminController;

class ContentController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->model = D('Content');
        $this->service = D('Content', 'Service');
        $this->category_service = D('Category', 'Service');
    }

    /**
     * [category_choose 选择框]
     */
    public function category_choose() {
		
        $category = $this->category_service->get_category_tree();

        $this->assign('category', $category);
        $this->display('/Category_category_choose');
    }

    /**
     * [index 文章列表]
     */
    public function index() {

        $sqlmap = array();

        if ($_GET['cid']) {
            $sqlmap['path_id'] = array('like', '%,' . $_GET['cid'] . ',%');
        }

        if ($_GET['keyword']) {
            $sqlmap['title'] = array('like', '%' . $_GET['keyword'] . '%');
        }

        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $count = $this->model->where($sqlmap)->order("sort DESC,id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("sort DESC,id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();

        foreach ($list as $key => $value) {
            $list[$key]['category'] = M('category')->where(array('id' => array('eq', $value['category_id'])))->getField('name');
        }
        $category = $this->category_service->get_category_tree();
        $pages = $this->admin_pages($count, $_GET['limit']);
        $this->assign('list', $list);
        $this->assign('category', $category);
        $this->assign('pages', $pages);
        $this->display();
    }

    /**
     * [add 添加内容]
     */
    public function add() {

        $this->display('edit');
    }

    /**
     * [edit 编辑]
     */
    public function edit() {


        $info = $this->service->get_content_by_id($_GET['id']);

        $this->assign('info', $info);

        $this->display();
    }

    /**
     * [send 提交]
     */
    public function send() {

        if ($_POST) {

            /* 内容图片 */
            if (!empty($_FILES['thumb']['name'])) {
                $file = uploads($_FILES['thumb']);
                $_POST['thumb'] = $file['file_url'];
            }
            /* 二维码图片 */
            if (!empty($_FILES['erweima']['name'])) {
                $file = uploads($_FILES['erweima']);
                $_POST['erweima'] = $file['file_url'];
            }


            $r = $this->service->save($_POST);
            if (!$r) {
                showmessage($this->service->errors);
            }

            showmessage('操作成功', U('index'),1);
        }
    }

    /**
     * [del 删除分类]
     */
    public function del() {

        $result = $this->service->del($_REQUEST['id']);
        if (!$result) {
           showmessage($this->service->errors);
        }

        showmessage('删除成功', U('index'),1);
    }

    /**
     * [ajax_edit 编辑文章]
     */
    public function ajax_edit() {

        $this->service->ajax_edit($_POST);
    }

    /**
     * [move_data 批量更新数据]
     */
    public function move_data() {

        if ($_POST) {
            
        } else {

            //$this->display();
        }
    }

}

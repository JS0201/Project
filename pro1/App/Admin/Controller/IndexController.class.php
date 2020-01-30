<?php

namespace Admin\Controller;

use Common\Controller\AdminController;

class IndexController extends AdminController {

    public function _initialize() {
        parent::_initialize();

        $this->node_model = M('node');
        $this->menu_model = M('admin_menu');
        $this->admin_menu_service = D('admin_menu', 'Service');
    }

    public function index() {

        /* 自定义菜单 */
        $menus = $this->admin_menu_service->getAll($_SESSION['gboy_admin_login']['id']);

        //导航菜单
        $menu = $this->node_model->where(array('parent_id' => 0, 'status' => 1))->order('sort desc,id asc')->select();
        $this->assign('node', $menu);
        $this->assign('menus', $menus);
        $this->display();
    }

    public function home() {
	
//		if($_SESSION['gboy_admin_login']['id']!=1){
//
//			exit();
//
//		}
        $this->display();
    }

    public function menu() {


        $menus = $this->admin_menu_service->getAll($_SESSION['gboy_admin_login']['id']);

        $this->assign('menus', $menus);
        $this->display();
    }

    //添加自定义菜单
    public function ajax_menu_add() {

        //if(empty($_GET['formhash']) || $_GET['formhash'] != FORMHASH) showmessage('_token_error_');
        $_m = $_c = $_a = '';
        $data = array();
        extract($_POST, EXTR_IF_EXISTS);

        $data['admin_id'] = $_SESSION['gboy_admin_login']['id'];
        $data['url'] = U("$_m/$_c/$_a");
        $data['title'] = $this->node_model->where(array('m' => $_m, 'c' => $_c, 'a' => $_a))->getField('name');

        if ($this->menu_model->where($data)->count() == 0) {


            $this->menu_model->add($data);

            showmessage('快捷菜单添加成功', '', 1);
        }
        showmessage('您已添加过此快捷菜单');
    }

    //删除自定义菜单
    public function ajax_diymenu_del() {
        //if(empty($_GET['formhash']) || $_GET['formhash'] != FORMHASH) showmessage('_token_error_');
        $ids = '';
        extract($_POST, EXTR_IF_EXISTS);
        $ids = explode(',', $ids);
        $this->menu_model->where(array('id' => array('IN', $ids)))->delete();
    }

    //获取自定义菜单
    public function ajax_menu_refresh() {
        //if(empty($_GET['formhash']) || $_GET['formhash'] != FORMHASH) showmessage('_token_error_');
        $menus = $this->admin_menu_service->getAll($_SESSION['gboy_admin_login']['id']);
        $html = '';
        foreach ($menus as $k => $v) {
            $html .= "<li><a href='{$v["url"]}'>{$v["title"]}</a></li> ";
        }

        echo $html;
    }

}

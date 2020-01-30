<?php

namespace Admin\Controller;

use Common\Controller\AdminController;

class TemplateController extends AdminController {

    public function _initialize() {
        parent::_initialize();

        $this->service = D('Template', 'Service');
    }

    public function index() {
        $tpls = $this->service->fetch_all();

        $conf = require('App/Home/Conf/config.php');

        $this->assign('thename', $conf['DEFAULT_THEME']);
        $this->assign('tpls', $tpls);
        $this->display();
    }

}

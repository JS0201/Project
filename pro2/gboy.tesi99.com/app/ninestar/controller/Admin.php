<?php

namespace app\ninestar\controller;
use app\common\controller\Init;

class Admin extends Init{

    public function _initialize()
    {
        parent::_initialize();
        $this->service = model('ninestar/admin','service');
    }

    //创客列表
    public function userlist() {
        $list = $this->service->userlist();
        $this->assign('list',$list);
        return $this->fetch();
    }
    //审核列表
    public function examine() {
        $list = $this->service->examinelist();
        $this->assign('list',$list);
        return $this->fetch();
    }

}
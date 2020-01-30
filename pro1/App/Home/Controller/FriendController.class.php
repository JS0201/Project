<?php

namespace Home\Controller;

use Common\Controller\BaseController;

class FriendController extends BaseController {

    public function _initialize(){
        parent::_initialize();
       
    }

    public function index(){
       
        $this->display();
    }
    

}

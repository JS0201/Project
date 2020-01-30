<?php

namespace Peer\Controller;
use Member\Controller\CheckController;
class IndexController extends CheckController {

    public function _initialize() {
        parent::_initialize();
     
    }

    public function index() {
        $uid = $this->member["id"];
        $wallet_address=M('member')->where(array('id'=>$uid))->getField('wallet_address');
        $this->assign('wallet_address',$wallet_address);
        $this->display();
    }

}

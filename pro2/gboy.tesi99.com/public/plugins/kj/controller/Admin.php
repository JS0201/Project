<?php
namespace plugins\kj\controller;

use app\common\controller\Init;
class Admin extends Init
{

    public function _initialize()
    {
        parent::_initialize();

    }

    public function index(){

       return  $this->fetch('/admin_index');
    }


}
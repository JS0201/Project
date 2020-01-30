<?php

/**
 * Created by gaorenhua.
 * User: 597170962 <597170962@qq.com>
 * Date: 2015/6/28
 * Time: 0:19
 */

namespace Member\Controller;

use Common\Controller\BaseController;
use Think\Controller;
use think\Db;
use think\config;

class ApiController extends BaseController{
    public function index(){

    }
    //获取助记词
    public function getword()
    {
        printf(getworden());

    }
    
}
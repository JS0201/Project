<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\content\controller;
use app\common\controller\Base;

class Index extends Base {

    public function _initialize()
    {
        parent::_initialize();

        $this->category_service = model('content/Category','service');
        $this->service = model('content/Content','service');
    }



    public  function index(){

        return $this->fetch();

    }



}
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\statistics\controller;
use app\common\controller\Init;

class Member extends Init{

    public function _initialize()
    {

        parent::_initialize();
        $this->service = model('statistics/Member','service');
    }




    public function index(){

        $this->assign('member',$this->service->count());
        return $this->fetch();
    }




    public function ajax_getdata(){
        $row = $this->service->build_data(input('get.'));
        echo json_encode($row);
    }



}
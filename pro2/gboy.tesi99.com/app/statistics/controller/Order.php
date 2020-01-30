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

class Order extends Init{

    public function _initialize()
    {

        parent::_initialize();
        $this->service = model('statistics/Order','service');
    }




    public function index(){
		$datas = $this->service->build_sqlmap(array('days' => 7))->output('sales');
        $this->assign('statistics',$datas);
        return $this->fetch();
    }




    public function ajax_getdata(){
		$days = (int) input('days');
		$start_time = strtotime(input('start_time'));
		$end_time = (input('end_time')) ? strtotime(input('end_time')) : strtotime(date('Y-m-d 00:00:00'));
		$datas = $this->service->build_sqlmap(array('days' => $days ,'start_time' => $start_time ,'end_time' => $end_time))->output('sales');
		echo json_encode(array('status'=>1,'message'=>"操作成功",'result'=>$datas));
    }




}
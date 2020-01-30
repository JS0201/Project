<?php

namespace Statistics\Controller;

use Common\Controller\AdminController;

class OrderController extends AdminController {

    public function _initialize() {

		$this->order_service = D('Statistics/Statistics','Service');
        
    }

   	public function index(){
		//$datas = $this->service_order->build_sqlmap(array('days' => 7))->output('sales,districts,payments');
		/* 组装地区信息 */
		/*
		if ($datas['districts']) {
			foreach ($datas['districts'] as $k => $v) {
				$datas['districts'][$k]['name'] = $v['name'];
				$datas['districts'][$k]['value'] = $v['value'];
			}
		}
		*/
		/* 组装支付方式 */
		/*
		if ($datas['payments']) {
			foreach ($datas['payments'] as $k => $v) {
				$datas['pays'][$k] = $v['name'];
			}
		}
		*/
		
		$this->assign('datas',$datas)->display();
	}
	
	
	
	/* 后台首页获取统计数据 */
	public function ajax_home() {
		$datas = $this->order_service->get_data();
		//$this->assign('datas',$datas);
     
		echo json_encode($datas);
	}
	
	
	
	
	

}

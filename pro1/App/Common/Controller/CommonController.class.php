<?php
namespace Common\Controller;
use Think\Controller;
use Think\Hook;
class CommonController extends Controller{
	
	public function _initialize() {
		
		//钩子，未付款自动取消订单
	//	Hook::add('auto_cancel_order', 'Order\\Addons\\HookAddons');
       // hook('auto_cancel_order');
	}

	
	
}
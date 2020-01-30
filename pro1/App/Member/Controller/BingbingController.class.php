<?php

namespace Member\Controller;

use Common\Controller\AdminController;

class BingbingController extends AdminController {

    public function _initialize() {
        parent::_initialize();
     
        //$this->service = D('Member/Member_code', 'Service');
    }

	
	
	public function add(){
		
		if(IS_POST){
			
			$username=I('username');
			$number=I('number');
			$price=I('price');
			$market=M('market');
			$rr=M('member')->where(array('username'=>$username))->find();
			$zjpush=M('zjpush')->where(array('id'=>1))->find();
			
			//判断是否卖出了100万个
			$num_all=$market->where(array('price'=>$price,'status'=>2))->sum('number');
			$all_all=$num_all+$number;//当前单价出售的数量
			$nnn=$zjpush['limit']-$num_all;
			if($all_all>$zjpush['limit']){
				showmessage('您当前出售的价格,最多能出售'.$nnn.'个！',U('add'),1);
			}
			if($rr){
				$data=array(
					'uid'=>$rr['id'],
					'price'=>$price,
					'number'=>$number,
					'status'=>2,
					'time'=>time(),
					);
				$market->data($data)->add();
				showmessage('卖出成功',U('add'),1);
			}else{
				showmessage('用户不存在');
			}
			
		}else{
			
			$this->display();
		}
		
		
	}


 
}

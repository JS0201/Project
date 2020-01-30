<?php

namespace Pay\Controller;

use Think\Controller;

class IndexController extends Controller {

    public function _initialize() {

        $this->order_service = D('Order/Order', 'Service');
        $this->order_sku_service = D('Order/Order_sku', 'Service');
        $this->order_sub_service = D('Order/Order_sub', 'Service');
    }

    public function dnotify() {
        $sqlmap = array();
        $sqlmap['sn'] = $_GET['order_sn'];
        // $sqlmap['pay_status']=0;
        // $sqlmap['status']=1;

        if (!$order = $this->order_service->find($sqlmap)) {
            echo '订单不存在或已支付过了';
            // return false;
        }



        $params = array();

        $params['paid_amount'] = sprintf("%0.2f", (float) $order['paid_amount']);
        $params['pay_method'] = 'wechat_js';
        $params['pay_sn'] = '';
        $params['msg'] = '';



        $result = $this->order_sub_service->set_order($order['sn'], 'pay', '', $params);
        if (!$result) {
            // echo $this->order_sub_service->errors;
            // return false;
        }

        $order_sku_info = $this->order_sku_service->get_by_order_sn($order['sn']);
        $buy_nums=0;
        foreach($order_sku_info as $k=>$v){
            $goods_price=$v['real_price'];
            $buy_nums+=$v['buy_nums'];
        }
    	$userid = $order['buyer_id'];
		$moneys = $goods_price;
		$goodsnums = $buy_nums;

    	$userid = array('userid'=>$userid,'moneys'=>$moneys,'goodsnums'=>$goodsnums);

    	if($order['order_type']==1){
			$userid = array('userid'=>$userid,'moneys'=>$order['real_price']);
			HOOK('givemoneys', $userid);
		}else{
			HOOK('domoreshoping', $userid);
		}
    }
		/*普通商城三级拨比传总价就好*/
	public function doshop($userid){


	}


}

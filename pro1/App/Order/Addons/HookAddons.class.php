<?php

namespace Order\Addons;

class HookAddons {

    public function delivery_finish() {
        
    }

    // 钩子：整个订单完成
    public function order_finish(&$order_sn) {
        $order = M('order')->where(array('sn' => $order_sn))->find();
        /* 增加会员经验值 */
        // 获取后台经验获取比例配置
        /*
          $exp_rate = (float) model('admin/setting', 'service')->get('exp_rate');
          if ($order && $exp_rate) {
          $exps = sprintf('%.2f', $order['paid_amount'] * $exp_rate);
          model('member/member')->where(array('id' => $order['buyer_id']))->setInc('exp', $exps);
          model('member/member', 'service')->change_group($order['buyer_id']);
          }
         * 
         */
        /* 扣除订单冻结余额 */
        /*
          if ($order['balance_amount'] > 0) {
          model('member/member', 'service')->action_frozen($order['buyer_id'], $order['balance_amount'], false, '完成订单，扣除冻结余额中的余额支付部分金额');
          }
         * 
         */
        /* 增加skus销量 */
        $skus = D('Order/Order_sku', 'Service')->get_by_order_sn($order_sn);
        if ($skus) {
            foreach ($skus as $sku) {
                M('goods_index')->where(array('sku_id' => $sku['sku_id']))->setInc('sales', $sku['buy_nums']);
            }
        }
    }
	
	//定时取消未支付订单
	public function auto_cancel_order(){
		
		$this->order_service = D('Order/Order', 'Service');
		$this->order_sub_service = D('Order/Order_sub', 'Service');
		
		$time=60*30;
		
		
		$sqlmap = $this->order_service->build_sqlmap(array('type'=>1));
      
        $orders = $this->order_service->get_order_lists($sqlmap, 1, 20);
		
		foreach($orders as $k=>$v){
		 
			if(time()>=$v['system_time']+$time){
				//30分钟未付款取消订单
			
				
				D('Order/Order_trade', 'Service')->setField(array('status' => -1), array('order_sn' => $v['sn']));

				$result = $this->order_sub_service->set_order($v['sub_sn'], 'order', 2, array('msg' => '', 'isrefund' => 1));
				
			}
			
		}
	
		
	}

}

?>
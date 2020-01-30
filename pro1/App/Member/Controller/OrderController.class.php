<?php

namespace Member\Controller;

class OrderController extends CheckController {

    public function _initialize() {
        parent::_initialize();
        $this->order_service = D('Order/Order', 'Service');
        $this->order_sub_service = D('Order/Order_sub', 'Service');
    }

//    public function index() {
//        $sqlmap = array();
//        $sqlmap = $this->order_service->build_sqlmap($_GET);
//
//
//
//        $sqlmap['buyer_id'] = $this->member['id'];
//        $result = $this->order_service->select($sqlmap);
//
//        $this->assign('result', $result)->display();
//    }
    public function index() {
        $sqlmap = array();
        $sqlmap = $this->order_service->build_sqlmap($_GET);



        $sqlmap['buyer_id'] = $this->member['id'];
        $result = $this->order_service->select($sqlmap);

        $this->assign('result', $result)->display();
    }


    public function detail() {
        $sqlmap = array();
        $sqlmap['order_sn'] = $_GET['order_sn'];
        $sqlmap['buyer_id'] = $this->member['id'];
        $order = $this->order_sub_service->find($sqlmap);
        if (!$order)
            showmessage(L('order_not_exist'));
        $order['_member'] = D('Member/Member')->fetch_by_id($order['buyer_id']);
        $order['_main'] = $this->order_service->find(array('sn' => $order['order_sn']));



        $this->assign('order', $order)->display();
    }

	
	public function detail1() {
        $sqlmap = array();
        $sqlmap['order_sn'] = $_GET['order_sn'];
        $sqlmap['buyer_id'] = $this->member['id'];
        $order = $this->order_sub_service->find($sqlmap);
        if (!$order)
            showmessage(L('order_not_exist'));
        $order['_member'] = D('Member/Member')->fetch_by_id($order['buyer_id']);
        $order['_main'] = $this->order_service->find(array('sn' => $order['order_sn']));



        $this->assign('order', $order)->display();
    }
    /* 取消订单 */

    public function cancel() {
        if (IS_POST) {

            $sub_sn = remove_xss($_POST['sub_sn']);
            $order = $this->order_sub_service->find(array('sub_sn' => $sub_sn), 'buyer_id,order_sn');
            if ($order['buyer_id'] != $this->member['id']) {
                showmessage(L('no_promission_operate_order'));
            }
            $result = $this->order_sub_service->set_order($sub_sn, $action = 'order', $status = 2, array('msg' => '用户取消订单', 'isrefund' => 1));
            if (!$result)
                showmessage($this->order_sub_service->errors);
            M('order_trade')->where(array('order_sn' => $order['order_sn']))->setField('status', -1);
            showmessage(L('_os_success_'), '', 1, 'json');
        } else {
            showmessage(L('_os_error_'));
        }


        // $result = $this->order_sub_service->set_order($_GET['order_sn'], $action = 'order', $status = 2, array('msg' => '用户取消订单', 'isrefund' => 1));
        // echo $this->order_sub_service->errors;
    }
    
    
	 
		
//物流信息
    public function express(){
        $delivery=M('order_delivery');
        $order_sku=M('order_sku');
        $sn=$_GET['sub_sn'];   //主订单的单号
        if($sn){

            $info=$delivery->where(array('sub_sn'=>$sn))->find();  //物流表
            $order_sn= $order_sku->where(array('sub_sn'=>$sn))->getField('order_sn');

          
            $this->assign('info',$info);
            $this->assign('order_sn',$order_sn);

        }



        $this->display();
    }

	public function pay(){
		$id=$this->member['id'];

		$tixian_setting=M('tixian_setting')->where(array('id'=>1))->find();
       	$sqlmap = array();
		$sqlmap['sn'] = $_GET['order_sn'];
		$sqlmap['pay_status'] = 0;
		$sqlmap['status'] = 1;
		if (!$order =  $this->order_service->find($sqlmap)) {
			showmessage('');
		}

		if($_GET['pay_method']=='1'){
			
			$params = array();
			$params['paid_amount'] = sprintf("%0.2f", (float) $order['paid_amount']);
			$params['pay_method'] = '1';
			$params['pay_sn'] = '';
			$params['msg'] = '';
			$uinfo=M('money_finance')->where(array('uid'=>$order['buyer_id']))->find();//查询会员的财务信息


			if($order['paid_amount']>$uinfo['money']){
				echo '<script>alert("ACNY余额不足，支付失败");location.href="'.U('member/order/index').'"</script>';
				exit();
			}else{

				$result = D('Order/Order_sub', 'Service')->set_order($order['sn'], 'pay', '', $params);
				$finance=M('money_finance');//财务报表
				$encryption=M('secure_encryption');//财务密钥表

				$finance_d=$finance->where(array('uid'=>$order['buyer_id']))->find();
				$keys=$encryption->where(array('uid'=>$order['buyer_id']))->find();//查出该会员密钥表里的数据
				$x_keys=md5(md5($finance_d['money']) . $keys['encrypt']);//安全加密
				if ($x_keys == $keys['money_keys']) {

					M('money_finance')->where(array('uid'=>$order['buyer_id']))->setdec('money',$order['paid_amount']);//减去会员账户金额
					$data_t=$finance->where(array('uid'=>$order['buyer_id']))->getField('money');//查出现金余额
					$keys_u=$encryption->where(array('uid'=>$order['buyer_id']))->find();//查出该会员密钥表里的数据
	        		$money_key = md5(md5($data_t) . $keys_u['encrypt']);//安全加密
	        		$encryption->where(array('uid'=>$order['buyer_id']))->setField(array('money_keys'=>$money_key));//写入密钥表

				}else{
					$log_data=array(
						'uid'=>$order['buyer_id'],
						'text'=>'系统执行购物扣款，发现此会员ACNY余额数据异常，停止给此会员执行扣款操作！',
						'time'=>time(),
						);
					M('exception_log')->data($log_data)->add();
					
					echo '<script>alert("您的ACNY余额数据异常，请联系客服查找异常情况！");location.href="'.U('member/order/index').'"</script>';
					exit();
				}

			}
			
			$order_sku_info = D('Order/Order_sku', 'Service')->get_by_order_sn($order['sn']);
			$buy_nums = 0;

			foreach ($order_sku_info as $k => $v) {
				$goods_price = $v['sku_price'];
				$buy_nums += $v['buy_nums'];
			}
			$userid = $order['buyer_id'];
			$moneys = $goods_price;
			$goodsnums = $buy_nums;
			$order['num']=$buy_nums;
            hook('member_upgrage', $order);//会员等级升级

			HOOK('dynamic_income', $order);//直推收益
			HOOK('user_path', $order);//团队收益计算方法

			//location.href="'.U('member/order/detail',array('order_sn'=>$order['sn'])).'
			echo '<script>alert("支付成功");location.href="'.U('member/order/index').'"</script>';
			exit();
		}elseif($_GET['pay_method']=='2'){
//		    USDT付款
			$params = array();
			$params['paid_amount'] = $order['usdt_amount'];
			$params['pay_method'] = '2';
			$params['pay_sn'] = '';
			$params['msg'] = '';
            $uinfo=M('money_finance')->where(array('uid'=>$order['buyer_id']))->find();//查询会员的财务信息
            if($order['paid_amount']>$uinfo['usdt']){
                echo '<script>alert("USDT余额不足，支付失败");location.href="'.U('member/order/index').'"</script>';
                exit();
            }else{

                $result = D('Order/Order_sub', 'Service')->set_order($order['sn'], 'pay', '', $params);
                $finance=M('money_finance');//财务报表
                $encryption=M('secure_encryption');//财务密钥表

                $finance_d=$finance->where(array('uid'=>$order['buyer_id']))->find();
                $keys=$encryption->where(array('uid'=>$order['buyer_id']))->find();//查出该会员密钥表里的数据
                $x_keys=md5(md5($finance_d['usdt']) . $keys['encrypt']);//安全加密
                if ($x_keys == $keys['u_currency']) {

                    M('money_finance')->where(array('uid'=>$order['buyer_id']))->setdec('usdt',$order['paid_amount']);//减去会员账户金额
                    $data_t=$finance->where(array('uid'=>$order['buyer_id']))->getField('usdt');//查出现金余额
                    $keys_u=$encryption->where(array('uid'=>$order['buyer_id']))->find();//查出该会员密钥表里的数据
                    $money_key = md5(md5($data_t) . $keys_u['encrypt']);//安全加密
                    $encryption->where(array('uid'=>$order['buyer_id']))->setField(array('u_currency'=>$money_key));//写入密钥表

                }else{
                    $log_data=array(
                        'uid'=>$order['buyer_id'],
                        'text'=>'系统执行购物扣款，发现此会员USDT账户余额数据异常，停止给此会员执行扣款操作！',
                        'time'=>time(),
                    );
                    M('exception_log')->data($log_data)->add();

                    echo '<script>alert("您的USDT账户余额数据异常，请联系客服查找异常情况！");location.href="'.U('member/order/index').'"</script>';
                    exit();
                }

            }

            $order_sku_info = D('Order/Order_sku', 'Service')->get_by_order_sn($order['sn']);
            $buy_nums = 0;

            foreach ($order_sku_info as $k => $v) {
                $goods_price = $v['sku_price'];
                $buy_nums += $v['buy_nums'];
            }
            $userid = $order['buyer_id'];
            $moneys = $goods_price;
            $goodsnums = $buy_nums;
            $order['num']=$buy_nums;
            hook('member_upgrage', $order);//会员等级升级
			HOOK('upgrade', $order);//会员下单，升级个人等级
			HOOK('dynamic_income', $order);//直推收益
			HOOK('user_path', $order);//团队收益计算方法
			HOOK('reward_phone',$order);    //固定手机获取收益
           
            //location.href="'.U('member/order/detail',array('order_sn'=>$order['sn'])).'
            echo '<script>alert("支付成功");location.href="'.U('member/order/index').'"</script>';
            exit();
		}
		$this->assign('order',$order);
        $this->display();
    }




}

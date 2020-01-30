<?php

namespace Peer\Controller;

use Member\Controller\CheckController;
class TradeController extends CheckController {

    public function _initialize() {
        parent::_initialize();
		
		$this->trade_order_model=D('Peer/TradingOrder','Service');
    }


	public function home(){
        $uid = $this->member['id'];
        $price_arr=get_min_price();

        $account_type = $_GET['type'];

        $type = $_GET['type'];
		
		if($type != 1){
			die("非法操作");
		}
		
        $this->assign('type',$type);

        $min=$price_arr['min_price'];
        $max=$price_arr['max_price'];
        $ok_count=M('trading_order')->where(array('order_status'=>3))->count(1);
        $ok_sum_money=M('trading_order')->where(array('order_status'=>3))->sum('num');

        $new_ok_order=M('trading_order')->where(array('order_status'=>3))->order('id desc')->select();

        $sqlmap=array();
        $sqlmap['order_type']=0;
        $sqlmap['order_status']=0;
        $sqlmap['account_type'] = $account_type;
        $buy_result=$this->trade_order_model->select($sqlmap);

        unset($sqlmap['order_type']);
        $sqlmap['order_type']=1;
        $sqlmap['order_status']=0;
        $sqlmap['account_type'] = $account_type;
        $sell_result=$this->trade_order_model->select($sqlmap);

//        //判断是否有设置二级密码
//        $twop=M('member')->where(array('id'=>$uid))->getField('twopassword');
//        $this->assign('twop',$twop);

        //查询总订单数（已完成）
        $order_count = M('trading_order')->where(array('order_status' => 3,'account_type'=>$type))->count();
        $this->assign('order_count',$order_count);
        //查询交易总数（已完成）
        $order_sum = M('trading_order')->where(array('order_status' => 3,'account_type'=>$type))->sum('num');
        $this->assign('order_sum',$order_sum);

        $this->assign('new_ok_order',$new_ok_order)->assign('buy_list',$buy_result['lists'])->assign('sell_list',$sell_result['lists']);
		$usd_price = M("setting")->where(array('key'=>'usd_price'))->getField('value');
		$this->assign('usd_price',$usd_price);
        $this->assign('min',$min)->assign('max',$max)->assign('ok_count',$ok_count)->assign('ok_sum_money',
		$ok_sum_money)->display();
	}
	
	public function index(){
		
		$this->display();
		
	}

    public function check_bank(){
        //ajax获取
        if(IS_POST){
            $uid = $this->member['id'];
            $member_profile = M('member_profile')->where(array('uid' => $uid,'default'=>1))->find();
            if($member_profile){
                $url = U('peer/trade/home');
                showmessage("1",$url,1);
            }else{
                $url = U('peer/trade/home');
                showmessage("0",$url,0);
            }

        }
    }
    public function get_order(){
        //ajax获取订单信息
        if(IS_POST){
            $id = I('post.id');//申请提链数量
            $trading_order = M('trading_order');
            $order=$trading_order->where(array('id'=>$id))->find();
            $url = U('peer/trade/home');
            showmessage("1",$order,$url,1);
        }
    }

    public function buy()
    {

        if (IS_POST) {

            $uid = $this->member['id'];
            $t = M('trading_order');
            $oid = (int)I('post.oid', '0');

            //支付密码
            $pass = I('post.pass', '0');
            $member = M('member')->where(array('id' => $this->member['id']))->find();
            if (md5(md5($pass) . $member['encrypt']) != $member['twopassword']) {
                showmessage('支付密码错误');
            }

            $bank = M('member_profile')->where(array('uid' => $uid))->find();
            if (empty($bank['account_name']) || empty($bank['account_bank']) || empty($bank['bank_account'])) {
                showmessage('请先到个人中心完善您的收款信息！');
            }
            if ($oid && $oinfo = $t->where(array('id' => $oid, 'order_status' => 0, 'order_type' => 0))->find()) {

                if ($oinfo['user_id'] == $uid) {
                    showmessage('自己的订单不能操作');
                }

                $personal_wallet = M('money_finance')->where(array('uid' => $uid))->find();//个人财务表

                if ($oinfo['account_type'] == 0) {
                    $type="member_bi";
                    if ($personal_wallet['member_bi'] < $oinfo['num']) {
                        showmessage('您的余额不足，交易失败！');
                    }
                } elseif($oinfo['account_type'] == 1) {
                    $type="member_z_bi";
                    if ($personal_wallet['member_z_bi'] < $oinfo['num']) {
                        showmessage('您的余额不足，交易失败！');
                    }
                } elseif($oinfo['account_type'] == 2) {
                    $type="money";
                    if ($personal_wallet['money'] < $oinfo['num']) {
                        showmessage('您的余额不足，交易失败！');
                    }
                }

                /*
                    uid 用户ID
                    types 1为卖出积分，2为撤销卖出
                    member_z_bi 子币
                    member_bi 母币
                    money 现金积分
                */
                if ($oinfo['account_type'] == 0) {
                    $qb_data = array(
                        'uid' => $uid,
                        'types' => '1',
                        'member_bi' => $oinfo['num'],
                        'text' => '会员出售子积分时，系统发现此会员账户余额数据异常，停止此会员出售积分交易！',
                    );
                }
                if ($oinfo['account_type'] == 1) {
                    $qb_data = array(
                        'uid' => $uid,
                        'types' => '1',
                        'member_z_bi' => $oinfo['num'],
                        'text' => '会员出售子积分时，系统发现此会员账户余额数据异常，停止此会员出售积分交易！',
                    );
                }
                if ($oinfo['account_type'] == 2) {
                    $qb_data = array(
                        'uid' => $uid,
                        'types' => '1',
                        'money' => $oinfo['num'],
                        'text' => '会员出售子积分时，系统发现此会员账户余额数据异常，停止此会员出售积分交易！',
                    );
                }

                R('Member/MemberSort/personal_wallet', array($qb_data));

                $data = array();
                $data['seller_id'] = $this->member['id'];
                $data['seller_user'] = $this->member['username'];
                $data['seller_realname'] = $this->member['realname'];
                $data['order_status'] = 1;
                $data['order_match_time'] = time();
                if ($t->where(['id' => $oid])->data($data)->save()) {
                    $text = '订单提交成功';
                    D('Member/MemberDetails')->add_details($uid, $type, $text, '-' . $oinfo['num']);
                    //买家手机号，短信通知
                    send_sms($oinfo['buy_user'],'【HM社区】尊敬的HM会员，您挂买的'.$oinfo['num'].'个的订单已被确认，请尽快安排打款');
                    showmessage('确认成功，请等待买家打款','',1);
                } else {
                    showmessage('交易失败');
                }
            } else {
                showmessage('订单不存在');
            }
        } else {
            $sqlmap = array();
            $sqlmap['order_type'] = 0;
            $sqlmap['order_status'] = 0;
            $sqlmap['account_type'] = 0;
            $result = $this->trade_order_model->select($sqlmap);
            $this->assign('list', $result['lists'])->assign('page', $result['page'])->display();
        }
    }


    public function sell(){

        if(IS_POST){
            $t=M('trading_order');
            $oid=(int)I('post.oid','0');
            $nums=I('post.nums','0');   //数量
            $price=I('post.price','0');  //单价
            $all_price = $nums*$price;   //总价
            $order = $t->where(array('id' => $oid))->find();  //订单信息

            //支付密码
            $pass = I('post.pass','0');
            $member = M('member')->where(array('id'=>$this->member['id']))->find();
            if(md5(md5($pass).$member['encrypt']) != $member['twopassword']){
                showmessage('支付密码错误');
            }

            if($oid && $oinfo=$t->where(array('id'=>$oid,'order_status'=>0,'order_type'=>1))->find()){

                if($oinfo['seller_id']==$this->member['id']){
                    showmessage('自己的订单不能操作');
                }
                //查找卖家定的数量
                $onum=$t->where(array('id'=>$oid))->getField('num');

                if($onum == $nums) { //如果买家买了100%的数量
                    $data = array();
                    $data['user_id'] = $this->member['id'];
                    $data['buy_user'] = $this->member['username'];
                    $data['user_realname'] = $this->member['realname'];
                    $data['order_status'] = 1;
                    $data['order_match_time'] = time();
                    if ($t->where(['id' => $oid])->data($data)->save()) {
                        $text = '订单提交成功';
                        D('Member/MemberDetails')->add_details($this->member['id'],'member_bi',$text,'-'.$oinfo['num']);
                        //卖家手机号，短信通知
                        send_sms($oinfo['seller_user'],'【HM社区】尊敬的HM会员，您挂卖的'.$oinfo['num'].'个的订单已被确认，如已收款请尽快确认完成');
                        showmessage('确认成功，请去打款', '', 1, $oinfo['order_no']);
                    } else {
                        showmessage('交易失败');
                    }
                }else if($onum > $nums){
                    $t->where(array('id'=>$oid))->setDec('num',$nums);//先减掉购买了的数量
                    $order = $t->where(array('id' => $oid))->find();  //订单信息
                    $new_price = $order['price'] * $order['num'];
                    $t->where(array('id'=>$oid))->setField('count_price',$new_price);//修改总价

                    $buy_user = M('member')->where(array('id' => $this->member['id']))->find();
                    //拆分多一条订单数据
                    $tdata['order_no']=build_order_no('');//订单编号;
                    $tdata['account_type']=$order['account_type'];
                    $tdata['user_id']=$buy_user['id'];
                    $tdata['buy_user']=$buy_user['username'];
                    $tdata['user_realname']=$buy_user['realname'];
                    $tdata['seller_id']=$order['seller_id'];
                    $tdata['seller_user']=$order['seller_user'];
                    $tdata['seller_realname']=$order['seller_realname'];
                    $tdata['order_type']=$order['order_type'];
                    $tdata['order_status']=1;
                    $tdata['num']=$nums;
                    $tdata['price']=$order['price'];
                    $tdata['count_price']=$order['price']*$nums;
                    $tdata['fee']=$order['fee'];
                    $tdata['order_create_time']=$order['order_create_time'];
                    $tdata['order_match_time']=time();
                    $tdata['order_pay_time']=time();
                    $tdata['order_ok_time']=time();
                    $t->add($tdata);

                    $text = '订单提交成功';
                    D('Member/MemberDetails')->add_details($this->member['id'],'member_bi',$text,'-'.$oinfo['num']);
                    //卖家手机号，短信通知
                    send_sms($oinfo['seller_user'],'【HM社区】尊敬的HM会员，您挂卖的'.$oinfo['num'].'个的订单已被确认，如已收款请尽快确认完成');
                    showmessage('确认成功，请去打款', '', 1, $tdata['order_no']);
                }
            }else{

                showmessage('订单不存在');
            }

        }else{


            $sqlmap=array();
            $sqlmap['order_type']=1;
            $sqlmap['order_status']=0;
            $result=$this->trade_order_model->select($sqlmap);

            $this->assign('list',$result['lists'])->assign('page',$result['page'])->display();

        }
    }
	
	
	
	public function k_line(){
		
		$ac=$_GET['ac'];
		
		$data=array();
		
		if($ac=='fs'){
			
				
			
			$sqlmap=array();
			$start_time=strtotime(date('Y-m-d 00:00:00'));
			$end_time=strtotime(date('Y-m-d H:i',time()));
			$sqlmap['order_ok_time']=array('between',array($start_time,$end_time));
			$sqlmap['order_status']=3;
			$hlist=M('trading_order')->where($sqlmap)->field('FROM_UNIXTIME(order_ok_time,"%H:%i") as group_time,count(1) as  count')->group('group_time')->select();
			
			
			foreach($hlist as $k=>$v){
				
				//$arr=explode('-',$v);
				
				
				
				//ECHO M('trading_order')->getlastsql();
				/*
				print_r($hlist);
				
				exit();
				
				$sqlmap=array();
				$sqlmap['order_ok_time']=array('between',array($start_time,$end_time));
				$sqlmap['order_status']=3;
				$count=M('trading_order')->where($sqlmap)->count(1);
				*/
				
				
				$data[]=array($v['group_time'],(int)$v['count']);
			}
			
		}elseif($ac=='day'){
			
			$start_time=strtotime(date('Y-m-d'));
			$end_time=strtotime(date('Y-m-d 23:59:59'));
			
			$sqlmap=array();
			$sqlmap['order_ok_time']=array('between',array($start_time,$end_time));
			$sqlmap['order_status']=3;
			$count=M('trading_order')->where($sqlmap)->count(1);
			
			
			$data[]=array(date('Y-m-d'),(int)$count);
			
		}elseif($ac=='week'){
			
			for($i=7;$i>0;$i--){
				$day=date('Y-m-d',strtotime('-'.$i.' day'));
				$start_time=strtotime($day);
				$end_time=strtotime(date('Y-m-d 23:59:59',strtotime('-'.$i.' day')));
				
				$sqlmap=array();
				$sqlmap['order_ok_time']=array('between',array($start_time,$end_time));
				$sqlmap['order_status']=3;
				$count=M('trading_order')->where($sqlmap)->count(1);
				$data[]=array($day,(int)$count);
			}
			
		}elseif($ac=='month'){
			
			for($i=30;$i>0;$i--){
				$day=date('Y-m-d',strtotime('-'.$i.' day'));
				$start_time=strtotime($day);
				$end_time=strtotime(date('Y-m-d 23:59:59',strtotime('-'.$i.' day')));
				
				$sqlmap=array();
				$sqlmap['order_ok_time']=array('between',array($start_time,$end_time));
				$sqlmap['order_status']=3;
				$count=M('trading_order')->where($sqlmap)->count(1);
				$data[]=array($day,(int)$count);
			}
			
		}
		
		
		
		echo json_encode($data);
	}
	
	

}

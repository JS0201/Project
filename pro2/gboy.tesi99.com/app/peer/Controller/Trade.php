<?php

namespace app\peer\controller;
use app\member\controller\Check;
use think\Db;
class Trade extends Check {

    public function _initialize() {
        parent::_initialize();
        $this->trade_order_model = model('Peer/TradingOrder','service');
    }

	public function home(){
        $uid = $this->member['id'];
		$price_arr=get_min_price();

        $account_type = $_GET['type'];
		
		$min=$price_arr['min_price'];
		$max=$price_arr['max_price'];
		$ok_count=db('trading_order')->where(array('order_status'=>3))->count(1);
		$ok_sum_money=db('trading_order')->where(array('order_status'=>3))->sum('num');
		$new_ok_order=db('trading_order')->where(array('order_status'=>3))->order('id desc')->limit(20)->select();

		$sqlmap=array();
		$sqlmap['order_type']=0;
		$sqlmap['order_status']=0;
		$sqlmap['account_type'] = $account_type;
		$buy_result=$this->trade_order_model->select($sqlmap);

		unset($sqlmap['order_type']);
		$sqlmap['order_type']=1;
        $sqlmap['account_type'] = $account_type;
		$sell_result=$this->trade_order_model->select($sqlmap);
		$type = $_GET['type'];
		$this->assign('type',$type);
        $acc_type = $_GET['acc_type'];
        $this->assign('acc_type',$acc_type);
        $value = db('setting')->where("`key` = 'usdt'")->value('value');
        $usdt = round(5 / $value, 4);
        $this->assign('usdt', $usdt);
		$this->assign('new_ok_order',$new_ok_order)->assign('buy_list',$buy_result['data'])->assign('sell_list',$sell_result['data']);
		$this->assign('min',$min)->assign('max',$max)->assign('ok_count',$ok_count)->assign('ok_sum_money',$ok_sum_money);
		return $this->fetch();
	}

	public function get_order(){
	    //ajax获取订单信息
        if(IS_POST){
            $id = input('id');//申请提链数量
            $trading_order = db('trading_order');
            $order=$trading_order->where(array('id'=>$id))->find();
            $url = url('peer/trade/home');
            showmessage("1",$order,$url,1);
        }
    }

    public function check_bank(){
        //ajax获取
        if(IS_POST){
            $uid = $this->member['id'];
            $member_profile = db('member_profile')->where(array('uid' => $uid,'default'=>1))->find();
            if($member_profile){
                $url = url('peer/trade/home');
                showmessage("1",$url,1);
            }else{
                $url = url('peer/trade/home');
                showmessage("0",$url,0);
            }

        }
    }
	
	public function index(){
		
		$this->display();
		
	}
	
	public function buy()
    {

        if (IS_POST) {

            $uid = $this->member['id'];
            $t = db('trading_order');
            $oid = (int)input('post.oid', '0');

            //支付密码
            $pass = input('post.pass', '0');
            $member = db('member')->where(array('id' => $this->member['id']))->find();
            if (md5(md5($pass) . $member['encrypt']) != $member['twopassword']) {
                showmessage('支付密码错误');
            }

                $bank = db('member_profile')->where(array('uid' => $uid))->find();
                if (empty($bank['account_bank'])) {
                    showmessage('请先到个人中心完善您的收款信息！');
                }
                if ($oid && $oinfo = $t->where(array('id' => $oid, 'order_status' => 0, 'order_type' => 0))->find()) {

                    if ($oinfo['user_id'] == $uid) {
                        showmessage('自己的订单不能操作');
                    }
                    $personal_wallet = db('money_finance')->where(array('uid' => $uid))->find();//个人财务表
                    if ($oinfo['account_type'] == 1) {
						$bi = 'member_z_bi';
                        if ($personal_wallet['member_z_bi'] < $oinfo['num']) {
                            showmessage('您的AE余额不足，交易失败！');
                        }
                    } else {
						$bi =  'member_bi';
                        if ($personal_wallet['member_bi'] < $oinfo['num']) {
                            showmessage('您的ATS余额不足，交易失败！');
                        }
                    }
                    /*
                        uid 用户ID
                        types 1为卖出积分，2为撤销卖出
                        member_z_bi 子币
                        member_bi 母币
                        money 现金积分
                    */
                    $qb_data = array(
                        'uid' => $uid,
                        'types' => '1',
                        'member_bi' => $oinfo['num'],
                        'text' => '会员出售子积分时，系统发现此会员账户余额数据异常，停止此会员出售积分交易！',
                    );
                    if ($oinfo['account_type'] == 1) {
                        $qb_data = array(
                            'uid' => $uid,
                            'types' => '1',
                            'member_z_bi' => $oinfo['num'],
                            'text' => '会员出售子积分时，系统发现此会员账户余额数据异常，停止此会员出售积分交易！',
                        );
                    }

                    //R('Member/MemberSort/personal_wallet', array($qb_data));
                    $this->personal_wallet2($qb_data);

                    $data = array();
                    $data['seller_id'] = $this->member['id'];
                    $data['seller_user'] = $this->member['username'];
                    $data['seller_realname'] = $this->member['realname'];
                    $data['order_status'] = 1;
                    $data['order_match_time'] = time();
                    if ($t->where(['id' => $oid])->data($data)->save()) {
                        $text = '订单提交成功';
                        model('Member/MemberDetails')->add_details($uid, $bi, $text, '-' . $oinfo['num']);
                        showmessage('确认成功，请等待买家打款');
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
		
		if(is_post()){
            $t=db('trading_order');
            $oid=(int)input('post.oid','0');
            $nums=input('post.nums','0');   //数量
            $price=input('post.price','0');  //单价
            $all_price = $nums*$price;   //总价
            $order = $t->where(array('id' => $oid))->find();  //订单信息

            //支付密码
            $pass = input('post.pass','0');
            $member = db('member')->where(array('id'=>$this->member['id']))->find();
            if(md5(md5($pass).$member['encrypt']) != $member['twopassword']){
                showmessage('支付密码错误');
            }

			 if($oid && $oinfo=$t->where(array('id'=>$oid,'order_status'=>0,'order_type'=>1))->find()){

					if($oinfo['seller_id']==$this->member['id']){
						showmessage('自己的订单不能操作');
					}
                 //查找卖家定的数量
                    $onum=$t->where(array('id'=>$oid))->value('num');

                    if($onum == $nums) { //如果买家买了100%的数量
                        $data = array();
                        $data['user_id'] = $this->member['id'];
                        $data['buy_user'] = $this->member['username'];
                        $data['user_realname'] = $this->member['realname'];
                        $data['order_status'] = 1;
                        $data['order_match_time'] = time();
                        if ($t->where(['id' => $oid])->data($data)->save()) {
                            $text = '订单提交成功';
                            //D('Member/MemberDetails')->add_details($this->member['id'],'member_bi',$text,'-'.$oinfo['num']);
                            showmessage('确认成功，请去打款', '', 1, $oinfo['order_no']);
                        } else {
                            showmessage('交易失败');
                        }
                    }else if($onum > $nums){
                        $t->where(array('id'=>$oid))->setDec('num',$nums);//先减掉购买了的数量
                        $order = $t->where(array('id' => $oid))->find();  //订单信息
                        $new_price = $order['price'] * $order['num'];
                        $t->where(array('id'=>$oid))->setField('count_price',$new_price);//修改总价

                        $buy_user = db('member')->where(array('id' => $this->member['id']))->find();
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
                        $t->data($tdata)->insert();

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
		
			$this->assign('list',$result['lists'])->assign('page',$result['page']);
			return $this->fetch();
			
		}
	}
    //AE兑换积分
    public function change() {
        if(IS_POST) {
            $nums = $_POST['nums'];
            if(!$nums || intval($nums) != $nums) {
                showmessage('参数错误');
            }
            $pecent = M('setting')->where("`key` = 'shop_change'")->getField('value');
            $memberService = D('Member/member','Service');
			$ae = M('money_finance')->where(array("uid"=>$this->member['id']))->getField('member_z_bi');
			if($ae < $nums) {
				showmessage('AE不足');
			}
            $memberService->change_account($this->member['id'], 'member_z_bi', '-'.$nums, '积分兑换消耗', true);
            $memberService->change_account($this->member['id'], 'shop_integral', $nums * $pecent, '兑换获得', true);
            $data_1=array(
                'member_getid'=>'',
                'member_giveid'=>$this->member['id'],
                'money_produtime'=>time(),
                'money_nums'=>$nums,
                'money_type'=>'19',
            );
            M('money_types')->data($data_1)->add();
            $data_2=array(
                'member_getid'=>$this->member['id'],
                'member_giveid'=>'',
                'money_produtime'=>time(),
                'money_nums'=>$nums * $pecent,
                'money_type'=>'20',
            );
            M('money_types')->data($data_2)->add();
            showmessage('兑换成功','',1);
        }else{
            $list = M("money_types")->where("`member_getid` = {$this->member['id']} and money_type = 20")->order("id desc")->select();
            if($list) {
                foreach($list as $k => $v){
                    $list[$k]['dateline'] = date('Y/m/d H:i:s', $v['money_produtime']);
                    $list[$k]['money_nums'] = intval($v['money_nums']);
                }
            }
            $pecent = M('setting')->where("`key` = 'shop_change'")->getField('value');
            $this->assign('pecent', $pecent);
            $this->assign('list', $list);
            $this->display();
        }
    }


    public function ep_sell(){
        $t=db('trading_order');
        $oid=(int)input('post.oid','0');
        $nums=input('post.nums','0');   //数量
        $order = $t->where(array('id' => $oid))->find();  //订单信息
        $price=$order['price'];  //单价
        $all_price = $nums*$price;   //总价
        $money_finance = db('money_finance')->where(array('uid' => $this->member['id']))->find();

        //支付密码
        $pass = input('post.pass','0');
		
        $member = db('member')->where(array('id'=>$this->member['id']))->find();
        if(md5(md5($pass).$member['encrypt']) != $member['twopassword']){
            showmessage('支付密码错误');
        }

        if($oid && $oinfo=$t->where(array('id'=>$oid,'order_status'=>0,'order_type'=>1))->find()){
            if($oinfo['seller_id']==$this->member['id']){
                showmessage('自己的订单不能操作');
            }
            if($money_finance['member_z_bi'] < $all_price){
                showmessage('您的AE余额不足');
            }
            //查找卖家定的数量
            $onum=$t->where(array('id'=>$oid))->value('num');
            if($onum == $nums){ //如果买家买了100%的数量
                $data=array();
                $data['user_id']=$this->member['id'];
                $data['buy_user']=$this->member['username'];
                $data['user_realname']=$this->member['realname'];
                $data['order_status']=3;
                $data['order_match_time']=time();
                if($t->where(['id'=>$oid])->data($data)->update()){
                    //扣除买家AE账户
                    $grqb_data['zh_types']='member_z_bi';//购物积分字段
                    $grqb_data['key_types']='currency';//购物积分加密字段
                    $grqb_data['uid']=$this->member['id'];//获取积分用户ID
                    $grqb_data['types']='1';//累减积分
                    $grqb_data['number']=$all_price;//本次产生的积分数量
                    $grqb_data['text']='买家进行交易时，系统发现AE数据异常，停止给此会员执行动作！';
                    $this->personal_wallet($grqb_data);//调用个人钱包方法
                    //累加卖家AE账户
                    //执行个人钱包操作动作
                    $grqb_data['zh_types']='member_z_bi';//购物积分字段
                    $grqb_data['key_types']='currency';//购物积分加密字段
                    $grqb_data['uid']=$order['seller_id'];//获取积分用户ID
                    $grqb_data['types']='2';//累加积分
                    $grqb_data['number']=$all_price;//本次产生的积分数量
                    $grqb_data['text']='买家进行交易时，系统发现AE数据异常，停止给此会员执行动作！';
                    $this->personal_wallet($grqb_data);//调用个人钱包方法

                    //扣除卖家ATS账户
                    /*$grqb_data['zh_types']='member_bi';//购物积分字段
                    $grqb_data['key_types']='mother_currency';//购物积分加密字段
                    $grqb_data['uid']=$order['seller_id'];//获取积分用户ID
                    $grqb_data['types']='1';//累减积分
                    $grqb_data['number']=$order['num'];//本次产生的积分数量
                    $grqb_data['text']='买家进行交易时，系统发现ATS数据异常，停止给此会员执行动作！';
                    $this->personal_wallet($grqb_data);//调用个人钱包方法*/

                    //累加买家ATS账户
                    //执行个人钱包操作动作
                    $grqb_data['zh_types']='member_bi';//购物积分字段
                    $grqb_data['key_types']='mother_currency';//购物积分加密字段
                    $grqb_data['uid']=$this->member['id'];//获取积分用户ID
                    $grqb_data['types']='2';//累加积分
                    $grqb_data['number']=$nums;//本次产生的积分数量
                    $grqb_data['text']='买家进行交易时，系统发现AE数据异常，停止给此会员执行动作！';
                    $this->personal_wallet($grqb_data);//调用个人钱包方法
                    $this->addlog($grqb_data);
                    model('Member/MemberDetails')->add_details($order['seller_id'],'member_z_bi','挂买交易成功',$all_price);
                    showmessage('','',1,$oinfo['order_no']);
                }else{
                    showmessage('交易失败');
                }
            }else if($onum > $nums){
                $t->where(array('id'=>$oid))->setDec('num',$nums);//先减掉购买了的数量
                $order = $t->where(array('id' => $oid))->find();  //订单信息
                $new_price = $order['price'] * $order['num'];
                $t->where(array('id'=>$oid))->setField('count_price',$new_price);//修改总价

                $buy_user = db('member')->where(array('id' => $this->member['id']))->find();
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
                $tdata['order_status']=3;
                $tdata['num']=$nums;
                $tdata['price']=$order['price'];
                $tdata['count_price']=$order['price']*$nums;
                $tdata['fee']=$order['fee'];
                $tdata['order_create_time']=$order['order_create_time'];
                $tdata['order_match_time']=time();
                $tdata['order_pay_time']=time();
                $tdata['order_ok_time']=time();
                $re = $t->data($tdata)->insert();

                //扣除AE账户
                //执行个人钱包操作动作
                $grqb_data['zh_types']='member_z_bi';//购物积分字段
                $grqb_data['key_types']='currency';//购物积分加密字段
                $grqb_data['uid']=$this->member['id'];//获取积分用户ID
                $grqb_data['types']='1';//累减积分
                $grqb_data['number']=$all_price;//本次产生的积分数量
                $grqb_data['text']='买家进行交易时，系统发现AE数据异常，停止给此会员执行动作！';
                $this->personal_wallet($grqb_data);//调用个人钱包方法

                //累加AE账户
                //执行个人钱包操作动作
                $grqb_data['zh_types']='member_z_bi';//购物积分字段
                $grqb_data['key_types']='currency';//购物积分加密字段
                $grqb_data['uid']=$order['seller_id'];//获取积分用户ID
                $grqb_data['types']='2';//累加积分
                $grqb_data['number']=$all_price;//本次产生的积分数量
                $grqb_data['text']='买家进行交易时，系统发现AE数据异常，停止给此会员执行动作！';
                $this->personal_wallet($grqb_data);//调用个人钱包方法

                //扣除卖家ATS账户
                /*$grqb_data['zh_types']='member_bi';//购物积分字段
                $grqb_data['key_types']='mother_currency';//购物积分加密字段
                $grqb_data['uid']=$order['seller_id'];//获取积分用户ID
                $grqb_data['types']='1';//累减积分
                $grqb_data['number']=$order['num'];//本次产生的积分数量
                $grqb_data['text']='买家进行交易时，系统发现ATS数据异常，停止给此会员执行动作！';
                $this->personal_wallet($grqb_data);//调用个人钱包方法*/

                //累加买家ATS账户
                //执行个人钱包操作动作
                $grqb_data['zh_types']='member_bi';//购物积分字段
                $grqb_data['key_types']='mother_currency';//购物积分加密字段
                $grqb_data['uid']=$this->member['id'];//获取积分用户ID
                $grqb_data['types']='2';//累加积分
                $grqb_data['number']=$nums;//本次产生的积分数量
                $grqb_data['text']='买家进行交易时，系统发现AE数据异常，停止给此会员执行动作！';
                $this->personal_wallet($grqb_data);//调用个人钱包方法

                $p_data=array(
                    'member_getid'=>$this->member['id'],//收益会员
                    'member_giveid'=>$order['seller_id'],//提供会员
                    'money_nums'=>$nums,//交易金额
                    'money_produtime'=>time(),//交易时间
                    'money_type'=>'14',//交易类型
                );
                db('money_types')->data($p_data)->insert(); // ATS
                $p_data=array(
                    'member_getid'=>$order['seller_id'],//收益会员
                    'member_giveid'=>$this->member['id'],//提供会员
                    'money_nums'=>$all_price,//交易金额
                    'money_produtime'=>time(),//交易时间
                    'money_type'=>'15',//交易类型
                );
                db('money_types')->data($p_data)->insert(); // ATS

                model('Member/MemberDetails')->add_details($order['seller_id'],'member_z_bi','挂买交易成功',$all_price);
                showmessage('','',1,$oinfo['order_no']);
            }
        }else{
            showmessage('订单不存在');
        }

    }
	
	/*
	//执行个人钱包操作动作
	$grqb_data['zh_types']='shop_integral';//购物积分字段
	$grqb_data['key_types']='shop_integral_key';//购物积分加密字段
	$grqb_data['uid']=$pid;//获取积分用户ID
	$grqb_data['types']='2';//累加积分
	$grqb_data['number']=$shop_jf;//本次产生的积分数量
	$grqb_data['text']='推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
	$this->personal_wallet($grqb_data);//调用个人钱包方法
	*/
	public function addlog($data)
    {

    }

	//累加个人钱包
	public function personal_wallet($qb_data){
			$arr = array('member_z_bi'=>'AE','member_bi'=>'ATS');
	        $qb_types=$qb_data['zh_types'];//账户类型
	        $key_types=$qb_data['key_types'];//加密类型
	        $id=$qb_data['uid'];//会员id
	        $types=$qb_data['types'];//1为减去积分，2为累加积分
	        $number=$qb_data['number'];//交易数量
	        $money_finance=db('money_finance');//个人财务表
	        $cw_encryption=db('secure_encryption');//个人财务加密表
	        $keys=$cw_encryption->where(array('uid'=>$id))->find();
	        $cw=$money_finance->where(array('uid'=>$id))->find();
	        if ($cw[$qb_types] > 0){
	            $x_keys=md5(md5($cw[$qb_types]) . $keys['encrypt']);//安全加密
	            if ($keys[$key_types]==$x_keys){
	                if ($types==1){
	                    $money_finance->where(array('uid'=>$id))->setDec($qb_types,$number);//减去积分
                        $msg = $arr[$qb_types].'交易支出：'.$number;
	                }elseif ($types==2){
                        $msg = $arr[$qb_types].'交易收入：'.$number;
	                    $money_finance->where(array('uid'=>$id))->setInc($qb_types,$number);//累加积分
	                }
                    //$_member = db('money_finance')->where("uid = {$id}")->find();
                    $log_info = array(
                        'mid' => $id,
                        'value' => $number,
                        'type' => $qb_types,
                        'msg' => $msg,
                        'dateline' => time(),
                        'admin_id' => (defined('IN_ADMIN')) ? ADMIN_ID : 0,
                        //'admin_user' => (defined('IN_ADMIN')) ? ADMIN_USER : '',
                        //'money_detail' => json_encode(array('member_bi' => sprintf('%.2f', $_member[$qb_types])))
                    );
                    db('member_log')->data($log_info)->insert();
	                $member_bii=$money_finance->where(array('uid'=>$id))->value($qb_types);
	                $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
	                $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);
	            }else{
	                $log_data=array(
	                    'uid'=>$id,
	                    'text'=>$qb_data['text'],
	                    'time'=>time(),
	                );
	                db('exception_log')->data($log_data)->insert();
	            }
	        }else{
	            if ($types==1) {
	                $money_finance->where(array('uid'=>$id))->setDec($qb_types,$number);//减去子积分
                    $msg = $arr[$qb_types].'交易支出：'.$number;
	            }elseif ($types==2) {
                    $msg = $arr[$qb_types].'交易收入：'.$number;
	                $money_finance->where(array('uid'=>$id))->setInc($qb_types,$number);//累加子积分
	            }
                //$_member = db('money_finance')->where("uid = {$id}")->find();
                $log_info = array(
                    'mid' => $id,
                    'value' => $number,
                    'type' => $qb_types,
                    'msg' => $msg,
                    'dateline' => time(),
                    'admin_id' => (defined('IN_ADMIN')) ? ADMIN_ID : 0,
                    //'admin_user' => (defined('IN_ADMIN')) ? ADMIN_USER : '',
                    //'money_detail' => json_encode(array('member_bi' => sprintf('%.2f', $_member[$qb_types])))
                );
                db('member_log')->data($log_info)->insert();
	            $member_bii=$money_finance->where(array('uid'=>$id))->value($qb_types);
	            $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
	            $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);
	        }
	}

    public function personal_wallet2($qb_data){

        $member_z_bi=$qb_data['member_z_bi'];//子积分账户
        $member_bi=$qb_data['member_bi'];//母积分账户
        $id=$qb_data['uid'];//会员id
        $types=$qb_data['types'];//1为卖出积分，2为撤销卖出

        $money_finance=db('money_finance');//个人财务表
        $cw_encryption=db('secure_encryption');//个人财务加密表
        $keys=$cw_encryption->where(array('uid'=>$id))->find();
        $cw=$money_finance->where(array('uid'=>$id))->find();

        if ($member_z_bi){

            if ($cw['member_z_bi'] > 0){
                $x_keys=md5(md5($cw['member_z_bi']) . $keys['encrypt']);//安全加密
                if ($keys['currency']==$x_keys){
                    if ($types==1){
                        $money_finance->where(array('uid'=>$id))->setDec('member_z_bi',$member_z_bi);//减去子积分
                    }elseif ($types==2){
                        $money_finance->where(array('uid'=>$id))->setInc('member_z_bi',$member_z_bi);//累加子积分
                    }
                    $member_bii=$money_finance->where(array('uid'=>$id))->getField('member_z_bi');
                    $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
                    $cw_encryption->where(array('uid'=>$id))->setField('currency',$datat);

                }else{

                    $log_data=array(
                        'uid'=>$id,
                        'text'=>$qb_data['text'],
                        'time'=>time(),
                    );
                    db('exception_log')->data($log_data)->insert();
                    showmessage('您的账户余额数据异常，终止操作，请联系后台管理员处理！');
                    exit();
                }

            }else{

                if ($types==1) {
                    $money_finance->where(array('uid'=>$id))->setDec('member_z_bi',$member_z_bi);//减去子积分
                }elseif ($types==2) {
                    $money_finance->where(array('uid'=>$id))->setInc('member_z_bi',$member_z_bi);//累加子积分
                }
                $member_bii=$money_finance->where(array('uid'=>$id))->value('member_z_bi');
                $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
                $cw_encryption->where(array('uid'=>$id))->setField('currency',$datat);
            }


        }elseif($member_bi){

            if ($cw['member_bi'] > 0) {
                $x_keys=md5(md5($cw['member_bi']) . $keys['encrypt']);//安全加密
                if ($keys['mother_currency']==$x_keys) {

                    if ($types==1) {
                        $money_finance->where(array('uid'=>$id))->setDec('member_bi',$member_bi);//减去母币
                    }elseif ($types==2) {
                        $money_finance->where(array('uid'=>$id))->setInc('member_bi',$member_bi);//累加母币
                    }
                    $member_bii=$money_finance->where(array('uid'=>$id))->value('member_bi');
                    $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
                    $cw_encryption->where(array('uid'=>$id))->setField('mother_currency',$datat);

                }else{
                    $log_data=array(
                        'uid'=>$id,
                        'text'=>$qb_data['text'],
                        'time'=>time(),
                    );
                    db('exception_log')->data($log_data)->add();
                    showmessage('您的账户余额数据异常，终止操作，请联系后台管理员处理！');
                    exit();
                }

            }else{

                if ($types==1) {
                    $money_finance->where(array('uid'=>$id))->setDec('member_bi',$member_bi);//减去母币
                }elseif ($types==2) {
                    $money_finance->where(array('uid'=>$id))->setInc('member_bi',$member_bi);//累加母币
                }
                $member_bii=$money_finance->where(array('uid'=>$id))->value('member_bi');
                $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
                $cw_encryption->where(array('uid'=>$id))->setField('mother_currency',$datat);
            }
            //$this->member_upgrade($id);//会员等级升级

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
	//提现
	public function withdraw() {
        if(is_post()) {
            $ats = $_POST['ats'];
            $ai = $_POST['ai'];
            $place = $_POST['place'];
            if(!$ats || !$ai || !$place) {
                showmessage("参数异常");
            }
            $last = db('withdraw')->where("uid = {$this->member['id']}")->order('id desc')->find();
            if($last && time() < $last['addtime'] + config('p_ai_day') * 86400) {
                showmessage(config('p_ai_day')."天内只能兑换一次");
            }
            if($ats < config('p_ai_min')) {
                showmessage("最低兑换".config('p_ai_min').'ATS');
            }
            $bi = db('money_finance')->where("uid = {$this->member['id']}")->value('member_bi');
            if($ats > config('p_ai_max') * $bi) {
                showmessage("最高兑换".config('p_ai_max') * $bi.'ATS');
            }
            $re = db('withdraw')->data(array(
                'uid' => $this->member['id'],
                'ats' => $ats,
                'ai' => $ai,
                'place' => $place,
                'atsprice' => config('p_open_price'),
                'aiprice' => config('p_ai_price'),
                'status' => 0,
                'addtime' => time()
            ))->insert();
            if($re) {
                $server = model('Member/member', 'Service');
                $server->change_account($this->member['id'],'member_bi','-'.$ats);
                showmessage('','',1);
            }
            showmessage('兑换失败');
        }
        $uid = $this->member['id'];
        $money=db('money_finance')->where(array('uid'=>$uid))->find();
        $this->assign('money', $money);
	    return $this->fetch();
    }

    //提现列表
    public function withdrawlist() {
        $list = M('withdraw')->where("uid = {$this->member['id']}")->order('id desc')->select();
        if($list) {
            foreach ($list as $k => $v) {
                $list[$k]['status'] = $v['status'] == 0 ? '审核中' : ($v['status'] == 1 ? '审核通过' : '审核不通过');
                $list[$k]['addtime'] = date('Y-m-d', $v['addtime']);
            }
            $this->assign('list', $list);
        }
        $this->display();
    }
}

<?php

namespace app\peer\controller;
use app\member\controller\Check;
class Sell extends Check {

     public function _initialize() {
        parent::_initialize();
        //$open=M('setting')->where(array('key'=>'p_open'))->getField('value');

        $open_time=db('setting')->where(array('key'=>'p_open_time'))->value('value');

        $checkDayStr=date('Y-m-d ',time());

        $p_time=rtrim($open_time, '-');//去除逗号
        $prev_path = explode('-', $p_time);//组成数组

        $timeBegin1 = strtotime($checkDayStr."$prev_path[0]".":00");
        $timeEnd1 = strtotime($checkDayStr."$prev_path[1]".":00");


        $curr_time = time();

        if($curr_time >= $timeBegin1 && $curr_time <= $timeEnd1){

        }else{
            echo '<script>alert("交易区未开放！");location.href="'.url('Peer/trade/home',array('type'=>$_GET['type'])).'"</script>';
            exit();
        }
    }
	
	
	public function sell(){
		if(is_post()){
			$uid=$this->member['id'];//用户ID
			//$num=(int)abs(I('post.num'));//出售数量
            $num = $_POST['num'];
			//$account_type=(int)abs(I('post.account_type'));//出售类型0母币1字币
            $account_type=$_POST['account_type'];
			$price=sprintf('%.4f',abs(input('post.price')));//出售单价
			$trading_order=db('trading_order');//出售币记录表
			
			$sell_bili = db("setting")->where(array('key'=>'sell_bili'))->value("value");  //出售数量基数
            $sell_di = db("setting")->where(array('key'=>'sell_di'))->value("value");       //出售最低数量
            $bei = $num % $sell_bili;

            if($num < $sell_di || $bei != 0){
                showmessage('金额输入错误');
            }

			$bank= db('member_profile')->where(array('uid'=>$this->member['id']))->find();
			//$exist=$trading_order->where(array('seller_id'=>$uid,'order_type'=>'1','order_status'=>array('neq','3')))->find();
//			if($exist){
//                exit('<script>alert("您已经有出售中的订单，不可重复出售！");location.href="'.U('peer/sell/sell').'"</script>');
//			}
			if(empty($bank['account_bank'])){
                showmessage('请先到个人中心完善您的收款信息！');
                //exit('<script>alert("请先到个人中心完善您的收款信息！");location.href="'.U('member/message/setbankcard').'"</script>');
			}


			if($num<=0){
                showmessage('请输入出售数量！');
                //exit('<script>alert("请输入出售数量！");location.href="'.U('peer/sell/sell').'"</script>');
			}

			if($price<=0){
                showmessage('请输入出售单价！');
			}
			
			$price_arr = get_min_price();
			
//			if($price<$price_arr['min_price']){
//
//				showmessage('交易价不能小于'.$price_arr['min_price']);
//			}
//
//			if($price>$price_arr['max_price']){
//
//				showmessage('交易价不能大于'.$price_arr['max_price']);
//			}
			
			if(!in_array($account_type,array(0,1))){
                showmessage('账户类型不正确！');
                //exit('<script>alert("账户类型不正确");location.href="'.U('peer/sell/sell').'"</script>');
			}
			
			$personal_wallet=db('money_finance')->where(array('uid'=>$uid))->find();//个人财务表
			$setting=db('setting')->where(array('key'=>'p_proportion'))->find();//出售母币比例
			
			/*
			uid 用户ID
			types 1为卖出积分，2为撤销卖出
			member_z_bi 子币
			member_bi 母币
			money 现金积分
			*/
            $p_fees1=db('setting')->where(array('key'=>'p_fees'))->find();//手续费比例
            $shouxu = $num * $p_fees1['value'];
			if($account_type == 0){//出售ATS
                $msg = "ATS卖出：";
				if($personal_wallet['member_bi'] < $num + $shouxu){
                    showmessage('您的ATS余额不足，不可出售！');
                    //exit('<script>alert("您的ATS余额不足，不可出售！");location.href="'.U('peer/sell/sell').'"</script>');
				}
			
				$balance=$personal_wallet['member_bi'] * $setting['value'];//母币余额 * 档次出售比例
                if($num > $balance){
						showmessage("您本次最多可出售{$balance}个ATS！");
                    //exit('<script>alert("您本次最多可出售'.$balance.'个ATS！");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
                }
			
				$qb_data=array(
					'uid'=>$uid,
					'types'=>'1',
					'member_bi'=>$num+$shouxu,
					'text'=>'会员出售ATS时，系统发现此会员账户余额数据异常，停止此会员出售ATS交易！',
				);
                $this->personal_wallet2($qb_data);
				
			}elseif($account_type == 1){//出售EP
                $msg = "AE卖出：";
				if($personal_wallet['member_z_bi'] < $num + $shouxu){
                    showmessage('您的AE余额不足，不可出售！');
				}
				
				$balance=$personal_wallet['member_z_bi'] * $setting['value'];//母币余额 * 档次出售比例
                if($num > $balance){
					showmessage("您本次最多可出售{$balance}个AE！");
                }
				
				$qb_data=array(
					'uid'=>$uid,
					'types'=>'1',
					'member_z_bi'=>$num+$shouxu,
					'text'=>'会员出售EP时，系统发现此会员账户余额数据异常，停止此会员出售EP交易！',
				);
                $this->personal_wallet2($qb_data);
			}
            $count_price=$num * $price;//出售数量 * 出售金额=总价
			$account_arr=array('member_bi','member_z_bi');

			$data=array();
			$data['num']=$num;//出售数量
			$data['price']=$price;//出售单价
			$data['order_type']=1;//出售类型0买入1卖出
			$data['account_type']=$account_type;//积分类型0母币1子币
			$data['count_price']=$count_price;//出售总价
			$data['fee']=$shouxu; //出售手续费
			$data['order_create_time']=time();//出售时间
			$data['seller_id']=$uid;//出售用户
			$data['seller_user']=$this->member['username'];//出售用户名
			$data['seller_realname']=$this->member['realname'];//出售姓名
			$data['order_no']=build_order_no('');//订单编号

			if(db('trading_order')->data($data)->insert()){
				model('Member/MemberDetails')->add_details($this->member['id'],$account_arr[$account_type],'提交卖出订单','-'.$num);
                $_member = db('money_finance')->where("uid = {$uid}")->find();
                $log_info = array(
                    'mid' => $uid,
                    'value' => '-'.$num,
                    'type' => 'member_bi',
                    'msg' => $msg."".$num,
                    'dateline' => time(),
                    'admin_id' => (defined('IN_ADMIN')) ? ADMIN_ID : 0
                );
                db('member_log')->data($log_info)->insert();
                showmessage('订单卖出成功！','',1);
			}else{
                showmessage('订单卖出失败！');
			}
		}else{
			$type = $_GET['type'];
			$acc_type = $_GET['acc_type'];
			$sell_bili = db("setting")->where(array('key'=>'sell_bili'))->value("value");  //出售数量基数
			$sell_di = db("setting")->where(array('key'=>'sell_di'))->value("value");       //出售最低数量
			$value = db('setting')->where("`key` = 'usdt'")->value('value');
        	$usdt = round(5 / $value, 4);
        	$this->assign('usdt', $usdt);
            $this->assign('sell_bili',$sell_bili);
            $this->assign('sell_di',$sell_di);
			$this->assign('type',$type);
            $this->assign('acc_type',$acc_type);
			return $this->fetch();
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
                if ($keys['mother_currency']==$x_keys || $keys['mother_currency'] == '') {

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
                    db('exception_log')->data($log_data)->insert();
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
	

}

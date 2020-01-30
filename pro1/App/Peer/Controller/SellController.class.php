<?php

namespace Peer\Controller;
use Member\Controller\CheckController;
class SellController extends CheckController {

    public function _initialize() {
        parent::_initialize();
    }


    public function sell(){

        if(IS_POST){
            $uid=$this->member['id'];//用户ID
            //$num=(int)abs(I('post.num'));//出售数量
            $num = $_POST['num'];

            //$account_type=(int)abs(I('post.account_type'));//出售类型0母币1字币
            $account_type=$_POST['account_type'];
            $price=sprintf('%.2f',abs(I('post.price')));//出售单价
		

            $trading_order=M('trading_order');//出售币记录表

            $bank= M('member_profile')->where(array('uid'=>$this->member['id'],'default'=>1))->find();
            $exist=$trading_order->where(array('seller_id'=>$uid,'order_type'=>'1','order_status'=>array('neq','3')))->count();
            $hb_num = M('setting')->where(array('key'=>'hb_num'))->getField('value');
			if($exist>=$hb_num && $account_type != 2){
                exit('<script>alert("您最多可挂卖'.$hb_num.'笔HB订单");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
			}

            if(empty($bank['account_name']) || empty($bank['account_bank']) || empty($bank['bank_account'])){
                exit('<script>alert("请先到个人中心完善您的收款信息！");location.href="'.U('member/account/base').'"</script>');
            }


            if($num<=0){
                exit('<script>alert("请输入出售数量！");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
            }

            if($price<=0){
                exit('<script>alert("请输入出售单价！");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
            }

            $price_arr=get_min_price();

//			if($price<$price_arr['min_price']){
//
//				showmessage('交易价不能小于'.$price_arr['min_price']);
//			}
//
//			if($price>$price_arr['max_price']){
//
//				showmessage('交易价不能大于'.$price_arr['max_price']);
//			}

            if(!in_array($account_type,array(0,1,2))){
                exit('<script>alert("账户类型不正确");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
            }

            $personal_wallet=M('money_finance')->where(array('uid'=>$uid))->find();//个人财务表
            $setting=M('setting')->where(array('key'=>'p_proportion'))->find();//出售母币比例

            /*
            uid 用户ID
            types 1为卖出积分，2为撤销卖出
            member_z_bi 子币
            member_bi 母币
            money 现金积分
            */
            $p_fees1=M('setting')->where(array('key'=>'p_fees'))->find();//手续费比例
            $shouxu = $num * $p_fees1['value'];
            if($account_type == 0){//出售ATS
                if($personal_wallet['member_bi'] < $num + $shouxu){
                    exit('<script>alert("您的HB余额不足，不可出售！");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
                }

                $balance=$personal_wallet['member_bi'] * $setting['value'];//母币余额 * 档次出售比例
                if($num > $balance){

                    exit('<script>alert("您本次最多可出售'.$balance.'个认购HM！");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
                }

                $qb_data=array(
                    'uid'=>$uid,
                    'types'=>'1',
                    'member_bi'=>$num+$shouxu,
                    'text'=>'会员出售HB时，系统发现此会员账户余额数据异常，停止此会员出售HB交易！',
                );

                R('Member/MemberSort/personal_wallet',array($qb_data));

            }elseif($account_type == 1){//出售EP

                if($personal_wallet['member_z_bi'] < $num + $shouxu){
                    exit('<script>alert("您的奖励HM余额不足，不可出售！");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
                }
				
				$balance=$personal_wallet['member_z_bi'] * $setting['value'];//母币余额 * 档次出售比例
                if($num > $balance){

                    exit('<script>alert("您本次最多可出售'.$balance.'个奖励HM！");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
                }

                $qb_data=array(
                    'uid'=>$uid,
                    'types'=>'1',
                    'member_z_bi'=>$num+$shouxu,
                    'text'=>'会员出售奖励HM时，系统发现此会员账户余额数据异常，停止此会员出售奖励HM交易！',
                );
                R('Member/MemberSort/personal_wallet',array($qb_data));
            }elseif($account_type == 2){
                if($personal_wallet['money'] < $num + $shouxu){
                    exit('<script>alert("您的USD余额不足，不可出售！");location.href="'.U('peer/sell/sell',array('type'=>$account_type)).'"</script>');
                }

                $qb_data=array(
                    'uid'=>$uid,
                    'types'=>'1',
                    'money'=>$num+$shouxu,
                    'text'=>'会员出售USD时，系统发现此会员账户余额数据异常，停止此会员出售USD交易！',
                );
                R('Member/MemberSort/personal_wallet',array($qb_data));
            }

            //$p_fees=M('setting')->where(array('key'=>'p_fees'))->find();//手续费比例

//			if($p_fees['value'] > 0){
//				$fee=$num * $p_fees['value'];//扣除手续费
//				$b_balance=$num * (1-$p_fees['value']);//减去手续费的数量
//				$count_price=$num * $price;//出售数量 * 出售金额
//			}else{
//				$fee='0';
//				$b_balance=$num;
//				$count_price=$num * $price;//出售数量 * 出售金额
//			}

            $count_price=$num * $price;//出售数量 * 出售金额=总价
            $account_arr=array('member_bi','member_z_bi','money');

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


            if(M('trading_order')->data($data)->add()){
                D('Member/MemberDetails')->add_details($this->member['id'],$account_arr[$account_type],'提交卖出订单','-'.$num);
                exit('<script>alert("订单卖出成功");location.href="'.U('trade/home',array('type'=>$_GET['type'])).'"</script>');
            }else{
                exit('<script>alert("订单卖出失败");location.href="'.U('peer/sell/sell',array('type'=>$_GET['type'])).'"</script>');
            }

        }else{
            $type = $_GET['type'];
			if($type != 1){
				die("非法操作");
			}
            $this->assign('type',$type);
            $usd_price = M("setting")->where(array('key'=>'usd_price'))->getField('value');
            $p_fee=M('setting')->where(array('key'=>'p_fees'))->find();//手续费比例
            $this->assign('p_fee',$p_fee['value']);
            $this->assign('usd_price',$usd_price);
            $this->display();
        }




    }
	
	
	

}

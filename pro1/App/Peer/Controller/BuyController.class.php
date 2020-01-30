<?php

namespace Peer\Controller;
use Member\Controller\CheckController;
class BuyController extends CheckController {

    public function _initialize() {
        parent::_initialize();
    }

    public function buy(){
        if(IS_POST){
            $num=(int)abs(I('post.num'));
            $price=sprintf('%.2f',abs(I('post.price')));
            $account_type=$_POST['account_type'];
            if($num<=0){
                exit('<script>alert("请输入数量");location.href="'.U('peer/buy/buy',array('type'=>$account_type)).'"</script>');
            }
            if($price<=0){
                exit('<script>alert("请输入单价");location.href="'.U('peer/buy/buy',array('type'=>$account_type)).'"</script>');
            }

            $bank= M('member_profile')->where(array('uid'=>$this->member['id'],'default'=>1))->find();
//            $exist=$trading_order->where(array('seller_id'=>$uid,'order_type'=>'1','order_status'=>array('neq','3')))->find();
//			if($exist){
//                exit('<script>alert("您已经有出售中的订单，不可重复出售！");location.href="'.U('peer/sell/sell').'"</script>');
//			}

            if(empty($bank['account_name']) || empty($bank['account_bank']) || empty($bank['bank_account'])){
                exit('<script>alert("请先到个人中心完善您的收款信息！");location.href="'.U('member/account/base').'"</script>');
            }
//			$price_arr=get_min_price();
//			if($price<$price_arr['min_price']){
//				showmessage('交易价不能小于'.$price_arr['min_price']);
//                exit('<script>alert("交易价不能小于'.$price_arr['min_price'].'");location.href="'.U('peer/buy/buy').'"</script>');
//			}
//			if($price>$price_arr['max_price']){
//                exit('<script>alert("交易价不能大于'.$price_arr['max_price'].'");location.href="'.U('peer/buy/buy').'"</script>');
//			}
            $account_arr=array('member_bi','member_z_bi','money');
            $data=array();
            $data['account_type']=$account_type;
            $data['num']=$num;
            $data['price']=$price;
            $data['order_type']=0;
            $data['count_price']=$num*$price;
            $data['order_create_time']=time();
            $data['user_id']=$this->member['id'];
            $data['buy_user']=$this->member['username'];
            $data['user_realname']=$this->member['realname'];
            $data['order_no']=build_order_no('');


            if(M('trading_order')->data($data)->add()){
                D('Member/MemberDetails')->add_details($this->member['id'],$account_arr[$account_type],'提交买入订单','-'.$num);
                exit('<script>alert("买入订单发布成功");location.href="'.U('trade/home',array('type'=>$_GET['type'])).'"</script>');
            }else{
                exit('<script>alert("订单买入失败");location.href="'.U('peer/buy/buy',array('type'=>$_GET['type'],'acc_type'=>$_GET['acc_type'])).'"</script>');
            }

        }else{
            $type = $_GET['type'];
			if($type != 1){
				die("非法操作");
			}
            $this->assign('type',$type);
			 $usd_price = M("setting")->where(array('key'=>'usd_price'))->getField('value');
         
            $this->assign('usd_price',$usd_price);
            $this->display();
        }
    }

}

<?php

namespace app\peer\controller;
use app\member\controller\Check;
class Buy extends Check {

     public function _initialize() {
        parent::_initialize();
        $open_time=db('setting')->where(array('key'=>'p_open_time'))->value('value');

        $checkDayStr=date('Y-m-d ',time());

        $p_time=rtrim($open_time, '-');//去除逗号
        $prev_path = explode('-', $p_time);//组成数组

        $timeBegin1 = strtotime($checkDayStr."$prev_path[0]".":00");
        $timeEnd1 = strtotime($checkDayStr."$prev_path[1]".":00");


        $curr_time = time();

        if($curr_time >= $timeBegin1 && $curr_time <= $timeEnd1){

        }else{
            echo '<script>alert("交易区未开放！");location.href="'.url('/Peer/trade/home',array('type'=>$_GET['type'])).'"</script>';
            exit();
        }
    }
	
	public function buy(){
		if(is_post()){
			$num=(int)abs(input('post.num'));
			$price=sprintf('%.2f',abs(input('post.price')));
            $account_type=$_POST['account_type'];
			if($num<=0){
                showmessage('请输入数量！');
			}
			if($price<=0){
                showmessage('请输入单价！');
			}
            $bank= db('member_profile')->where(array('uid'=>$this->member['id'],'default'=>1))->find();
//            $exist=$trading_order->where(array('seller_id'=>$uid,'order_type'=>'1','order_status'=>array('neq','3')))->find();
//			if($exist){
//                exit('<script>alert("您已经有出售中的订单，不可重复出售！");location.href="'.U('peer/sell/sell').'"</script>');
//			}

            if(empty($bank['account_bank'])){
                showmessage('请先到个人中心完善您的收款信息！');
            }
//			$price_arr=get_min_price();
//			if($price<$price_arr['min_price']){
//				showmessage('交易价不能小于'.$price_arr['min_price']);
//                exit('<script>alert("交易价不能小于'.$price_arr['min_price'].'");location.href="'.U('peer/buy/buy').'"</script>');
//			}
//			if($price>$price_arr['max_price']){
//                exit('<script>alert("交易价不能大于'.$price_arr['max_price'].'");location.href="'.U('peer/buy/buy').'"</script>');
//			}
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
			if(db('trading_order')->data($data)->insert()){
                showmessage('买入订单发布成功！');
			}else{
                showmessage('订单买入失败！');
			}
		}else{
            $type = $_GET['type'];
            $acc_type = $_GET['acc_type'];
            $value = db('setting')->where("`key` = 'usdt'")->value('value');
            $usdt = round(5 / $value, 4);
            $this->assign('usdt', $usdt);
            $this->assign('type',$type);
            $this->assign('acc_type',$acc_type);
			return $this->fetch();
		}
	}

}

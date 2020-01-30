<?php

namespace Peer\Controller;

use app\Common\Controller\Admin;
use think\Db;

class Transaction extends Admin {

    public function _initialize() {
        parent::_initialize();
        $this->service = model('Peer/TradingOrder', 'Service');
    }

	public function buy(){
		
		$sqlmap = array();
	
        if ($_GET['status']!='') {
            $sqlmap['order_status'] = $_GET['status'];
        }

        if ($_GET['keyword']) {
            $sqlmap['user_id|buy_user|seller_user'] = array('like', '%' . $_GET['keyword'] . '%');
        }

		$sqlmap['order_type']=0;
		$_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $list = $this->service->select($sqlmap,20);
		$count =db('TradingOrder')->where($sqlmap)->count();
        $pages = $this->admin_pages($count, $_GET['limit']);

		
		$this->assign('list',$list)->assign('pages',$pages)->assign('count',$count);
		return $this->fetch();
	}	
	
	
	public function sell(){
		
		$sqlmap = array();
	
        if ($_GET['status']!='') {
            $sqlmap['order_status'] = $_GET['status'];
        }

        if ($_GET['keyword']) {
            $sqlmap['user_id|buy_user|seller_user'] = array('like', '%' . $_GET['keyword'] . '%');
        }

		$sqlmap['order_type']=1;
		$_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $list = $this->service->select($sqlmap,20);
		$count =db('TradingOrder')->where($sqlmap)->count();
        $pages = $this->admin_pages($count, $_GET['limit']);

		
		$this->assign('list',$list)->assign('pages',$pages)->assign('count',$count);
		return $this->fetch();
	}
    public function shenshu(){
        $sqlmap = array();

        if ($_GET['status']!='') {
            $sqlmap['order_status'] = $_GET['status'];
        }

        if ($_GET['keyword']) {
            $sqlmap['user_id|buy_user|seller_user'] = array('like', '%' . $_GET['keyword'] . '%');
        }

        $sqlmap['order_type']=1;
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        //$list = $this->service->select($sqlmap,20);

       // $count =M('TradingOrder')->where($sqlmap)->count();
        $Model = new Db();
        $count_sql = $Model->query("SELECT count(*) as count FROM gboy_trading_shenshu a,gboy_trading_order b WHERE a.oid = b.id and b.order_status = 2");
        $count = $count_sql[0]['count'];
        $pages = $this->admin_pages($count, $_GET['limit']);

        $Model = new Db();
        $list = $Model->limit($pages->firstRow . ',' . $pages->listRows)->query("SELECT a.*,b.* FROM gboy_trading_shenshu a,gboy_trading_order b WHERE a.oid = b.id and b.order_status = 2");
        $this->assign('list',$list)->assign('pages',$pages)->assign('count',$count);
        return $this->fetch();
    }
	
	
	public function cancel(){
        $id=(int)input('id','0');
        $t=db('trading_order');
		$a=$_GET["a"];

        if($info=$t->where(array('id'=>$id))->find()){

            if($info['order_status']!=3 && $info['seller_id']){
				if($info['order_status'] != -1){
					if($info["account_type"] == 0){
                    $zh_types = "member_bi";
                    $key_types = "mother_currency";
					}else{
						$zh_types = "member_z_bi";
						$key_types = "currency";
					}
					$text=$info['order_no'].'订单取消';
					model('Member/MemberDetails')->add_details($info['seller_id'],$zh_types,$text,$info['num']+$info['fee']);

					$grqb_data['zh_types']=$zh_types;//购物积分字段
					$grqb_data['key_types']=$key_types;//购物积分加密字段
					$grqb_data['uid']=$info['seller_id'];//获取积分用户ID
					$grqb_data['types']='2';//累加积分
					$grqb_data['number']=$info['num']+$info['fee'];//本次产生的积分数量
					$grqb_data['text']='后台取消会员卖单时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
					$this->personal_wallet($grqb_data);//调用个人钱包方法
				}
            }

            $t->where(array('id'=>$id))->data(array('order_status'=>'-1'))->save();

        }
        showmessage(L('_os_success_'), $_SERVER['HTTP_REFERER'], 1);
	}
	public function su(){
        $id=(int)I('id','0');
        $t=M('trading_order');

        if($info=$t->where(array('id'=>$id))->find()){

            if($info['order_status']>0 && $info['seller_id']){
                $account_arr=array('0'=>'member_bi','1'=>'member_z_bi');
                $c_arr=array('0'=>'mother_currency','1'=>'currency');
                $text=$info['order_no'].'订单完成';
                model('Member/MemberDetails')->add_details($info['seller_id'],$account_arr[$info['account_type']],$text,$info['num']+$info['fee']);

                //金额付给买家
                $type = $account_arr[$info['account_type']];
                $c_type = $c_arr[$info['account_type']];

                $grqb_data['zh_types']=$type;//购物积分字段
                $grqb_data['key_types']=$c_type;//购物积分加密字段
                $grqb_data['uid']=$info['user_id'];//获取积分用户ID
                $grqb_data['types']='2';//累加积分
                $grqb_data['number']=$info['num'];//本次产生的积分数量
                $grqb_data['text']='订单取消时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
                $this->personal_wallet($grqb_data);//调用个人钱包方法

            }

            $t->where(array('id'=>$id))->data(array('order_status'=>'3'))->save();

        }
        showmessage(L('_os_success_'), url('shenshu'), 1);
    }
	
    /*
           //执行个人钱包操作动作
           $grqb_data['zh_types']='shop_integral';//购物积分字段
           $grqb_data['key_types']='shop_integral_key';//购物积分加密字段
           $grqb_data['uid']=$pid;//获取积分用户ID
           $grqb_data['types']='2';//累加积分
           $grqb_data['number']=$shop_jf;//本次产生的积分数量
           $grqb_data['text']='推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
           $grqb_data['is_mx']=0;//是否添加明细，0不添加，1添加
           $grqb_data['member_giveid'] = $id;//提供会员id
           $grqb_data['money_type'] = 11;//明细类型
           $this->personal_wallet($grqb_data);//调用个人钱包方法
           */

    //累加个人钱包
    public function personal_wallet($qb_data){
        $qb_types=$qb_data['zh_types'];//账户类型
        $key_types=$qb_data['key_types'];//加密类型
        $id=$qb_data['uid'];//会员id
        $types=$qb_data['types'];//1为减去积分，2为累加积分
        $number=$qb_data['number'];//交易数量
        //添加明细参数
        $is_mx = $qb_data['is_mx'];//是否添加明细，0不添加，1添加
        $member_giveid = $qb_data['member_giveid'];//提供会员id
        $money_type = $qb_data['money_type'];//明细类型

        $money_finance=db('money_finance');//个人财务表
        $cw_encryption=db('secure_encryption');//个人财务加密表
        $money_types=db('money_types');//交易详情表
        $keys=$cw_encryption->where(array('uid'=>$id))->find();
        $cw=$money_finance->where(array('uid'=>$id))->find();
        if ($cw[$qb_types] > 0){
            $x_keys=md5(md5($cw[$qb_types]) . $keys['encrypt']);//安全加密
            if ($keys[$key_types]==$x_keys){
                if ($types==1){
                    $money_finance->where(array('uid'=>$id))->setDec($qb_types,$number);//减去积分
                }elseif ($types==2){
                    $money_finance->where(array('uid'=>$id))->setInc($qb_types,$number);//累加积分
                }
                $member_bii=$money_finance->where(array('uid'=>$id))->getField($qb_types);
                $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
                $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);

                if($is_mx == 1){   //开始添加明细
                    $bdata['member_getid'] = $id;//获得会员id
                    $bdata['member_giveid'] = $member_giveid;//提供会员id
                    $bdata['money_produtime'] = time();
                    $bdata['money_nums'] = $number;//当次产生金额
                    $bdata['money_type'] = $money_type;//代理等级类型
                    $money_types->add($bdata);//写入收益详情表
                }
            }else{
                $log_data=array(
                    'uid'=>$id,
                    'text'=>$qb_data['text'],
                    'time'=>time(),
                );
               db('exception_log')->data($log_data)->add();
            }
        }else{
            if ($types==1) {
                $money_finance->where(array('uid'=>$id))->setDec($qb_types,$number);//减去子积分
            }elseif ($types==2) {
                $money_finance->where(array('uid'=>$id))->setInc($qb_types,$number);//累加子积分
            }
            $member_bii=$money_finance->where(array('uid'=>$id))->getField($qb_types);
            $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
            $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);
            if($is_mx == 1){   //开始添加明细
                $bdata['member_getid'] = $id;//获得会员id
                $bdata['member_giveid'] = $member_giveid;//提供会员id
                $bdata['money_produtime'] = time();
                $bdata['money_nums'] = $number;//当次产生金额
                $bdata['money_type'] = $money_type;//代理等级类型
                $money_types->add($bdata);//写入收益详情表
            }
        }
    }
  
    public function del() {
		$id = (array) $_REQUEST['id'];
		
        $sqlmap = array();
        $sqlmap['id'] = array('in',$id);

        $result = $this->service->del($sqlmap);

        showmessage(L('_os_success_'), $_SERVER['HTTP_REFERER'], 1);
    }
	
	
}

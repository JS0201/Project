<?php

namespace app\peer\controller;
use app\member\controller\Check;
use think\Db;
class Order extends Check {

    public function _initialize() {
        parent::_initialize();
		
		$this->service = model('Peer/TradingOrder','Service');
    }
	
	
	public function index(){
		
		
	}

    public function buy(){
        $user_id = $this->member['id'];
        $this->assign('user_id',$user_id);
        $trading_order = db("trading_order");
        $condition['user_id'] = $this->member['id'];
        $condition['seller_id'] = $this->member['id'];
        //$condition['_logic'] = 'OR';
        // 把查询条件传入查询方法
        $lists = $trading_order->where($condition)->order('id desc')->select();
        $this->assign('list',$lists);
        // 把查询条件传入查询方法
        //$Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $lists2 = Db::query("select * from gboy_trading_order where user_id = $user_id and order_status in (0,1,2) or seller_id = $user_id and order_status in (0,1,2) order by id desc");
        $this->assign('list2',$lists2);
        $value = db('setting')->where("`key` = 'usdt'")->value('value');
        $usdt = round(5 / $value, 4);
        $this->assign('usdt', $usdt);
        // 把查询条件传入查询方法
        //$Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $lists3 = Db::query("select * from gboy_trading_order where user_id = $user_id and order_status = 3 or seller_id = $user_id and order_status =3 order by id desc");
        $this->assign('list3',$lists3);
        $type = $_GET['type'];
        $acc_type = $_GET['acc_type'];
        $this->assign('type',$type);
        $this->assign('acc_type',$acc_type);
        return $this->fetch();
	}	
	
	public function sell(){

		$status=$_GET['status'];

		$sqlmap=array();
		$sqlmap['seller_id']=$this->member['id'];

		if($status!=''){

			$sqlmap['order_status']= (int) $status;
		}
		$result=$this->service->select($sqlmap);
		$this->assign('list',$result['lists'])->assign('page',$result['page'])->display();

	}
	
	
	public function buy_details(){
		$sn=input('sn','');
		if(is_post()){
            $type = input('type');
			$sqlmap=array();
			$sqlmap['order_no']=$sn;
			$sqlmap['user_id']=$this->member['id'];
			//$sqlmap['order_status']=1;
			if(!$info=$this->service->find($sqlmap)){

				showmessage('订单不存在');
			}
			
			if($info['order_status']==1){
				
				if(!isset($_FILES['pic']['name'])){
					showmessage('请上传凭证');
				}
                $upload = new \org\Uploader();
                $result = $upload->upload('pic','uplaod/c2c/');
		        if(!$result) {
                    showmessage($upload->errors);
                }
                $img = explode('/', $result);
		        $image = $img[3].'/'.$img[4].'/'.$img[5].'/'.$img[6];
				if(model('trading_order')->where(['id'=>$info['id']])->data(['pay_pic'=>$image,'order_status'=>2,'order_pay_time'=>time()])->update()){
				
					showmessage('凭证上传成功','',1);
				}else{
					showmessage('凭证上传失败');
				}
			}elseif($info['order_status']==0){
				
				$re=db('trading_order')->where(['id'=>$info['id'],'order_status'=>0])->update(['order_status'=>-1,'order_cance_time'=>time()]);
				
				if($re){
					showmessage('撤销成功','',1);
				}else{
					showmessage('撤销失败');
				}
				
			}elseif($info['order_status']==2 && $type == 1){   //申诉
                $uid = $this->member['id'];
                $check = db('trading_shenshu')->where(array('uid' => $uid,'oid'=>$info['id']))->find();
                if($check){
                    showmessage('此订单已经申诉过，无需重复提交','',1);
                }
                //判断哪方
                if($uid == $info['seller_id']){
                    $fang = 0;  //卖方
                }elseif($uid == $info['user_id']){
                    $fang = 1;  //买方
                }
                $liyou = input('liyou');
                $tdata['uid']=$uid;
                $tdata['oid']=$info['id'];
                $tdata['fang']=$fang;
                $tdata['liyou']=$liyou;
                $tdata['time']=time();
                db('trading_shenshu')->data($tdata)->insert();
                showmessage('申诉成功','',1);
            }
		}else{
			$data=array();
			$data['order_no']=$sn;
			$data['user_id']=$this->member['id'];
			$info=db('trading_order')->where($data)->find();
            if($info['order_type']){
                $info['_username']=db('member')->where(array('id'=>$info['seller_id']))->value('username');
            }else{
                $info['_username']=db('member')->where(array('id'=>$info['user_id']))->value('username');
            }
            switch($info['order_status']){
                case 0:
                    $_status='待接单';
                    break;
                case 1:
                    $_status='待打款';
                    break;
                case 2:
                    $_status='已打款';
                    break;
                case 3:
                    $_status='交易完成';
                    break;
                case -1:
                    $_status='取消';
                    break;

            }

            $info['_status']=$_status;

            if($info['seller_id']){
                $info['_bank']=db('member_profile')->where(array('uid'=>$info['seller_id']))->find();
            }
            $seller_id = $info['seller_id']; //卖id
            $seller = db('member_profile')->where(array('uid' => $seller_id,'default'=>1))->find(); //卖家信息
			$seller['mobile'] = db('member')->where("id = {$seller_id}")->value("username");
            $this->assign('seller',$seller);
			if(!$info){
				showmessage('');
			}
			$config = config('aliyun_oss');
            $info['pay_pic'] = $config['Url'].$info['pay_pic'];
			$this->assign('info',$info);
			return $this->fetch();
		}
	}
	
	
	
	
	
	    public function sell_details(){

        $sn=input('sn','');
        if(is_post()){
            $type = input('type');
            $sqlmap=array();
            $sqlmap['order_no']=$sn;
            $sqlmap['seller_id']=$this->member['id'];
            //$sqlmap['order_status']=2;
            if(!$info=$this->service->find($sqlmap)){
                showmessage('订单不存在');
            }
            if($info['order_status']==2 && $type == 0){
                if(db('trading_order')->where(['id'=>$info['id']])->update(['order_status'=>3,'order_ok_time'=>time()])){
                    if($info['account_type'] == 0) {   //ats
                        model('Member/MemberDetails')->add_details($info['user_id'],'member_bi','挂买交易成功',$info['num']);
                        //执行个人钱包操作动作
                        $grqb_data['zh_types']='member_bi';//购物积分字段
                        $grqb_data['key_types']='mother_currency';//购物积分加密字段
                        $grqb_data['uid']=$info['user_id'];//获取积分用户ID
                        $grqb_data['types']='2';//累加积分
                        $grqb_data['number']=$info['num'];//本次产生的积分数量
                        $grqb_data['text']='推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
                        $this->personal_wallet($grqb_data);//调用个人钱包方法
                    }else{ //ae
                        $buyin = db('setting')->where("key = 'ae_buyin'")->value('value');
                        model('Member/MemberDetails')->add_details($info['user_id'],'member_z_bi','挂买交易成功',$info['num']);
                        //执行个人钱包操作动作
                        $grqb_data['zh_types']='member_z_bi';//购物积分字段
                        $grqb_data['key_types']='currency';//购物积分加密字段
                        $grqb_data['uid']=$info['user_id'];//获取积分用户ID
                        $grqb_data['types']='2';//累加积分
                        $grqb_data['number']=$info['num'] * (1 + $buyin);//本次产生的积分数量
                        $grqb_data['text']='推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
                        $this->personal_wallet($grqb_data);//调用个人钱包方法
                    }
                    showmessage('确认成功','',1);
                }else{
                    showmessage('确认失败');
                }

			}elseif(empty($info['order_status'])){

                $account_type = $info['account_type'];
                $re=model('trading_order')->where(['id'=>$info['id'],'order_status'=>0])->update(['order_status'=>-1,'order_cance_time'=>time()]);
                if($re){
                    $account_arr=array('0'=>'member_bi','1'=>'member_z_bi');
                    model('Member/MemberDetails')->add_details($info['seller_id'],$account_arr[$info['account_type']],'撤销订单',$info['num']+$info['fee']);

                    if($account_type == 0){//出售母币
                        $grqb_data['zh_types']='member_bi';//购物积分字段
                        $grqb_data['key_types']='mother_currency';//购物积分加密字段
                        $grqb_data['uid']=$info['seller_id'];//获取积分用户ID
                        $grqb_data['types']='2';//累加积分
                        $grqb_data['number']=$info['num']+$info['fee'];//本次产生的积分数量
                        $grqb_data['text']='推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
                        $this->personal_wallet($grqb_data);//调用个人钱包方法
                    }elseif($account_type == 1){//出售子币
                        $grqb_data['zh_types']='member_z_bi';//购物积分字段
                        $grqb_data['key_types']='currency';//购物积分加密字段
                        $grqb_data['uid']=$info['seller_id'];//获取积分用户ID
                        $grqb_data['types']='2';//累加积分
                        $grqb_data['number']=$info['num']+$info['fee'];//本次产生的积分数量
                        $grqb_data['text']='推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
                        $this->personal_wallet($grqb_data);//调用个人钱包方法
                    }

                    showmessage('撤销成功','',1);
                }else{
                    showmessage('撤销失败');
                }

            }elseif($info['order_status']==2 && $type == 1){   //申诉
                $uid = $this->member['id'];
                $check = db('trading_shenshu')->where(array('uid' => $uid,'oid'=>$info['id']))->find();
                if($check){
                    showmessage('此订单已经申诉过，无需重复提交','',1);
                }
                //判断哪方
                if($uid == $info['seller_id']){
                    $fang = 0;  //卖方
                }elseif($uid == $info['user_id']){
                    $fang = 1;  //买方
                }
                $liyou = input('liyou');
                $tdata['uid']=$uid;
                $tdata['oid']=$info['id'];
                $tdata['fang']=$fang;
                $tdata['liyou']=$liyou;
                $tdata['time']=time();
                db('trading_shenshu')->data($tdata)->insert();
                showmessage('申诉成功','',1);
            }

        }else{
            $data=array();
            $data['order_no']=$sn;
            $data['seller_id']=$this->member['id'];
            $info=db('trading_order')->where($data)->find();
            $user_id = $info['user_id']; //买家id
            $user = db('member_profile')->where(array('uid' => $user_id,'default'=>1))->find(); //买家信息
            $user['mobile'] = db('member')->where("id = {$user_id}")->value('username');
            $this->assign('user',$user);
            if(!$info){
                showmessage('');
            }
            $this->assign('info',$info);
            return $this->fetch();
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
                $member_bii=$money_finance->where(array('uid'=>$id))->value($qb_types);
                $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
                $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);

                if($is_mx == 1){   //开始添加明细
                    $bdata['member_getid'] = $id;//获得会员id
                    $bdata['member_giveid'] = $member_giveid;//提供会员id
                    $bdata['money_produtime'] = time();
                    $bdata['money_nums'] = $number;//当次产生金额
                    $bdata['money_type'] = $money_type;//代理等级类型
                    $money_types->data($bdata)->insert();//写入收益详情表
                }
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
            }elseif ($types==2) {
                $money_finance->where(array('uid'=>$id))->setInc($qb_types,$number);//累加子积分
            }
            $member_bii=$money_finance->where(array('uid'=>$id))->value($qb_types);
            $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
            $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);
            if($is_mx == 1){   //开始添加明细
                $bdata['member_getid'] = $id;//获得会员id
                $bdata['member_giveid'] = $member_giveid;//提供会员id
                $bdata['money_produtime'] = time();
                $bdata['money_nums'] = $number;//当次产生金额
                $bdata['money_type'] = $money_type;//代理等级类型
                $money_types->data($bdata)->insert();//写入收益详情表
            }
        }
    }

	
}

<?php

namespace Member\Controller;

use Common\Controller\AdminController;

class SimuController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        //$this->service = D('Member/Report', 'Service');
    }

	public function index(){
		$result=M('simu')->select();
        $setting = M("tixian_setting")->where(array("id"=>1))->find();
        $date1=strtotime($setting['simu_end']);
        $this->assign('end_time',$setting['simu_end']);
        $this->assign('date1',$date1);
		$this->assign('list',$result)->display();
	}

    public function edit(){
        $sqlmap=array();
        $id=(int)$_GET['id'];
        $info=M("simu")->where(array('id'=>$id))->find();

        if(!$info){
            showmessage(L('_data_not_exist_'));
        }

        $this->assign('info',$info)->display();
    }
    public function save(){
		$tdata['bili']=$_POST['bili'];
		$tdata['rengou_price']=$_POST['rengou_price'];
	
        $r=M("simu")->where(array('id'=>$_POST['id']))->save($tdata);
		//$info=M("simu")->where(array('id'=>1))->find();
        
        //M('setting')->where(array('key'=>"p_open_price"))->save($tdata);
		//M('setting')->where(array('key'=>"p_open_price"))->save($tdata);
        if(!$r){
            showmessage(L('_os_success_'),U('index'),1);
        }
        showmessage(L('_os_success_'),U('index'),1);
    }
	
	public function stat_time(){
        $url = U('member/simu/index');
        $newdate = date('Y-m-d H:i:s',strtotime("+90 day"));
        M('tixian_setting')->where(array('id'=>1))->setField('simu_end',$newdate);
        //修改第一期的上线时间
        M('simu')->where(array('id'=>1))->setField('time',time());
        //修改第一期的剩余发行量
        $yunum=M('simu')->where(array('id'=>1))->getField('num');
        M('simu')->where(array('id'=>1))->setField('yunum',$yunum);
        showmessage("1",$url,1);
    }

    //释放私募期TKSC
    public function sf(){
        $url = U('member/simu/index');
        $sid = $_GET['id']; //私募期id
        $chong = M('member_chong');
        $money_finance = M("money_finance");
        $sfmx = M("sfmx");
        $setting = M("tixian_setting")->where(array("id"=>1))->find();
        $date1=strtotime($setting['simu_end']);//私募期结束时间
        $date1=date_create(date("Y-m-d",$date1));//数据保存的更新时间
        $date2=date_create(date("Y-m-d",time()));//当前时间
        if ($date2 <= $date1 ){
            showmessage("释放时间未到，不能释放",$url,0);
        }else{
            $is_add = $sfmx->where(array('type' => $sid))->select();
            if(!$is_add){
                $members = M('member')->where(array('gou'=>1))->select();
                foreach ($members as $k => $v) {
                    $tksc = $money_finance->where(array('uid'=>$v['id']))->getField('member_bi');
                    $money = $tksc/(6-$sid+1);
                    if($sid == 6){
                        $money = $tksc;   //如果是第6期，全部释放完
                    }
                    $grqb_data['zh_types']='member_bi';//购物积分字段
                    $grqb_data['key_types']='mother_currency';//购物积分加密字段
                    $grqb_data['uid']=$v['id'];//获取积分用户ID
                    $grqb_data['types']='1';//累加积分
                    $grqb_data['number']=$money;//本次产生的积分数量
                    $grqb_data['text']='会员余额时，系统发现HMK余额数据异常，停止给此会员执行累减HMK动作！';
                    $this->personal_wallet($grqb_data);//调用个人钱包方法

                    $grqb_data['zh_types']='member_s_bi';//购物积分字段
                    $grqb_data['key_types']='s_currency';//购物积分加密字段
                    $grqb_data['uid']=$v['id'];//获取积分用户ID
                    $grqb_data['types']='2';//累加积分
                    $grqb_data['number']=$money;//本次产生的积分数量
                    $grqb_data['text']='会员余额时，系统发现HB余额数据异常，停止给此会员执行累加HB动作！';
                    $this->personal_wallet($grqb_data);//调用个人钱包方法

                    //释放明细
                    $yunum = $money_finance->where(array('uid'=>$v['id']))->getField('member_bi');
                    $tdata['num']=$money;
                    $tdata['yunum']=$yunum;
                    $tdata['type']=$sid;
                    $tdata['time']=time();
                    $tdata['uid']=$v['id'];
                    $sfmx->add($tdata);
                }

                showmessage("释放成功",$url,1);
            }else{
                showmessage("本期已释放过，不得重复释放",$url,0);
            }
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

            $money_finance=M('money_finance');//个人财务表
            $cw_encryption=M('secure_encryption');//个人财务加密表
            $money_types=M('money_types');//交易详情表
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
                    M('exception_log')->data($log_data)->add();
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
}

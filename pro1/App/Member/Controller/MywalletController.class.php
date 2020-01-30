<?php

namespace Member\Controller;

class MywalletController extends CheckController {

    public function _initialize() {
        $this->service = D('Member', 'Service');
         parent::_initialize();
    }

   
    //充值 
    public function myRecharge(){
        //二维码
        $id = $this->member['id'];

           //usdt
           $member=M('member')->where(array('id'=>$id))->find();
           if($member['btc_address'] == 0){
               $usdt_address=M('usdt_address');
               $ucountnum=$usdt_address->where(array('status'=>0,'uid'=>$id))->count();   //获取数据库中未使用的条数总和
               if($ucountnum <= 0 ){ //创建生成usdt地址
                    unset($data);
                    $data = array();
                    $parm = "transName=getNewAddressUsdt";
                    $url=$this->coinurldecode($parm);   
                    $address = $content = http_getstr($url);
                    if ($address !== "验签失败") {
                        $data['address'] = trim($address);
                        $data['uid'] = $id;
                        $data['status']=1;
                        $data['add_time']=time();
                        $usdt_address->add($data); 
                    }
               }
               $usdt=$usdt_address->where(array('uid'=>$id))->find();
               $address=$usdt['address'];
               $data=array(
                   'btc_address'=>$address,
               );
               M('member')->where(array('id'=>$id))->save($data);
           }else{
               $address=$member['btc_address'];
           }

           $al=M('member')->where(array('id'=>$id))->getField('btc_code');   //二维码图片
           if($al){
               $membera = M('member')->where(array('id' => $id))->find();
               if($membera["is_buy3"] == 0){
                   //确定开始查该会员区块
                   $tdata['is_buy3']=1;
                   $tdata['buy_time3']=time();
                   M('member')->where(array('id'=>$id))->save($tdata);
               }
               $this->assign('address',$address);
               $this->assign('img_url',$al);
           }else{
               $membera = M('member')->where(array('id' => $id))->find();
               if($membera["is_buy3"] == 0){
                   //确定开始查该会员区块
                   $tdata['is_buy3']=1;
                   $tdata['buy_time3']=time();
                   M('member')->where(array('id'=>$id))->save($tdata);
               }
               $url = $address;
               $file_url="uploads/usdt_code/".$id.'.png';
               $img_url=phpqrcode($url,$file_url);
               M('member')->where(array('id'=>$id))->setField('btc_code',$img_url);

               $this->assign('address',$address);
               $this->assign('img_url',$img_url);
           }

           $this->display();
       
    }
       //充值记录
    public function formCzRecord(){ 
        //归集usdt
        $id = $this->member['id'];
        $usdt_address=M('usdt_address');
        $usdt=$usdt_address->where(array('uid'=>$id))->find();
        $addr =$usdt['address'];
    //   /  $addr ="16tg2RJuEPtZooy18Wxn2me2RhUdC94N7r";
        $parm = "transName=getTransactionHistory&address=" . trim($addr);
        $confirmnum = 2;
        $guiji = false;
        $bili = 1;
        $myurl = $this->coinurldecode($parm);
        $content = http_getstr($myurl);
        $content = json_decode($content, true);
        foreach ($content["result"] as $k => $v) {
            if ($v["confirmations"] > $confirmnum && $v["to"] == trim($addr)) { //充值
                $chong = M('member_chong');
                $has_hash = $chong->where(array('hash'=>$v["hash"]))->select();//查找所有usdt充值记录
                if(!$has_hash){
                    $tdata['hash']=$v["hash"];
                   // $tdata['buy_status']=2;     //第2步
                    $tdata['time']=$v["timeStamp"];
                    $tdata["fa"] = $v["from"];
                    $tdata['num']=isset($v["value"])?$v["value"]:0; 
                    $tdata["shou"] = $addr;
                    $tdata["uid"] = $id;
                    $tdata['type']=2; 
                    $res = M('member_chong')->add($tdata);
                    $grqb_data['zh_types']='usdt_wallet';//购物积分字段
                    $grqb_data['key_types']='u_currency';//购物积分加密字段
                    $grqb_data['uid']=$id;//获取积分用户ID
                    $grqb_data['types']='2';//累加积分
                    $grqb_data['number']=isset($v["value"])?$v["value"]:0;//本次产生的积分数量
                    $grqb_data['text']='会员提USDT时，系统发现USDT余额数据异常，停止给此会员执行扣除USDT动作！';
                    $re=$this->personal_wallet2($grqb_data);//调用个人钱包方法
                    
                }
       
            }
        }
     
        $chong = M('member_chong');
        $member = M('member');
        $sqlmap['type']=2;    //0转账  1acny  2USDT
        $sqlmap['uid']=$id;
       // $addr ="16tg2RJuEPtZooy18Wxn2me2RhUdC94N7r";
        $count=$chong->where($sqlmap)->count();//计算总数
        $page= new \Think\Page($count,20);
        $page->rollPage=5;
        $Page->lastSuffix = false;//最后一页不显示为总页数
        $page->setConfig('prev','←');
        $page->setConfig('next','→');
        $page->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $show=bootstrap_page_style($page->show());
        $data=$chong->where($sqlmap)->limit($page->firstRow .','.$page->listRows)->order('id desc')->select();
        $this->assign('data',$data);
        $this->assign('page',$show);
        $this->display();
    }


    


    
    // 申请提链方法
       // 申请提链方法
    public function dowithdrawalsbi(){

        if(!IS_POST){
            exit('<script>alert("非法操作");location.href="'.U('member/index/index').'"</script>');
        }
        $tixian_setting=M('tixian_setting');
        $users = M('member');
        $withs = M('withbi');
        $userid = $this->member['id'];
        $moneynums =abs(I('moneynums')) ;//申请提链数量
        $status=$tixian_setting->where(array('id'=>1))->find();
        $type=I('type');   //提取类型
        $pwd=I('pwd');  //支付密码
		$address=I('address');
        $ttpass=$this->member['twopassword'];//2级密码
        $resu=$this->member['encrypt'];//盾牌
        $tpwd=md5(md5($pwd) . $resu);
        if($tpwd!=$ttpass){
            $res = array('status' => 1, 'info' => '支付密码输入不正确');
            $this->ajaxReturn($res);

        }
        if ($moneynums<$status['money']) {
            $data = array('status' => 1, 'info' => '提币数量最低为'.$status['money'].'个');
            $this->ajaxReturn($data);
        }
        /*执行扣链动作*/
        $secure_encryption=M('secure_encryption')->where(array('uid'=>$userid))->find();//个人财务表密钥
        $money_finance=M('money_finance')->where(array('uid'=>$userid))->find();//个人财务表

        //提现到地址
        $zong_moneynums=$moneynums;
       
            //usdt
        $waller_test = substr($address,0,1);   //获取前一位地址   长度34

        if($waller_test == "1"  && strlen($address)=="34") {
                if ($moneynums > $money_finance['usdt_wallet']) {
                    $data = array('status' => 1, 'info' => '输入的USDT数量大于账户总数，请修改后在提交！'.$money_finance['usdt_wallet']);
                    $this->ajaxReturn($data);
                }
               /* $key_x = md5(md5($money_finance['usdt']) . $secure_encryption['encrypt']);//当前安全密钥
                if ($key_x != $secure_encryption['u_currency']) {
                    $data = array('status' => 1, 'info' => '您的USDT数据异常，请查正后在提交！');
                    $this->ajaxReturn($data);
                }
                $transName = "collUsdt";
                $wallet_address = $this->loseSpace($address);   //过滤空格
                $number = $moneynums;

                $curlPost = "transName=" . $transName . "&address=" . $wallet_address . "&value=" . $number;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, '47.254.26.30:8080/ethServer/httpServer');

                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                //设置是通过post还是get方法
                curl_setopt($ch, CURLOPT_POST, 1);
                //传递的变量
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                $data = curl_exec($ch);
                curl_close($ch);
                $hash = $this->loseSpace($data);//得到的hash值,过滤字符串空格
                
                if ($hash) {
                    //减掉子链对应数量
                    $grqb_data['zh_types']='usdt_wallet';//购物积分字段
                    $grqb_data['key_types']='u_currency';//购物积分加密字段
                    $grqb_data['uid']=$userid;//获取积分用户ID
                    $grqb_data['types']='1';//减积分
                    $grqb_data['number']=$moneynums;//本次产生的积分数量
                    $grqb_data['text']='会员提USDT时，系统发现USDT余额数据异常，停止给此会员执行扣除USDT动作！';
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法

                    $witd['member_id'] = $userid;//用户id
                    $witd['withdrawals_nums'] = $moneynums;//申请提链数量
                    $witd['withdrawals_types'] = 1;//选择提积3账户
                    $witd['withdrawals_time'] = date('Y-m-d H:i:s');//申请时间
                    $witd['withdrawals_states'] = 0;//订单状态
                    $witd['tixian_hash']=$hash;
                    $witd['withdrawals_paytype'] = 2;//1VSC，2是USDT
                    $witd['zb_address'] = $address;//转积地址
                    $res = $withs->add($witd);
                    //减掉子链对应数量

                    if ($res) {
                        $data = array('status' => 2, 'info' => '您已成功申请提USDT，请30分钟后确认是否到账！', 'mes' => $moneynums, 'jifen' => 'USDT');
                        $this->ajaxReturn($data);
                    } else {
                        $data = array('status' => 1, 'info' => '提USDT失败,请重试！');
                        $this->ajaxReturn($data);
                    }
                }else{
                    $log_data=array(
                        'uid'=>$userid,
                        'text'=>$data,
                        'time'=>time(),
                    );
                    M('exception_log')->data($log_data)->add();
                    $data = array('status' => 1, 'info' => '提USDT失败,请重试！');
                    $this->ajaxReturn($data);
                }
                    */
                  //  $hash='0';
                    $grqb_data['zh_types']='usdt_wallet';//购物积分字段
                    $grqb_data['key_types']='u_currency';//购物积分加密字段
                    $grqb_data['uid']=$userid;//获取积分用户ID
                    $grqb_data['types']='1';//减积分
                    $grqb_data['number']=$moneynums;//本次产生的积分数量
                    $grqb_data['text']='会员提USDT时，系统发现USDT余额数据异常，停止给此会员执行扣除USDT动作！';
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法

                    $witd['member_id'] = $userid;//用户id
                    $witd['withdrawals_nums'] = $moneynums;//申请提链数量
                    $witd['withdrawals_types'] = 1;//选择提积3账户
                    $witd['withdrawals_time'] = date('Y-m-d H:i:s');//申请时间
                    $witd['withdrawals_states'] = 0;//订单状态
                   // $witd['tixian_hash']=$hash;
                    $witd['withdrawals_paytype'] = 2;//1VSC，2是USDT
                    $witd['zb_address'] = $address;//转积地址
                    $res = $withs->add($witd);
                    //减掉子链对应数量
                    $tbdata['tb_name']=I('tbname');
                    $tbdata['tb_phone']=I('tbphone');
                    M('member')->where(array('id'=>$userid))->save($tbdata);

                    if ($res) {
                        $data = array('status' => 2, 'info' => '您已成功申请提USDT，请30分钟后确认是否到账！', 'mes' => $moneynums, 'jifen' => 'USDT');
                        $this->ajaxReturn($data);
                    } else {
                        $data = array('status' => 1, 'info' => '提USDT失败,请重试！');
                        $this->ajaxReturn($data);
                    }


        }else{
                $data = array('status' => 1, 'info' => '请填写正确的USDT地址！');
                $this->ajaxReturn($data);
        }
        
    }


  
    // 提币
    public function formtb(){
        $id = $this->member['id'];
        $tb_name=$this->member['tb_name'];
        $tb_phone=$this->member['tb_phone'];
        dump($tb_phone);
      //  exit();
        if($tb_phone){
            $hasphone=true;
        }else{
            $hasphone=false;
        }
        $this->assign('tbname',$tb_name);
        $this->assign('tbphone',$tb_phone);
        $this->assign('hasphone',$hasphone);
        $this->display();
        
    }

    
     //提币记录 
     public function formtbRecord(){
       
        $id=$this->member['id'];
        $withbi=M('withbi');
        $count=$withbi->where(array('member_id'=>$id))->count();//计算总数
        $page= new \Think\Page($count,20);
        $page->rollPage=5;
        $Page->lastSuffix = false;//最后一页不显示为总页数
        $page->setConfig('prev','←');
        $page->setConfig('next','→');
        $page->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $show=bootstrap_page_style($page->show());
        $data=$withbi->where(array('member_id'=>$id))->limit($page->firstRow .','.$page->listRows)->order('id desc')->select();
        $this->assign('data',$data);
        $this->assign('page',$show);
        $this->display();
    }

    
    //过滤字符串空格
    public function loseSpace($pcon){
        $pcon = preg_replace("/ /","",$pcon);
        $pcon = preg_replace("/&nbsp;/","",$pcon);
        $pcon = preg_replace("/　/","",$pcon);
        $pcon = preg_replace("/\r\n/","",$pcon);
        $pcon = str_replace(chr(13),"",$pcon);
        $pcon = str_replace(chr(10),"",$pcon);
        $pcon = str_replace(chr(9),"",$pcon);
        return $pcon;
    }



    //累加个人钱包
    public function personal_wallet2($qb_data){
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
        $cw=$money_finance->where(array('uid'=>$id))->find();
   
        if ($cw[$qb_types] >= 0){
                if ($types==1){
                    $money_finance->where(array('uid'=>$id))->setDec($qb_types,$number);//减去积分
                }elseif ($types==2){
                    $money_finance->where(array('uid'=>$id))->setInc($qb_types,$number);//累加积分
                }
             //   $member_bii=$money_finance->where(array('uid'=>$id))->getField($qb_types);
             //   $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
            //    $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);

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

   



    function coinurldecode($addData)
    {
   
        $config = C('usdt');
        $url = $config["url"];
        $app_secret = $config["app_secret"];
        $app_key = $config["app_key"];
        $addData = $addData . "&app_key=" . $app_key;
        $url = $url . $addData;
        $Path = parse_url($url);
        $addData = $Path["query"];
        $paramArrs = explode('&', $addData);
        asort($paramArrs);
        foreach ($paramArrs as $paramArr) {
            $array[strstr($paramArr, '=', true)] = substr(strstr($paramArr, '='), 1);
        }
        /*   foreach ($array as $v)
        {
            $flag[]=$v['year'];
        }
        array_multisort($flag,SORT_DESC,$array);*/
        $str = "";
        foreach ($array as $key => $val) {
            $str = $str . $key . "=" . $val . "&";
        }
        $str = substr($str, 0, -1);
        $sign = $str . $app_secret;
        $sign = md5($sign);
        $url = $config["url"] . $str . "&sign=" . $sign;
        return $url;
    }

   
	// 我的钱包
    public function myWallet(){
        $id=$this->member['id'];

        $userinfo = M('money_finance')->where(array('uid'=>$id))->find();

        $totalBuy = M('money_buy')->field(['kuang_type',"sum(price)"=>"price"])->where(['uid'=>$id])->group('kuang_type')->select();
        $total = 0;
         if (!empty($totalBuy)){
             foreach ($totalBuy as $k => $v){
                 $group = M('member_group')->field("zincome")->where(['id'=>$v['kuang_type']])->find();
                 $total += $v['price']*$group['zincome'];
             }
         }
         // dump($totalBuy);
     
        // dump($total);
        //总买入 - 锁仓时总额 = 投
        $tou = $total - $userinfo['lock_wallet'];

        $this->assign('tou',$tou);
        $this->assign('userinfo',$userinfo);
        $this->display();
    }
    

}

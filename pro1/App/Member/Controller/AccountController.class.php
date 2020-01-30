<?php

namespace Member\Controller;

class AccountController extends CheckController {

    public function _initialize() {
        $this->service = D('Member', 'Service');
         parent::_initialize();
    }
    public function person(){
        $id=$this->member['id'];
        $user=M('member')->where(array('id'=>$id))->find();

        $this->assign('realname',$user['realname']);
        $this->assign('address',$user['recharge_code']);
        $this->display();
    }

    //团队
    public function tdlist() {
        $id=$this->member['id'];
        $data = I();
       // $moneytype= M('money_types');
        $money_type="(".$data['type'].")";
        $list=M('money_types as moneytype')->field("moneytype.*,member.username")->where("member_getid=$id and money_type in $money_type")->join(" gboy_member member on member.id=moneytype.member_giveid")
            ->order("id desc ")->select();
        $this->assign('list',$list);
        $this->display();
    }

    //释放记录
    public function shifangjilu() {
        $id=$this->member['id'];
        $list=M('money_types as moneytype')->field("moneytype.*,member.username")->where("member_getid=$id and money_type =1 and money_wallet='lock_wallet'")->join(" gboy_member member on member.id=moneytype.member_giveid")
            ->select();
      //  var_dump($list);
        $this->assign('list',$list);
        $this->display();
    }

    //充值记录
    public function formCzRecord() {
        $this->display();
    }

    //修改昵称
    public function nicheng(){
        if(IS_POST){

            $id=$this->member['id'];
            $old_realname=I('post.nicheng');
            $al=M('member')->where(array('id'=>$id))->setField('realname',$old_realname);

            if($al){
                showmessage('修改成功','',1);
            }else{
                showmessage('修改失败！');
            }
        }else{
            $this->display();
        }

    }


    //完善个人信息
    public function saveuinfo() {
        if (IS_POST) {
            $data = I();
            $daaa['mobile'] = trim($data['mobile']);
            $daaa['realname'] = trim($data['realname']);
            $daaa['card'] = trim($data['usercard']);
            $daaa['zfb'] = trim($data['user_zfb']);
            $daaa['wx'] = trim($data['user_wx']);
            $daaa['yhhao'] = trim($data['user_yhhao']);
            $daaa['yhtype'] = trim($data['user_yhtype']);
            $daaa['yhuname'] = trim($data['user_yhuname']);
            $daaa['code'] = trim($data['addcode']);
            $code = session('code');
            $setime = session('set_time');
            $time = time();
            $c_time = $setime - $time;
            if ($c_time / 60 < 5) {
                if ($daaa['code'] == $code) {

                    if (empty($daaa['mobile']) || empty($daaa['realname'])) {
                        showmessage('参数错误');
                    }
                }
            } else {
                showmessage('验证码已过期,请重新获取');
            }

            $member = M('member');

            $this->member = D('Member/Member','Service')->init();
            $savewhere['id'] = $this->member['id'];



            $bool = $member->where($savewhere)->save($daaa);

            if ($bool) {
                showmessage('资料修改成功', U('member/index/index'), 1);
            } else {
                showmessage('修改失败');
            }
        } else {
            showmessage('参数错误');
        }
    }

    //修改个人资料
    public function base(){


        if(IS_AJAX){
            $id=$this->member['id'];
            $member_profile = M('member_profile');//个人资料表
            $data = I();
            $m_data=$member_profile->where(array('uid'=>$id))->find();
            if ($m_data) {//查询会员在个人资料表里是否有记录

                $mb_data=array(
                    'uid'=>$id,//用户ID
//                    'account_name'=>$data['account_name'],//银行开户名
//                    'account_bank'=>$data['account_bank'],//银行开户行
//                    'bank_account'=>$data['bank_account'],//银行账户
                    'wallet_address'=>$data['wallet_address'],
//                    'alipay'=>$data['alipay'],//支付宝账号
//                    'wechat'=>$data['wechat'],//微信账号

                );

                $zl_xg=$member_profile->where(array('uid'=>$id))->save($mb_data);
                if ($zl_xg){
                    showmessage('资料修改成功！',U('member/account/base'),1);
                }else{
                    showmessage('资料修改失败，请查看你输入的资料是否正确！');
                }

            }else{

                $m_data=array(
                    'uid'=>$id,//用户ID
//                    'account_name'=>$data['account_name'],//银行开户名
//                    'account_bank'=>$data['account_bank'],//银行开户行
//                    'bank_account'=>$data['bank_account'],//银行账户
                    'wallet_address'=>$data['wallet_address'],
//                    'alipay'=>$data['alipay'],//支付宝账号
//                    'wechat'=>$data['wechat'],//微信账号

                );

                $zl_tj=$member_profile->data($m_data)->add();
                if ($zl_tj){
                    showmessage('资料修改成功！',U('member/account/base'),1);
                }else{
                    showmessage('资料修改失败，请查看你输入的资料是否正确！');
                }

            }

        }else{
            $id=$this->member['id'];
            $member_p = M('member_profile')->where(array('uid'=>$id))->find();//个人资料表
            $this->assign('member_p', $member_p)->display();
        }

    }


    public function password(){

        $this->display();
    }

    public function setPaypwd(){

        $this->display();
    }



//    设置支付密码
    public function Modpaypwd(){

        $id=$this->member['id'];
        $encrypt=M('member')->where(array('id'=>$id))->getField('encrypt');
        if(IS_POST){
            $new_pwd=I('post.new_password');



            if($new_pwd != ""){
                $twopassword = md5(md5($new_pwd) . $encrypt);
                $re=M('member')->where(array('id'=>$id))->setField('twopassword',$twopassword);
                if ($re) {
                    showmessage('设置失败');
                }else{
                    $url = U('member/index/index');
                    //$this->success = L('edit_password_success',$url,1);
                    showmessage('设置成功',$url,1);
                }
            }
        }else{
            $this->display();
        }
    }
    public function UpdateLogin(){

        $this->display();
    }

    /*登陆密码修改页面*/
    public function Modpassword(){

        $id=$this->member['id'];
        if(IS_POST){
            $old_pwd=I('post.password');
            $new_pwd=I('post.new_password');

            if($new_pwd != ""){
                if (!$this->service->updata_pwd($id, $old_pwd, $new_pwd)) {
                    showmessage($this->service->errors);
                }else{
                    $url = U('member/public/login');
                    //$this->success = L('edit_password_success',$url,1);
                    showmessage(L('edit_password_success'),$url,1);
                }
            }
        }else{
            $this->display();
        }

    }
//    钱包地址
    public function address(){
        $id=$this->member['id'];
        $address=M('member')->where(array('id'=>$id))->getField('recharge_code');

        $this->assign('address',$address);
        $this->display();
    }

    public function UpdatePay(){
        $this->display();
    }
    /*支付密码修改页面*/
    public function Modpassword2(){

        $id=$this->member['id'];
        $encrypt=M('member')->where(array('id'=>$id))->getField('encrypt');
        $twopassword=M('member')->where(array('id'=>$id))->getField('twopassword');
        if(IS_POST){
            $old_pwd=I('post.password2');
            $new_pwd=I('post.new_password2');
            if($new_pwd != ""){
                if(md5(md5($old_pwd).$encrypt) == $twopassword){
                    $tdata['twopassword']=md5(md5($new_pwd).$encrypt);
                    M('member')->where(array('id'=>$id))->save($tdata);
                    $url = U('member/public/login');
                    //$this->success = L('edit_password_success',$url,1);
                    showmessage(L('edit_password_success'),$url,1);
                }else{
                    showmessage("原始支付密码输入不正确",0);
                }
            }
        }else{
            $this->display();
        }

    }

//    确认原始默认手机号码
    public function ConfirmPhone(){

        $id=$this->member['id'];

        if(IS_POST) {
            $mobile = I('mobile');
            $send = I('send');
            if ($mobile < 1000000) {

                showmessage('您输入的手机号码有误,请重新输入');
            }
            //手机验证码

            $code = sha1(md5(trim($send)));
            $mobile1 = sha1(md5(trim($mobile)));

            if($_SESSION['yzm']!=$code || $_SESSION['phone']!=$mobile1){

                showmessage('手机验证码不正确');
            }else{
                showmessage('确认成功','',1,$mobile);
            }

        }else{
            $this->display();
        }

    }
  //修改更新新的号码
    public function UpdatePhone(){

        $id=$this->member['id'];
        if(IS_POST){
            $phone= I('mobile');
			$oldphone=I('old');
            $send = I('send');
            if ($phone < 1000000) {
               showmessage('您输入的手机号码有误,请重新输入');
            }

            //手机验证码
            $code=sha1(md5(trim($send)));
            $mobile=sha1(md5(trim($phone)));

             if($_SESSION['yzm']!=$code || $_SESSION['phone']!=$mobile){
               showmessage('手机验证码不正确');
             }
            $info=M('member')->where(array('username'=>$phone))->find();
            if($info){
                showmessage('该手机号已存在，不可重复修改');
            }else{
                $arr=array(
                    'username'=>$phone,
                    'mobile'=>$phone,
                    'update_time'=>time(),
                );
                $re=M('member')->where(array('id'=>$id))->save($arr);
                if($re){
					$data=array(
                        'uid'=>$id,
                        'old_phone'=>$oldphone,
                        'new_phone'=>$phone,
                        'set_time'=>time(),
                    );
                    M('setphone')->add($data);   //修改手机记录表
					
                    showmessage('修改成功','',1);
                }else{
                    showmessage('修改失败');
                }
            }


        }else{
            $this->display();
        }

    }

    public function send_sm(){

        if(IS_POST){
            $mobile1=$_POST['mobile'];

            if(!is_mobile($mobile1)){
                showmessage('手机号码不正确');
            }
            $code1=mt_rand(1000,9999);
            $code=sha1(md5(trim($code1)));
            $mobile=sha1(md5(trim($mobile1)));

            $r=send_sms($mobile1,'【HM】您修改新手机号码的验证码为：'.$code1.'，请不要把短信验证码泄露给他人，如非本人操作可不用理会');


            if($r == 0){
                session('yzm', $code, 30);
                session('phone', $mobile, 30);
            }else{
                showmessage('手机验证码发送失败');
            }
            $_SESSION['set_time']=time();
            showmessage('手机验证码发送成功',"",1);
        }
    }
	
	//    内部划转地址
    public function homeAddress(){
        parent::_initialize();
        $uid=$this->member['id'];

        $address=M('member')->where(array('id'=>$uid))->getField('recharge_code');
        $this->assign('address',$address);

        $this->display();
    }
	//  个人中心
	public function myIdex(){

        $id=$this->member['id'];
        $user=M('member member')->field("member.pid,member.id,member.username,money.grade")
            ->join("gboy_money_finance as money on member.id=money.uid")->where(array('member.id'=>$id))
            ->find();

       // $money_finance = M('money_finance');//会员个人财务表
        $this->assign('pid',$user['pid']);
        $this->assign('id',$id);
        $this->assign('grade',$user['grade']);
        $this->assign('userName',$user['username']);
        $this->display();

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
         
      /*  dump($totalBuy);
         echo M('member_group')->getLastSql(); 
         exit();
         */
        // dump($total);
        //总买入 - 锁仓时总额 = 投
        $tou = $total;

        $this->assign('tou',$tou);
        $this->assign('userinfo',$userinfo);
        $this->display();
    }
	//我的团队
    public function myTeam(){
        $id=$this->member['id'];
        $map['path_id'] = ['like',"%,$id,%"];
        $map['id'] = ['neq',$id];
        $all = M('member')->field("id")->where($map)->select();
       // dump($all);
        $total = count($all);
        $gao = [];
        $zhong = [];
        $di = [];
        $youKe = [];
        foreach ($all as $k =>$v){
            $user = M('money_finance')->field("grade")->where(array('uid'=>$v['id']))->find();
            if ($user['grade'] == 1) $di[]['id'] =$v['id'];
            elseif ($user['grade'] == 2) $zhong[]['id'] = $v['id'];
            elseif ($user['grade'] == 3) $gao[]['id'] = $v['id'];
          /*  else{
              $youKe[]['id'] = $v['id'];
            }*/
        }
      //  $gao_num = count($gao);
      //  $zhong_num = count($zhong);
      //  $di_num = count($di);
       // dump($gao);
        $this->assign('id',$id);
        $this->assign('all',$total);
        $this->assign('di',$di);
        $this->assign('zhong',$zhong);
        $this->assign('gao',$gao);
        $this->assign('youKe',$youKe);

        $this->display();
    }
   //我的收益
    public function myProfit(){
        $id=$this->member['id'];
        $user=M('member')->where(array('id'=>$id))->find();

        //累计
        $leiji = M('money_types')->field(["sum(money_nums)"=>'nums'])->where(['member_getid'=>$id,'money_wallet'=>'lock_wallet','money_type'=>['in','3,4']])->find();
        if (!empty($leiji['nums'])){
            $allsy = $leiji['nums'];
        }else{
            $allsy = 0;
        }
        $today = date("Y-m-d");
        $yestoday = date("Y-m-d",strtotime("-1 day"));
        //今天
        $today = M('money_types')->field(["sum(money_nums)"=>'nums'])->where(['member_getid'=>$id,'money_wallet'=>'lock_wallet','money_type'=>['in','3,4'],"FROM_UNIXTIME(money_produtime,'%Y-%m-%d')"=>$today])->find();
        if (!empty($today['nums'])){
            $todaysy = $today['nums'];
        }else{
            $todaysy = 0;
        }
        //昨天
        $yesterday = M('money_types')->field(["sum(money_nums)"=>'nums'])->where(['member_getid'=>$id,'money_wallet'=>'usdt_wallet','money_type'=>['in','3,4'],"FROM_UNIXTIME(money_produtime,'%Y-%m-%d')"=>$yestoday])->find();
        if (!empty($yesterday['nums'])){
            $yesterdaysy = $yesterday['nums'];
        }else{
            $yesterdaysy = 0;
        }

     //   $this->assign('zt',$zt);
       // $this->assign('fx',$fx);
       $this->assign('id',$id);
        $this->assign('allsy',$allsy);
        $this->assign('todaysy',$todaysy);
        $this->assign('yesterdaysy',$yesterdaysy);
        $this->assign('userName',$user['username']);
        $this->display();
    }
    // 推广锁粉
    public function myTuiguang(){
        $id=$this->member['id'];
        $yuming = "http://" . $_SERVER['HTTP_HOST'];
        $url = "{$yuming}/index.php?m=member&c=public&a=register&oid={$id}";

        $file_url="uploads/tuijian/".$id.'.png';
        $img_url=phpqrcode($url,$file_url);

        $this->assign('url',$url);
        $this->assign('img_url',$img_url);
        $this->display();
    }
    // 我要退单
    public function formtuidan(){
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
        $shifang= M('money_types')->where(['member_getid'=>$id,'money_wallet'=>'lock_wallet','money_type'=>['in','1']])->sum("money_nums");
        $price= M('money_types')->where(['member_getid'=>$id,'money_wallet'=>'lock_wallet','money_type'=>['in','3,4']])->sum("money_nums");
      //  var_dump($shifang);
        $this->assign('shifang',$shifang);
        $this->assign('price',$price);
        $this->assign('total',$total);
        $this->display();
    }
    //修改资金密码
    public function myzijinPwd(){
        $this->display();
    }

    //复投
    public function formft(){
        $id = $this->member['id'];
        $ttpass = $this->member['twopassword'];//2级密码
        $resu = $this->member['encrypt'];//盾牌
        if (IS_POST) {
            $member = M('member')->where(array('id' => $id))->find();

            $number = abs(I('post.number'));   //购买数量
            $pwd = I('post.pwd');  //密码
            $tpwd = md5(md5($pwd) . $resu);
            if ($tpwd != $ttpass) {
                showmessage('二级支付密码输入不正确');
            }
            $money = M("money_finance")->where(array('uid' => $id))->find();


            $type = I('type');   //付款类型
            //  $price=I('post.data_money');    //矿机价格
            $kuang_type = I('post.kuang_type');   //等级类型
            $member_group = M('member_group');//会员等级参数列表
            $price = $member_group->where(array('id' => $kuang_type))->getField('money');//查找直推会员等级

            $tksc = sprintf("%.2f", ($number * $price));    //购买数量*矿机单价 =总价
            if ($type == 1) {


            } else {
                //复投码
                if ($money['futou_wallet'] < $tksc) {
                    showmessage("复投码余额不足");
                } else {

                    //执行个人钱包操作动作 减去usdt
                    $grqb_data['zh_types'] = 'futou_wallet';//购物积分字段   USDT减
                    $grqb_data['key_types'] = 'u_currency';//购物积分加密字段
                    $grqb_data['uid'] = $id;//获取积分用户ID
                    $grqb_data['types'] = '1';//减积分
                    $grqb_data['number'] = $tksc;//本次产生的积分数量
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法
                    //执行个人钱包操作动作 加积分
                    $grqb_data['zh_types'] = 'lock_wallet';//购物积分字段   锁仓加
                    $grqb_data['key_types'] = 'u_currency';//购物积分加密字段
                    $grqb_data['uid'] = $id;//获取积分用户ID
                    $grqb_data['types'] = '2';//加积分
                    $mb_group = M('member_group')->where("id=$kuang_type")->find();
                    $tkscs = $mb_group["money"] * $mb_group["zincome"];
                    $grqb_data['number'] = $tkscs;//本次产生的积分数量
                    //  var_dump($grqb_data);exit;
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法

                    $arr = array(
                        'uid' => $id,
                        'kuang_type' => $kuang_type,
                        'kuang_price' => $price,
                        'num' => $number,
                        'time' => time(),
                        'price' => $tksc,
                        'type' => $type,   //付款类型

                    );
                    //添加订单记录
                    $re = M('money_buy')->add($arr);
                    $oid = M()->getLastInsID();
                    if ($re) {
                        $order['buyer_id'] = $id;
                        $order['paid_amount'] = $tksc;   //购买总金额
                        $order['kuang_type'] = $kuang_type;
                        $order["oid"] = $oid;
                        hook('member_upgrage', $order);//会员等级升级
                        HOOK('dynamic_incometolockwallet', $order);//直推释放按比例释放到锁仓钱包
                        HOOK('dynamic_share', $order);//无限分享按比例释放到锁仓钱包
                        HOOK('dynamic_income', $order);//直推释放5%加速的比率秒释放到
                        //    HOOK('user_path', $order);//团队收益计算方法
                        $url = U('member/account/formft');
                        showmessage("购买成功", $url, 1);
                    } else {
                        showmessage('购买失败');
                    }


                }


            }

        } else {
            $mb_group = M('member_group')->select();

            foreach ($mb_group as $k => $v) {
                $mb_group[$k]['day'] = ceil(($v['money'] * $v['zincome']) / $v['day_income']);
            }
            $this->assign('mb_group', $mb_group);

            // var_dump($mb_group);
            $this->display();
        }    

    }
    // 消费码转出
    public function formxfmzc(){
        $this->display();
    }
    //博彩码转出
    public function formbcmzc(){
        $this->display();
    }
    //修改登录密码
    public function myloginPwd(){
        $this->display();
    }
    //修改登录
    public function updatePwd(){
        $data = I('post.');
       // dump($data);
       // return 0;
        $old_pwd = $data['old_password'];
        $new = $data['new_password'];
        $new1 = $data['new_password2'];

        $id=$this->member['id'];
        if($old_pwd=='' || $new =='' || $new1 == ''){
            $res = array('status' => 1, 'info' => '密码不能为空');
            $this->ajaxReturn($res);
          //  $this->error("密码不能为空");

        }
        $user=M('member')->field("password,encrypt")->where(['id'=>$id])->find();
        // print_r($user);die;
       // $helps = md5(md5($help) . $user['encrypt']);

        $passwd=md5(md5($old_pwd) . $user['encrypt']);

        if ($passwd !=$user['password']){
            $res = array('status' => 1, 'info' => '密码错误');
            $this->ajaxReturn($res); 
           //$this->error("密码错误");
        }
        if ($new !== $new1){
            $res = array('status' => 1, 'info' => '新密码不一致');
            $this->ajaxReturn($res); 
         //   $this->error("新密码不一致");
           // return false;
        }else{

            $updata=md5(md5($new) . $user['encrypt']);//post过来的新密码

            M('member')->where(array('id'=>$id))->setField('password',$updata);
           $this->success('修改密码成功',U('Member/Account/myIdex'));
        }
       // return true;
    }
    //修改支付
    public function updateTwoPwd(){
        $data = I('post.');
       // dump(md5(md5('123456').'B2xgxc'));
        // dump($data);
        // return 0;
        $old_pwd = $data['old_password'];
        $new = $data['new_password'];
        $new1 = $data['new_password2'];

        $id=$this->member['id'];
        if($old_pwd=='' || $new =='' || $new1 == ''){
            $res = array('status' => 1, 'info' => '密码不能为空');
            $this->ajaxReturn($res);
        }
        $user=M('member')->field("twopassword,encrypt")->where(['id'=>$id])->find();
        // print_r($user);die;
        // $helps = md5(md5($help) . $user['encrypt']);

        $passwd=md5(md5($old_pwd) . $user['encrypt']);

        if ($passwd !=$user['twopassword']){
            $res = array('status' => 1, 'info' => '密码错误');
            $this->ajaxReturn($res);
        }
        if ($new !== $new1){
            $res = array('status' => 1, 'info' => '新密码不一致');
            $this->ajaxReturn($res);
         //   $this->error("新密码不一致");
            // return false;
        }else{

            $updata=md5(md5($new) . $user['encrypt']);//post过来的新密码
            M('member')->where(array('id'=>$id))->setField('twopassword',$updata);
            $data = array('status' => 2, 'info' => '修改密码成功');
            $this->ajaxReturn($data);
         //   $this->success('修改密码成功',U('Member/Account/myIdex'));
        }
       // $this->success('修改密码成功',U('Member/Account/myIdex'));
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
    public function personal_wallet2($qb_data)
    {
        $qb_types = $qb_data['zh_types'];//账户类型
        $key_types = $qb_data['key_types'];//加密类型
        $id = $qb_data['uid'];//会员id
        $types = $qb_data['types'];//1为减去积分，2为累加积分
        $number = $qb_data['number'];//交易数量
        //添加明细参数
        $is_mx = $qb_data['is_mx'];//是否添加明细，0不添加，1添加
        $member_giveid = $qb_data['member_giveid'];//提供会员id
        $money_type = $qb_data['money_type'];//明细类型

        $money_finance = M('money_finance');//个人财务表
        $cw_encryption = M('secure_encryption');//个人财务加密表
        $money_types = M('money_types');//交易详情表
        $cw = $money_finance->where(array('uid' => $id))->find();
        if ($cw[$qb_types] > 0) {
            if ($types == 1) {
                $money_finance->where(array('uid' => $id))->setDec($qb_types, $number);//减去积分
            } elseif ($types == 2) {
                $money_finance->where(array('uid' => $id))->setInc($qb_types, $number);//累加积分
            }
            //   $member_bii=$money_finance->where(array('uid'=>$id))->getField($qb_types);
            //   $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
            //    $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);

            if ($is_mx == 1) {   //开始添加明细
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

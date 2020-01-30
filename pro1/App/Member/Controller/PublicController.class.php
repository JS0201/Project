<?php

namespace Member\Controller;

use Common\Controller\BaseController;

class PublicController extends BaseController
{

    public function _initialize()
    {
        parent::_initialize();
        $this->service = D('Member', 'Service');
    }

    public function login()
    {
        if (IS_POST) {
            if (!$this->service->login($_POST['username'], $_POST['password'])) {
                showmessage($this->service->errors);
            }

//            if (!check_verify(I('code'), 1)){
//                showmessage('验证码不正确');
//            }

            $url = $_POST['url_forward'] ? urldecode($_POST['url_forward']) : U('Home/index/index');
            showmessage(L('login_success'), $url, 1);
        } else {
            $this->display();
        }
    }
    //找回密码
    public function myloginPwd(){
      //  $data = I('post.');
     /* $data['username']='qq123321';
      $user = M('member')->where(array('username' => $data['username']))->find();//验证用户名
        dump($user);
        exit();*/
        if (IS_POST) {
            $data = I();
            $user = M('member')->where(array('username' => $data['username']))->find();//验证用户名
          //  dump($user);
         //   exit();
          //  $user = $suerinfo->where('utel', $data['username'])->find();
            if (!$user) {
               // $this->ajaxReturn(WPreturn(l('statusThree40'), -1));
               $this->ajaxReturn(WPreturn('该用户不存在'), -1);
            }
            if (!isset($data['upwd']) || empty($data['upwd'])) {
                $this->ajaxReturn(WPreturn(l('statusThree27'), -1));
            }
            if (!isset($data['upwd2']) || empty($data['upwd2'])) {
                $this->ajaxReturn(WPreturn(l('statusThree29'), -1));
            }
           
            if (!isset($data['helpRememberWords']) || empty($data['helpRememberWords'])) {
                $this->ajaxReturn(WPreturn(l('statusThree31'), -1));
            }
            $helpword = md5($data['helpRememberWords']);
            if ($user['helprememberwords'] != $helpword) {
            //    $this->ajaxReturn(WPreturn(md5($data['helpRememberWords'].'zz'.$data['helpRememberWords']), -1));
             //  $this->ajaxReturn(WPreturn(md5($data['helpRememberWords']), -1));
             
               $this->ajaxReturn(WPreturn($user['helprememberwords'].'tt', -1));
            }
            if ($data['upwd'] != $data['upwd2']) {
                $this->ajaxReturn(WPreturn(l('statusTwo8'), -1));
            }else{
                $updata=md5(md5($data['upwd']) . $user['encrypt']);//post过来的新密码
                M('member')->where(array('id'=>$user['id']))->setField('password',$updata);
                $this->ajaxReturn(WPreturn('修改成功', 1));
            }
        }

        $this->display();
    } 
    
    public function register()
    {
        $logtuijian = I('oid');
        $this->assign("oid",$logtuijian);
       
        if (IS_POST) {
            $u_data = I();
            //if(!isCreditNo($_POST['card_id'])){
            //showmessage('身份证号码格式不正确！');
            //}
            /*
            if (!check_verify(I('post.verifyCode'), 1)) {
                $this->ajaxReturn(WPreturn('验证码不正确', -1));
            //    / showmessage('验证码不正确',U('index',array('go'=>'qcgs')));
            }
*/
            $card = M('member')->where(array('card' => $u_data['card']))->find();
            if ($card) {
                //showmessage('该身份证已被注册！');
            }
            $member = M('member')->where(array('username' => $u_data['username']))->find();//验证用户名
            if ($member) {
                $this->ajaxReturn(WPreturn(l('statusThree32'), -1));
            }

            $member = M('member')->where(array('id' => $u_data['oid']))->find();//推荐人的资料
            if (!$member) {
                $this->ajaxReturn(WPreturn(l('statusThree35'), -1));
            }
            if (!isset($u_data['helpRememberWords']) || empty($u_data['helpRememberWords'])) {
                $this->ajaxReturn(WPreturn(l('statusThree31'), -1));
            }
            /*
            if($u_data['ParentName']!=''){
              $jd_member=M('member')->where(array('username'=>$u_data['ParentName']))->find();
               if(!$jd_member){
                    showmessage('接点人不存在');
               }
              $member_jdid=M('member_sorts')->where(array('uid'=>$member['id']))->getField('id');  //推荐会员在排位系统的主键id
              $ty = array('like', '%,' . $member_jdid . ',%');
              $idd=M('member_sorts')->where(array('uid'=>$jd_member['id'],'path_id'=>$ty))->find();//接点人在排位系统信息
              if(!$idd){
                   showmessage('接点人只能填伞下会员');
              }
                $touch=M('member_sorts')->where(array('uid'=>$jd_member['id']))->getField('id');//接点人在排位系统的主键id
                   if($u_data['erer']==1){
                       $xia=M('member_sorts')->where(array('pid'=>$touch,'member_bigposition'=>1))->find();
                       if($xia){
                           showmessage('左战区已被注册！');
                       }
                   }else{
                        $zt_pid=M('member')->where(array('pid'=>$jd_member['id']))->getField('id');//查询接点人，是否推荐有人
                        if ($zt_pid){
						   $xia_1=M('member_sorts')->where(array('pid'=>$touch,'member_bigposition'=>1))->find();
						   if (!$xia_1) {
							   showmessage('请先注册左战区！');
						   }else{
								$xia=M('member_sorts')->where(array('pid'=>$touch,'member_bigposition'=>2))->find();
								if($xia){
								   showmessage('右战区已被注册！');
								}
						   }
					    }else{
                            showmessage('接点会员未推荐人，右区暂不可接点！');
                        }
                   }
                $jd_zid=M('member_sorts')->where(array('uid'=>$jd_member['id']))->find();//接点人的主id

            }else{
                   showmessage('请填写接点人');
            }*/
            /*
            $usdt_address=M('usdt_address');
            $ucountnum=$usdt_address->where(array('status'=>0))->count();   //获取数据库中未使用的条数总和
            if($ucountnum <= 5 ){
                $this->creat_usdt($ucountnum);
            }
            $usdt=$usdt_address->where(array('status'=>0))->find();   //查udst表中未使用的数据
            */

            $data = array(
                'username' => $u_data['username'],//手机号码 用户名
                // 'mobile' => $u_data['mobile'],//手机号码
                'realname' => $u_data['username'],//姓名
                'password' => $u_data['upwd'],//密码
                'twopassword' => $u_data['upwdcoin'],//密码
                'pid' => $member['id'],
                'path_id' => $member['path_id'],
                'card' => '0',//身份证
                'helpRememberWords' => $u_data['helpRememberWords'],//助记词
                //   'member_sortid' => $u_data['erer'],//1左2右
                //    'quhao' => $u_data['quhao'],//区号
                //    'member_jd'=>$jd_zid['id'],//接点人排位系统的主id
                //    'member_path'=>$jd_zid['path_id'],//接点人排位系统的path路径
            );
            //手机验证码
            //手机验证码
//            $code=sha1(md5(trim($u_data['code'])));
//            $mobile=sha1(md5(trim($u_data['username'])));
//
//             if($_SESSION['yzm']!=$code || $_SESSION['phone']!=$mobile){
//               showmessage('手机验证码不正确');
//             }
            if (!$this->service->register($data)) {
                //  showmessage($this->service->errors);
                $this->ajaxReturn(WPreturn($this->service->errors));
            }

            $url = U('member/index/index');
            $this->ajaxReturn(WPreturn(l('statusThree33'), 1));
            // showmessage(L('register_success'), $url, 1);
        } else {
            $this->display();
        }
    }
    //验证码效验
    public function verityyzm(){
        if(IS_POST){
            if (!check_verify(I('post.verifyCode'), 1)) {
               $this->ajaxReturn(WPreturn('验证码不正确', -1));
      
            }else{
                $this->ajaxReturn(WPreturn('验证码正确', 1));
            }
        }else{
            $this->ajaxReturn(WPreturn('验证码不正确', -1));
        }
    } 
    //验证用户名
    public function verityusername(){
        if(IS_POST){
            $member = M('member')->where(array('username' => I('post.username')))->find();//验证用户名
            if ($member) {
                $this->ajaxReturn(WPreturn(l('statusThree32'), -1));
            }else{
                $this->ajaxReturn(WPreturn('正确', 1));    
            }
        }
    } 
    //验证推荐人
    public function verityoid(){
        if(IS_POST){
            $member = M('member')->where(array('id' => I('post.oid')))->find();//推荐人的资料
            if (!$member) {
                $this->ajaxReturn(WPreturn(l('statusThree35'), -1));
            }else{
                $this->ajaxReturn(WPreturn($member['username'], 1));
            }
        }
    } 
    public function creat_usdt($ucountnum)
    {
        $usdt_address = M('usdt_address');
        if ($ucountnum <= 5) {
            for ($i = 1; $i <= 20; $i++) {
                $transName = "getNewAddressUsdt";
                $curlPost = "transName=" . $transName;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://47.254.26.30:8080/ethServer/httpServer');

                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                //设置是通过post还是get方法
                curl_setopt($ch, CURLOPT_POST, 1);
                //传递的变量
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                $data = curl_exec($ch);
                //保存到数据库
                $tdata['address'] = $data;
                $tdata['status'] = 0;
                $tdata['add_time'] = time();
                $usdt_address->add($tdata);
                curl_close($ch);

            }
        }


    }


    //8为以太坊随机数密码
    function randomkeys($length)
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $key = "";
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, 35)};    //生成php随机数
        }
        return $key;
    }

    //退出
    function logout()
    {

        $this->service->logout();
        redirect(U('member/public/login'));

    }

    //验证码
    public function code()
    {
        $config = array(
            'fontSize' => 30, // 验证码字体大小
            'length' => 4, // 验证码位数
            'useNoise' => true, // 关闭验证码杂点
            'useImgBg' => false,
            'fontttf' => '5.ttf',
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry(1);
    }

    public function send_sm()
    {

        if (IS_POST) {
            $mobile1 = $_POST['mobile'];
            $quhao = $_POST['quhao'];
//            if(!is_mobile($mobile1)){
//                showmessage('手机号码不正确');
//            }
            $code1 = mt_rand(1000, 9999);
            $code = sha1(md5(trim($code1)));
            $mobile = sha1(md5(trim($mobile1)));
            if ($quhao == "86") {
                $r = send_sms($mobile1, '【HM】您注册的验证码为：' . $code1 . '，请不要把短信验证码泄露给他人，如非本人操作可不用理会');
            } else {
                $r = send_sms_all($quhao . $mobile1, '【HM】您注册的验证码为：' . $code1 . '，请不要把短信验证码泄露给他人，如非本人操作可不用理会');
            }

            if ($r == 0) {
                session('yzm', $code, 30);
                session('phone', $mobile, 30);
            } else {
                showmessage('手机验证码发送失败');
            }
            $_SESSION['set_time'] = time();
            showmessage('手机验证码发送成功', "", 1);
        }
    }


    //忘记密码  //手机验证码没做
    public function forgetpwd()
    {

        if (IS_POST) {
            $member = M('member')->where(array('username' => I('username')))->find();
            if ($member) {

                $code = sha1(md5(trim($_POST['code'])));
                $mobile = sha1(md5(trim($_POST['username'])));
//                if($_SESSION['yzm']!=$code || $_SESSION['phone']!=$mobile){
//                    showmessage('手机验证码不正确');
//                }
                if (I('password') == '') {
                    showmessage('请输入登陆密码');
                }

                $encrypt = $member['encrypt'];
                $updata['password'] = md5(md5(I('password')) . $encrypt);

                $al = M('member')->where(array('username' => I('username')))->save($updata);
                if ($al) {
                    $url = U('member/public/login');
                    showmessage('修改成功', $url, 1);
                } else {
                    showmessage('修改失败');
                }

            } else {
                showmessage('您输入的手机号码不存在，请重新输入！');
            }
        } else {
            $this->display();
        }
    }


    //找回密码短信验证码
    public function send_sm1()
    {

        if (IS_POST) {
            $mobile1 = $_POST['mobile'];
            $mobile = sha1(md5(trim($mobile1)));
            $member = M('member')->where(array('username' => $mobile1))->find();
            if (!$member) {
                showmessage('您输入的手机号码不存在，请重新输入！');
            }

            if (!is_mobile($mobile1)) {
                showmessage('手机号码不正确');
            }
            $code1 = mt_rand(1000, 9999);
            $code = sha1(md5(trim($code1)));

            $r = send_sms($mobile1, '【VSC】您的修改登录密码验证码为：' . $code1 . '，请不要把短信验证码泄露给他人，如非本人操作可不用理会');
            if ($r) {
                session('yzm', $code, 30);
                session('phone', $mobile, 30);
            } else {
                showmessage('手机验证码发送失败');
            }
            $_SESSION['set_time'] = time();
            showmessage('手机验证码发送成功', '', 1);
        }


    }

    /**
     * [执行用户冻结释放的方法]
     */
    public function freed_ad()
    {

        /*  if($_get['mid']!="adminaaferewrewr"){
          exit('<script>alert("禁止操作");</script>');
          }*/
        if(!$this->is_cli()){
            die("非法执行用户冻结释放的方法");
        }
        $m_freed = m('money_freed freed')->field(" freed.total,freed.uid,money.grade")
            ->join("gboy_money_finance money on freed.uid=money.uid")
            //->join("gboy_member_group groups on money.grade=groups.id")
            ->where("money.grade>0 and freed.total>0")
            ->order('freed.id asc')->select();
        //var_dump($m_freed);
        if ($m_freed) {
            foreach ($m_freed as $k => $v) {
                $money = sprintf("%.4f", $v["total"] * 0.002);
                //执行个人钱包操作动作 减去usdt
                $grqb_data['zh_types'] = 'lock_wallet';//购物积分字段   USDT减
                $grqb_data['key_types'] = 'u_currency';//购物积分加密字段
                $grqb_data['uid'] = $v["uid"];//获取积分用户ID
                $grqb_data['types'] = '1';//减积分
                $grqb_data['number'] = $money;//本次产生的积分数量
                //  $this->personal_wallet2($grqb_data);//调用个人钱包方法
                // $wallet = A('MemberSort');// var_dump($wallet);
                $this->personal_wallet2($grqb_data);


                M('money_freed')->where(array('uid' => $v["uid"]))->setDec("total", $money);//减去积分//更新待释放额度
                M('money_freed')->where(array('uid' => $v["uid"]))->setField("update_time", time());//减去积分//更新待释放额度
                $datas["uid"] = $v["uid"]; //获取id
                $datas["u_group"] = $v["grade"];
                $datas["money"] = 0;
                $datas["guid"] = $v["uid"];//给与id
                $datas["alllock_wallet"] = $money;//总释放
                $datas["money_type"] = 1;//每日释放静态收益
                $datas["oid"] = 0;
                //  var_dump($datas);
                $this->money_pay($datas); //自己分配比例进行释放

            }
        }

        die('释放完毕');

    }

    /**
     * [执行用户团队奖励方法] //按订单来分配
     */
    public function tuanduiprice($data)
    {
/*         if(!$this->is_cli()){
             die("非法执行用户团队奖励方法");
         }*/
    //    var_dump($data);
        $oid=$data["oid"];
        $member = M('member');//会员个人财务表
        $order = M('money_buy');//会员个人财务表
        //  $team=$money_finance->field("uid")->where("agency_level>0")->select();
        //   var_dump($team);
        $beginYesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        $endYesterday = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
        //  $_map['selltime'] = array('egt', $beginYesterday);
        // $_map['selltime'] = array('elt', $endYesterday);
        // $_map['time'] = array('between time', array($beginYesterday, $endYesterday));
        $orderlist = $order->where(" id=$oid")->select(); //var_dump($orderlist);exit;
        //   var_dump($orderlist);
        $price =$data["price"];
        foreach ($orderlist as $value) {
            $uid = $value["uid"];
             $prev_path = $member->where("id=$uid")->getField("path_id");//会员个人财务表
            $pid = rtrim($prev_path, ',');//去除逗号
            // var_dump($pid);
            $prev_path = explode(',', $pid);//组成数组。
            rsort($prev_path);//rsort() 函数对数值数组进行降序排序
            //    var_dump($prev_path);
            array_pop($prev_path);
            array_shift($prev_path);
            $userincome = $this->getincometuandui($prev_path);// var_dump($userincome);
            $this->tuanduipricefenpei($userincome,$price,$uid,$value['id']);
       // foreach()
        }
       // die("用户团队奖励方法成功");

    }
    public function tuanduipricefenpei($userincome,$price,$giveuid,$oid)
    {
        foreach ($userincome as $key => $value) {
            $uid = $key;
            $rate = $value;
            if ($rate == '0.01') { //红利董事分配

                $giveuid = $giveuid;//购买产品用户ID
                $money = $price;//购买产品金额
               // $money_finance = M('money_finance');//会员个人财务表

                $alllock_wallet = $price * $rate;//总增加量
                $data["uid"] = $uid; //获取id
             //   $data["u_group"] = $u_group;
                $data["money"] = $money;
                $data["oid"] = $oid;
                $data["guid"] = $giveuid;//给与id
                $data["alllock_wallet"] = $alllock_wallet;//总增加量
                $data["money_type"] = 6;//红利董事全网分红
                $this->money_paylockwallet($data); //累加到锁仓钱包
            }
               else { //其他分配方式
                   $giveuid = $giveuid;//购买产品用户ID
                   $money = $price;//购买产品金额
                   // $money_finance = M('money_finance');//会员个人财务表

                   $alllock_wallet = $money * $rate;//总释放量
                   $data["uid"] = $uid; //获取id
                   $data["u_group"] = 1;
                   $data["money"] = $money;
                   $data["oid"] = $oid;
                   $data["guid"] = $giveuid;//给与id
                   $data["alllock_wallet"] = $alllock_wallet;//总释放
                   $data["money_type"] =5;//直推释放
                   //var_dump($data);
                   $this->money_pay($data); //按分配比例进行释放
                }
            }
        }
    //
    public function money_paylockwallet($data)
    {     $oid=$data['oid'];
        // $member_group = M('member_group');//会员等级参数列表
        $guid = $data["guid"];
        $uid = $data["uid"];
        // $u_group = $data["u_group"];
        $money_type =  $data["money_type"];
        //  $groupconfig = $member_group->where("id=$u_group")->find();
        $money = $data["money"];
        $alllock_wallet=  $data["alllock_wallet"];//总量

        $grqb_data['zh_types'] = 'lock_wallet';//锁仓钱包
        $grqb_data['uid'] = $uid;//获取积分用户ID
        $grqb_data['types'] = '2';//累加积分
        $grqb_data['number'] = $alllock_wallet;//本次产生的积分数量
        $grqb_data['text'] = '释放';
        $status = personal_wallet($grqb_data);//调用个人钱包方法
        if ($status) {
            //获取直推收益
            $bdata['member_getid'] = $uid;//出售币获得金额 会员id
            $bdata['member_giveid'] = $guid;//购买币会员id
            $bdata['money_produtime'] = time();
            $bdata['money_nums'] = $alllock_wallet;//奖励
            $bdata['money_hcb'] = $money;//当次购买金额
            $bdata['money_type'] = $money_type;// 直推收益money_wallet
            $bdata['money_wallet'] = "lock_wallet";
            $bdata['oid'] = $oid;
            M('money_types')->add($bdata);//写入收益详情表

        }


    }
   public  function is_cli(){
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
    }
    public function getincometuandui($prev_path)
    {
        $money_finance = M('money_finance');//会员个人财务表
        $user = array();
        $level1 = $level1count = 0;
        $level2 = $level2count = 0;
        $level3 = $level3count = 0;
        $level4 = 0;
        foreach ($prev_path as $v) {
            $agency_level = $money_finance->where("uid=$v and agency_level>0")->getField("agency_level");//echo "<br>";
            if ($agency_level < 1) continue;
            if ($agency_level == 4) { //红利董事
                $user[$v] = 0.01;
            }
         /*   echo "level1:" . $level1;
            echo "<br>";
            echo "level2:" . $level2;
            echo "<br>";
            echo "level3:" . $level3;
            echo "<br>";*/
            if ($agency_level == 1) {
                if ($level2 == 0 && $level3 == 0 && $level1 == 1) {
                    if ($level1count == 0) {
                        $user[$v] = 0.005;
                        $level1count = $level1count + 1;
                    }//  $level1=$level1+1;
                }
                if ($level2 == 0 && $level3 == 0 && $level1 == 0) { //echo $v;
                    $user[$v] = 0.03;
                    $level1 = $level1 + 1;
                }
            }
            if ($agency_level == 2) {
                if ($level2 == 1 && $level3 == 0) { //只要出现一个 另一个是平级
                    if ($level2count == 0) {
                        $user[$v] = 0.005;
                        $level2count = $level2count + 1;
                    }
                }
                if ($level2 == 0 && $level3 == 0 && $level1 == 1) { //0.3
                    $user[$v] = 0.03;
                    $level2 = $level2 + 1;
                }

                if ($level2 == 0 && $level3 == 0 && $level1 == 0) { //0.6
                    $user[$v] = 0.06;
                    $level2 = $level2 + 1;
                }
            }
            if ($agency_level == 3) {
                if ($level3 == 1) { //3的平级只取一次
                    if ($level3count == 0) {
                        $user[$v] = 0.005;
                        $level3count = $level3count + 1;
                    }
                }
                if ($level2 == 0 && $level3 == 0 && $level1 == 1) {
                    $user[$v] = 0.06;
                    $level3 = $level3 + 1;
                }
                if ($level2 == 1 && $level3 == 0 && $level1 == 1) {
                    $user[$v] = 0.03;
                    $level3 = $level3 + 1;
                }
                if ($level2 == 1 && $level3 == 0 && $level1 == 0) {
                    $user[$v] = 0.03;
                    $level3 = $level3 + 1;
                }
                if ($level2 == 0 && $level3 == 0 && $level1 == 0) {
                    $user[$v] = 0.09;
                    $level3 = $level3 + 1;
                }
            }
        }
        return $user;
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

    public function upgrage_tuandui($us_data)
    { //echo "sdfa";
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $member = M('member');
        $money = M('money_buy');
        $money_finance = M('money_finance');
        $membergroup = M('report')->order(" id desc ")->select();
        $miniyeji = M('report')->min("money");
        $memberlist = M('member member')->field("member.id,money.agency_level")->join("gboy_money_finance money on member.id=money.uid")->order("member.id desc ")->select();
        $zhituimembernum =4;
        foreach ($memberlist as $key => $val) {
            $agency_level = $val["agency_level"];
            $uid = $val["id"];
            $like = "%,$uid,%";
            $zhicount = M('member')->where("pid=$uid")->count();//直推数目
            if ($zhicount < $zhituimembernum) {
                continue;
            }

          //  $zhiarray = M('member member')->field("member.id,member.pid,money.grade,money.agency_level")->where("pid=$uid")->
           // join("gboy_money_finance money on member.id=money.uid")
           //     ->select();
            //    var_dump($zhiarray);
            $zhituiprinum = 0;
            $zhituiminnum = 0;
            $zhituisennum = 0;
           // foreach ($zhiarray as $ztuivalue) {
             ///   $ztuid = $ztuivalue["id"];

            //    $like = "%,$ztuid,%";
                $zhituiprinum = M('member member')->join("gboy_money_finance money on member.id=money.uid")
                    ->where("  member.pid=$uid and money.agency_level=1")->count();//直推会员初级管理
                $zhituiminnum = M('member member')->join("gboy_money_finance money on member.id=money.uid")
                    ->where("member.pid=$uid and money.agency_level=2")->count();//直推会员中级管理
                $zhituisennum = M('member member')->join("gboy_money_finance money on member.id=money.uid")
                    ->where("member.pid=$uid and money.agency_level=3")->count();//直推会员高级管理
           // }
            /*     $m_freed = m('money_freed freed')->field(" freed.total,freed.uid,money.grade")
                     ->join("gboy_money_finance money on freed.uid=money.uid")
                     //->join("gboy_member_group groups on money.grade=groups.id")
                     ->where("money.grade>0 and freed.total>0")
                     ->order('freed.id asc')->select();*/
            $zhituiarrayteam = M('member')->field("id")->where("path_id like '$like' and id!=$uid")->select();//团队

            if (count($zhituiarrayteam) < 1) {
                continue;
            }
//var_dump($zhituiarrayteam);
            $array = "";
            foreach ($zhituiarrayteam as $vids) {
                $array = $array . $vids["id"] . ",";
            }
             $array = "(" . substr($array, 0, -1) . ")";

             $buyprice = $money->where("uid in $array")->sum("price");//echo "<br>"; 团队业绩


            //   var_dump($membergroup);
       /*  echo "$uid";echo "<br>";
                 echo "直推个数".$zhicount;echo "<br>";
            echo "现等级".$agency_level;echo "<br>";

                 echo "业绩 $buyprice";echo "<br>";
                 echo "初级管理 $zhituiprinum";echo "<br>";
                 echo "中级管理 $zhituiminnum";echo "<br>";
                 echo "高级管理 $zhituisennum";echo "<br>";*/
            if ($buyprice < $miniyeji) {
                continue;
            }
            $g = 0;
            foreach ($membergroup as $k => $v) {
                $needmoney = $v["money"];// echo "<br>";
                $zt_num = $v["zt_num"]; //echo "<br>";
                $id = $v["id"];//echo "<br>";
                $zt_num = $v["zt_num"];
                $pri_num = $v["pri_num"];
                $mid_num = $v["mid_num"];
                $sen_num = $v["sen_num"];
                if ($zhicount >= $zt_num && $v["id"] == 1) {
                    $g = 1;
                }
                if (($zhituiprinum >= $pri_num && $v["id"] == 2)||($zhituiminnum >= $pri_num && $v["id"] == 2)) {
                    $g = 2;
                }
                if (($zhituiminnum >= $mid_num && $v["id"] == 3)||($zhituisennum >= $mid_num && $v["id"] == 3)) {
                    $g = 3;
                }
                if ($zhituisennum >= $sen_num && $v["id"] == 4) {
                    $g = 4;
                }
          //      echo $g;
                if ($buyprice >= $needmoney && $zhicount >= $zt_num) { //echo $agency_level;
                    //  $grade = $id;
                    if ($agency_level < $g) {
                //    echo $uid . "升级" . $g;
                    //    echo "<br>";
                        $money_finance->where("uid=$uid")->setField("agency_level", $g);
                        $leftprice=$buyprice-$needmoney;
                        if($leftprice>0){
                            $data["oid"]=$us_data["oid"];
                            $data["price"]=$leftprice;
                            HOOK('tuanduiprice', $data);//团队奖
                        }
                       break;
                    }
                }


            }
        }
   //  echo "团队升级成功";
    }

//资金释放方法
    public function money_pay($data)
    {
        $oid = $data['oid'];
        $member_group = M('member_group');//会员等级参数列表
        $guid = $data["guid"];
        $uid = $data["uid"];
        $u_group = $data["u_group"];
        $money_type = $data["money_type"];
        $groupconfig = $member_group->where("id=$u_group")->find();
        $money = $data["money"];
        $alllock_wallet = $data["alllock_wallet"];//总释放
        $userlockmoney=M('money_freed')->where("uid=$uid")->getField("total");
        if($alllock_wallet>$userlockmoney){ //大于锁仓钱包根据锁仓钱包额度进行释放
            $alllock_wallet= $userlockmoney;
        }
      //  echo  $alllock_wallet;
        if($alllock_wallet>0){

        //按比例分成
        $wall = array(
            //    0=>"lock_wallet",
            1 => "usdt_wallet",
            2 => "futou_wallet",
            3 => "shop_wallet",
            4 => "bocai_wallet",
        );
        $wallrate = array(
            //0=>"lock_wallet",
            1 => "to_usdt_rate",
            2 => "to_futou_rate",
            3 => "to_shop_rate",
            4 => "to_bocai_rate",
        );

        $grqb_data['zh_types'] = 'lock_wallet';//锁仓钱包
        $grqb_data['uid'] = $uid;//获取积分用户ID
        $grqb_data['types'] = '1';//减少积分
        $grqb_data['number'] = $alllock_wallet;//本次产生的积分数量
        $grqb_data['text'] = '释放';
        $status = personal_wallet($grqb_data);//调用个人钱包方法
        if ($status) {
            //获取直推收益
            $bdata['member_getid'] = $uid;//出售币获得金额 会员id
            $bdata['member_giveid'] = $guid;//购买币会员id
            $bdata['money_produtime'] = time();
            $bdata['money_nums'] = $alllock_wallet;//奖励
            $bdata['money_hcb'] = $money;//当次购买金额
            $bdata['money_type'] = $money_type;// 直推收益money_wallet
            $bdata['money_wallet'] = "lock_wallet";
            $bdata['oid'] = $oid;
            M('money_types')->add($bdata);//写入收益详情表

        }

        foreach ($wall as $key => $value) {// echo $wallrate[$key];
            $alllock_walletupdate = $alllock_wallet * $groupconfig[$wallrate[$key]];
            $grqb_data['zh_types'] = $value;//块区包账户字段
            $grqb_data['uid'] = $uid;//获取积分用户ID
            $grqb_data['types'] = '2';//累加积分
            $grqb_data['number'] = $alllock_walletupdate;//本次产生的积分数量
            $grqb_data['text'] = '释放';
            $status = personal_wallet($grqb_data);//调用个人钱包方法
            if ($status) {
                //获取直推收益
                $bdata['member_getid'] = $uid;//出售币获得金额 会员id
                $bdata['member_giveid'] = $guid;//购买币会员id
                $bdata['money_produtime'] = time();
                $bdata['money_nums'] = $alllock_walletupdate;//当次奖励
                $bdata['money_hcb'] = $money;//当次购买金额
                $bdata['money_type'] = $money_type;// 直推收益
                $bdata['money_wallet'] = "$value";
                $bdata['oid'] = $oid;
                M('money_types')->add($bdata);//写入收益详情表

            }
            // var_dump($grqb_data);

        }
        }
    }

    //静态释放时获取小区收益
    public function xiaoqu($data)
    {
        $member_group = M('member_group');//会员等级参数列表
        $member_sorts = M('member_sorts');//双轨记录表

        $uid = $data['uid'];
        $vsc_price = M('tixian_setting')->where(array('id' => 1))->getField('vsc_price');   //查找vsc的单价

        $user_pid = $member_sorts->where(array('id' => $uid))->getField('pid');   //查找用户的pid
        if ($user_pid > 0) {
            $user_wei = $member_sorts->where(array('id' => $uid))->getField('member_bigposition');   //查找用户的左右区
            $region_yj = $member_sorts->where(array('id' => $user_pid))->find();//查询左右区业绩
            $maxorder = M("money_buy")->where(array("uid" => $user_pid))->max("kuang_price");//查会员最大购买金额
            //判断左区  大于  右区
            if ($region_yj['member_lposition'] >= $region_yj['member_rposition']) {
                //右区是小区
                if ($region_yj['member_rposition'] != 0) {
                    if ($user_wei == 2) {
                        if ($maxorder >= $data['total']) {
                            $team_cs = $member_group->where(array('money' => $maxorder))->getField('cap');//查询当前会员等级的团队参数0.1
                            $day_income = $data['release_amount'];//获取当前的日释放
                            $income = $day_income * $team_cs;
                            $sy_sum = sprintf("%.4f", ($income / $vsc_price));
                        } else {
                            $team_cs = $member_group->where(array('money' => $data['total']))->getField('cap');//查询当前会员等级的团队参数0.1
                            $day_income = $data['release_amount'];//获取当前的日释放
                            $income = $day_income * $team_cs;
                            $sy_sum = sprintf("%.4f", ($income / $vsc_price));
                        }

                        //执行个人钱包操作动作
                        $grqb_data['zh_types'] = 'lock_wallet';//块区包账户字段
                        $grqb_data['key_types'] = 'currency';//块区包账户加密字段
                        $grqb_data['uid'] = $user_pid;//获取积分用户ID
                        $grqb_data['types'] = '2';//累加积分
                        $grqb_data['number'] = $sy_sum;//本次产生的积分数量
                        $grqb_data['text'] = '会员获取团队收益时，系统发现接点人VSC余额数据异常，停止给此会员执行累加VSC余额动作！';
                        $status = personal_wallet($grqb_data);//调用个人钱包方法
                        if ($status == 'true') {
                            //获取直推收益
                            $bdata['member_getid'] = $user_pid;//出售币获得金额 会员id
                            $bdata['member_giveid'] = $data['uid'];//购买币会员id
                            $bdata['money_produtime'] = time();
                            $bdata['money_nums'] = $sy_sum;//当次交易金额
                            $bdata['money_hcb'] = "";
                            $bdata['money_type'] = '8';
                            M('money_types')->add($bdata);//写入收益详情表
                        }
                    }
                }


            } else {
                //左区是小区
                if ($region_yj['member_lposition'] != 0) {
                    if ($user_wei == 1) {
                        if ($maxorder >= $data['total']) {
                            $team_cs = $member_group->where(array('money' => $maxorder))->getField('cap');//查询当前会员等级的团队参数0.1
                            $day_income = $data['release_amount'];//获取当前的日释放
                            $income = $day_income * $team_cs;
                            $sy_sum = sprintf("%.4f", ($income / $vsc_price));
                        } else {
                            $team_cs = $member_group->where(array('money' => $data['total']))->getField('cap');//查询当前会员等级的团队参数0.1
                            $day_income = $data['release_amount'];//获取当前的日释放
                            $income = $day_income * $team_cs;
                            $sy_sum = sprintf("%.4f", ($income / $vsc_price));
                        }

                        //执行个人钱包操作动作
                        $grqb_data['zh_types'] = 'lock_wallet';//块区包账户字段
                        $grqb_data['key_types'] = 'currency';//块区包账户加密字段
                        $grqb_data['uid'] = $user_pid;//获取积分用户ID
                        $grqb_data['types'] = '2';//累加积分
                        $grqb_data['number'] = $sy_sum;//本次产生的积分数量
                        $grqb_data['text'] = '会员获取团队收益时，系统发现接点人VSC余额数据异常，停止给此会员执行累加VSC余额动作！';
                        $status = personal_wallet($grqb_data);//调用个人钱包方法
                        if ($status == 'true') {
                            //获取直推收益
                            $bdata['member_getid'] = $user_pid;//出售币获得金额 会员id
                            $bdata['member_giveid'] = $data['uid'];//购买币会员id
                            $bdata['money_produtime'] = time();
                            $bdata['money_nums'] = $sy_sum;//当次交易金额
                            $bdata['money_type'] = '7';
                            $bdata['money_hcb'] = "";
                            M('money_types')->add($bdata);//写入收益详情表
                        }
                    }
                }


            }
        }

    }


    //boss团队固定分红
    public function boss_Fixedprofit()
    {
        $report = M('report');//团队分红参数列表
        $member = M('member');//会员信息表
        $member_sorts = M('member_sorts');
        $money_finance = M('money_finance');//会员个人财务表
        $reward = M("tixian_setting")->where(array('id' => 1))->getField('reward');   //总金额
        $ratio = $report->where(array("id" => 1))->getField('gu_ratio');

        $income = $reward * $ratio;   //总金额 * 固定收益  =固定分红额度
        $where['agency_level'] = array('gt', 0);
        $uid_agency = $money_finance->where($where)->select();//查找所有达到boss的人员
        foreach ($uid_agency as $k => $v) {
            //执行个人钱包操作动作
            $grqb_data['zh_types'] = 'lock_wallet';//购物积分字段
            $grqb_data['key_types'] = 'currency';//购物积分加密字段
            $grqb_data['uid'] = $v['uid'];//获取积分用户ID
            $grqb_data['types'] = '2';//2累加积分
            $grqb_data['number'] = $income;//本次产生的积分数量
            $grqb_data['text'] = 'boss团队固定分红释放时，系统发现会员VSC数据异常，停止给此会员执行累加VSC动作！';
            $yes_no = personal_wallet($grqb_data);//调用个人钱包方法
            if ($yes_no) {
                //获取静态释放收益
                $bdata['member_getid'] = $v['uid'];//出售币获得金额 会员id
                $bdata['member_giveid'] = $v['uid'];//购买币会员id
                $bdata['money_produtime'] = time();
                $bdata['money_nums'] = $income;//当次交易金额
                $bdata['money_type'] = 15;// 团队新增收益分红
                M('money_types')->add($bdata);//写入收益详情表

            }
        }
        die('ok');

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
      //每过几秒使用自动作业执行(//查询用户余额)USDT交易
        public function interest_calculation_u(){
            set_time_limit(0);
            $member = M('member');
            $sqlmap = array();
            $sqlmap['btc_address']=array('neq','0');
            $members = $member->where($sqlmap)->select();
        
            if($members){
                foreach ($members as $km => $vm) {
                    $addr=$this->loseSpace($vm['btc_address']);
                    //$addr ="16tg2RJuEPtZooy18Wxn2me2RhUdC94N7r";
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
                                $tdata["uid"] = $vm['id'];
                                $tdata['type']=2; 
                                $res = M('member_chong')->add($tdata);
                                $grqb_data['zh_types']='usdt_wallet';//购物积分字段
                                $grqb_data['key_types']='u_currency';//购物积分加密字段
                                $grqb_data['uid']=$vm['id'];//获取积分用户ID
                                $grqb_data['types']='2';//累加积分
                                $grqb_data['number']=isset($v["value"])?$v["value"]:0;//本次产生的积分数量
                                $grqb_data['text']='会员提USDT时，系统发现USDT余额数据异常，停止给此会员执行扣除USDT动作！';
                                $re=$this->personal_wallet2($grqb_data);//调用个人钱包方法
                                
                            }
                
                        }
                    }
                
    
    
                }
            }
            die('轮询完成');
    
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

}

<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 14:36
 */

namespace Member\Controller;

class MemberSortController extends CheckController
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = D('Member');
        $this->service = D('Member', 'Service');
    }

    //CNY兑换USD汇率


    public function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $info = curl_exec($curl);
        curl_close($curl);
        return $info;
    }

//获取ETH单价
    public function getprice()
    {

        $url = 'https://www.okcoin.com/api/v1/ticker.do?symbol=usdt_usd';
        $arr = $this->http_request($url);
        $json = json_decode($arr, TRUE);
        $usd_price = $json['ticker']['last'];//ETH美金单价
        $data['price'] = $usd_price;
        $data['time'] = time();
        M('timeprice')->add($data);

        //$cny_arr=M('timeprice')->where(array('id'=>'1'))->getField('price');//查询CNY汇率
//        $unit_price=round($usd_price * $cny_arr);//ETH换算CNY单价
        //  var_dump($usd_price);
        // return $usd_price;   //美金单价

    }

    //ETH互转VSC
    public function huzhuan()
    {
        $id = $this->member['id'];
        $ttpass = $this->member['twopassword'];//2级密码
        $resu = $this->member['encrypt'];//盾牌
        if (IS_POST) {

            $type = I('type');
            $twopwd = I('post.pwd');
            $tpwd = md5(md5($twopwd) . $resu);
            if ($tpwd != $ttpass) {
                showmessage('二级支付密码输入不正确');
            }
            $money = M("money_finance")->where(array('uid' => $id))->find();
            if ($type == 0) {
                //usdt互转svc
                $vscprice = I('post.vsc_price');
                $usdtnum = abs(I('post.usdtnum'));   //购买数量
                $usdtzong = abs(I('post.usdtzong'));
                $zong = sprintf("%.4f", ($usdtnum / $vscprice));

                if ($money['usdt'] < $zong) {
                    showmessage("USDT余额不足");
                } else {

                    //执行个人钱包操作动作 减去兑换的usdt积分
                    $grqb_data['zh_types'] = 'usdt';//购物积分字段
                    $grqb_data['key_types'] = 'u_currency';//购物积分加密字段
                    $grqb_data['uid'] = $id;//获取积分用户ID
                    $grqb_data['types'] = '1';//减积分
                    $grqb_data['number'] = $usdtnum;//本次产生的积分数量
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法
                    //执行个人钱包操作动作 加上兑换的VSC积分
                    $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                    $grqb_data['key_types'] = 'currency';//购物积分加密字段
                    $grqb_data['uid'] = $id;//获取积分用户ID
                    $grqb_data['types'] = '2';//减积分
                    $grqb_data['number'] = $zong;//本次产生的积分数量
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法
                    $arr = array(
                        'uid' => $id,
                        'num' => $usdtnum,
                        'money' => $zong,
                        'type' => '0',
                        'time' => time(),
                        'vscprice' => $vscprice,
                    );
                    $re = M('transfers')->add($arr);
                    if ($re) {
                        $url = U('member/memberSort/hzlist');
                        showmessage("购买成功", $url, 1);
                    } else {
                        showmessage('购买失败');
                    }

                }
            } else {
                //svc互转eth
                $vscprice = I('post.vsc_price');
                $usdtprice = I('post.usdt_price');
                $vscnum = abs(I('post.vscnum'));   //购买数量
                $usdtzong = abs(I('post.usdtzong'));
                $zong = sprintf("%.4f", (($vscnum * $vscprice) / $usdtprice));

                if ($money['usdt'] < $zong) {
                    showmessage("USDT余额不足");
                } else {

                    //执行个人钱包操作动作 减去兑换的VSC积分
                    $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                    $grqb_data['key_types'] = 'currency';//购物积分加密字段
                    $grqb_data['uid'] = $id;//获取积分用户ID
                    $grqb_data['types'] = '1';//减积分
                    $grqb_data['number'] = $vscnum;//本次产生的积分数量
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法
                    //执行个人钱包操作动作 加上兑换的VSC积分
                    $grqb_data['zh_types'] = 'usdt';//购物积分字段
                    $grqb_data['key_types'] = 'u_currency';//购物积分加密字段
                    $grqb_data['uid'] = $id;//获取积分用户ID
                    $grqb_data['types'] = '2';//减积分
                    $grqb_data['number'] = $zong;//本次产生的积分数量
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法
                    $arr = array(
                        'uid' => $id,
                        'num' => $vscnum,
                        'money' => $zong,
                        'type' => '1',
                        'time' => time(),
                        'vscprice' => $vscprice,
                        'usdtprice' => $usdtprice,
                    );
                    $re = M('transfers')->add($arr);
                    if ($re) {
                        $url = U('member/memberSort/hzlist');
                        showmessage("购买成功", $url, 1);
                    } else {
                        showmessage('购买失败');
                    }

                }
            }
        } else {
            // $usd_price=$this->getprice();   //
            $usd_price = M('timeprice')->order("id desc")->limit(1)->find();
            $vsc_price = M('tixian_setting')->where(array('id' => 1))->getField('vsc_price');
            $money = M("money_finance")->where(array('uid' => $id))->find();
            $this->assign('money', $money);
            $this->assign("usd_price", $usd_price['price']);
            $this->assign("vsc_price", $vsc_price);
            $this->display();
        }

    }

    public function hzlist()
    {


        $uid = $this->member['id'];
        $transfers = M('transfers');
        $count1 = $transfers->where(array('uid' => $uid))->count();//计算总数
        $page1 = new \Think\Page($count1, 10);
        $page1->rollPage = 10;
        $page1->lastSuffix = false;//最后一页不显示为总页数
        $page1->setConfig('prev', '←');
        $page1->setConfig('next', '→');
        $page1->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $showw = bootstrap_page_style($page1->show());
        $data = $transfers->where(array('uid' => $uid))->limit($page1->firstRow . ',' . $page1->listRows)->order('id desc')->select();

        $this->assign('data', $data);
        $this->assign('page', $showw);

        $this->display();

    }

    public function index()
    {
        $sortid = $this->member['member_sortid'];
        $pwdatas = $this->findnextp($sortid);

        $username = $this->member['username'];
        $this->assign('pwdatas', $pwdatas);
        $this->assign('username', $username);
        $this->display();
    }

    public function get_path_id($id)
    {
        $member = M('member');
        $al = $member->where(array('id' => $id))->find();
        $pid = rtrim($al['path_id'], ',');//去除逗号,切割
        $prev_path = explode(',', $pid);//组成数组
        rsort($prev_path);//rsort() 函数对数值数组进行降序排序
        $prev_path = array_splice($prev_path, 1); //去除自己的ID
        return $prev_path;
    }

    //购买等级
    public function vsc_buy()
    {
        $id = $this->member['id'];
        $ttpass = $this->member['twopassword'];//2级密码
        $resu = $this->member['encrypt'];//盾牌
        if (IS_POST) {
         //   $member = M('member')->where(array('id' => $id))->find();
           $buyprice=I('post.buyprice');
            $number = abs(I('post.number'));   //购买数量
            $pwd = I('post.pwd');  //密码
            $tpwd = md5(md5($pwd) . $resu);
            if ($tpwd != $ttpass) {
                showmessage('二级支付密码输入不正确');
            }
            $money = M("money_finance")->where(array('uid' => $id))->find();
            $totalbuymoney = M('money_buy')->where(array('uid' => $id))->sum("price");
            $type = I('type');
             $newtotalbuymoney= $totalbuymoney+$buyprice;
            $member_group = M('member_group');//会员等级参数列表
            $price = $member_group->Field('money,id')->order("id desc")->select();//查找直推会员等级
            foreach ($price as $key => $value) //根据购买金额来确定杠杆
            {
                $pricearr= explode("-",$value["money"]);//var_dump($pricearr);
                $min=$pricearr[0];
                $max=$pricearr[1];
                if($buyprice>=$min&&$buyprice<=$max){
                    $gangganggrade=  $value["id"]; //新等级
                    break;
                }
            }
            foreach ($price as $key => $value) //根据总入单额确定等级
            {
                $pricearr= explode("-",$value["money"]);//var_dump($pricearr);
                $min=$pricearr[0];
                $max=$pricearr[1];
                if($newtotalbuymoney>=$min&&$newtotalbuymoney<=$max){
                    $newgrade=  $value["id"];
                    break;
                }
            }



            $tksc = sprintf("%.2f", ($number * $buyprice));    //购买数量*矿机单价 =总价
            if ($type == 1) {


            } else {
                //USDT
                if ($money['usdt_wallet'] < $tksc) {
                    showmessage("USDT余额不足");
                } else {

                    //执行个人钱包操作动作 减去usdt
                    $grqb_data['zh_types'] = 'usdt_wallet';//购物积分字段   USDT减
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
                    $mb_group = M('member_group')->where("id=$gangganggrade")->find();
                     $tkscs = $tksc * $mb_group["zincome"];
                    $grqb_data['number'] = $tkscs;//本次产生的积分数量
                  // var_dump($grqb_data);exit;
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法

                    $arr = array(
                        'uid' => $id,
                        'kuang_type' => $newgrade,
                        'kuang_price' => $buyprice,
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
                        $order['kuang_type'] = $newgrade;
                        $order["oid"] = $oid;
                        $data["oid"]=$oid;
                        $data["price"]=$tksc;
                        hook('member_upgrage', $order);//会员等级升级
                        HOOK('dynamic_share', $order);//无限分享按比例释放到锁仓钱包
                        HOOK('dynamic_incometolockwallet', $order);//直推释放按比例释放到锁仓钱包
                        HOOK('dynamic_income', $order);//直推释放5%加速的比率秒释放
                        HOOK('tuanduiprice',$data);//团队奖
                        HOOK('upgrage_tuandui', $order);//团队升级
                        //    HOOK('user_path', $order);//团队收益计算方法
                        $url = U('member/memberSort/vsc_buy');
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
    //购买矿机列表
    //购买矿机列表
    public function vsclist()
    {
        $uid = $this->member['id'];
        $money_buy = M('money_buy');

        $list = $money_buy->where(array('uid' => $uid))->order("id desc ")->select();

        $mb_group = M('member_group');
        foreach ($list as $k => $v) {
            $list[$k]['kuang_type'] = $mb_group->where(array("grade" => $v['kuang_type']))->getField('name');  //矿机名称
             $zincome= $mb_group->where(array("grade" => $v['kuang_type']))->getField('zincome');  //矿机名称
            $list[$k]['ganggan']=sprintf("%.2f", $list[$k]['price']*$zincome);
            //  $list[$k]['richan']=$mb_group->where(array("grade"=>$v['kuang_type']))->getField('day_income');  //日产量
            //  $list[$k]['beishu']=$mb_group->where(array("grade"=>$v['kuang_type']))->getField('zincome');  //倍数
            //   $list[$k]['total_day']=M('money_freed')->where(array('uid'=>$v['uid'],'kuang_price'=>$v['kuang_type']))->getField('total_day');

        }
        // var_dump($list);
        $this->assign('list', $list);
        $this->display();
    }


    //获取第三方平台价格
    public function geteth($url_data)
    {
        $re = $this->http_request($url_data);
        $result = explode("￥", $re);
        $temp = explode("<", str_replace(",", "", $result[1]));
        return $temp[0];
    }


    //过滤字符串空格
    public function loseSpace($pcon)
    {
        $pcon = preg_replace("/ /", "", $pcon);
        $pcon = preg_replace("/&nbsp;/", "", $pcon);
        $pcon = preg_replace("/　/", "", $pcon);
        $pcon = preg_replace("/\r\n/", "", $pcon);
        $pcon = str_replace(chr(13), "", $pcon);
        $pcon = str_replace(chr(10), "", $pcon);
        $pcon = str_replace(chr(9), "", $pcon);
        return $pcon;
    }

    //ETH充值详情页
    public function recharge()
    {
        $id = $this->member['id'];
        $member = M('member');
        $m_name = $member->where(array('id' => $id))->getField('username');
        $bi_money = M('zjpush')->where(array('id' => 1))->getField('money');
        $details = M('eth_detail')->where(array('uid' => $id, 'type' => 1))->order('id desc')->select();
        $list = M('money_buy')->where(array('uid' => $id))->select();
        $tishi = M('zjpush')->where(array('id' => 1))->getField('tishi');
        $usdt_price = M('timeprice')->where(array('id' => 1))->getField('price');
        $this->assign('usdt_price', $usdt_price);
        $this->assign('tishi', $tishi);
        $this->assign('list', $list);
        $this->assign('name', $m_name);
        $this->assign('bi_money', $bi_money);
        $this->assign('details', $details);
        $this->display();
    }


    //用户usdt充值页面
    public function tksc_chong()
    {
        //二维码
        $id = $this->member['id'];

        //usdt
        $member = M('member')->where(array('id' => $id))->find();
        if ($member['btc_address'] == 0) {
            $usdt_address = M('usdt_address');
            $ucountnum = $usdt_address->where(array('status' => 0))->count();   //获取数据库中未使用的条数总和
            if ($ucountnum <= 5) {
                $this->creat_usdt($ucountnum);
            }
            $usdt = $usdt_address->where(array('status' => 0))->find();   //查acny表中未使用的数据
            $address = $usdt['address'];
            $usdtdata = array(
                'uid' => $id,
                'status' => 1,
                'set_time' => time(),
            );
            $usdt_address->where(array('address' => $address))->save($usdtdata);
            $data = array(
                'btc_address' => $address,

            );
            M('member')->where(array('id' => $id))->save($data);
        } else {
            $address = $member['btc_address'];
        }

        $al = M('member')->where(array('id' => $id))->getField('btc_code');   //二维码图片
        if ($al) {
            $membera = M('member')->where(array('id' => $id))->find();
            if ($membera["is_buy3"] == 0) {
                //确定开始查该会员区块
                $tdata['is_buy3'] = 1;
                $tdata['buy_time3'] = time();
                M('member')->where(array('id' => $id))->save($tdata);
            }
            $this->assign('address', $address);
            $this->assign('img_url', $al);
        } else {
            $membera = M('member')->where(array('id' => $id))->find();
            if ($membera["is_buy3"] == 0) {
                //确定开始查该会员区块
                $tdata['is_buy3'] = 1;
                $tdata['buy_time3'] = time();
                M('member')->where(array('id' => $id))->save($tdata);
            }
            $url = $address;
            $file_url = "uploads/usdt_code/" . $id . '.png';
            $img_url = phpqrcode($url, $file_url);
            M('member')->where(array('id' => $id))->setField('btc_code', $img_url);

            $this->assign('address', $address);
            $this->assign('img_url', $img_url);
        }

        $this->display();


    }


    //创建USDT地址
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

    //创建以太坊acny钱包，返回钱包地址,随机密码
    public function creat_acny($acountnum)
    {
        $acny_address = M('acny_address');

        if ($acountnum <= 5) {
            for ($i = 1; $i <= 30; $i++) {
                $transName = "newAccount";
                $password = "#@!hongmun";
                $curlPost = "transName=" . $transName . "&password=" . $password;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://47.89.212.2:8080/ethServer/httpServer');

                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                //设置是通过post还是get方法
                curl_setopt($ch, CURLOPT_POST, 1);
                //传递的变量
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                $data = curl_exec($ch);
                //保存到数据库
                $tdata['address'] = $data;
                $tdata['address_key'] = $password;
                $tdata['status'] = 0;
                $tdata['add_time'] = time();
                $acny_address->add($tdata);
                curl_close($ch);
            }
        }
    }


    public function chong_details()
    {
        $this->display();
    }


    public function queryTransaction($wallet_address)
    {
        //查询用户交易记录
        $transName = "queryTransaction";
        $curlPost = "transName=" . $transName . "&address=" . $wallet_address;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://47.90.100.253:8080/ethServer/httpServer');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //设置是通过post还是get方法
        curl_setopt($ch, CURLOPT_POST, 1);
        //传递的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);
        return $data;
        curl_close($ch);
    }


    /*申请提链明细*/
    public function transferring()
    {

        $uid = $this->member['id'];
        $m_finance = M('money_finance')->where(array('uid' => $uid))->find();//个人财务表
        $this->assign('m_finance', $m_finance);
        $this->assign('uid', $uid);
        $details = M('givemoneys');

        $detail_3 = $details->where("(member_giveid={$uid} or member_receiveid={$uid})")->order("id desc")->select();
        // var_dump($detail_3);
        $super = '0';
        foreach ($detail_3 as $k => $v) {
            $detail_3[$super]['provider'] = M('member')->where(array('id' => $v['member_giveid']))->getField('realname');
            $detail_3[$super]['receiver'] = M('member')->where(array('id' => $v['member_receiveid']))->getField('realname');
            $super += '1';
        }
        $this->assign('detail_3', $detail_3);
        $this->assign('uid', $uid);
        //print_r($detail_3);


        $this->display();
    }

    //互转
    public function member_transfer()
    {
        $rransfer = M('givemoneys');
        $uid = $this->member['id'];
        $users = M('member');
        $money_finance = M('money_finance');//财务记录表
        $secure_encryption = M('secure_encryption');//财务秘钥
        $mobile = I('mobile');
        $nums = I('nums');
        $type = I('type');//转账类型，0认购，1奖励
        $types = 2;

        /* if ($mobile < 1000000) {
            $res = array('staus' => 2, 'info' => '您输入的手机号码有误,请重新输入');
            $this->ajaxReturn($res);
         }  */
        //只能互转上下级
//        $users_a = $users->where(array('id' => $uid))->find();    //转账人信息
//        $users_b = $users->where(array('mobile' => $mobile))->find();//被转人信息
//        $path_id_a =$users_a['path_id'];
//        $path_id_b =$users_b['path_id'];//0,1,2,3,4,5,
//        $pid = rtrim($path_id_a, ',');//去除逗号,切割
//        $prev_path = explode(',', $pid);//组成数组
//
//        $pid2 = rtrim($path_id_b, ',');//去除逗号,切割
//        $prev_path2 = explode(',', $pid2);//组成数组
//
//        $isin = in_array($users_b['id'],$prev_path);//我的path_id有没他
//        $isin2 = in_array($users_a['id'],$prev_path2);//他的path_id有没我
//        if(!$isin && !$isin2){
//            $res = array('staus' => 2, 'info' => '只能转给上级和下级，请重新输入手机号码');
//            $this->ajaxReturn($res);
//        }

        /*个人账户信息*/

        $zz_fee = M('tixian_setting')->where(array('id' => 1))->getField('zz_fee');//手续费比例
        $fee = sprintf("%.2f", $nums * $zz_fee); //手续费

        $jfs = $money_finance->where(array('uid' => $uid))->find();
        if ($types == 2) {
            if ($type == 0) {
                if ($nums + $fee > $jfs['member_bi']) {
                    $res = array('staus' => 2, 'info' => '您输入的转账数量大于您当前账户可用余额,请重新输入！');
                    $this->ajaxReturn($res);
                }
            } elseif ($type == 1) {
                if ($nums + $fee > $jfs['member_z_bi']) {
                    $res = array('staus' => 2, 'info' => '您输入的转账数量大于您当前账户可用余额,请重新输入！');
                    $this->ajaxReturn($res);
                }
            }
        }

        if ($nums <= 0) {
            $mes = '您输入的数量有误,请重新输入';
            $res = array('staus' => 2, 'info' => '您输入的数量有误,请重新输入');
            $this->ajaxReturn($res);
        }

        $username = $users->where(array('username' => $mobile))->find();
        if ($username["id"] == $uid) {
            $res = array('staus' => 2, 'info' => '自己不能给自己转账哦');
            $this->ajaxReturn($res);
        }
        if ($username) {

            $m_finance = $money_finance->where(array('uid' => $uid))->find();//财务记录表
            $keys = $secure_encryption->where(array('uid' => $uid))->find();//会员财务秘钥

            if ($type == 0) {
                if ($m_finance['member_bi'] > 0) {
                    $key_z = md5(md5($m_finance['member_bi']) . $keys['encrypt']);//加密当前子链密钥
                    if ($keys['mother_currency'] != $key_z) {
                        $res = array('staus' => 2, 'info' => '您的认购HM有异常，互转失败！');
                        $this->ajaxReturn($res);
                    }
                }
            } elseif ($type == 1) {
                if ($m_finance['member_z_bi'] > 0) {
                    $key_z = md5(md5($m_finance['member_z_bi']) . $keys['encrypt']);//加密当前子链密钥
                    if ($keys['currency'] != $key_z) {
                        $res = array('staus' => 2, 'info' => '您的奖励HM有异常，转账失败！');
                        $this->ajaxReturn($res);
                    }
                }
            }

            $z_finance = $money_finance->where(array('uid' => $username['id']))->find();//财务记录表
            $keys_z = $secure_encryption->where(array('uid' => $username['id']))->find();//会员财务秘钥
            if ($type == 0) {
                if ($z_finance['member_bi'] > 0) {
                    $key_z = md5(md5($z_finance['member_bi']) . $keys_z['encrypt']);//加密当前子链密钥
                    if ($keys_z['mother_currency'] != $key_z) {
                        $res = array('staus' => 2, 'info' => '您转赠的用户认购HM有异常，转账失败！');
                        $this->ajaxReturn($res);
                    }
                }
            } elseif ($type == 1) {
                if ($z_finance['member_z_bi'] > 0) {
                    $key_z = md5(md5($z_finance['member_z_bi']) . $keys_z['encrypt']);//加密当前子链密钥
                    if ($keys_z['currency'] != $key_z) {
                        $res = array('staus' => 2, 'info' => '您转赠的用户奖励HM有异常，转账失败！');
                        $this->ajaxReturn($res);
                    }
                }
            }


            if ($z_finance) {
                if ($type == 0) {
                    //执行个人钱包操作动作
                    $grqb_data['zh_types'] = 'member_bi';//购物积分字段
                    $grqb_data['key_types'] = 'mother_currency';//购物积分加密字段
                    $grqb_data['uid'] = $username['id'];//获取积分用户ID
                    $grqb_data['types'] = '2';//累加积分
                    $grqb_data['number'] = $nums;//本次产生的积分数量
                    $grqb_data['text'] = '推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法


                } elseif ($type == 1) {
                    //执行个人钱包操作动作
                    $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                    $grqb_data['key_types'] = 'currency';//购物积分加密字段
                    $grqb_data['uid'] = $username['id'];//获取积分用户ID
                    $grqb_data['types'] = '2';//累加积分
                    $grqb_data['number'] = $nums;//本次产生的积分数量
                    $grqb_data['text'] = '推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法

                    $mobile1 = M("member")->where(array('id' => $uid))->getField("username");
                    $mobile2 = M("member")->where(array('id' => $username['id']))->getField("username");
                    send_sms($mobile2, '【HM社区】尊敬的HM会员，' . $mobile1 . '给您转入' . $nums . '个奖励HM已到账，请在个人中心查收。');
                }
            }

            if ($type == 0) {
                //执行个人钱包操作动作
                $grqb_data['zh_types'] = 'member_bi';//购物积分字段
                $grqb_data['key_types'] = 'mother_currency';//购物积分加密字段
                $grqb_data['uid'] = $uid;//获取积分用户ID
                $grqb_data['types'] = '1';//累减积分
                $grqb_data['number'] = $nums + $fee;//本次产生的积分数量
                $grqb_data['text'] = '推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
                $this->personal_wallet2($grqb_data);//调用个人钱包方法
            } elseif ($type == 1) {
                //执行个人钱包操作动作
                $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                $grqb_data['key_types'] = 'currency';//购物积分加密字段
                $grqb_data['uid'] = $uid;//获取积分用户ID
                $grqb_data['types'] = '1';//累减积分
                $grqb_data['number'] = $nums + $fee;//本次产生的积分数量
                $grqb_data['text'] = '推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
                $this->personal_wallet2($grqb_data);//调用个人钱包方法
            }

            /*加到转账明细*/
            $data_a = array(
                'member_giveid' => $uid,//转出会员
                'member_receiveid' => $username['id'],//接收会员
                'money_types' => 3,//交易类型
                'money_states' => '1',
                'money_nums' => $nums,//交易数量
                'do_times' => time(),
                'type' => $type,
                'fee' => $fee,
            );
            $rransfer->add($data_a);

            $res = array('money' => $nums, 'username' => $username['username'], 'staus' => 1, 'types' => $types);
            $this->ajaxReturn($res);
        } else {
            $res = array('staus' => 2, 'info' => '您转账的用户不存在,请重新输入！');
            $this->ajaxReturn($res);
        }


    }


    /*佣金提取*/
    public function extract()
    {
        $tixian = M('tixian_setting')->where(array('id' => 1))->find();
        $users = M('member');
        $uid = $this->member['id'];
        $pays = $users->where(array('id' => $uid))->find();
        $minemsg = $users->where(array('id' => $uid))->find();
        $this->assign('tixian', $tixian);
        $this->assign('pays', $pays);
        $this->assign('minemsg', $minemsg);
        $this->display();
    }


    /*看图片凭证*/
    public function look_news()
    {
        if (IS_POST) {
            $content = M('member_chong')->where(array('id' => I('pid')))->find();
            if (!$content) {
                showmessage('数据不存在');
            }
            showmessage('', '', 1, $content);
        }
        $this->display();
    }

    /*现金积分充值*/
    public function point()
    {
        $id = $this->member['id'];

        $count1 = M('member_chong')->where(array('uid' => $id))->count();//计算总数
        $page1 = new \Think\Page($count1, 10);
        $page1->rollPage = 10;
        $page1->lastSuffix = false;//最后一页不显示为总页数
        $page1->setConfig('prev', '←');
        $page1->setConfig('next', '→');
        $page1->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $showw = bootstrap_page_style($page1->show());
        $data = M('member_chong')->where(array('uid' => $id))->limit($page1->firstRow . ',' . $page1->listRows)->order('id desc')->select();

        $this->assign('data', $data);
        $this->assign('pagee', $showw);
        $this->assign('id', $id);
        $this->display();
    }

    public function geteth_hb()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://hbblock.in/block/webapp/market/getMarketAPP');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        $data = curl_exec($ch);
        curl_close($ch);
        $arr = json_decode($data, true);
        $arr = $arr['result'];
        return $arr[1]['high'];
    }

    public function upload()
    {

        $result['url'] = uploads($_FILES['files']);

        echo json_encode($result);
    }

    public function que()
    {

        $id = $this->member['id'];
        $result = M('member')->where(array('id' => $id))->find();
        if (IS_POST) {
            if (!empty($_FILES['file'])) {
                $file = uploads($_FILES['file']);
                $_POST['image'] = $file['file_url'];
            } else {
                showmessage('请上传凭证');
            }
            if (I('money') == '') {
                showmessage('请填写充值金额');
            }
            if (I('money') <= 0) {
                showmessage('金额填写不正确');
            }
            $order_id = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $tdata['uid'] = $id;
            $tdata['money'] = I('money');
            $tdata['time'] = time();
            $tdata['status'] = 1;
            $tdata['image'] = I('image');
            $tdata['order_id'] = $order_id;
            $al = M('member_chong')->add($tdata);
            if ($al) {
                $url = U('Member/MemberSort/point');
                showmessage('已付款，待确认', $url, 1);
            } else {
                showmessage('提交失败');
            }
        } else {
            $this->display();
        }

    }

    public function xian()
    {

        $this->display();
    }


    /*执行现金积分转赠*/
    public function que_xian()
    {

        $rransfer = M('givemoneys');
        $secure_encryption = M('secure_encryption');//财务秘钥
        $money_finance = M('money_finance');//财务记录表
        $uid = $this->member['id'];
        $users = M('member');
        $address = I('address');    //32位加密地址  recharge_code
        $types = I('type');  //转币类型  1VSC 2USDT
        $nums = abs(I('nums'));
        $pwd = I('twopwd');  //支付密码
        $ttpass = $this->member['twopassword'];//2级密码
        $resu = $this->member['encrypt'];//盾牌
        $tpwd = md5(md5($pwd) . $resu);
        if ($tpwd != $ttpass) {
            $res = array('status' => 1, 'info' => '支付密码输入不正确');
            $this->ajaxReturn($res);

        }


        //只能互转上下级
//        $users_a = $users->where(array('id' => $uid))->find();    //转账人信息
//        $users_b = $users->where(array('mobile' => $mobile))->find();//被转人信息
//        $path_id_a =$users_a['path_id'];
//        $path_id_b =$users_b['path_id'];//0,1,2,3,4,5,
//        $pid = rtrim($path_id_a, ',');//去除逗号,切割
//        $prev_path = explode(',', $pid);//组成数组
//
//        $pid2 = rtrim($path_id_b, ',');//去除逗号,切割
//        $prev_path2 = explode(',', $pid2);//组成数组
//
//        $isin = in_array($users_b['id'],$prev_path);//我的path_id有没他
//        $isin2 = in_array($users_a['id'],$prev_path2);//他的path_id有没我
//        if(!$isin && !$isin2){
//            $res = array('staus' => 2, 'info' => '只能转给上级和下级，请重新输入手机号码');
//            $this->ajaxReturn($res);
//        }

        if ($nums <= 0) {

            $res = array('staus' => 2, 'info' => '您输入的数量有误,请重新输入');
            $this->ajaxReturn($res);
        }
        /*个人账户信息*/
        $m_finance = $money_finance->where(array('uid' => $uid))->find();//财务记录表
        $keys = $secure_encryption->where(array('uid' => $uid))->find();//会员财务秘钥
        $jfs = $users->where(array('id' => $uid))->find();
        $username = $users->where(array('recharge_code' => $address))->find();   //根据对方地址查找用户信息

        if ($username['recharge_code']) {
            if ($types == 1) {
                //type=1  VSC
                if ($nums > $m_finance['member_z_bi']) {
                    $res = array('staus' => 2, 'info' => '您输入的转赠VSC大于您账户当前可用余额,请重新输入');
                    $this->ajaxReturn($res);
                }
                //执行个人钱包操作动作
                $grqb_data['zh_types'] = 'member_z_bi';//块区包账户字段
                $grqb_data['key_types'] = 'currency';//块区包账户加密字段
                $grqb_data['uid'] = $username['id'];//获取积分用户ID
                $grqb_data['types'] = '2';//累加积分
                $grqb_data['number'] = $nums;//本次产生的积分数量
                $grqb_data['text'] = '会员互转VSC时，系统发现对方VSC余额数据异常，停止给此会员执行累加VSC余额动作！';
                personal_wallet($grqb_data);//调用个人钱包方法
                //执行个人钱包操作动作
                $grqb_data['zh_types'] = 'member_z_bi';//块区包账户字段
                $grqb_data['key_types'] = 'currency';//块区包账户加密字段
                $grqb_data['uid'] = $uid;//获取积分用户ID
                $grqb_data['types'] = '1';//减自己的
                $grqb_data['number'] = $nums;//本次产生的积分数量
                $grqb_data['text'] = '会员互转VSC时，系统发现会员VSC余额数据异常，停止给此会员执行累加VSC余额动作！';
                $status = personal_wallet($grqb_data);//调用个人钱包方法
                if ($status) {
                    /*加到转账明细*/
                    $data['member_giveid'] = $uid;
                    $data['member_receiveid'] = $username['id'];
                    $data['money_types'] = '1';
                    $data['money_states'] = 1;
                    $data['money_nums'] = $nums;
                    $data['do_times'] = time();
                    $rransfer->add($data);

                    $res = array('money' => $nums, 'username' => $username['username'], 'staus' => 1, 'types' => $types);
                    $this->ajaxReturn($res);
                }


            } elseif ($types == 2) {
                //type=2  USDT
                if ($nums > $m_finance['usdt']) {
                    $res = array('staus' => 2, 'info' => '您输入的转赠USDT大于您账户当前可用余额,请重新输入');
                    $this->ajaxReturn($res);
                }
                //执行个人钱包操作动作
                $grqb_data['zh_types'] = 'usdt';//块区包账户字段
                $grqb_data['key_types'] = 'u_currency';//块区包账户加密字段
                $grqb_data['uid'] = $username['id'];//获取积分用户ID
                $grqb_data['types'] = '2';//累加积分
                $grqb_data['number'] = $nums;//本次产生的积分数量
                $grqb_data['text'] = '会员互转USDT时，系统发现对方USDT余额数据异常，停止给此会员执行累加USDT收益余额动作！';
                personal_wallet($grqb_data);//调用个人钱包方法
                //执行个人钱包操作动作
                $grqb_data['zh_types'] = 'usdt';//块区包账户字段
                $grqb_data['key_types'] = 'u_currency';//块区包账户加密字段
                $grqb_data['uid'] = $uid;//获取积分用户ID
                $grqb_data['types'] = '1';//减自己的
                $grqb_data['number'] = $nums;//本次产生的积分数量
                $grqb_data['text'] = '会员互转USDT收益时，系统发现对方USDT收益余额数据异常，停止给此会员执行累加USDT收益余额动作！';
                $status = personal_wallet($grqb_data);//调用个人钱包方法
                if ($status) {
                    /*加到转账明细*/
                    $data['member_giveid'] = $uid;
                    $data['member_receiveid'] = $username['id'];
                    $data['money_types'] = '2';
                    $data['money_states'] = 1;
                    $data['money_nums'] = $nums;
                    $data['do_times'] = time();
                    $rransfer->add($data);

                    $res = array('money' => $nums, 'username' => $username['username'], 'staus' => 1, 'types' => $types);
                    $this->ajaxReturn($res);
                }
            }

        } else {
            $res = array('staus' => 2, 'info' => '您输入的对方地址有误,请重新输入');
            $this->ajaxReturn($res);
        }

    }

    //查看直推业绩
    public function team()
    {
        $id = $this->member['id'];
        $member = M('member');
        $order_sub = M('order_sub');    //子订单表

        if ($_GET['start'] && $_GET['end']) {
            $start_time = strtotime($_GET['start']);

            $end_time = strtotime(date('Y-m-d 23:59:59', strtotime($_GET['end'])));
            $dateline = array('between', array($start_time, $end_time));
        } elseif ($_GET['start'] && !$_GET['end']) {
            $start_time = strtotime($_GET['start']);

            $dateline = array('egt', $start_time);

        } elseif (!$_GET['start'] && $_GET['end']) {

            $end_time = strtotime(date('Y-m-d 23:59:59', strtotime($_GET['end'])));
            $dateline = array('elt', $end_time);
        }

        if ($dateline) {
            $zi_tua = $member->where(array('pid' => $id))->field('id')->select();
            foreach ($zi_tua as $k => $v) {
                $ty = array('like', '%,' . $v['id'] . ',%');
                $z_tuan1 = $member->where(array('path_id' => $ty))->select();//下级

                foreach ($z_tuan1 as $kkk => $vvv) {

                    $order_info = $order_sub->where(array('buyer_id' => $vvv['id'], 'pay_status' => 1, 'pay_time' => $dateline))->find();   //查所有订单
                    if ($order_info) {
                        $mid[$kkk] = $order_info;
                    }
                }
            }
            //print_r($mid);
            $az_zhi = 0;
            $danjia = 6500;
            foreach ($mid as $k => $v) {
                $az_zhi += $v['real_price'];   //总额
                $user[$k]['username'] = $member->where(array('id' => $v['buyer_id']))->getField('username');
                $user[$k]['shij'] = date("Y-m-d", $v['pay_time']);
                $user[$k]['dan'] = $v['real_price'] / $danjia;

            }
            $this->ajaxReturn($user);
        } else {

            //查询所有直推
            $zhi_tui = $member->where(array('pid' => $id))->field('id,username')->select();
            $az_zhi = 0;
            $azb_zhi = 0;
            $danjia = 6500;
            foreach ($zhi_tui as $k => $v) {
                $ty = array('like', '%,' . $v['id'] . ',%');
                $z_tuan1 = $member->where(array('path_id' => $ty))->select();//下级

                foreach ($z_tuan1 as $kkk => $vvv) {
                    $user[$k]['real_price'] += $order_sub->where(array('buyer_id' => $vvv['id'], 'pay_status' => 1))->getField('real_price');   //总业绩
                }

                $user[$k]['username'] = $v['username'];
                $user[$k]['id'] = $order_sub->where(array('buyer_id' => $v['id'], 'pay_status' => 1))->getField('buyer_id');

                $user[$k]['shij'] = $order_sub->where(array('buyer_id' => $v['id'], 'pay_status' => 1))->getField('pay_time');
                $user[$k]['dan'] = ($user[$k]['real_price'] / $danjia);

                $azb_zhi += $user[$k]['real_price'];
            }


            $zz_zhi = $azb_zhi / $danjia;//团队总业绩


            $this->assign('user', $user);

            $this->assign('zz_zhi', $zz_zhi);//总共的业绩

        }


        $this->display();
    }


    public function clientsee()
    {
        $id = $this->member['id'];
        $sort = M('member_sorts');//左区右区表

        if ($_GET) {

//		    根据页面查询输入框的值 查询用户表是否有此用户id
            $id = M('member')->where(array('username' => array('like', '%' . $_GET['oid'] . '%')))->getField('id');
            //print_r($id);die;
        }


        $sortid = $sort->where(array('uid' => $id))->find();//左区右区表

        $one = $sort->where(array('pid' => $sortid['id'], 'member_bigposition' => 1))->select();//系统排位第二层左

        $one_pid1 = '';   //第三层左
        $one_pid2 = '';   //第三层右
        foreach ($one as $key => $value) {
            $one_pid1[] = $sort->where(array('pid' => $value['id'], 'member_bigposition' => 1))->select();//系统下下级
            $one_pid2[] = $sort->where(array('pid' => $value['id'], 'member_bigposition' => 2))->select();//系统下下级
        }

        $two = $sort->where(array('pid' => $sortid['id'], 'member_bigposition' => 2))->select();//系统排位第二层右边
        $two_pid1 = '';   //第三层左
        $two_pid2 = '';   //右
        foreach ($two as $key => $value) {
            $two_pid1[] = $sort->where(array('pid' => $value['id'], 'member_bigposition' => 1))->select();//系统下下级
            $two_pid2[] = $sort->where(array('pid' => $value['id'], 'member_bigposition' => 2))->select();//系统下下级
        }


//        print_r($one);
        $this->assign('one', $one);
        $this->assign('two', $two);
        $this->assign('one_pid1', $one_pid1);
        $this->assign('one_pid2', $one_pid2);
        $this->assign('two_pid1', $two_pid1);
        $this->assign('two_pid2', $two_pid2);


        $az_zhi = $sort->where(array('uid' => $id))->find();


        //自己的信息
        $zj = M('member')->where(array('id' => $id))->find();
        $this->assign('zj', $zj);

        $this->assign('l_az_zhi', $az_zhi['member_lposition']);
        $this->assign('r_az_zhi', $az_zhi['member_rposition']);

        //print_r($two_pid);
        $this->display();
    }

    //无限级树形
    function tomapp($pid = '0')
    {
        //echo '4444';die;

        global $arr;
        $t = M('member_sorts');
        //$return_ka=M('return_ka');
        $sort = M('member_sorts');//左区右区表
        $time = array('between', array(strtotime(date('Y-m-d 0:0:0', time())), strtotime(date('Y-m-d 23:59:59', time()))));
        $list = $t->where(array('pid' => $pid))->order('id asc')->select();
        if (is_array($list)) {

            foreach ($list as $k => $v) {

                //$dada=$sort->where(array('uid'=>$v['id']))->find();
                $vv = M('member')->where(array('id' => $v['uid']))->find();
                $l_personal = $sort->where(array('pid' => $v['id'], 'member_bigposition' => 1))->find();//左区
                if ($l_personal) {
                    $l_ty = array('like', '%,' . $l_personal['id'] . ',%');
                    $l_tuan = $sort->where(array('path_id' => $l_ty))->getfield('uid', true);
                    if ($l_tuan) {
                        $l_az_zhi = M('money_finance')->where(array('uid' => array('in', $l_tuan), 'grade' => array('neq', 0)))->sum('member_bi');//左区母币总值
                        if ($l_az_zhi == 0) {
                            $l_az_zhi = 0;
                        }
                    }
                } else {
                    $l_az_zhi = 0;
                }


                $r_personal = $sort->where(array('pid' => $v['id'], 'member_bigposition' => 2))->find();//右区
                if ($r_personal) {
                    $r_ty = array('like', '%,' . $r_personal['id'] . ',%');
                    $r_tuan = $sort->where(array('path_id' => $r_ty))->getfield('uid', true);
                    if ($r_tuan) {
                        $r_az_zhi = M('money_finance')->where(array('uid' => array('in', $r_tuan), 'grade' => array('neq', 0)))->sum('member_bi');//右区母币总值
                        if ($r_az_zhi == 0) {
                            $r_az_zhi = 0;
                        }
                    }
                } else {
                    $r_az_zhi = 0;
                }

                $list[$k]['name'] = $vv['username'] . ' | ' . $vv['realname'] . ' | 左区:' . $l_az_zhi . ' | 右区:' . $r_az_zhi;
                if ($t->where(array('pid' => $v['id']))->getfield('id')) {

                    $list[$k]['isParent'] = true;
                } else {

                    $list[$k]['isParent'] = false;

                }

                $arr[] = $list[$k];

                $this->tomapp($v['id']);
            }


            return json_encode($arr);

        }


    }


//查看左右区业绩

    public function client()
    {

        $id = $this->member['id'];
        $member = M('member');
        $return_ka = M('order_sub');

        if ($_GET['start'] && $_GET['end']) {
            $start_time = strtotime($_GET['start']);

            $end_time = strtotime(date('Y-m-d 23:59:59', strtotime($_GET['end'])));
            $dateline = array('between', array($start_time, $end_time));
        } elseif ($_GET['start'] && !$_GET['end']) {
            $start_time = strtotime($_GET['start']);

            $dateline = array('egt', $start_time);

        } elseif (!$_GET['start'] && $_GET['end']) {

            $end_time = strtotime(date('Y-m-d 23:59:59', strtotime($_GET['end'])));
            $dateline = array('elt', $end_time);
        }


        $time = array('between', array(strtotime(date('Y-m-d 0:0:0', time())), strtotime(date('Y-m-d 23:59:59', time()))));
        $ty = array('like', '%,' . $id . ',%');
        $z_tuan1 = $member->where(array('path_id' => $ty, 'id' => array('neq', $id)))->select();//下级
        $az_zhi = 0;
        $azb_zhi = 0;
        $new_zhi = 0;
        foreach ($z_tuan1 as $kkk => $vvv) {
            $az_zhi += $return_ka->where(array('buyer_id' => $vvv['id'], 'pay_status' => 1, 'pay_time' => $time))->sum('real_price');
            $azb_zhi += $return_ka->where(array('buyer_id' => $vvv['id'], 'pay_status' => 1))->sum('real_price');
            $new_zhi += $return_ka->where(array('buyer_id' => $vvv['id'], 'pay_status' => 1, 'pay_time' => $dateline))->sum('real_price');
        }

        $t_zhi = $az_zhi;//团队今日新增业绩
        $zz_zhi = $azb_zhi;//团队总业绩
        $new_zz = $new_zhi;//查询业绩
        $this->assign('t_zhi', $t_zhi);//新增的业绩 今天的
        $this->assign('zz_zhi', $zz_zhi);//总共的业绩
        $this->assign('new_zz', $new_zz);

        $money_types = M('money_types');//通过交易详情表查询链总业绩
        $azz_zhi = 0;
        $abb_zhi = 0;
        foreach ($z_tuan1 as $k => $v) {
            $azz_zhi += $money_types->where(array('member_getid' => $v['id'], 'money_type' => 1, 'money_produtime' => $time))->sum('money_hcb');//统计今日新增链数量
            $azz_zhi += $money_types->where(array('member_getid' => $v['id'], 'money_type' => 1, 'money_produtime' => $time))->sum('money_hcb');//统计今日新增链数量
            $abb_zhi += $money_types->where(array('member_getid' => $v['id'], 'money_type' => 1))->sum('money_hcb');//统计团队链数量

        }
        $azz_zhi = $azz_zhi;//团队今日新增链
        $abb_zhi = $abb_zhi;//团队总链总额
        $this->assign('azz_zhi', $azz_zhi);//团队今日新增链
        $this->assign('abb_zhi', $abb_zhi);//团队总链总额

        if ($_GET['ac'] == 'ajax') {

            echo $this->tomap($id);
            exit();
        }
        $this->display();


    }

    //无限级树形
    function tomap($pid = '0')
    {
        global $arr;
        $t = M('member');
        $return_ka = M('order_sub');
        $time = array('between', array(strtotime(date('Y-m-d 0:0:0', time())), strtotime(date('Y-m-d 23:59:59', time()))));
        $list = $t->where(array('pid' => $pid))->order('id asc')->select();
        if (is_array($list)) {

            foreach ($list as $k => $v) {

                $z_tuan1 = $t->where(array('pid' => $v['id']))->select();//下级

                $az_zhi = 0;
                $azb_zhi = 0;
                foreach ($z_tuan1 as $kkk => $vvv) {
                    $tyy = array('like', '%,' . $vvv['id'] . ',%');
                    $z_tuan = $t->where(array('path_id' => $tyy))->select();//团队(下级)
                    $z_zhi = 0;
                    $zb_zhi = 0;
                    foreach ($z_tuan as $kk => $vv) {
                        $z_zhi += $return_ka->where(array('buyer_id' => $vv['id'], 'pay_time' => $time, 'pay_status' => 1))->sum('real_price');//团队的总业绩 (直推单个)
                        $zb_zhi += $return_ka->where(array('buyer_id' => $vv['id'], 'pay_status' => 1))->sum('real_price');//团队的总业绩 (直推单个)
                    }
                    $az_zhi += $z_zhi;
                    $azb_zhi += $zb_zhi;

                }

                $ziji = $return_ka->where(array('pay_status' => 1, 'buyer_id' => $v['id']))->sum('real_price');
                if ($ziji == '') {
                    $ziji = 0;
                }


                $list[$k]['name'] = $v['username'] . ' | ' . $v['realname'];
                if ($t->where(array('pid' => $v['id']))->getfield('id')) {

                    $list[$k]['isParent'] = true;
                } else {

                    $list[$k]['isParent'] = false;

                }

                $arr[] = $list[$k];

                $this->tomap($v['id']);
            }


            return json_encode($arr);

        }


    }


    public function dowithdrawals()
    {
        $tixian_setting = M('tixian_setting');
        $users = M('member');
        $withs = M('withdrawals');
        $userid = $this->member['id'];
        $type = I('type');
        $moneynums = I('moneynums');
        $recode = I('code');
        $remobile = I('mobile');

        $code = session('code');
        $mobile = session('mobile');
        $time = session('set_time');

        //提现开关
        $status = $tixian_setting->where(array('id' => 1))->find();
        if ($status['status'] == 0) {
            $data = array('status' => 1, 'info' => '提现功能暂未开启');
            $this->ajaxReturn($data);
        }

        //提现次数
        $ok_count = $tixian_setting->where(array('id' => 1))->getField('gong');

        $cishu = $withs->where(array('member_id' => $userid))->order('id desc')->find();
        if ($cishu) {
            $date1 = date_create($cishu['withdrawals_time']);
            $date2 = date_create(date("Y-m-d", time()));//当前时间
            $diff = date_diff($date1, $date2);//日期相减
            $tian = $diff->format("%a");//得到的天数

            if ($tian < $ok_count) {
                $data = array('status' => 1, 'info' => '提现失败，每隔' . $ok_count . '天只能提一次');
                $this->ajaxReturn($data);
            }

        }

        /*我所拥有的佣金*/
        $mineyj = $users->where(array('id' => $userid))->getField('member_yj');

        if ($moneynums % $status['money'] != 0 && $moneynums < $status['low_money']) {
            $data = array('status' => 1, 'info' => '提现金额为' . $status['money'] . '的倍数,并且大于' . $status['low_money'] . '元');
            $this->ajaxReturn($data);
        }
        if ($moneynums > $mineyj) {
            $data = array('status' => 1, 'info' => '可提SKV大于账户SKV总数');
            $this->ajaxReturn($data);
        }
        if (empty($type)) {
            $data = array('status' => 1, 'info' => '请选择提现账户');
            $this->ajaxReturn($data);
        }

        //手续费
        $moneynums_money = $moneynums - sprintf("%.2f", ($moneynums * ($status['poundage'])));

        $fee = sprintf("%.2f", ($moneynums * ($status['poundage'])));

        $witd['member_id'] = $userid;
        $witd['withdrawals_nums'] = $moneynums_money;
        $witd['fee'] = $fee;
        $witd['withdrawals_types'] = $type;
        $witd['withdrawals_time'] = date('Y-m-d H:i:s');
        $witd['withdrawals_states'] = 0;
        $witd['withdrawals_paytype'] = 1;
        $witd['withdrawals_khname'] = I('khname');
        $witd['withdrawals_khbank'] = I('khbank');
        $witd['withdrawals_khnums'] = I('khnums');
        $witd['withdrawals_zfbnums'] = I('zfbnums');
        $witd['withdrawals_wxnums'] = I('wxnums');
        //print_r($witd);die;


        /*给佣金减掉对应数量*/
        $users->where(array('id' => $userid))->setDec('member_yj', $moneynums);

        $res = $withs->add($witd);
        if ($res) {
            $data = array('status' => 2, 'info' => '您已成功提现', 'mes' => $moneynums, 'types' => $type);
            $this->ajaxReturn($data);

        } else {
            $data = array('status' => 3, 'info' => '提现失败,请重试');
            $this->ajaxReturn($data);
        }
    }

    /*链提现*/
    public function intremove()
    {
        $users = M('member');
        $userid = $this->member['id'];
        $minejf = $users->where(array('id' => $userid))->getfield('member_jifen');
        $minemsg = $users->where(array('id' => $userid))->find();
        $this->assign('minejf', $minejf);
        $this->assign('minemsg', $minemsg);
        $this->display();
    }


    // 发送验证码
    public function sentcode()
    {
        $mobile = I('post.mobile');
        $user = M("member");
        $bool = $user->where(array('mobile' => $mobile))->find();

        if (empty($mobile)) {
            $data['state'] = 0;
            $data['res'] = "手机号不能为空";
        } elseif (time() > session('set_time') + 60 || session('set_time') == '') {
            // ==产生验证码==
            $code = rand(100000, 999999);
            // 5分钟后session过期
            session('set_time', time());
            session('code', $code, 300);
            session('mobile', $mobile);

            // 发送验证码
            $mes = setMsg($mobile, $code);
            if ($mes == '0') {
                $data['state'] = 1;
                $data['res'] = "发送成功";
            } else {
                $data['state'] = 0;
                $data['res'] = "发送失败";
            }
        } else {
            $msgtime = session('set_time') + 60 - time();
            $res = $msgtime . '秒之后再试';
            $data['state'] = 0;
            $data['res'] = $res;
        }
        $this->AjaxReturn($data);
    }

    public function tixian()
    {
        $users = M('withdrawals');
        $uid = $this->member['id'];
        $msg = $users->where(array('member_id' => $uid))->select();
        $this->assign('msg', $msg);
        $this->display();
    }


    //撤销出售
    public function order_cancellation()
    {
        exit('<script>alert("非法操作");</script>');
        $member = M('member');
        $market = M('market');
        $zjpush = M('zjpush')->where(array('id' => 1))->find();
        $id = $this->member['id'];
        $d_id = $_POST['d_id'];//订单id

        $us_order = $market->where(array('id' => $d_id, 'uid' => $id, 'zt_status' => '0'))->find();
        if ($us_order) {
            $cx_number = $us_order['number'] / $zjpush['sell_get_money'];//挂单数量/出售99%
            $m_ok = $member->where(array('id' => $id))->setInc('member_bi', $cx_number);//卖家获得的收益
            if ($m_ok) {
                $bdata['member_getid'] = $id;//出售币获得金额 会员id
                $bdata['member_giveid'] = $id;//购买币会员id
                $bdata['money_produtime'] = time();
                $bdata['money_price'] = $zjpush['money'];//当次交易单价
                $bdata['money_hcb'] = $cx_number;//当次交易数量
                $bdata['money_type'] = 17;
                M('money_types')->add($bdata);//写入收益详情表

                $invalid = array(
                    'uid' => $us_order['uid'],
                    'price' => $us_order['price'],
                    'number' => $us_order['number'],
                    'status' => $us_order['status'],
                    'time' => $us_order['time'],
                    'zt_status' => '1',//1是会员手动撤销，2是系统撤销
                );
                M('market_invalid')->add($invalid);//写入出售无效表里备用

                $market->where(array('id' => $d_id))->delete();
            }

            showmessage('出售鸿承撤销成功，请确认数据！', '', 1);

        } else {

            showmessage('数据不存在，请重新操作！');
        }

    }


    //奖金类型；1->服务中心获得收益，2->代理收益，3->董事收益,4->消费收益，5->直推收益，6->间推收益

    public function returnka()
    {
        $id = $this->member['id'];
        $returnka = M('money_types');

        $count = $returnka->where(array('member_getid' => $id))->count();//计算总数
        $page = new \Think\Page($count, 10);
        $page->rollPage = 10;
        $Page->lastSuffix = false;//最后一页不显示为总页数
        $page->setConfig('prev', '←');
        $page->setConfig('next', '→');
        $page->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $show = bootstrap_page_style($page->show());
        $where['money_type'] = array('in', '1,2,3,4,5,6,7');
        $jingtai = $returnka->where(array('member_getid' => $id))->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('id desc')->select();
        $dongtai = $returnka->where(array('member_getid' => $id, 'money_type' => '8'))->limit($page->firstRow . ',' . $page->listRows)->order('id desc')->select();
        $boss = $returnka->where(array('member_getid' => $id, 'money_type' => array('in', '14,15')))->limit($page->firstRow . ',' . $page->listRows)->order('id desc')->select();
        //print_r($data);die;
        //  var_dump($jingtai);
        $this->assign('jingtai', $jingtai);
        $this->assign('dongtai', $dongtai);
        $this->assign('boss', $boss);
        $this->assign('page', $show);
        $this->display();
    }

    //链冻结记录
    public function freed()
    {
        $id = $this->member['id'];
        $freed = M('integral_freezing');
        $count = $freed->where(array('uid' => $id))->count();//计算总数
        $page = new \Think\Page($count, 8);
        $page->rollPage = 5;
        $Page->lastSuffix = false;//最后一页不显示为总页数
        $page->setConfig('prev', '←');
        $page->setConfig('next', '→');
        $page->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $show = bootstrap_page_style($page->show());
        $data = $freed->where(array('uid' => $id))->limit($page->firstRow . ',' . $page->listRows)->order('id desc')->select();
        $this->assign('data', $data);
        $this->assign('page', $show);
        $this->display();
    }


    /*我的申请提币*/
    public function commidetbi()
    {
        $users = M('withbi');
        $uid = $this->member['id'];
        $msg = $users->where(array('member_id' => $uid))->order('id desc')->select();
        $tixian = M('tixian_setting')->where(array('id' => 1))->find();
        $this->assign('tixian', $tixian);

        $this->assign('msg', $msg);
        //var_dump($msg);
        $this->display();
    }

//    提币输入密码页面
    public function upCoinPwd()
    {

        $this->display();
    }

    /*SKV提取*/
    public function extractbi()
    {
        $tixian = M('tixian_setting')->where(array('id' => 1))->find();
        $users = M('member');
        $uid = $this->member['id'];
        $pays = $users->where(array('id' => $uid))->find();
        $minemsg = $users->where(array('id' => $uid))->find();

        $userdata = M('member_profile')->where(array('uid' => $uid))->find();
        $this->assign('userdata', $userdata);

        $this->assign('tixian', $tixian);
        $this->assign('pays', $pays);
        $this->assign('minemsg', $minemsg);
        $this->display();
    }



    // 申请提链方法
    // 申请提链方法
    public function dowithdrawalsbi()
    {

        if (!IS_POST) {
            exit('<script>alert("非法操作");location.href="' . U('member/index/index') . '"</script>');
        }
        $tixian_setting = M('tixian_setting');
        $users = M('member');
        $withs = M('withbi');
        $userid = $this->member['id'];
        $moneynums = abs(I('moneynums'));//申请提链数量
        $status = $tixian_setting->where(array('id' => 1))->find();
        $type = I('type');   //提取类型
        $pwd = I('pwd');  //支付密码
        $address = I('address');
        $ttpass = $this->member['twopassword'];//2级密码
        $resu = $this->member['encrypt'];//盾牌
        $tpwd = md5(md5($pwd) . $resu);
        if ($tpwd != $ttpass) {
            $res = array('status' => 1, 'info' => '支付密码输入不正确');
            $this->ajaxReturn($res);

        }
        if ($moneynums < $status['money']) {
            $data = array('status' => 1, 'info' => '提币数量最低为' . $status['money'] . '个');
            $this->ajaxReturn($data);
        }
        /*执行扣链动作*/
        $secure_encryption = M('secure_encryption')->where(array('uid' => $userid))->find();//个人财务表密钥
        $money_finance = M('money_finance')->where(array('uid' => $userid))->find();//个人财务表

        //提现到地址
        $zong_moneynums = $moneynums;
        if ($type == 1) {
            //VSC
            $waller_test = $users->where(array("recharge_code" => $address))->getField('recharge_code');
            if ($waller_test) {
                if ($zong_moneynums > $money_finance['member_z_bi']) {
                    $data = array('status' => 1, 'info' => '输入的VSC数量大于账户总数，请修改后在提交！');
                    $this->ajaxReturn($data);
                }

                $key_x = md5(md5($money_finance['member_z_bi']) . $secure_encryption['encrypt']);//当前安全密钥
                if ($key_x != $secure_encryption['currency']) {
                    $data = array('status' => 1, 'info' => '您的VSC提币数据异常，请查正后在提交！');
                    $this->ajaxReturn($data);
                }

                $witd['member_id'] = $userid;//用户id
                $witd['withdrawals_nums'] = $moneynums - ($status['poundage'] * $moneynums);//申请提币数量   0.05
                $witd['fee'] = $status['poundage'] * $moneynums;//矿工费
                $witd['shangcheng_jf'] = 0;//商城积分
                $witd['withdrawals_types'] = 1;//选择提积3账户
                $witd['withdrawals_time'] = date('Y-m-d H:i:s');//申请时间
                $witd['withdrawals_states'] = 0;//订单状态
                $witd['withdrawals_paytype'] = 1;//1是VSC，2是USDT
//            $witd['withdrawals_khname'] = $info['account_name'];//开户名
//            $witd['withdrawals_khbank'] = $info['account_bank'];//开户行
//            $witd['withdrawals_khnums'] = $info['bank_account'];//卡号
                $witd['zb_address'] = $address;
                $res = $withs->add($witd);
                //执行扣币
                $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                $grqb_data['key_types'] = 'currency';//购物积分加密字段
                $grqb_data['uid'] = $userid;//获取积分用户ID
                $grqb_data['types'] = '1';//减积分
                $grqb_data['number'] = $zong_moneynums;//本次产生的积分数量
                $grqb_data['text'] = '会员提币时，系统发现会员VSC余额数据异常，停止给此会员执行扣VSC动作！';
                $this->personal_wallet($grqb_data);

                if ($res) {
                    $data = array('status' => 2, 'info' => '您已成功申请提收VSC，请注意查收！', 'mes' => $moneynums, 'jifen' => 'VSC');
                    $this->ajaxReturn($data);
                } else {
                    $data = array('status' => 3, 'info' => '提VSC失败,请重试！');
                    $this->ajaxReturn($data);
                }
            } else {

                $data = array('status' => 1, 'info' => '您填写的提现地址不存在！');
                $this->ajaxReturn($data);
            }
        } else {
            //usdt
            $waller_test = substr($address, 0, 1);   //获取前一位地址   长度34

            if ($waller_test == "1" && strlen($address) == "34") {
                if ($moneynums > $money_finance['usdt']) {
                    $data = array('status' => 1, 'info' => '输入的USDT数量大于账户总数，请修改后在提交！');
                    $this->ajaxReturn($data);
                }

                $key_x = md5(md5($money_finance['usdt']) . $secure_encryption['encrypt']);//当前安全密钥
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
                    /*减掉子链对应数量*/
                    $grqb_data['zh_types'] = 'usdt';//购物积分字段
                    $grqb_data['key_types'] = 'u_currency';//购物积分加密字段
                    $grqb_data['uid'] = $userid;//获取积分用户ID
                    $grqb_data['types'] = '1';//减积分
                    $grqb_data['number'] = $moneynums;//本次产生的积分数量
                    $grqb_data['text'] = '会员提USDT时，系统发现USDT余额数据异常，停止给此会员执行扣除USDT动作！';
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法

                    $witd['member_id'] = $userid;//用户id
                    $witd['withdrawals_nums'] = $moneynums;//申请提链数量
                    $witd['withdrawals_types'] = 1;//选择提积3账户
                    $witd['withdrawals_time'] = date('Y-m-d H:i:s');//申请时间
                    $witd['withdrawals_states'] = 0;//订单状态
                    $witd['tixian_hash'] = $hash;
                    $witd['withdrawals_paytype'] = 2;//1VSC，2是USDT
                    $witd['zb_address'] = $address;//转积地址
                    $res = $withs->add($witd);
                    /*减掉子链对应数量*/


                    if ($res) {
                        $data = array('status' => 2, 'info' => '您已成功申请提USDT，请30分钟后确认是否到账！', 'mes' => $moneynums, 'jifen' => 'USDT');
                        $this->ajaxReturn($data);
                    } else {
                        $data = array('status' => 1, 'info' => '提USDT失败,请重试！');
                        $this->ajaxReturn($data);
                    }
                } else {
                    $log_data = array(
                        'uid' => $userid,
                        'text' => $data,
                        'time' => time(),
                    );
                    M('exception_log')->data($log_data)->add();
                    $data = array('status' => 1, 'info' => '提USDT失败,请重试！');
                    $this->ajaxReturn($data);

                }


            } else {
                $data = array('status' => 1, 'info' => '请填写正确的USDT地址！');
                $this->ajaxReturn($data);
            }
        }


    }

    public function usdt_que()
    {
        $withbi = M('withbi');

        $sqlmap['withdrawals_states'] = 0;   //0未查询  1已查询  2已到账
        $sqlmap['withdrawals_paytype'] = 3;//1ACNY，2是HMK  3USDT

        $withbis = $withbi->where($sqlmap)->select();
        if ($withbis) {
            foreach ($withbis as $k => $v) {

                //根据交易hash值查询交易是否已经完成
                $transName = "getTransActionUsdt";
                $curlPost = "transName=" . $transName . "&txid=" . $this->loseSpace($v['tixian_hash']);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://47.254.26.30:8080/ethServer/httpServer');
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                $data = curl_exec($ch);
                curl_close($ch);
                $result = $data;//得到的hash值,过滤字符串空格
                $result = explode(",", $result);
                $result1 = explode(":", $result[0]);    //flag
                $result2 = explode(":", $result[1]);   //value
                $result3 = explode(":", $result[2]);   //msg:查询交易事物成功

                $flag = $this->loseSpace($result1[1]);   //true   false
                $value = $this->loseSpace($result2[1]);   //归集的总额
                if ($flag) {
                    $witd['que_time'] = time();//申请时间
                    $witd['withdrawals_states'] = 2;//订单状态

                    M('withbi')->where(array('id' => $v['id']))->save($witd);

                } else {

                    //失败返回给用户
                    $witd['que_time'] = time();//申请时间
                    $witd['withdrawals_states'] = 1;//订单状态
                    $withbi->where(array('id' => $v['id']))->save($witd);
                    $secure_encryption = M('secure_encryption')->where(array('uid' => $v['member_id']))->find();//个人财务表密钥
                    /*增加对应数量*/
                    M('money_finance')->where(array('uid' => $v['member_id']))->setInc('usdt', $v['withdrawals_nums']);
                    $m_finan = M('money_finance')->where(array('uid' => $v['member_id']))->find();//个人财务表
                    $key_u = md5(md5($m_finan['usdt']) . $secure_encryption['encrypt']);//当前安全密钥
                    M('secure_encryption')->where(array('uid' => $v['member_id']))->setField('u_currency', $key_u);//个人财务表密钥
                    $log_info = array(
                        'mid' => $v['member_id'],
                        'value' => $v['withdrawals_nums'],
                        'type' => "usdt",
                        'msg' => "用户提现USDT失败，返回",
                        'dateline' => time(),

                    );
                    M('member_log')->data($log_info)->add();


                }
            }


        }

    }


    public function withbilist()
    {
        $id = $this->member['id'];
        $withbi = M('withbi');
        $count = $withbi->where(array('member_id' => $id))->count();//计算总数
        $page = new \Think\Page($count, 8);
        $page->rollPage = 5;
        $Page->lastSuffix = false;//最后一页不显示为总页数
        $page->setConfig('prev', '←');
        $page->setConfig('next', '→');
        $page->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $show = bootstrap_page_style($page->show());
        $data = $withbi->where(array('member_id' => $id))->limit($page->firstRow . ',' . $page->listRows)->order('id desc')->select();
        $this->assign('data', $data);
        $this->assign('page', $show);


        $this->display();
    }


    public function hzjf()
    {
        $id = $this->member['id'];
        $records = M('givemoneys');
        $data['member_giveid'] = $id;
        $data['member_receiveid'] = $id;
        $data['_logic'] = 'or';
        $sqlmap = array();
        $sqlmap['_complex'] = $data;
        $sqlmap['money_types'] = 1;

        $dets = $records->where($sqlmap)->order('id desc')->select();
        $this->assign('dets', $dets);
        $this->assign('uid', $id);
        $this->display();
    }


    //交易市场
    public function jf()
    {

        $market_ok = M('market_ok');
        $market = M('market');
        $id = $this->member['id'];
        $zjpush = M('zjpush')->where(array('id' => 1))->find();
        $zuotime = array('between', array(strtotime(date('Y-m-d 00:00:00', time() - 24 * 60 * 60)), strtotime(date('Y-m-d 23:59:59', time() - 24 * 60 * 60))));
        $m_fan = $market_ok->where(array('time' => $zuotime))->order('id desc')->limit(1)->select();
        $z_price = $zjpush['money'];
        $da = $market_ok->limit(50)->order('id desc')->select();
        $dat = $market->where(array('zt_status' => 0))->select();
        $data = $market->where(array('uid' => $id, 'zt_status' => 0))->find();
        $sql = "select max(s_price) from gboy_market_ok";//最大值
        $result = $market_ok->query($sql);
        foreach ($result['0'] as $k => $v) {
            if ($v) {
                $vv = $v;
            } else {
                $vv = $zjpush['money'];
            }

        }
        $this->assign('vv', $vv);
        $sqll = "select min(s_price) from gboy_market_ok";//最小值
        $results = $market_ok->query($sqll);
        foreach ($results['0'] as $kk => $vv) {
            if ($v) {
                $vvv = $vv;
            } else {
                $vvv = $zjpush['money'];
            }
        }
        $this->assign('vvv', $vvv);

        //一天的成交量
        $sqltime = array('between', array(strtotime(date('Y-m-d 00:00:00', time())), strtotime(date('Y-m-d 23:59:59', time()))));
        $zong = $market_ok->where(array('time' => $sqltime))->sum('number');

        $new_price = M('zjpush')->where(array('id' => 1))->getField('money');//当前单价
        $this->assign('new_price', $new_price);
        $this->assign('zong', $zong);
        $this->assign('da', $da);
        $this->assign('dat', $dat);
        $this->assign('data', $data);
        $this->assign('z_price', $z_price);
        $this->assign('zjpush', $zjpush);
        $this->display();
    }

    /*系统执行撤销前一天挂单未出售成功的SKV*/
    public function unsold()
    {

        exit('<script>alert("非法操作");</script>');

        if ($_GET['mid'] != IEdcdeRDDw4d2uy5) {
            exit('<script>alert("禁止操作");</script>');
        }

        $zjpush = M('zjpush')->where(array('id' => 1))->find();
        $u_market = M('market');//出售挂单列表
        $member = M('member');
        $t_market = $u_market->where(array('zt_status' => '0'))->order('id asc')->select();
        if ($t_market) {
            foreach ($t_market as $k => $v) {
                $date1 = date_create(date("Y-m-d", $v['time']));//数据保存的更新时间
                $date2 = date_create(date("Y-m-d", time()));//当前时间
                $diff = date_diff($date1, $date2);//日期相减
                $tian = $diff->format("%a");//得到的天数
                if ($tian >= 1) {
                    $cx_number = $v['number'] / $zjpush['sell_get_money'];//挂单数量/出售99%
                    $m_ok = $member->where(array('id' => $v['uid']))->setInc('member_bi', $cx_number);//撤销挂单，返回链给会员

                    $invalid = array(
                        'uid' => $v['uid'],
                        'price' => $v['price'],
                        'number' => $v['number'],
                        'status' => $v['status'],
                        'time' => $v['time'],
                        'zt_status' => '2',//1是会员手动撤销，2是系统撤销
                    );
                    M('market_invalid')->add($invalid);//写入出售无效表里备用
                    $u_market->where(array('id' => $v['id']))->delete();//删除未出售的订单

                }

            }
        }

    }

    // 系统自动控制每天涨0.03元
    public function hc_rise()
    {

        if ($_GET['mid'] != IEdcdeRDDw4d2uy4) {
            exit('<script>alert("禁止操作");</script>');
        }

        $zjpush = M('zjpush')->where(array('id' => '1'))->find();
        $date1 = date_create(date("Y-m-d", $zjpush['change_time']));//数据保存的更新时间
        $date2 = date_create(date("Y-m-d", time()));//当前时间
        $diff = date_diff($date1, $date2);//日期相减
        $tian = $diff->format("%a");//得到的天数
        if ($tian >= 1) {
            M('zjpush')->where(array('id' => '1'))->setInc('money', $zjpush['float']);//每天固定涨0.03元
            M('zjpush')->where(array('id' => '1'))->setField('change_time', time());//修改时间

        }

    }


    //修改产品的销售价格

    public function change_price()
    {

        if ($_GET['mid'] != IEdcdeRDDw4d2uy43fdd) {
            exit('<script>alert("禁止操作");</script>');
        }

        $goods_spu = M('goods_spu');//产品主表
        $goods_sku = M('goods_sku');//产品副表
        $goods_index = M('goods_index');//产品副表
        $zjpush = M('zjpush')->where(array('id' => '1'))->find();//参数表
        $member_group = M('member_group');//会员等级表

        $goods = $goods_index->order('sort asc')->select();
        $number = 0;
        foreach ($goods as $k => $v) {
            $number += 1;
            $hct_number = $member_group->where(array('id' => $number))->getField('min_points');
            $money = $hct_number * $zjpush['money'];//链的总数 * 链当前单价
            $goods_spu->where(array('id' => $v['spu_id']))->setField(array('min_price' => $money, 'max_price' => $money));//修改产品单价
            $goods_sku->where(array('spu_id' => $v['spu_id']))->setField(array('market_price' => $money, 'shop_price' => $money));//修改产品单价
            $goods_index->where(array('spu_id' => $v['spu_id']))->setField(array('shop_price' => $money));//修改产品单价

        }

    }


    //每过几秒使用自动作业执行(//查询用户余额)USDT交易
    public function interest_calculation_u()
    {
        $member = M('member');
        $sqlmap['id'] = array('neq', 1);
        $sqlmap['is_buy3'] = 1;

        $members = $member->where($sqlmap)->select();
        if ($members) {
            foreach ($members as $k => $v) {
                //查询是否1小时没进行充值
                $date1 = strtotime(date("y-m-d h:i:s", $v['buy_time']));//数据保存的更新时间
                $date2 = strtotime(date("y-m-d h:i:s", time()));//当前时间
                $diff = $date2 - $date1;//日期相减
                $m = $diff / 60 / 60;//得到的分钟数

                if ($m > 1) {//如果上次点开充值时间距离现在大于1小时，撤销
                    $ttdata['is_buy3'] = 0;
                    M('member')->where(array('id' => $v['id']))->save($ttdata);
                }

                //查询余额
                $wallet_address = $this->loseSpace($v['btc_address']);   //过滤空格
                $transName = "getBalanceUsdt";
                $curlPost = "transName=" . $transName . "&address=" . $wallet_address;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://47.254.26.30:8080/ethServer/httpServer');
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                $data = curl_exec($ch);

                curl_close($ch);
                $yue = $data;   //余额

                if ($data > 0) {

                    $tdata['is_buy3'] = 2;    //如果有余额设置为2
                    M('member')->where(array('id' => $v['id']))->save($tdata);
                    $sqlmap['uid'] = $v['id'];
                    $sqlmap['status'] = 1;
                    $sqlmap['hash'] = array('NEQ', "0");
                    $is_true = M('member_chong')->where($sqlmap)->find();
                    if (!$is_true) {

                        $tdata['uid'] = $v['id'];
                        $tdata['time'] = time();
                        $tdata['status'] = 1;   //1待审核 2审核  3失败
                        $tdata['hou_time'] = time();   //后台确定时间
                        $tdata['type'] = 2;    //0转账  1acny  2USDT
                        $tdata['hash'] = 0;
                        //$tdata['eth_price']=$eth_money;
                        $tdata['usdt'] = $yue;    //余额
                        $tdata['buy_status'] = 1;    //第一步
                        //$ins_id = M('member_chong')->add($tdata);

                        M('member_chong')->add($tdata);
                    }
                }


            }
        }


    }

//归集usdt
    public function interest_calculation_ubuy()
    {

        $chong = M('member_chong');
        $member = M('member');
        $sqlmap['status'] = 1;   //待审核
        $sqlmap['buy_status'] = 2;    //第一步
        $sqlmap['type'] = 2;    //0转账  1acny  2USDT
        $sqlmap['chong_type'] = 0;
        $chongs = $chong->where($sqlmap)->order('id desc')->select();//查找所有usdt充值记录
        if ($chongs) {
            foreach ($chongs as $k => $v) {
                //归集usdt
                $user = $member->where(array('id' => $v['uid']))->find();
                $transName = "collUsdt";
                $wallet_address = $this->loseSpace($user['btc_address']);   //过滤空格

                $number = $v['usdt'];    //归集usdt
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
                $result = $data;//得到的hash值,过滤字符串空格
                $result = explode(",", $result);
                $result1 = explode(":", $result[0]);
                $result2 = explode(":", $result[1]);

                $hash = $this->loseSpace($result[1]);   //hash值
                $value = $this->loseSpace($result[1]);   //归集的总额
                $tdata['hash'] = $hash;
                $tdata['buy_status'] = 2;     //第2步
                $tdata['uque_time'] = time();
                M('member_chong')->where(array('id' => $v['id']))->save($tdata);

            }
        }
    }


//确认交易状态   flag=true  还需5个人以上确认成功
    public function interest_calculation_uque()
    {

        $chong = M('member_chong');
        $member = M('member');
        $sqlmap['type'] = 2;   //usdt
        $sqlmap['status'] = 1;   //待确认
        $sqlmap['hash'] = array('NEQ', "0");
        $sqlmap['buy_status'] = 2;
        $sqlmap['chong_type'] = 0;
        $chongs = $chong->where($sqlmap)->order('id desc')->select();//查找所有ETH充值记录
        foreach ($chongs as $k => $v) {

            //根据交易hash值查询交易是否已经完成
            $transName = "getTransActionUsdt";
            $curlPost = "transName=" . $transName . "&txid=" . $this->loseSpace($v['hash']);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://47.254.26.30:8080/ethServer/httpServer');
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
            $data = curl_exec($ch);
            curl_close($ch);
            $result = $data;//得到的hash值,过滤字符串空格
            $result = explode(",", $result);
            $result1 = explode(":", $result[0]);    //flag
            $result2 = explode(":", $result[1]);   //value
            $result3 = explode(":", $result[2]);   //msg:查询交易事物成功
            $da3 = explode(",", $result3);

            $flag = $this->loseSpace($result1[1]);   //true   false
            $value = $this->loseSpace($result2[1]);   //归集的总额

            if ($flag) {
                //执行个人钱包操作动作
                $grqb_data['zh_types'] = 'usdt';//购物积分字段
                $grqb_data['key_types'] = 'u_currency';//购物积分加密字段
                $grqb_data['uid'] = $v['uid'];//获取积分用户ID
                $grqb_data['types'] = '2';//累加积分
                $grqb_data['number'] = $value;//本次产生的积分数量
                $grqb_data['text'] = '会员充值USDT时，系统发现会员USDT余额数据异常，停止给此会员执行USDT动作！';
                $this->personal_wallet2($grqb_data);//调用个人钱包方法

                $tdata['status'] = 2;   //后台已确认
                $tdata['buy_status'] = 3;     //第3步
                $tdata['money'] = $value;     //充值的币数量
                $chong->where(array('id' => $v['id']))->save($tdata);

                $tdata['is_buy3'] = 0;
                M('member')->where(array('id' => $v['uid']))->save($tdata);
            } else {
                continue;
            }

        }


    }


    //充值自动认购
    public function tksc_buy_auto($id, $money)
    {
        $rengou_price = M('simu')->where(array('is_here' => 1))->getField("rengou_price");
        $member = M('member')->where(array('id' => $id))->find();
        $tksc_test = $money / $rengou_price; //认购hm
        $tksc = $this->buy_simu($money, $tksc_test);
        $tixian_setting = M('tixian_setting')->where(array('id' => 1))->find();
        if ($tixian_setting['team_status'] == 1) {//团队奖是否已开启
            //团队动态奖励
            $userinfo['uid'] = $id;//用户id
            $userinfo['money'] = $tksc;//TKSC数量
            $userinfo['gou'] = $member['gou'];//用于判断该会员是否为新增成员
            $this->team_rewards($userinfo);
        }
        //保存到数据库
        $sid = M('simu')->where(array('is_here' => 1))->getField('id');
        $tdata['num'] = $money;
        $tdata['type'] = 1;//0转入，1转出
        $tdata['time'] = time();
        //$tdata['hash']=$hash;
        $tdata['uid'] = $id;
        $tdata['status'] = 1;
        $tdata['tksc'] = $tksc;
        //$tdata['fa']=$address;
        //$tdata['shou']=$address;
        $tdata['simu'] = $sid;
        M('eth_detail')->add($tdata);
        $tdata['gou'] = 1;
        M('member')->where(array('id' => $id))->save($tdata);
    }

    //每过几秒使用自动作业执行(查询余额，保存数据)
    public function interest_calculation_token()
    {
        $abcdate["uid"] = "1";
        M("linshi")->add($abcdate);
        $member = M('member');
        $sqlmap['id'] = array('neq', 1);
        $sqlmap['is_buy2'] = 0;
        //$sqlmap['is_buy']=0;

        $members = $member->where($sqlmap)->select();
        if ($members) {
            foreach ($members as $k => $v) {
                $waller_test = substr($v['wallet_address'], 0, 2);

                if ($waller_test == "0x") {
                    //查询余额
                    $wallet_address = $this->loseSpace($v['wallet_address']);
                    $transName = "queryBalanceErc20";
                    $curlPost = "transName=" . $transName . "&address=" . $wallet_address;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://47.89.212.2:8080/ethServer/httpServer');
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    $yue = $data;
                    $yue = number_format($yue / pow(10, 18), 7, '.', '');

                    $finance = M("money_finance")->where(array('uid' => $v['id']))->find();
                    if ($yue > 0 && $yue > $finance['member_t_bi'] + 0.1) {
                        $yue = $yue - $finance['member_t_bi'];

                        $tdata['is_buy2'] = 1;
                        M('member')->where(array('id' => $v['id']))->save($tdata);
                        //$eth_money2=$this->geteth('https://www.feixiaohao.com/currencies/ethereum');//第三方以太坊单价
                        //$eth_money = $eth_money2[2][0];
                        $tdata['uid'] = $v['id'];
                        $tdata['money'] = $yue;
                        $tdata['time'] = time();
                        $tdata['status'] = 2;
                        $tdata['hou_time'] = time();
                        $tdata['type'] = 1;
                        $tdata['hash'] = 0;
                        //$tdata['eth_price']=$eth_money;
                        $tdata['eth'] = $yue;
                        $tdata['chong_type'] = 1;
                        $tdata['buy_status2'] = 3;

                        M('member_chong')->add($tdata);

                        //执行个人钱包操作动作
                        $grqb_data['zh_types'] = 'member_t_bi';//购物积分字段
                        $grqb_data['key_types'] = 't_currency';//购物积分加密字段
                        $grqb_data['uid'] = $v['id'];//获取积分用户ID
                        $grqb_data['types'] = '2';//累加积分
                        $grqb_data['number'] = $yue;//本次产生的积分数量
                        $grqb_data['text'] = '会员撤单时，系统发现会员CNY余额数据异常，停止给此会员执行CNY动作！';


                        $this->personal_wallet2($grqb_data);//调用个人钱包方法

                        $tdata['is_buy2'] = 0;
                        M('member')->where(array('id' => $v['id']))->save($tdata);
                        $mobile = M("member")->where(array('id' => $v['id']))->getField("username");
                        send_sms($mobile, '【HM社区】尊敬的HM会员，您充值的的' . $grqb_data['number'] . '个HMK Token已到账，请在个人中心查收。');
                    }
                }

            }
        }


    }


    public function getdetails()
    {
        $sqlmap = array();
        if ($_GET['start'] && $_GET['end']) {
            $start_time = strtotime($_GET['start']);
            $end_time = strtotime($_GET['end']);
            $sqlmap['time'] = array('between', array($start_time, $end_time));

        } elseif ($_GET['start'] && !$_GET['end']) {
            $start_time = strtotime($_GET['start']);
            $sqlmap['time'] = array('egt', $start_time);

        } elseif (!$_GET['start'] && $_GET['end']) {
            $end_time = strtotime($_GET['end']);
            $sqlmap['time'] = array('elt', $end_time);
        }

        if ($_GET['keyword']) {
            $uid = M("member")->where(array('username' => $_GET['keyword']))->getField('id');
            $ty = array('like', '%,' . $uid . ',%');
            $tuan = M("member")->where(array('path_id' => $ty, 'islock' => 0))->getfield('id', true);
            if ($tuan) {
                $sqlmap['uid'] = array('in', $tuan);
                $num = M('eth_detail')->where($sqlmap)->sum('tksc');
                $count = M('eth_detail')->where($sqlmap)->count();
                $this->assign("num", $num);
                $this->assign("count", $count);
            }
            $members = M('member')->where(array('id' => $uid))->find();
            if ($members) {
                $this->assign("members", $members);
            }
        }
        $this->display();
    }

    //每过几秒使用自动作业执行(撤单)
    public function interest_calculation_che()
    {
        //order_match_time
        $trading_order = M("trading_order");

        $day = date('Y-m-d H:i:s', strtotime("-2 hours"));
        $star_date = strtotime($day);//过去两小时
        $end_date = strtotime(date('Y-m-d H:i:s'));//当前时间
        $sqlmap['order_match_time'] = array('between', $star_date . "," . $end_date);
        $sqlmap['order_status'] = 1;

        $order = $trading_order->where($sqlmap)->order('id desc')->select();
        foreach ($order as $k => $v) {
            $date1 = strtotime(date("y-m-d h:i:s", $v['order_match_time']));//数据保存的更新时间
            $date2 = strtotime(date("y-m-d h:i:s", time()));//当前时间
            $diff = $date2 - $date1;//日期相减
            $m = $diff / 60 / 60;//得到的分钟数
            if ($m > 2) {//如果已经超过两个小时，撤单
                $tdata['order_status'] = -1;
                $trading_order->where(array('id' => $v['id']))->save($tdata);
                if ($v['account_type'] == 0) {
                    //执行个人钱包操作动作
                    $grqb_data['zh_types'] = 'member_bi';//购物积分字段
                    $grqb_data['key_types'] = 'mother_currency';//购物积分加密字段
                    $grqb_data['uid'] = $v['seller_id'];//获取积分用户ID
                    $grqb_data['types'] = '2';//累加积分
                    $grqb_data['number'] = $v['num'] + $v['fee'];//本次产生的积分数量
                    $grqb_data['text'] = '会员撤单时，系统发现会员积分余额数据异常，停止给此会员执行累加积分动作！';
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法
                } elseif ($v['account_type'] == 1) {
                    //执行个人钱包操作动作
                    $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                    $grqb_data['key_types'] = 'currency';//购物积分加密字段
                    $grqb_data['uid'] = $v['seller_id'];//获取积分用户ID
                    $grqb_data['types'] = '2';//累加积分
                    $grqb_data['number'] = $v['num'] + $v['fee'];//本次产生的积分数量
                    $grqb_data['text'] = '会员撤单时，系统发现会员积分余额数据异常，停止给此会员执行累加积分动作！';
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法
                }
            }
        }
    }


    /**
     * [激活token]
     */
    public function token_jihuo($userinfo)
    {
        $id = $userinfo['uid'];//用户id
        $money = $userinfo['money'];//TKSC数量
        $seting = M("tixian_setting")->where(array("id" => 1))->find();
        $money_finance = M("money_finance");
        $jihuo_details = M("jihuo_details");
        $prev_path = $this->get_path_id($id);

        $give = M('member')->where(array('id' => $id))->find(); //触发者

        $num_my = $money * $seting['gr_jihuo'];//个人激活数量
        $token_my = $money_finance->where(array('uid' => $id))->getField('member_t_bi');//token余额
        if ($token_my >= $num_my) {//余额充足
            $snum_my = $num_my;
        } else {
            $snum_my = $token_my;
        }
        if ($token_my > 0) {
            //执行个人钱包操作动作
            $grqb_data['zh_types'] = 'member_t_bi';//购物积分字段
            $grqb_data['key_types'] = 't_currency';//购物积分加密字段
            $grqb_data['uid'] = $id;//获取积分用户ID
            $grqb_data['types'] = '1';//累减积分
            $grqb_data['number'] = $snum_my;//本次产生的积分数量
            $grqb_data['text'] = '进行激活token时，系统发现会员的数据异常，停止给此会员执行动作！';
            $this->personal_wallet2($grqb_data);//调用个人钱包方法

            //执行个人钱包操作动作
            $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
            $grqb_data['key_types'] = 'currency';//购物积分加密字段
            $grqb_data['uid'] = $id;//获取积分用户ID
            $grqb_data['types'] = '2';//累加积分
            $grqb_data['number'] = $snum_my;//本次产生的积分数量
            $grqb_data['text'] = '进行激活token时，系统发现会员的数据异常，停止给此会员执行动作！';
            $this->personal_wallet2($grqb_data);//调用个人钱包方法

            //添加明细
            $tdata['num'] = $snum_my;
            $tdata['give_username'] = $give['username'];
            $tdata['give_realname'] = $give['realname'];
            $tdata['user_username'] = $give['username'];
            $tdata['user_realname'] = $give['realname'];
            $tdata['time'] = time();
            $jihuo_details->add($tdata);
        }

        foreach ($prev_path as $k => $v) {  //循环当前会员的上级
            if ($k == 10) {
                break;
            }//从0开始，最高追溯十层
            if ($v == "0") {
                break;
            }//如果pid为0，结束

            $get = M('member')->where(array('id' => $v))->find(); //激活者

            if ($k == 0) {
                $num = $money * $seting['zt_jihuo'];//直推激活数量
            } else {
                $num = $money * $seting['sj_jihuo'];//十级激活数量
            }
            $token = $money_finance->where(array('uid' => $v))->getField('member_t_bi');//token余额
            if ($token >= $num) {//余额充足
                $snum = $num;
            } else {
                $snum = $token;
            }
            if ($token > 0) {
                //执行个人钱包操作动作
                $grqb_data['zh_types'] = 'member_t_bi';//购物积分字段
                $grqb_data['key_types'] = 't_currency';//购物积分加密字段
                $grqb_data['uid'] = $v;//获取积分用户ID
                $grqb_data['types'] = '1';//累减积分
                $grqb_data['number'] = $snum;//本次产生的积分数量
                $grqb_data['text'] = '进行激活token时，系统发现会员的数据异常，停止给此会员执行动作！';
                $this->personal_wallet2($grqb_data);//调用个人钱包方法

                //执行个人钱包操作动作
                $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                $grqb_data['key_types'] = 'currency';//购物积分加密字段
                $grqb_data['uid'] = $v;//获取积分用户ID
                $grqb_data['types'] = '2';//累加积分
                $grqb_data['number'] = $snum;//本次产生的积分数量
                $grqb_data['text'] = '进行激活token时，系统发现会员的数据异常，停止给此会员执行动作！';
                $this->personal_wallet2($grqb_data);//调用个人钱包方法

                //添加明细
                $tdata['num'] = $snum;
                $tdata['give_username'] = $give['username'];
                $tdata['give_realname'] = $give['realname'];
                $tdata['user_username'] = $get['username'];
                $tdata['user_realname'] = $get['realname'];
                $tdata['time'] = time();
                $jihuo_details->add($tdata);
            }
        }
    }


    /**
     * [团队动态奖励]
     */
    public function team_rewards($userinfo)
    {
        $id = $userinfo['uid'];//用户id
        $money = $userinfo['money'];//TKSC数量
        $gou = $userinfo['gou'];//如果是0，代表首次充值，为新增成员
        $seting = M("tixian_setting")->where(array("id" => 1))->find();
        $member = M("member");//用户表
        $prev_path = $this->get_path_id($id);

        foreach ($prev_path as $k => $v) {  //循环当前会员的上级
            if ($k == 10) {
                break;
            }//从0开始，最高追溯十层
            if ($v == "0") {
                break;
            }//如果pid为0，结束

            $vgou = M('member')->where(array('id' => $v))->getField('gou');
            $bi = M('money_finance')->where(array('uid' => $v))->getField('member_bi');
            if ($bi) {
                if ($bi >= 1000) {

                    //先给上级直推的10%
                    if ($k == 0) {
                        $psum = M("money_finance")->where(array('uid' => $id))->getField("member_bi");//当前上级的直推人数
                        if ($psum >= 1000) {
                            $num = $money * $seting['register'];//团队发展奖励数量
                            //执行个人钱包操作动作
                            $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                            $grqb_data['key_types'] = 'currency';//购物积分加密字段
                            $grqb_data['uid'] = $v;//获取积分用户ID
                            $grqb_data['types'] = '2';//累加积分
                            $grqb_data['number'] = $num;//本次产生的积分数量
                            $grqb_data['text'] = '进行团队发展奖励时，系统发现会员的糖果余额数据异常，停止给此会员执行糖果积分动作！';
                            $grqb_data['is_mx'] = 1;//是否添加明细，0不添加，1添加
                            $grqb_data['member_giveid'] = $id;//提供会员id
                            $grqb_data['money_type'] = 1;//明细类型
                            $this->personal_wallet2($grqb_data);//调用个人钱包方法
                        }

                    } elseif ($k > 0) { //如果1-3人，拿5层，如果4-10人，拿10层
                        //$tuan_count = $member->where(array('pid' => $v, 'islock' => 0,'gou'=>1))->count();//当前上级的直推人数

                        $Model = new \Think\Model();
                        $num = $Model->query("SELECT count(*) as num FROM `gboy_member` a,gboy_money_finance b where a.id = b.uid and a.pid=" . $v . " and b.member_bi >= 1000");
                        $tuan_count = $num[0]['num'];

                        if ($k < 5 && $tuan_count >= 3) {  //直推多少人，拿几层
                            $num = $money * $seting['team'];//团队发展奖励数量
                            //执行个人钱包操作动作
                            $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                            $grqb_data['key_types'] = 'currency';//购物积分加密字段
                            $grqb_data['uid'] = $v;//获取积分用户ID
                            $grqb_data['types'] = '2';//累加积分
                            $grqb_data['number'] = $num;//本次产生的积分数量
                            $grqb_data['text'] = '进行团队发展奖励时，系统发现会员的糖果余额数据异常，停止给此会员执行糖果积分动作！';
                            $grqb_data['is_mx'] = 1;//是否添加明细，0不添加，1添加
                            $grqb_data['member_giveid'] = $id;//提供会员id
                            $grqb_data['money_type'] = 2;//明细类型
                            $this->personal_wallet2($grqb_data);//调用个人钱包方法
                        }
                        if ($k >= 5 && $tuan_count >= 6) {  //直推多少人，拿几层
                            $num = $money * $seting['team'];//团队发展奖励数量
                            //执行个人钱包操作动作
                            $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                            $grqb_data['key_types'] = 'currency';//购物积分加密字段
                            $grqb_data['uid'] = $v;//获取积分用户ID
                            $grqb_data['types'] = '2';//累加积分
                            $grqb_data['number'] = $num;//本次产生的积分数量
                            $grqb_data['text'] = '进行团队发展奖励时，系统发现会员的糖果余额数据异常，停止给此会员执行糖果积分动作！';
                            $grqb_data['is_mx'] = 1;//是否添加明细，0不添加，1添加
                            $grqb_data['member_giveid'] = $id;//提供会员id
                            $grqb_data['money_type'] = 2;//明细类型
                            $this->personal_wallet2($grqb_data);//调用个人钱包方法
                        }
                    }

                }

            }


        }
    }

    /**
     * [社区动态奖励，极差]
     */
    public function shequ_rewards($userinfo)
    {
        $id = $userinfo['uid'];//用户id
        $money = $userinfo['money'];//TKSC数量
        $member = M("member");//用户表
        $prev_path = $this->get_path_id($id);
        $in = 0;
        foreach ($prev_path as $k => $v) {  //循环当前会员的上级
            if ($v == "0") {
                break;
            }//如果pid为0，结束

            $users = $member->where(array('id' => $v))->find();//上级
            $group = M('member_group')->where(array('id' => $users['group_id']))->find();//升级后的等级
            $group2 = M('member_group')->where(array('id' => $in))->find();//极差上一个等级

            if ($group['id'] > $in) {
                if ($group2) {
                    $interest = $group['interest'] - $group2['interest'];
                } else {
                    $interest = $group['interest'];
                }
                if ($interest > 0) {
                    $num = $money * $interest;//奖励数量
                    //执行个人钱包操作动作
                    $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                    $grqb_data['key_types'] = 'currency';//购物积分加密字段
                    $grqb_data['uid'] = $v;//获取积分用户ID
                    $grqb_data['types'] = '2';//累加积分
                    $grqb_data['number'] = $num;//本次产生的积分数量
                    $grqb_data['text'] = '进行团队发展奖励时，系统发现会员的糖果余额数据异常，停止给此会员执行糖果积分动作！';
                    $grqb_data['is_mx'] = 1;//是否添加明细，0不添加，1添加
                    $grqb_data['member_giveid'] = $id;//提供会员id
                    $grqb_data['money_type'] = 3;//明细类型
                    $this->personal_wallet2($grqb_data);//调用个人钱包方法
                }
                $in = $group['id'];
            }
        }
    }


    public function send_sm()
    {

        if (IS_POST) {
            $mobile1 = $this->member['username'];
            $code1 = mt_rand(1000, 9999);
            $code = sha1(md5(trim($code1)));
            $mobile = sha1(md5(trim($mobile1)));
            $r = send_sms($mobile1, '【HM】您的注册验证码为：' . $code1 . '，请不要把短信验证码泄露给他人，如非本人操作可不用理会');

            if ($r == 0) {

                session('yzm', $code, 30);
                session('phone', $mobile, 30);

            } else {
                showmessage('手机验证码发送失败');
            }
            $_SESSION['set_time'] = time();
            showmessage('手机验证码发送成功', '', 1);
        }

    }

    //修改手机验证码
    public function send_sm1()
    {

        if (IS_POST) {
            $mobile1 = $_POST['mobile'];
            $code1 = mt_rand(1000, 9999);
            $code = sha1(md5(trim($code1)));
            $mobile = sha1(md5(trim($mobile1)));
            $r = send_sms($mobile1, '【HM】您的确认修改手机号验证码为：' . $code1 . '，请不要把短信验证码泄露给他人，如非本人操作可不用理会');

            if ($r == 0) {

                session('yzm', $code, 30);
                session('phone', $mobile, 30);

            } else {
                showmessage('手机验证码发送失败');
            }
            $_SESSION['set_time'] = time();
            showmessage('手机验证码发送成功', '', 1);
        }
    }

    //修改手机验证码
    public function send_sm2()
    {

        if (IS_POST) {
            $mobile1 = $this->member['username'];
            $code1 = mt_rand(1000, 9999);
            $code = sha1(md5(trim($code1)));
            $mobile = sha1(md5(trim($mobile1)));
            $r = send_sms($mobile1, '【HM】您的资产互转验证码为：' . $code1 . '，请不要把短信验证码泄露给他人，如非本人操作可不用理会');

            if ($r == 0) {

                session('yzm', $code, 30);
                session('phone', $mobile, 30);

            } else {
                showmessage('手机验证码发送失败');
            }
            $_SESSION['set_time'] = time();
            showmessage('手机验证码发送成功', '', 1);
        }
    }

    //修改手机验证码
    public function send_sm3()
    {

        if (IS_POST) {
            $mobile1 = $this->member['username'];
            $code1 = mt_rand(1000, 9999);
            $code = sha1(md5(trim($code1)));
            $mobile = sha1(md5(trim($mobile1)));
            $r = send_sms($mobile1, '【HM】您的申请提资产验证码为：' . $code1 . '，请不要把短信验证码泄露给他人，如非本人操作可不用理会');

            if ($r == 0) {

                session('yzm', $code, 30);
                session('phone', $mobile, 30);

            } else {
                showmessage('手机验证码发送失败');
            }
            $_SESSION['set_time'] = time();
            showmessage('手机验证码发送成功', '', 1);
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
    //    if ($cw[$qb_types] > 0) {
            if ($types == 1) {
                $money_finance->where(array('uid' => $id))->setDec($qb_types, $number);//减去积分
            } elseif ($types == 2) { //echo $qb_types;
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
     //   }

    }

    public function ceshi()
    {
        $money_finance = M('money_finance');//个人财务表
        $cw_encryption = M('secure_encryption');//个人财务加密表
        $users = $money_finance->select();
        foreach ($users as $k => $v) {
            $keys = $cw_encryption->where(array('uid' => $v['uid']))->find();

            $member_bii = $money_finance->where(array('uid' => $v['uid']))->getField('money');
            $datat = md5(md5($member_bii) . $keys['encrypt']);//安全加密
            $cw_encryption->where(array('uid' => $v['uid']))->setField('money_keys', $datat);
        }


    }


}

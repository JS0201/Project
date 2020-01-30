<?php

namespace Member\Addons;
class HookAddons
{

    //注册后
    public function after_register($data)
    {
        //path_id设置
        D('Member/Member')->path_id($data['register_id']);
        //生成钱包数据
        $us_data['uid'] = $data['register_id'];
        $us_data['encrypt'] = random(6);//6位随机数令牌
        M('secure_encryption')->data($us_data)->add();
        $cw_data = array(
            'uid' => $data['register_id'],
            'time' => time(),
        );
        M('money_finance')->data($cw_data)->add();
        /*生成锁仓钱包数据*/
        $money_freed = M('money_freed');
        $tdata['total'] = 0;
        $tdata['uid'] = $data['register_id'];
        $money_freed->add($tdata);
    }

    //处理双轨注册
    public function admin_level($userid)
    {

        $member = M('member');
        $sorts = M('member_sorts');
        $uid = $userid['register_id'];//新注册会员id

        $data1 = array(
            'uid' => $uid,
            'pid' => $userid['member_jd'],//接点人排位系统的主id
            'member_bigposition' => $userid['bigposition'],//1左2右
            'member_regtime' => time(),
        );
        $sorts->data($data1)->add();
        $t_id = $sorts->where(array('uid' => $uid))->find();//排位系统新注册会员的主id
        $path_id = $userid['member_path'] . $t_id['id'] . ',';//$userid['member_path']接点人排位系统的path路径
        $sorts->where(array('uid' => $uid))->setField('path_id', $path_id);//修改path路径

    }


    public function check_lock($member)
    {

        if ($member['islock'] == 1) {
            D('Member/Member', 'Service')->logout();
            exit('<script>alert("该用户已被锁定，禁止操作");location.href="' . U('member/public/login') . '"</script>');
        }
    }

    //会员等级升级
    public function member_upgrage($up_data)
    {
        $uid = $up_data['buyer_id'];//购买产品用户ID
        $money = $up_data['paid_amount'];//购买产品金额
        $gid = $up_data['kuang_type'];
        //会员升级//
        $member = M('member');
        $member_group = M('member_group');//会员等级表
        $money_finance = M('money_finance');//会员个人财务表
        $grade = $money_finance->where(array('uid' => $uid))->getField('grade'); //当前等级
        $group = $member_group->where("id=$gid")->getField("grade"); //买的产品等级

        if ($group > $grade) {
            $money_finance->where(array('uid' => $uid))->setField('grade', $group);//更新等级

        }

        $data['uid'] = $uid;
        $data['gid'] = $gid;
        $data['paid_amount'] = $money;
        $this->freed_data($data);//更改待释放表数据
        if ($group > $grade) {
            $money_finance->where(array('uid' => $uid))->setField('grade', $group);//更新等级

        }
    }


    //会员购买更改待释放表数据
    public function freed_data($freed)
    {
        $member_group = M('member_group');//会员等级表
        $uid = $freed['uid'];
        $gid = $freed['gid'];
        $paid_amount = $freed['paid_amount'];
        //  $group=$member_group->where("id=$gid")->getField("grade"); //买的产品等级
        $zincome= $member_group->where("id=$gid")->getField("zincome");
        $money=$paid_amount*$zincome;
        M('money_freed')->where(array('uid' => $uid))->setInc('total', $money);
       /* $oldmoney = M('money_freed')->where("uid=$uid")->getField("total");
        $total = $money + $oldmoney;
        M('money_freed')->where(array('uid' => $uid))->setField('total', $total);//更新待释放额度*/

    }


    //计算直推收益  找最大单释放，多出的不算
//   $status_array=array('1'=>'每日释放静态收益','2'=>'直推下单加速释放','3'=>'无限分享加速释放','4'=>'直推下单返锁仓钱包','7'=>'左小区奖励','8'=>'右小区奖励','14'=>'BOSS 团队分红','15'=>'BOSS 全网分红');

    public function dynamic_income($us_data)
    { //var_dump($us_data);
         $uid = $us_data['buyer_id'];//购买产品用户ID
        $oid=$us_data['oid'];
        $money = $us_data['paid_amount'];//购买产品金额
        $member_group = M('member_group');//会员等级参数列表
        $member = M('member');
        $money_freed = M('money_freed');
        $money_finance = M('money_finance');//会员个人财务表
        $us_pid = $member->where(array('id' => $uid))->getField('pid');//查找购买产品会员的推荐人和等级
        $u_group = $money_finance->where(array('uid' => $us_pid))->getField('grade');//查找直推会员等级
        $u_groupinfo = $money_finance->where(array('uid' => $us_pid))->find();//查找直推会员财务信息

        if ($u_group > 0 && $u_groupinfo["lock_wallet"] > 0) {

            $groupconfig = $member_group->where("id=$u_group")->find();
            //$bili = $groupconfig["zt_rate"];
            $bili =0.05;
            $m_freed = $money_freed->where(array('uid' => $us_pid))->find();
            $lock_wallet = $m_freed["total"];
            if($money*$bili>=$lock_wallet){
                $alllock_wallet=$lock_wallet ;
            }else{
                $alllock_wallet=$money*$bili;
            }
         //   $alllock_wallet = $lock_wallet * $bili;//总释放量
            $data["uid"] = $us_pid; //获取id
            $data["u_group"] = $u_group;
            $data["money"] = $money;
            $data["oid"] = $oid;
            $data["guid"] = $uid;//给与id
            $data["alllock_wallet"] = $alllock_wallet;//总释放
            $data["money_type"] = 2;//直推释放
            $this->money_pay($data); //自己分配比例进行释放

        }


    }
/*
 //直推（1）初级经纪人获直推额度8%比率比率释放；
（2）中级经纪人获直推额度10%比率比率释放；
（3）高级经纪人获直推额度12%比率释放。
所有到锁仓钱包
*/

    public function dynamic_incometolockwallet($us_data)
    {  $uid = $us_data['buyer_id'];//购买产品用户ID
        $oid=$us_data['oid'];
        $money = $us_data['paid_amount'];//购买产品金额
        $member_group = M('member_group');//会员等级参数列表
        $member = M('member');
        $money_freed = M('money_freed');
        $money_finance = M('money_finance');//会员个人财务表
        $us_pid = $member->where(array('id' => $uid))->getField('pid');//查找购买产品会员的推荐人和等级
        $u_group = $money_finance->where(array('uid' => $us_pid))->getField('grade');//查找直推会员等级
        $u_groupinfo = $money_finance->where(array('uid' => $us_pid))->find();//查找直推会员财务信息

        if ($u_group > 0) {

            $groupconfig = $member_group->where("id=$u_group")->find();
           $bili = $groupconfig["zt_rate"];
            $moneylevel=$groupconfig["money"];
           // $m_freed = $money_freed->where(array('uid' => $us_pid))->find();
           // $lock_wallet = $m_freed["total"];
          //  if($lock_wallet==0){return;}
            if($money>$moneylevel){
                $allpricemoney=$moneylevel;
            }
            else{
                $allpricemoney=$money;
            }
            $alllock_wallet = $allpricemoney * $bili;//总释放量
          //  $alllock_wallet = $lock_wallet * $bili;//总增加量
            $data["uid"] = $us_pid; //获取id
          //  $data["u_group"] = $u_group;
            $data["money"] = $money;
            $data["oid"] = $oid;
            $data["guid"] = $uid;//给与id
            $data["alllock_wallet"] = $alllock_wallet;//总增加量
            $data["money_type"] = 4;//直推下单返锁仓钱包
            $this->money_paylockwallet($data); //自己分配比例进行释放


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
    //资金释放方法
    public function money_pay($data)
    {     $oid=$data['oid'];
        $member_group = M('member_group');//会员等级参数列表
        $guid = $data["guid"];
        $uid = $data["uid"];
        $u_group = $data["u_group"];
        $money_type =  $data["money_type"];
        $groupconfig = $member_group->where("id=$u_group")->find();
        $money = $data["money"];
        $alllock_wallet=  $data["alllock_wallet"];//总释放
        if($alllock_wallet<=0){ return;}
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
        $grqb_data['types'] = '1';//累加积分
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
            if($alllock_walletupdate<=0){ continue;}
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

    //无限分享

    public function dynamic_share($us_data)
    {  //var_dump($us_data);
        $oid=$us_data['oid'];
        $uid = $us_data['buyer_id'];//购买产品用户ID
        $money = $us_data['paid_amount'];//购买产品金额
        $member_group = M('member_group');//会员等级参数列表
        $member = M('member');
        $money_freed = M('money_freed');
        $money_finance = M('money_finance');//会员个人财务表
         $us_pid = $member->where(array('id' => $uid))->getField('pid');//查找购买产品会员的推荐人和等级
        $u_group = $money_finance->where(array('uid' => $us_pid))->getField('grade');//查找直推会员等级
        $u_groupinfo = $money_finance->where(array('uid' => $us_pid))->find();//查找直推会员财务信息
        if ($u_group > 0 && $u_groupinfo["lock_wallet"] > 0) {
            $groupconfig = $member_group->where("id=$u_group")->find();
            $bili = $groupconfig["zt_rate"];
            $moneylevel= $groupconfig["money"];
        //    $m_freed = $money_freed->where(array('uid' => $us_pid))->find();
             $path_id = $member->where(array('id' => $us_pid))->getField('path_id');//查找path_id
           // $lock_wallet = $m_freed["total"];
            if($money>$moneylevel){
                $allpricemoney=$moneylevel;
            }
            else{
                $allpricemoney=$money;
            }
            $alllock_wallet = $allpricemoney * $bili;//总释放量
            if ($alllock_wallet > 0) {
                $data["uid"] = $us_pid; //获取id
                $data["u_group"] = $u_group;
                $data["money"] = $money;
                $data["guid"] = $uid;//给与id
                $data["path_id"] = $path_id;
                $data["alllock_wallet"] = $alllock_wallet;
                $data["oid"] =$oid;
                $this->dynamic_shareto($data);
            }

        }


    }

    function dynamic_shareto($data)
    { //var_dump($data);
        $prev_path= $data["path_id"];
        $pid = rtrim($prev_path, ',');//去除逗号
        $prev_path = explode(',', $pid);//组成数组。
        $oid=$data['oid'];
        rsort($prev_path);//rsort() 函数对数值数组进行降序排序
        array_pop($prev_path);
        array_shift($prev_path);
        $ids = $prev_path;
        $money=$data["money"];
        $uid=$data["guid"];
        $alllock_wallet=$data["alllock_wallet"];
        $money_finance = M('money_finance');//会员个人财务表

        foreach ($ids as $k => $v) {
            if ($v == 0) {
                break;
            }
            $grade= $money_finance->where(array('uid' => $v))->getField('grade');//查找直推会员等级
            if($grade==0){ continue;}
            $alllock_wallet= 0.5 * $alllock_wallet;
 /*      //     $users[$v]["buyer_id"] = $data["buyer_id"];
        //    $users[$v]["pid"] = $data["pid"];

            $datas["uid"] = $v; //获取id
            $datas["u_group"] = $grade;
            $datas["money"] = $money;
            $datas["guid"] = $uid;//给与id
            $datas["alllock_wallet"] = $alllock_wallet;//总量
            $datas["money_type"] = 3;//分享释放
            $datas["oid"] =$oid;
            $this->money_pay($datas); //自己分配比例进行释放*/
            $data["uid"] = $v; //获取id
            $data["u_group"] = $grade;
            $data["money"] = $money;
            $data["oid"] = $oid;
            $data["guid"] = $uid;//给与id
            $data["alllock_wallet"] = $alllock_wallet;//总增加量
            $data["money_type"] = 3;//无限分享到锁仓钱包
            $this->money_paylockwallet($data); //自己分配比例进行释放
            if($alllock_wallet<10){
                break;
            }
        }
        // set_money($users, $typemsg, $msg);


    }

    //循环用户表里面的path_id路径
    public function user_path($user_data)
    {
        $uid = $user_data['buyer_id'];//购买产品用户ID
        $money = $user_data['paid_amount'];//购买产品金额
        $member_sorts = M('member_sorts');
        $pid = $member_sorts->where(array('uid' => $uid))->getField('path_id');//查找用用path路径
        $pid = rtrim($pid, ',');//去除逗号
        $prev_path = explode(',', $pid);//组成数组
        rsort($prev_path);//krsort() 根据键，以降序对关联数组进行排序

        $td_arr['money'] = $money;//当前购买金额
        $td_arr['id'] = $uid;//当前购买会员id
        $td_arr['path'] = $prev_path;
        $this->team_reward($td_arr);//调用处理团队奖励方法


        $path = array_splice($prev_path, 1);//剔除自己
        foreach ($path as $k => $v) {
            if ($v > 1) {
                $td_arr['money'] = $money;//当前购买金额
                $td_arr['id'] = $uid;//当前购买会员id
                $td_arr['pid'] = $v;//当前购买会员的pid
                $this->bossteam_profit($td_arr);
            }

        }

    }

public function upgrage_tuandui($data){

    $MemberSort = A('Public');// var_dump($wallet);
    $MemberSort->upgrage_tuandui($data);
}
    //查看左右区业绩，并产生小区奖励

    public function tuanduiprice($data){
        $MemberSort = A('Public');// var_dump($wallet);
        $MemberSort->tuanduiprice($data);
    }
    //查看左右区业绩，并产生小区奖励，

    public function team_reward($us_data)
    {

        $money = $us_data['money'];//购买产品金额
        $path = $us_data['path'];
        $member_sorts = M('member_sorts');//双轨记录表
        $money_finance = M('money_finance');//会员个人财务表
        foreach ($path as $k => $v) {
            if ($k == 0) {
                continue;
            } else {
                $user_wei = $member_sorts->where(array('id' => $path[$k - 1]))->getField('member_bigposition');   //查找购买用户的左右区
                $j_id = $member_sorts->where(array('id' => $path[$k]))->find();//查找接点人id和左右区 4
                $uid_group = $money_finance->where(array('uid' => $j_id['uid']))->getField('grade');//查找pid会员等级
                if ($uid_group > 0) {

                    if ($user_wei == 1) {
                        $member_sorts->where(array('id' => $path[$k]))->setInc('member_lposition', $money);//给pid累积左区业绩
                    } elseif ($user_wei == 2) {
                        $member_sorts->where(array('id' => $path[$k]))->setInc('member_rposition', $money);//给pid累积右区业绩
                    }
                    $region_yj = $member_sorts->where(array('id' => $path[$k]))->find();//查询左右区业绩
                    //判断左区  大于  右区
                    if ($region_yj['member_lposition'] >= $region_yj['member_rposition']) {
                        //右区是小区
                        if ($user_wei == $j_id["member_bigposition"]) {
                            //boss团队升级
                            $report = M('report');//会员等级表
                            $report_group = M('report')->select();
                            $minbi = $report->min('money');    //求出最大个数的最小值
                            $grade = $money_finance->where(array('uid' => $j_id['uid']))->getField('agency_level');   //团队等级
                            $v_member = $member_sorts->where(array('uid' => $j_id['uid']))->find();//会员的财务信息
                            $v_zong = $v_member['member_rposition'];  //加左右区业绩
                            if ($v_zong < $minbi) {
                                if ($grade != 0) {
                                    $money_finance->where(array('uid' => $j_id['uid']))->setField('agency_level', '0');
                                }
                            } else {
                                foreach ($report_group as $kk => $vv) {
                                    if ($v_zong >= $vv['money']) {
                                        if ($grade < $vv['dj_id']) {
                                            $money_finance->where(array('uid' => $j_id['uid']))->setField('agency_level', $vv['dj_id']);
                                            break;
                                        }
                                    }
                                }
                            }

                        }

                    } else {
                        //左区是小区

                        if ($user_wei == $j_id["member_bigposition"]) {
                            //boss团队升级
                            $report = M('report');//会员等级表
                            $report_group = M('report')->select();
                            $minbi = $report->min('money');    //求出最大个数的最小值
                            $grade = $money_finance->where(array('uid' => $j_id['uid']))->getField('agency_level');   //团队等级
                            $v_member = $member_sorts->where(array('uid' => $j_id['uid']))->find();//会员的财务信息
                            $v_zong = $v_member['member_lposition'];  //加左右区业绩
                            if ($v_zong < $minbi) {
                                if ($grade != 0) {
                                    $money_finance->where(array('uid' => $j_id['uid']))->setField('agency_level', '0');
                                }
                            } else {
                                foreach ($report_group as $kk => $vv) {
                                    if ($v_zong >= $vv['money']) {
                                        if ($grade < $vv['dj_id']) {
                                            $money_finance->where(array('uid' => $j_id['uid']))->setField('agency_level', $vv['dj_id']);

                                            break;
                                        }
                                    }
                                }
                            }

                        }


                    }

                }

            }
        }


    }

    /**
     * [执行用户冻结释放的方法]
     */
    public function freed_ad()
    {

//        if($_GET['mid']!=IEdcdeRDDw4d2uy4e7h){
//            //exit('<script>alert("禁止操作");</script>');
//        }
        $vsc_price = M('tixian_setting')->where(array('id' => 1))->getField('vsc_price');
        $m_freed = M('money_freed')->where(array('status' => '0'))->order('id asc')->select();
        if ($m_freed) {
            foreach ($m_freed as $k => $v) {
                if ($v['total_day'] > 0 && $v['sheng_amount'] > 0) {
                    $date1 = date_create(date("Y-m-d", $v['generated_time']));//数据保存的更新时间
                    $date2 = date_create(date("Y-m-d", time()));//当前时间
                    $diff = date_diff($date1, $date2);//日期相减
                    $tian = $diff->format("%a");//得到的天数
                    if ($tian >= $v['total_days']) {//当前的天数 大于 数据库天数

                        $days1 = date_create(date("Y-m-d", $v['update_time']));//数据保存的更新时间
                        $days2 = date_create(date("Y-m-d", time()));//当前时间
                        $dt = date_diff($days1, $days2);//日期相减
                        $day = $dt->format("%a");//得到的天数
                        //  if ($day >= $v['each_release']) {//判断是否超过一天

                        if ($v['sheng_amount'] < $v['release_amount']) {  //如果剩余个数 < 每天释放个数
                            $day_income = $v['sheng_amount'];
                            $income = sprintf("%.4f", $v['sheng_amount'] / $vsc_price);  //日产出/vsc单价=vsc个数
                        } else {
                            $day_income = $v['release_amount'];
                            $income = sprintf("%.4f", $v['release_amount'] / $vsc_price);  //日产出/vsc单价=vsc个数
                        }
                        //执行个人钱包操作动作
                        $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
                        $grqb_data['key_types'] = 'currency';//购物积分加密字段
                        $grqb_data['uid'] = $v['uid'];//获取积分用户ID
                        $grqb_data['types'] = '2';//2累加积分
                        $grqb_data['number'] = $income;//本次产生的积分数量
                        $grqb_data['text'] = '静态VSC收益释放时，系统发现会员VSC数据异常，停止给此会员执行累加VSC动作！';
                        $yes_no = personal_wallet($grqb_data);//调用个人钱包方法

                        if ($yes_no) {
                            M('money_freed')->where(array('id' => $v['id']))->setDec('total_day', '1');//减去释放天数
                            M('money_freed')->where(array('id' => $v['id']))->setDec('sheng_amount', $day_income);//减每天释放的个数
                            M('money_freed')->where(array('id' => $v['id']))->setField('update_time', time());//修改时间

                        }
                        $day_ye = M('money_freed')->where(array('id' => $v['id']))->getField('total_day');//查询剩余天数
                        if ($day_ye == 0) {
                            M('money_freed')->where(array('id' => $v['id']))->setField('status', '1');//修改状态为1，无效
                        }


                        //获取静态释放收益
                        $bdata['member_getid'] = $v['uid'];//出售币获得金额 会员id
                        $bdata['member_giveid'] = $v['uid'];//购买币会员id
                        $bdata['money_produtime'] = time();
                        $bdata['money_nums'] = $income;//当次交易金额
                        $bdata['money_type'] = 1;// 静态释放收益
                        M('money_types')->add($bdata);//写入收益详情表

                        //  }

                    }
                } else {
                    M('money_freed')->where(array('id' => $v['id']))->setField('total_day', '0');//修改释放天数
                    M('money_freed')->where(array('id' => $v['id']))->setField('status', '1');//修改状态为1，无效
                }

            }
        }

        die('ok');

    }


    //boss团队分红极差
    public function bossteam_profit($boss)
    {
        $uid = $boss['id'];//购买产品用户ID
        $pid = $boss['pid'];//排位表获取业绩会员id
        $money = $boss['money'];//购买产品金额
        $report = M('report');//团队分红参数列表
        $member = M('member');//会员信息表
        $money_finance = M('money_finance');//会员个人财务表
        $uid_agency = $money_finance->where(array('uid' => $pid))->getField('agency_level');//查找pid会员团队等级
        if ($uid_agency > 0) {
            $ratio = $report->where(array("dj_id" => $uid_agency))->getField('ratio');
            $income = $money * $ratio;   //购买金额 * 等级百分比
            //执行个人钱包操作动作
            $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
            $grqb_data['key_types'] = 'currency';//购物积分加密字段
            $grqb_data['uid'] = $pid;//获取积分用户ID
            $grqb_data['types'] = '2';//2累加积分
            $grqb_data['number'] = $income;//本次产生的积分数量
            $grqb_data['text'] = '静态收益释放时，系统发现会员子币数据异常，停止给此会员执行累加子币动作！';
            $yes_no = personal_wallet($grqb_data);//调用个人钱包方法
            if ($yes_no) {
                //获取静态释放收益
                $bdata['member_getid'] = $pid;//出售币获得金额 会员id
                $bdata['member_giveid'] = $uid;//购买币会员id
                $bdata['money_produtime'] = time();
                $bdata['money_nums'] = $income;//当次交易金额
                $bdata['money_type'] = 14;// 团队新增收益分红
                M('money_types')->add($bdata);//写入收益详情表

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
            $grqb_data['zh_types'] = 'member_z_bi';//购物积分字段
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

    }


}

?>
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\hook;
use app\member\service\MoneyFinance;
class Hook
{

    public function registerInit(&$params){
        //注册开关
        $reg_allow=config('cache.reg_allow');

        if(empty($reg_allow)){
            $params['reg_status']=0;
            $params['reg_error']='注册暂未开放';
        }

        //注册上限
        $member_reg_num = config('cache.member_reg_num');

        if($member_reg_num!=''){

            $member_reg_num=(int) $member_reg_num;

            $day_reg_count=db('member')->where(['register_time'=>['between',[strtotime(date('Y-m-d')),time()]]])->count(1);
            if($day_reg_count>=$member_reg_num){

                $params['reg_status']=0;
                $params['reg_error']='今天注册人数已上限了';
            }

        }
    }

    public function memberInit($member){
        //单点登录
        $member_one_login=config('cache.member_one_login');
        if($member_one_login){

            $authkey = cookie('gboy_member_auth');
            if ($authkey) {
                list($uid, $rand, $login_key) = explode("\t", authcode($authkey));
                if($member['login_key']!=$login_key){
                    model('member/Member','service')->logout();
                    exit('<script>alert("您的账户在另一地登录，您被迫下线");location.href="'.url('member/publics/login').'";</script>');
                }
            }
        }
    }


    public function afterRegister($reg_data){


        if($reg_data['pid'] && is_int($reg_data['pid'])){

            $top_path_id=db('member')->where(['id'=>$reg_data['pid']])->value('path_id');

            $path_id=sprintf('%s%s,',$top_path_id,$reg_data['reg_id']);

        }else{
            $path_id=sprintf('0,%s,',$reg_data['reg_id']);
        }

        db('member')->where(['id'=>$reg_data['reg_id']])->setField('path_id',$path_id);
    }

    public function checkCart($buyer_id)
    {
        $all_item = json_decode(cookie('cart_recharge'), TRUE);
        if($all_item) {
            $model = model('order/Cart');
            foreach($all_item as $k => $v) {
                unset($all_item[$k]['id']);
                $all_item[$k]['buyer_id'] = $buyer_id;
                $all_item[$k]['type'] = 1;
            }
            $model->insertAll($all_item);
            cookie('cart_recharge', NULL);
        }
        $all_item = json_decode(cookie('cart_consume'), TRUE);
        if($all_item) {
            $model = model('order/Cart');
            foreach($all_item as $k => $v) {
                unset($all_item[$k]['id']);
                $all_item[$k]['buyer_id'] = $buyer_id;
                $all_item[$k]['type'] = 3;
            }
            $model->insertAll($all_item);
            cookie('cart_consume', NULL);
        }
    }
    //找到推荐人ID  判断他直推中级代码有没有20人  如果有再判断是否为高级代理 如果不是就升为高级。 如果不符合条件再循环第二条。
    public function promoteGrade ($uid)
    {
        if($uid) {
            $pid = db("member")->where("id = {$uid}")->value('pid'); //推荐人ID
            $userArr = db("member")->where("id = {$pid}")->field("id,group_id,isreturn,deposit")->find();
            $group=db('member_group')->where("grade > 1")->order('id desc')->select();
            $deposit = config('configs.deposit_return');
            $finance_service = new MoneyFinance();
            foreach($group as $k => $v) {
                $group_i = $v['id'] - 1;
                $num = db('member')->where("pid = {$pid} and group_id >= {$group_i}")->count();
                if($num >= $v['description']) { //人数达标
                    if(!$userArr['isreturn'] && $userArr['group_id'] == $v['id'] && $userArr['deposit'] >= $deposit[$userArr['group_id']][1]) {
                        $finance_service->setFinance($userArr['id'], $deposit[$userArr['group_id']][2], 'shop_integral', '押金退还','', true, 4, $uid);
                        db('member')->where("id = {$pid}")->update(array('isreturn' => 1, 'deposit'=> 0));
                    }
                    if($userArr['group_id'] > 1 && $userArr['group_id'] < $v['id']) {
                        db('member')->where("id = {$pid}")->update(array('group_id' => $v['id']));
                        $this->promoteGrade($userArr['id']);
                        break;
                    }
                }
            }
        }
    }

    //循环用户表里面的path_id路径
    public function userPath($uid){
        $path_id = db('member')->where(array('id'=>$uid))->value('path_id');
        $path_uid = rtrim($path_id, ',');//去除逗号
        $prev_path = explode(',', $path_uid);//组成数组
        rsort($prev_path);//rsort() 函数对数值数组进行降序排序
        $prev_path=array_splice($prev_path,1); //去除自己的ID

        //获取配置表
        $group_config=db('member_group')->where("grade > 1")->order('id desc')->select();
        $money = array();
        foreach($group_config as $kk => $vv) {
            $money[$vv['id']] = $vv['discount'];  // 等级对应积分
        }
        $linshi = array('level' => 3, 'receive' => 0);  // level 等级  receive 已领取积分
        $finance_service = new MoneyFinance();
        foreach ($prev_path as $k => $v) {
            $pid_group=db('member')->where(array('id'=>$v))->value('group_id');//查出上级会员等级
            if ($v > 0 && $pid_group >= $linshi['level']) {
                $finance_service->setFinance($v, $money[$pid_group] - $linshi['receive'], 'shop_integral', '代理补助','', true, 3, $uid);
                $linshi['level'] = $pid_group;
                $linshi['receive'] = $money[$pid_group];
            }
        }
    }

}
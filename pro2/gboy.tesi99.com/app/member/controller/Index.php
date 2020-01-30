<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\controller;
use app\member\service\Address;
use app\member\service\Order;
use app\member\service\MoneyFinance;
use app\member\service\Member;
use app\member\service\Profile;
use app\member\service\Rechange;
use app\member\service\Withdraw;
class Index extends Check{

    var $uid;
    public function _initialize()
    {
        parent::_initialize();
        if(!cookie('gboy_member_auth')) {
            $this->redirect('/member/publics/login');
        }
        $temp = explode("\t",authcode(cookie('gboy_member_auth')));
        $this->uid = $temp[0];
    }

    //个人首页
    public function order()
    {
        header("Cache-control:no-cache,no-store,must-revalidate");
        header("Pragma:no-cache");
        header("Expires:0");
        $service = new MoneyFinance();
        $info = $service->getByUid($this->uid);
        $this->assign('info', $info);
        return $this->fetch();
    }
    //刷新积分
    public function reset()
    {
        $financeServer = new MoneyFinance();
        $uid = $this->uid;
        $result = $financeServer->getByUid($uid);
        if($result) {
            showmessage(lang('_operation_success_'), '', 1, $result);
        } else {
            showmessage(lang('_operation_fail_'));
        }
    }

    //用户订单列表
    public function allorder()
    {
        $uid = $this->uid;
        $orderServer = new Order();
        $list = $orderServer->getList($uid);
        $this->assign('orderList', $list);
        return $this->fetch();
    }

    //现金积分
    public function xjcz()
    {
        header("Cache-control:no-cache,no-store,must-revalidate");
        header("Pragma:no-cache");
        header("Expires:0");
        if(is_post()) {
            $money = input('post.money');
            $img_re = upload_img($_FILES);
            if(!$img_re['status']) {
                showmessage($img_re['msg']);
            }
            $dir = ROOT_PATH.'public/uploads/'.date('Ymd');
            if (!file_exists($dir)){
                mkdir($dir, 0777);
            }
            $file_name = "uploads/".date('Ymd')."/". uniqid() . '.png';
            move_uploaded_file($_FILES["file"]["tmp_name"], ROOT_PATH.'public/'.$file_name);
            $service = new Rechange();
            $id = $service->add(array(
                'uid' => $this->uid,
                'money' => $money,
                'accessory' => '/'.$file_name,
                'status' => 1,
                'created' => time()
            ));
            if(!$id) {
                showmessage(lang('_operation_fail_'));
            }
            showmessage(lang('_operation_success_'), '', 1);
        }else{
            $model = model('admin/Site');
            $result = array(
                'account_bank' => $model->where(array('key' => 'account_bank'))->value('value'),
                'account_name' => $model->where(array('key' => 'account_name'))->value('value'),
                'bank_account' => $model->where(array('key' => 'bank_account'))->value('value')
            );
            $this->assign('bank', $result);
            return $this->fetch();
        }
    }
    public function transaction()
    {
        header("Cache-control:no-cache,no-store,must-revalidate");
        header("Pragma:no-cache");
        header("Expires:0");
        if(is_post()) {
            $service = new MoneyFinance();
            $result = $service->transaction($this->uid, input('post.'));
            if(!$result) {
                showmessage($service->errors);
            }
            showmessage(lang('_operation_success_'), '', 1);
        }else{
            $this->assign('user', array('realname' => $this->member['realname'], 'money' => $this->member['finance_money']));
            return $this->fetch();
        }
    }

    //充值明细
    public function czmx()
    {
        $service = new Member();
        $this->assign('list', $service->rechargeDetail($this->uid));
        return $this->fetch();
    }
    //提现 申请提现
    public function withdraw()
    {
        header("Cache-control:no-cache,no-store,must-revalidate");
        header("Pragma:no-cache");
        header("Expires:0");
        if(is_post()){
            $service = new Withdraw();
            $re = $service->apply($this->uid, input('post.money'));
            if(!$re) {
                showmessage($service->errors);
            }
            showmessage(lang('_operation_success_'), '',1);
        }else{
            $info = array();
            $finance_service = new MoneyFinance();
            $profile_service = new Profile();
            $info = $profile_service->getOne("uid = {$this->uid}");
            if(!$info || !$info['account_name'] || !$info['account_bank'] || !$info['bank_account']) {
                $this->assign('url', '/member/index/userdata');
            }
            $info['money'] = $finance_service->getBi($this->uid, 'shop_integral');
            $this->assign('info', $info);
            return $this->fetch();
        }
    }
    //提现明细
    public function withdrawdetail()
    {
        $service = new Withdraw();
        $this->assign('list', $service->detail($this->uid));
        return $this->fetch();
    }
    //收益详情
    public function tradedetail()
    {
        $service = new Member();
        $limit = 10; //默认
        $start = input('post.page') ? (input('post.page') - 1) * $limit : 0;
        $list = $service->tradedetail($this->uid, $start, $limit);
        $this->assign('list', $list);
        return $this->fetch();
    }

    //个人资料
    public function userdata()
    {
        $service = new Profile();
        if(is_post()) {
            $re = $service->reset($this->uid, input('post.'));
            if($re !== false) {
                showmessage(lang('_operation_success_'), '',1);
            }else{
                $error = $service->errors ? $service->errors : lang('_operation_fail_');
                showmessage($error);
            }
        }else{
            $list = $service->getOne(array('uid'=>$this->uid));
            $this->assign('list', $list);
            $this->assign('user', $this->member);
            return $this->fetch();
        }

    }
    //收货地址
    public function address()
    {
        header("Cache-control:no-cache,no-store,must-revalidate");
        header("Pragma:no-cache");
        header("Expires:0");

        $addressService = new Address();
        $list = $addressService->getList();
        $this->assign('list', $list);
        return $this->fetch();
    }
    //添加编辑收货地址
    public function addaddress()
    {
        $addressService = new Address();
        if(is_post()) {
            $id = input('post.id');
            if($id && intval($id) == $id) {
                if(!$addressService->edit($id, $this->uid, input('post.'))) {
                    showmessage($addressService->errors);
                }
                showmessage(lang('_operation_success_'), '',1);
            }else{
                if(!$addressService->add($this->uid, input('post.'))) {
                    showmessage($addressService->errors);
                }
                showmessage(lang('_operation_success_'), '',1);
            }
        }
        $id = input('get.id');
        $info = array();
        if($id && intval($id) == $id) { //编辑
            $info = $addressService->getOne("id = {$id}");
            if($info) {
                $info['place'] = $info['province'].' '.$info['city'].' '.$info['county'];
            }
        }

        $this->assign('info', $info);
        return $this->fetch();
    }
    public function setdefault()
    {
        $addressService = new Address();
        if(!$addressService->setDefault($this->uid ,input('post.'))) {
            showmessage($addressService->errors);
        }
        showmessage(lang('_operation_success_'), '',1);
    }
    //删除地址
    public function deladdress()
    {
        $addressService = new Address();
        if(!$addressService->del($this->uid, input('post.id'))) {
            showmessage($addressService->errors);
        }
        showmessage(lang('_operation_success_'), '',1);
    }

    //安全设置（修改密码）
    public function resetpw()
    {
        if(is_post()) {
            $service = new Member();
            if($service->resetpw($this->uid, input('post.'))) {
                showmessage(lang('_operation_success_'), '',1);
            }
            showmessage($service->errors);
        }else{
            return $this->fetch();
        }
    }
    //推广中心
    public function share()
    {
        return $this->fetch();
    }
    public function getcode()
    {
        $return = array('status' => false, 'msg' => '');
        $member_server = new Member();
        $code = $member_server ->get_find(array('id' => $this->uid), 'img_code')->toArray();
        if($code['img_code']) {
            $this->assign('code', $code['img_code']);
            $return = array('status'=>true, 'msg' => $code['img_code']);
        }else{
            $url = 'http://'.$_SERVER['HTTP_HOST'].'/member/publics/register?mobile='.$this->member['mobile'];
            $file_url="static/code/".$this->uid.'.png';
            $img_url = phpqrcode($url,$file_url);
            if(is_file($file_url)) {
                $member_server->updated(array('id' => $this->uid), array('img_code' => '/'.$img_url));
                $return = array('status'=>true, 'msg' => '/'.$img_url);
            }
        }
        echo json_encode($return);
    }
    //我的团队
    public function team()
    {
        $service = new Member();
        if(is_post()) {
            $id = input('post.id');
            $uid = input('post.ids') ? input('post.ids') : $this->uid;
            echo json_encode($service->getTeam($id, $uid));
            exit;
        }
        $this->assign('num', $service->getCount($this->uid));
        return $this->fetch();
    }

}
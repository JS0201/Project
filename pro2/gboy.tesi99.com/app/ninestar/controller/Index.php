<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5
 * Time: 10:32
 */
namespace app\ninestar\controller;
use app\member\controller\Check;
use app\ninestar\service\User;
class Index extends Check{
    public $user_server;
    public $arr = array('0','一','二','三','四','五','六','七','八','九');
    public function __construct()
    {
        parent::__construct();
        $this->user_server = new User();
        $isregister = $this->user_server->isregister($this->member['username']);
        if(!$isregister) {
            if(is_post()) {
                showmessage($this->user_server->errors);
            }else{
                echo "<script>alert('".$this->user_server->errors."');window.location.href = '/member/index/order'</script>";
            }
        }else if($isregister && $_POST['type'] == 'into') {
            showmessage('', '', 1);
        }
    }
    //首页
    public function index() {
       $user = $this->user_server->getUser($this->member['id']);
       $level = $this->arr[$user['level']];
        if($user['level'] == 9) {
            $next = '';
        }else{
            $next = $this->arr[$user['level'] + 1].'星会员';
        }
        $isup = 3;
        if($user['level'] == 1) {
            $isup = $this->user_server->checkSum($this->member['id']);
        }
        $this->assign('isup', $isup);
        $this->assign('level', $level);
        $this->assign('next', $next);
        $this->assign('face', $user['face']);
        $this->assign('wechat', $user['wechat']);
       return $this->fetch();
    }


    //激活用户
    public function register() {
        if(is_post()) {
            if(!$this->user_server->register(input('post.'), $this->member['id'])){
                showmessage($this->user_server->errors);
            }
            showmessage('', '', 1);
        }
        $user = $this->user_server->getUser($this->member['id']);
        $level = $this->arr[$user['level']];
        if($user['level'] == 9) {
            $next = '';
        }else{
            $next = $this->arr[$user['level'] + 1];
        }
        $list = $this->user_server->getRegisterList($this->member['id']);
        $this->assign('list', $list);
        $this->assign('level', $level);
        $this->assign('next', $next);
        $this->assign('face', $user['face']);
        return $this->fetch();
    }
    //申请升级
    public function apply() {
        if(is_post()) {
            if(!$this->user_server->apply($this->member['id'])){
                showmessage($this->user_server->errors);
            }
            showmessage('', '', 1);
        }
        $user = $this->user_server->getUser($this->member['id']);
        $level = $this->arr[$user['level']];
        if($user['level'] == 9) {
            $next = '';
        }else{
            $next = $this->arr[$user['level'] + 1].'星会员';
        }
        $list = $this->user_server->applyList($this->member['id']);
        $this->assign('list', $list);
        $this->assign('next', $next);
        $this->assign('level', $level);
        return $this->fetch();
    }
    //审核
    public function examine() {
        if(is_post()) {
            if(!$this->user_server->examine(input('post.uid'), $this->member['id'])) {
                showmessage($this->user_server->errors);
            }
            showmessage('', '', 1);
        }
        $list = $this->user_server->examineList($this->member['id']);
        $this->assign('list', $list);
        return $this->fetch();
    }
    //我的团队
    public function team() {
        $list = $this->user_server->teamList($this->member['id']);
        $this->assign('list', $list);

        $count = $this->user_server->getCount($this->member['id']);
        $count = $count ? $count : 0;
        $this->assign('count', $count);
        return $this->fetch();
    }

}
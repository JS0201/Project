<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5
 * Time: 17:05
 */
namespace app\ninestar\service;
use app\ninestar\model\Init;
class User extends Init{
    public $arr = array('0','一','二','三','四','五','六','七','八','九');
    public function isregister($username) {
        if(!$username) {
            $this->errors = '异常请求';
            return false;
        }
        $re = $this->db->table('star_user')->where("phone = '{$username}'")->value('id');
        if(!$re) {
            $this->errors = '未激活用户';
            return false;
        }
        return true;
    }
    //注册
    public function register($data,$uid) {
        if(!$data || !$data['phone']) {
            $this->errors = '账号不能为空';
            return false;
        }
        if(!is_mobile($data['phone'])) {
            $this->errors = '请输入正确的手机号';
            return false;
        }
        $member_model = model('member/member');
        $user = $member_model->where("username = '{$data['phone']}'")->find();
        if(!$user) {
            $this->errors = '用户不存在';
            return false;
        }
        if($user['pid'] != $uid){
            $this->errors = '非推荐关系不可激活';
            return false;
        }
        $is_regist = $this->db->table('star_user')->where("phone = '{$data['phone']}'")->value('id');
        if($is_regist) {
            $this->errors = '该用户已激活';
            return false;
        }
        $member_model->startTrans();
        $reslut =$this->db->table('star_user')->data([
            'id'      => $user['id'],
            'pid'     => $user['pid'],
            'path_id' => $user['path_id'],
            'phone'   => $data['phone'],
            'created' => time()
        ])->insert();
        $reslut2 = $this->db->table('star_activate_log')->data([
            'fid'     => $uid,
            'tid'     => $user['id'],
            'phone'   => $data['phone'],
            'created' => time()
        ])->insert();
        if(!$reslut || !$reslut2) {
            $member_model->rollback();
            $this->errors = '激活失败';
            return false;
        }
        $member_model->commit();
        return true;
    }
    //查询满足等级条件的上级用户
    public function getTopUser($path_id, $level, $type) {
        $path_data = explode(',',trim($path_id,','));
        rsort($path_data);
        $userid = '';
        $arr = $this->arrchange();
        foreach($path_data as $k => $v) {
            if($k == 0) {
                continue;
            }
            $star_user  = $this->db->table('star_user')->where("id = {$v} and level >= {$level}")->find();
            if($star_user['id']) {
                // && ( || )
                if($type == 1 && $star_user['ordersum'] < $arr[$star_user['level']['ordersum']]) { // 直接给上级
                    $userid = $star_user['id'];
                    break;
                }else if($type == 2 && $star_user['fiveorder'] < $arr[$star_user['level']]['fiveorder']) { //一星给五星
                    $userid = $star_user['id'];
                    break;
                }else if($type == 3 && $star_user['nineorder'] < $arr[$star_user['level']]['nineorder']) { // 五星级九星
                    $userid = $star_user['id'];
                    break;
                }
            }
        }
        return $userid;
    }
    //组装配置数组
    public function arrchange() {
        $list = $this->db->table('star_config')->where('id > 0')->select();
        $result = array();
        if($list) {
            foreach($list as $k => $v) {
                $result[$v['level']]['ordersum'] = $v['ordersumt'];
                $result[$v['level']]['fiveorder'] = $v['fiveorder'];
                $result[$v['level']]['nineorder'] = $v['nineorder'];
            }
        }
        return $result;
    }

    // 针对一星会员。检测升级资格
    public function checkSum($uid) {
        $count = $this->db->table('star_user')->where("pid = {$uid} and level = 1")->count();
        $count = $count ? $count : 0;
        return $count;
    }


    //申请升级
    public function apply($uid) {
        $wechat = model('member/memberProfile')->where("uid = {$uid}")->value('wechat');
        if(!$wechat) {
            $this->errors = '请完善微信信息';
            return false;
        }
        $id = $this->db->table('star_apply_log')->where("uid = {$uid} and status = 0")->value('id');
        if($id) {
            $this->errors = '请勿重复申请';
            return false;
        }
        //判断用户等级
        $user = $this->getUser($uid);
        if($user['level'] == 9) {
            $this->errors = '您已满级';
            return false;
        }
        if($user['level'] == 0) { //一级用户只需要给五星用户即可
            $type = 2;
            $level = 5;
            $userid = $this->getTopUser($user['path_id'], $level, $type);
        }else if($user['level'] == 5){ //五级用户需要给九星用户
            $type = 3;
            $level = 9;
            $userid = $this->getTopUser($user['path_id'], $level, $type);
        }else{  //普通用户直接找上级即可
            $type = 1;
            $level = $user['level'] + 1;
            $userid = $this->getTopUser($user['path_id'], $level, $type);
        }
        if(!$userid) {
            $this->errors = "未找到".$level."星级用户";
            return false;
        }
        $re = $this->db->table('star_apply_log')->data([
            'uid' => $uid,
            'level' => $user['level'],
            'leaderid' => $userid,
            'status' => 0,
            'type' => $type,
            'created' => time()
        ])->insert();
        if(!$re) {
            $this->errors = '申请失败';
            return false;
        }
        return true;
    }

    //获取升级申请记录
    public function applyList($uid) {
        $list = $this->db->table('star_apply_log')->where("uid = {$uid}")->select();
        if($list) {
            $result = array();
            foreach($list as $k => $v) {
                $leader = $this->getUser($v['leaderid']);
                $result[$v['level']]['list'][] = [
                    'wechat' => $leader['wechat'],
                    'realname' => $leader['realname'],
                    'phone' => $leader['phone'],
                ];
                if(!isset($result[$v['level']]['level'])) {
                    $result[$v['level']]['level'] = $this->arr[$v['level']];
                }
            }
        }
        return $result;
    }
    //审核列表
    public function examineList($uid) {
        $list = $this->db->table('star_apply_log')->where("leaderid = {$uid} and status = 0")->select();
        if($list) {
            $result = array();
            foreach($list as $k => $v) {
                $user = $this->getUser($v['uid']);
                $result[$k]['uid'] = $v['uid'];
                $result[$k]['realname'] = $user['realname'];
                $result[$k]['phone'] = $user['phone'];
                $result[$k]['wechat'] = $user['wechat'];
                $result[$k]['created'] = date('Y-m-d H:i:s', $v['created']);
            }
        }
        return $result;
    }

    //审核
    public function examine($uid, $leaderid) {
        $log_re = $level_re = $sum_re = false;
        $arr = array('1'=>'ordersum','2'=>'fiveorder','3'=>'nineorder');
        $leader = $this->getUser($leaderid);
        $user = $this->getUser($uid);
        $this->db->transaction();
        $log = $this->db->table('star_apply_log')->where("uid = {$uid} and leaderid = {$leaderid} and status = 0")->find();
        $log_re = $this->db->table('star_apply_log')->where("uid = {$uid} and leaderid = {$leaderid} and status = 0")->data(['status' => 1,'utime'=>time()])->update();
        if($log_re !== false) {
            $level_re = $this->db->table('star_user')->where("id = {$uid}")->data(['level' => $user['level'] + 1])->update();
            $sum_re = $this->db->table('star_user')->where("id = {$leaderid}")->data(["{$arr[$log['type']]}" => $leader[$arr[$log['type']]] + 1])->update();
        }
        if(!$log_re || !$level_re|| !$sum_re) {
            $this->db->rollback();
            $this->errors = '审核失败';
            return false;
        }
        $this->db->commit();
        return true;
    }

    //获取激活记录
    public function getRegisterList($uid) {
        $list = $this->db->table('star_activate_log')->where("fid = {$uid}")->select();
        if($list) {
            foreach($list as $k => $v) {
                $list[$k]['created'] = date('Y-m-d H:i:s', $v['created']);
            }
        }
        return $list;
    }


    //获取用户数据
    public function getUser($uid) {
        $user = $this->db->table('star_user')->where("id = {$uid}")->find();
        if($user) {
            $data = model('member/member')->where("id = {$uid}")->find();
            $wechat = model('member/memberProfile')->where("uid = {$uid}")->value('wechat');
            $user['face'] = $data['face'] ? $data['face'] : "__ROOT__/template/wap/ninestar/static/img/user.jpg";
            $user['realname'] = $data['realname'] ? $data['realname'] : $data['username'];
            $user['wechat'] = $wechat;
        }
        return $user;
    }

    public function teamList($uid) {
        $list = $this->db->table('star_user')->where("pid = {$uid}")->select();
        if($list) {
            $result = array();
            foreach($list as $k => $v) {
                if($v['level'] == 0) {
                    $result[$k]['level'] = '普通会员';
                }else{
                    $result[$k]['level'] = $this->arr[$v['level']].'级会员';
                }
                $user = $this->getUser($v['id']);
                $result[$k]['realname'] = $user['realname'];
                $result[$k]['wechat'] = $user['wechat'];
                $result[$k]['created'] = date('Y-m-d H:i:s', $v['created']);
            }
        }
        return $result;
    }

    public function getCount($uid) {
        return $this->db->table('star_user')->where("pid = {$uid} and level >= 1")->count();
    }
}
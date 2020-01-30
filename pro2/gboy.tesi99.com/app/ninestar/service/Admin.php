<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/12
 * Time: 14:47
 */
namespace app\ninestar\service;
use app\ninestar\model\Init;

class Admin extends Init{

    public function userlist($sqlmap='',$order='id asc',$page=[]) {
        $all = $this->db->table('star_user')->field('id,phone,pid,level,created')->where($sqlmap)->order($order)->paginate($page);
        $all->each(function($item,$key) {
            $phone = 0;
            if($item['pid']) {
                $phone = $this->db->table('star_user')->where("id = {$item['pid']}")->value('phone');
            }
            $item['count'] = $this->db->table('star_apply_log')->where("leaderid = {$item['id']}")->count();
            $item['parent'] = $phone;
            $item['created'] = date('Y-m-d H:i:s', $item['created']);
            return $item;
        });
        return $all;
    }

    public function examinelist($sqlmap='',$order='id asc',$page=[]) {
        $list = $this->db->table('star_apply_log')->where($sqlmap)->order($order)->paginate($page);
        $list->each(function($item,$key) {
            $level = $item['level'] + 1;
            $item['level'] = $item['level'].'星->'.$level.'星';
            $item['status'] = $item['status'] == 0 ? '未审核' : '已审核';
            $item['phone'] = $this->db->table('star_user')->where("id = {$item['uid']}")->value('phone');
            $item['parent'] = $this->db->table('star_user')->where("id = {$item['leaderid']}")->value('phone');
            $item['created'] = date('Y-m-d H:i:s', $item['created']);
            $item['utime'] = date('Y-m-d H:i:s', $item['utime']);
            return $item;
        });
        return $list;
    }
}
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\admin\model;
use think\Model;
use think\Db;

class Log extends Model{

    protected $table = 'gboy_admin_log';

    public function getAll($where)
    {
        $re = array();
        $re = DB::table($this->table)->alias('l')->where($where)->join('__ADMIN_USER__ a', 'a.id = l.admin_id')->field('l.*, a.username as username')->order('add_time DESC')->select();
        return $re;
    }
}
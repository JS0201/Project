<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\model;
use think\Model;
use think\Db;
class MemberGroup extends Model{

    protected $table='gboy_member_group';

    public function getValue($where, $value)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->value($value);
        return $re;
    }



}
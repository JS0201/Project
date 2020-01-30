<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\goods\model;
use think\Db;
use think\Model;

class Content extends Model
{
    protected $table='gboy_content';
    public function add($arr)
    {
        $re = false;
        $re = DB::table($this->table)->insertGetId($arr);
        return $re;
    }
    public function getByid($id)
    {
        $re = array();
        $re = DB::table($this->table)->where(array('id' => $id))->find();
        return $re;
    }
    public function getAll($where, $select)
    {
        $re = array();
        $re = DB::table($this->table)->field($select)->where($where)->select();
        return $re;
    }
    public function getList($where, $select, $start, $limit)
    {
        $re = array();
        $re = DB::table($this->table)->field($select)->where($where)->limit($start, $limit)->select();
        return $re;
    }
}
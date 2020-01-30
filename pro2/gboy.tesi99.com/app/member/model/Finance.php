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
class Finance extends Model
{
    protected $table='gboy_money_finance';
    public function add($arr)
    {
        $re = false;
        $re = DB::table($this->table)->insertGetId($arr);
        return $re;
    }
    public function getValue($where, $value)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->value($value);
        return $re;
    }
    public function updated($where, $data)
    {
        $re = '';
        $re = DB::table($this->table)->where($where)->update($data);
        return $re;
    }
    public function getByUid($id)
    {
        $re = array();
        $re = DB::table($this->table)->where(array('uid' => $id))->find();
        return $re;
    }
    public function getAll($where, $select)
    {
        $re = array();
        $re = DB::table($this->table)->field($select)->where($where)->select();
        return $re;
    }

    public function sql($sql)
    {
        $re = DB::query($sql);
        return $re;
    }
}
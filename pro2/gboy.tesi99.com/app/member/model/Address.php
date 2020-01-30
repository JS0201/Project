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

class Address extends Model{

    protected $table='gboy_member_address';

    protected $append=['complete_address'];


    public function getCompleteAddressAttr($value,$data){

        return $data['province'].$data['city'].$data['county'].$data['address'];

    }
    public function getList($where, $select)
    {
        $re = array();
        $re = DB::table($this->table)->field($select)->where($where)->select();
        return $re;
    }
    public function getOne($where)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->find();
        return $re;
    }
    public function add($data)
    {
        $re = false;
        $re = DB::table($this->table)->insertGetId($data);
        return $re;
    }
    public function updated($where, $data)
    {
        $re = '';
        $re = DB::table($this->table)->where($where)->update($data);
        return $re;
    }
    public function del($where)
    {
        return Db::table($this->table)->where($where)->delete();
    }
    //获取最后一条记录
    public function getLast($where, $start, $limit, $order='id desc')
    {
        $re = array();
        $re = DB::table($this->table)->limit($start, $limit)->where($where)->order($order)->select();
        return $re;
    }

    public function getValue($where, $value)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->value($value);
        return $re;
    }

}
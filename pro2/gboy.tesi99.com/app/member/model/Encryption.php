<?php
namespace app\member\model;
use think\Model;
use think\Db;
class Encryption extends Model
{
    protected $table = 'gboy_secure_encryption';

    public function add($data)
    {
        $re = false;
        $re = DB::table($this->table)->insertGetId($data);
        return $re;
    }
    public function getOne($where)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->find();
        return $re;
    }
    public function updated($where, $data)
    {
        $re = '';
        $re = DB::table($this->table)->where($where)->update($data);
        return $re;
    }
}

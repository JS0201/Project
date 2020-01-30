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
class Member extends Model{

    protected $insert=['register_time','register_ip'];
    protected $append=['group_name'];
    protected $table = 'gboy_member';
    protected function setRegisterTimeAttr(){
        return time();
    }

    protected function setRegisterIpAttr(){

        return getip();
    }

    protected function getGroupNameAttr($value,$data){

        $group=model('member/MemberGroup','service')->get_find(['id'=>$data['group_id']]);
        return $group['name'];

    }

    protected function getRegisterTimeAttr($value){

        if($value)  return getdatetime($value);

    }

    protected function getLoginTimeAttr($value){

        if($value)  return getdatetime($value);

    }


    public function count($sqlmap=''){

        return  $this->where($sqlmap)->count(1);

    }

    public function getOne($where)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->find();
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

    public function getAll($where, $field = '*')
    {
        $re = array();
        $re = DB::table($this->table)->field($field)->where($where)->select();
        return $re;
    }

}
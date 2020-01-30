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
use think\db;
class MemberLog extends Model{

    protected $append=['username'];
    protected $table='gboy_member_log';

    protected function getUserNameAttr($value,$data){

        if($data['mid']){

            $username=model('member/Member','service')->get_find(['id'=>$data['mid']],'username');

            return  $username['username'];
        }

    }


    protected function getTypeAttr($value){

      return $this->type($value);


    }

    protected function getValueAttr($value){
        if($value>=0){
            $value='+'.$value;
        }

        return $value;
    }

    protected function getDateLineAttr($value){
        return getdatetime($value);
    }



    public function type($type=''){


        $type_arr=['money'=>'金额','exp'=>'经验值'];

        if($type) $type_arr=$type_arr[$type];


        return $type_arr;

    }

    public function add($data)
    {
        $re = false;
        $re = DB::table($this->table)->insertGetId($data);
        return $re;
    }
    public function getList($where, $select, $start, $limit)
    {
        $re = array();
        $re = DB::table($this->table)->field($select)->limit($start, $limit)->where($where)->select();
        return $re;
    }
    public function getAll($where, $select)
    {
        $re = array();
        $re = DB::table($this->table)->field($select)->where($where)->order('id desc')->select();
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

    public function sql($sql)
    {
        $re = DB::query($sql);
        return $re;
    }
}
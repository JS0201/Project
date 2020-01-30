<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\goods\model;
use think\Model;
use think\Db;

class Sku extends Model{

    protected $table='gboy_goods_sku';

    protected $append=['spec_md5','spec_str','spec_query'];


    protected $autoWriteTimestamp=true;
    protected $createTime='up_time';
    protected $updateTime='update_time';

    protected $spc_md5='';
    protected $spec_str='';
    protected $spec_query='';

    protected function getSpecAttr($value,$data){


        $specs = json_decode($value,true);
        $result=[];
        $spec_strs = '';
        foreach ($specs AS $id => $spec) {

            $result[md5($id.$spec['value'])]=$spec;
            $specs['spec'][md5($id.$spec['value'])] = $spec;
            $spec_strs .= $spec['name'].':'.$spec['value'].' ';
            $spec_md5 = md5($spec_strs);
        }

        $this->spc_md5=$spec_md5;
        $this->spec_str=$spec_strs;
        $this->spec_query=$value;

        return $result;
    }

    protected  function getSpecMd5Attr($values,$data){
       return  $this->spc_md5;
    }

    protected  function getSpecStrAttr($values,$data){
        return  $this->spec_str;
    }



    protected function getSpecQueryAttr($value,$data){
        return  $this->spec_query;
    }



    protected function getThumbAttr($value,$data){

        $thumb=db('goods_spu')->where(['id'=>$data['spu_id']])->value('thumb');
        return $thumb;

    }
    public function getOne($where)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->find();
        return $re;
    }
    public function getAll($where){
        $re = array();
        $re = DB::table($this->table)->where($where)->select();
        return $re;
    }

    protected function getImgsAttr($value,$data){

        $imgs=db('goods_spu')->where(['id'=>$data['spu_id']])->value('imgs');

        if(is_array($imgs)){

            $imgs=json_decode($imgs,true);
        }

        return $imgs;
    }
}
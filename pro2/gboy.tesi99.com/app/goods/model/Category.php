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
class Category extends Model{

    protected $table='gboy_goods_category';
    protected $append=['data_num'];

    protected static function init()
    {

        //写入后对path_id处理
        self::afterWrite(function ($data) {

            if($data['parent_id']){

                $top_info = self::where('id',$data['parent_id'])->find();

                $path_id=sprintf('%s%d,',$top_info['path_id'],$data['id']);

            }else{
                $path_id=sprintf('0,%d,',$data['id']);
            }

            $info = self::where('id',$data['id'])->find();
            $info->path_id     = $path_id;
            $info->save();

        });




    }
    public function getOne($where)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->find();
        return $re;
    }



    protected function getDataNumAttr($value,$data){

        return  model('goods/Index')->where('path_id','like','%,'.$data['id'].',%')->field('id')->count();

    }



}
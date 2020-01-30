<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\content\model;
use think\Model;

class Category extends Model{


    protected $append=['model_type','data_num'];

    protected static function init()
    {
        self::afterInsert(function ($data) {


            self::path_id($data);

        });

        //缓存
        //cache('category',self::select()->toArray());



    }


    protected static function path_id(&$data){




        if($data['parent_id']){

            $top_info = self::where('id',$data['parent_id'])->find();

            $path_id=$top_info['path_id'].$data['id'].',';

        }else{
            $path_id='0,'.$data['id'].',';
        }


        $info = self::where('id',$data['id'])->find();
        $info->path_id     = $path_id;
        $info->save();
    }





    public function getModelTypeAttr($value, $data)
    {

       return $this->model($data['modelid']);
    }


    public function model($modelid=''){

        $model=model('content/Models')->where(array('disabled'=>0))->column('modelid,name');

        if($modelid){
            return $model[$modelid];
        }else{
            return $model;
        }

    }



    public function getDataNumAttr($value,$data){

        return  model('content/Content')->where('path_id','like','%,'.$data['id'].',%')->count();

    }




}
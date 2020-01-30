<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\content\model;
use think\Model ;

class Content extends Model{

    protected $insert=['add_time'];
    protected $update=['update_time'];
    protected $append=['category_name'];


    protected static function init()
    {

        self::beforeInsert(function ($data) {
            return $data['category_id']=$data['menuid'];
        });

        self::beforeInsert(function ($data) {
            $category=model('content/Category')->field('id,path_id')->getById($data['menuid']);
            return $data['path_id']=$category['path_id'];
        });




    }

    protected function setAddTimeAttr(){
        return time();
    }

    protected function setUpdateTimeAttr(){
        return time();
    }


    protected function getCategoryNameAttr($value,$data){
       return  model('content/Category')->where(['id'=>$data['category_id']])->value('name');
    }

    protected function getAddTimeAttr($value,$data){

        return getdatetime($value);

    }





}

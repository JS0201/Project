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

class Spu extends Model{


    protected  $table='gboy_goods_spu';

    protected $append=['brand_name','cate_name','price'];




    protected function getBrandNameAttr($value,$data){


        $brand_name=model('goods/Brand')->where(['id'=>$data['brand_id']])->value('name');


        return $brand_name;

    }


    protected function getCateNameAttr($value,$data){

        $category_info=model('goods/Category','service')->get_by_id(['id'=>$data['catid']],true,false);

        return $category_info['parent_name'];
    }

    protected function getPriceAttr($value,$data){

        $price = $data['min_price'].'-'.$data['max_price'];

        return $price;
    }


    protected function getImgsAttr($value,$data){

        if($value){

            return json_decode($value,true);
        }

    }



    protected function getSpecsAttr($value,$data){
        if($value){
            $specs = json_decode($value, true);
            foreach ($specs as $id => $spec) {
                $specs[$id]['value'] = explode(",", $spec['value']);
                $specs[$id]['img'] = explode(",", $spec['img']);
                // $specs[$id]['md5'] = md5($spec['name'].':'.$spec['value']);
            }

            return  $specs;
        }

    }

    /**
     * 获取拓展品牌
     * @param $spu
     * @return mixed
     */
    public function get_extra_brand($spu){
        $spu_id = (int) $spu['id'];
        $brand_id = (int) $spu['brand_id'];
        if($spu_id > 0 && $brand_id > 0) {
            return model('goods/Brand','service')->get_by_id(['id'=>$brand_id],false);
        }

    }

    /**
     * 获取拓展分类
     * @param $spu
     * @return bool
     */
    public function get_extra_category($spu) {
        $catid = (int) $spu['catid'];
        if($catid > 0) {
            return model('goods/Category','service')->get_by_id(['id'=>$catid],false,false);
        }

    }


    /**
     * 获取拓展类型
     * @param $spu
     * @return bool
     */
    public function get_extra_type($spu) {
        $spu_id = (int) $spu['id'];
        if($spu_id > 0) {

        }

    }

    /**
     * 获取拓展SKU列表
     * @param $spu
     * @return bool
     */
    public function get_extra_sku($spu) {
        $spu_id = (int) $spu['id'];
        if($spu_id > 0) {
            return model('goods/Sku','service')->get_sku($spu_id);
        }

    }



    public function get_extra($goods){

        $extra=[];
        $extra['specs'] = model('goods/Spu','service')->get_goods_specs($goods['_sku']);


        return $extra;
    }



}
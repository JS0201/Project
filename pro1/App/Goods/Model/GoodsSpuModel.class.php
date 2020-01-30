<?php

namespace Goods\Model;

use Think\Model;

class GoodsSpuModel extends Model {

    protected $_validate = array(
        array('name', 'require', '请输入商品名称', 1),
        array('catid', 'require', '请选择商品分类', 1),
    );
    protected $_auto = array(
        array('path_id', 'set_path', 3, 'callback'),
    );

    //设置path
    public function set_path() {

        $catid = $_POST['spu']['catid'];

        $row = D('Goods/GoodsCategory', 'Service')->find(array('id' => $catid));

        return $row['path_id'];
    }

    /**
     * 获取拓展分类
     * @param  array $spu SPU数组
     * @return array
     */
    public function get_extra_category($spu) {

        $catid = (int) $spu['catid'];
        if ($catid > 0) {

            $result = D('Goods/GoodsCategory', 'Service')->find(array('id' => $catid));
            $result['parent_name'] = D('Goods/GoodsCategory', 'Service')->get_parent_name($result['id']);
            return $result;
        }
        return false;
    }

    protected function _after_find(&$result, $options) {

        $result['imgs'] = json_decode($result['imgs'], true);
        return $result;
    }

    /**
     * 获取拓展品牌
     * @param  array $spu SPU数组
     * @return array
     */
    public function get_extra_brand($spu) {
        $spu_id = (int) $spu['id'];
        $brand_id = (int) $spu['brand_id'];
        if ($spu_id > 0 && $brand_id > 0) {
            return D('Goods/Brand', 'Service')->find(array('id' => $spu['brand_id']));
        }
        return false;
    }

    /**
     * 获取拓展SKU列表
     * @param  array $spu SPU数组
     * @return array
     */
    public function get_extra_sku($spu) {
        $spu_id = (int) $spu['id'];
        if ($spu_id > 0) {

            return D('Goods/Goods_sku', 'Service')->get_sku($spu_id);
        }
        return false;
    }

    public function get_extra_type($spu) {
        $spu_id = (int) $spu['id'];
        if ($spu_id > 0) {
            //return $this->load->service('goods/type')->get_type_by_goods_id($spu_id);
        }
        return false;
    }



}

?>
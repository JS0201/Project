<?php

namespace Goods\Service;

use Think\Model;

class GoodsSkuService extends Model {

    public function __construct() {

        $this->model = D('Goods/Goods_sku');
        $this->spu_model = D('Goods/Goods_spu');
    }

    /**
     * [select 列表]
     * @return [type]            [description]
     */
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'sku_id desc') {

        $count = $this->model->where($sqlmap)->count();
        $pages = new \Think\Page($count, $limit);
        $page = $page ? $page : 1;

        if (isset($_GET['p'])) {
            $page = (int) $_GET['p'];
        }

        if ($limit != '') {

            $limits = (($page - 1) * $limit) . ',' . $limit;
        }

        $lists = $this->model->where($sqlmap)->order($order)->limit($limits)->select();

        return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
    }
/*商城首页获取2条数据*/
    public function select2($sqlmap = array(), $limit = 2, $page = 1, $order = 'sku_id desc') {

        $count = $this->model->where($sqlmap)->count();
        $pages = new \Think\Page($count, $limit);
        $page = $page ? $page : 1;

        if (isset($_GET['p'])) {
            $page = (int) $_GET['p'];
        }

        if ($limit != '') {

            $limits = (($page - 1) * $limit) . ',' . $limit;
        }

        $lists = $this->model->where($sqlmap)->order($order)->limit($limits)->select();

        return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
    }

    /**
     * [create_sku 处理子商品]
     * @param  [type] $params [子商品信息] 二维数组
     * @return [type]         [boolean]
     */
    public function create_sku($params) {

        //sku数据处理
        foreach ($params as $k => $v) {
            if ($v['sku_id']) {
                $this->model->where(array('sku_id' => $v['sku_id']))->data($v)->save();
            } else {
                $params[$k]['sku_id'] = $this->model->data($v)->add();
            }
        }

        return $params;
    }

    /**
     * [find 获取一条子商品信息]
     * @param  [type]  $id    [description]
     * @param  boolean $field [description]
     * @return [type]         [description]
     */
    public function find($sqlmap = array(), $extra = 'spu') {

        $goods = $this->model->where($sqlmap)->find();

        if (!$goods) {

            $this->errors = L('_data_not_exist_');
            return false;
        }

        if ($extra) {
            $extra = explode(",", $extra);
            foreach ($extra AS $method) {
                if (method_exists($this->model, $method)) {

                    $goods[$method] = $this->model->$method($goods['spu_id']);
                }
            }
        }

        return $goods;
    }

    /**
     * [detail 获取一条sku商品信息] 已处理imgs、thumb
     * @param  [type]  $id    [description]
     * @param  boolean $field [description]
     * @return [type]         [description]
     */
    public function detail($sku_id) {

        $goods_sku = $this->find(array('sku_id' => $sku_id));

        $goods_spu = D('Goods/Goods_spu', 'Service')->find(array('id' => $goods_sku['spu_id']));

        $goods_sku['imgs'] = $goods_sku['imgs'] ? $goods_spu['imgs'] : $goods_spu['spu']['imgs'];
        $goods_sku['thumb'] = $goods_sku['thumb'] ? $goods_spu['thumb'] : $goods_spu['spu']['thumb'];
        $goods_sku['shop_price'] = $this->model->price($goods_sku['shop_price']);

        return $goods_sku;
    }

    /**
     * [get_sku 根据主商品获取子商品]
     * @param  [type] $id [主商品id]
     * @return [type]     [description]
     */
    public function get_sku($id) {
        $result = $this->model->where(array('spu_id' => $id, 'status' => array('NEQ', -1)))->order('sku_id ASC')->select();
        $spec_str = '';
        foreach ($result AS $k => $v) {
            $spec_md5 = md5($v['sku_id']);
            $spec_str = md5($v['sku_id']);
            $result[$k]['spec_md5'] = $spec_md5;
        }

        return $result;
    }

    /**
     * [get_lists 获取商品sku列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function get_lists($sqlmap = array(), $limit = 20, $page = 1, $order = 'sort asc') {
        $result = $this->select($sqlmap, $limit, $page, $order);

        foreach ($result['lists'] as $k => $v) {

            $goods_spu = D('Goods/Goods_spu', 'Service')->find(array('id' => $v['spu_id']));

            $result['lists'][$k]['imgs'] = $v['imgs'] ? $v['imgs'] : $goods_spu['spu']['imgs'];
            $result['lists'][$k]['thumb'] = $v['thumb'] ? $v['thumb'] : $goods_spu['spu']['thumb'];
            $result['lists'][$k]['shop_price'] = $this->model->price($v['shop_price']);
        }

        return $result;
    }


    /**
     * [get_lists 获取商品sku列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     * 商城首页获取2条数据
     */
    public function get_lists2($sqlmap = array(), $limit = 2, $page = 1, $order = 'sort asc') {
        $result = $this->select2($sqlmap, $limit, $page, $order);

        foreach ($result['lists'] as $k => $v) {

            $goods_spu = D('Goods/Goods_spu', 'Service')->find(array('id' => $v['spu_id']));

            $result['lists'][$k]['imgs'] = $v['imgs'] ? $v['imgs'] : $goods_spu['spu']['imgs'];
            $result['lists'][$k]['thumb'] = $v['thumb'] ? $v['thumb'] : $goods_spu['spu']['thumb'];
            $result['lists'][$k]['shop_price'] = $this->model->price($v['shop_price']);
            $result['lists'][$k]['catid'] = $v['catid'] ? $v['catid'] : $goods_spu['spu']['catid'];
        }

        return $result;
    }
    /**
     * 指定商品减少库存
     * @param [int] $id 子商品ID
     * @param [int] $number变更数量
     * @return bool
     */
    public function set_dec_number($id, $number) {
        $sku_id = (int) $id;
        $number = (int) $number;
        if ($id < 1 || $number < 1) {
            $this->errors = L('_params_error_');
            return FALSE;
        }

        $sqlmap = $map = array();
        $map['sku_id'] = $id;
        $sqlmap['id'] = $this->model->where(array('sku_id' => $id))->getField('spu_id');
        $result = $this->model->where($map)->setDec('number', $number);
        $spu_info= $this->spu_model->where($sqlmap)->find();
        if($spu_info['sku_total']>=$number){
             $_result = $this->spu_model->where($sqlmap)->setDec('sku_total', $number);
        }else{
             $_result = $this->spu_model->where($sqlmap)->data(array('sku_total'=>0))->save();
        }
    
        
        return TRUE;
    }

    /**
     * 指定商品增加库存
     * @param [int] $sku_id 子商品ID
     * @param [int] $goods_num变更数量
     * @return bool
     */
    public function set_inc_number($id, $number) {
        $id = (int) $id;
        $number = (int) $number;
        if ($id < 1 || $number < 1) {
            $this->errors = L('_params_error_');
            return FALSE;
        }
        $sqlmap = $map = array();
        $map['sku_id'] = $id;
        $sqlmap['id'] = $this->model->where(array('sku_id' => $id))->getField('spu_id');
        $result = $this->model->where($map)->setInc('number', $number);
        $_result = $this->spu_model->where($sqlmap)->setInc('sku_total', $number);

        return TRUE;
    }

    /**
     * [is_favorite 判断商品是否已收藏]
     * @param  [type]  $id [description]
     * @return boolean     [description]
     */
    public function is_favorite($mid, $id) {
        if ((int) $id < 1)
            return FALSE;
        $favorite = FALSE;
        if ($mid > 0) {
            $favorite = D('Member/Member_favorite','Service')->set_mid($mid)->is_exists($id);
        }
        return $favorite;
    }

}

?>
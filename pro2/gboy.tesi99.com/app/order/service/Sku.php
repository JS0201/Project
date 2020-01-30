<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\order\service;
use think\Model;

class Sku extends Model{

    public function initialize()
    {
       $this->model = model('order/Sku');
       $this->skumodel = model('goods/Sku');
    }





    /**
    /**
     * 创建订单商品
     * @param  array	$params 订单商品相关参数
     * @return [boolean]
     */
    public function create_all($params) {
        if (count(array_filter($params)) < 1) {
            $this->errors = lang('order_goods_empty');
            return FALSE;
        }
        foreach ($params as $key => $val) {
            $sku_info['sku_thumb'] = $val['sku_thumb'];
            $sku_info['sku_name'] = $val['sku_name'];
            $sku_info['sku_spec'] = $val['sku_spec'];
            $sku_info['sku_price'] = $val['sku_price'];
            $sku_info['real_price'] = $val['real_price'];
            $val['spu_id'] = $val['spu_id'];
            $val['sku_info'] = json_encode($sku_info);
            $result = $this->model->insert($val);
            if (!$result) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * 指定商品减少库存
     * @param [int] $id 子商品ID
     * @param [int] $number变更数量
     * @return bool
     */
    public function set_dec_number($sku_id, $number) {
        $sku_id = (int) $sku_id;
        $number = (int) $number;
        if($sku_id < 1 || $number < 1) {
            $this->errors = lang('_param_error_');
            return false;
        }
        // 查询数据
        $data = $this->skumodel->where(array('sku_id' => $sku_id))->find();
        if(!$data){
            $this->errors = lang('_param_error_');
            return false;
        }
        $data = $data->toArray();
        // 判断库存是否足够
        if($data['number'] < $number){
            $this->errors = lang('goods_stock_insufficient');
            return false;
        }

        $result = $this->skumodel->where(array('sku_id' => $sku_id))->setDec('number', $number);
        if(!$result){
            return false;
        }else{
            return true;
        }
    }

    public function updated($where, $data)
    {
        return $this->model->updated($where, $data);
    }
    public function getList($where, $select)
    {
        return $this->model->getList($where, $select);
    }
}
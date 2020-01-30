<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\order\model;
use think\Model;
use think\Db;

class OrderSku extends Model{

    protected $table='gboy_order_sku';

    public function getList($where, $select)
    {
        $re = array();
        $re = DB::table($this->table)->field($select)->where($where)->select();
        return $re;
    }
    public function getOne($where)
    {
        $re = array();
        $re = DB::table($this->table)->where($where)->find();
        return $re;
    }
}
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\service;
use think\Model;
class Order extends Model
{
    public function initialize()
    {
        $this->orderModel = model('member/Order');
        $this->skuModel = model('goods/Sku');
        $this->orderSkuModel = model('order/OrderSku');
    }

    public function getList($userid)
    {
        $sql = "select o.sn, o.pay_status, o.delivery_status, o.pay_type, o.real_amount, d.o_sku_ids, d.isreceive from gboy_order as o left join gboy_order_delivery as d on o.sn = d.sn 
                                                                                                          where buyer_id = {$userid} and status = 1 order by o.system_time desc";
        $result = $this->orderModel->sql($sql);
        $nopay = $nosend = $noget = array();
        if($result) {
            foreach ($result as $k => $v) {
                $ordersku = $this->orderSkuModel->getList(array('order_sn' => $v['sn']), '*');
                $sum = 0;
                foreach($ordersku as $kk => $vv) {
                    $result[$k]['list'][$kk]['sum'] = isset($result[$k]['list'][$kk]['sum']) ? $result[$k]['list'][$kk]['sum'] + $vv['buy_nums'] : $vv['buy_nums'];
                    $sum += $vv['buy_nums'];
                    $sku = $this->skuModel->getOne("sku_id = {$vv[sku_id]}"); //第一件商品的信息
                    $result[$k]['list'][$kk]['sku_name'] = $sku['sku_name'];
                    $result[$k]['list'][$kk]['subtitle'] = $sku['subtitle'];
                    //$result[$k]['list'][$kk]['weight'] = $sku['weight'];
                    $result[$k]['list'][$kk]['message'] = (array)json_decode($vv['sku_spec']);
                    $result[$k]['list'][$kk]['thumb'] = $vv['sku_thumb'];
                }
                $result[$k]['sum'] = $sum;
                if (!$v['pay_status'] && $v['pay_type'] == 1) {
                    $status = 0; //未支付
                    $nopay[] = $result[$k];
                } else if (!$v['delivery_status']) {
                    $status = 1; //待发货 可催单
                    $nosend[] = $result[$k];
                } else if (!$v['isreceive']) {
                    $status = 2; //待收货
                    $noget[] = $result[$k];
                } else {
                    $status = 3; // 完成
                }
                $result[$k]['status'] = $status;
            }
        }
        $return['result'] = $result;
        $return['nopay'] = $nopay;
        $return['nosend'] = $nosend;
        $return['noget'] = $noget;
        return $return;
    }


}

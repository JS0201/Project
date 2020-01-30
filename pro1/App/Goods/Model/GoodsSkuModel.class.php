<?php

namespace Goods\Model;

use Think\Model;

class GoodsSkuModel extends Model {
    protected $result=array();
    protected $_validate = array(
    );
    protected $_auto = array(
        array('up_time', 'time', 1, 'function'),
        array('update_time', 'time', 2, 'function')
    );

    /**
     * 获取spu数据
     */
    public function spu($spu_id) {

        return M('goods_spu')->find($spu_id);
    }

    /**
     * 计算实际售价
     */
    public function price($sku_price) {
        $prom_price = 0;
        $member = D('Member/Member', 'Service')->init();
        if (!$member['id']) {
            $prom_price = sprintf("%.2f", $sku_price);
        } else {
    
            $discount = (!$member['_group']['discount']) ? 100 : $member['_group']['discount'];
            $prom_price = sprintf("%.2f", $sku_price / 100 * $discount);
        }

        return  $prom_price;
   
    }

}

?>
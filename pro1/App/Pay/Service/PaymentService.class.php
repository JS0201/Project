<?php

namespace Pay\Service;

use Think\Model;

class PaymentService extends Model {

    public function __construct() {
        $this->model = D('Pay/payment');
    }

    /**
     * [支付方式列表]
     * @return boolean
     */
    public function get($key = NULl) {
        $result_enable = $this->model->where(array('enabled' => 1))->getField('pay_code,pay_name,pay_fee,pay_desc,enabled,config,dateline,sort,isonline,applie', TRUE);
        return is_string($key) ? $result_enable[$key] : $result_enable;
    }

}

?>
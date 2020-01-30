<?php

namespace Order\Service;

use Think\Model;

class OrderTradeService extends Model {

    public function __construct() {

        $this->model = D('Order/Order_trade');
    }

    /* 修改 */

    public function setField($data, $sqlmap = array()) {
     
        $result = $this->model->where($sqlmap)->save($data);
    
        return $result;
    }

}

?>
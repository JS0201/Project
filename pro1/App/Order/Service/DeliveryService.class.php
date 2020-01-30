<?php

namespace Order\Service;

use Think\Model;

class DeliveryService extends Model {

    public function __construct() {
        $this->model = D('Order/Delivery');
    }

    
    
    
    /**
     * @param  string  获取的字段
     * @param  array 	sql条件
     * @return [type]
     */
    public function getField($field = '', $sqlmap = array()) {
        $result = $this->model->where($sqlmap)->getfield($field);
   
        return $result;
    }

}

?>
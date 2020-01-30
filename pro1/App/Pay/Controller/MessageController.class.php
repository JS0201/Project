<?php

namespace Pay\Controller;

use Think\Controller;

class MessageController extends Controller {

    public function _initialize() {

        $this->order_service = D('Order/Order', 'Service');
        $this->order_sku_service = D('Order/Order_sku', 'Service');
        $this->order_sub_service = D('Order/Order_sub', 'Service');
    }

    public function dnotify() {

       


		$this->display();
    }

    function xmltoarray($xml) {
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

}

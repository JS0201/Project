<?php

namespace Order\Controller;

use Common\Controller\BaseController;

class CartController extends BaseController {

    public function _initialize() {
        parent::_initialize();

        $this->service = D('Order/Cart', 'Service');
        $this->order_service = D('Order/Order', 'Service');
        $this->sku_service = D('Goods/Goods_sku', 'Service');
    }

    /* 购物车页面 */

    public function index() {

        $_SESSION['cart_type'] = 'cart_info';

        //unset($_SESSION['cart_info_to']['goods_info']);
        $carts = $this->service->get_cart_lists('cart_info');
        
        //  print_r($_SESSION['cart_info']);
       // $carts = $this->service->get_cart_lists('cart_info_to');
       // print_r($carts);
        //$carts_to = $this->service->get_cart_lists('cart_info_to');
        //print_r($_SESSION['cart_info']);
        //print_r($_SESSION['cart_info_to']);
        //print_r($carts);
        $uid = $this->member['id'];

        $status=M('order')->where(array('buyer_id'=>$uid))->find();

        $this->assign('status',$status);

        $this->assign($carts, $carts)->display();
    }

    public function cart() {

        $_SESSION['cart_type'] = 'cart_info_to';


        //print_r($_SESSION['cart_info_to']);
        $carts = $this->service->get_cart_lists('cart_info_to');
        
       //print_r($carts);

        //print_r($_SESSION['cart_info']);
        //print_r($_SESSION['cart_info_to']);
        //print_r($carts_to);


        $this->assign($carts, $carts)->display();
    }

    /**
     * 设置购物车商品数量
     * @param int 	$sku_id 商品sku_id
     * @param int 	$nums 	要设置的数量
     * @return [boolean]
     */
    public function set_nums() {
        $result = $this->service->set_nums($_GET['sku_id'], $_GET['nums'], (int) $this->member['id']);
        if (!$result)
            showmessage($this->service->errors);
        showmessage(L('_operation_success_'), '', 1, '', 'json');
    }

    /**
     * 购物车结算
     * @return [boolean]
     */
    public function settlement() {
//print_r($_SESSION);
        if (!$this->member['id']) {
            $url_forward = $_GET['url_forward'] ? $_GET['url_forward'] : urlencode($_SERVER['REQUEST_URI']);

            redirect(U('member/public/login', array('url_forward' => $url_forward)));
        }
        foreach ($_SESSION['cart_info']['goods_info'][0]['sku_list'] as $k=>$v){
            $data[$k]=$v;

        }
		$id=$this->member['id'];
        $info = $this->service->get_cart_lists($_SESSION['cart_type']);
        //print_r($info);
        //print_r($info['bi']);die;
//		$status=M('order')->where(array('buyer_id'=>$id,'pay_status'=>1))->find();
//
//        $this->assign('stauts',$status);
		 $usdt_price=M('timeprice')->where(array('id'=>1))->getField('price');
        $this->assign('usdt_price',$usdt_price);
        $address = D('Member/Member_address', 'Service')->uid($this->member['id'])->get_address($_GET['address_id']);
        $this->assign('data',$data)
            ->assign('info', $info)
            ->assign('address', $address)
            ->display();
    }


    /**
     * 购物车结算  联盟商家
     * @return [boolean]
     */
    public function settlement1() {
//print_r($_SESSION);

        if (!$this->member['id']) {
            $url_forward = $_GET['url_forward'] ? $_GET['url_forward'] : urlencode($_SERVER['REQUEST_URI']);

            redirect(U('member/public/login', array('url_forward' => $url_forward)));
        }


        $info = $this->service->get_cart_lists($_SESSION['cart_type']);
        //print_r($info);die;
        $address = D('Member/Member_address', 'Service')->uid($this->member['id'])->get_address($_GET['address_id']);
        $this->assign('info', $info)->assign('address', $address)->display();
    }


    /**
     * 创建订单
     * @param 	array
     * @return  [boolean]
     */
      public function create() {

        
        if (!$this->member['id']) {
            $url_forward = $_GET['url_forward'] ? $_GET['url_forward'] : urlencode($_SERVER['REQUEST_URI']);

            redirect(U('member/public/login', array('url_forward' => $url_forward)));
        }

		$pay_type =1;
        $pay_method = (int) $_POST['pay_method'];
        $deliverys = (array) $_POST['deliverys'];
        $order_prom = (array) $_POST['order_prom'];
        $sku_prom = (array) $_POST['sku_prom'];
        $remarks = (array) $_POST['remarks'];
        $invoices = (array) $_POST['invoices'];
        $address_id = (int) $_POST['address_id'];
      
        $result = $this->order_service->create($this->member['id'],$pay_method, $address_id, $pay_type, $deliverys, $order_prom, $sku_prom, $remarks, $invoices);
        if (!$result) {

           showmessage($this->order_service->errors);
        }
         
        $this->service->set_cart('', $_SESSION['cart_type']);

   		if($pay_method==1){
			
			$url=U('member/order/pay', array('order_sn' => $result,'pay_method'=>$pay_method));
			
		}else{
			$url=U('member/order/pay', array('order_sn' => $result,'pay_method'=>$pay_method));
		}
		
        showmessage(L('order_create_success'), $url, 1, 'json');
    }


    /**
     * 添加加购物车 (支持批量添加)
     * @param  array	$params ：array('sku_id' => 'nums'[,'sku_id' => 'nums'...])
     * @param  int    $nums 	数量
     * @return [boolean]
     */
    public function cart_add() {

        $params = array_filter($_GET['params']);
// print_r($params);die;
        if (empty($params))
            showmessage(L('parameter_empty'));
        $result = $this->service->cart_add($params, (int) $this->member['id'], $_GET['buynow']);
        //print_r($result);die;
        if (!$result) {

            showmessage($this->service->errors);
        }

        showmessage('', '', 1, '', 'json');
    }

    /**
     * 删除购物车商品
     * @param  int 	$sku_id 商品sku_id
     * @return [boolean]
     */
    public function delpro() {
        $result = $this->service->delpro($_GET['sku_id']);
        if (!$result)
            showmessage($this->service->errors);
        showmessage(L('cart_delete_success'), '', 1, 'json');
    }

    /**
     * 清空购物车
     * @return [boolean]
     */
    public function clear() {
        $this->service->clear();
    }


}

<?php

namespace Order\Service;

use Think\Model;

class CartService extends Model {

    protected $cart_type;

    public function __construct() {

        if ($_SESSION['cart_type'] == 'cart_info_to') {
            $cart_type = 'cart_info_to';
        } else {
            $cart_type = 'cart_info';
        }

        //echo $cart_type;

        $this->cart_type = $cart_type;
        $this->goods_sku_service = D('Goods/Goods_sku', 'Service');
    }

    /**
     * 获取购物车列表
     * @return [result]
     */
    public function get_cart_lists($cart_type = '') {

        if ($cart_type) {
            $result = $_SESSION[$cart_type];
        } else {
            $result = $_SESSION[$this->cart_type];
        }

//print_r($result);die;
        //重组
        if ($result) {

            foreach ($result as $k => $v) {
                $prices = $number = 0;
                foreach ($v as $kk => $vv) {

                    foreach ($vv as $kkk => $vvv) {
                        $number = $prices = 0;
                        foreach ($vvv as $kkkk => $vvvv) {
                            $number += $vvvv['number'];
                            $prices += $vvvv['prices'];
                            $bi=$vvvv['_sku_']['spu_id'];
                            //print_r($vvvv);die;
                        }
                    }
                    $result[$k][$kk]['sku_price'] = sprintf("%.2f", $prices);
                    $result[$k][$kk]['sub_numbers'] = $number;
                    $result[$k][$kk]['spu_id'] = $bi;
                }
            }
            
            

            // $prices = $number = 0;
            /*
              foreach ($result['goods_info'][0]['sku_list'] as $k => $v) {
              //$result['goods_info'][0]['sku_list'][$k]=
                  $number += $vvvv['number'];
                  $prices += $vvvv['prices'] * $vvvv['number'];
              }
             * 
             */
           



            $result['all_prices'] = sprintf("%.2f", $prices);
            $result['sku_numbers'] = $number;
            $result['bi'] = $bi;
        }

        return $result;
    }

    /**
     * 购物车添加商品(支持数组)
     * @param  array	$params ：array('sku_id' => 'nums'[,'sku_id' => 'nums'...])
     * @param  int 		$buyer_id ：会员id (游客为0，默认0)
     * @param  boolean  $buynow ：是否立即购买(默认false)
     * @return [boolean]
     */
    public function cart_add($params, $buyer_id = 0, $buynow = FALSE) {

        if (empty($params)) {
            $this->errors = L('_params_error_');
            return FALSE;
        }

        // print_r($params);
        foreach ($params as $skuid => $nums) {
            $skuid = (int) $skuid;
            $sku_info = $this->goods_sku_service->find(array('sku_id' => $skuid, 'status' => 1)); // 获取商品信息



            if ($sku_info) {
				
				
				    if($sku_info['number']<$nums){
                         $this->errors = L('kucun_no');
                        return false;

                    }

                //验证购物车类型
                $goods_spu_info = M('goods_spu')->where(array('id' => $sku_info['spu_id'], array('path_id' => array('like', '%,1,%'))))->find();
             
      
                if ($goods_spu_info) {
                    //加盟商城   普通商城

                    $_SESSION['cart_type'] = 'cart_info';
                    $this->cart_type = 'cart_info';
                } else {

                    //win商城 跨境商城
                    $_SESSION['cart_type'] = 'cart_info_to';

                    $this->cart_type = 'cart_info_to';
                }



                unset($sku_info['content']);
                $nums = ((int) $nums === 0) ? 1 : $nums;
                $this->_add($skuid, $nums, $buyer_id, $buynow);
            } else {
                
            }
        }

        return TRUE;
    }

    /* 执行添加购物车 */

    private function _add($sku_id, $nums, $buyer_id = 0, $buynow = FALSE) {
        $sqlmap = $data = array();

        $sku_info = $this->goods_sku_service->detail($sku_id);

        $_SESSION[$this->cart_type]['goods_info'][$sku_info['uid']]['sku_list'][$sku_id]['number'] = $nums;
        $_SESSION[$this->cart_type]['goods_info'][$sku_info['uid']]['sku_list'][$sku_id]['prices'] = sprintf("%.2f", $nums * $sku_info['shop_price']);
        $_SESSION[$this->cart_type]['goods_info'][$sku_info['uid']]['sku_list'][$sku_id]['_sku_'] = $sku_info;
    }

    /**
     * 设置购物车
     * @param  array $cart_info  购物车信息
     * @return [boolean]
     */
    public function set_cart($cart_info = '', $cart_name = 'cart_info') {
        $cart_name = $this->cart_type;

        if (!$cart_info) {

            unset($_SESSION[$cart_name]);
        } else {

            $_SESSION[$cart_name] = $cart_info;

            //没有商品清空整个购物车

            if (!count($_SESSION[$cart_name]['goods_info'][0]['sku_list'])) {

                $this->clear();
            }
        }
    }

    /**
     * 设置购物车商品数量
     * @param int $sku_id 	商品sku_id
     * @param int $nums 	数量
     * @param int $buyer_id 会员id(游客为0 ,默认0)
     * @return [boolean]
     */
    public function set_nums($sku_id, $nums, $buyer_id = 0) {
        $sku_id = (int) $sku_id;
        $nums = max(0, (int) $nums);
        $buyer_id = (int) $buyer_id;


        $sku_info = $this->goods_sku_service->find(array('sku_id' => $sku_id));





        if (!$sku_info) {

            $this->errors = L('goods_not_exist');
            return false;
        }
        if ($nums > $sku_info['number']) {
            $this->errors = L('buy_max_nums');
            return false;
        }

        
        
  
        //验证购物车类型
        $goods_spu_info = M('goods_spu')->where(array('id' => $sku_info['spu_id'], array('path_id' => array('like', '%,1,%'))))->find();
      
    
        if ($goods_spu_info) {
            //加盟商城

            $_SESSION['cart_type'] = 'cart_info';
            $this->cart_type = 'cart_info';
        } else {
            //跨境商城
            $_SESSION['cart_type'] = 'cart_info_to';

            $this->cart_type = 'cart_info_to';
        }
        
        
        $cart_sku_list = $_SESSION[$this->cart_type]['goods_info'][0]['sku_list'];

        if (array_key_exists($sku_id, $cart_sku_list)) {

            $_SESSION[$this->cart_type]['goods_info'][0]['sku_list'][$sku_id]['number'] = $nums;
            $_SESSION[$this->cart_type]['goods_info'][0]['sku_list'][$sku_id]['prices'] = sprintf("%.2f", $nums * $sku_info['shop_price']);
        }

        return true;
    }

    /**
     * 删除购物车商品
     * @param  int $sku_id 商品sku_id
     * @return [boolean]
     */
    public function delpro($sku_id) {
        $sku_id = (int) trim($sku_id);

        if (!$sku_id) {
            $this->errors = L('delete_parame_empty');
            return FALSE;
        }

        $cart_list = $this->get_cart_lists();


        $sku_list = $cart_list['goods_info'][0]['sku_list'];

        if (array_key_exists($sku_id, $sku_list)) {

            unset($sku_list[$sku_id]);
        }

        $new_cart_list['goods_info'][0]['sku_list'] = $sku_list;

        $this->set_cart($new_cart_list);
        return true;
    }

    /**
     * 清空购物车
     * @return [boolean]
     */
    public function clear() {


        $this->set_cart();

        return true;
    }

}

?>
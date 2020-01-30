<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\order\controller;
use app\common\controller\Base;

class Cart extends Base{

    public function _initialize()
    {

        parent::_initialize();

        $this->service = model('order/Cart','service');
    }

    public function index(){
        //$type = input('get.type') ? input('get.type') : 1;
        $cart_recharge = $this->service->get_cart_lists($this->member['id'], 1);
        $cart_consume = $this->service->get_cart_lists($this->member['id'], 2);

        $this->assign('cart_recharge',$cart_recharge['skus']?$cart_recharge['skus']:'[]');
        $this->assign('cart_consume',$cart_consume['skus']?$cart_consume['skus']:'[]');

        return $this->fetch();
    }

    /**
     * 添加加购物车 (支持批量添加)
     * @param  array	$params ：array('sku_id' => 'nums'[,'sku_id' => 'nums'...])
     * @param  int    $nums 	数量
     * @return [boolean]
     */
    public function cart_add(){
        // 获取数据
        $post = input('post.');
        $result = $this->service->cart_add($post['sku_id'], $post['nums'], (int) $this->member['id'] );
        if (!$result) showmessage($this->service->errors);

        showmessage('购物车添加商品成功', url('order/order/settlement', array('skuids' => implode(";", $forward))), 1);
    }


    /**
     * 设置购物车商品数量
     * @param int 	$sku_id 商品sku_id
     * @param int 	$nums 	要设置的数量
     * @return [boolean]
     */
    public function set_nums() {
        $result = $this->service->set_nums(input('sku_id/d') ,input('post.specid') , input('nums/d') , (int) $this->member['id'], input('post.type'));
        if (!$result) showmessage($this->service->errors);
        showmessage(lang('_operation_success_'),'',1,'json');
    }


    /**
     * 删除购物车商品
     * @param  int 	$sku_id 商品sku_id
     * @return [boolean]
     */
    public function delpro() {
        $spec_id = input('post.spec_id');
        $type = input('post.type');
        if(strstr($spec_id, ',')) {
            $sku_arr = explode(',', $spec_id);
            foreach($sku_arr as $k => $v) {
                $result = $this->service->delpro($v, (int) $this->member['id'], $type);
            }
        }else{
            $result = $this->service->delpro($spec_id, (int) $this->member['id'], $type);
        }
        if (!$result) showmessage($this->service->errors);
        showmessage(lang('_operation_success_'),'',1,'json');
    }

}
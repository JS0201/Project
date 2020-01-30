<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\order\service;
use think\Model;

class Cart extends Model{

    public function initialize()
    {
       $this->model = model('order/Cart');
       $this->specmodel = model('goods/Spec');
       $this->skumodel = model('goods/Sku', 'service');
    }/*

    // 合并cookie和数据库数据，同时保存到数据库
    public function cart_merge($buyer_id = 0){
        // 判断是否游客
        if($buyer_id > 0){ // 不是
            // 读取数据库数据
            $items =  $this->model->where(array('buyer_id' => $buyer_id))->order("id DESC")->select()->toArray();
            // 读取本地数据
            $cook_items = json_decode(cookie('cart_recharge'), TRUE);
            // 判断是否需要合并数据
            if($cook_items){

            }
            return $items;
        }

    }*/


    /**
     * 获取购物车列表
     * @param  integer $buyer_id 会员id(游客为0)
     * @param  string  $sku_ids   skuids (默认空,例：sku_id1[,number1][;sku_id2[,number2]])；多个sku用;分割。数量number可省略，代表购物车记录的件数。为空则获取购物车所有列表
     * @param  boolean $isgroup   是否根据商家分组(默认false)
     * @return [result]
     */
    public function get_cart_lists($buyer_id = 0, $type) {
        if ($buyer_id > 0) {
            $str = $type == 1 ? "type <= 2" : "type > 2";
            $items = $this->model->where("buyer_id = {$buyer_id} and {$str}")->order("id DESC")->field('id,sku_id,nums,spec,img')->select()->toArray();
        } else if($type == 1){
            $items = json_decode(cookie('cart_recharge'), TRUE);
        } else if($type == 2) {
            $items = json_decode(cookie('cart_consume'), TRUE);
        }
        $result = [];
        $all_prices = $numbers = $sold_count = 0;
        foreach ($items as $item) {
            $val = array();
            $sku_id = $item['sku_id'];
            $number = $item['nums'];
            $sku_info = model('goods/Sku')->getOne(['sku_id' => $sku_id, 'status' => 1]);
            unset($sku_info['description']);
            if($sku_info === false || ($sku_info['status'] == -1)) {
                continue;
            };
            $number = min($sku_info['number'], $number);
            $val['sku_id'] = $sku_id;
            $val['number'] = $number;
            $val['_sku_'] = $sku_info;
            $val['specid'] = $item['id'];
            $val['_sku_']['thumb'] = $item['img'];
            $spec = json_decode($item['spec'], true);
            //获取图片规格数据
            /*$specArr = $this->specmodel->getOne("sku_id = {$sku_id} and img != ''");
            $value = $img = array();
            if($specArr) {
                foreach(json_decode($specArr['value'], true) as $kk => $vv) {
                    $value[$vv] = $kk;
                }
                $img = json_decode($specArr['img']);
            }*/

            foreach($spec as $k => $v) {
                $val['_sku_']['spec'] .= $k.':'.$v.'&nbsp;&nbsp;';
            }

            if($number == 0) {
                $val['issold'] = true;
                $sold_count++;
            }
            $val['prices'] = sprintf("%.2f",$sku_info['shop_price'] * $number);
            $result['skus'][] = $val;
            $numbers += $number;
            $all_prices += $val['prices'];
        }
        $result['all_prices']  = sprintf("%.2f",$all_prices);
        $result['sku_numbers'] = $numbers;
        $result['sku_counts']  = count($result['skus']);
        $result['sold_count']  = $sold_count;

        return $result;
    }

    /**
     * 购物车添加商品(支持数组)
     * @param  array	$params ：array('sku_id' => 'nums'[,'sku_id' => 'nums'...])
     * @param  int 		$buyer_id ：会员id (游客为0，默认0)
     * @param  boolean  $buynow ：是否立即购买(默认false)
     * @return [boolean]
     */
    public function cart_add($sku_id, $nums, $buyer_id = 0, $buynow = FALSE) {
        if (empty($sku_id) && empty($nums)) {
            $this->errors = lang('_param_error_');
            return FALSE;
        };
        $sku_info = model('goods/Sku')->where(['sku_id' => $sku_id, 'status' => 1])->find();	// 获取商品信息
        if($sku_info){
            $sku_info = $sku_info->toArray();
        }

        if($sku_info['number'] <= $nums){
            $this->errors=lang('goods_stock_insufficient');
            return false;
        };
        return $this->_add($sku_id , $nums , $buyer_id ,$buynow, $sku_info['spec_query'], $sku_info['thumb'], $sku_info['type']);
    }

    /* 执行添加购物车 */
    private function _add($sku_id ,$nums ,$buyer_id = 0 ,$buynow = FALSE, $spec, $img, $type) {
        // 判断库存
        $res = $this->is_stock($sku_id, $nums);
        if(!$res) return false;
        // 初始化
        $sqlmap = $data = [];
        if ($buyer_id > 0) {
            $data['buyer_id'] = $sqlmap['buyer_id'] = $buyer_id;
            $data['sku_id'] = $sqlmap['sku_id'] = $sku_id;
            $data['spec'] = $sqlmap['spec'] = $spec;
            $data['nums'] = $nums;
            // 获取数据
            $cart = $this->model->where($sqlmap)->find();
            if ($cart) { // 已存在购物车，修改数据
                $cart = $cart->toArray();
                /*if ($buynow == TRUE) {
                    $nums = max($nums ,$cart['nums']);
                } else {
                    $nums = (int) $cart['nums'] + $nums;
                }*/

                // 操作数据
                $this->model->isUpdate(true)->data($data)->save(array('id' => $cart['id']));
                return true;
            } else { // 新增数据
                $data['img'] = $img;
                $data['system_time']=time();
                $data['type']= $type;
                return $this->model->isUpdate(false)->save($data);
            }
        }
        else {
            if($type <= 2) {  //充值积分商品
                $name = 'cart_recharge';
            }else{
                $name = 'cart_consume';
            }
            $all_item = json_decode(cookie("{$name}"), TRUE);
            $spec_id = uniqid();
            if(count($all_item) > 0) { //购物车已有商品
                $in = true;
                foreach($all_item as $kk => $vv) {
                    if($sku_id == $vv['sku_id'] && $spec == $vv['spec']) {
                        $all_item[$kk]['nums'] = $nums; // 这里只添加最新的数量数据，不叠加
                        $in = false;
                    }
                }
                if($in) {
                    $all_item[$spec_id] = array(
                        'id' => $spec_id,
                        'sku_id' => $sku_id,
                        'nums'   => $nums,
                        'spec'   => $spec,
                        'img'    => $img,
                        'type'   => $type,
                        'system_time' => time(),
                        'clientip' => getip()
                    );
                }
            }else{
                $all_item[$spec_id] = array(
                    'id' => $spec_id,
                    'sku_id' => $sku_id,
                    'nums'   => $nums,
                    'spec'   => $spec,
                    'img'    => $img,
                    'type'   => $type,
                    'system_time' => time(),
                    'clientip' => getip()
                );
            }
            //cookie('cart_nums', count($all_item));
            cookie("{$name}", json_encode($all_item));
            return $all_item;
        }
    }
    // 库存判断
    public function is_stock($sku_id, $nums){
        //库存验证
        $sku_info = db('goods_sku')->where(array('sku_id' => $sku_id))->column('sku_id, number');
        if(!$sku_info){
            $this->errors = lang('goods_goods_not_exist');
            return false;
        }
        if($sku_info[$sku_id] < $nums){
            $this->errors = lang('goods_stock_insufficient');
            return false;
        }
        return true;
    }


    /**
     * 设置购物车商品数量
     * @param int $sku_id 	商品sku_id
     * @param int $specid  购物车ID
     * @param int $nums 	数量
     * @param int $buyer_id 会员id(游客为0 ,默认0)
     * @return [boolean]
     */
    public function set_nums($sku_id ,$specid,$nums ,$buyer_id = 0, $type) {
        $sku_id = (int) $sku_id;
        $nums = max(0, (int) $nums);
        $buyer_id = (int) $buyer_id;
        if ($sku_id < 1) {
            $this->errors = lang('goods_goods_not_exist');
            return FALSE;
        }

        //库存验证
        $sku_info=db('goods_sku')->where(['sku_id'=>$sku_id])->column('sku_id,number');


        if(!$sku_info){
            $this->errors = lang('goods_goods_not_exist');
            return FALSE;
        }

        if($sku_info[$sku_id] < $nums){
            $this->errors = lang('goods_stock_insufficient');
            return FALSE;
        }
        if($buyer_id > 0) {
            $set_info = $this->model->where("id = {$specid}")->find();
            if (!$set_info) {
                $this->errors = lang('goods_goods_not_exist');
                return FALSE;
            }
            if ($nums == 0) {	// 删除该记录
                $result = $this->delpro($specid ,$buyer_id, $type);
                if (!$result) return FALSE;
            } else {
                $data['nums']        = $nums;
                $data['system_time'] = time();
                $result = $this->model->where("id = {$specid}")->setField($data);
                if (!$result) {
                    $this->errors = lang('_operation_fail_');
                    return FALSE;
                }
            }
            return $result;
        }else{
            if($type <= 2) {
                $name = 'cart_recharge';
            }else{
                $name = 'cart_consume';
            }
            $all_item = json_decode(cookie("{$name}"), TRUE);
            if (!$all_item) return FALSE;
            $all_item[$specid]['nums'] = $nums;
            cookie("{$name}", json_encode($all_item));
            return $all_item;
        }

    }


    /**
     * 减除相应购买数量
     * @param  array $params  参数 ：array('sku_id1' => number1 [,'sku_id2' => number2])
     * @param  int   $buyer_id 会员id(游客为0)
     * @return [boolean]
     */
    public function dec_nums($params = [] ,$buyer_id = 0) {
        if (empty($params)) {
            $this->errors = lang('_param_error_');
            return FALSE;
        }
        foreach($params as $specid => $v) {
            $nums[$specid] = $v['nums'];
        }

        $spec_ids = join(',', array_keys($params));
        $carts = $this->model->where("buyer_id = {$buyer_id} and id in ({$spec_ids})")->field('id,sku_id,nums')->select()->toArray();
        foreach ($carts as $k => $v) {
            $this->set_nums($v['sku_id'] ,$v['id'], ($v['nums'] - $nums[$v['id']]), $buyer_id);
        }
        return TRUE;
    }


    /**
     * 删除购物车商品
     * @param  int $sku_id 商品sku_id
     * @param  int $buyer_id 会员id(游客为0 ,默认0)
     * @return [boolean]
     */
    public function delpro($spec_id ,$buyer_id = 0, $type) {
        $buyer_id = (int) $buyer_id;
        if (!$spec_id) {
            $this->errors = lang('goods_goods_not_exist');
            return FALSE;
        }
        if ($buyer_id > 0) {
            $sqlmap = [];
            $sqlmap['id'] = $spec_id;
            $result = $this->model->where($sqlmap)->delete();
            if ($result == FALSE) {
                $this->errors = lang('_operation_fail_');
                return FALSE;
            }
        } else{
            if($type <= 2) {
                $name = 'cart_recharge';
            }else{
                $name = 'cart_consume';
            }
            $all_item = json_decode(cookie("{$name}"), TRUE);
            if (!$all_item) return FALSE;
            unset($all_item[$spec_id]);
            cookie("{$name}", json_encode($all_item));
        }
        return TRUE;
    }

    public function getOne($where) {
        return $this->model->getOne($where);
    }

}
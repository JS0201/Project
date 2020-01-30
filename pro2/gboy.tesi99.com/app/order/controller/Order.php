<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\order\controller;
use app\member\controller\Check;
use app\member\service\Address;
use app\goods\service\Sku;

class Order extends Check{

    public function _initialize()
    {

        parent::_initialize();
        $this->cart_service = model('order/Cart', 'service');
        $this->service = model('order/order', 'service');
        $this->spec_service = model('goods/Spec', 'service');
    }


    public function index(){


    }


    /**
     * 创建订单
     * @param 	array
     * @return  [boolean]
     */
    public function create() {
        // 会员收货地区id，便于加载配送物流
        $request = input('post.');
        $result =  $this->service->temp_creates($this->member, $request['params'], $request['addressid'], $request['remark'], 1,[], true, $request['isbuy']);
        if (!$result || isset($this->service->NotEnough)) {
            isset($this->service->NotEnough) ? showmessage($this->service->errors, 1) : showmessage($this->service->errors);
        }
        showmessage(lang('order_create_success','order/language'), url('/member/index/allorder'), 1,'json');
    }

    //确认订单
    public function confirm()
    {
        header("Cache-control:no-cache,no-store,must-revalidate");
        header("Pragma:no-cache");
        header("Expires:0");

        $request = input('get.');
        foreach($request as $k => $v) {
            if(empty($v)) {
                showmessage(lang('_param_error_'));
                break;
            }
        }
        //默认地址
        $addressService = new Address();
        $address = $addressService->getDef();
        $good = $list = array();
        $skuService = new Sku();
        if(isset($request['spec_ids']) && !empty($request['spec_ids'])) { //购物车确认订单
            $ids = explode(',', $request['spec_ids']);
            foreach($ids as $k => $v) {
                $cartArr = $this->cart_service->getOne(array('id' => $v));
                if(!$cartArr) {
                    $this->redirect("/goods/index/index");
                }
                $skuArr = $skuService->getGood(array("sku_id" => $cartArr['sku_id']));
                $skuArr['price'] = $cartArr['nums'] * $skuArr['shop_price'];
                $skuArr['sum'] = $cartArr['nums'];
                $skuArr['spec_id'] = $v;
                foreach(json_decode($cartArr['spec'], true) as $kk => $vv) {
                    $skuArr['message'] .= $kk.':'.$vv.'&nbsp;&nbsp';
                }
                $skuArr['thumb'] = $cartArr['img'];
                $list[$k] = $skuArr;
                if(isset($good['express'])) {
                    $good['express'] += $skuArr['express'];   //运费待整改
                }else{
                    $good['express'] = $skuArr['express'];
                }
                if(isset($good['price'])) {
                    $good['price'] += $skuArr['price'];
                }else{
                    $good['price'] = $skuArr['price'];
                }
            }
        } else { //单个产品直接购买
            $spuid = $request['spu_id'];
            $skus = db("goods_sku")->where(array('spu_id'=>$spuid,'spec'=>$request['message']))->find();
            $spu = db("goods_spu")->where(array('id'=>$spuid))->find();

            $sku = $skuService->getGood(array('sku_id' => $skus['sku_id']));
            $good['thumb'] = config("aliyun_oss.Url").$spu['thumb'];
            $good['sku_name'] = $sku['sku_name'];
            $good['sum'] = $request['sum'];
            $good['shop_price'] = $sku['shop_price'];
            $good['price'] = $request['sum'] * $sku['shop_price'];
            $good['sku_id'] = $skus['sku_id'];
            $good['express'] = $sku['express'];
            $msg = json_decode($request['message'], true);
            $message = '';
//            foreach($msg as $k => $v) {
//                $message .= $k.':'.$v.'&nbsp;&nbsp;';
//                $img = $this->spec_service->getImg($spuid,$k,$v);
//                if($img) {
//                    $good['thumb'] = $img;
//                }
//            }
            $good['message'] = $msg;
        }

        $this->assign('list', $list);
        $this->assign('good', $good);
        $this->assign('address', $address);
        return $this->fetch();
    }
    public function deleted()
    {
        $sn = input('post.sn');
        $action = input('post.action');
        $status = input('post.status');
        $re = $this->service->set_order($sn, $action, $status);
        if(!$re) {
            showmessage($this->errors);
        }
        showmessage(lang('_operation_success_'),'',1,'json');
    }

    public function pay()
    {
        $sn = input('post.sn');
        $action = input('post.action');
        $status = input('post.status');
        $re = $this->service->set_order($sn, $action, $status);
        if(!$re) {
            showmessage($this->errors);
        }
        showmessage(lang('_operation_success_'),'',1,'json');
    }
    public function sureget()
    {
        $sn = input('post.sn');
        $action = input('post.action');
        $status = input('post.status');
        $re = $this->service->set_order($sn, $action, $status);
        if(!$re) {
            showmessage($this->errors);
        }
        showmessage(lang('_operation_success_'),'',1,'json');
    }

}
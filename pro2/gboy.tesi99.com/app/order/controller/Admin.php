<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\order\controller;
use app\common\controller\Init;

class Admin extends Init{

    public function _initialize()
    {
        parent::_initialize();
        $this->service = model('order/Order','service');
        $this->order_log_service = model('order/Log','service');
    }


    public function index(){

        $sqlmap = $this->service->build_sqlmap(input('get.'));
        $list=$this->service->get_list($sqlmap);
        $this->assign('list',$list);
        return $this->fetch();
    }



    public function detail(){
        // 限制商户查询
        if(ADMIN_ID != 1){
            $sqlmap['admin_id'] = ADMIN_ID;
        }
        $sn=input('sn/s');
        $sqlmap['sn'] = $sn;
        $order = $this->service->get_find($sqlmap);
        if (!$order) showmessage(lang('order_not_exist'));
        $order_logs = $this->order_log_service->get_by_order_sn($order['sn'],'id DESC');

        //查询商品信息
        $sku = db('order_sku')->where(array('order_sn' => $order['sn']))->order('id asc')->select();
        foreach ($sku as $k => $v) {
            $spec = (array)json_decode($v['sku_spec']);
            $sku[$k]['sku_spec'] = $spec;
        }
        $this->assign('order',$order)->assign('order_logs',$order_logs)->assign('sku_info',$sku);
        return $this->fetch();
    }


    /* 确认付款 */
    public function pay() {
        if (is_post()) {
            $params = [];
            $params['pay_time'] = remove_xss(strtotime(input('pay_time')));
            $params['paid_amount']= sprintf("%0.2f", (float) input('paid_amount'));
            $params['pay_method'] = remove_xss(input('pay_method'));
            $params['pay_sn']     = remove_xss(input('pay_sn'));
            $params['msg']        = remove_xss(input('msg'));
            if ($params['pay_method'] != 'other' && !$params['pay_sn']) {
                showmessage(lang('pay_deal_sn_empty'));
            }
            $result = $this->service->set_order(input('sn') ,'pay' ,'',$params);
            if (!$result) showmessage($this->service->errors);
            showmessage(lang('pay_success'),'',1,'json');
        } else {
            // 获取所有已开启的支付方式
            $pays = model('pay/Payment','service')->get_list();
            foreach ($pays as $k => $pay) {
                $pays[$k] = $pay['pay_name'];
            }
            $pays['other'] = '其它付款方式';
            $order = $this->service->get_find(array('sn' => input('sn')));
            $this->assign('pays',$pays)->assign('order',$order);
            return $this->fetch('alert_pay');

        }
    }

    /* 确认订单 */
    public function confirm() {
        if (is_post()) {
            $result = $this->service->set_order(input('sn') ,'confirm' ,'',['msg' => input('msg')]);
            if (!$result) showmessage($this->service->errors);
            showmessage('确认订单成功','',1,'json');
        } else {
           return  $this->fetch('alert_confirm');
        }
    }

    /* 确认发货 */
    public function delivery() {
        if (is_post()) {
            $params = array();
            $params['is_choise']     = remove_xss(input('is_choise'));
            $params['delivery_id']   = remove_xss(input('delivery_id'));
            $params['delivery_sn']   = remove_xss(input('delivery_sn'));
            $params['sn']   	 = remove_xss(input('sn'));
            $params['msg']           = remove_xss(input('msg'));
            $result = $this->service->set_order(input('sn') ,'delivery' ,$_GET['status'],$params);
            if (!$result) showmessage($this->service->errors);
            showmessage('确认发货成功','',1,'json');
        } else {
            // 获取已开启的物流
            $sqlmap = $deliverys = [];
            $sqlmap['enabled'] = 1;
            $deliverys = db('delivery')->where($sqlmap)->column('id,name');
            $this->assign('deliverys',$deliverys);
            return $this->fetch('alert_delivery');
        }
    }



    /* 确认完成 */
    public function finish() {
        if (is_post()) {
            $result = $this->service->set_order(input('sn') ,'finish' ,'',['msg'=>input('msg')]);
            if (!$result) showmessage($this->service->errors);
            showmessage('确认完成成功','',1,'json');
        } else {
            return $this->fetch('alert_finish');
        }
    }


    /* 取消订单 */
    public function cancel() {
        if (is_post()) {
            $result = $this->service->set_order(input('sn') ,'order' ,2,['msg'=>input('msg'),'isrefund' => 1]);
            if (!$result) showmessage($this->service->errors,'',0,'json');
            showmessage(lang('cancel_order_success'),'',1,'json');
        } else {
            $order = $this->service->get_find(array('sn' => input('sn')));

            $this->assign('order',$order);
            return $this->fetch('alert_cancel');
        }
    }


    /* 作废 */
    public function recycle() {
        if (is_post()) {
            $result = $this->service->set_order(input('sn') ,'order' ,3,['msg'=>input('msg')]);
            if (!$result) showmessage($this->service->errors);
            showmessage(lang('cancellation_order_success'),'',1,'json');
        } else {

            return $this->fetch('alert_recycle');
        }
    }



}
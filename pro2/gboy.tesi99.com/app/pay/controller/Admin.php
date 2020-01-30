<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\pay\controller;
use app\common\controller\Init;

class Admin extends Init{

    public function _initialize()
    {
        parent::_initialize();
        $this->service = model('pay/Payment','service');
        $this->payments = $this->service->fetch_all();
    }




    public function index(){
        $payments=$this->payments;

        $this->assign('payments',$payments);
        return $this->fetch();
    }



    public function config(){

        $pay_code=input('code');

        if(!$payment = $this->payments[$pay_code]){
            showmessage(lang('pay_code_require'));
        }

        if(is_post()){
            $data=$payment;
            $data['config']=json_encode(input('post.config/a'));

            if(!$this->service->config($data)){
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'),1);
        }else{

            $this->assign('payment',$payment);
            return $this->fetch();
        }

    }


    public function uninstall() {
        $code = input('code');
        $this->service->del($code);
        showmessage(lang('pay_uninstall_success'), url('index'), 1);
    }

}
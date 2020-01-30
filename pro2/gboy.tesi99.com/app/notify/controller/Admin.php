<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\notify\controller;
use app\common\controller\Init;

class Admin extends Init{

    public function _initialize()
    {

        parent::_initialize();
        $this->service = model('notify/Notify','service');
        $this->temlage_service = model('notify/Template','service');
    }




    public function index(){

        $notifys = $this->service->fetch_all();


        $this->assign('notifys',$notifys);
        return $this->fetch();
    }


    public function config(){

        if(false === $notify = $this->service->fetch_code(input('code'))) {

            showmessage($this->service->errors);
        }


        if(is_post()){

            if(!$this->service->config(input('post.vars/a'),input('code/s'))){
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'),1);
        }else{


            $_setting = $this->service->get_fech_all();
            $_config = $_setting[input('code')]['configs'];


            $this->assign('notify',$notify)->assign('_config',$_config);
            return $this->fetch();
        }

    }


    public function template(){

        $code=input('code');
        if(!$notify = $this->service->fetch_code($code)) {
            showmessage($this->service->errors);
        }
        $hooks = [
            'after_register'=>'注册成功',
            'register_validate'=>'注册验证',
            'mobile_validate'=>'手机验证',
            'email_validate'=>'邮箱验证',
            'forget_pwd'=>'找回密码',
            'money_change'=>'余额变动',
            'recharge_success'=>'充值成功',
            'order_delivery'=>'订单发货',
            'confirm_order'=>'确认订单',
            'pay_success'=>'付款成功',
            'create_order'=>'下单成功',
            'goods_arrival'=>'商品到货',
        ];


        //过滤不相干选项
        $ignore = explode(',', $notify['ignore']);
        foreach($ignore as $k=>$v){
            unset($hooks[$v]);
        }

        $replace = $this->service->template_replace();

        if(is_post()){

            $template = [];
            foreach ($hooks as $k => $v) {
                $template[$k] = str_replace($replace,array_keys($replace),input($k.'/a'));
            }


            $data=input('post.');

            $data['template']=json_encode($template,256);



            $result = $this->temlage_service->edit($data);
            if($result===false){
                showmessage($this->template_service->errors);
            }
            showmessage(lang('upload_message_success'),url('index'),1);

        }else{

            $template = $this->temlage_service->fetch_code($code)->toArray();
            foreach ($template['template'] as $k => $temp) {
                $template['template'][$k] = str_replace(array_keys($replace),$replace,$temp);
            }
            //单独处理短信
            if($code=='sms'){
                $sms_tpl=db('sms_tpl')->field('type')->group('type')->column('type');
                $template_tpl=[];
                foreach($sms_tpl as $k=>$v){
                    $template_tpl[$v]=db('sms_tpl')->where(['type'=>$v])->select();
                }

                $template=array_merge($template,$template_tpl);
            }


            $this->assign('hooks',$hooks)->assign('notify',$notify)->assign('template',$template);
            return $this->fetch();

        }



    }


    public function uninstall() {
        $code = input('code');
        $this->service->del($code);
        $this->temlage_service->del($code);
        showmessage(lang('uninstall_success'), url('index'), 1);
    }

}
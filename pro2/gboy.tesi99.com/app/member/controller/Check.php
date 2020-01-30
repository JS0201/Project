<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\controller;
use app\common\controller\Base;

class Check extends Base{


    public function __construct()
    {

        parent::__construct();

         if($this->member['id'] < 1){

             if(is_ajax()){
                  showmessage(lang('_not_login_'),url('member/publics/login'));
             }else{

                 $url_forward = input('url_forward') ? input('url_forward') : request()->server('REQUEST_URI');
                 $this->redirect('member/publics/login',['url_forward'=>$url_forward],302);

             }

         }

    }



}
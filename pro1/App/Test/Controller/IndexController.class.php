<?php

namespace Test\Controller;

use Common\Controller\BaseController;

class IndexController extends BaseController {

    public function _initialize(){
        parent::_initialize();
       /* $this->goods_spu_service = D('Goods/Goods_spu', 'Service');
        $this->goods_sku_service = D('Goods/Goods_sku', 'Service');
        $this->spu_service = D('Goods/Goods_spu', 'Service');
        $this->sku_service = D('Goods/Goods_sku', 'Service');*/
    }

    
    public function user(){
        $this->display();
    }

    // 登录
    public function login(){
      $this->display();
      }

    // 注册  
    public function register(){
      $this->display();
    }

   

    // 发现-
    public function search(){
        $this->display();
    }
    
    // 提币
    public function formtb(){
        $this->display();
    }
    
    //修改资金密码
    public function myzijinPwd(){
        $this->display();
    }
    //修改登录密码 
    public function myloginPwd(){
        $this->display();
    }
    
    //充值 
    public function myRecharge(){
        $this->display();
    }
    




    // 首页 DAPP
    public function index(){
        $this->display();
    }
     // c2c
    public function c2c(){
      $this->display();
    }
    // 我的-
    public function myIdex(){
        $this->display();
    }
    //任务
    public function task(){
        $this->display();
    }
    //充值记录
    public function formCzRecord(){
        $this->display();
    }
    //提现记录 
    public function formtxRecord(){
        $this->display();
    }
    //提现记录详情
    public function txdetail(){
        $this->display();
    }
    // 商家申请
    public function myapplication(){
        $this->display();
    }
    // 我的团队
    public function myTeam(){
        $this->display();
    }
    // 分享
    public function myshare(){
        $this->display();
    }
    // 奖励明细
    public function myReward(){
        $this->display();
    }
    //个人设置
    public function mySetting(){
        $this->display();
    }
    //实名认证    
    public function myCertification(){
        $this->display();
    }
    //微信收款
    public function myWechat(){
        $this->display();
    }
    //支付宝收款
    public function myAlipay(){
        $this->display();
    }




    public function lang(){
       echo  cookie('think_language');
        echo   L("ComingSoon");
         $url=__SELF__;
        $this->assign('url', $url);
        $this->display();

        $this->display();
    }
}

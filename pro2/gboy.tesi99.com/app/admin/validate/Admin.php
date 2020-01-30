<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\admin\validate;
use think\Validate;

class Admin extends Validate{

    protected $rule = [
        ['code'  ,'require|checkCode','{%code_not_exist}|{%code_checked_error}'],
        ['username'  ,'require','{%username_not_exist}'],
        ['password'  ,'require','{%password_not_exist}'],
    ];



    protected function checkCode($value,$rule,$data){

        $captcha = new \think\captcha\Captcha();
        $result=$captcha->check($value);
        return $result;

    }



}

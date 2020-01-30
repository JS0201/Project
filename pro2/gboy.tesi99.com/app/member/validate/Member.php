<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\validate;
use think\Validate;

class Member extends Validate {

    protected $rule = [

        ['username'  ,'require|length:3,15|unique:member','{%username_length_require}|{%username_length_require}|{%username_exist}'],
        ['password'  ,'require|min:3,16','{%password_length_require}'],
        ['group_id'  ,'require','{%group_id_require}'],

    ];


    protected $scene = [
        'edit' => ['id' => 'require','group_id'=>'require','password' => 'requireWith:password|length:3,16'],
    ];

}
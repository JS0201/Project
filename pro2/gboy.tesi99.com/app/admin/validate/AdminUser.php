<?php

/**
 * gboyshop
 * ============================================================================
 * 版权所有 2010-2020 gboyshop，并保留所有权利。
 * ============================================================================
 * Author: gboy
 * Date: 2017/11/4
 */

namespace app\admin\validate;

use think\Validate;

class AdminUser extends Validate
{


    protected $rule = [
        'username' => 'require|length:6,16|unique:admin_user',
        'password' => 'require|length:6,16',

    ];


    protected $message = [
        'username.require' => '{%username_not_exist}',
        'username.length' => '{%username_error_length}',
        'username.unique' => '{%user_name_exist}',
        'password.require' => '{%password_not_exist}',
        'password.length' => '{%password_error_length}',
    ];

    protected $scene = [
        'edit' => ['id' => 'require','password' => 'requireWith:password|length:6,16'],
    ];


}
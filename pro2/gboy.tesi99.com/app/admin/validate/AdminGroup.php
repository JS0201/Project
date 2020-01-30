<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/21
 * Time: 20:32
 */

namespace app\admin\validate;
use think\Validate;

class AdminGroup extends Validate
{

    protected $rule = [
        'title' => 'require',
        'description' => 'require',
        'rules' => 'require',

    ];

    protected $message = [
        'title.require' => '{%role_name_require}',
        'description.require' => '{%role_description_require}',
        'rules.require' => '{%rules_admin_group_require}',

    ];

}
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用行为扩展定义文件
return [
    'registerInit'      =>'app\\member\\hook\\Hook',
    'afterRegister'      =>'app\\member\\hook\\Hook',
    'checkCart'        => 'app\\member\\hook\\Hook',
    'deposit'          =>  'app\\member\\hook\\Hook'
];


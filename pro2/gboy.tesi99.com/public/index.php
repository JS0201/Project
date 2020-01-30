<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
header("Content-type:text/html;charset=utf-8");
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.4.0', '<'))
    die('require PHP > 5.4.0 !');

// 定义应用目录
define('PUBLIC_PATH', __DIR__ . '/../public/');
define('APP_PATH', __DIR__ . '/../app/');
define('SESSION_PATH', __DIR__.'/../data/session/');
define('RUNTIME_PATH', __DIR__.'/../data/runtime/');

// 加载框架引导文件
require_once dirname(__DIR__) . '/extend/org/aliyun_oss/autoload.php';
require dirname(__DIR__) . '/thinkphp/start.php';



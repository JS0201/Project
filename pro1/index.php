<?php


header("Content-type:text/html;charset=utf-8");
// 应用入口文件
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<'))
    die('require PHP > 5.3.0 !');
//error_reporting(E_ALL);
// 调试模式
define('APP_DEBUG', true);//false

define('LANG_PATH',      'Lang/');
// 定义应用目录
define('APP_PATH', 'App/');

// 定义应用目录
define('ROOT_PATH', __DIR__);

//定义编译目录
define('RUNTIME_PATH', 'data/runtime/');


// 引入ThinkPHP入口文件
require './App/Core/ThinkPHP.php';



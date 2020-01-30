<?php

use think\Loader;


/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @param bool $ucfirst 首字母是否大写（驼峰规则）
 * @return string
 */
function parse_name($name, $type = 0, $ucfirst = true)
{
    return Loader::parseName($name, $type, $ucfirst);
}


/**
 * 获取插件类的类名
 * @param string $name 插件名
 * @return string
 */
function get_plugin_class($name)
{

    $name      = ucwords($name);
    $plugin_dir = parse_name($name);

    $class     = "plugins\\{$plugin_dir}\\{$name}";
    return $class;
}


?>
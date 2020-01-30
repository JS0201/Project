<?php
namespace plugins\sysinfo;	// 注意命名空间规范

use think\Addons;

/**
 * 插件测试
 * @author byron sampson
 */
class Sysinfo extends Addons	// 需继承think\addons\Addons类
{

    public $has_admin=0;

	// 该插件的基础信息
    public $info = [
        'name' => 'sysinfo',	// 插件标识
        'title' => '系统信息',	// 插件名称
        'description' => '后台首页信息显示',	// 插件简介
        'status' => 0,	// 状态
        'author' => 'gboy',
        'version' => '0.1'
    ];

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 实现的testhook钩子方法
     * @return mixed
     */
    public function sysinfohook($param)
    {

        return $this->fetch('sysinfo');
    }

}
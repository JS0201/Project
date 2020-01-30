<?php
namespace plugins\maintain;	// 注意命名空间规范

use think\Addons;

/**
 * 插件测试
 * @author byron sampson
 */
class Maintain extends Addons	// 需继承think\addons\Addons类
{

    public $has_admin=0;

	// 该插件的基础信息
    public $info = [
        'name' => 'maintain',	// 插件标识
        'title' => '日常维护',	// 插件名称
        'description' => '后台首页日常维护',	// 插件简介
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
    public function maintainhook()
    {



        //安全检测
        $system_safe = true;
        //调试模式
        $danger_mode_debug = config('app_debug');
        if ($danger_mode_debug) {
            $system_safe = false;
        }
        $this->assign('danger_mode_debug',$danger_mode_debug);

        //数据库密码
        $weak_setting_db_password = false;
        if( password_strength(config('database.password'))<=5){
          $weak_setting_db_password = true;
        }



        $this->assign('weak_setting_db_password',$weak_setting_db_password);


        $this->admin = model('admin/Admin','service')->init();


        //密码修改时间
        $weak_setting_admin_last_change_password = ($this->admin['admin_changepwd'] < time() - 3600 * 24 * 30 );
        if ($weak_setting_admin_last_change_password) {
            $system_safe = false;
        }
        $this->assign('weak_setting_admin_last_change_password',$weak_setting_admin_last_change_password);



        //计算机名称授权
        $weak_auth_computer_name=config('cache.auth_computer_name');
        if(!$weak_auth_computer_name){
            $system_safe=false;
        }
        $this->assign('weak_auth_computer_name',$weak_auth_computer_name);



        //整体安全性
        $this->assign('system_safe',$system_safe);

        $this->assign('system_pageshow',config('app_trace'));
        //日志分析
        $log_size = 0;
        $log_file_cnt = 0;
        foreach (list_file(LOG_PATH) as $f) {
            if ($f ['isDir']) {
                foreach (list_file($f ['pathname'] . '/', '*.log') as $ff) {
                    if ($ff ['isFile']) {
                        $log_size += $ff ['size'];
                        $log_file_cnt++;
                    }
                }
            }
        }
        $this->assign('log_size',$log_size);
        $this->assign('log_file_cnt',$log_file_cnt);
        return $this->fetch('maintain');
    }

}
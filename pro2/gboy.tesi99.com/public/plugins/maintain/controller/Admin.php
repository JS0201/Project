<?php
namespace plugins\maintain\controller;
use think\Cache;
use app\common\controller\Init;
class Admin extends Init
{

    public function _initialize()
    {
        parent::_initialize();

    }

    public function maintain(){

        $action=input('action');
        switch ($action) {
            case 'download_log' :
            case 'view_log':
                $logs = [];
                foreach (list_file(LOG_PATH) as $f) {
                    if ($f ['isDir']) {
                        foreach (list_file($f ['pathname'] . '/', '*.log') as $ff) {
                            if ($ff ['isFile']) {
                                $spliter = '==========================';
                                $logs [] = $spliter . '  ' . $f ['filename'] . '/' . $ff ['filename'] . '  ' . $spliter . "\n\n" . file_get_contents($ff ['pathname']);
                            }
                        }
                    }
                }
                if ('download_log' == $action) {
                    force_download_content('log_' . date('Ymd_His') . '.log', join("\n\n\n\n", $logs));
                } else {
                    echo '<pre>' . htmlspecialchars(join("\n\n\n\n", $logs)) . '</pre>';
                }
                break;
            case 'clear_log' :

                remove_dir(LOG_PATH);

                showmessage('清除日志成功',url('admin/index/main'),1);
                break;
            case 'debug_on' :
                $data = ['app_debug'=>true];
                $res=set_conifg($data);
                if($res === false){
                    showmessage('打开调试失败');
                }else{
                    model('admin/Site','service')->submit(['web_app_debug'=>1], 'cache.php');
                    Cache::clear();
                    showmessage('已打开调试',url('admin/index/main'),1);
                }
                break;
            case 'debug_off' :
                $data = ['app_debug'=>false];
                $res=set_conifg($data);
                if($res === false){
                    showmessage('关闭调试失败');
                }else{
                    model('admin/Site','service')->submit(['web_app_debug'=>0], 'cache.php');
                    Cache::clear();
                    showmessage('已关闭调试',url('admin/index/main'),1);
                }
                break;

            case 'trace_on' :
                $data = ['app_trace'=>true];
                $res=set_conifg($data);
                if($res === false){
                    showmessage('打开Trace失败');
                }else{
                    model('admin/Site','service')->submit(['web_app_trace'=>1], 'cache.php');
                    Cache::clear();
                    showmessage('已打开Trace',url('admin/index/main'),1);
                }
                break;
            case 'trace_off' :
                $data = ['app_trace'=>false];
                $res=set_conifg($data);
                if($res === false){
                    showmessage('打开Trace失败');
                }else{
                    model('admin/Site','service')->submit(['web_app_trace'=>0], 'cache.php');
                    Cache::clear();
                    showmessage('已关闭Trace',url('admin/index/main'),1);

                }
                break;


        }

    }


}
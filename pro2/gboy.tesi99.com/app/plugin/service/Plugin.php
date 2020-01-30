<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\plugin\service;
use app\ninestar\model\Init;
use think\Model;
use think\Db;
use think\Validate;
class Plugin extends Model{

    protected $entrydir = '';

    public function initialize()
    {
        $this->entrydir = ADDON_PATH;
        $this->model = model('plugin/Plugin');
        $this->plugin_hook_model = model('plugin/PluginHook');
        $this->plug_model = model('plugin/Plugin');
    }


/*    public function fetch_all() {
        $folders = glob($this->entrydir.'*');
        foreach ($folders as $key => $folder) {
            $dir_arr=explode(DS,$folder);
            $name=end($dir_arr);
            $plugin_class = get_plugin_class($name);
            if(class_exists($plugin_class)) {
                $icon=is_file($this->entrydir.$name.DS.'icon.png');
                if(!$icon){
                    $icon=__ROOT__.'static/images/plugins.png';
                }
                $obj = new $plugin_class;
                $plugins[$name] = $obj->info;
                $plugins[$name]['icon'] = $icon;
            }
        }
        $plugins = array_merge_multi($plugins, $this->get_fech_all());
        return $plugins;
    }*/

    public function fetch_all() {
        return $this->model->getList();
    }

    public function get_fech_all() {
        $result = [];

        $plugins = $this->model->column('name,title,status,config,description,has_admin');
        foreach ($plugins as $key => $value) {
            $value['config'] = json_decode($value['config'], TRUE);
            $result[$value['name']] = $value;
        }
        return $result;
    }





    public function fetch_name($name) {

        if(!$plugin=$this->model->where(['name'=>$name])->find()){
            $this->errors=lang('plugin_not_exist');
            return false;

        }

        $plugin_class = get_plugin_class($plugin['name']);

        if (!class_exists($plugin_class)) {

            $this->errors=lang('plugin_not_exist');
            return false;
        }

        $plugin_obj = new $plugin_class;


        $plugin['config'] = include $plugin_obj->config_file;

        return $plugin;
    }



    /*public function install($name) {

        $plugin_name=get_plugin_class($name);

        if(!class_exists($plugin_name)){
            $this->errors = lang('plugin_not_exist');
            return false;
        }

        if($this->model->where(['name'=>$name])->count()>0){
            $this->errors = lang('plugin_repeat_install');
            return false;
        }

        $plugin_class=new $plugin_name;
        $info=$plugin_class->info;

        if (!$info || !$plugin_class->checkInfo()) {
            $this->errors=lang('plugin_info_error');
            return false;
        }

        if (!$plugin_class->install()) {
            $this->errors=lang('plugin_pre_install_error');
            return false;
        }


        // 执行安装插件sql文件
        $sql_file = realpath(ADDON_PATH.$name.'/install.sql');
        if (file_exists($sql_file)) {
            db_restore_file($sql_file);
        }

        //$methods=get_class_methods($plugin_class);

        //添加钩子
        $this->plugin_hook_model->save(['plugin'=>$name,'hook'=>$name,'status'=>1,'sort'=>100]);


        if (!empty($plugin_class->has_admin)) {
            $info['has_admin'] = 1;
        } else {
            $info['has_admin'] = 0;
        }



        $icon=is_file($this->entrydir.$name.DS.'icon.png');
        if(!$icon){
            $icon=__ROOT__.'static/images/plugins.png';
        }
        $info['icon']=$icon;
        $info['config'] = json_encode($plugin_class->getConfig());
        $info['datetime']=time();
        $info['status']=1;
        $this->model->allowField(true)->data($info)->save();
        $this->sethook();

        return true;

    }*/

    public function install() {
        $name = $_GET['name'];
        $zipfile = ROOT_PATH.'public/plugins/'.$name.'.zip';
        if(!is_file($zipfile)) {
            $this->errors='插件不存在';
            return false;
        }

        if(is_file(APP_PATH.$name)) {
            $this->errors='插件已安装';
            return false;
        }
        copy($zipfile, APP_PATH.$name.'.zip');
        $zip = new \ZipArchive();
        if ($zip->open(APP_PATH.$name.'.zip')) {
            $zip->extractTo(APP_PATH);//解压文件到/tmp/zip/文件夹下面
            $zip->close();
            unlink(APP_PATH.$name.'.zip');
        }else{
            $this->errors='解压失败';
            return false;
        }
        if(is_file(ROOT_PATH.'public/plugins/sql/'.$name.'.sql')) {
            $init = new Init();
            Db::query("drop database if exists {$name};");
            Db::query("create database {$name};");
            $sql_array = $_arr = explode(';', file_get_contents(ROOT_PATH.'public/plugins/sql/'.$name.'.sql'));
            foreach($sql_array as $k => $v) {
                if(!empty($v)) {
                    $init->db->query($v.';');
                }
            }
        }
        //生成配置
        $service = model('admin/Site', 'service');
        $service->conf('plug.php',[$name=>2]);
        $this->plug_model->updated(['name'=>$name],['status' => 1]);
        return true;
    }


    public function config($vars, $name) {

        $plugin = $this->fetch_name($name);
        if($plugin === false) {
            $this->errors=$this->errors;
            return false;
        }

        $name_class=get_plugin_class($name);
        $plugin_obj = new $name_class;

        $plugin['config'] = include $plugin_obj->config_file;


        $rules    = [];
        $messages = [];

        foreach ($plugin['config'] as $key => $value) {
            if ($value['type'] != 'group') {
                if (isset($value['rule'])) {
                    $rules[$key] = $this->_parseRules($value['rule']);
                }

                if (isset($value['message'])) {
                    foreach ($value['message'] as $rule => $msg) {
                        $messages[$key . '.' . $rule] = $msg;
                    }
                }

            } else {
                foreach ($value['options'] as $group => $options) {
                    foreach ($options['options'] as $gkey => $value) {
                        if (isset($value['rule'])) {
                            $rules[$gkey] = $this->_parseRules($value['rule']);
                        }

                        if (isset($value['message'])) {
                            foreach ($value['message'] as $rule => $msg) {
                                $messages[$gkey . '.' . $rule] = $msg;
                            }
                        }
                    }
                }
            }
        }




        $validate = new Validate($rules, $messages);
        $result   = $validate->check($vars);
        if ($result !== true) {
           $this->errors=$validate->getError();
           return false;
        }



        $config= json_encode($vars,256);

        $rs = $this->model->allowField(true)->save(['config'=>$config],['name'=>$name]);

        if($rs === false) {
            $this->errors = lang('config_operate_error');
            return false;
        }
        return true;
    }
    public function updated($name){

        $plugin = $this->fetch_name($name);
        if($plugin === false) {
            $this->errors=$this->errors;
            return false;
        }


        /**
         * 更新
         */

        return true;


    }



    public function uninstall($name) {


        if(!$plugin=$this->fetch_name($name)){
            $this->errors=$this->errors;
            return false;

        }

        $plugin_name=get_plugin_class($name);
        $plugin_obj = new $plugin_name;
        if(!$plugin_obj->uninstall()){
            $this->errors=lang('plugin_uninstall_fail');
            return false;
        }


        $sql_file = realpath(ADDON_PATH.$name.'/uninstall.sql');
        if (file_exists($sql_file)) {
            db_restore_file($sql_file);
        }

        $this->model->where(['name'=>$name])->delete();
        $this->plugin_hook_model->where(['plugin'=>$name])->delete();
        $this->sethook();
        return true;
    }



    public function toggle($name){

        $plugin = $this->fetch_name($name);
        if($plugin === false) {
            $this->errors=$this->errors;
            return false;
        }

        $status=(int)!$plugin['status'];

        $this->plugin_hook_model->save(['status'=>$status],['plugin'=>$plugin['name']]);

        $this->model->where(['name'=>$name])->data(['status'=>$status])->update();
        $this->sethook();

        return true;

    }




    /**
     * 解析插件配置验证规则
     * @param $rules
     * @return array
     */
    private function _parseRules($rules)
    {
        $newRules = [];

        $simpleRules = [
            'require', 'number',
            'integer', 'float', 'boolean', 'email',
            'array', 'accepted', 'date', 'alpha',
            'alphaNum', 'alphaDash', 'activeUrl',
            'url', 'ip'];
        foreach ($rules as $key => $rule) {
            if (in_array($key, $simpleRules) && $rule) {
                array_push($newRules, $key);
            }
        }

        return $newRules;
    }


    /*
     * 更新钩子文件
     *
     */
    private function sethook(){

        $hook_list=$this->plugin_hook_model->where(['status'=>1])->column('hook,plugin');

        $hooks['hooks']=$hook_list;

        file_put_contents(APP_PATH . 'extra/addons.php', "<?php\t \n\n return  " . var_export($hooks, true) . ";");



    }


}
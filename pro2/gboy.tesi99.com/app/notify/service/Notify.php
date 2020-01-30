<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\notify\service;
use think\Model;

class Notify extends Model{

    protected $entrydir = '';

    public function initialize()
    {
        $this->entrydir = APP_PATH.'notify/library/driver/';
        $this->model = model('notify/Notify');
        $this->template_model = model('notify/Template');
    }


    public function fetch_all() {
        $folders = glob($this->entrydir.'*');

        foreach ($folders as $key => $folder) {
            $file = $folder. DIRECTORY_SEPARATOR .'config.xml';
            if(file_exists($file)) {
                $importtxt = @implode('', file($file));
                $xmldata = xml2array($importtxt);
                $notifys[$xmldata['code']] = $xmldata;
            }
        }
        $notifys = array_merge_multi($notifys, $this->get_fech_all());
        return $notifys;
    }


    public function get_fech_all() {
        $result = [];

        $notifys = $this->model->column('code,enabled,config,description,name');
        foreach ($notifys as $key => $value) {
            $value['configs'] = json_decode($value['config'], TRUE);
            $result[$value['code']] = $value;
        }



        return $result;
    }



    public function fetch_code($code) {


        if(empty($code)) {
            $this->errors = lang('_param_error_');
            return false;
        }

        if(!is_dir($this->entrydir.$code) || !file_exists($this->entrydir.$code)) {
            $this->errors = lang('no_found_config_file');
            return false;
        }

        $config = $this->entrydir.$code.'/config.xml';

        $importtxt = @implode('', file($config));
        $xmldata = xml2array($importtxt);


        return $xmldata;
    }



    public function config($vars, $code) {


        $notify = $this->fetch_code($code);
        if($notify === false) {
            $this->errors=$this->errors;
            return false;
        }
        $notify['config'] = json_encode($vars,256);
        unset($notify['datetime']);
        if($this->model->where(['code'=>$code])->find()) {
            $rs = $this->model->allowField(true)->save($notify,['code'=>$code]);
        } else {

            $rs = $this->model->allowField(true)->data($notify)->save();
            $this->template_model->data(array('id'=>$code))->save();
        }
        if($rs === false) {
            $this->errors = lang('config_operate_error');
            return false;
        }
        // 生成log记录
        $name = lang('admin_log_notify_edit');
        $contetn = '修改：' . $this->model->data['name'];
        AdminLog(2, $contetn, lang("admin_log_notify"), $name, $code);

        return true;
    }



    /**
     * [template_replace 替换模版内容]
     * @return [type] [description]
     */
    public function template_replace(){
        $replace = [
            '{site_name}' => '{商城名称}',
            '{username}' => '{用户名}',
            '{mobile}' => '{用户手机}',
            '{email}' => '{用户邮箱}',
            '{goods_name}' => '{商品名称}',
            '{goods_spec}' => '{商品规格}',
            '{order_sn}' => '{主订单号}',
            '{order_amount}' => '{订单金额}',
            '{shop_price}' => '{商品金额}',
            '{real_amount}' => '{付款金额}',
            '{pay_type}' => '{支付方式}',
            '{change_money}' => '{变动金额}',
            '{delivery_type}' => '{配送方式}',
            '{delivery_sn}' => '{运单号}',
            '{user_money}' => '{用户可用余额}',
            '{recharge_money}' => '{充值金额}',
            '{email_validate}' => '{邮件验证链接}',
            '{mobile_validate}' => '{验证码}'
        ];
        return $replace;
    }



    public function del($code) {
        if(empty($code)) {
            $this->errors = lang('_param_error_');
            return false;
        }
        $result = $this->model->where(['code'=>$code])->delete();

        return true;
    }

}
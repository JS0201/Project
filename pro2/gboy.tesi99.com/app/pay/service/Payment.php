<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\pay\service;
use think\Model;

class Payment extends Model{

    public function initialize()
    {
       $this->model = model('pay/Payment');
    }


    public function fetch_all() {
        $entrydir = APP_PATH.'pay/library/driver/';

        $folders = glob($entrydir.'*');
        foreach ($folders as $key => $folder) {
            $file = $folder. DIRECTORY_SEPARATOR .'config.xml';
            if(file_exists($file)) {
                $importtxt = @implode('', file($file));
                $xmldata = xml2array($importtxt);
                $payments[$xmldata['pay_code']] = $xmldata;
                $payments[$xmldata['pay_code']]['pay_install'] = 0;
            }
        }

        $payments = array_merge_multi($payments, $this->get_fetch_all());
        return multi_array_sort($payments,'pay_name');
    }


    public function get_fetch_all() {
        $result = $this->model->column('*','pay_code');
        if($result){

            foreach ($result as $key => $value) {
                $result[$key]['config'] = json_decode($value['config'],true);
                $result[$key]['pay_install'] = 1;
            }

            return $result;

        }
    }



    /**
     * [支付方式列表]
     * @return boolean
     */

    public function get_list($key = NULl) {

        $result_enable = $this->model->where(['enabled' => 1])->column('pay_code,pay_name,pay_fee,pay_desc,enabled,config,dateline,sort,isonline,applie');
        return is_string($key) ? $result_enable[$key] : $result_enable;
    }


    public function config($data) {
        if(empty($data['pay_install'])){
            $data['enabled']=1;
            $result = $this->model->allowField(true)->data($data)->save();
        }else{
            $result = $this->model->allowField(true)->isupdate(true)->save($data);
        }
        if($result === false) {
            $this->errors = lang('_operation_error_');
            return false;
        }

        return true;
    }



    public function del($code) {
        if(empty($code)) {
            $this->errors = lang('_param_error_');
            return false;
        }
        $result = $this->model->where(['pay_code'=>$code])->delete();

        return true;
    }



}
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\ads\service;
use think\Model;

class AdsPosition extends Model{

    public function initialize()
    {
        $this->model = model('ads/AdsPosition');
    }


    /**
     * @param        $sqlmap 条件
     * @param string $field 字段
     * @return mixed
     */
    public function get_by_id($sqlmap, $field = '')
    {


        $info = $this->model->get($sqlmap);


        if($field) {
            return $info[$field];
        }

        return $info;
    }


    public function get_position($sqlmap = [],$field='id,name') {
        $advs = $this->model->where($sqlmap)->column($field);
        return $advs;
    }

    public function get_list($sqlmap=[],$order='sort asc,id asc'){

        $list=$this->model->where($sqlmap)->order($order)->select();

        return $list;
    }



    public function edit(array $data, $isupdate = false, $valid = true, $msg = []){

        $result = $this->model->except('id')->validate($valid, $msg)->isupdate($isupdate)->save($data);
        if ($result === false) {
            $this->errors = $this->model->getError();
            return false;
        }
        // 生成log记录
        $code = $isupdate ? $data['id'] : $this->model->id;
        $name = $isupdate ? lang('admin_log_ads_edit_position') :  lang('admin_log_ads_add_position');
        $contetn = $data['name'] ? $data['name'] : '';
        AdminLog(1, $contetn, lang("admin_log_ads"), $name, $code);

        return true;
    }


    /**
     * @param array $ids id主键
     * @return bool
     */
    public function del($ids){


        if(empty($ids)){

            $this->errors = lang('_param_error_');
            return false;
        }

        $_map = [];
        if(is_array($ids)) {
            $_map['id'] = array("IN", $ids);
        } else {
            $_map['id'] = $ids;
        }
        $this->model->destroy($ids);
        // 生成log记录
        $code = AdminLoginFormat($ids);
        AdminLog(1, '', lang("admin_log_ads"), lang('admin_log_ads_delete_position'), $code);

        return true;
    }


}
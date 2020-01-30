<?php
/**
 * gboyshop
 * ============================================================================
 * 版权所有 2010-2020 gboyshop，并保留所有权利。
 * ============================================================================
 * Author: gboy
 * Date: 2017/11/1
 */

namespace app\admin\service;

use think\Model;

class AdminGroup extends Model
{
    public function initialize()
    {

        $this->model = model('admin/AdminGroup');
    }



    /**
     * [获取所有团队角色]
     * @param array $sqlmap 数据
     * @return array
     */
    public function get_lists($sqlmap = [])
    {

        return $this->model->where($sqlmap)->order('id asc')->select()->toArray();

    }

    public function get_select_data()
    {
        return $this->model->where(["status" => 1, "id" => ['neq', 1]])->column('id,title');
    }


    /**
     * @param $data 数据
     * @param bool $isupdate 是否更新
     * @param bool $valid 是否验证
     * @param array $msg 验证信息
     * @return bool
     */
    public function edit($data, $isupdate = false, $valid = true, $msg = [])
    {
        $result = $this->model->except('id')->validate($valid, $msg)->isupdate($isupdate)->save($data);
        if ($result === false) {
            $this->errors = $this->model->getError();
            return false;
        }
        // 生成log记录
        $code = $isupdate ?  $data['id'] : $this->model->id;
        $name = $isupdate ? lang('admin_log_site_edit_admmingroup') :  lang('admin_log_site_add_admmingroup');
        AdminLog(3, $data['title'], lang("admin_log_site_admin_group"), $name, $code);

        return TRUE;
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

        if(in_array(1, $ids)){
            $this->errors = lang('del_default_admin_user_error');
            return false;
        }

        $_map = [];
        if(is_array($ids)) {
            $_map['id'] = array("IN", $ids);
        } else {
            $_map['id'] = $ids;
        }
        $result = $this->model->destroy($ids);

        // 生成log记录
        $code = AdminLoginFormat($ids);
        AdminLog(3, '', lang("admin_log_site_admin_group"), lang('admin_log_site_delete_admmingroup'), $code);

        return true;
    }

}
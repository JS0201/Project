<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\service;
use think\Model;

class MemberGroup extends Model{

    public function initialize()
    {
       $this->model = model('member/MemberGroup');
    }


    public function get_list($sqlmap,$order='sort asc,id asc',$page=[]){

        $lists = $this->model->where($sqlmap)->order($order)->paginate($page);
        return $lists;

    }

    public function get_column($sqlmap=[],$field='id,name'){

       return  $this->model->where($sqlmap)->column($field);
    }




    public function get_find($sqlmap=[]){

        if(!$result=$this->model->where($sqlmap)->find()){
            $this->errors=lang('_param_error_');
            return false;
        }

        return $result;

    }


    public function edit($data, $isupdate = false, $valid = true, $msg = []){

        $result = $this->model->except('id')->validate($valid, $msg)->isupdate($isupdate)->save($data);


        if ($result === false) {

            $this->errors = $this->model->getError();
            return false;
        }

        // 生成log记录
        $code = $isupdate ? $data['id'] : $this->model->id;
        $name = $isupdate ? lang('admin_log_member_edit_group') :  lang('admin_log_member_add_group');
        $contetn = $data['name'] ? $data['name'] : '';
        AdminLog(2, $contetn, lang("admin_log_member_group"), $name, $code);

        return true;
    }



    /**
     * @param array $ids id主键
     * @return bool
     */
    public function del($sqlmap){


        if(empty($sqlmap)){

            $this->errors = lang('_param_error_');
            return false;
        }

        $this->model->destroy($sqlmap);
        // 生成log记录
        $code = AdminLoginFormat($sqlmap);
        AdminLog(2, '', lang("admin_log_member_group"), lang('admin_log_member_delete_group'), $code);

        return true;
    }


}
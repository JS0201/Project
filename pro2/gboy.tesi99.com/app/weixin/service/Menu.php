<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\weixin\service;
use think\Model;

class Menu extends Model{

    public function initialize()
    {
       $this->model = model('weixin/Menu');
    }


    public function get_list($sqlmap,$page=[]){


        $lists = $this->model->where($sqlmap)->order('id desc')->paginate($page);
        return $lists;

    }



    public function get_find($sqlmap=[]){

        if(!$result=$this->model->where($sqlmap)->find()){
            $this->errors=lang('_param_error_');
            return false;
        }

        return $result;

    }


    public function edit($data,$isupdate = false){

        $result = $this->model->except('id')->validate(true)->isupdate($isupdate)->save($data);

        if ($result === false) {
            $this->errors = $this->model->getError();
            return false;
        }
        // 生成log记录
        $code = $isupdate ? $data['id'] : $this->model->id;
        $name = $isupdate ? lang('admin_log_weixin_edit_menu') :  lang('admin_log_weixin_add_menu');
        $contetn = $data['title'] ? ('菜单名称：' . $data['title']) : '';
        AdminLog(2, $contetn, lang("admin_log_weixin_menu"), $name, $code);

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
        AdminLog(2, '', lang("admin_log_weixin_menu"), lang('admin_log_weixin_delete_menu'), $code);

        return true;
    }


}
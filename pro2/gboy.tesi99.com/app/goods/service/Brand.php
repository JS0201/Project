<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\goods\service;
use think\Model;

class Brand extends Model{

    public function initialize()
    {
       $this->model = model('goods/Brand');
    }


    public function get_list($sqlmap,$order='sort asc,id desc',$page=[]){
        $lists = $this->model->where($sqlmap)->order($order)->paginate($page);
        return $lists;
    }

    public function get_column($sqlmap=[],$field='id,name') {
       return  $this->model->where($sqlmap)->column($field);
    }

    public function category_lists()
    {
        $category = $this->model->order('sort asc,id asc')->select()->toArray();
        $category = \houdunwang\arr\Arr::tree($category, 'name', 'id', 'parent_id');
        return $category;
    }


    public function get_by_id($sqlmap=[],$flag=true){

        if(!$result=$this->model->where($sqlmap)->find()->toArray()){
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
        $name = $isupdate ? lang('admin_log_goods_edit_brand') :  lang('admin_log_goods_add_brand');
        $contetn = $data['name'] ? $data['name'] : '';
        AdminLog(1, $contetn, lang("admin_log_goods_brand"), $name, $code);

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
        AdminLog(1, '', lang("admin_log_goods_brand"), lang('admin_log_goods_delete_brand'), $code);

        return true;
    }


}
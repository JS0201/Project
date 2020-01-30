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

class Category extends Model{


    public function initialize()
    {
       $this->model = model('goods/Category');
    }




    public function category_lists()
    {
        $category = $this->model->order('sort asc,id asc')->select()->toArray();
        $category = \houdunwang\arr\Arr::tree($category, 'name', 'id', 'parent_id');
        return $category;
    }


    public function get_by_id($sqlmap=[],$show_self=true,$flag=true){
        $result=$this->model->where($sqlmap)->find();
        if(!$result){
            $this->errors=lang('_param_error_');
            return false;
        }
        $info=$result->toArray();
        $info['parent_name']=$this->create_cat_format($info['path_id'],$show_self,$flag);
        return $info;
    }


    public function edit($data, $isupdate = false, $valid = true, $msg = []){
        if(!$data['type']) {
            $data['type'] = 3;
        }else{
            $data['type'] = 2;
        }
        $result = $this->model->except('id')->validate($valid, $msg)->isupdate($isupdate)->save($data);

        if ($result === false) {
            $this->errors = $this->model->getError();
            return false;
        }

        // 生成log记录
        $code = $isupdate ? $data['id'] : $this->model->id;
        $name = $isupdate ? lang('admin_log_goods_edit_category') :  lang('admin_log_goods_add_category');
        $contetn = $data['name'] ? $data['name'] : '';
        AdminLog(1, $contetn, lang("admin_log_goods_category"), $name, $code);

        return true;
    }



    /**
     * [create_cat_format 组合父子级分类关系]
     * @param string $path_id 层级
     * @param string $show_self 是否显示自己
     * @param bool   $extra 是否显示顶级分类文字
     * @return string
     */
    public function create_cat_format($path_id='',$show_self = true,$extra = FALSE){
        $names = [];
        $cat_str='';
        if(empty($path_id)){
            if($extra == TRUE) $names = array('0' => '顶级分类');
        }else{

            $path_id=trim($path_id,',');

            if(!$show_self){
                $path_id=substr($path_id,0,strrpos($path_id,','));
            }

            if($path_id){
                //$path_arr=explode(',',$path_id);


                //$names=$this->model->where(['id'=>['in',$path_arr]])->order('field(id,"1,2,3,4")')->column('name');
                $names=[];
                $category_list=$this->model->query('select name from gboy_goods_category where id in ('.$path_id.') order by field(id,'.$path_id.')');


                foreach($category_list as $k=>$v){

                    $names[]=$v['name'];
                }

            }


            if($extra == TRUE) array_unshift($names,'顶级分类');
        }


        foreach ($names as $key => $value) {
            if($key == count($names)-1){
                $cat_str .= $value;
            }else{
                $cat_str .= $value.' > ';
            }
        }

        return $cat_str;
    }



    public function node_list(){


        $list=$this->model->order('sort asc,id asc')->column('id,name,parent_id');

        $tree = new \org\Tree();
        $tree->init($list);

        $html="<span ><a href='".url('goods/admin/add')."/catid/\$id' target='main_frame'><img src=".__ROOT__."static/js/lighttreeview/images/add_content.gif></a><a href='".url('goods/admin/index')."/catid/\$id' target='main_frame'>\$name</a></span>";

        $parent_html="<span ><a href='".url('goods/admin/index')."/catid/\$id' target='main_frame'>\$name</a></span>";


        $categorytree = $tree->get_treeview(0,'category_tree',$html,$parent_html);

        return $categorytree;

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
        AdminLog(1, '', lang("admin_log_goods_category"), lang('admin_log_goods_delete_category'), $code);

        return true;
    }


}
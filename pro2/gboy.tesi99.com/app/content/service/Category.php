<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\content\service;

use think\Model;


class Category extends Model
{

    public function initialize()
    {

        $this->model = model('content/Category');

    }

    /**
     * @param        $sqlmap 条件
     * @param string $field 字段
     * @return mixed
     */
    public function get_by_id($sqlmap, $field = '')
    {
        $info = $this->model->get($sqlmap)->toArray();

        if($field) {
            return $info[$field];
        }

        return $info;
    }



    /**
     * @param $sqlmap 条件
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function get_lists($sqlmap, $page, $limit)
    {

        $lists = $this->model->where($sqlmap)->page($page)->limit($limit)->select()->toArray();

        return $lists;
    }

    public function category_lists()
    {

        $category = $this->model->order('sort asc')->select()->toArray();
        $category = \houdunwang\arr\Arr::tree($category, 'name', 'id', 'parent_id');
        return $category;
    }



    public function edit($data, $isupdate = false, $valid = true, $msg = []){

        $result = $this->model->except('id')->validate($valid, $msg)->isupdate($isupdate)->save($data);
        if ($result === false) {
            $this->errors = $this->model->getError();
            return false;
        }
        // 生成log记录
        $code = $isupdate ?  $code = $data['id'] : $this->model->id;
        $log_content = $isupdate ? lang('admin_log_content_edit_category') :  lang('admin_log_content_add_category');
        AdminLog(1, $data['name'], lang("admin_log_content_category"), $log_content, $code);

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
        AdminLog(1, '', lang("admin_log_content_category"), lang('admin_log_content_delete_category'), $code);

        return true;
    }


    /**
     * 分类层级
     * @param int $cid
     * @return array
     */
    public function category_node($cid = 0)
    {
        $new_category=['顶级分类'];

        $category = $this->model->select()->toArray();

        if ($category) {

            $category = \houdunwang\arr\Arr::tree($category, 'name', 'id', 'parent_id');


            foreach($category as $k=>$v){

                //编辑时在栏目选择中不显示自身与子级栏目
                if ($cid && ($v['id'] == $cid || \houdunwang\arr\Arr::isChild($category, $v['id'], $cid))) {
                    //unset($new_category[$v['id']]);
                }else{
                    $new_category[$v['id']]=$v['_name'];
                }


            }

        }

        return $new_category;
    }

    /**
     * 模型选择
     * @param $modelid 模型ID
     * @return array|string
     */
    public function select_tpl($modelid){

        $dir=ROOT_PATH.config('base.view_path').DS.config('base.default_theme').DS.'content';

        $models=model('content/Models')->where(['disabled'=>0,'modelid'=>$modelid])->find();

        $html='';

        if($modelid && $models ){

            $style = $models['default_style'];
            $category_template = $models['category_template'];
            $list_template = $models['list_template'];
            $show_template = $models['show_template'];
            $category_template = str_replace($dir.'/','',glob($dir.'/'.$category_template.'*.html'));
            $list_template = str_replace($dir.'/','',glob($dir.'/'.$list_template.'*.html'));
            $show_template = str_replace($dir.'/','',glob($dir.'/'.$show_template.'*.html'));


            $html = [
                'template_list'=> $style,
                'category_template'=> $category_template,
                'list_template'=>$list_template,
                'show_template'=>$show_template
            ];



        }
        return $html;


    }

    public function node_json(){



        $list=$this->order('sort asc,id asc')->column('id,name,parent_id,modelid');


        foreach($list as $k=>$v){

            if($v['modelid']==1){
                $list[$k]['add_icon']="";
                $list[$k]['add_url']=url('content/content/add');
            }else{
                $list[$k]['add_icon']="<a href='".url('content/content/add',['menuid'=>$v['id']])."' target='main_frame'><img src=".__ROOT__."static/js/lighttreeview/images/add_content.gif></a>";
                $list[$k]['add_url']=url('content/content/index');
            }
            $list[$k]['icon_type']='treeview-folder';
        }

        $tree = new \org\Tree();
        $tree->init($list);

        $html="<span >\$add_icon<a href='\$add_url/menuid/\$id' target='main_frame'>\$name</a></span>";

        $parent_html="<span class='\$icon_type'>\$add_icon<a href='\$add_url/menuid/\$id' target='main_frame'>\$name</a></span>";


        $categorytree = $tree->get_treeview(0,'category_tree',$html,$parent_html);

        return $categorytree;

    }

}
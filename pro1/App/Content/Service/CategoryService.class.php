<?php

namespace Content\Service;

use Think\Model;

class CategoryService extends Model {

    public function __construct() {

        $this->model = D('Category');
    }

    /**
     * [get_category_by_id 根据id获取分类信息]
     * @param  [type] $id [description]
     * @field  [string] 字段
     * @return [type]     [description]
     */
    public function get_category_by_id($id, $field = FALSE) {
        if ((int) $id < 1) {
            $this->errors = '分类不存在';
            return FALSE;
        }

        $result = $this->model->find($id);
        //$result['category_id'] = $this->get_parents_id($result['id']);
        $result['parent_name'] = $this->get_parents_name($id);
        if (!$result) {
            $this->errors = '出错';
        }
        if ($field)
            return $result[$field];
        return $result;
    }

    //获取父分类id
    public function get_parents_id($id) {
        if ($id < 0) {
            $this->errors = '分类不存在';
            return false;
        }
        static $ids = array();
        $row = $this->model->where(array('id' => $id))->find();
        if ($row['parent_id'] != 0) {
            $ids[] = $row['id'];
            $parent_id = $row['parent_id'];
            $this->get_parents_id($parent_id);
        } else {
            $ids[] = $row['id'];
        }
        return array_reverse($ids);
    }

    //获取父分类名称
    public function get_parents_name($id) {
        $ids = $this->get_parents_id($id);
        $names = $this->model->where(array('id' => array('IN', $ids)))->getField('name', true);
        $cat_name = '';
        foreach ($names as $k => $v) {
            $cat_name .= $v . ' > ';
        }
        return rtrim($cat_name, ' > ');
    }

    /**
     * [add 保存分类]
     * @param [array] $params [分类信息]
     * @return [boolean]         [返回ture or false]
     */
    public function save($params) {
        if (empty($params['parent_id'])) {
            $params['parent_id'] = 0;
        } else {
            $count = $this->model->where(array('id' => array('eq', (int) $params['parent_id'])))->count();
            if ($count < 1) {
                $this->errors = '分类不存在';
                return false;
            }
        }



        $data = array();
        $data = $this->model->create($params);

        if (!$data) {
            $this->errors = $this->model->geterror();
            return false;
        }


        //缩略图添加附件
        if ($params['thumb']) {

            $conf = array(
                'module' => 'article',
                'url' => $params['thumb'],
                'catid' => $params['id'],
            );
            attachment($conf);
        }


        //编辑器图片添加水印
        preg_match_all('/<img[^>]*src\s?=\s?[\'|"]([^\'|"]*)[\'|"]/is', $data['content'], $picarr);

        if (is_array($picarr[1])) {

            foreach ($picarr[1] as $k => $v) {


                att_mark('article', $v, $data['id']);
            }
        }



        if ($data['id']) {


            //path_id设置
            if (empty($params['parent_id'])) {
                $data['path_id'] = '0,' . $data['id'] . ',';
            } else {
                $par_path_id = $this->model->where(array('id' => $params['parent_id']))->getfield('path_id');

                $data['path_id'] = $par_path_id . $data['id'] . ',';
            }

            //是否应用到子模板
            if ($params['template_child'] == 1) {
                $data_tpl = array();
                $data_tpl['category_template'] = $data['category_template'];
                $data_tpl['list_template'] = $data['list_template'];
                $data_tpl['show_template'] = $data['show_template'];

                $this->model->where(array('path_id' => array('like', '%,' . $data['id'] . ',%')))->data($data_tpl)->save();
            }


            $result = $this->model->save($data);
        } else {

            $result = $this->model->add($data);

            //path_id设置
            if (empty($params['parent_id'])) {
                $path_id = '0,' . $result . ',';
            } else {
                $par_path_id = $this->model->where(array('id' => $params['parent_id']))->getfield('path_id');

                $path_id = $par_path_id . $result . ',';
            }

            $this->model->where(array('id' => $result))->data(array('path_id' => $path_id))->save();
        }





        if ($result === false) {
            $this->errors = '发生知未错误，操作失败';
            return false;
        } else {
            return true;
        }
    }

    /**
     * [get_category_by 获取一条分类信息]
     */
    public function get_category_by($id, $type) {
        $id = (int) $id;
        if ($id < 1) {
            $this->errors = '分类不存在';
            return FALSE;
        }
        $data = array();
        $data['id'] = $id;
        $row = $this->model->where($data)->find();
        if ($type) {
            return $row[$type];
        }
        return $row;
    }

    /**
     * [get_category_tree 获取分类树]
     * @return [type] [description]
     */
    public function get_category_tree() {
        $_catinfo = $this->model->select();
        if (!$_catinfo) {
            $this->errors = '没有数据';
            return FALSE;
        }
        $first = array(
            'id' => '0',
            'name' => '顶级分类',
            'parent_id' => '-1'
        );
        array_unshift($_catinfo, $first);
        return $_catinfo;
    }

    /**
     * [ajax_sun_class ajax获取分类]
     */
    public function ajax_son_class($params) {

        if ((int) $params['id'] < 1) {
            $this->errors = '数据不存在';
            return FALSE;
        }
        $result = $this->model->where(array('parent_id' => array('eq', $params['id'])))->select();

        foreach ($result as $key => $value) {
            $result[$key]['row'] = $this->model->where(array('parent_id' => array('eq', $value['id'])))->count();
            $result[$key]['model_name'] = site_model($value['modelid']);
        }
        if (!$result) {
            $this->errors = '数据不存在';
        }
        return $result;
    }

    /**
     * [ajax_edit 编辑分类信息]
     */
    public function ajax_edit($params) {

        $result = $this->model->save($params);
        if ($result == false) {
            $this->errors = '修改失败';
            return false;
        }
        return true;
    }

    /**
     * [del 删除分类]
     * @param [type] $id [分类id]
     * @return [boolean]         [返回ture or false]
     */
    public function del($params) {
        $data = array();
        if (is_array($params)) {

            foreach ($params as $k => $v) {

                $data['path_id'] = array('like', '%,' . $v . ',%');
                $this->model->where($data)->delete();
            }
        } else {
            $data['path_id'] = array('like', '%,' . $params . ',%');
            $this->model->where($data)->delete();
        }

        return TRUE;
    }

    /**
     * [select_tpl 选择模板]
     * @param [type] $id [模型id]
     * @return [array]    
     */
    public function select_tpl() {

        $id = $_REQUEST['id'];
        $modelid = $_REQUEST['modelid'];

        if (empty($id)) {

            $tpl_list = array(
                '1' => array(
                    'category_tpl' => 'Content_page.html',
                    'list_tpl' => 'Content_page.html',
                    'show_tpl' => 'Content_page.html',
                ),
                '2' => array(
                    'category_tpl' => 'category.html',
                    'list_tpl' => 'Content_list.html',
                    'show_tpl' => 'Content_show.html',
                ),
                '3' => array(
                    'category_tpl' => 'category_img.html',
                    'list_tpl' => 'Content_list_img.html',
                    'show_tpl' => 'Content_show_img.html',
                ),
            );
        } else {


            $row = $this->model->where(array('id' => $id))->field('category_tpl,list_tpl,show_tpl')->find();

            return $row;
        }


        return $tpl_list[$modelid];
    }

    /**
     * [category_node 分类递归]
     */
    function category_node($parent_id = 0, $id = '', $d = '├') {
        global $list;

        $row = $this->model->where(array('parent_id' => $parent_id))->order('sort desc,id asc')->select();
        if (is_array($row)) {  //验证下，不然调试模式有错误
            foreach ($row as $key => $v) {
                $selected = '';
                if ($id == $v['id'])
                    $selected = 'selected="selected"';
                $list .= '<option value="' . $v['id'] . '" ' . $selected . ' data-name="' . $v['name'] . '" data-modelid="' . $v['modelid'] . '">';
                for ($j = 1; $j < substr_count($v['path_id'], ',') - 1; $j++) {
                    $list .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                if (!empty($v['parent_id'])) {
                    $list .= $d;
                }


                $list .= $v['name'];
                $list .= '</option>';
                $this->category_node($v['id'], $id, $d);
            }
        }

        return $list;
    }

}

?>
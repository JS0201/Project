<?php

namespace Goods\Service;

use Think\Model;

class GoodsCategoryService extends Model {

    public function __construct() {

        $this->model = D('Goods/Goods_category');
    }

    /**
     * [select 列表]
     * @return [type]            [description]
     */
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'sort asc') {


        $count = $this->model->where($sqlmap)->count();

        $pages = new \Think\Page($count, $limit);

        $page = $page ? $page : 1;


        if (isset($_GET['p'])) {
            $page = (int) $_GET['p'];
        }

        if ($limit != '') {

            $limits = (($page - 1) * $limit) . ',' . $limit;
        }

        $result = $this->model->where($sqlmap)->order($order)->limit($limits)->select();
	
		
        foreach ($result as $key => $value) {
            $result[$key]['type_name'] = '';
            if ($this->has_child($value['id'])) {
                $result[$key]['level'] = 1;
            }
            $lists[] = array('id' => $value['id'], 'sort' => $value['sort'], 'name' => $value['name'], 'type_name' => $result[$key]['type_name'], 'status' => $value['status'], 'level' => $result[$key]['level']);
        }

        return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
    }

    /**
     * [ajax_category ajax获取分类]
     * @param  [type] $parent_id [description]
     * @return [type]            [description]
     */
    public function ajax_category($parent_id) {
        if ((int) $parent_id < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }
        $result = $this->model->where(array('parent_id' => $parent_id))->order('sort desc')->select();
        foreach ($result as $key => $value) {

            $result[$key]['type_name'] = '';
            $result[$key]['row'] = $this->model->where(array('parent_id' => $value['id']))->count();
        }

        return $result;
    }

    /**
     * [get_category_tree 获取分类树]
     * @return [type] [description]
     */
    public function get_category_tree() {
        $_catinfo = $this->model->select();

        $first = array(
            'id' => '0',
            'name' => '顶级分类',
            'parent_id' => '-1'
        );
        array_unshift($_catinfo, $first);

        return $_catinfo;
    }

    /**
     * [find 获取]
     * @param [array] $sqlmap [条件]
     * @return [boolean]         [返回ture or false]
     */
    public function find($sqlmap) {

        $data = $this->model->where($sqlmap)->find();

        if ((int) $data['id'] < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }

        if (isset($data['grade'])) {
            $data['grade'] = preg_replace("/(\s)/", '', preg_replace("/(\n)|(\t)|(\')|(')|(，)/", ',', $data['grade']));
        }
        if ($data['url'] == 'http://') {
            unset($data['url']);
        }



        return $data;
    }

    /**
     * [get_parent_name 获取父级名称]
     * @param [array] $sqlmap [条件]
     * @return [boolean]         [返回ture or false]
     */
    public function get_parent_name($id, $extra = false) {



        if (empty($id)) {
            if ($extra)
                $parent_list = '顶级分类';
        }else {

            $data = $this->model->where(array('id' => $id))->find();
		
            $names = array();
			if($data){
				$names = $this->model->where(array('id' => array('IN', $data['path_id'])))->getField('name', TRUE);
			}
            

            if ($extra)
                array_unshift($names, '顶级分类');

            $parent_list = implode(' > ', $names);
        }




        return $parent_list;
    }

    /**
     * [save 保存]
     * @param [array] $params [参数]
     * @param [array] $condition [条件]
     * @return [boolean]         [返回ture or false]
     */
    public function save($params, $condition = array()) {
        if (isset($params['grade'])) {
            $params['grade'] = preg_replace("/(\s)/", '', preg_replace("/(\n)|(\t)|(\')|(')|(，)/", ',', $params['grade']));
        }

        if ($params['url'] == 'http://') {
            unset($params['url']);
        }

        $data = $this->model->create($params);

        if (!$data) {

            $this->errors = $this->model->geterror();
            return false;
        }

        if ($data['id']) {
			if($condition){
				$result = $this->model->where($condition)->save($data);
			}else{
				$result = $this->model->save($data);
			}
            

            $this->model->set_path_id($data['id']);
        } else {

            $result = $this->model->add($data);

            $this->model->set_path_id($result);
        }



        if ($result===false) {
            $this->errors = L('_os_error_');
        } else {
            return true;
        }
    }

    /**
     * [has_child 判断分类是否有子分类]
     * @param  [type]  $id [分类id]
     * @return boolean     [description]
     */
    public function has_child($id) {
        if ((int) $id < 0) {
            $this->errors = lang('_params_error_');
            return FALSE;
        }
        $rows = $this->model->where(array('parent_id' => $id))->count();
        return $rows > 0 ? true : false;
    }

    /**
     * [change_info ajax修改字段]
     * @param  [array] $params [参数]
     * @return [boolean]     [返回更改结果]
     */
    public function change_info($params) {
        if ((int) $params['id'] < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }
        $result = $this->model->where(array('id' => $params['id']))->save($params);
        if (!$result) {
            $this->errors = L('_os_error_');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * [change_status 改变状态]
     * @param  [int] $id [id]
     * @return [boolean]     [返回更改结果]
     */
    public function change_status($id) {
        if ((int) $id < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }
        $data = array();
        $data['status'] = array('exp', ' 1-status ');
        $result = $this->model->where(array('id' => array('eq', $id)))->save($data);
        if (!$result) {
            $this->errors = L('_os_error_');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * [delete 删除分类]
     * @param  [int] $params [分类id]
     * @return [boolean]     [返回删除结果]
     */
    public function del($sqlmap = array()) {

        $result = $this->model->where($sqlmap)->delete();

        return true;
    }

}

?>
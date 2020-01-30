<?php

namespace Goods\Service;

use Think\Model;

class BrandService extends Model {

    public function __construct() {

        $this->model = D('Goods/Brand');
    }

    /**
     * 品牌列表
     * @param type $sqlmap array
     * @param type $limit 每页分多少条
     * 
     */
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = "id asc") {

        $count = $this->model->where($sqlmap)->count();

        $pages = new \Think\Page($count, $limit);

        $page = $page ? $page : 1;
        if (isset($_GET['p']))
            $page = (int) $_GET['p'];

        if ($limit != '')
            $limits = (($page - 1) * $limit) . ',' . $limit;


        $lists = $this->model->where($sqlmap)->order($order)->limit($limits)->select();

        return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
    }

    /**
     * [find 根据id获取品牌信息]
     * @param  [type] $sqlmap [array]
     * @param  [type] $field [stirng] 
     * @return [type]     [array]
     */
    public function find($sqlmap = array(), $field = '') {
        if ((int) $sqlmap['id'] < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }
        $result = $this->model->where($sqlmap)->field($field)->find();


        return $result;
    }

    /**
     * [save 保存]
     * @param [array] $goods		[参数]
     * @return [boolean]         [返回ture or false]
     */
    public function save($params) {


        $data = $this->model->create($params);

        if (!$data) {
            $this->errors = $this->model->geterror();
            return false;
        }
        if ($data['id']) {

            $result = $this->model->save($data);
        } else {

            $result = $this->model->data($data)->add();
        }
        if ($result === FALSE) {
            $this->errors = L('_os_error_');
            return FALSE;
        }

        return true;
    }

    /**
     * [search_brand 关键字查找品牌]
     * @param  [type] $keyword [description]
     * @return [type]          [description]
     */
    public function ajax_brand($keyword) {
        $sqlmap = array();
        if ($keyword) {
            $sqlmap = array('name' => array('LIKE', '%' . $keyword . '%'));
        }
        $result = $this->model->where($sqlmap)->getField('id,name', TRUE);
        if (!$result) {
            $this->errors = L('_os_error_');
        }
        return $result;
    }

    /**
     * [change_recommend 改变状态]
     * @param  [int] $id [规格id]
     * @return [boolean]     [返回更改结果]
     */
    public function change_recommend($id) {
        if ((int) $id < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }
        $data = array();
        $data['isrecommend'] = array('exp', ' 1-isrecommend ');
        $result = $this->model->where(array('id' => array('eq', $id)))->save($data);
        if (!$result) {
            $this->errors = L('_os_error_');
            return FALSE;
        }
        return $result;
    }

    /**
     * [change_sort 改变排序]
     * @param  [array] $params [规格id和排序数组]
     * @return [boolean]     [返回更改结果]
     */
    public function change_sort($params) {
        if ((int) $params['id'] < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }
        $data = array();
        $data['sort'] = $params['sort'];
        $result = $this->model->where(array('id' => array('eq', $params['id'])))->save($data);
        if (!$result) {
            $this->errors = L('_os_error_');
            return FALSE;
        }
        return $result;
    }

    /**
     * [change_sort 改变名称]
     * @param  [array] $params [品牌id和name]
     * @return [boolean]     [返回更改结果]
     */
    public function change_name($params) {
        if ((int) $params['id'] < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }
        $data = array();
        $data['name'] = $params['name'];
        $result = $this->model->where(array('id' => $params['id']))->save($data);
        if (!$result) {
            $this->errors = L('_os_error_');
            return FALSE;
        }
        return $result;
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
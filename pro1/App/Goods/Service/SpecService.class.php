<?php

namespace Goods\Service;

use Think\Model;

class SpecService extends Model {

    public function __construct() {

        $this->model = D('Goods/Spec');
    }

    /**
     * [select 列表]
     * @return [type]            [description]
     */
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'sort desc,id asc') {

        $count = $this->model->where($sqlmap)->count();
        $pages = new \Think\Page($count, $limit);
        $page = $page ? $page : 1;

        if (isset($_GET['p'])) {
            $page = (int) $_GET['p'];
        }

        if ($limit != '') {
            $limits = (($page - 1) * $limit) . ',' . $limit;
        }

        $lists = $this->model->where($sqlmap)->order($order)->limit($limits)->select();

        return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
    }

    /**
     * [find 根据id获取商品类型信息]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function find($sqlmap = array()) {

        $result = $this->model->where($sqlmap)->find();
        $result['spec_array'] = explode(',', $result['value']);
        $result['price_array'] = explode(',', $result['price']);

        return $result;
    }

    /**
     * [save 保存]
     * @param [array] $goods		[参数]
     * @param [array] $condition	[条件]
     * @return [boolean]         [返回ture or false]
     */
    public function save($params, $condition = array()) {

        $params['value'] = array_unique($params['value']);

        foreach ($params['value'] as $k => $value) {
            if ($value == '') {
                unset($params['value'][$k]);
            }
        }
        $params['value'] = implode(',', $params['value']);

        foreach ($params['price'] as $k => $value) {
            if ($value == '') {
                unset($params['price'][$k]);
            }
        }


        $params['price'] = implode(',', $params['price']);

        $data = $this->model->create($params);

        if (!$data) {
            $this->errors = $this->model->geterror();
            return false;
        }

        if ($data['id']) {
            $result = $this->model->where($condition)->save($data);
        } else {
            $result = $this->model->add($data);
        }

        if ($result === false) {
            $this->errors = L('_os_error_');
            return false;
        }

        return true;
    }

    /**
     * [delete_spec 删除规格，可批量删除]
     * @param  [int||array] $sqlmap [条件]
     * @return [boolean]         [返回删除结果]
     */
    public function del($sqlmap = array()) {
        $result = $this->model->where($sqlmap)->delete();
        if (!$result) {
            $this->errors = L('_os_error_');
        }
        return $result;
    }

}

?>
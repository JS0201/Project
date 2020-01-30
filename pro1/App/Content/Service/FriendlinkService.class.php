<?php

namespace Content\Service;

use Think\Model;

class FriendlinkService extends Model {

    public function __construct() {

        $this->model = D('Friendlink');
    }

    /**
     * [add 保存]
     * @param [array] $params [信息]
     * @return [boolean]         [返回ture or false]
     */
    public function save($params) {
        if (empty($params['type_id'])) {

            $params['type_id'] = 1;
        }



        $data = array();
        $data = $this->model->create($params);


        if ($data['logo']) {

            $conf = array(
                'module' => 'ad',
                'url' => $data['logo'],
                'catid' => $params['type_id'],
            );
            attachment($conf);
        }


        if (!$data) {
            $this->errors = $this->model->geterror();
            return false;
        }

        if ($data['id']) {
            $result = $this->model->save($data);
        } else {
            $data['add_time'] = time();
            $result = $this->model->add($data);
        }

        if ($result === false) {
            $this->errors = '发生知未错误，操作失败';
            return false;
        } else {
            return true;
        }
    }

    /**
     * [get_friendlink_by_id 查询某条数据]
     * @id [number] 传入的id
     * @return [type] [description]
     */
    public function get_by_id($id) {
        if ((int) $id < 1) {
            $this->errors = '数据不存在';
            return FALSE;
        }
        $result = $this->model->find($id);
        if (!$result) {
            $this->errors = '数据错误';
        }
        return $result;
    }

    /**
     * [del 删除]
     * @param [type] $id [id]
     * @return [boolean]         [返回ture or false]
     */
    public function del($params) {


        $data = array();
        if (is_array($params)) {

            foreach ($params as $k => $v) {

                $data['id'] = $v;
                $this->model->where($data)->delete();
            }
        } else {
            $data['id'] = $params;
            $this->model->where($data)->delete();
        }

        return TRUE;
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

}

?>
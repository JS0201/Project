<?php

namespace Member\Service;

use Think\Model;

class MemberAddressService extends Model {

    public function __construct() {

        $this->model = D('Member_address');
    }

    /**
     * 用户地址列表
     * @param array $sqlmap 条件
     * @param int $limit 条数
     * @param int $page 当前分页
     * @return array
     */
    public function select($sqlmap = array(), $limit = 20, $page = 1) {
        $this->sqlmap = array_merge($this->sqlmap, $sqlmap);

        $count = $this->model->where($this->sqlmap)->order("id desc")->count();

        $Pages = new \Think\Page($count, $limit);

        //$page=$Pages->firstRow.','.$Pages->listRows
        $page = ($page * $limit) . ',' . $Pages->listRows;

        $lists = $this->model->where($this->sqlmap)->order("id desc")->limit($page)->select();

        $count = $this->model->where($this->sqlmap)->count();
        return array('count' => $count, 'lists' => dhtmlspecialchars($lists));
    }

    public function fetch_all_by_uid($uid, $order = 'isdefault desc') {

        return $this->model->where(array('uid' => $uid))->order($order)->select();
    }

    /**
     * 编辑收货地址
     * @param array $params [description]
     */
    public function save($params = array()) {
        $params['address'] = remove_xss($params['address']);
        $params['status'] = 1;
        if (!$this->_validate($params)) {
            return $params['id'] ? true : false;
        }

        if ($params['id']) {

            if (!empty($params['default'])) {
                $params['isdefault'] = 1;
                $this->model->where(array("id" => array("neq", $params['id']), 'uid' => $params['uid']))->setField('isdefault', 0);
            }

            $result = $this->model->save($params);
        } else {

            /* 校验发布条数 */
            $count = $this->model->where(array('uid' => $params['uid']))->count();
            if ($count == 20) {
                $this->errors = L('shipping_address_more_limit');
                return false;
            }

            /* 如果没有收货地址则设为默认 */
            if ($count == 0 || !empty($params['default'])) {
                $params['isdefault'] = 1;
            }

            $result = $this->model->add($params);
            $id = $params['id'] ? $params['id'] : $result;
            if (!empty($params['default'])) {
                $this->model->where(array("id" => array("neq", $id), 'uid' => $params['uid']))->setField('isdefault', 0);
            }
        }

        if ($result === false) {
            $this->errors = L('_os_error_');
            return false;
        }

        return true;
    }

    public function uid($uid) {
        $this->sqlmap['uid'] = $uid;
        return $this;
    }

    public function set_default($id) {
        $result = $this->model->where(array("id" => $id, 'uid' => $this->sqlmap['uid']))->setField('isdefault', 1);
        if (!$result) {
            $this->errors = L('_data_not_exist_');
            return false;
        }
        $this->model->where(array("id" => array("NEQ", $id), 'uid' => $this->sqlmap['uid']))->setField('isdefault', 0);
        return true;
    }

    public function fetch_by_id($id) {
        $this->sqlmap['id'] = $id;
        $r = $this->model->where($this->sqlmap)->find();
        if (!$r) {
            $this->errors = L('_data_not_exist_');
            return false;
        }
        // $r['district_tree'] = $this->load->service('admin/district')->fetch_position($r['district_id'], 'id');
        // $r['full_district'] = $this->load->service('admin/district')->fetch_position($r['district_id']);
        return $r;
    }

    /**
     * 选择收货地址
     * @param  [type] $id [description]
     * @return bool
     */
    public function get_address($address_id = '') {
        $sqlmap = array();
        $sqlmap['uid'] = $this->sqlmap['uid'];
        if ($address_id) {
            //选择地址
            $sqlmap['id'] = $address_id;
        } else {
            //默认地址
            $sqlmap['isdefault'] = 1;
        }

        if(!$address_info=$this->model->where($sqlmap)->find()){ 
            unset($sqlmap['id']);
            unset($sqlmap['isdefault']);
            $address_info=$this->model->where($sqlmap)->find();
        }
        
        return $address_info;
    }

    /**
     * 删除收货地址
     * @param  [type] $id [description]
     * @return bool
     */
    public function delete($id) {
        $r = $this->fetch_by_id($id);
        if (!$r) {
            $this->errors = L('_valid_access_');
            return false;
        }
        if ($r['isdefault'] == 1) {
            $this->errors = L('default_address_not_delete');
            return false;
        }
        // runhook('member_address_delete', $id);
        $result = $this->model->delete($id);
        if ($result === FALSE) {
            $this->errors = L('_os_error_');
            return false;
        }
        return TRUE;
    }

    /**
     * 数据校验
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    private function _validate($params = array()) {
        if (empty($params)) {
            $this->errors = L('_params_error_');
            return false;
        }
        if (empty($params['uid']) || (int) $params['uid'] < 1) {
            $this->errors = L('member_error');
            return false;
        }
        if (empty($params['name'])) {
            $this->errors = L('address_name_not_null');
            return false;
        }
        if (empty($params['mobile'])) {
            $this->errors = L('mobile_number_empty');
            return false;
        }
        if (!is_mobile($params['mobile'])) {
            $this->errors = L('mobile_number_format_empty');
            return false;
        }
        if (!empty($params['zipcode']) && !is_zipcode($params['zipcode'])) {
            //$this->errors = L('email_format_empty');
            // return false;
        }


        if (empty($params['address']) || strlen($params['address']) < 5) {
            $this->errors = L('detail_area_require');
            return false;
        }
        return $params;
    }

}

?>
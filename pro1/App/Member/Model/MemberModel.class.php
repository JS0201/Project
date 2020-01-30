<?php

namespace Member\Model;

use Think\Model;

class MemberModel extends Model {

    protected $_validate = array(
    );
    protected $_auto = array(
        array('register_time', 'time', 1, 'function'),
        array('register_ip', 'getip', 1, 'function'),
    );
    protected $_result = array();

    public function fetch_by_id($id, $field = null) {

        if ($id < 1) {
            return false;
        }

        $rs = $this->find($id);

        return (!is_null($field)) ? $rs[$field] : $rs;
    }

    public function uid($id) {

        $this->_result = $this->fetch_by_id($id);
        return $this;
    }
    
    
    public function push() {

      
        
        if($this->_result['pid']){
            
            $this->_result['_push']=$this->fetch_by_id($this->_result['pid']);
        }
        
        return $this;
    }

    public function address() {

        $this->_result['_address'] = D('Member/Member_address', 'Service')->fetch_all_by_uid($this->_result['id']);
        return $this;
    }

    public function group() {
        $this->_result['_group'] = D('Member/Member_group', 'Service')->fetch_by_id($this->_result['group_id']);
        return $this;
    }

    public function output() {

        return $this->_result;
    }

    public function path_id($uid) {

        $row = $this->where(array('id' => $uid))->find();
        if (empty($row['pid'])) {

            $path_id = '0,' . $uid . ',';
        } else {
            $prow = $this->where(array('id' => $row['pid']))->find();
            $path_id = $prow['path_id'] . $uid . ',';
        }

        $this->where(array('id' => $uid))->data(array('path_id' => $path_id))->save();
    }

}

?>
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\service;
use think\Db;
use think\Hook;
use think\Model;

class Address extends Model
{
    var $userid;
    public function initialize()
    {
        $this->model = model('member/Address');
        $authkey = cookie('gboy_member_auth');
        $user = explode("\t", authcode($authkey));
        $this->userid = $user[0];
    }
    public function getOne($where)
    {
        return $this->model->getOne($where);
    }

    //地址列表
    public function getList()
    {

        $list = $this->model->getList("mid = {$this->userid}", "*");
        $result = array();
        if($list) {
            foreach($list as $k => $v) {
                $address = $this->model->getCompleteAddressAttr('', $v);
                $result[$k]['address'] = $address;
                $result[$k] = $v;
            }
        }
        return $result;
    }
    //添加编辑地址
    public function add($uid, $request)
    {
        if(!$request) {
            $this->errors = lang('submit_parameters_error');
            return false;
        }
        $request['mid'] = $uid;
        $region = explode(' ',$request['region']);
        $request['province'] = $region[0];
        $request['city'] = $region[1];
        $request['county'] = $region[2];
        unset($request['region']);
        $isdefault = $this->model->getOne("mid = {$uid} and isdefault = 1");
        if(!$isdefault) {
            $request['isdefault'] = 1;
        }
        if(!$this->model->add($request)) {
            $this->errors = lang('_operation_fail_');
            return false;
        }
        return true;
    }
    public function edit($id, $uid, $request)
    {
        if(!$request) {
            $this->errors = lang('submit_parameters_error');
            return false;
        }
        unset($request['id']);
        unset($request['uid']);
        $region = explode(' ',$request['region']);
        $request['province'] = $region[0];
        $request['city'] = $region[1];
        $request['county'] = $region[2];
        unset($request['region']);
        $re = $this->model->updated("id = {$id}", $request);
        if($re !== false) {
            return true;
        }
        $this->errors = lang('_operation_fail_');
        return false;
    }


    //获取默认
    public function getDef()
    {
        $result = array();
        $arr = $this->model->getOne(array('mid' => $this->userid, 'isdefault' => 1));
        $address = $this->model->getCompleteAddressAttr('', $arr);
        $result['name'] = $arr['name'];
        $result['mobile'] = $arr['mobile'];
        $result['id'] = $arr['id'];
        $result['address'] = $address;
        return $result;
    }
    public function setDefault($uid, $request)
    {
        $this->model->updated("mid = {$uid} and isdefault = 1",array('isdefault' => 0));
        $_re = $this->model->updated("id = {$request['id']} and isdefault = 0",array('isdefault' => 1));
        if($_re == false) {
            $this->errors = lang('_operation_fail_');
            return false;
        }
        return true;
    }

    public function del($uid, $id)
    {
        if(intval($id) != $id) {
            $this->errors = lang('_param_error_');
            return false;
        }
        $this->model->startTrans();
        if($this->model->getValue(array('id'=>$id), 'isdefault')) {  //删除默认地址
            $updated = $this->model->updated("id = (select max(id) from (select * from gboy_member_address) as g where mid = {$uid})", array('isdefault' => 1));
            if($updated === false){
                $this->model->rollback();
                $this->errors = lang('_operation_fail_');
                return false;
            }
        }
        $re = $this->model->del("id = {$id}");
        if(!$re) {
            $this->model->rollback();
            $this->errors = lang('_operation_fail_');
            return false;
        }
        $this->model->commit();
        return true;
    }
}
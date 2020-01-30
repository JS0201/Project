<?php
namespace app\member\service;
use think\Model;

class Profile extends Model
{
    public function initialize()
    {
        $this->model = model('member/MemberProfile');
        $this->usermodel = model('member/Member');
    }

    public function getOne($where)
    {
        return $this->model->getOne($where);
    }

    //修改
    public function reset($uid, $request)
    {
        $where = array('uid' => $uid);
        if(isset($request['user']) && $request['user']) { //个人资料
            if(!$request['mobile'] || !$request['realname'] || !is_mobile($request['mobile'])) {
                $this->errors = lang('userphone_error');
                return false;
            }
            unset($request['user']);
            return $this->usermodel->updated(array('id' => $uid), $request);
        }
        $arr = $this->model->getOne($where);
        $isnull = false;
        foreach($request as $k => $v) {
            if(!$v) {
                $isnull = true;
            }
            $data[$k] = $v;
        }
        if($isnull) {
            $this->errors = lang('message_empty');
            return false;
        }
        if(!$arr) {
            $data['uid'] = $uid;
            return $this->model->add($data);
        } else {
            return $this->model->updated($where, $data);
        }
    }

    public function getUserProfile($uid)
    {
        $return = array('status'=> true, 'data'=>array());
        $bank = $this->model->getOne(array('uid' => $uid));

        if(!$bank || !$bank['account_name'] || !$bank['account_bank'] || !$bank['bank_account']) {
            $return['status'] = false;
        }
        $bank['mobile'] = $this->usermodel->getValue(array('id' => $uid),'mobile');
        $return['data'] = $bank;
        return $return;
    }


}
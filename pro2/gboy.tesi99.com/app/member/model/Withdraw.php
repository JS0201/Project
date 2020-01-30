<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\model;
use think\Model;

class Withdraw extends Model{

    protected $table='gboy_member_withdraw';

    protected $append=['withdraw_account','status_text'];




    protected function getWithdrawAccountAttr($value,$data){

        switch($data['withdraw_type']){
            case 0:
                $withdraw_account='银行卡';
            break;
            case 1:
                $withdraw_account='支付宝';
            break;
            case 2:
                $withdraw_account='微信';
            break;
        }

        return $withdraw_account;
    }



    protected function getAddTimeAttr($value){

        return getdatetime($value);
    }

    protected function getFailureTimeAttr($value){

        if($value){
            return getdatetime($value);
        }

    }

    protected function getSuccessTimeAttr($value){

        if($value){
            return getdatetime($value);
        }

    }

    protected function getCancelTimeAttr($value){

        if($value){
            return getdatetime($value);
        }

    }

    protected function getStatusTextAttr($value,$data){

        return $this->get_status($data['status']);
    }




    public function get_status($status=''){

        $arr=['-1'=>'提现取消','0'=>'审核中','1'=>'提现失败','2'=>'提现成功'];
        $status=(string) $status;
        if($status!=''){
           return  $arr[$status];
        }

        return $arr;
    }

    public function del($where)
    {
        return Db::table($this->table)->where($where)->delete();
    }

    public function count($sqlmap=''){

        return  $this->where($sqlmap)->count(1);

    }


    public function sum($field,$sqlmap=''){

        return  $this->where($sqlmap)->sum($field);

    }

}
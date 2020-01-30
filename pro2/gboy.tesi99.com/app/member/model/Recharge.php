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

class Recharge extends Model{

    protected $table='gboy_member_deposit';

    protected $append=['pay_type'];



    protected function getOrderTimeAttr($value){

        if($value) return getdatetime($value);
    }

    protected function getTradeTimeAttr($value){

        if($value) return getdatetime($value);
    }

    protected function getPayTypeAttr($value,$data){

        if($data['pay_code']){
                return model('pay/Payment')->where(['pay_code'=>$data['pay_code']])->value('pay_name');
        }

    }

    public function count($sqlmap=''){

        return  $this->where($sqlmap)->count(1);

    }

}
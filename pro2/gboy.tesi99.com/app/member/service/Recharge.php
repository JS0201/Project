<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\service;
use think\Model;

class Recharge extends Model{

    public function initialize()
    {
       $this->model = model('member/Recharge');
    }


    public function get_list($sqlmap,$page=[]){


        $lists = $this->model->where($sqlmap)->order('id desc')->paginate($page);

        return $lists;

    }



    public function get_find($sqlmap=[],$field=''){

        if(!$result=$this->model->where($sqlmap)->field($field)->find()){
            $this->errors=lang('_param_error_');
            return false;
        }

        return $result;

    }

    public function build_sqlmap($params){
        $sqlmap=[];

        if($params['start']) {
            $time[] = ["GT", strtotime($params['start'])];
        }
        if($params['end']) {
            $time[] = ["LT", strtotime($params['end'])];
        }
        if($time){
            $sqlmap['order_time'] = $time;
        }

        if($params['order_status']){
            $sqlmap['order_status'] = $params['order_status'];
        }

        if($params['keywords']){
            $keywords=trim($params['keywords']);
            $sqlmap['username|order_sn'] = ['like','%'.$keywords.'%'] ;
        }

        return $sqlmap;
    }

}
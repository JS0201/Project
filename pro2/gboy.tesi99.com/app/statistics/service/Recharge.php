<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\statistics\service;
use think\Model;

class Recharge extends Model{

    public function initialize()
    {
       $this->model = model('member/Recharge');
       $this->checkmodel = model('member/RechangeCheck');
    }


    public function _query($field,$sqlmap,$group){
        return $this->model->field($field)->where($sqlmap)->group($group)->select();
    }


    public function count(){

        $count=[];
        $count['day'] = $this->checkmodel->count('date_format(from_UNIXTIME(`created`),\'%Y-%m-%d\') = date_format(now(),\'%Y-%m-%d\') and `type`=1 and status = 2');



        $count['yesterday'] = $this->checkmodel->count(['created'=>['between',[strtotime(date('Y-m-d',strtotime('-1 day'))),strtotime(date('Y-m-d 23:59:59',strtotime('-1 day')))]],'type'=>1,'status'=>2]);



        $count['current_month'] = $this->checkmodel->count('date_format(from_UNIXTIME(`created`),\'%Y-%m\') = date_format(now(),\'%Y-%m\') and `type`=1 and status = 2');

        $count['week'] = $this->checkmodel->count(['created'=>['between',[strtotime('-6 day'),time()]],'type'=>1,'status'=>2]);
        $count['month'] = $this->checkmodel->count(['created'=>['between',[strtotime('-29 day'),time()]],'type'=>1,'status'=>2]);
        $count['count'] = $this->checkmodel->count();





        return $count;


    }


    public function build_data($data){
        $params = $data;
        $sqlmap = [];
        $xAxis = [];
        /* 时间周期 */
        if(isset($params['days']) && $params['days']>0){
            $params['etime'] = time();
            $params['stime'] = strtotime("-{$params['days']}day",$params['etime']);

        }
        if(isset($params['stime']{0}) && isset($params['etime']{0})){
            $params['etime'] = strtotime($params['etime']);
            $params['stime'] = strtotime($params['stime']);
        }

        $days=round(($params['etime']-$params['stime'])/86400);

        //两个时间戳之间的天数

        $sqlmap['created'] = array('between',array(
            strtotime(date('Y-m-d',$params['stime']).'00:00:00'),
            strtotime(date('Y-m-d',$params['etime']).'23:59:59')
        ));

        $subtext = $params['stime'].'-'.$params['etime'];
        for ($i=0; $i <= $days; $i++) {
            $xAxis[$i] = date('Y-m-d',strtotime("+{$i}day",$params['stime']));
        }



        //充值数
        $sqlmap['created'] = array('between',array(
            strtotime(date('Y-m-d',$params['stime']).'00:00:00'),
            strtotime(date('Y-m-d',$params['etime']).'23:59:59')
        ));
        $sqlmap['type'] = 1;
        $sqlmap['status'] = 2;
        $group = 'days';


        $field = "FROM_UNIXTIME(created,'%Y-%m-%d') days,SUM(money) as money";

        $_money = $this->checkmodel->field($field)->where($sqlmap)->group($group)->select();
        foreach($_money as $k =>$v){
            $_money[$v['days']] = $_money[$k];
        }




        //组装数据
        foreach ($xAxis as $key => $value) {
            $_moneyval[] = isset($_money[$value]['money'])?$_money[$value]['money']:'0.00';
        }

        $row['recharge'] ['xAxis']= $xAxis;
        $row['recharge'] ['money']= $_moneyval;
        return $row;
    }



}
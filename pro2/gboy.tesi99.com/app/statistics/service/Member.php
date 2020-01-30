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

class Member extends Model{

    public function initialize()
    {
       $this->model = model('member/Member');
    }


    public function _query($field,$sqlmap,$group){
        return $this->model->field($field)->where($sqlmap)->group($group)->select();
    }


    public function count(){

        $count=[];
        $count['day'] = $this->model->count('date_format(from_UNIXTIME(`register_time`),\'%Y-%m-%d\') = date_format(now(),\'%Y-%m-%d\')');


        $count['yesterday'] = $this->model->count(['register_time'=>['between',[strtotime(date('Y-m-d',strtotime('-1 day'))),strtotime(date('Y-m-d 23:59:59',strtotime('-1 day')))]]]);


        $count['current_month'] = $this->model->count('date_format(from_UNIXTIME(`register_time`),\'%Y-%m\') = date_format(now(),\'%Y-%m\')');

        $count['week'] = $this->model->count(['register_time'=>['between',[strtotime('-6 day'),time()]]]);
        $count['month'] = $this->model->count(['register_time'=>['between',[strtotime('-29 day'),time()]]]);
        $count['count'] = $this->model->count();



        $count['auth_count'] = $this->model->count(['isauth'=>1]);
        $count['not_auth_count'] = $this->model->count(['isauth'=>0]);
        $count['not_lock_count'] = $this->model->count(['islock'=>0]);
        $count['lock_count'] = $this->model->count(['islock'=>1]);


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

        $sqlmap['register_time'] = array('between',array(
            strtotime(date('Y-m-d',$params['stime']).'00:00:00'),
            strtotime(date('Y-m-d',$params['etime']).'23:59:59')
        ));
        $group = 'days';
        $subtext = $params['stime'].'-'.$params['etime'];
        for ($i=0; $i <= $days; $i++) {
            $xAxis[$i] = date('Y-m-d',strtotime("+{$i}day",$params['stime']));
        }



        //注册数
        $field = "FROM_UNIXTIME(register_time,'%Y-%m-%d') days,count(id) as member_num";
        $_reg = $this->_query($field,$sqlmap,$group);

        foreach($_reg as $k =>$v){
            $_reg[$v['days']] = $_reg[$k];
        }


        //组装数据
        foreach ($xAxis as $key => $value) {
            $_regval[] = isset($_reg[$value]['member_num'])?$_reg[$value]['member_num']:'0';
        }

        $row['member'] ['xAxis']= $xAxis;
        $row['member'] ['reg']= $_regval;
        return $row;
    }



}
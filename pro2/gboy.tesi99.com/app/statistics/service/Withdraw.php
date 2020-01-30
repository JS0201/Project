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

class Withdraw extends Model{

    public function initialize()
    {
       $this->model = model('member/Withdraw');
       $this->checkmodel = model('member/RechangeCheck');
    }


    public function _query($field,$sqlmap,$group){
        return $this->model->field($field)->where($sqlmap)->group($group)->select();
    }


    public function count(){

        $result=[];
        $count=[];
        $sum=[];

        $sqlmap='date_format(from_UNIXTIME(`created`),\'%Y-%m-%d\') = date_format(now(),\'%Y-%m-%d\') and `status` = 2 and type = 2';

        $count['day'] = $this->checkmodel->count($sqlmap);
        $sum['day'] = $this->checkmodel->sum('money',$sqlmap);




        $sqlmap=['created'=>['between',[strtotime(date('Y-m-d',strtotime('-1 day'))),strtotime(date('Y-m-d 23:59:59',strtotime('-1 day')))]],'status'=>2,'type'=>2];
        $count['yesterday'] = $this->checkmodel->count($sqlmap);
        $sum['yesterday'] = $this->checkmodel->sum('money',$sqlmap);
        $sqlmap='date_format(from_UNIXTIME(`created`),\'%Y-%m\') = date_format(now(),\'%Y-%m\') and `status`= 2 and `type` = 2';
        $count['current_month'] = $this->checkmodel->count($sqlmap);
        $sum['current_month'] = $this->checkmodel->sum('money',$sqlmap);

        $sqlmap=['created'=>['between',[strtotime('-6 day'),time()]],'status'=>2,'type'=>2];
        $count['week'] = $this->checkmodel->count($sqlmap);
        $sum['week'] = $this->checkmodel->sum('money',$sqlmap);

        $sqlmap=['created'=>['between',[strtotime('-29 day'),time()]],'status'=>2,'type'=>2];
        $count['month'] = $this->checkmodel->count($sqlmap);

        $sum['month'] = $this->checkmodel->sum('money',$sqlmap);

        $count['count'] = $this->checkmodel->count(['status'=>2,'type'=>2]);
        $sum['count'] = $this->checkmodel->sum('money',['status'=>2,'type'=>2]);


        //提现类型
        $sum['bank']=$this->checkmodel->sum('money',['received'=>1,'status'=>2,'type'=>2]);
        $sum['alipay']=$this->checkmodel->sum('money',['received'=>2,'status'=>2,'type'=>2]);
        $sum['wechat']=$this->checkmodel->sum('money',['received'=>3,'status'=>2,'type'=>2]);

        $result['count']=$count;
        $result['sum']=$sum;






        return $result;


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



        $group = 'days';
        //提现
        $field = "FROM_UNIXTIME(created,'%Y-%m-%d') days,SUM(money) as money,count(1) as counts";
        $sqlmap=[];
        $sqlmap['created'] = array('between',array(
            strtotime(date('Y-m-d',$params['stime']).'00:00:00'),
            strtotime(date('Y-m-d',$params['etime']).'23:59:59')
        ));
        $sqlmap['status']=2;
        $sqlmap['type']=2;
        $_withdraw = $this->checkmodel->field($field)->where($sqlmap)->group($group)->select();

        $sqlmap['received']=0;
        $_withdraw_bank = $this->checkmodel->field($field)->where($sqlmap)->group($group)->select();

        unset($sqlmap['received']);
        $sqlmap['received']=1;
        $_withdraw_alipay = $this->checkmodel->field($field)->where($sqlmap)->group($group)->select();

        unset($sqlmap['received']);
        $sqlmap['received']=2;
        $_withdraw_wechat = $this->checkmodel->field($field)->where($sqlmap)->group($group)->select();

        foreach($_withdraw as $k =>$v){
            $_withdraw[$v['days']] = $_withdraw[$k];
            $_withdraw_bank[$v['days']] = $_withdraw_bank[$k];
            $_withdraw_alipay[$v['days']] = $_withdraw_alipay[$k];
            $_withdraw_wechat[$v['days']] = $_withdraw_wechat[$k];
        }




        //组装数据
        foreach ($xAxis as $key => $value) {
            $_withdrawval[] = isset($_withdraw[$value]['money'])?$_withdraw[$value]['money']:'0.00';
            $_withdrawcount[] = isset($_withdraw[$value]['counts'])?$_withdraw[$value]['counts']:'0';
            $_withdrawbank[] = isset($_withdraw_bank[$value]['money'])?$_withdraw_bank[$value]['money']:'0.00';
            $_withdrawalipay[] = isset($_withdraw_alipay[$value]['money'])?$_withdraw_alipay[$value]['money']:'0.00';
            $_withdrawwechat[] = isset($_withdraw_wechat[$value]['money'])?$_withdraw_wechat[$value]['money']:'0.00';
        }

        $row['withdraw'] ['xAxis']= $xAxis;
        $row['withdraw'] ['money']= $_withdrawval;
        $row['withdraw'] ['count']= $_withdrawcount;
        $row['withdraw'] ['sum_bank']= $_withdrawbank;
        $row['withdraw'] ['sum_alipay']= $_withdrawalipay;
        $row['withdraw'] ['sum_wechat']= $_withdrawwechat;
        return $row;
    }



}
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
class Withdraw extends Model{

    public function initialize()
    {
       $this->model = model('member/Withdraw');
        $this->check_model = model('member/RechangeCheck');
       $this->member_service = model('member/Member','service');
        $this->memberlog = model('member/MemberLog');
       $this->finance_service = model('member/MoneyFinance', 'service');
       $this->profile_service = model('member/Profile', 'service');
       $this->usermodel = model('member/Member');
    }


    public function get_list($sqlmap,$page=[]){
        $lists = $this->check_model->alias('c')->join('gboy_member m','m.id = c.uid')->field('m.username,m.id,c.*,c.id as idd')->where($sqlmap)->order('c.id desc')->paginate($page);
        if($lists) {
            foreach($lists as $k => $v) {
                $lists[$k]['created'] = date('Y-m-d H:i:s', $v['created']);
                $lists[$k]['checktime'] = $v['checktime'] ? date('Y-m-d H:i:s', $v['checktime']) : '';
                $lists[$k]['status'] = config('configs.check_status')[$v['status']];
                $lists[$k]['proportion'] = '10%';
                $lists[$k]['cost'] = ($v['money'] / 0.9 )- $v['money'];
                $lists[$k]['received'] = config('configs.received')[$v['received']];
            }
        }
        return $lists;
    }


    public function get_find($sqlmap=[]){

        if(!$result=$this->check_model->alias('c')->join('gboy_member m','m.id = c.uid','left')->field('m.username,m.realname,m.id,c.*,c.id as idd')->where($sqlmap)->find()){
            $this->errors=lang('_param_error_');
            return false;
        }

        $result['rates'] = '10%';
        $result['cost'] = ($result['money'] / 0.9) - $result['money'];
        $result['count_money'] = $this->finance_service->getBi($result['uid'], 'money');
        $result['created'] = date('Y-m-d H:i:s', $result['created']);
        $result['checktime'] = $result['checktime'] ? date('Y-m-d H:i:s', $result['checktime']) : '';
        $result['received'] = config('configs.received')[$result['received']];
        //个人资料
        $result['profile'] = $this->profile_service->getOne("uid = {$result['uid']}");
        return $result;

    }

    public function build_sqlmap($params){
        $sqlmap='';

        if($params['start']) {
            $time[] = ["GT", strtotime($params['start'])];
        }
        if($params['end']) {
            $time[] = ["LT", strtotime($params['end'])];
        }
        if($time){
            $sqlmap['created'] = $time;
        }

        if($params['status']){
            $sqlmap['status'] = $params['status'];
        }
        if($params['keywords']){
            $keywords=trim($params['keywords']);
            $sqlmap['username'] = ['like','%'.$keywords.'%'] ;
        }
        $sqlmap['type'] = 2;
        return $sqlmap;
    }



    public function edit($admin_id, $data)
    {


        if(!$info=$this->get_find(['c.id'=>$data['id']])){
            $this->errors=$this->errors;
            return false;
        }
        $this->check_model->updated("id = {$data['id']}", array('status' => $data['status'],'remark' => $data['remark'],'checktime' => time()));
        if($data['status']=='3'){
            //提现失败或取消资金原路回来
            $id = $this->memberlog->getValue("signid = {$data['id']} and style = 9",'id');
            if(!$id) {
               $re = $this->finance_service->setFinance($info['uid'], $info['money'] / 0.9, 'money', '提现退回',$admin_id, true, 9, '', $info['id']);
               if(!$re) {
                   $this->errors = $this->finance_service->errors;
                   return false;
               }
            }
        }
        return TRUE;
    }

    public function del($id)
    {
        if (!$id) return false;
        $re = $this->check_model->del("id = {$id}");
        if($re !== false){
            // 生成log记录
            AdminLog(1, '', lang("admin_log_member_withdraw"), lang('admin_log_member_delete_withdraw'), $id);
            return true;
        }

        return false;
    }

    //申请提现
    public function apply($uid, $money)
    {
        if(!$money){
            $this->errors = lang('parameter_empty');
            return false;
        }
        if(intval($money) != $money){
            $this->errors = '提现金额必需为整数';
            return false;
        }

        //扣款
        $re = $this->finance_service->setFinance($uid, $money, 'shop_integral', '提现扣款','', false, 8, '', 0);
        if(!$re) {
            $this->errors = $this->finance_service->errors;
            return false;
        }
        //审核表
        $service = new Rechange();
        $id = $service->add(array(
            'uid' => $uid,
            'money' => $money * 0.9,
            'accessory' => '',
            'status' => 1,
            'type'   => 2,
            'created' => time()
        ));
        if(!$id) {
            $this->errors = (lang('_operation_fail_'));
            return false;
        }
        $this->memberlog->sql("update `gboy_member_log` set `signid` = {$id} where id=( select max(id) from (select * from `gboy_member_log`) as b where mid = {$uid})");
        return true;
    }

    public function detail($uid)
    {
        $log = $this->memberlog->sql("select * from gboy_rechange_check as c left join gboy_member_log as l on c.id = l.signid where uid = {$uid} and style in (8,9)");
        if($log) {
            foreach($log as $k => $v) {
                $log[$k]['created'] = date('Y-m-d H:i:s', $v['created']);
                $log[$k]['checktime'] = $v['checktime'] ? date('Y-m-d H:i:s', $v['checktime']) : '';
                $log[$k]['received'] = config('configs.received')[$v['received']];
                $log[$k]['status'] = config('configs.check_status')[$v['status']];
                $log[$k]['cost'] = $v['value'] * 0.1;
            }
        }
        return $log;

    }
}
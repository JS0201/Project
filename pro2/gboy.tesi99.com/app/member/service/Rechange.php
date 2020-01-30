<?php
namespace app\member\service;
use think\Model;

class Rechange extends Model
{
    public function initialize()
    {
        $this->model = model('member/RechangeCheck');
        $this->usermodel = model('member/Member');
        $this->FinanceService = model('member/MoneyFinance', 'service');
        $this->MemberLog = model('member/MemberLog');
    }

    public function add($data)
    {
        return $this->model->add($data);
    }

    public function build_sqlmap($params)
    {
        $sqlmap=[];

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
        }else{
            $sqlmap['status'] = ["GT", 0];
        }
        if($params['username']){
            $keywords=trim($params['username']);
            $sqlmap['username'] = ['like','%'.$keywords.'%'] ;
        }
        $sqlmap['type'] = 1;
        return $sqlmap;
    }

    public function get_list($sqlmap,$page=[])
    {
        $lists = $this->model->alias('c')->join('gboy_member m','m.id = c.uid')->field('m.username,m.id,c.*,c.id as idd')->where($sqlmap)->order('c.id desc')->paginate($page);
        if($lists) {
            $status = config('configs.check_status');
            foreach($lists as $k => $v) {
                $lists[$k]['created'] = date('Y-m-d H:i:s', $v['created']);
                $lists[$k]['checktime'] = $v['checktime'] ? date('Y-m-d H:i:s', $v['checktime']) : '';
                $lists[$k]['status'] = $status[$v['status']];
            }
        }
        return $lists;

    }
    public function getAll($where)
    {
        $lists = $this->model->where($where)->order('id desc')->select();
        return $lists;
    }

    public function edit($admin_id, $data)
    {
        if(!$info=$this->getOne(['id'=>$data['id']])){
            $this->errors=lang('_param_error_');
            return false;
        }
        $check_re = $this->model->updated("id = {$data['id']}", array('status' => $data['status'], 'remark' => $data['remark'],'checktime' => time()));
        if($data['status'] == 2) {
            $id = $this->MemberLog->getValue("mid = {$info['uid']} and signid = {$data['id']}",'id');
            if($check_re !== false) {
                if(!$id) {
                    return $this->FinanceService->setFinance($info['uid'], $info['money'], 'money', '充值成功',$admin_id, true, 7, '', $data['id']);
                }
                return true;
            }
            $this->errors=lang('_operation_fail_');
            return false;
        }
        return true;
    }
    public function del($id)
    {
        if(!$id) return false;
        $re = $this->model->del("id = {$id}");
        if($re !== false) {
            // 生成log记录
            AdminLog(1, '', lang("admin_log_member_recharge"), lang('admin_log_member_delete_recharge'), $id);
            return true;
        }
        $this->errors=lang('_operation_fail_');

        return false;
    }

    //获取详情页内容
    public function getMessage($id)
    {
        $rechange = $this->getOne("id = {$id}");
        $status = config('configs.check_status');
        if(!$rechange) {
            $this->errors = lang('_param_error_');
            return false;
        }
        $user = $this->usermodel->getOne("id = {$rechange['uid']}");

        $rechange['username'] = $user['username'];
        $rechange['realname'] = $user['realname'];
        $rechange['status_text'] = $status[$rechange['status']];
        $rechange['created'] = date('Y-m-d H:i:s', $rechange['created']);
        $rechange['checktime'] = $rechange['checktime'] ? date('Y-m-d H:i:s', $rechange['checktime']) : '';
        return $rechange;
    }

    public function getOne($where)
    {
        return $this->model->getOne($where);
    }
}
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\controller;
use app\common\controller\Init;
use app\member\service\MoneyFinance;
use think\Session;
class Member extends Init{

    public function _initialize()
    {
        parent::_initialize();
        $this->service = model('member/Member','service');
        $this->group_service = model('member/MemberGroup','service');
    }



    public function index(){
        $sqlmap=$this->service->sqlmap(input('get.'));
        $list=$this->service->get_list($sqlmap);
        $service = new MoneyFinance();
        if($list) {
            foreach($list as $k => $v) {
                $list[$k]['money'] = $service->getBi($v['id'], 'money');
                $list[$k]['shop_integral'] = $service->getBi($v['id'], 'shop_integral');
            }
        }
        $member_group=$this->group_service->get_column();
        $member_group[0]='所有等级';
        ksort($member_group);
        $this->assign('list',$list)->assign('member_group',$member_group);
        return $this->fetch();
    }


    public function add(){
        if(is_post()){
            if(!$this->service->edit(input('post.'))){
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'),1);
        }else{

            $member_group=$this->group_service->get_column();
            $this->assign('member_group',$member_group);
            return $this->fetch('edit');
        }


    }


    public function edit(){
        $info=$this->service->get_find(['id'=>input('id/d')]);
        if(is_post()){
            $validate = new \app\member\validate\Member();
            if(!$validate->scene('edit')->check(input('post.'))) {
                showmessage($validate->getError());
            }
            $authkey=Session::get('gboy_admins_authkey');
            list($admin_id, $authkey) = explode("\t", authcode($authkey, 'DECODE'));
            if(!$this->service->setMoney(input('post.'),$admin_id)){
                showmessage($this->service->errors);
            }

            if(!$this->service->edit(input('post.'),true,false)){
                showmessage($this->service->errors);
            }

            showmessage(Lang('_operation_success_'), url('index'),1);
        }else{
            $service = new MoneyFinance();
            $info['profile'] = model('member/MemberProfile')->getByUid($info['id']);
            $info['money'] = $service->getBi($info['id'], 'money');
            $info['shop_integral'] = $service->getBi($info['id'], 'shop_integral');
            $member_group=$this->group_service->get_column();
            $this->assign('member_group',$member_group)->assign('info',$info);
            return $this->fetch();
        }

    }

    public function delete()
    {

        $ids = input('param.id/a');

        if(!$this->service->del($ids)) {
            showmessage($this->service->errors);
        }

        showmessage(lang('_operation_success_'), url('index'), 1);
    }


}
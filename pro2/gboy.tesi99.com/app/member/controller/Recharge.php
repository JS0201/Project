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
use think\Session;
class Recharge extends Init{

    public function _initialize()
    {

        parent::_initialize();
        $this->service = model('member/Rechange','service');
    }


    //充值记录
    public function index(){
        $sqlmap=$this->service->build_sqlmap(input('get.'));
        $list=$this->service->get_list($sqlmap);
        $this->assign('list',$list)->assign('member_group');
        return $this->fetch();
    }

    public function edit(){

        if(is_post()){
            $authkey=Session::get('gboy_admins_authkey');
            list($admin_id, $authkey) = explode("\t", authcode($authkey, 'DECODE'));
            if(!$this->service->edit($admin_id, input('post.'))){
                showmessage($this->service->errors);
            }

            // 生成log记录
            $name = lang('admin_log_member_edit_recharge');
            $status = input('post.status');
            $contetn = lang("admin_log_member_edit_recharge_$status");
            AdminLog(1, $contetn, lang("admin_log_member_recharge"), $name, input('post.id'));

            showmessage(Lang('_operation_success_'), url('index'),1);
        }else{
            $info=$this->service->getMessage(input('id/d'));
            $this->assign('info',$info);
            return $this->fetch();
        }
    }
    public function delete()
    {
        $re = $this->service->del(input('id/d'));
        if(!$re) {
            showmessage($this->service->errors);
        }
        showmessage(Lang('_operation_success_'), url('index'),1);
    }
}
<?php
namespace app\common\controller;
class Base extends Common
{


	public function __construct()
    {
        hook('webSite');
        parent::__construct();

        $this->member_service = model('member/Member','service');

        $this->member=$this->member_service->inits();
        $this->assign('member',$this->member);
        $id=$this->member['id'];
        $money_finance = db('money_finance')->where(array('uid' => $id))->find();
        $this->assign('money_finance',$money_finance);
	}

    public function _empty(){


        error_status(404);

    }

}

<?php

namespace app\peer\controller;
use app\member\controller\Check;
class Index extends Check {

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $code = db('member')->where("id = {$this->member['id']}")->value('recharge_code');
        $value = db('setting')->where("`key` = 'usdt'")->value('value');
        $usdt = round(5 / $value, 4);
        $this->assign('usdt', $usdt);
        $this->assign('code', $code);
        return $this->fetch();
    }

}

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

class MemberLog extends Init{

    public function _initialize()
    {
        parent::_initialize();
        $this->service = model('member/MemberLog','service');
    }

    public function index(){
        if(false===$sqlmap = $this->service->build_sqlmap(input('get.'))){
            showmessage($this->service->errors);
        }
        $list=$this->service->get_list($sqlmap);
        $type=model('member/MemberLog')->type();
        array_unshift($type,'请选择');
        $this->assign('list',$list)->assign('type',$type);
        return $this->fetch();
    }

}
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\ads\controller;
use app\common\controller\Init;

class Admin extends Init{

    public function _initialize()
    {
        parent::_initialize();

        $this->service = model('ads/Ads','service');
        $this->position_service = model('ads/AdsPosition','service');
    }


    public function index(){

        $sqlmap=[];

        $list=$this->service->get_list($sqlmap);

        $this->assign('list',$list);
        return $this->fetch();
    }



    public function add(){

        $position_format=$this->position_service->get_position([],'id,name');

        if(is_post()){
            if (!$this->service->edit(input('post.'))) {
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'),1);

        }else{

            $this->assign('position_format',$position_format);
            return $this->fetch('edit');

        }


    }


    public function edit(){

        $position_format=$this->position_service->get_position([],'id,name');

        if(is_post()){
            if (!$this->service->edit(input('post.'), true)) {
                showmessage($this->service->errors);
            }
            showmessage(Lang('_operation_success_'), url('index'),1);

        }else{

            $id=input('id/d');
            $info=$this->service->get_by_id(['id'=>$id]);
            $this->assign('position_format',$position_format);
            $this->assign('info',$info);
            return $this->fetch();

        }

    }


    public function delete()
    {

        if(!$this->service->del(input('param.id/a'))) {
            showmessage($this->service->errors);
        }

        showmessage(lang('_operation_success_'), url('index'), 1);
    }


}
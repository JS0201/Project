<?php
namespace app\admin\controller;
use app\common\controller\Init;
class District extends Init{

	public function _initialize(){
        parent::_initialize();

        $this->service = model('admin/District','service');
		
	}
	
	
	public function index(){

        $lists=$this->service->get_lists();

        $this->assign('lists',$lists);
		return $this->fetch();
	}


    public function add() {
        $parent_id = input('parent_id/d');
        $parent_pos = array('顶级地区');
        if ($parent_id > 0) {
            $parent_pos = $this->service->fetch_position($parent_id);
        }
        if (is_post()) {
            if (FALSE === $this->service->edit(input('post.'))) {
                showmessage($this->service->errors);
            }
            showmessage(lang('add_address_success'), url('index'), 1);
        } else {
            $this->assign('parent_id',$parent_id)->assign('parent_pos',$parent_pos);
            return $this->fetch('district_add');
        }
    }


    public function edit() {
        $id = input('id/d');

        if ($id < 1) {

            showmessage(lang('_param_error_'));
        }

        $info = $this->service->get_find($id);
        $parent_pos = array('顶级地区');
        if ($info['parent_id'] > 0) {
            $parent_pos = $this->service->fetch_position($info['id']);
        }
        if (is_post()) {

            $params = array_merge($info->toArray(), input('post.'));

            if (FALSE === $this->service->edit($params,true)) {
                showmessage($this->service->errors);
            }

            showmessage(lang('edit_address_success'), url('index'), 1);
        } else {

            $this->assign('parent_pos',$parent_pos)->assign('info',$info);
            return $this->fetch('district_edit');
        }
    }


	public function ajax_district(){


        $id = (int) input('id');

        $result = $this->service->get_lists($id);

        echo  json_encode($result);
    }

    public function delete() {
        $ids =input('id/a');
        $result = $this->service->del($ids);
        if ($result === false) {
            showmessage($this->service->errors);
        }
        showmessage(lang('delete_region_success'));
    }
	
}
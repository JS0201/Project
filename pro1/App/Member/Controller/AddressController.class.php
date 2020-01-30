<?php

namespace Member\Controller;

class AddressController extends CheckController {

    public function _initialize() {
        parent::_initialize();

        $this->service = D('Member/MemberAddress', 'Service');
    }
	public function index() {
        if(IS_POST) {
            $id = I('post.id');
            if(!$id) {
                showmessage('参数异常');
            }
            $def_id = M('member_address')->where("uid = {$this->member['id']} and `isdefault` = 1")->getField('id');
            if($def_id) {
                M('member_address')->where("id = {$def_id}")->save(array('isdefault' => 0));
                M('member_address')->where("id = {$id}")->save(array('isdefault' => 1));
            }
            showmessage('操作成功','',1);
        }else {
            $cart_info = D('Order/Cart', 'Service')->get_cart_lists();
            $this->assign('cart_info', $cart_info)->display();
        }
    }
	
    public function add() {


        if (IS_AJAX) {
            $sqlmap = array();
            $sqlmap['name'] = I('post.name');
            $sqlmap['province'] = I('post.province');
            $sqlmap['city'] = I('post.city');
            $sqlmap['county'] = I('post.county');
            $sqlmap['address'] = I('post.address');
            $sqlmap['mobile'] = I('post.mobile');
            $sqlmap['uid'] = $this->member['id'];
            if (!$this->service->save($sqlmap)) {
                showmessage($this->service->errors);
            }
            showmessage(L('_os_success_'), U('index'), 1);
        } else {
            $this->display();
        }
        
    }

    public function edit() {
        $_GET['id'] = (int) $_GET['id'];
        if ($_GET['id'] < 1) {
            showmessage(L('_params_error_'));
        }
        $address = $this->service->uid($this->member['id'])->fetch_by_id($_GET['id']);
        if (!$address) {
            showmessage(L('_data_not_exist_'));
        }
        if (IS_AJAX) {

            $sqlmap = array();
            $sqlmap['id'] = I('post.id');
            $sqlmap['name'] = I('post.name');
            $sqlmap['province'] = I('post.province');
            $sqlmap['city'] = I('post.city');
            $sqlmap['county'] = I('post.county');
            $sqlmap['address'] = I('post.address');
            $sqlmap['mobile'] = I('post.mobile');
            $sqlmap['uid'] = $this->member['id'];

            $result = $this->service->save($sqlmap);
            if ($result === FALSE) {
                showmessage($this->service->errors);
            }
            showmessage(L('_operation_success_'), U('index'), 1);
        } else {

            $this->assign('address', $address)->display();
        }
    }
    public function delete() {
        if(IS_POST) {
            $id=I('post.id');
            $address=M("member_address")->where(array("id"=>$id))->find();
            if($address['isdefault']==1){
                showmessage('此为默认地址，请修改后删除！');
            }else{
                $re=M("member_address")->where(array("id"=>$id))->delete();

                if(!$re){
                    showmessage('删除失败');
                }else{
                    showmessage('删除成功','',1);

                }
            }

        }

    }
//    public function delete() {
//        $id = (int) $_GET['id'];
//        if ($id < 1)
//            showmessage(L('_params_error_'));
//        $result = $this->service->uid($this->member['id'])->delete($id);
//        if ($result === FALSE) {
//            showmessage($this->service->errors);
//        }
//        showmessage(L('_os_success_'), U('index'), 1);
//    }

}

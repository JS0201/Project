<?php

namespace Member\Controller;

class CommentController extends CheckController {

    public function _initialize() {
		
        parent::_initialize();
       
	   $this->order_sku_service = D('Order/Order_sku','Service');
	   $this->comment_service = D('Comment/Comment','Service');
    }

    public function index() {
		
		$ac=$_GET['ac'];
		$sqlmap=array();
		if($ac=='ok'){
			
			$sqlmap['iscomment']=1;
		}else{
			$sqlmap['iscomment']=0;
		}
		
		
		$sqlmap['buyer_id']=$this->member['id'];
		$sqlmap['delivery_status']=2;
		
		 
		$result=$this->order_sku_service->lists($sqlmap);
		
        $this->assign($result,$result)->display();
    }
	
	public function add() {
		
		if(IS_AJAX) {
			$data=array();
			$data['id'] = 0;
			$data['tid'] = (int)$_POST['tid'];
			$data['mid'] = $this->member['id'];
			$data['content'] = I('post.content');
			$data['username'] = $this->member['username'];
			
			if(!$data['content']){
				showmessage('请输入评价内容');
			}
			
			$result = $this->comment_service->add($data);
			if($result === false) {
				showmessage($this->comment_service->errors);
			}
			
			showmessage('评价成功', U('index'), 1);
		} else {
			$id=(int)$_GET['id'];
			$sqlmap=array();
			$sqlmap['buyer_id']=$this->member['id'];
			$sqlmap['id']=$id;
			$sqlmap['delivery_status']=2;
			$sqlmap['iscomment']=0;
			 
			if(!$info=$this->order_sku_service->find($sqlmap)){
				showmessage('评价不存在');
			
			}
			
			
			$this->assign('info',$info)->display();
		}
	}
	

}

<?php

namespace Comment\Service;

use Think\Model;

class CommentService extends Model {

    public function __construct() {
		
        $this->model = D('Comment/Comment');
        $this->order_sku_service = D('Order/Order_sku','Service');
    }

	
	public function lists($sqlmap = array(), $limit = 20, $order = 'id DESC', $page = 1) {
		$this->sqlmap = array_merge($this->sqlmap, $sqlmap);
	 
		$lists = $this->model->where($this->sqlmap)->page((int)$page)->limit($limit)->order($order)->select();
		$count = $this->model->where($this->sqlmap)->count();
        foreach ($lists as $key => $value) {
        	//$sku = $this->sku_service->fetch_by_id($value['sku_id'],'spu');
			
        	$user = D('Member/Member','Service')->find(array('id'=>$value['mid']),'nickname,face');
			
			$value['username']=$user['nickname'];
			
            $value['username'] = cut_str($value['username'], 1, 0).'**'.cut_str($value['username'], 1, -1);
            $value['imgs'] = ($value['imgs']) ? json_decode($value['imgs']) : array();
        	//$value['_sku'] = $sku;
        	$value['datetime'] = date('Y-m-d', $value['datetime']);
        	//$value['avatar'] = getavatar($value['mid']);
        	$lists[$key] = $value;
        }
        $result = array('count' => $count, 'lists' => $lists);
        return $result;
	}
	
	
	
	public function add($params) {
		$r = $this->order_sku_service->find(array('id'=>$params['tid']));
		if($r === false) {
			$this->errors = L('_data_not_exist_');
			return false;
		}
		
		if($params['mid'] < 1 || $params['mid'] != $r['buyer_id']) {
			$this->errors = L('_data_not_exist_');
			return false;
		}
		if($r['iscomment'] == 1) {
			$this->errors = '请勿重复发表';
			return false;
		}
		$params['spu_id'] = M('goods_sku')->where(array('sku_id'=>$r['sku_id']))->getField('spu_id');
		$params['sku_id'] = $r['sku_id'];
		$params['order_sn'] = $r['order_sn'];
		$params['datetime']=time();
		$params['clientip']=getip();
		
		//runhook('comment_add',$params);
		$result = $this->model->data($params)->add();
		if(!$result) {
			return false;
		}
		
		M('order_sku')->where(array('id'=>$params['tid'],'buyer_id'=>$params['mid']))->data(array('iscomment'=>1))->save();
		
		return true;
	}

}

?>
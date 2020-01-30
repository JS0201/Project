<?php

namespace Member\Service;

use Think\Model;

class MemberFavoriteService extends Model {

    public function __construct() {

        $this->model = D('Member/Member_favorite');
        $this->goods_sku_service = D('Goods/Goods_sku', 'Service');
    }
    

    /**
     * 收藏列表
     */
    public function lists($sqlmap = array(), $limit = 20, $page = 1) {
        $this->sqlmap = array_merge($this->sqlmap, $this->build_map($sqlmap));
       
        $lists = $this->model->where($this->sqlmap)->page($page)->limit($limit)->select();
		
        foreach ($lists as $key => $value) {
         
			$sku = $this->goods_sku_service->detail($value['sku_id']);
            if($sku === false) continue;
            $lists[$key]['_sku'] = $sku;
        }
        $count = $this->model->where($this->sqlmap)->count();
        return array('count' => $count, 'lists' => $lists);
    }
	
	
	
	
    /**
     * 构造查询语句
     */
	private function build_map($data){
		switch($data['datetime']){
			case 'week':
				$data['datetime'] = array(array('GT',strtotime('last monday')),array('LT',time()));
				break;
			case 'month':
				$data['datetime'] = array(array('GT',strtotime(date('Y-m-01'))),array('LT',time()));
				break;
			case 'year':
				$data['datetime'] = array(array('GT',strtotime(date('Y-01-01'))),array('LT',time()));
				break;
			case 'lastyear':
				$data['datetime'] = array('LT',strtotime(date('Y-01-01')));
				break;
			default:
				$data['datetime'] = array('LT',time());
				break;
		}
		return  $data;
	}


    /**
     * 设置mid
     */
    public function set_mid($mid) {
        if ((int) $mid > 0) {
            $this->sqlmap['mid'] = $mid;
        }
        return $this;
    }

    /* 加入收藏夹 */

    public function add($sku_id, $sku_price = 0, $mid = 0, $id = 0, $datetime = 0) {
        if (!$sku_price) {
            $_sku_info = $this->goods_sku_service->find(array('sku_id' => $sku_id));
            if (!$_sku_info) {
                $this->errors = $this->goods_sku_service->errors;
                return false;
            }
        }
        $this->sqlmap['sku_id'] = $sku_id;
        $this->sqlmap['sku_name'] = $_sku_info['sku_name'];
        $this->sqlmap['sku_price'] = $_sku_info['shop_price'];
        $this->sqlmap['datetime'] = $datetime ? $datetime : time();
        if (!$this->sqlmap['mid']) {
            $this->sqlmap['mid'] = $mid;
            $this->sqlmap['id'] = $id;
        }
        if ($this->is_exists($sku_id) === true) {
            $this->errors = L('repeat_collect');
            return false;
        }
        // runhook('member_favorite_add', $this->sqlmap);
        $data = $this->model->data($this->sqlmap)->add();
        if (!$data) {
            $this->errors = L('_os_error_');
            return false;
        }
        return true;
    }

    public function is_exists($sku_id) {
        $_sqlmap = array();
        $_sqlmap['mid'] = $this->sqlmap['mid'];
        $_sqlmap['sku_id'] = $sku_id;
        return ($this->model->where($_sqlmap)->count() > 0) ? true : false;
    }

	
    /* 删除收藏夹 */
    public function del($sku_id = array()) {
        $this->sqlmap['sku_id'] = array("IN", $sku_id);
        //runhook('member_favorite_delete',$sku_id);
        $result = $this->model->where($this->sqlmap)->delete();
       
        return true;
    }
	
	
}

?>
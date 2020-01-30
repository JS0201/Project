<?php

namespace Order\Service;

use Think\Model;

class OrderSkuService extends Model {

    public function __construct() {
        $this->model = D('Order/Order_sku');
        $this->goods_sku_service = D('Goods/Goods_sku', 'Service');
    }

    /**
     * [fetch_by_id 获取一条子商品信息]
     * @param  [type]  $id    [description]
     * @param  boolean $field [description]
     * @return [type]         [description]
     */
    public function fetch_by_id($id = 0, $extra = '') {
        if ((int) $id < 1) {
            $this->errors = L('_params_error_');
            return FALSE;
        }
        $goods = $this->goods_sku_service->detail($id);
        
        /*
        if ($extra) {
            $extra = explode(",", $extra);
            foreach ($extra AS $method) {
                if (method_exists($this->sku_db, $method)) {
                    $goods = $goods->$method();
                }
            }
        }
         * 
         */
       // $sku = $goods->output();
       // runhook('after_sku_fetch_by_id', $sku);
        return $goods;
    }

    /**
     * 创建订单商品
     * @param  array	$params 订单商品相关参数
     * @return [boolean]
     */
    public function create_all($params) {
        if (count(array_filter($params)) < 1) {
            $this->errors = L('order_goods_empty');
            return FALSE;
        }

        foreach ($params as $key => $val) {
            $goods = $this->goods_sku_service->find(array('sku_id' => $val['sku_id']));
            $sku_info['sku_thumb'] = $val['sku_thumb'];
            $sku_info['sku_barcode'] = $val['sku_barcode'];
            $sku_info['sku_name'] = $val['sku_name'];
            $sku_info['sku_spec'] = $val['sku_spec'];
            $sku_info['sku_price'] = $val['sku_price'];
            $sku_info['real_price'] = $val['real_price'];
            $sku_info['content'] = $goods['content'];
            $sku_info['img_list'] = json_encode($goods['img_list']);
            $val['spu_id'] = $goods['spu_id'];
            $val['sku_info'] = json_encode($sku_info);

            $result = $this->model->add($val);
            if (!$result) {
                $this->errors = L('_os_error_');
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * 根据主订单号获取订单商品
     * @param 	$sn : 主订单号
     * @return 	[result]
     */
    public function get_by_order_sn($sn = '') {
        $sn = (string) trim($sn);
        if ($sn == '') {
            $this->errors = L('order_sn_not_null');
            return FALSE;
        }
        $sqlmap = $arr = array();
        $sqlmap['order_sn'] = $sn;
        $result = $this->model->where($sqlmap)->order('id asc')->select();
        if (!$result) {
            $this->errors = L('order_goods_not_exist');
            return FALSE;
        }
        foreach ($result as $k => $val) {
            if (!empty($val['sku_spec'])) {
                $val['sku_spec'] = json_decode($val['sku_spec'], TRUE);
            }
            if (!empty($val['sku_spec'])) {
                $_spec = '';
                foreach ($val['sku_spec'] as $key => $v) {
                    $_spec .= $v['name'] . '：' . $v['value'] . '&nbsp;&nbsp;';
                }
                $val['_sku_spec'] = $_spec;
            }
            $arr[$val['id']] = $val;
        }
        return $arr;
    }
	
	
	
	/**
	 * 通用列表接口
	 * @param  array   $sqlmap 查询条件(默认空)
	 * @param  integer $limit  获取条数(默认取20条)
	 * @param  integer $page   分页页码(默认1)
	 * @param  string  $order  排序(默认主键降序)
	 * @return [result]
	 */
	public function lists($sqlmap = array(), $limit = 20,  $page = 1,$order = 'id DESC') {
		
		$this->sqlmap = array_merge($this->sqlmap, $sqlmap);
   
        $lists = $this->model->where($this->sqlmap)->page($page)->limit($limit)->order($order)->select();
        foreach ($lists as $key => $value) {
        	$value['sku_spec'] = json_decode($value['sku_spec'], true);
        	$value['url'] = U("goods/index/detail",array('sku_id' => $value['sku_id']));
        	$lists[$key] = $value;
        }
        $count = $this->model->where($this->sqlmap)->count();
        return array('count' => $count, 'lists' => $lists);
	}
	
	
	/**
	 * @param  array 	sql条件
	 * @param  integer 	读取的字段
	 * @return [type]
	 */
	public function find($sqlmap = array(), $field = "") {
		$result = $this->model->where($sqlmap)->field($field)->find();
		
		return $result;
	}
	
	
	
	

}

?>
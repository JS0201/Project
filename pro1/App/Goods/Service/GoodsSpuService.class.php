<?php

namespace Goods\Service;

use Think\Model;

class GoodsSpuService extends Model {

    public function __construct() {

        $this->category_service = D('Goods/Goods_category', 'Service');
        $this->sku_service = D('Goods/Goods_sku', 'Service');
        $this->brand_service = D('Goods/Brand', 'Service');
        $this->model = D('Goods/Goods_spu');
        $this->sku_model = D('Goods/Goods_sku');
        $this->goods_index_model = D('Goods/Goods_index');
    }

    /**
     * [select 列表]
     * @return [type]            [description]
     */
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'sort asc') {

        $count = $this->model->where($sqlmap)->count();
        $pages = new \Think\Page($count, $limit);
        $page = $page ? $page : 1;

        if (isset($_GET['p'])) {
            $page = (int) $_GET['p'];
        }


        if ($limit != '') {

            $limits = (($page - 1) * $limit) . ',' . $limit;
        }

        $result = $this->model->where($sqlmap)->order($order)->limit($limits)->select();

        foreach ($result as $key => $value) {
		
            $parent_name = $this->category_service->get_parent_name($value['catid']);
            $brand_info = $this->brand_service->find(array('id'=>$value['brand_id']));
            
            $lists[] = array(
                'id' => $value['id'],
                'sn' => $value['sn'],
                'name' => $value['name'],
                'brand_name' => $brand_info['name'],
                'price' => $value['min_price'] . '-' . $value['max_price'],
                'number' => $value['sku_total'],
                'sort' => $value['sort'],
                'status' => $value['status'],
                'thumb' => $value['thumb'] ? $value['thumb'] : $value['thumb'],
                'cate_name' => $value['parent_name'],
                'cate_name' => $parent_name,
                'catid'=>$value['catid'],
            );
        }

        return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
    }

    /**
     * [find 获取]
     * @param [array] $sqlmap [条件]
     * @return [boolean]         [返回ture or false]
     */
    public function find($sqlmap) {

        $result = array();

        $data = $this->model->where($sqlmap)->find();

        if ((int) $data['id'] < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }

        $result['spu'] = $data;

        /* 返回值 */

        $extra = 'brand,category,sku,type';

        if ($extra) {
            $extra = explode(",", $extra);
            foreach ($extra AS $val) {
                $method = "get_extra_" . $val;
                if (method_exists($this->model, $method)) {
                    $result['_' . $val] = $this->model->$method($data);
                }
            }
        }

        return $result;
    }

    /**
     * [save 保存]
     * @param [array] $goods		[参数]
     * @return [boolean]         [返回ture or false]
     */
    public function save($goods) {

        $spu = $goods['spu'];
        $specs = $goods['specs'];
        $spu_imgs = $goods['album'][0]; //spu组图
        $spu['imgs'] = $spu_imgs ? json_encode($spu_imgs) : '';
        $spu['thumb'] = $spu_imgs ? $spu_imgs[0] : ''; //spu主图


        $data = $this->model->create($spu);
        if (!$data) {

            $this->errors = $this->model->geterror();
            return false;
        }

        if ($data['id']) {
            $result = $this->model->save($data);
            $spu['id'] = $data['id'];
        } else {
            $result = $this->model->add($data);
            $spu['id'] = $result;
        }

        if ($result === false) {
            $this->errors = L('_os_error_');
            return false;
        }


        foreach ($specs as $key => $spec) {
            $_value = array();
            if (!strpos($spec, '___hd___')) {
                parse_str($spec, $myAarray);
                $_value = current($myAarray);
            } else {
                $arr = explode("___hd___", $spec);
                foreach ($arr as $k => $v) {
                    preg_match('/.*\[([a-zA-Z0-9]{32}+)\].*\=(.*)/', $v, $res);

                    if ($key == 'spec') {
                        $_value[$res[1]][] = $res[2];
                    } else {
                        $_value[$res[1]] = $res[2];
                    }
                }
            }
            $params[$key] = $_value;
        }


        //组装sku数据
        $skus = $data = array();


        foreach ($params['sn'] as $key => $sn) {
            $skus[$key]['uid'] = $params['uid'][$key]?$params['uid'][$key]:0;
            $skus[$key]['sid'] = $params['sid'][$key]?$params['sid'][$key]:0;
            $skus[$key]['sn'] = $sn;
            $skus[$key]['status_ext'] = $params['status_ext'][$key];
            $skus[$key]['barcode'] = $params['barcode'][$key];
            $skus[$key]['spec'] = $params['spec'][$key] ? $params['spec'][$key] : '';
            $skus[$key]['shop_price'] = $params['shop_price'][$key];
            $skus[$key]['market_price'] = $params['market_price'][$key];
            $skus[$key]['number'] = $params['number'][$key];
            $skus[$key]['sku_id'] = $params['sku_id'][$key];
            $spu['sku_total'] += $params['number'][$key];
            $skus[$key]['weight'] = $params['weight'][$key];
            $skus[$key]['volume'] = $params['volume'][$key];
            $skus[$key]['spu_id'] = $spu['id'];
            $skus[$key]['status'] = $spu['status'];
            $skus[$key]['sort'] = $spu['sort'];
            $skus[$key]['sku_name'] = $spu['name'];
        }

        //sku数据入库操作
        $skuinfo = $this->sku_service->create_sku($skus);

        //组织spu总库存最大最小价格
        $_price = $this->sku_model->field("min(shop_price) AS min_price, max(shop_price) AS max_price")->where(array("spu_id" => $spu['id']))->find();

        $this->model->save(array('id' => $spu['id'], 'min_price' => $_price['min_price'], 'max_price' => $_price['max_price'], 'sku_total' => $spu['sku_total']));

        //创建商品索引
        $this->create_goods_index($spu, $skuinfo);

        return true;
    }

    /**
     * [create_goods_index 生成商品索引表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function create_goods_index($spu, $skus) {


        foreach ($skus AS $sku) {
            $item = array();
            $item['sku_id'] = $sku['sku_id'];
            $item['spu_id'] = $spu['id'];
            $item['catid'] = $spu['catid'];
            $item['brand_id'] = $spu['brand_id'];
            $item['shop_price'] = $sku['shop_price'];
            $item['show_in_lists'] = $sku['show_in_lists'] ? $sku['show_in_lists'] : 0;
            $item['status'] = $spu['status'];
            $item['status_ext'] = $sku['status_ext'] ? $sku['status_ext'] : 0;
            $item['sort'] = $spu['sort'];
            $skuindex = $this->goods_index_model->find($item['sku_id']);
            if (empty($skuindex)) {
                $this->goods_index_model->add($item);
            } else {
                $this->goods_index_model->save($item);
            }
        }

        return TRUE;
    }

    /**
     * [change_spu_info 改变商品货号]
     * @param  [array] $params []
     * @return [boolean]     [返回更改结果]
     */
    public function change_spu_info($params) {
        if ((int) $params['id'] < 1) {
            $this->errors = L('_data_not_exist_');
            return FALSE;
        }
        $result = $this->model->where(array('id' => $params['id']))->save($params);
        if (!$result) {
            $this->errors = L('_os_error_');
        }
        return $result;
    }

    /**
     * [del 删除商品]
     * @param  [type] $id [description]
     * @param  [boolean] $isdel  [true为删除，false为回收站]
     * @return [type]         [description]
     */
    public function del($id, $isdel = false) {

        $data = $sqlmap = $map = array();
        if ($id) {
            if ($isdel) {
                $result = $this->delete_goods($id);
                return $result;
            } else {
                $sqlmap['id'] = $map['spu_id'] = array('IN', $id);
                $data['status'] = -1;
                $result = $this->model->where($sqlmap)->save($data);

                $this->sku_model->where($map)->save($data);
                $this->goods_index_model->where($map)->save($data);
                if (!$result) {
                    $this->errors = L('_os_error_');
                    return FALSE;
                }
                return TRUE;
            }
        } else {
            $this->errors = L('_params_error_');
            return FALSE;
        }
    }

    /**
     * [delete_goods 真删除商品]
     * @param  [array] $id [商品id]
     * @return [type]     [description]
     */
    private function delete_goods($id) {
        $id = (array) $id;
        if (empty($id)) {
            $this->errors = L('_params_error_');
            return FALSE;
        }
        $_goods_del_result = $this->model->where(array('id' => array("IN", $id)))->delete();
        $_pro_del_result = $this->sku_model->where(array('spu_id' => array('IN', $id)))->delete();
        $this->goods_index_model->where(array('spu_id' => array('IN', $id)))->delete();
        return true;
    }

	
	
	/**
	 * [count_spu_info 统计商品信息]
	 * @param  [type] $status [商品状态]
	 * @return [type]         [description]
	 */
	public function count_spu_info($status){
		$spu = C("DB_PREFIX").'goods_spu';
		if($status != 2){
			$result = $this->sku_model->where(array('status' => $status))->count();
		}else{
			$sqlmap[C("DB_PREFIX").'goods_sku.status'] = 1;
			$sqlmap['number'] = array('EXP','<='.$spu.'.warn_number');
			
		
			
			$result =  $this->sku_model->join($spu.' on id = spu_id')->where($sqlmap)->count();
		}
		return $result;
	}
	
	
	
	
	
}

?>
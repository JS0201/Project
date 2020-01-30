<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\goods\service;
use think\Model;
use think\config;

class Sku extends Model{



    public  function initialize()
    {
        $this->model = model('goods/Sku');
        $this->sku_db = db('goods_sku');
        $this->spu_db = db('goods_spu');
    }
    //商品列表
    public function get_list($where = '', $isindex = true, $start = 0, $limit = 20){
        $result = array();
        if($isindex) { //积分商城
            $result = $this->sku_db->where('type = 2 and status = 1')->limit(20)->order('shop_price asc')->select(); //套餐
        }else{ //消费商城
            $result = $this->sku_db->where($where)->limit($start, $limit)->order('shop_price asc')->select();
        }
        if($result) {
            $config = config('aliyun_oss');
            foreach($result as $k => $v) {
                $result[$k]['thumb'] = $config['Url'].$result[$k]['thumb'];
            }
        }
        return $result;
    }

    //规格列表
    public function get_spec($where = ''){
        $result = $this->sku_db->where($where)->order('sku_id asc')->column('spec_query'); //规格
        return $result;
    }

    public function get_find(array $sqlmap){
        return $this->model->where($sqlmap)->find();
    }

    public function getGood($where)
    {
        return $this->model->getOne($where);

    }

    /**
     * [create_sku_name 生成子商品名称]
     * @param  [type] $params [商品参数]
     * @return [string]         [子商品名称]
     */
    public function create_sku_name($spec_array){


        $name = '';
        $spec_array=$spec_array?json_decode($spec_array,true):$spec_array;

        foreach ($spec_array as $k => $v) {
            $name .= ' '.$v['value'];
        }
        return $name;
    }


    public function create_sku($params){
        if(!empty($params['del'])){
            $map = array();
            $map['sku_id'] = ["IN", $params['del']];
            $this->model->where($map)->delete();
        }


        //sku新增数据处理
        if(isset($params['new'])){
            foreach ($params['new'] as $key => $new_data) {
                $imgs = json_decode($new_data['imgs']);
                $new_data['thumb'] =  $imgs[0] ? $imgs[0] : '';
                $new_data['imgs'] = $new_data['imgs'] ? $new_data['imgs'] : '';
                //$new_data['spec'] = !empty($new_data['spec']) ? $new_data['spec'] : '';
                $result = $this->model->isupdate(false)->save($new_data);

                if ($result === false) {
                    $this->errors = $this->model->getError();
                    return false;
                }

                $params['new'][$key]['sku_id']=$this->model->sku_id;
            }
        }
        //sku编辑数据处理
        if(isset($params['edit'])){
            foreach ($params['edit'] AS $edit_data) {
                $edit_data['thumb'] = $edit_data['imgs'][0] ? $edit_data['imgs'][0] : '';
                $edit_data['imgs'] = $edit_data['imgs'] ? json_encode($edit_data['imgs']) : '';
                //$edit_data['spec'] = !empty($edit_data['spec']) ? $edit_data['spec'] : '';
                $result=$this->model->update($edit_data);

                if ($result === false) {
                    $this->errors = $this->model->getError();
                    return false;
                }

            }
        }

        return $params;
    }


    /**
     * 指定商品减少库存
     * @param [int] $id 子商品ID
     * @param [int] $number变更数量
     * @return bool
     */

/*    废弃，移动到order\Sku  service
    public function set_dec_number($id, $number) {
        $sku_id = (int) $id;
        $number = (int) $number;
        if($id < 1 || $number < 1) {
            $this->errors = lang('_param_error_');
            return FALSE;
        }
        $sqlmap = $map = array();
        $map['sku_id'] = $id;
        $sqlmap['id'] = $this->sku_db->where(array('sku_id'=>$sku_id))->value('spu_id');
        $result = $this->sku_db->where($map)->setDec('number', $number);
        //$_result = $this->spu_db->where($sqlmap)->setDec('sku_total',$number);
        if(!$result){
            return FALSE;
        }else{
            return TRUE;
        }
    }
*/




        /**
         * 获取sku列表
         * @param $id [主商品id]
         * @return array
         */

    public function get_sku($id){

        return $this->model->getAll(['spu_id'=>$id]);

    }



    public function get_lists($params){
        extract($params);

        $sqlmap=[];
        $sqlmap['status']=1;
        $result=$this->model->field($field)->where($sqlmap)->order('sort asc,sku_id desc')->paginate($page,$simple);

        $results=[];
        $results['list']=$result->toArray();
        $results['page']=$result->render();



        return  $results;

    }


}
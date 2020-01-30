<?php

namespace Goods\Controller;

use Common\Controller\BaseController;

class IndexController extends BaseController {

    public function _initialize() {
        parent::_initialize();
        $this->spu_service = D('Goods/Goods_spu', 'Service');
        $this->sku_service = D('Goods/Goods_sku', 'Service');

    }

    public function index() {


        if ($_GET['catid']) {
            $catid = (int) $_GET['catid'];
            $sqlmap['path_id'] = array('like', '%,' . $catid . ',%');
            $spu_list = $this->spu_service->select($sqlmap, 0);


            if ($spu_list['lists']) {
                $spu_id = array();
                foreach ($spu_list['lists'] as $k => $v) {

                    $spu_id[] = $v['id'];
                }

                $spu_id = implode(',', $spu_id);
            } else {
                $spu_id = '0';
            }
            $sqlmap = array();
            $sqlmap['spu_id'] = array('in', $spu_id);
        }

        $result = $this->sku_service->get_lists($sqlmap, 0);

        $this->assign($result, $result)->display();
    }

    public function jifen() {

        $sku_id = (int) $_GET['sku_id'];

        $info = $this->sku_service->detail($sku_id);
		
		$sqlmap=array();
		$sqlmap['sku_id']=$sku_id;
		$sqlmap['status']=0;
		
		$comment_list=D('Comment/Comment','Service')->lists($sqlmap);
		
        $this->assign('comment_list',$comment_list)->assign($info, $info)->display();
    }
	
	 public function detail() {
		$que_time=M('tixian_setting')->where(array('id'=>1))->find();

        if($que_time['que_status']==1){
            $checkDayStr=date('Y-m-d ',time());

            $p_time=rtrim($que_time['que_time'], '-');//去除逗号
            $prev_path = explode('-', $p_time);//组成数组

            $timeBegin1 = strtotime($checkDayStr."$prev_path[0]".":00");  
            $timeEnd1 = strtotime($checkDayStr."$prev_path[1]".":00");

            $curr_time = time();  
             
            if($curr_time >= $timeBegin1 && $curr_time <= $timeEnd1)  
            {  
                $t_time=0;
            }else{
                $t_time=1;
            }    
            
        }else{
            $t_time=1;
        }

        //print_r($t_time);

        $this->assign('t_time',$t_time);
        $this->assign('que_time',$que_time);

        $sku_id = (int) $_GET['sku_id'];

        $info = $this->sku_service->detail($sku_id);

		$sqlmap=array();
		$sqlmap['sku_id']=$sku_id;
		$sqlmap['status']=0;
		$comment_list=D('Comment/Comment','Service')->lists($sqlmap);
         $uid = $this->member['id'];
//         $status=M('order')->where(array('buyer_id'=>$uid,'pay_status'=>1))->find();
//         $this->assign('stauts',$status);

        $this->assign('comment_list',$comment_list)->assign($info, $info)->display();
    }

    public function category() {


        $this->display();
    }

    public function ajax_get_favorite() {
        $favorite = $this->sku_service->is_favorite($this->member['id'], $_POST['sku_id']);
        showmessage('获取收藏状态成功', '', $favorite);
    }

}

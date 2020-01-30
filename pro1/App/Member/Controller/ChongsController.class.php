<?php

namespace Member\Controller;

use Common\Controller\AdminController;

class ChongsController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->model = M('member_chong');
        $this->service = D('Member/Chongs', 'Service');
    }



    public function index(){

        $sqlmap = array();
        if ($_GET['group_id']!=0) {
            $sqlmap['status'] = $_GET['group_id'];
        }
        if ($_GET['keyword']){
            $aa = array('like', '%' . $_GET['keyword'] . '%');
            $uid=M('member')->where(array('username'=>$aa))->getField('id');
            if ($uid){
                $sqlmap['uid']=$uid;
            }else{
                $sqlmap['order_id']=$_GET['keyword'];
            }
        }
        if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime(date('Y-m-d 23:59:59',strtotime($_GET['end'])));
            $sqlmap['time']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['time']=array('egt',$start_time);
        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime(date('Y-m-d 23:59:59',strtotime($_GET['end'])));
            $sqlmap['time']=array('elt',$end_time);
        }
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $this->model->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
        $pages = $this->admin_pages($count, $_GET['limit']);
        $t=M('member_chong');
        $count=array();
        $count['count_money']=$t->sum('money');
        $count['ing_count_money']=$t->where(array('status'=>1))->sum('money');
        $count['ok_count_money']=$t->where(array('status'=>2))->sum('money');
        $time=array('between',array(strtotime(date('Y-m-d 0:0:0',time())),strtotime(date('Y-m-d 23:59:59',time()))));
        $count['jin_count_money']=$t->where(array('time'=>$time))->sum('money');
        $count['jin_ing_count_money']=$t->where(array('status'=>1,'time'=>$time))->sum('money');
        $count['jin_ok_count_money']=$t->where(array('status'=>2,'hou_time'=>$time))->sum('money');
        $this->assign('list',$list)->assign('count',$count)->assign('pages',$pages)->display();
    }



    public function edit(){
		$sqlmap=array();
		
		$id=(int)$_GET['id'];
		
		$info=$this->service->find(array('id'=>$id));
		if(!$info){
			showmessage(L('_data_not_exist_'));
		}
		
		$this->assign('info',$info)->display();
	}
	
	
	public function save(){

    $r=$this->service->save($_POST);
    if(!$r){

        showmessage($r->service->errors);
    }

    $url=$_POST['go_url']?$_POST['go_url']:U('index');



    showmessage(L('_os_success_'),$url,1);

    }
	
	
    /**
     * [ajax_del 删除分类]
     */
    public function del() {
		$id = (array) $_REQUEST['id'];
		
		$chongm = M("member_chong")->where(array('id'=>$id[0]))->find();

		if($chongm["type"] != 0){//不是现金充值
            $tdata['is_buy']=0;
            $tdata['is_buy2']=0;
            M('member')->where(array('id'=>$chongm['uid']))->save($tdata);
        }

        $sqlmap = array();
        $sqlmap['id'] = array('in',$id);

        $result = $this->service->del($sqlmap);

        showmessage(L('_os_success_'), U('index'), 1);
    }



 public function exports(){
        $sqlmap = array();
		if ($_GET['type']!=0) {
            $sqlmap['status'] = $_GET['type'];
        }
		if ($_GET['chong_type']!=0) {
            $sqlmap['chong_type'] = $_GET['chong_type']-1;
        }

        if ($_GET['keyword']) {
            $aa = array('like', '%' . $_GET['keyword'] . '%');
            $sqlmap['uid']=M('member')->where(array('username'=>$aa))->getField('id');
        }

        $limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? $_GET['limit'] : 20;
        $data = M('member_chong')->where($sqlmap)->order('id desc')->select();

        import("Vendor.Excel.PHPExcel", '', '.php');
        import("Vendor.Excel.PHPExcel.Reader.Excel2007", '', '.php');
        import("Vendor.Excel.PHPExcel.IOFactory", '', '.php');

        $file_name = "data/xls_tpl/order.xlsx";
        //检查文件路径
        if(!file_exists($file_name)){
            showmessage('模板不存在');
        }

        $PHPReader=new \PHPExcel_Reader_Excel2007();
        $objPHPExcel=$PHPReader->load($file_name,$encode='utf-8');
        /* //print_r($sqlmap);die;
         $sqlmap = $this->service->build_sqlmap($_GET);
         $limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? $_GET['limit'] : 20;
        $data = $this->service->get_order_lists($sqlmap, $_GET['p'], $limit); */


        $k1="会员用户";$k2="充值金额";$k3="确认金额";$k4="充值状态";$k5="充值时间";$k6="确认时间";$k7="充值类型";
        $objPHPExcel->getActiveSheet()->setCellValue('a1', "$k1");
        $objPHPExcel->getActiveSheet()->setCellValue('b1', "$k2");
        $objPHPExcel->getActiveSheet()->setCellValue('c1', "$k3");
        $objPHPExcel->getActiveSheet()->setCellValue('d1', "$k4");
		$objPHPExcel->getActiveSheet()->setCellValue('e1', "$k5");
		$objPHPExcel->getActiveSheet()->setCellValue('f1', "$k6");
        $objPHPExcel->getActiveSheet()->setCellValue('g1', "$k7");


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

        foreach($data as $k => $v){

            if(empty($k)){

                $num=2;
            }else{
                $num=2+$k;

            }

            $membera = M("member")->where(array('id' => $v['uid']))->find();


            $objPHPExcel->getActiveSheet()->getStyle('A'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('B'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('C'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('D'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
			$objPHPExcel->getActiveSheet()->getStyle('F'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
			$objPHPExcel->getActiveSheet()->getStyle('G'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));


            if($v['status'] == 1){
                $status = "已付款,待确认";
            }elseif($v['status'] == 2){
                $status = "充值成功";
            }elseif($v['status'] == 3){
                $status = "充值失败";
            }

            if($v['type']==1){

                $type_s = "ACNY";
                $usd_money = $v['money'];
            }else{

                $type_s = "USDT";
                $usd_money = $v['usdt'];
            }

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $membera['username']."（".$membera['realname']."）")
                ->setCellValue('B'.$num, $v['money'])
                ->setCellValue('C'.$num, $usd_money)
				->setCellValue('D'.$num, $status)
				->setCellValue('E'.$num, date("Y-m-d H:i:s",$v['time']))
                ->setCellValue('F'.$num, date("Y-m-d H:i:s",$v['hou_time']))
                ->setCellValue('G'.$num, $type_s);

        }
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.getdatetime(time(),'Y-m-d').'转账充值.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');



    }
	
	
}

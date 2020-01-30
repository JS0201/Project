<?php

namespace Member\Controller;

use Common\Controller\AdminController;


class MemberLogController extends AdminController {

    public function _initialize() {
        parent::_initialize();
       
        $this->service = D('Member_log', 'Service');
	
    }

    public function index() {
        $sqlmap = $this->service->build_sqlmap($_GET);
        if($sqlmap === false) showmessage($this->service->errors);
        $limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? (int) $_GET['limit'] :20;
        $logs = $this->service->get_lists($sqlmap,$_GET['p'],$limit);
        $count = $this->service->count($sqlmap);
    	$pages = $this->admin_pages($count, $limit);
        $lists = array(
            'th' => array(
                'username' => array('title' => '会员账号','length' => 10),
                'dateline' => array('title' => '操作日期','length' => 20,'style' => 'date'),
                'value' => array('length' => 20,'title' => '操作金额'),
                'msg' => array('title' => '日志描述','length' => 40),
            ),
            'lists' => $logs,
            'pages' => $pages,
        );

        $t=M('member_log');
        //print_r($lists);die;

        $count=array();
        $count['ing_count_money']=$t->where(array('type'=>'member_bi','value'=>array('gt',0)))->sum('value');//加
      
        $count['not_count_money']=$t->where(array('type'=>'member_yj','value'=>array('gt',0)))->sum('value');//加


        $count['check_count_money']=$t->where(array('type'=>'member_bi','value'=>array('lt',0)))->sum('value');//减
        
        $count['wx_count_money']=$t->where(array('type'=>'member_yj','value'=>array('lt',0)))->sum('value');//减
		
        $this->assign('lists',$lists)->assign('count',$count)->assign('pages',$pages)
            ->display();
    }



        public function exports(){
        $sqlmap = array();
        if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime($_GET['end']);
            $sqlmap['dateline']=array('between',array($start_time,$end_time));

        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['dateline']=array('egt',$start_time);

        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime($_GET['end']);
            $sqlmap['dateline']=array('elt',$end_time);
        }
        if($_GET['keyword']){
            $k_user = M("member")->where(array('username'=>$_GET['keyword']))->find();
            $sqlmap['mid'] = $k_user['id'];
        }


        $limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? $_GET['limit'] : 20;
        $data = M('member_log')->where($sqlmap)->order('id desc')->select();


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


        $k1="会员账号";$k2="操作日期";$k3="操作金额";$k4="日志描述";$k5="状态";
        $objPHPExcel->getActiveSheet()->setCellValue('a1', "$k1");
        $objPHPExcel->getActiveSheet()->setCellValue('b1', "$k2");
        $objPHPExcel->getActiveSheet()->setCellValue('c1', "$k3");
        $objPHPExcel->getActiveSheet()->setCellValue('d1', "$k4");
        $objPHPExcel->getActiveSheet()->setCellValue('e1', "$k5");


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);


        foreach($data as $k => $v){

            if(empty($k)){

                $num=2;
            }else{
                $num=2+$k;

            }

            $membera = M("member")->where(array('id' => $v['mid']))->find();


            $objPHPExcel->getActiveSheet()->getStyle('A'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('B'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('C'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('D'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));


            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $membera['realname']."-".$membera['username'])
                ->setCellValue('B'.$num, date("Y-m-d H:i:s",$v['dateline']))
                ->setCellValue('C'.$num, $v['value'])
                ->setCellValue('D'.$num, $v['msg'])
                ->setCellValue('E'.$num, "成功");
        }
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.getdatetime(time(),'Y-m-d').'财务变动.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');



    }

}

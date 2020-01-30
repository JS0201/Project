<?php

namespace Member\Controller;

use Common\Controller\AdminController;

class WithbiController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->service = D('Member/Withbi', 'Service');

    }

	public function index(){
		
        $sqlmap = array();
        if ($_GET['type']) {

            $sqlmap['withdrawals_paytype']=$_GET['type'];
        }

        if ($_GET['keyword']) {
            $aa = array('like', '%' . $_GET['keyword'] . '%');
            $sqlmap['member_id']=M('member')->where(array('username'=>$aa))->getField('id');
        }
		
		if($_GET['start'] &&  $_GET['end']){
            $start_time=date('Y-m-d 0:00:00',strtotime($_GET['start']));

            $end_time=date('Y-m-d 23:59:59',strtotime($_GET['end']));
            $sqlmap['withdrawals_time']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=date('Y-m-d 0:00:00',strtotime($_GET['start']));
        
            $sqlmap['withdrawals_time']=array('egt',$start_time);
            
        }elseif(!$_GET['start'] &&  $_GET['end']){
            
            $end_time=date('Y-m-d 23:59:59',strtotime($_GET['end']));
            $sqlmap['withdrawals_time']=array('elt',$end_time);
        }



		 $result=$this->service->select($sqlmap,'20','','id desc');
      

         //print_r($result);die;
		
		 $pages = $this->admin_pages($result['count'], $result['limit']);
		
		$t=M('withbi');
		
		$count=array();
		$count['count_money']=$t->where($sqlmap)->sum('withdrawals_nums');
		$count['ing_count_money']=$t->where($sqlmap)->where('withdrawals_states=0')->sum('withdrawals_nums');  //   审核中
		$count['not_count_money']=$t->where($sqlmap)->where('withdrawals_states=1')->sum('withdrawals_nums');  //提现失败
		$count['ok_count_money']=$t->where($sqlmap)->where('withdrawals_states=2')->sum('withdrawals_nums');  //提现成功

		$count['a_count_money']=$t->where($sqlmap)->where('withdrawals_paytype=1')->sum('withdrawals_nums');
		$count['h_count_money']=$t->where($sqlmap)->where('withdrawals_paytype=2')->sum('withdrawals_nums');
		$count['u_count_money']=$t->where($sqlmap)->where('withdrawals_paytype=3')->sum('withdrawals_nums');
		
	
		
		$this->assign('list',$result['lists'])->assign('count',$count)->assign('pages',$pages)->display();
	}
	
	
	public function edit(){
		$sqlmap=array();
		
		$id=(int)$_GET['id'];
		
		$info=$this->service->find(array('id'=>$id));
		if(!$info){
			showmessage(L('_data_not_exist_'));
		}
		$bank_info = M('member_profile')->where(array('uid' => $info['member_id']))->find();
		$this->assign('bank_info',$bank_info);
		$this->assign('info',$info)->display();
	}
	
	
	public function save(){
		
		$r=$this->service->save($_POST);

		if($_POST['withdrawals_states']==1){
			$member_id=M('withbi')->where(array('id'=>$_POST['id']))->find();
			$phone=M('member')->where(array('id'=>$member_id['member_id']))->getField('username');
			send_sms($phone,'【HM社区】尊敬的HM会员，您申请的提现：'.$member_id['withdrawals_nums'].'个，已转入您平台账户中，请尽快查收确认！');
		}
		if(!$r){
			
			showmessage($r->service->errors);
		}
		
		showmessage(L('_os_success_'),U('index'),1);
		
	}
	
	public function good(){
		$id = (array) $_REQUEST['id'];
		
        $sqlmap = array();
        $sqlmap['id'] = array('in',$id);
        $sqlmap['withdrawals_states'] = array('neq',1);

        $result = $this->service->good($sqlmap);

        showmessage('成功', U('index'), 1);
	}
	
	
    /**
     * [ajax_del 删除分类]
     */
    public function del() {
		$id = (array) $_REQUEST['id'];
		
        $sqlmap = array();
        $sqlmap['id'] = array('in',$id);

        $result = $this->service->del($sqlmap);

        showmessage(L('_os_success_'), U('index'), 1);
    }
	
	
	public function export(){

        $sqlmap = array();
        if ($_GET['type']) {

            $sqlmap['withdrawals_paytype']=$_GET['type'];
        }

        if ($_GET['keyword']) {
            $aa = array('like', '%' . $_GET['keyword'] . '%');
            $sqlmap['member_id']=M('member')->where(array('username'=>$aa))->getField('id');
        }

        if($_GET['start'] &&  $_GET['end']){
            $start_time=date('Y-m-d 0:00:00',strtotime($_GET['start']));

            $end_time=date('Y-m-d 23:59:59',strtotime($_GET['end']));
            $sqlmap['withdrawals_time']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=date('Y-m-d 0:00:00',strtotime($_GET['start']));

            $sqlmap['withdrawals_time']=array('egt',$start_time);

        }elseif(!$_GET['start'] &&  $_GET['end']){

            $end_time=date('Y-m-d 23:59:59',strtotime($_GET['end']));
            $sqlmap['withdrawals_time']=array('elt',$end_time);
        }
      //  print_r($_GET);
        $data=M('withbi')->where($sqlmap)->select();

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

        $k1="编号";$k2="会员用户";$k3="姓名";$k4="手机号码";$k5="提现金额";$k6="转币地址";$k7="提现类型";$k8="提现状态";$k9="提现时间";
        $objPHPExcel->getActiveSheet()->setCellValue('a1', "$k1");
        $objPHPExcel->getActiveSheet()->setCellValue('b1', "$k2");
        $objPHPExcel->getActiveSheet()->setCellValue('c1', "$k3");
        $objPHPExcel->getActiveSheet()->setCellValue('d1', "$k4");
        $objPHPExcel->getActiveSheet()->setCellValue('e1', "$k5");
        $objPHPExcel->getActiveSheet()->setCellValue('f1', "$k6");
        $objPHPExcel->getActiveSheet()->setCellValue('g1', "$k7");
        $objPHPExcel->getActiveSheet()->setCellValue('h1', "$k8");
        $objPHPExcel->getActiveSheet()->setCellValue('i1', "$k9");


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);



        foreach($data as $k => $v){

            if(empty($k)){
                
                $num=2;
            }else{
                $num=2+$k;
                
            }


            //0->审核中,1->已通过,2->已拒绝,3->核对中
            switch ($v['withdrawals_states']) {
            	case '0':
            		$status='审核中';
            		break;
            	case '1':
            		$status='失败';
            		break;
            	case '2':
            		$status='成功';
            		break;

            	
            }

            switch ($v['withdrawals_paytype']) {
                case '1':
                    $type='USDT';
                    break;
                case '2':
                    $type='VSC';
                    break;

            }

            $objPHPExcel->getActiveSheet()->getStyle('A'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('B'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('C'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('D'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('F'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('G'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('H'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('I'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));

            $mid=M('member')->where(array('id'=>$v['member_id']))->find();

          $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('A'.$num, $v['id'])   
          ->setCellValue('B'.$num, $mid['username'])
          ->setCellValue('C'.$num, $mid['realname'])
          ->setCellValue('D'.$num, $mid['mobile'])
          ->setCellValue('E'.$num, $v['withdrawals_nums'])
          ->setCellValue('F'.$num, $v['zb_address'])
          ->setCellValue('G'.$num, $type)
          ->setCellValue('H'.$num, $status)

          ->setCellValue('I'.$num, $v['withdrawals_time']?$v['withdrawals_time']:'');


          
        }

         header('Content-Type: application/vnd.ms-excel');

         header('Content-Disposition: attachment;filename="'.getdatetime(time(),'Y-m-d').'提币明细.xls"');
         header('Cache-Control: max-age=0');
         $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
         $objWriter->save('php://output');



    }
	
}

<?php

namespace Order\Controller;

use Common\Controller\AdminController;

class OrderController extends AdminController {

    public function _initialize() {
        parent::_initialize();

        $this->service = D('Order/Order', 'Service');
        $this->sub_service = D('Order/OrderSub', 'Service');
        $this->sku_service = D('Order/OrderSku', 'Service');
        $this->order_trade_service = D('Order/Order_trade', 'Service');
        $this->member_model = D('Member/Member');
    }

    /* 订单列表管理 */

    public function index() {
        // 查询条件
        $sqlmap = array();

        $sqlmap = $this->service->build_sqlmap($_GET);
        $limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? $_GET['limit'] : 20;
        $orders = $this->service->get_order_lists($sqlmap, $_GET['p'], $limit);
        $count = $this->service->count($sqlmap);
        $pages = $this->admin_pages($count, $limit);
        $lists = array(
            'th' => array(
                'sn' => array('title' => '订单号', 'length' => 12),
                'ordername'=>array('length' => 10, 'title' => '商品名称'),
                'username' => array('title' => '会员帐号', 'length' => 8),
                'address_name' => array('length' => 7, 'title' => '收货人'),

                'address_mobile' => array('title' => '收货电话', 'length' => 8),
               // 'yes_no' => array('length' => 5, 'title' => '是否发货', 'style' => 'datee'),
                'system_time' => array('length' => 8, 'title' => '下单时间', 'style' => 'date'),
                'real_amount' => array('length' => 6, 'title' => '订单金额'),
                '_pay_type' => array('length' => 6, 'title' => '支付方式'),
				 'pay_method'=>array('length'=>6,'title'=>'支付类型'),
                'payment'=>array('length'=>7,'title'=>'实际付款'),
                'source' => array('length' => 6, 'title' => '订单类型', 'style' => 'source'),
//                'order_type' => array('length' => 8, 'title' => '订单商城', 'style' => 'order_type'),
                '_status' => array('length' =>6, 'title' => '订单状态', 'style' => '_status')
            ),
            'lists' => $orders,
            'pages' => $pages,
        );


       //print_r($lists['lists']);


		$count=array();
		$count['count_money']=M('order')->sum('paid_amount');
		$count['pay_count_money']=M('order')->where(array('pay_status'=>1))->sum('paid_amount');
		$count['not_pay_count_money']=M('order')->where(array('pay_status'=>0))->sum('paid_amount');

        $this->assign('lists', $lists)->assign('count',$count)->assign('pages', $pages)->display();
		
		
		
		
		
    }

    /* 订单详细页面 */

    public function detail() {
        $order = $this->sub_service->find(array('order_sn' => $_GET['order_sn']));
        if (!$order)
            showmessage(L('order_not_exist'));
        $order['_member'] = $this->member_model->fetch_by_id($order['buyer_id']);
        $order['_main'] = $this->service->find(array('sn' => $order['order_sn']));


        foreach ($order['_skus'] as $key => $value) {
            if ($key > 0) {
                $status = 1;
            }
        }

        // 日志
        // $order_logs = $this->service_order_log->get_by_order_sn($order['sub_sn'], 'id DESC');
        $this->assign('order', $order)->assign('status', $status)->display('detail');
    }

    /* 确认付款 */

    public function pay() {
        if (IS_POST) {

            $params = array();
            // $params['pay_status'] = remove_xss(strtotime($_POST['pay_status']));
            $params['paid_amount'] = sprintf("%0.2f", (float) $_POST['paid_amount']);
            $params['pay_method'] = remove_xss($_POST['pay_method']);
            $params['pay_sn'] = remove_xss($_POST['pay_sn']);
            $params['msg'] = remove_xss($_POST['msg']);
            if ($params['pay_method'] != 'other' && !$params['pay_sn']) {
                showmessage(L('pay_deal_sn_empty'));
            }

            $result = $this->sub_service->set_order($_GET['order_sn'], 'pay', '', $params);
            if (!$result)
                showmessage($this->sub_service->errors);
            showmessage(L('pay_success'), '', 1, 'json');
        } else {
            // 获取所有已开启的支付方式
            $pays = D('Pay/payment', 'Service')->get();

            foreach ($pays as $k => $pay) {
                $pays[$k] = $pay['pay_name'];
            }
            $pays['other'] = '其它付款方式';
            $order = $this->service->find(array('sn' => $_GET['order_sn']));
            $this->assign('pays', $pays)->assign('order', $order)->display('alert_pay');
        }
    }

    /* 确认订单 */

    public function confirm() {
        if (IS_POST) {
            $result = $this->sub_service->set_order($_POST['sub_sn'], 'confirm', '', array('msg' => $_POST['msg']));
            if (!$result)
                showmessage($this->sub_service->error);
            showmessage(L('confirm_success'), '', 1, 'json');
        } else {
            $this->display('alert_confirm');
        }
    }

    /* 确认发货 */

    public function delivery() {
        if (IS_POST) {
            $params = array();
            $params['is_choise'] = remove_xss($_POST['is_choise']);
            $params['delivery_id'] = remove_xss($_POST['delivery_id']);
            $params['delivery_sn'] = remove_xss($_POST['delivery_sn']);
            $params['sub_sn'] = remove_xss($_POST['sub_sn']);
            $params['msg'] = remove_xss($_POST['msg']);
            $o_sku_ids = remove_xss($_POST['o_sku_id']);
            $params['o_sku_ids'] = implode(',', $o_sku_ids);
            $result = $this->sub_service->set_order($_POST['sub_sn'], 'delivery', $_POST['status'], $params);
            if (!$result)
                showmessage($this->sub_service->errors);
            showmessage(L('confirm_delivery_success'), '', 1, 'json');
        } else {
            // 获取已开启的物流
            $sqlmap = $deliverys = array();
            $sqlmap['enabled'] = 1;
            $deliverys = D('Order/Delivery', 'Service')->getField('id,name', $sqlmap);
            // 获取子订单下的skus
            $o_skus = $this->sub_service->sub_delivery_skus($_GET['sub_sn']);
            if (!$o_skus) {
                //   showmessage($this->service_sub->error);
            }
            $this->assign('deliverys', $deliverys)->assign('o_skus', $o_skus)->display('alert_delivery');
        }
    }

    /* 确认完成 */

    public function finish() {
        if (IS_POST) {
            $result = $this->sub_service->set_order($_GET['sub_sn'], 'finish', '', array('msg' => $_POST['msg']));
            if (!$result)
                showmessage($this->sub_service->errors);
            showmessage(L('confirm_finish_success'), '', 1, 'json');
        } else {
            $this->display('alert_finish');
        }
    }

    /* 取消订单 */

    public function cancel() {
        if (IS_POST) {
            $order_sn = M('order_sub')->where(array('sub_sn' => $_POST['sub_sn']))->getField('order_sn');
            $this->order_trade_service->setField(array('status' => -1), array('order_sn' => $order_sn));

            $result = $this->sub_service->set_order($_GET['sub_sn'], 'order', 2, array('msg' => $_POST['msg'], 'isrefund' => 1));
            if (!$result)
                showmessage($this->sub_service->errors, '', 0, 'json');
            showmessage(L('cancel_order_success'), '', 1, 'json');
        } else {
            $order = $this->sub_service->find(array('sub_sn' => $_GET['sub_sn']));
            $this->assign('order', $order)->display('alert_cancel');
        }
    }

    /* 作废 */

    public function recycle() {
        if (IS_POST) {
            $result = $this->sub_service->set_order($_GET['sub_sn'], 'order', 3, array('msg' => $_POST['msg']));
            if (!$result)
                showmessage($this->sub_service->errors);
            showmessage(L('cancellation_order_success'), '', 1, 'json');
        } else {
            $this->display('alert_recycle');
        }
    }
	
	//	每日订单明细
     public function orderinfo(){
        $order=M('order');
        $sqlmap = array();
        $keyword=$_GET['group_id'];
        if($keyword){
            $sqlmap['group_id']=M('member')->where(array('group_id'=>$keyword))->getField('group_id');
        }

        if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime($_GET['end']);
            $sqlmap['pay_time']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['pay_time']=array('egt',$start_time);   //大于等于
        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime($_GET['end']);
            $sqlmap['pay_time']=array('elt',$end_time);   //小于等于
        }
        $sqlmap['status']=1;
        $dan=6500;
        $count=$order->where($sqlmap)->sum('real_amount');
        $count_dan=$count/$dan;
        $orderlist=$order->where($sqlmap)->select();
        $t_count=0;
        $y_count=0;
        $j_count=0;

        foreach ($orderlist as $k=>$v){
            $group_id=M('member')->where(array('id'=>$v['buyer_id']))->getField('group_id');

            $t_count =$order->where($sqlmap)->where('real_amount=6500')->count();   //铜

            $y_count =$order->where($sqlmap)->where('real_amount=13000')->count();  //银

            $j_count =$order->where($sqlmap)->where('real_amount=26000')->count();  //金

        }
      //print_r($orderlist);
        $this->assign('count',$count)
             ->assign('count_dan',$count_dan)
            ->assign('t_count',$t_count)
            ->assign('y_count',$y_count)
            ->assign('j_count',$j_count);
        $this->display();
    }



    public function exports(){
        // 查询条件
        $sqlmap = array();
        $sqlmap = $this->service->build_sqlmap($_GET);
        if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime($_GET['end']);
            $sqlmap['system_time']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['system_time']=array('egt',$start_time);
        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime($_GET['end']);
            $sqlmap['system_time']=array('elt',$end_time);
        }
        $limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? $_GET['limit'] : 20;
        $orders = $this->service->get_order_lists2($sqlmap);
        $count = $this->service->count($sqlmap);
        $pages = $this->admin_pages($count, $limit);
        $lists = array(

            'lists' => $orders,
            'pages' => $pages,
        );


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

        $k1="订单号";$k2="产品名称";$k3="会员账号";$k4="收货人";$k5="收货电话";$k6="下单时间";$k7="订单金额";$k8="支付方式";$k9="支付类型";$k10="实际付款";$k11="订单状态";
        $objPHPExcel->getActiveSheet()->setCellValue('a1', "$k1");
        $objPHPExcel->getActiveSheet()->setCellValue('b1', "$k2");
        $objPHPExcel->getActiveSheet()->setCellValue('c1', "$k3");
        $objPHPExcel->getActiveSheet()->setCellValue('d1', "$k4");
        $objPHPExcel->getActiveSheet()->setCellValue('e1', "$k5");
        $objPHPExcel->getActiveSheet()->setCellValue('f1', "$k6");
        $objPHPExcel->getActiveSheet()->setCellValue('g1', "$k7");
        $objPHPExcel->getActiveSheet()->setCellValue('h1', "$k8");
        $objPHPExcel->getActiveSheet()->setCellValue('i1', "$k9");
        $objPHPExcel->getActiveSheet()->setCellValue('j1', "$k10");
        $objPHPExcel->getActiveSheet()->setCellValue('k1', "$k11");


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);


        foreach($lists['lists'] as $k => $v){
            if(empty($k)){

                $num=2;
            }else{
                $num=2+$k;


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
            $objPHPExcel->getActiveSheet()->getStyle('J'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('K'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));



            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num,$v['sn'].' ')
                ->setCellValue('B'.$num, $v['ordername'])
                ->setCellValue('C'.$num,$v['username'])
                ->setCellValue('D'.$num,  $v['address_name'])
                ->setCellValue('E'.$num,$v['address_mobile'])
                ->setCellValue('F'.$num, date("Y-m-d H:i:s",$v['system_time']))
                ->setCellValue('G'.$num, $v['real_amount'])
                ->setCellValue('H'.$num, $v['_pay_type'])
                ->setCellValue('I'.$num, $v['pay_method'])
                ->setCellValue('J'.$num, $v['payment'])
                ->setCellValue('K'.$num, ch_status($v['_status']));



        }
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.getdatetime(time(),'Y-m-d').'订单明细.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');



    }




}

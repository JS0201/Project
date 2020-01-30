<?php

namespace Member\Controller;

use Common\Controller\AdminController;

class MemberController extends AdminController {

    public function _initialize() {
        parent::_initialize();
        $this->model = D('Member');
        $this->service = D('Member', 'Service');
    }

    /**
     * [category_choose 选择框]
     */
    public function category_choose() {
        $category = $this->category_service->get_category_tree();

        $this->assign('category', $category);
        $this->display('Category/category_choose');
    }

    /**
     * [index 列表]
     */

      public function index() {
        $sqlmap = array();
        //个人等级
        $fc=0;
        if($_GET['report_id']){
            $fc=1;
            if($_GET['report_id']==1){
                $sqlmap['grade']='1';
            }elseif ($_GET['report_id']==2) {
                $sqlmap['grade']='2';
            }elseif($_GET['report_id']==3){
                $sqlmap['grade']='3';
            }elseif ($_GET['report_id']==4) {
                $sqlmap['grade']='4';
            }elseif($_GET['report_id']==5){
                $sqlmap['grade']='5';
            }elseif($_GET['report_id']==6){
                $sqlmap['grade']='6';
            }elseif($_GET['report_id']==7){
                $sqlmap['grade']='0';
            }
        }
        //团队等级
        $tc=0;
        if($_GET['tuan_id']){
            $tc=1;
            if($_GET['tuan_id']==1){
                $sqlmap['agency_level']='1';
            }elseif ($_GET['tuan_id']==2) {
                $sqlmap['agency_level']='2';
            }elseif($_GET['tuan_id']==3){
                $sqlmap['agency_level']='3';
            }elseif ($_GET['tuan_id']==4) {
                $sqlmap['agency_level']='4';
            }
        }

        if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);

            $end_time=strtotime($_GET['end']);
            $sqlmap['register_time']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){

            $start_time=strtotime($_GET['start']);

            $sqlmap['register_time']=array('egt',$start_time);

        }elseif(!$_GET['start'] &&  $_GET['end']){

            $end_time=strtotime($_GET['end']);
            $sqlmap['register_time']=array('elt',$end_time);
        }
        if ($_GET['keyword']) {
            $sqlmap['username|email|mobile|realname|nickname'] = array('like', '%' . $_GET['keyword'] . '%');
        }
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;

        if(!empty($fc)){
            $list=M('member  m')->field('m.*,f.grade')->join('left join gboy_money_finance f on m.id=f.uid ')->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
            $count = M('member  m')->field('m.*,f.grade')->join('left join gboy_money_finance f on m.id=f.uid ')->where($sqlmap)->count();
        }
        if(!empty($tc)){
            $list=M('member  m')->field('m.*,f.agency_level')->join('left join gboy_money_finance f on m.id=f.uid ')->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
            $count = M('member  m')->field('m.*,f.agency_level')->join('left join gboy_money_finance f on m.id=f.uid ')->where($sqlmap)->count();
        }
        if(empty($fc) && empty($tc) && empty($cj)){
            $list = $this->model->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
            $count = $this->model->where($sqlmap)->order("id desc")->count();
        }

        $Pages = new \Think\Page($count, $_GET['limit']);
       
        $pages = $this->admin_pages($count, $_GET['limit']);

        $bi=M('money_finance');
        $zong=array();
        $zong['q_count_money']=$bi->sum('lock_wallet');//区块包
        $zong['u_count_money']=$bi->sum('usdt_wallet');//usdt
        $this->assign('list', $list);
        $this->assign('zong',$zong);
        $this->assign('pages', $pages);

        $this->display();
    }
   public function del_user(){
        $uid = (int) $_REQUEST['id'];
      //  var_dump($id);
        $pid=M('member')->where(array('pid'=>$uid))->select();
        if($pid){
            showmessage('该帐号伞下有用户，不能删除！');
        }else{
            $this->model->where(array('id' => $uid))->delete();
            M('secure_encryption')->where(array('uid' => $uid))->delete();
            M('money_finance')->where(array('uid' => $uid))->delete();
            M('member_sorts')->where(array('uid' => $uid))->delete();
            showmessage('删除成功', U('index'), 1);
        }


    }
	public function recharge(){
        $this->model=M('givemoneys');
        $sqlmap = array();
        $keyword=$_GET['keyword'];
        if($keyword){
            $u_id_arr=M('member')->where(array('username'=>$keyword))->getField('id',true);
            if($u_id_arr){
                $sqlmap['member_receiveid']=array('in',implode(',',$u_id_arr));
            }else{
                $sqlmap['member_receiveid']='0';
            }
        }

        $keyword2=$_GET['keyword2'];
        if($keyword2){
            $u_id_arr=M('member')->where(array('username'=>$keyword2))->getField('id',true);
            if($u_id_arr){
                $sqlmap['member_giveid']=array('in',implode(',',$u_id_arr));
            }else{
                $sqlmap['member_giveid']='0';
            }
        }


        if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime($_GET['end']);
            $sqlmap['do_times']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['do_times']=array('egt',$start_time);
        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime($_GET['end']);
            $sqlmap['do_times']=array('elt',$end_time);
        }


        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $this->model->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();

        foreach ($list as $key => $value) {
            $list[$key]['member_giveid'] = M('member')->where(array('id'=>$value['member_giveid']))->getField('username');
            $list[$key]['member_receiveid'] = M('member')->where(array('id'=>$value['member_receiveid']))->getField('username');
            $list[$key]['member_givename'] = M('member')->where(array('id'=>$value['member_giveid']))->getField('recharge_code');
            $list[$key]['member_receivename'] = M('member')->where(array('id'=>$value['member_receiveid']))->getField('recharge_code');
        }
		$a_count= $this->model->where($sqlmap)->where('money_types=1')->sum('money_nums');
        $h_count= $this->model->where($sqlmap)->where('money_types=2')->sum('money_nums');
        $u_count= $this->model->where($sqlmap)->where('money_types=3')->sum('money_nums');
        $pages = $this->admin_pages($count, $_GET['limit']);
        $this->assign('list', $list);
		$this->assign('a_count', $a_count);
        $this->assign('h_count', $h_count);
        $this->assign('u_count', $u_count);

        $this->assign('pages', $pages);

        $this->display();
    }
	
	//修改手机号码明细
    public function upphone(){
	    $setphone=M('setphone');
        $sqlmap = array();
//        原用户
        $keyword=$_GET['keyword'];
        if($keyword){
            $sqlmap['old_phone']=$keyword;
        }
//现用户
        $keyword2=$_GET['keyword2'];
        if($keyword2){
            $sqlmap['new_phone']=$keyword2;
        }

        if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime($_GET['end']);
            $sqlmap['set_time']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['set_time']=array('egt',$start_time);
        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime($_GET['end']);
            $sqlmap['set_time']=array('elt',$end_time);
        }
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $setphone->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $info = $setphone->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
        $pages = $this->admin_pages($count, $_GET['limit']);

        $this->assign('pages', $pages);
        $this->assign('info',$info);

	    $this->display();
    }


	 //修改用户等级明细
    public function setgrade(){
        $setgrade=M('setgrade');
        $sqlmap = array();
//        原用户
        $keyword=$_GET['keyword'];
        if($keyword){
            $sqlmap['uid']=M('member')->where(array('username'=>$keyword))->getField('id');
        }

        if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime($_GET['end']);
            $sqlmap['set_time']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['set_time']=array('egt',$start_time);
        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime($_GET['end']);
            $sqlmap['set_time']=array('elt',$end_time);
        }
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $setgrade->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $info = $setgrade->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
        $pages = $this->admin_pages($count, $_GET['limit']);
       // var_dump($info);
        $this->assign('pages', $pages);
        $this->assign('info',$info);

        $this->display();
    }

	
	
    /**
     * [togglelock 解锁]
     */
    public function togglelock(){
        $ids = (array) $_REQUEST['ids'];
        $result = $this->service->togglelock_by_id($ids, $_REQUEST['type']);
        $this->success('操作成功', U('index'));
    }
    /**
     * [账户释放]
     */
    public function shifang(){
        $uid = $_SESSION['gboy_admin_login']['id'];
        $gid = M("admin_user")->where(array('id'=>$uid))->getField('group_id');
        if($gid > 1) {
            showmessage('权限不足，操作失败','null');
            exit();
        }else{
            $this->display();
        }
    }

    /**
     * [del 删除分类]
     */
    public function del() {

        $id = (array) $_REQUEST['ids'];
        if($id){
            $this->model->where(array('id' => array('IN', $id)))->delete();
        }
      
        $this->success('删除成功', U('index'));
    }

    public function update() {
        $id = (int) $_REQUEST['id'];
        $member = $this->service->find(array('id'=>$id));
        if (!$member)
            showmessage($this->service->errors);
        if ($_POST) {
			
			
            foreach ($_POST['info'] as $t => $v) {
                if (is_numeric($v['num']) && !empty($v['num'])) {
                    $v['num'] = ($v['action'] == 'inc') ? '+' . $v['num'] : '-' . $v['num'];
                    $this->service->change_account($id, $t, $v['num'], $_POST['msg']);
                }
            }
            showmessage('_os_success_', U('index'), 1);
        } else {
            $this->assign('member', $member)->display();
        }
    }


    public function uppdate(){

    	$id = (int) $_REQUEST['id'];
    	$return_ka=M('return_ka');
        $member = $this->service->find(array('id'=>$id));
        if (!$member)
            showmessage($this->service->errors);
        if($_POST){
        	if($_POST['money']=='' || $_POST['day_day']=='' || $_POST['card']==''){
        		showmessage('请填写完整信息');
        	}else{
				$arr=array(
					'uid'=>$id,
					'number'=>1,
					'money'=>$_POST['money'],
					'expected_money'=>$_POST['money'],
					'expected_b'=>$_POST['money'],
					'change_money'=>$_POST['money'],
					'change_b'=>$_POST['money'],
					'card'=>$_POST['card'],
					'current_money'=>$_POST['money']/$_POST['card'],
					'gou_time'=>time(),
					'change_time'=>time(),
					'day_day'=>$_POST['day_day'],
					'or_status'=>2,
					);
				$return_ka->add($arr);
				showmessage('_os_success_', U('index'), 1);
        	}
        }else{
        	$this->display();
        }
	
    }
	
	public function modify_data(){
        $id = (int) $_REQUEST['id'];
        $member_profile=M('member_profile');//个人资料表
        $profile =$member_profile->where(array('uid'=>$id))->find();
        $this->assign('profile', $profile)->display();
    }
	
	//后台修改个人资料
    public function modify_data1(){

        $id = (int) $_REQUEST['id'];
        $member_profile=M('member_profile');//个人资料表
        $profile =$member_profile->where(array('uid'=>$id))->find();
        $member = $this->service->find(array('id'=>$id));
        $data = I();
        if (!$member){
            showmessage($this->service->errors);
        }
        if($_POST){
            if ($profile) {//查询会员在个人资料表里是否有记录
                $gd_dz=$data['wallet_address'];
                if ($gd_dz==''){
                    $mb_data['wallet_address']='';
                    $mb_data['address_key']='';//国盾地址密钥
                    $mb_data['encrypt']='';//令牌
                }else{
                    $encrypt = random(6);//6位随机数令牌
                    $key=md5(md5($gd_dz) .  $encrypt);//安全加密
                    $mb_data['wallet_address']=$gd_dz;
                    $mb_data['address_key']=$key;//国盾地址密钥
                    $mb_data['encrypt']=$encrypt;//令牌
                }
                
                $mb_data['uid']=$id;//用户ID
                $mb_data['account_name']=$data['account_name'];//银行开户名
                $mb_data['account_bank']=$data['account_bank'];//银行开户行
                $mb_data['bank_account']=$data['bank_account'];//银行账户
                $mb_data['alipay']=$data['alipay'];//支付宝账号
                $mb_data['wechat']=$data['wechat'];//微信账号
                $mb_data['gd_names']=$data['gd_names'];//国盾账户姓名
                $mb_data['gd_id']=$data['gd_id'];//国盾账户ID
                $mb_data['gd_account_number']=$data['gd_account_number'];//国盾账户账号

                $zl_xg=$member_profile->where(array('uid'=>$id))->save($mb_data);
                if ($zl_xg){
                         showmessage('_os_success_', U('index'), 1);
                    }else{
                        showmessage('资料修改失败，请查看你输入的资料是否正确！');
                    }
            }else{

                $gd_dz=$data['wallet_address'];
                if($gd_dz){
                    $encrypt = random(6);//6位随机数令牌
                    $key=md5(md5($gd_dz) .  $encrypt);//安全加密
                }else{
                    $encrypt ='';//6位随机数令牌
                    $key='';//安全加密
                }

                $m_data=array(
                    'uid'=>$id,//用户ID
                    'account_name'=>$data['account_name'],//银行开户名
                    'account_bank'=>$data['account_bank'],//银行开户行
                    'bank_account'=>$data['bank_account'],//银行账户
                    'alipay'=>$data['alipay'],//支付宝账号
                    'wechat'=>$data['wechat'],//微信账号
                    'wallet_address'=>$data['wallet_address'],//国盾钱包地址
                    'gd_names'=>$data['gd_names'],//国盾账户姓名
                    'gd_id'=>$data['gd_id'],//国盾账户ID
                    'gd_account_number'=>$data['gd_account_number'],//国盾账户账号
                    'address_key'=>$key,//国盾地址密钥
                    'encrypt'=> $encrypt,//令牌
                    );
                
                $zl_tj=$member_profile->data($m_data)->add();

                if ($zl_tj){
                    showmessage('_os_success_', U('index'), 1);
                }else{
                    showmessage('资料修改失败，请查看你输入的资料是否正确！');
                }
           
                
            }

        }else{
            $this->assign('profile', $profile)->display();
        }
    
    }
	
	
	
	public function map(){
		if($_GET['ac']=='ajax'){
		    if($_POST['id']){
		        $id = $_POST['id'];
            }else{
		        $id = "0";
            }
			echo $this->tomap($id);
			exit();
		}
		$this->display();
	}
	
	
	//无限级树形
	function tomap($pid='0'){
	//echo '4444';die;
	
		global $arr;
		$t=M('member');
		
		$time=array('between',array(strtotime(date('Y-m-d 0:0:0',time())),strtotime(date('Y-m-d 23:59:59',time()))));
		$list=$t->where(array('pid'=>$pid))->order('id asc')->select();
		if(is_array($list)){
			foreach($list as $k=>$v){
				$list[$k]['name']=$v['username'].' | '.$v['realname'];
				if($t->where(array('pid'=>$v['id']))->getfield('id')){
					$list[$k]['isParent']=true;
				}else{
					$list[$k]['isParent']=false;
				}
				$arr[]=$list[$k];
				//$this->tomap($v['id']);
			}
			return json_encode($arr);
		}
	}
	
	public function point() {
        $sqlmap = array();
		$this->model=M('member_joinsorts');
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $this->model->where($sqlmap)->order("member_sortid desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("member_sortid desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
        foreach ($list as $key => $value) {
            $list[$key]['realname'] = M('member')->where(array('id'=>$value['member_id']))->getField('realname');
        }
        $pages = $this->admin_pages($count, $_GET['limit']);
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        $this->display();
    }

	public function pai(){
		$this->model=M('money_types');
        $sqlmap = array();
		$type=$_GET['type'];
		$keyword=$_GET['keyword'];
		if($type){
			$sqlmap['money_type']=$type;
		}
		if($keyword){
			$u_id_arr=M('member')->where(array('username'=>$keyword))->getField('id',true);
			if($u_id_arr){
				$sqlmap['member_getid']=array('in',implode(',',$u_id_arr));
			}else{
				$sqlmap['member_getid']='0';
			}
		}
		 if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime($_GET['end']);
            $sqlmap['money_produtime']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['money_produtime']=array('egt',$start_time);
        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime($_GET['end']);
            $sqlmap['money_produtime']=array('elt',$end_time);
        }

		
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $this->model->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
		
		$count_money= $this->model->where($sqlmap)->sum('money_nums');

        $zhitui_money= $this->model->where($sqlmap)->where(array('money_type'=>1))->sum('money_nums');
        $tuan_money= $this->model->where($sqlmap)->where(array('money_type'=>2))->sum('money_nums');
        $zj_money= $this->model->where($sqlmap)->where(array('money_type'=>3))->sum('money_nums');
		$reward_money=$this->model->where($sqlmap)->where(array('money_type'=>4))->sum('money_nums');
        foreach ($list as $key => $value) {
            $list[$key]['member_getid'] = M('member')->where(array('id'=>$value['member_getid']))->getField('username');
            $list[$key]['member_giveid'] = M('member')->where(array('id'=>$value['member_giveid']))->getField('username');
        }
        $pages = $this->admin_pages($count, $_GET['limit']);
        $this->assign('list', $list);
        $this->assign('count_money', $count_money);
		$this->assign('zhitui_money', $zhitui_money);
        $this->assign('tuan_money', $tuan_money);
        $this->assign('zj_money', $zj_money);
		$this->assign('reward_money', $reward_money);
        $this->assign('pages', $pages);

        $this->display();
	}

    //用户购买矿机明细
    public function vsclist(){
        $this->model=M('money_buy');
        $sqlmap = array();
        $type=$_GET['type'];
        $keyword=$_GET['keyword'];
        if($type){
            $sqlmap['kuang_type']=$type;
        }
        if($keyword){
            $u_id_arr=M('member')->where(array('username'=>$keyword))->getField('id',true);
            if($u_id_arr){
                $sqlmap['uid']=array('in',implode(',',$u_id_arr));
            }else{
                $sqlmap['uid']='0';
            }
        }
        if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime($_GET['end']);
            $sqlmap['time']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['time']=array('egt',$start_time);
        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime($_GET['end']);
            $sqlmap['time']=array('elt',$end_time);
        }


        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $this->model->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();

        $count_money= $this->model->where($sqlmap)->sum('price');


        foreach ($list as $key => $value) {
            $list[$key]['uid'] = M('member')->where(array('id'=>$value['uid']))->getField('username');

        }
        $pages = $this->admin_pages($count, $_GET['limit']);
        $this->assign('list', $list);
        $this->assign('count_money',$count_money);
        $this->assign('pages', $pages);

        $this->display();
    }



	public function jihuo(){
        $this->model=M('jihuo_details');
        $sqlmap = array();
        $keyword=$_GET['keyword'];
        if($keyword){
                $sqlmap['user_username']=$keyword;
        }
        $sqlmap['member_getid'] = array("neq",0);
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $this->model->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
        $sqlmap['money_type']=array('neq',4);

        $pages = $this->admin_pages($count, $_GET['limit']);
        $this->assign('list', $list);
        $this->assign('pages', $pages);

        $this->display();
    }
	//奖励明细
	public function exports(){
        $sqlmap = array();
        if($_GET['keyword']){
            $k_user = M("member")->where(array('username'=>$_GET['keyword']))->find();
            $sqlmap['member_getid'] = array('eq',$k_user['id']);
        }
        if($_GET['type'] && $_GET['type'] != 0){
            $sqlmap['money_type'] = array('eq',$_GET['type']);
        }
		if($_GET['start'] &&  $_GET['end']){
            $start_time=strtotime($_GET['start']);
            $end_time=strtotime($_GET['end']);
            $sqlmap['money_produtime']=array('between',array($start_time,$end_time));
        }elseif($_GET['start'] &&  !$_GET['end']){
            $start_time=strtotime($_GET['start']);
            $sqlmap['money_produtime']=array('egt',$start_time);
        }elseif(!$_GET['start'] &&  $_GET['end']){
            $end_time=strtotime($_GET['end']);
            $sqlmap['money_produtime']=array('elt',$end_time);
        }

        $limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? $_GET['limit'] : 20;
        $data = M('money_types')->where($sqlmap)->order('id desc')->select();


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


        $k1="获得用户";$k2="提供会员";$k3="获取类型";$k4="奖励金额";$k5="时间";
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

            $membera = M("member")->where(array('id' => $v['member_getid']))->find();
            $memberb = M("member")->where(array('id' => $v['member_giveid']))->find();

            $objPHPExcel->getActiveSheet()->getStyle('A'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('B'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('C'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('D'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($num))->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));


            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $membera['username'])
                ->setCellValue('B'.$num, $memberb['username'])
                ->setCellValue('C'.$num, types_of($v['money_type']))
                ->setCellValue('D'.$num, $v['money_nums'])
                ->setCellValue('E'.$num, date("Y-m-d H:i:s",$v['money_produtime']));
        }
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.getdatetime(time(),'Y-m-d').'奖励明细.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');



    }
	
	
	
	   public function exports_user(){
            $sqlmap = array();
            $fc=0;
            if($_GET['report_id']){
                $fc=1;
                if($_GET['report_id']==1){
                    $sqlmap['grade']='1';
                }elseif ($_GET['report_id']==2) {
                    $sqlmap['grade']='2';
                }elseif($_GET['report_id']==3){
                    $sqlmap['grade']='3';
                }elseif ($_GET['report_id']==4) {
                    $sqlmap['grade']='4';
                }elseif($_GET['report_id']==5){
                    $sqlmap['grade']='5';
                }elseif($_GET['report_id']==6){
                    $sqlmap['grade']='6';
                }elseif($_GET['report_id']==7){
                    $sqlmap['grade']='0';
                }
            }
            //团队等级
            $tc=0;
            if($_GET['tuan_id']){
                $tc=1;
                if($_GET['tuan_id']==1){
                    $sqlmap['agency_level']='1';
                }elseif ($_GET['tuan_id']==2) {
                    $sqlmap['agency_level']='2';
                }elseif($_GET['tuan_id']==3){
                    $sqlmap['agency_level']='3';
                }elseif ($_GET['tuan_id']==4) {
                    $sqlmap['agency_level']='0';
                }
            }

            if($_GET['start'] &&  $_GET['end']){
                $start_time=strtotime($_GET['start']);

                $end_time=strtotime($_GET['end']);
                $sqlmap['register_time']=array('between',array($start_time,$end_time));
            }elseif($_GET['start'] &&  !$_GET['end']){

                $start_time=strtotime($_GET['start']);

                $sqlmap['register_time']=array('egt',$start_time);

            }elseif(!$_GET['start'] &&  $_GET['end']){

                $end_time=strtotime($_GET['end']);
                $sqlmap['register_time']=array('elt',$end_time);
            }
            if ($_GET['keyword']) {
                $sqlmap['username|email|mobile|realname|nickname'] = array('like', '%' . $_GET['keyword'] . '%');
            }
            $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;

            if(!empty($fc)){
                $data=M('member  m')->field('m.*,f.grade')->join('left join gboy_money_finance f on m.id=f.uid ')->where($sqlmap)->order("id desc")->select();

            }
            if(!empty($tc)){
                $data=M('member  m')->field('m.*,f.agency_level')->join('left join gboy_money_finance f on m.id=f.uid ')->where($sqlmap)->order("id desc")->select();

            }
            if(empty($fc) && empty($tc) && empty($cj)){
                $data = $this->model->where($sqlmap)->order("id desc")->select();

            }

            if ($_GET['keyword']) {
                $sqlmap['username|email|mobile|realname|nickname'] = array('like', '%' . $_GET['keyword'] . '%');
            }


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



            $k1="会员";$k2="介绍人";$k3="接点人";$k4="USDT余额";$k5="VSC余额";$k6="直推人数";$k7="左区业绩";$k8="右区业绩";$k9="会员等级";$k10="团队等级";$k11="注册时间";
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



            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);




            foreach($data as $k => $v){

                if(empty($k)){

                    $num=2;
                }else{
                    $num=2+$k;

                }
                $m_bi=M('money_finance')->where(array('uid'=>$v['id']))->find();
                if($m_bi['grade']==0){
                    $type_s = "游客";
                }elseif($m_bi['grade']==1){
                    $type_s = "V-A";
                }elseif($m_bi['grade']==2){
                    $type_s = "V-B";
                }elseif($m_bi['grade']==3){
                    $type_s = "V-C";
                }elseif($m_bi['grade']==4){
                    $type_s = "V-D";
                }elseif($m_bi['grade']==5){
                    $type_s = "V-E";
                }else{
                    $type_s = "V-F";
                }
                if($m_bi['agency_level']==0){
                    $type_t = "暂无等级";
                }elseif($m_bi['agency_level']==1){
                    $type_t = "Boss";
                }elseif($m_bi['agency_level']==2){
                    $type_t = "Big boss";
                }elseif($m_bi['agency_level']==3){
                    $type_t = "Super boss";
                }
                $jieshao_name=M('member')->where(array('id'=>$v['pid']))->getField('username');
                $j_name=M('member_sorts')->where(array('uid'=>$v['id']))->getField('pid');
                $uuname=M('member_sorts')->where(array('id'=>$j_name))->getField('uid');
                $one_name=M('member')->where(array('id'=>$uuname))->getField("username");

                $zhizhi=M('member')->where(array('pid'=>$v['id']))->count();
                $zy_money=M('member_sorts')->where(array('uid'=>$v['id']))->find();
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
                    ->setCellValue('A'.$num, $v['username'])
                    ->setCellValue('B'.$num, $jieshao_name)
                    ->setCellValue('C'.$num, $one_name)
                    ->setCellValue('D'.$num, $m_bi['usdt'])
                    ->setCellValue('E'.$num, $m_bi['member_z_bi'])
                    ->setCellValue('F'.$num, $zhizhi)
                    ->setCellValue('G'.$num, $zy_money['member_lposition'])
                    ->setCellValue('H'.$num, $zy_money['member_rposition'])
                    ->setCellValue('I'.$num, $type_s)
                    ->setCellValue('J'.$num, $type_t)
                    ->setCellValue('K'.$num, date("Y-m-d H:i:s",$v['register_time']));


            }
            ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.getdatetime(time(),'Y-m-d').'会员明细表.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
    }
	
	
	
    public function sfmx(){
        $this->model=M('sfmx');
        $sqlmap = array();
        $type=$_GET['type'];
        $keyword=$_GET['keyword'];
        if($type){
            $sqlmap['type']=$type-1;
        }
        if($keyword){
            $u_id_arr=M('member')->where(array('username'=>$keyword))->getField('id',true);
            if($u_id_arr){
                $sqlmap['uid']=array('in',implode(',',$u_id_arr));
            }else{
                $sqlmap['uid']='0';
            }
        }
        $_GET['limit'] = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $count = $this->model->where($sqlmap)->order("id desc")->count();
        $Pages = new \Think\Page($count, $_GET['limit']);
        $list = $this->model->where($sqlmap)->order("id desc")->limit($Pages->firstRow . ',' . $Pages->listRows)->select();
//        $sqlmap['money_type']=array('neq',4);
//        $count_money= $this->model->where($sqlmap)->sum('money_hcb');

        foreach ($list as $key => $value) {
            $list[$key]['uid'] = M('member')->where(array('id'=>$value['uid']))->getField('username');
        }
        $pages = $this->admin_pages($count, $_GET['limit']);
        $this->assign('list', $list);
//        $this->assign('count_money', $count_money);

        $this->assign('pages', $pages);

        $this->display();
    }

	public function edit() {
        $id = (int) $_REQUEST['id'];

        if($_POST) {

            $result = $this->service->save($_POST);
            if(!$result){//var_dump($this->service->errors);
              //  var_dump($result);
                showmessage($this->service->errors);
            }

            showmessage(L('_os_success_'), U('index'), 1);


        } else {
			$member = $this->service->find(array('id'=>$id));
			
            $this->assign('member', $member)->display();
        }
		
    }



    //后台管理登录前台用户
	public function login(){
		$mid=(int)$_GET['id'];
		if(!$this->service->find(array('id'=>$mid))){
			showmessage(L('username_not_exist'), U('member/member/index'));
		}
        $rand = random(6);
        $auth = authcode($mid . "\t" . $rand, 'ENCODE');
        cookie('member_auth', $auth, 86400);
        cookie('member_authadmin', $auth, 86400);
		$_SESSION['login_info']=array('login_time'=>'-1','one_login_time'=>'-1');
		showmessage(L('login_success'), U('member/index/index'), 1);
        //echo U('member/index/index');
      //  header( U('member/index/index'));
     //   header('Location:'.U('member/index/index'));
	}
	    public function mapp(){
        
        if($_GET['ac']=='ajax'){
            
            echo $this->tomapp('0');
            exit();
        }
        $this->display();
    }
    
    
    //无限级树形
    function tomapp($pid='0'){
    //echo '4444';die;
    
        global $arr;
        $t=M('member_sorts');
        //$return_ka=M('return_ka');
        $sort=M('member_sorts');//左区右区表
        $time=array('between',array(strtotime(date('Y-m-d 0:0:0',time())),strtotime(date('Y-m-d 23:59:59',time()))));
        $list=$t->where(array('pid'=>$pid))->order('id asc')->select();
        if(is_array($list)){
        
            foreach($list as $k=>$v){

                //$dada=$sort->where(array('uid'=>$v['id']))->find();
                $vv=M('member')->where(array('id'=>$v['uid']))->find();




				$personal=$sort->where(array('uid'=>$v['id']))->find();




               $list[$k]['name']=$vv['username'].' | '.$vv['realname'].' | 左区业绩:'.$personal['member_lposition'].' | 右区业绩:'.$personal['member_rposition'];
                if($t->where(array('pid'=>$v['id']))->getfield('id')){
                    
                    $list[$k]['isParent']=true;
                }else{
                    
                    $list[$k]['isParent']=false;
                    
                }
                
                $arr[]=$list[$k];
            
                $this->tomapp($v['id']);
            }
            
            
            return json_encode($arr);
            
        }
        
        
        
    }
}

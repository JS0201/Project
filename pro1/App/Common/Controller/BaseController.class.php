<?php

namespace Common\Controller;

use Think\Hook;

class BaseController extends CommonController {
	
	public $donjie=[];
	
    public function _initialize() {

        parent::_initialize();

        cookie('think_var', 'zh');
        
        $go_url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER["REQUEST_URI"];
        $_SESSION['go_url'] = $go_url;
        Hook::add('Site', 'Home\\Behavior\\SiteBehavior');
        hook('Site');
        $this->member = D('Member/Member', 'Service')->init();
  
        //团队人数
        $daili=$this->member['daili_dj'];
        if($daili>0){
            $id=$this->member['id'];
            $ty=array('like','%,'.$id.',%');
            $zong_tuan=M('member')->where(array('path_id'=>$ty,'id'=>array('neq',$id),'gou'=>1))->count();
            $this->assign('zong_tuan',$zong_tuan);
        }

        $p_open_price=M('setting')->where(array('key'=>'p_open_price'))->getField('value');
        $this->assign('p_open_price',$p_open_price);

        $this->assign('member', $this->member);
		$uid=$this->member['id'];
		$pid=M('member')->where(array('id'=>$uid))->getField('pid');
        $username_p=M('member')->where(array('id'=>$pid))->getField('username');
        $realname_p=M('member')->where(array('id'=>$pid))->getField('realname');
        $this->assign('username_p',$username_p);
        $this->assign('realname_p',$realname_p);
		
		
        $money_finance = M('money_finance')->where(array('uid' => $uid))->find();
        $this->assign('money_finance',$money_finance);
        $username=M('member')->where(array('id'=>$uid))->getField('username');
        $realname=M('member')->where(array('id'=>$uid))->getField('realname');
        $this->assign('username',$username);
        $this->assign('realname',$realname);
        if($uid){
            $donjie=M('money_finance')->where(array('uid'=>$uid))->find();//全局输出会员财务信息

            $donjie['zi']=M('withbi')->where(array('member_id'=>$uid,'withdrawals_paytype'=>1))->sum('withdrawals_nums');//提币子链

            $donjie['mu']=M('withbi')->where(array('member_id'=>$uid,'withdrawals_paytype'=>2))->sum('withdrawals_nums');//提币母链

            $donjie['grade_name'] = M('member_group')->where(array('grade'=>$donjie['grade']))->getField('name');
			$this->donjie=$donjie;

			$member_info = M('member')->where(array('id'=>$uid))->find();
            $this->assign('member_info', $member_info);
            $this->assign('donjie', $donjie);

        }
		 //判断是否开启购买
        $que_time=M('tixian_setting')->where(array('id'=>1))->find();
        if($que_time['que_status']==1){
            $checkDayStr=date('Y-m-d ',time());
            $p_time=rtrim($que_time['que_time'], '-');//去除逗号
            $prev_path = explode('-', $p_time);//组成数组
            $timeBegin1 = strtotime($checkDayStr."$prev_path[0]".":00");  
            $timeEnd1 = strtotime($checkDayStr."$prev_path[1]".":00");
            $curr_time = time();
            if($curr_time >= $timeBegin1 && $curr_time <= $timeEnd1){  
                $t_time=0;
            }else{
                $t_time=1;
            }
        }else{
            $t_time=1;
        }

        $this->assign('t_time',$t_time);
        $this->assign('que_time',$que_time);
		
    }

    /*     * 重写display

     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $templateFile 模板文件名
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀
     * @return mixed
     */

    final public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {

        $m = MODULE_NAME;
        $c = CONTROLLER_NAME;
        $a = ACTION_NAME;
        $tmpl_path = C('TMPL_PATH');
        $depr = C('TMPL_FILE_DEPR');
        $theme = C('_DEFAULT_THEME');
        $suffix = C('TMPL_TEMPLATE_SUFFIX');
        if (empty($templateFile)) {
             $templateFile = $tmpl_path . '/' . $theme . '/' . $m . '/' . $c . $depr . $a . $suffix;
        }
        parent::display($templateFile, $charset = '', $contentType = '', $content = '', $prefix = '');
    }

    public function lang(){
        $lang = input('?get.lang') ? input('get.lang') : 'zh';
        switch ($lang) {
            case 'zh':
                cookie('think_var', 'zh');
                break;
            case 'en':
                cookie('think_var', 'en');
                break;
            //其它语言
            default:
                cookie('think_var','ch');
        }
    }

}

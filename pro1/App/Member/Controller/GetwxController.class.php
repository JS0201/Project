<?php

/**
 * Created by gaorenhua.
 * User: 597170962 <597170962@qq.com>
 * Date: 2015/6/29
 * Time: 21:28
 */

namespace Member\Controller;

use Common\Controller\BaseController;

/**
 * 通用控制器
 * 主要用于验证是否登陆 以及 用户权限
 * @package Home\Controller
 */
class GetwxController extends BaseController
{

	public function _initialize()
	{
		parent::_initialize();

		$this->member = D('Member/Member', 'Service')->init();
	}

	public function getseid()
	{

		$userid = $this->member['id'];

	
		$this->redirect('Getwx/getWxCode', array('userid' => $userid));

	}

	//二维码详情
	public function getWxCode()
	{
	
    	//二维码
    	$id=$_GET['userid'];
    	$member=M('member')->where(array('id'=>$id))->find();
    	$mobile=$member['mobile'];

		$name=$member['realname'];
    	//$url=U("Member/Public/register",array('mobile'=>$mobile));

    	$al=M('member')->where(array('id'=>$id))->getField('code_img');
    	if($al){
    		$this->assign('img_url', $al);
    	}else{
			
	    	$url="http://".$_SERVER['SERVER_NAME']."/index.php/Member/Public/register/mobile/".$mobile;
	    	
	    	$file_url="uploads/code/".$id.'.png';
	    	$img_url=phpqrcode($url,$file_url);
	    	M('member')->where(array('id'=>$id))->setField('code_img',$img_url);
	    	$this->assign('img_url', $img_url);
    	}
    	$this->assign('name', $name);
    	$this->display();
	}

//填写个人信息
	public function determine()
	{
		$member = M('member');
		$where['id'] = $this->member['pid'];
		$p_mobile = $member->where($where)->find();
		$wherep['id'] = $this->member['id'];
		$u_oldinfo=$member->where($wherep)->find();
		 $this->u_oldinfo = $u_oldinfo;
		$this->p_mobile = $p_mobile;
		$this->display();

	}

	// 获取微信信息
	public function getweixin()
	{
		if (empty($_GET['code'])) {//请求微信公众号
			echo "<script>window.location.href=\"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx2e9575ebfa3f86b8&redirect_uri=http://{$_SERVER['HTTP_HOST']}/index.php/Member/Getwx/getweixin/&response_type=code&scope=snsapi_userinfo&state=STATE&connect_redirect=1#wechat_redirect\";</script>";
			die;
		} else {


			// =====获取用户微信头像=====
			// 获取access_token和openid
			$code = $_GET['code'];
			$return = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx2e9575ebfa3f86b8&secret=f72c937185322ffc23bebd3cb4c1178b&code=" . $code . "&grant_type=authorization_code");
			$access = json_decode($return, 1);
			$openid = $access['openid'];

			$access_token = $access['access_token'];
			//获取用户信息
			$info = file("https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid);
			//json数据转为数组
			$user_info = json_decode($info[0], 1);
			$member = M('member');
			$where['openid'] = $user_info['openid'];
			$u_info = $member->where($where)->find();

			if (empty($u_info)) {
				//通过URL地址获得公众号，直接进来的
				$data = array();
				$data['username'] = rand(100000, 999999);
				$data['password'] = '123456';
				$data['pwdconfirm'] = '123456';
				$data['pid'] = 0;
				$data['openid'] = "" . $openid . "";
				D('Member/Member', 'Service')->register($data);

				$u_info['username'] = $data['username'];
				$infodata['nickname'] = $user_info['nickname'];
				$infodata['face'] = $user_info['headimgurl'];
				$wheret['openid'] = "" . $user_info['openid'] . "";
				$member->where($wheret)->save($infodata);

			} else {

				$infodata['nickname'] = $user_info['nickname'];
				$infodata['face'] = $user_info['headimgurl'];
				$where['openid'] = "" . $openid . "";
				$member->where($where)->save($infodata);

				$this->member_service = D('Member/Member', 'Service');
				$r = $this->member_service->login($u_info['username'], '123456');
			}


			if ($_SESSION['go_url']) {

				$url = $_SESSION['go_url'];
			} else {
				$url = U('home/Index/index');
			}

            if (empty($u_info['mobile'])) {
              $url = U('Member/Getwx/determine');
              $this->redirect($url);
               // $this->redirect(U('Member/Getwx/determine'));
// $this->redirect($url);
            } else {
				$this->member = D('Member/Member', 'Service')->init();
                $wheretg['id']=$this->member['id']; 
                
                $mobile=$member->where($wheretg)->getfield('mobile'); 
                if(empty($mobile)){
                     $url = U('member/Getwx/determine');
                }else{
                
                }
                
			$this->redirect($url);

		 }
		}
	}

	public function test()
	{
		session('userid', null);
	}

	// 发送验证码
	public function sentcode()
	{
		$mobile = I('post.mobile');
		$user = M("member");
		$bool = $user->where(array('mobile' => $mobile))->find();

		if (empty($mobile)) {
			$data['state'] = 0;
			$data['res'] = "手机号不能为空";
		} elseif (time() > session('set_time') + 60 || session('set_time') == '') {
			// ==产生验证码==
			$code = rand(100000, 999999);
			// 5分钟后session过期
			session('set_time', time());
			session('code', $code, 300);
			session('mobile', $mobile);
			// 发送验证码
			$mes = setMsg($mobile, $code);
			if ($mes == '0') {
				$data['state'] = 1;
				$data['res'] = "发送成功";
			} else {
				$data['state'] = 0;
				$data['res'] = "发送失败";
			}
		} else {
			$msgtime = session('set_time') + 60 - time();
			$res = $msgtime . '秒之后再试';
			$data['state'] = 0;
			$data['res'] = $res;
		}
		$this->AjaxReturn($data);
	}
}

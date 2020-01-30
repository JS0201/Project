<?php

/**
 * Created by gaorenhua.
 * User: 597170962 <597170962@qq.com>
 * Date: 2015/6/28
 * Time: 0:19
 */

namespace Member\Controller;

// use Common\Controller\BaseController;
use Think\Controller;

class WeixinController extends Controller {

    public function index() {

        $this->responseMsg();
    }

    public function valid() {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg() {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
              the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName; //发送者open_id

            $toUsername = $postObj->ToUserName; //接收者 即公众号ID
            $msgType = $postObj->MsgType;
            // 获得菜单点击事件
            $event = trim($postObj->Event);
            $eventKey = trim($postObj->EventKey);
            //$keyword 用户输入的信息
            $keyword = trim($postObj->Content);
            $time = time();

            //======用于返回信息=========
            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";

            if (!empty($eventKey)) {
                //判断用户是否已关注
                $keyArray = explode("_", $eventKey);
                if (count($keyArray) == 1) { //已关注
                    //<EventKey><![CDATA[qrscene_123123]]></EventKey>
                    $str = "天地融集团，欢迎您回来";
                    $pid = $eventKey;
                } else { //未关注
                    // <EventKey><![CDATA[SCENE_VALUE]]></EventKey> 
                    $str = "欢迎关注win微信公众平台,首次关注,请点击“会员中心”,完善个人资料。";
                    $pid = $keyArray[1];
                }

                $openid = $fromUsername;
                    
                // =====注册会员====
                $this->register($openid, $pid);
               
              
                // =====注册会员====
                $msgType = "text";
                $resTpl = $textTpl;
                $contentStr = $str;
                // 返回$textTpl字符串并附加上数据
                $resultStr = sprintf($resTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            } else {
                // echo "Input something...";
            }
        } else {
            echo "";
            exit;
        }
    }

    private function checkSignature() {
        // you must define TOKEN by yourself
        $token = 'dragondean';
        if (empty($token)) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    // 生成带参数二维码
    public function getMycode($userid) {
        // 获取access_token
        $token_arr = $this->access_token();
        $access_token = $token_arr['access_token'];
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token;
        // expire_seconds 该二维码有效时间，以秒为链。 最大不超过604800（即7天）
        // action_name 二维码类型，QR_SCENE为临时,QR_LIMIT_SCENE为永久,QR_LIMIT_STR_SCENE为永久的字符串参数值
        $data = '{
                "expire_seconds": 604800,
                "action_name": "QR_SCENE",
                "action_info": {"scene": {"scene_id":' . $userid . '}}
              }';
        $result = $this->https_post($url, $data);
        $jsinfo = json_decode($result, 1);
        $ticket = $jsinfo['ticket'];
        // 获取二维码
        $get_rwm = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket;
        return $get_rwm;
    }

    /**
     * [https_post post提交数据]
     * @param  [type] $url  [提交地址]
     * @param  [type] $data [提交数据]
     * @return [type]       [description]
     */
    public function https_post($url, $data = null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //返回数组，access_token 和  time 有效期 
    public function access_token() {
        $appid = "wx2e9575ebfa3f86b8";
        $appsecret = "f72c937185322ffc23bebd3cb4c1178b";
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
        $cont = file_get_contents($url);
        return json_decode($cont, 1);
    }

    /**
     * [register 注册用户]
     * @param  [type] $openid [用户openid]
     * @param  [type] $pid    [父级ID]
     * @return [type]         [description]
     */
    public function register($openid, $pid) {
        if (empty($openid)) {
            return;
        }
        $where['openid'] = "" . $openid . "";
        $info = M('member')->where($where)->find();

        if (empty($info)) {
            $this->member_service = D('Member/Member', 'Service');
            $data = array();
            $data['username'] = rand(100000, 999999);
            $data['password'] = '123456';
            $data['pwdconfirm'] = '123456';
            $data['openid'] = "" . $openid . "";
            $data['pid'] = $pid;
            $r = $this->member_service->register($data);
            
             $this->sendmessage("" . $openid . "");
        }
    }

    // 发送消息通知上级
    public function sendmessage($openid){
           $openid=$openid;
             
            // 获得所有父级的openid
              $arrparent=$this->getParents($openid);
                $oowhere['openid']=$openid;
              $id=M('member')->where($oowhere)->getfield('id');
            // 如果存在父级，通过公众号发送通知消息
            if(count($arrparent)>0){
            // 获取access_token
                $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx2e9575ebfa3f86b8&secret=f72c937185322ffc23bebd3cb4c1178b";
                $str=file_get_contents($url);
                // 转为数组
                $info=json_decode($str,1);
                $access_token=$info['access_token'];
                // 发送消息
                $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
                // 模板ID
                $template_id="72UyqkOjzuwEr4KAIeNVSl9qrQdFHx7MA2jem1q7u2Q";

                // 请求数据
                $data = array(
                    'first' => array(
                        'value' => '您好，您有新的下级会员注册成功。'
                    ),
                    'keyword1' => array(
                        'value' => "TDR".$id
                    ),
                    'keyword2' => array(
                        'value' => date('Y-m-d')
                    ),
                    'remark' => array(
                        'value' => '详情请进入TDR商城查看'
                    )
                );
               foreach ($arrparent as $k => $v) {
                    $openid=$v['openid'];
                 if(!empty($openid)){
                     $template_msg=array('touser'=>$openid,'template_id'=>$template_id,'topcolor'=>'#FF0000','data'=>$data);
                     $this->post_data($url, json_encode($template_msg));
                 }
               }
            }


    }


    private function post_data($url,$param){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /*
        递归获取所有父级
     */
    public function getParents($openid){
        $users=M('member');

          //静态变量，用于存上级ID
        static $result1= array();
        $onewhere['openid']=$openid;
        $info=$users->where($onewhere)->find();
        $pid=$info['pid'];//获取父级ID

        if($pid>0){
            $twowhere['id']=$pid;
            $onepinfo=$users->where($twowhere)->find();//当前用户的上级
            if($onepinfo['openid']!='0'){
                $result1[]['openid']=$onepinfo['openid'];
                //$result1[]['id']=$onepinfo['id'];
                $onepid=$onepinfo['pid'];
                $threewhere['id']=$onepid;
                $twoinfo=$users->where($threewhere)->find();//当前用户的上级的上级
                if($twoinfo['openid']!='0'){
                    $result1[]['openid']=$twoinfo['openid'];
                    //$result1[]['id']=$twoinfo['id'];
                }
              }
            }
        return $result1;
    }

	// 发送验证码
	public function sentcode(){
		$mobile=I('post.mobile');
		$user=M("member");
		$bool=$user->where(array('mobile'=>$mobile))->find();

		if(empty($mobile)){
			$data['state']=0;
			$data['res']="手机号不能为空";
		}

		elseif(time() >session('set_time')+60 || session('set_time') == ''){
			// ==产生验证码==
			$code=rand(100000,999999);
			// 5分钟后session过期
			session('set_time',time());
			session('code',$code,300);
			session('mobile',$mobile);

			// 发送验证码
			$mes=setMsg($mobile,$code);
			if($mes=='0'){
				$data['state']=1;
				$data['res']="发送成功";
			}else{
				$data['state']=0;
				$data['res']="发送失败";
			}
		}else{
			$msgtime=session('set_time')+60 - time();
			$res = $msgtime.'秒之后再试';
			$data['state']=0;
			$data['res']=$res;
		}
		$this->AjaxReturn($data);
	}

}

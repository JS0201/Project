<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/24
 * Time: 16:18
 */
namespace app\demo\controller;
use app\common\controller\Base;
use think\Session;
use app\demo\service\Wechat;
class Index extends Base
{
    private $cfg = array(
        //测试商户号，商户需改为自己的
        'merchantNo'=>'8090595609861811',
        //#测试密钥，商户需改为自己的
        'secret'=>'120455030441',
        //接口请求地址，固定不变，无需修改
        'reqUrl'=>'https://pay.cnmobi.cn/pay/',
        //通知回调地址，目前默认是****，商户在测试支付和上线时必须改为自己的，且保证外网能访问到
        'nofityUrl'=>'tsz.sjzc5168.com/demo/index/back'
    );


    public function index() {
        if(is_post()) {
            $params['orderNo'] = $_POST['orderNo'];
            $params['name'] = $_POST['name'];
            $params['total'] = $_POST['total'];
            $params['merchantNo'] = $this->cfg['merchantNo']; //获取配置文件里面的商户号
            $params['nofityUrl'] = $this->cfg['nofityUrl']; //获取配置文件里面的回调
            $data = $this->HttpPost('scancode/wx', $params);

            $data = $this->signCheck($data); //对返回的数据进行验签名
            echo $data;
        }else{
            return $this->fetch();
        }
    }
    //  wx080f81aeae2460ee
    public function pay()
    {
        if(is_post()) {
                $params['openID'] = $_POST['openID'];
                $params['orderNo'] = $_POST['orderNo'];
                $params['name'] = $_POST['name'];
                $params['total'] = $_POST['total'];
                $params['returnUrl'] = $_POST['returnUrl'];
                $params['merchantNo'] = '800440054111002'; //获取配置文件里面的商户号
                $params['nofityUrl'] = "http://aus-club.com/index.php?m=cron&c=index&a=demo"; //获取配置文件里面的回调

                $data = $this->HttpPost('wxgzh/api', $params);
                $data = $this->signCheck($data); //对返回的数据进行验签名
                echo $data;
                exit;
        }
        return $this->fetch();
    }



    //微信扫一扫
    public function saosao() {
        $wx = config('configs.wx');
        $js = new Wechat($wx['appId'], $wx['secret']);
        $sign = $js->getSignPackage();
        $this->assign('data', $sign);
        return $this->fetch();
    }

    public function successful() {
        echo "支付成功。。。";
    }

    //回调地址 / 检测订单状态
    public function back() {
        $response = $_REQUEST;
        $resSign=$response['sign'];
        if(!$response){
            echo "error";
            die();
        }
        unset($response['sign']);
        $sign=$this->createSign($response);
        if($sign==$resSign){
            if($response['code'] == 1) {
                session('result', 1); //代表成功
            }

        }
    }
    //检查订单是否支付成功
    public function check() {
        if(is_post()) {
            $url = "order/orderStatus";
            $param['orderNo'] = input('post.orderNo');
            $param['merchantNo'] = $this->cfg['merchantNo'];
            $result = $this->HttpPost($url, $param);
            $re = json_decode($result, true);
            if($re['result']['code'] == 1) {
                echo json_encode(array('status' => 1,'message' => '支付成功'));
            }else{
                echo json_encode(array('status' => 0,'message' => ''));
            }
        }
    }



    public function HttpPost($url, $param)
    {
        $param['timestamp'] = 	time()*1000;  //统一给参数添加时间戳参数
        $reqUrl = "https://pay.cnmobi.cn/pay/";
        $param['sign'] = $this->createSign($param); //生成签名参数

        $ch = curl_init();//启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $reqUrl . $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //设置请求超时时间
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); //设置请求方式为POST请求
        curl_setopt($ch, CURLOPT_POST, 1); //发送一个常规的POST请求。
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param)); //将params 转成 a=1&b=2&c=3的形式
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //curl获取页面内容, 不直接输出

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $data = curl_exec($ch); // 已经获取到内容，没有输出到页面上。

        curl_close($ch);
        return $data;
    }

    public function  signCheck($response){
        $responses=json_decode($response, true);
        if(!isset($responses['result'])){
            return $response;
        }else{
            $result=$responses['result']; //得到result部分进行验签
            $resSign=$result['sign'];  //获取接口返回的sign
            unset($result['sign']);   //去除result的sign 将其加密生成签名
            $sign=$this->createSign($result);
            if($sign !=$resSign){ //判断接口传回的签名的是否和该签名一直、保证数据传输时不被篡改
                return json_encode(array("msg"=>"sign 验签失败",'code'=>5));
            }else{
                return  $response;
            }
        }
    }

    public function createSign($parms)
    {
        $signPars = "";
        ksort($parms);
        foreach ($parms as $k => $v) {
            if ("" != $v && "sign" != $k) {
                $signPars .= $k . "=" . $v;
            }
        }
        $secret = $this->cfg['secret'];
        $sign = md5($signPars . $secret); //sign签名生成
        return $sign;
    }
}
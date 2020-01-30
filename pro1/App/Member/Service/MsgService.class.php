<?php

namespace Member\Service;

use Think\Model;

class MsgService{


    /**
     * 发送短信
     *
     * @param string $mobile 		手机号码
     * @param string $msg 			短信内容
     */
    public function sendInternational( $mobile, $msg) {

        //创蓝接口参数
        $postArr = array (
            'account'  =>  "CI7141530",
            'password' => "kGXgaFlNAV3d90",
            'msg' => $msg,
            'mobile' => $mobile
        );

        $result = $this->curlPost("http://intapi.253.com/send/json?" , $postArr);
        return $result;
    }




    /**
     * 查询额度
     *
     *  查询地址
     */
    public function queryBalance() {
        //查询参数
        $postArr = array (
            'account' => self::API_ACCOUNT,
            'password' => self::API_PASSWORD,
        );
        $result = $this->curlPost(self::API_BALANCE_QUERY_URL, $postArr);
        return $result;
    }

    /**
     * 拉取上行短信
     *
     *
     */
    public function pullMo() {
        //const count = "20"
		//查询参数
		$postArr = array (
            'account' => self::API_ACCOUNT,
            'password' => self::API_PASSWORD,
            'count' => count
        );
		$result = $this->curlPost(self::API_PULL_MO_URL, $postArr);
		return $result;
	}

    /**
     * 拉取上行状态
     *
     *
     */
    public function pullReport() {
        //const count = "20"
		//查询参数
		$postArr = array (
            'account' => self::API_ACCOUNT,
            'password' => self::API_PASSWORD,
            'count' => count
        );
		$result = $this->curlPost(self::API_PULL_REPORT_URL, $postArr);
		return $result;
	}

    /**
     * 通过CURL发送HTTP请求
     * @param string $url  //请求URL
     * @param array $postFields //请求参数
     * @return mixed
     */
    private function curlPost($url,$postFields){
        $postFields = json_encode($postFields);
        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'
            )
        );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt( $ch, CURLOPT_TIMEOUT,10);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec ( $ch );
        if (false == $ret) {
            $result = curl_error(  $ch);
        } else {
            $rsp = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
            if (200 !== $rsp) {
                $result = "请求状态 ". $rsp . " " . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close ( $ch );
        return $result;
    }

}

?>
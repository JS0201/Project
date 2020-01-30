<?php

namespace Member\Controller;

class IndexController extends CheckController {

    public function _initialize() {
        parent::_initialize();
        $this->model = D('Member');
        $this->service = D('Member', 'Service');
    }

    public function index() {
    	$id=$this->member['id'];
        $userinfo = M('money_finance')->where(array('uid'=>$id))->find();
		$vsc_price=M('tixian_setting')->where(array('id'=>1))->getField('vsc_price');
		$con = M('content')->limit(3)->order('add_time desc')->select();
		//dump($con);
		//return;
        $this->assign("vsc_price",$vsc_price);
        $this->assign("dd",$con);
        $this->assign('userinfo',$userinfo);
        $this->display();
    }
    ////查询ETH余额
    public function queryBalance($wallet_address){
         $transName ="queryBalance";
         $address = $wallet_address;
         $curlPost = "transName=".$transName."&address=".$address;
         $ch=curl_init();
         curl_setopt($ch,CURLOPT_URL,'http://47.90.100.253:8080/ethServer/httpServer');
         curl_setopt($ch,CURLOPT_HEADER,0);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
         //设置是通过post还是get方法
         curl_setopt($ch,CURLOPT_POST,1);
         //传递的变量
         curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
         $data = curl_exec($ch);
         curl_close($ch);
         return $data;
    }
    //过滤字符串空格
    public function loseSpace($pcon){
        $pcon = preg_replace("/ /","",$pcon);
        $pcon = preg_replace("/&nbsp;/","",$pcon);
        $pcon = preg_replace("/　/","",$pcon);
        $pcon = preg_replace("/\r\n/","",$pcon);
        $pcon = str_replace(chr(13),"",$pcon);
        $pcon = str_replace(chr(10),"",$pcon);
        $pcon = str_replace(chr(9),"",$pcon);
        return $pcon;
    }

    public function get_path_id($id){
         $member = M('member');
         $al=$member->where(array('id'=>$id))->find();
         $pid = rtrim($al['path_id'], ',');//去除逗号,切割
         $prev_path = explode(',', $pid);//组成数组
         rsort($prev_path);//rsort() 函数对数值数组进行降序排序
         $prev_path=array_splice($prev_path,1); //去除自己的ID
         return $prev_path;
    }
    public function demo(){
            require_once("/java/Java.inc"); //引入java bridge
            //require_once("./java/Java.inc"); //引入java bridge
            try {
                die("123");
                $_oJava = new Java('myjava.MyJava');

                $_rRes = $_oJava->getAge(10);
                $_aRes = java_values($_rRes);
                print_r($_aRes);
            } catch (JavaException $_oExp) {
                dd($_oExp);
            }
    }

    public function friend(){
        $member = M('member');
        $id=$this->member['id'];
        $id=1;
        $where="path_id like '%$id%' and id!=$id";
        if (IS_POST) {
             $username = I('post.username');  //密码
            if(!empty($username)){
            $where=$where." and username like '%$username%' ";
            }
        }

        $al=$member->field("id,username")->where($where)->order("id desc ")->select();
        $this->assign("list",$al);
       $this->display();
    }
}

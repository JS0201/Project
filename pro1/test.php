<<<<<<< HEAD
<?php

////发起交易
$transName ="sendTransaction";
$address = "0x2a377b8aaabbb5685a1cd7e683791bb324437828";//过滤字符串空格
$value = "10000000000000000";//eth数量要乘以10的18次幂\number_format用于格式化数字格式
$password = "#@!tksc";
$curlPost = "transName=".$transName."&address=".$address."&value=".$value."&password=".$password;
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,'http://54.153.32.40:8080/ethServer/httpServer');
curl_setopt($ch,CURLOPT_HEADER,0);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//设置是通过post还是get方法
curl_setopt($ch,CURLOPT_POST,1);
//传递的变量
curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
$data = curl_exec($ch);
curl_close($ch);
$hash = $data;//得到的hash值,过滤字符串空格
var_dump($hash);exit;

//根据hash值获取交易
//$transName ="getTransactionByHash";
//$curlPost = "transName=".$transName."&hash=0x381da65aad6d204ab5bbc3f65b27ed0aa10c8d2a8cc3577261430c94d0fd090d";
//$ch=curl_init();
//curl_setopt($ch,CURLOPT_URL,'http://54.153.32.40:8080/ethServer/httpServer');
//curl_setopt($ch,CURLOPT_HEADER,0);
//curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
////设置是通过post还是get方法
//curl_setopt($ch,CURLOPT_POST,1);
////传递的变量
//curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
//$data = curl_exec($ch);
//curl_close($ch);
//$result = $data;//得到的hash值,过滤字符串空格
//$result = explode(",",$result);
//$result1 = explode(":",$result[0]);
//$result2 = explode(":",$result[1]);
//$hash = $result1[1];
//$value = $result2[1];


////新建账户
// $transName ="newAccount";
// $address = "0xa2fA34756B5c9059eE887CCD41c067811E6Cad6a";
// $curlPost = "transName=".$transName."&address=".$address;
// $ch=curl_init();
// curl_setopt($ch,CURLOPT_URL,'http://47.90.100.253:8080/ethServer/httpServer');
// curl_setopt($ch,CURLOPT_HEADER,0);
// curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
// //设置是通过post还是get方法
// curl_setopt($ch,CURLOPT_POST,1);
// //传递的变量
// curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
// $data = curl_exec($ch);
// echo $data;
// curl_close($ch);

////查询余额
// $transName ="queryBalance";
// $address = "0x9242a60d9173344de910ed154ec30e97411be639";
// $curlPost = "transName=".$transName."&address=".$address;
// $ch=curl_init();
// curl_setopt($ch,CURLOPT_URL,'http://47.90.100.253:8080/ethServer/httpServer');
// curl_setopt($ch,CURLOPT_HEADER,0);
// curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
// //设置是通过post还是get方法
// curl_setopt($ch,CURLOPT_POST,1);
// //传递的变量
// curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
// $data = curl_exec($ch);
// echo $data;
// curl_close($ch);

//查询用户交易记录
//$transName ="queryTransaction";
//$address = "0xa2fA34756B5c9059eE887CCD41c067811E6Cad6a";
//$curlPost = "transName=".$transName."&address=".$address;
//$ch=curl_init();
//curl_setopt($ch,CURLOPT_URL,'http://47.90.100.253:8080/ethServer/httpServer');
//curl_setopt($ch,CURLOPT_HEADER,0);
//curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
////设置是通过post还是get方法
//curl_setopt($ch,CURLOPT_POST,1);
////传递的变量
//curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
//$data = curl_exec($ch);
//echo $data;
//curl_close($ch);

//发起交易
//$transName ="sendTransaction";
//$address = "0x85971b7e26b843331b11b79dbbbd4af925c3d655";
//$value = "1000000000000000000";
//$password = "wn38waqm";
//$curlPost = "transName=".$transName."&address=".$address."&value=".$value."&password=".$password;
//$ch=curl_init();
//curl_setopt($ch,CURLOPT_URL,'http://47.90.100.253:8080/ethServer/httpServer');
//curl_setopt($ch,CURLOPT_HEADER,0);
//curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
////设置是通过post还是get方法
//curl_setopt($ch,CURLOPT_POST,1);
////传递的变量
//curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
//$data = curl_exec($ch);
//echo $data;
//curl_close($ch);

//根据hash值获取交易
//$transName ="getTransactionByHash";
//$hash = "0x17ccc0eb8103264caed55b1dcb6794b42873c75ab48f87ebee3bfb8be0d3663e";
//$curlPost = "transName=".$transName."&hash=".$hash;
//$ch=curl_init();
//curl_setopt($ch,CURLOPT_URL,'http://47.90.100.253:8080/ethServer/httpServer');
//curl_setopt($ch,CURLOPT_HEADER,0);
//curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
////设置是通过post还是get方法
//curl_setopt($ch,CURLOPT_POST,1);
////传递的变量
//curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
//$data = curl_exec($ch);
//echo $data;
//curl_close($ch);
?>
=======
<!-- 放公共文件 -->
//用户注册时添加path_id  用户注册LOGIN中调用
function path_id($uid) {
    $row=db('userinfo')->where('uid',$uid)->find();
    //$row = $this->where(array('id' => $uid))->find();
    if (empty($row['oid'])) {

        $path_id = '0,' . $uid . ',';
    } else {
        $prow = db('userinfo')->where('uid',$row['oid'])->find();
        $path_id = $prow['path_id'] . $uid . ',';
    }

    $newdata['path_id'] = $path_id;
    db('userinfo')->where('uid',$uid)->update($newdata);   //更新path_id
}



//收益明细
function types_of($status=''){
    $status_array=array('1'=>'直推收益','2'=>'普通会员团队收益','3'=>'高级会员动态收益');
    if($status==''){
        return $status_array;
    }else{
        return $status_array[$status];
    }

}
//会员等级
function agency_level($status=''){
    $status_array=array('0'=>'游客','1'=>'普通会员','2'=>'VIP会员');
    if($status==''){
        return $status_array;
    }else{
        return $status_array[$status];
    }

}
//游客充值升级普通会员、 
/**
 * @param $data
 */
function member_upgrade($data){

    $userinfo=db('userinfo');//会员个人信息表
    $uid=$data['uid'];
    $money=$data['money'];   //充值的金额
    $grade=$userinfo->where('uid',$uid)->value('grade');
    if($money > 0){
        if($grade == 0){
            db('userinfo')->where('uid',$uid)->update('grade','1');
        }

    }

}
//普通会员充BTC升级高级会员
function vip_upgrade($data){

    $userinfo=db('userinfo');//会员个人信息表
    $uid=$data['uid'];
    $type=$data['btc'];
    $money=$data['money'];   //充值的金额
    $grade=$userinfo->where('uid',$uid)->value('grade');

    if($grade > 0){
        if($type == 'btc'){
            $userinfo->where('uid',$uid)->update('grade','2');
            $userinfo->where('uid',$uid)->update('otype','101');   //给otype设置默认值101 识别
        }

    }

}


//算直推收益
function team_reward($us_data){

    $uid=$us_data['uid'];//购买产品用户ID
    $sx_fee=$us_data['sx_fee'];//购买产品手续费
    $userinfo=db('userinfo');
    $us_pid=$userinfo->wehre("uid",$uid)->field('oid','grade')->find();//查找购买产品会员的推荐人和等级
    $u_group=$userinfo->where('uid',$us_pid['oid'])->value('grade');//查找直推会员等级
    if ($u_group > 0) {
        $income_bili=0.01;
        $income= $sx_fee * $income_bili;   //手续费*比例

        $status=$userinfo->where('uid',$us_pid['oid'])->setInc("income",$income);
        if ($status) {
            //获取直推收益
            $bdata['member_getid']=$us_pid['oid'];//出售币获得金额 会员id
            $bdata['member_giveid']=$uid;//购买币会员id
            $bdata['money_produtime']=time();
            $bdata['money_nums']=$income;//当次交易金额
            $bdata['money_type']='1';  //直推
            db('moneytypes')->insert($bdata);//写入收益详情表
        }
    }


}

//购买产品后获取手续费收益
function user_path($udata)
{
    $uid = $udata['buyer_id'];  //获取购买id
    $money = $udata['paid_amount'];   //实付总额
    $userinfo=db('userinfo');
    $pid=$userinfo->where('uid',$udata['uid'])->value('path_id');
    $pid = rtrim($pid, ',');//去除逗号
    $prev_path = explode(',', $pid);//组成数组
    rsort($prev_path);//rsort() 函数对数值数组进行降序排序
    $prev_path = array_splice($prev_path, 1); //去除自己的ID
    $td_arr['sx_fee']=$money;//当前购买金额的手续费
    $td_arr['path']=$prev_path;
    $this->check_ae($td_arr);//调用处理团队奖励方法

}
//寻找9位上级
function getnine($uid, $number)
{
    $userinfo=db('userinfo');
    $pids = array();
    $number = $number >= 9 ? 9 : $number;
    if(!$number) {
        return $pids;
    }
    foreach($path as $k=>$v){
        $where['uid']=$v;
        $user = M('member')->where($where)->find();
        if($user['oid'] == 0){
            $pids['over']=1;
            break;
        }elseif($user['grade'] >=1){
            $pids [] = $v;
        }else{
            continue;
        }
    if(count($pids) >= $number){
        $pids['over']=1;
        break;
        }
    }
    return $pids;

}


//红包释放AE累计
function check_ae($data)
{
    $persent = array(0.01,0.02,0.03,0.04,0.05,0.06,0.07,0.08,0.09);//给的比例
    $users=array();
    $ids = $this->getnine($data['path'], 9); //每次往上找9位
    if(!$ids) {
        $this->set_money($users);
    }else{

        foreach($ids as $k => $v) {
            foreach($ids as $k => $v){
                $users[$v]['sx_fee'] = $persent[$k] * $data['sx_fee'];
            }
            if(isset($ids['over'])) {
                $this->set_money($users);
            }
        }

    }
}

function set_money($users)
{
    $users=db('userinfo');
    if($users) {

        foreach($users as $kk => $vv) {

            if($vv['sx_fee'] == 0) {
                continue;
            }
            /*获取的收益写入表中*/
            $num = $vv['sx_fee'];
            $where['uid']=$kk;
            $aa['income']=$num;

            $users->where($where)->update($aa);

            $data_1=array(
                'member_getid'=>$kk,
                'member_giveid'=>$this->uid,
                'money_produtime'=>time(),
                'money_nums'=>$num,
                'money_type'=>'2',
            );
            M('money_types')->insert($data_1);
        }
    }

}






>>>>>>> d069f81567f70b94c1ba11181772e36799a2b95a

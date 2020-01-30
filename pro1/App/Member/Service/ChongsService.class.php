<?php 

namespace Member\Service;
use Think\Model;
class ChongsService extends Model{
	
	//protected $tableName='withdrawals';
	
	public function __construct() {
		
		$this->model =  M("member_chong");
		
	}
	

    /**
     * 提现列表
     * @param array $sqlmap 条件
     * @param int $limit 条数
     * @param int $page 当前分页
     * @return array
     */
  
    public function select($sqlmap = array(), $limit = 20, $page = 1, $order = 'id desc') {
        $count = $this->model->where($sqlmap)->count();
        $pages = new \Think\Page($count, $limit);
        $page = $page ? $page : 1;
        if (isset($_GET['p'])) {
            $page = (int) $_GET['p'];
        }
        if ($limit != '') {
            $limits = (($page - 1) * $limit) . ',' . $limit;
        }
        $lists = $this->model->where($sqlmap)->order($order)->limit($limits)->select();
        return array('count' => $count, 'limit' => $limit, 'lists' => dhtmlspecialchars($lists), 'page' => $pages->show());
    }
	
	public function find($sqlmap=array()){
		return $this->model->where($sqlmap)->find();
	}


    public function save($params){
        //$admin_user=$_SESSION['gboy_admin_login']['username'];

        if(!$this->find(array('id'=>$params['id']))){
            $this->errors=L('_data_not_exist_');
            return false;
        }

        if($params['id']){

            $row=$this->find(array('id'=>$params['id']));

            if($row['status']==1){

                if($params['status']==2){
                    $finance=M('money_finance');//财务报表
                    $encryption=M('secure_encryption');//财务密钥表
                    $finance_d=$finance->where(array('uid'=>$row['uid']))->find();
                    if ($finance_d) {
                        if ($finance_d['money'] > 0 ) {
                            $keys=$encryption->where(array('uid'=>$row['uid']))->find();//查出该会员密钥表里的数据
                            $x_keys=md5(md5($finance_d['money']) . $keys['encrypt']);//安全加密
                            if ($x_keys == $keys['money_keys']) {
                                $finance->where(array('uid'=>$row['uid']))->setInc('money',$row['money']);//累加现金账户现金
                                $data_t=$finance->where(array('uid'=>$row['uid']))->getField('money');//查出现金现金
                                $keys_u=$encryption->where(array('uid'=>$row['uid']))->find();//查出该会员密钥表里的数据
                                $money_key = md5(md5($data_t) . $keys_u['encrypt']);//安全加密
                                $encryption->where(array('uid'=>$row['uid']))->setField(array('money_keys'=>$money_key));//写入密钥表
                                M('member_chong')->where(array('id'=>$row['id']))->setField(array('status'=>2,'hou_time'=>time()));//修改会员充值订单状态
                            }else{
                                $log_data=array(
                                    'uid'=>$row['uid'],
                                    'text'=>'系统执行会员充值操作时，发现此会员现金账户现金数据异常，停止给此会员执行充值操作！',
                                    'time'=>time(),
                                );
                                M('exception_log')->data($log_data)->add();
                                exit('<script>alert("此会员现金账户现金数据异常，请联系此会员了解情况！");location.href="'.U('Member/chongs/index').'"</script>');
                            }

                        }else{
                            $keys_u=$encryption->where(array('uid'=>$row['uid']))->find();//查出该会员密钥表里的数据
                            $finance->where(array('uid'=>$row['uid']))->setInc('money',$row['money']);//累加现金账户现金
                            $data_t=$finance->where(array('uid'=>$row['uid']))->getField('money');//查出现金现金
                            $money_key = md5(md5($data_t) . $keys_u['money_keys']);//安全加密
                            $encryption->where(array('uid'=>$row['uid']))->setField(array('money_keys'=>$money_key));//写入密钥表
                            M('member_chong')->where(array('id'=>$row['id']))->setField(array('status'=>2,'hou_time'=>time()));//修改会员充值订单状态
                        }

                    }else{

                        $data_x['uid']=$row['uid'];
                        $data_x['money']=$row['money'];
                        $data_x['time']=time();
                        $finance->data($data_x)->add();//累加现金账户现金
                        $data_i=$finance->where(array('uid'=>$row['uid']))->getField('money');//查出现金现金
                        $data['uid'] = $row['uid'];
                        $data['encrypt'] = random(6);//6位随机数令牌
                        $data['money_keys'] = md5(md5($data_i) . $data['encrypt']);//安全加密
                        $encryption->data($data)->add();//写入密钥表
                        M('member_chong')->where(array('id'=>$row['id']))->setField(array('status'=>2,'hou_time'=>time()));//修改会员充值订单状态
                    }

                    //执行后台操作日志
                    $ad_data['admin_id']=(defined('IN_ADMIN')) ? ADMIN_ID : 0;//后台管理员
                    $ad_data['uid']=$row['uid'];//被操作的前台会员
                    $ad_data['text']='会员充值'.$row['money'].'已通过';//操作动作
                    $ad_data['time']=time();//操作时间
                    admin_operating($ad_data);//调用后台操作日志方法



                }elseif($params['status']==3){
                    //执行后台操作日志
                    $ad_data['admin_id']=(defined('IN_ADMIN')) ? ADMIN_ID : 0;//后台管理员
                    $ad_data['uid']=$row['uid'];//被操作的前台会员
                    $ad_data['text']='会员充值'.$row['money'].'审核失败';//操作动作
                    $ad_data['time']=time();//操作时间
                    admin_operating($ad_data);//调用后台操作日志方法
                }
            }else{
                $this->errors=L('_data_not_exist_');
                return false;
            }




            $this->model->save($params);
        }

        return true;
    }



    /**
     * [delete 删除分类]
     * @param  [int] $params [分类id]
     * @return [boolean]     [返回删除结果]
     */
    public function del($sqlmap = array()) {
        $result = $this->model->where($sqlmap)->delete();
        return true;
    }

        /*
            //执行个人钱包操作动作
            $grqb_data['zh_types']='shop_integral';//购物积分字段
            $grqb_data['key_types']='shop_integral_key';//购物积分加密字段
            $grqb_data['uid']=$pid;//获取积分用户ID
            $grqb_data['types']='2';//累加积分
            $grqb_data['number']=$shop_jf;//本次产生的积分数量
            $grqb_data['text']='推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
            $grqb_data['is_mx']=0;//是否添加明细，0不添加，1添加
            $grqb_data['member_giveid'] = $id;//提供会员id
            $grqb_data['money_type'] = 11;//明细类型
            $this->personal_wallet($grqb_data);//调用个人钱包方法
            */

            //累加个人钱包
            public function personal_wallet($qb_data){
                    $qb_types=$qb_data['zh_types'];//账户类型
                    $key_types=$qb_data['key_types'];//加密类型
                    $id=$qb_data['uid'];//会员id
                    $types=$qb_data['types'];//1为减去积分，2为累加积分
                    $number=$qb_data['number'];//交易数量
                    //添加明细参数
                    $is_mx = $qb_data['is_mx'];//是否添加明细，0不添加，1添加
                    $member_giveid = $qb_data['member_giveid'];//提供会员id
                    $money_type = $qb_data['money_type'];//明细类型

                    $money_finance=M('money_finance');//个人财务表
                    $cw_encryption=M('secure_encryption');//个人财务加密表
                    $money_types=M('money_types');//交易详情表
                    $keys=$cw_encryption->where(array('uid'=>$id))->find();
                    $cw=$money_finance->where(array('uid'=>$id))->find();
                    if ($cw[$qb_types] > 0){
                        $x_keys=md5(md5($cw[$qb_types]) . $keys['encrypt']);//安全加密
                        if ($keys[$key_types]==$x_keys){
                            if ($types==1){
                                $money_finance->where(array('uid'=>$id))->setDec($qb_types,$number);//减去积分
                            }elseif ($types==2){
                                $money_finance->where(array('uid'=>$id))->setInc($qb_types,$number);//累加积分
                            }
                            $member_bii=$money_finance->where(array('uid'=>$id))->getField($qb_types);
                            $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
                            $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);

                            if($is_mx == 1){   //开始添加明细
                                $bdata['member_getid'] = $id;//获得会员id
                                $bdata['member_giveid'] = $member_giveid;//提供会员id
                                $bdata['money_produtime'] = time();
                                $bdata['money_nums'] = $number;//当次产生金额
                                $bdata['money_type'] = $money_type;//代理等级类型
                                $money_types->add($bdata);//写入收益详情表
                            }
                        }else{
                            $log_data=array(
                                'uid'=>$id,
                                'text'=>$qb_data['text'],
                                'time'=>time(),
                            );
                            M('exception_log')->data($log_data)->add();
                        }
                    }else{
                        if ($types==1) {
                            $money_finance->where(array('uid'=>$id))->setDec($qb_types,$number);//减去子积分
                        }elseif ($types==2) {
                            $money_finance->where(array('uid'=>$id))->setInc($qb_types,$number);//累加子积分
                        }
                        $member_bii=$money_finance->where(array('uid'=>$id))->getField($qb_types);
                        $datat= md5(md5($member_bii) . $keys['encrypt']);//安全加密
                        $cw_encryption->where(array('uid'=>$id))->setField($key_types,$datat);
                        if($is_mx == 1){   //开始添加明细
                            $bdata['member_getid'] = $id;//获得会员id
                            $bdata['member_giveid'] = $member_giveid;//提供会员id
                            $bdata['money_produtime'] = time();
                            $bdata['money_nums'] = $number;//当次产生金额
                            $bdata['money_type'] = $money_type;//代理等级类型
                            $money_types->add($bdata);//写入收益详情表
                        }
                    }
            }





}

?>
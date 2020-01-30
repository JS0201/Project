<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\service;
use think\Model;
use app\member\model\ExceptionLog;
use app\member\model\Encryption;
use app\member\model\MemberLog;
class MoneyFinance extends Model
{
    public function initialize()
    {
        $this->Model = model('member/Finance');
    }
    public function getBi($uid, $value = 'member_bi')
    {
        return $this->Model->getValue(array('uid' => $uid), $value);
    }
    public function getByUid($id)
    {
        if(!$id) return false;
        return $this->Model->getByUid($id);
    }

    /**调整用户个人积分
     * @param $uid 用户ID
     * @param $number 更改数值
     * @param $type 积分类型
     * @param $settype 加或减 true为加 false为减
     * @param $admin_id 后台用户ID
     * @param $settype 加钱或减钱
     * @param $transaction_type 交易类型 1支付 2直推收益  3团队收益 4押金退还
     * @param $fromid 收益来源用户
     * @param $signid 收益来源用户
     */
    public function setFinance($uid, $number, $type, $msg = '',$admin_id = '', $settype = true, $transaction_type, $fromid, $signid = 0)
    {
        $encryptionModel = new Encryption();
        $finance = $this->Model->getByUid($uid);
        $encryp = $encryptionModel->getOne("uid = {$uid}");
        $key = md5(md5(floatval($finance[$type])) . $encryp['encrypt']);
        if($encryp[$type] > 0 && $key !== $encryp[$type]) { //余额数据异常
            $logModel = new ExceptionLog();
            $log_data=array(
                'uid'=>$uid,
                'text'=>lang('exception_log'),
                'time'=>time(),
            );
            $logModel->add($log_data);
            $this->errors = lang('money_abnormal');
            return false;
        }
        if(!$settype && $finance[$type] < $number) { //余额不足
            $this->errors = lang('money_not_enough');
            $this->not_enough = true;
            return false;
        }
        $money = $settype ? $finance[$type] + $number : $finance[$type] - $number;
        $money_re = $this->Model->updated("uid = {$uid}", array("{$type}" => $money, "time" => time())); //修改金额
        $log_model = new MemberLog();
        $log_re = $log_model->add(array(
            'mid' => $uid,
            'value' => $number,
            'fromid' => $fromid,
            'msg'   => $msg,
            'dateline' => time(),
            'type'     => $type,
            'style'    => $transaction_type,
            'admin_id' => $admin_id,
            'money_detail' => json_encode(array("{$type}" => $money)),
            'signid'    => $signid
        ));                                                                  //用户交易日志
        $encryt_re = $encryptionModel->updated("uid = {$uid}", array(
            "{$type}" => md5(md5($money) . $encryp['encrypt'])
        ));                                                               //加密余额
        if($money_re === false || !$log_re || $encryt_re === false) {
            $this->errors = lang('_operation_fail_va');
            return false;
        }
        return true;
    }
    //积分互转
    public function transaction($uid, $request)
    {

        if(!$request['mobile'] || !is_mobile($request['mobile'])) {
            $this->errors = lang('userphone_error');
            return false;
        }
        if(intval($request['money']) != $request['money']) {
            $this->errors = lang('金额只能为整数');
            return false;
        }
        $userid = model('member/Member')->getValue("mobile = {$request['mobile']} and id != {$uid}",'id'); //收款方
        if(!$userid) {
            $this->errors = lang('userphone_error');
            return false;
        }
        $re_out = $this->setFinance($uid, $request['money'], 'money', $msg = '转账支出',$admin_id = '', $settype = false, 5, $userid); //支出
        if(!$re_out) {
            return false;
        }
        return $this->setFinance($userid, $request['money'], 'money', $msg = '转账收入',$admin_id = '', $settype = true, 6, $uid); //支出
    }
}
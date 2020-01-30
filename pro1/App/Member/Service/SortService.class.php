<?php

namespace Member\Service;

use Think\Model;

class SortService extends Model {

    public function __construct() {

        //$this->model = D('Member_address');
    }

	/*通过用户确定复购按钮来进行复购插入排位操作*/
	public function confires($info_sort)
	{


		$sort_pwid = explode(',', $info_sort);
		$joins = M('member_joinsorts');

		/*当状态2执行复购*/
		if ($sort_pwid[1] == 2) {
			/*去排位表里面找到最后一个人的层数*/
			$sortid_max = $joins->order('member_sortid DESC')->limit(1)->getfield('member_layer');


			/*找到自己冻结时的层数*/
			$mine_sortid = $joins->where(array('member_sortid' => $sort_pwid[0]))->getField('member_feznumber');
			$mine_sortid = $mine_sortid + 1;
			$mine_sortid = 12;
			if ($mine_sortid - $sortid_max >= 0) {

				/*判断对应层数需要复购多少个位置*/
				$nesfugou = $mine_sortid - 10;
				$nes_per_num = 1 << $nesfugou;

				/*找到该层现有人数*/
				$member_cengshu_pers = $joins->where(array('member_layer' => $mine_sortid))->count(1);

				/*算出填满该层总人数*/
				$all_tm = 1 << $mine_sortid;
				/*当总人数减现有人数大于等于所需复购人数成立*/
				if ($all_tm - $member_cengshu_pers >= $nes_per_num) {
					/*执行注册会员*/
					$knum = $nes_per_num;

					for ($i = 1; $i <= $knum; $i++) {
						/*复购时的userid为0*/
						$userid = 0;
						$this->insetpoints($userid);
					}

					/*插入到数据库*/
					$allnums = $joins->where(array('member_sortid' => $sort_pwid[0]))->getField('member_buyallnum');
					/*对应上一次累计套数*/
					$joins->where(array('member_sortid' => $sort_pwid['0']))->setField('member_addsh', $allnums);
					/*实际购买套数*/
					$joins->where(array('member_sortid' => $sort_pwid['0']))->setInc('member_buyallnum', $nes_per_num);

					/*复购成功清零冻结资金*/
					$joins->where(array('member_sortid' => $sort_pwid[0]))->setField('member_freezingmoney', 0);
				}
			} else {
				echo '亲,您已经错过复购机会了哦,请重新开始';
				return false;
			}
		}
		/*当状态3执行放弃*/
		if ($sort_pwid[1] == 3) {
			/*将该排位会员的总复购套数改为负数*/
			$dodec = $joins->where(array('member_sortid' => $sort_pwid[0]))->setField('member_buyallnum', -10);
			/*把冻结的钱加到会员钱包里面去*/
			$userid = $joins->where(array('member_sortid' => $sort_pwid[0]))->find();
			/*加到用户钱包里面去*/
			$users = M('member');
			$users->where(array('id' => $userid['member_id']))->setInc('member_money', $userid['member_freezingmoney']);
			/*钱给客户之后清空冻结资金池*/
			$joins->where(array('member_sortid' => $sort_pwid[0]))->setField('member_freezingmoney', 0);
		}
	}


	//注册后
	public function insetpoints($userid)
	{
		/*接收买多少套,然后插入对应多少个会员到排位表里面去*/
		$userid = $userid['userid'];
		$userid = 5;
		if ($userid == '') {
			$userid = 0;
		}


		/*程序判断排位位置*/
		$sorts = M('member_sorts');
		$joins = M('member_joinsorts');
		$u = M('member');

		/*判断是否参与过排位商城购买*/
		$paiwei_zhi = $u->where(array('id' => $userid))->getField('member_paiwei');
		if ($paiwei_zhi == 0) {
			/*是的话 改变用户表字段改成参与过排位商城购买*/
			$u->where(array('id' => $userid))->setField('member_paiwei', 1);
		}

		/*将表内容改变*/
		$sorts_det = $sorts->where(array('id' => 1))->find();

		/*判断是否满足加2条件*/
		if ($sorts_det['member_bigposition'] == 1) {
			$b=fopen('dd.txt','w+');
			fwrite($b,$sorts_det['member_bigposition']);
			/*左边位置切割*/
			$sorts_det_position = explode(',', $sorts_det['member_leftposition']);

			$huoqu = $sorts_det['member_leftposition'];

			if ($sorts_det_position[1] == 2) {
				$det_position = $sorts_det_position[0] + 2;
				/*将左边+2进行定位到排位表里面去*/
				$sorts_det_new = $det_position . ',' . '1';
				$sorts_id = 2;
				$sorts->where(array('id' => 1))->setField(array('member_leftposition' => $sorts_det_new, 'member_bigposition' => $sorts_id));
				/*排位表*/
				$join['member_id'] = $userid;
				/*path路径*/
				$shangjpth = $joins->where(array('member_sortid' => $sorts_det_position[0]))->getField('member_pathid');
				$layers = $joins->where(array('member_sortid' => $sorts_det_position[0]))->getField('member_layer');
				$allpathid = $shangjpth . $sorts_det_position[0] . ',';
				$join['member_pathid'] = $allpathid;
				/*上级排位id*/
				$join['member_pid'] = $sorts_det_position[0];
				$join['member_layer'] = $layers + 1;
				$join['member_jointime'] = date('Y-m-d');
				$joins->add($join);
			} else {

				/*修改为右边位置*/
				$sorts_det_new = $sorts_det_position[0] . ',' . '2';
				$sorts_id = 2;
				$sorts->where(array('id' => 1))->setField(array('member_leftposition' => $sorts_det_new, 'member_bigposition' => $sorts_id));
				/*排位表*/

				$join['member_id'] = $userid;

				/*path路径*/
				$shangjpth = $joins->where(array('member_sortid' => $sorts_det_position[0]))->getField('member_pathid');
				$layers = $joins->where(array('member_sortid' => $sorts_det_position[0]))->getField('member_layer');

				$allpathid = $shangjpth . $sorts_det_position[0] . ',';
				$join['member_pathid'] = $allpathid;


				/*上级排位id*/
				$join['member_pid'] = $sorts_det_position[0];
				$join['member_layer'] = $layers + 1;
				$join['member_jointime'] = date('Y-m-d');
				$joins->add($join);
			}
		}

		if ($sorts_det['member_bigposition'] == 2) {
			$b=fopen('aa.txt','w+');
			fwrite($b,$sorts_det['member_bigposition']);
			/*右边位置切割*/
			$sorts_det_right = explode(',', $sorts_det['member_rightposition']);
			$sorts_det = $sorts->where(array('id' => 1))->find();
			$huoqu = $sorts_det['member_rightposition'];

			if ($sorts_det_right[1] == 1) {
				$sorts_right = $sorts_det_right[0] + 2;
				/*奖左边加2*/
				$sorts_right_new = $sorts_right . ',' . '2';
				$sorts_id = 1;
				$sorts->where(array('id' => 1))->setField(array('member_rightposition' => $sorts_right_new, 'member_bigposition' => $sorts_id));
				/*排位表*/
				$join['member_id'] = $userid;
				/*path路径*/
				$shangjpth = $joins->where(array('member_sortid' => $sorts_det_right[0]))->getField('member_pathid');
				$layers = $joins->where(array('member_sortid' => $sorts_det_right[0]))->getField('member_layer');
				$allpathid = $shangjpth . $sorts_det_right[0] . ',';
				$join['member_pathid'] = $allpathid;
				/*上级排位id*/
				$join['member_pid'] = $sorts_det_right[0];
				$join['member_layer'] = $layers + 1;
				$join['member_jointime'] = date('Y-m-d');
				$joins->add($join);
			} else {
				/*只更新右边参数*/
				$sorts_right_new = $sorts_det_right[0] . ',' . '1';
				$sorts_id = 1;
				$sorts->where(array('id' => 1))->setField(array('member_rightposition' => $sorts_right_new, 'member_bigposition' => $sorts_id));
				/*排位表*/
				$join['member_id'] = $userid;
				/*path路径*/
				$shangjpth = $joins->where(array('member_sortid' => $sorts_det_right[0]))->getField('member_pathid');
				$layers = $joins->where(array('member_sortid' => $sorts_det_right[0]))->getField('member_layer');
				$allpathid = $shangjpth . $sorts_det_right[0] . ',';
				$join['member_pathid'] = $allpathid;
				/*上级排位id*/
				$join['member_pid'] = $sorts_det_right[0];
				$join['member_layer'] = $layers + 1;
				$join['member_jointime'] = date('Y-m-d');
				$joins->add($join);
			}
		}
		/*给自己加上总共购买套数*/
		$u->where(array('id' => $userid))->setInc('member_buynums', 1);
	}
}

?>
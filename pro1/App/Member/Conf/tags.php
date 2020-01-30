<?php

return array(
	'after_register' => array('Member\Addons\HookAddons'),//注册处理path路径
	'check_lock' => array('Member\Addons\HookAddons'),//处理冻结账户
	'admin_level'=> array('Member\Addons\HookAddons'),//双轨排位
	
	'user_path' => array('Member\Addons\HookAddons'),//循环path路径
	'member_upgrage'=> array('Member\Addons\HookAddons'),//会员下单，升级个人等级
	'dynamic_income'=> array('Member\Addons\HookAddons'),//计算直推收益
	'dynamic_share'=> array('Member\Addons\HookAddons'),//计算直推分享收益

	'dynamic_incometolockwallet'=> array('Member\Addons\HookAddons'),//计算直推分享收益
	'reward_phone' => array('Member\Addons\HookAddons'),   //固定手机获取收益'
	'upgrage_tuandui'=> array('Member\Addons\HookAddons'),//团队升级
	'tuanduiprice'=> array('Member\Addons\HookAddons'),//团队奖
    'upgrade'=>array('Member\Addons\HookAddons'),   //直推收益
);


?>
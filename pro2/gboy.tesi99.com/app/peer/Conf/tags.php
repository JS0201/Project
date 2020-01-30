<?php 
	
return array(


	'after_register' => array('Member\Addons\HookAddons'),
	'before_register' => array('Member\Addons\HookAddons'),
	'logout' => array('Member\Addons\HookAddons'),
	'givemoneys' => array('Member\Addons\HookAddons'),
	'domore' => array('Member\Addons\HookAddons'),
	'report' => array('Member\Addons\HookAddons'),
	'user_path' => array('Member\Addons\HookAddons'),
	'team' => array('Member\Addons\HookAddons'),
	'check_lock' => array('Member\Addons\HookAddons'),
	'digui' => array('Member\Addons\HookAddons'),
	'team_performance'=> array('Member\Addons\HookAddons'),
	'zeng'=> array('Member\Addons\HookAddons'),
	'cha'=> array('Member\Addons\HookAddons'),
	'cha_digui'=> array('Member\Addons\HookAddons'),
	'mother_currency'=> array('Member\Addons\HookAddons'),//会员下单，获取母币数量
	'shang_dai'=> array('Member\Addons\HookAddons'),//一代二代三代
	'admin_level'=> array('Member\Addons\HookAddons'),//member_sorts
);
	

?>
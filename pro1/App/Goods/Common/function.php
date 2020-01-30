<?php 

/**
 * 会员权限
 */
function rule_auth_list() {
	
	$arr=array(
		 '打折'=>'goods:edit',
		 '赠送'=>'goods:zs',
		 '退菜'=>'goods:out',
		 '结账'=>'goods:order',
		 '报表'=>'goods:count',
	);
	
	return $arr;
}




?>
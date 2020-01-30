<?php
namespace Home\Behavior;
class SiteBehavior extends \Think\Behavior{
	
	
	public function run(&$param){
		
		$site_status=C('site_isclosed');
		$site_closedreason=C('site_closedreason');
		
		if($site_status!=1){
			
			exit($site_closedreason);
			
		}
			
	}
	
	
	
}
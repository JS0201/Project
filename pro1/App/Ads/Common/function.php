<?php 

/**
 * 添加附件、水印管理
 * @param string $module  模型
 * @param string $url 附件地址
 * @return null
 */
function att_mark($module,$url,$catid='0') {
	if(!$catid) $catid=0;
		
	if($arow=M('attachment')->where(array('module'=>$module,'url'=>$url))->field('aid,is_mark')->find()){
				
		if(empty($arow['is_mark'])){
		
			thumb_mark($url,false); //水印
		}
		
	}else{
		
		$conf=array(
			'module'=>$module,
			'url'=>$url,
			'catid'=>$catid
		);
		attachment($conf);
 
		thumb_mark($url,false); //水印	
	}
	
	
	if(C('watermark_switch')==1){
		
		M('attachment')->where(array('module'=>$module,'url'=>$url))->data(array('is_mark'=>1))->save();
	}
}




?>
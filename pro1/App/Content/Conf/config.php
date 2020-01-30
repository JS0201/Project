<?php

return array(
	//'配置项'=>'配置值'

	'TAGLIB_LOAD'=>true,
	'TAGLIB_PRE_LOAD' => 'Content\TagLib\Gboy',// 预先加载标签 
	'TAGLIB_BUILD_IN' => 'cx,Content\TagLib\Gboy',// 定义成内置标签
	
	
	'TMPL_FILE_DEPR'=>'_', // 控制器和方法分隔符
);
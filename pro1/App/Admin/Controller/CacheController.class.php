<?php 
namespace Admin\Controller;

use Common\Controller\AdminController;

class CacheController extends AdminController {

 
	function clear(){
		//删除html目录
		deldir('data/cache',true);
		//$this->success('缓存清除完毕','null');
		showmessage('缓存清除完毕');
	}

}

?>
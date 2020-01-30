<?php
namespace Attachment\Controller;
use Think\Controller;
class IndexController extends Controller {
	
	public function _initialize() {
		
		
		$this->service = D('Attachment/Attachment','Service');
	
	}

	
	
    public function index(){
		
	
    }
	
 
	
	/* 编辑器图片上传 */
	public function editor() {
		
		$json_result=array();
		$result=$this->service->upload($_FILES['editor']);
		
		//$result['url']=$result['file_url'];
		$json_result['url']=$result;
		
		showmessage('上传成功', '', 1, $json_result, 'json');
	}

	
	
}
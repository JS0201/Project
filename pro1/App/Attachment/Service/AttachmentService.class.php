<?php 

namespace Attachment\Service;
use Think\Model;
class AttachmentService extends Model{
	
	
	public function __construct() {
	
		
		  
		
	}
	

    /**
     * 附件上传接口
     * @param 变量名 $field
     * @param 密钥 $code
     * @return mixed
     */
	 
	public function upload($file,$dirname='',$subname='',$filename=array('uniqid',''),$saveext='') {
		
		$result=uploads($file,$dirname,$subname,$filename,$saveext);
		
		return $result['file_url'];
		
	}
	
}


?>
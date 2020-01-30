<?php
namespace app\admin\service;

use think\Model;
use think\Db;

class Site extends Model
{
	

	
	public function __construct() {
		
		
		$this->model = model('admin/Site');
		
		
	}
	
	/**
     * 网站配置分组
     * @return array
     */
	
 	public function group($group_key=[]) {
		
		$array=[];
		
		$array[0]='其它设置';
		$array[1]='站点信息';
		$array[2]='基本设置';
		$array[3]='购物设置';
		$array[4]='附件设置';
		$array[5]='退货设置';
		$array[6]='会员设置';
		$array[7]='水印设置';
		$array[8]='快递设置';
		$array[9]='微信配置';
		$array[10]='安全配置';
		$array[11]='性能配置';
		$array[12]='缩略图设置';
        $array[13]='交易区设置';

		
		if($group_key){
		    $new_group=[];
		   foreach($group_key as $key=>$val){
		       $new_group[$val]=$array[$val];
		   }
		   
		   $array= $new_group;
		}
		
		return $array;
	}
	
	
	/**
     * 网站配置信息
     * @param int $group_id 分组ID	 
     * @param string $order 排序	 
     * @return array
     */
 	public function setting($group_id,$order='sort asc') {
		
		return $this->model->where(array('group_id'=>$group_id))->order($order)->select()->toArray();
		
		
	}	
	
	
	
	
	/**
	 * 编辑
	 * @param array 	$params 内容
	 * @param string 	$filename 文件名
	 * @return [boolean]
	 */
	public function submit($params,$filename,$name = ''){
		
		if(empty($filename)){
		
			$this->errors='文件名不能为空';
			return false;
		}
        // 生成log记录
        $name = $name ? $name : 'default';
        $action = (($name == 'weixin') || $name == 'weixin_reply') ? 4 : 3;
        AdminLog($action, $_POST, lang("admin_log_site_$name"));

		foreach ($params as $key => $value) {
			if (is_array($value)) $value = serialize($value);
			$this->model->where(array('key' => $key))->data(array('value' => $value))->update();
			
		}
		$this->conf($filename);
		return TRUE;
	
    }	
	
	
	/**
	 * 生成配置文件
	 * @param string 	$filename 文件名
	 * @param array 	$params 内容
	 * @return [boolean]
	 */
	public function conf($filename,$params=[]){
		
		if(empty($filename)){
		
			$this->errors='文件名不能为空';
			return false;
		}
		
		if(empty($params)){
			$params=$this->model->column('key,value');
		}
		
	
		
		$settingstr="<?php \n return [\n";
		foreach($params as $k=>$v){
			if (is_array($v)) $v = serialize($v);
			$settingstr.= "\t'".$k."'=>'".$v."',\n";
		}
		$settingstr.="];\n?>\n";
		
		$file=APP_PATH.'extra/'.$filename;
		if(!file_exists($file)) fopen($file,'w+');
		$r=file_put_contents($file,$settingstr); 
		if(!$r){
			
			$this->errors='没有写入权限';
			return false;
		}	
    }	
	
	

	
	
}

?>
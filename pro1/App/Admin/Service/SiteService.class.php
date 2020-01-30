<?php 

namespace Admin\Service;
use Think\Model;
class SiteService extends Model{
	
	protected $tableName = 'setting'; 
	
	
	public function __construct() {
		
		$this->model = D('setting');
		
	}
	
	/**
     * 网站配置分组
     * @return array
     */
 	public function group() {
		
		$array=array();
		
		$array[1]='站点信息';
		$array[2]='基本设置';
		$array[3]='静态设置';
		$array[4]='附件设置';
		$array[5]='性能设置';
		$array[6]='会员设置';
		$array[7]='水印设置';
		$array[0]='其它设置';
		
		return $array;
	}
	
	/**
     * 网站配置信息
     * @param int $group_id 分组ID	 
     * @param string $order 排序	 
     * @return array
     */
 	public function setting($group_id,$order='sort asc') {
		
		return $this->model->where(array('group_id'=>$group_id))->order($order)->select();
		
		
	}	
	
	
	
	/**
	 * 添加
	 * @param array 	$params 内容
	 * @return [boolean]
	 */
	public function add($params){
		
		if(!is_array($params) || empty($params)){
			
			 $this->errors='非法操作';
			 return false;
		}
		
		if($params['var_title']==''){
			
			 $this->errors='请输入变量标题';
			 return false;
		}			
		
		if($params['var_name']==''){
			
			 $this->errors='请输入变量名称';
			 return false;
		}	
		
		if(!preg_match("/^[a-z]\w+$/", $params['var_name'])){
			
			 $this->errors='变量名称只能以字母开头、数字、下划线组成';
			 return false;
		}
		
		

		
		$key=C($params['var_name']);
		
		//查询配置文件是否有重复值
		if(isset($key)==true){
			
			$this->errors='变量名称重复，请重新输入';
			return false;
		}
		$data['group_id']=0;
		$data['title']=$params['var_title'];
		$data['type']=$params['var_type'];
		$data['key']=$params['var_name'];
		$data['value']=$params['var_value'];
		$data['description']=$params['var_description'];
		
		
		if(!$this->model->data($data)->add()){
			
			$this->errors='变量添加失败，请重新输入';
			return false;
		}
		
		return true;
    }		
	
	
 
	/**
	 * 编辑
	 * @param array 	$params 内容
	 * @return [boolean]
	 */
	public function update($params){
		
		//网站访问模式处理
	
		$conf_url='App/Home/Conf/html.php';
		
		$html_conf_url='App/Common/Conf/html_conf.php';
		
		$conf_array=require_once($conf_url);
		
		
		if($params['html_dir']){
			
			$html_dir=trim($params['html_dir'],'/').'/';
		}else{
			
			$html_dir='html/';
		}
		
                if(!$params['reg_pass_complex']){
                    $params['reg_pass_complex']='';
                }		
	
			
		
		if($params['run_mode']==2){
			//纯静态
			
			$conf_array['HTML_CACHE_ON']=true;
			
		}else{
			
			//动态或伪静态
			$conf_array['HTML_CACHE_ON']=false;
			
			//删除html文件夹
			deldir('html/');
			
			//删除index.html首页
			@unlink('index.html');
		}
		
		
		$new_conf="<?php\n return ".var_export($conf_array,true).";\n?>";
		
		file_put_contents($conf_url,$new_conf);	


		//生成静态配置文件
		
		$html_conf="<?php\n define('HTML_PATH','".$html_dir."');";
		file_put_contents($html_conf_url,$html_conf);		
	
	
	
	
	
		foreach ($params as $key => $value) {
		
			if (is_array($value)) $value = serialize($value);
			
			
			$this->model->where(array('key' => $key))->data(array('value' => $value))->save();
			
		}
		
		if(!isset($params['watermark_logo'])){
			
			$params['watermark_logo']=$this->model->where(array('key'=>'watermark_logo'))->getfield('value');
			
		}		
		
		if(!isset($params['site_logo'])){
			
			$params['site_logo']=$this->model->where(array('key'=>'site_logo'))->getfield('value');
			
		}
		
		$this->conf($params);
		return TRUE;
	
    }	
	
	
	/**
	 * 生成配置文件
	 * @param array 	$params 内容
	 * @return [boolean]
	 */
	public function conf($params){
		
		$params=$this->model->getfield('key,value',true);
		
		$settingstr="<?php \n return array(\n";
		foreach($params as $k=>$v){
			if (is_array($v)) $v = serialize($v);
			$settingstr.= "\t'".$k."'=>'".$v."',\n";
		}
		$settingstr.=");\n?>\n";
		
		
		$file='./App/Common/Conf/config_cache.php';
		$r=file_put_contents($file,$settingstr); 
		if(!$r){
			
			$this->errors='没有写入权限';
			return false;
		}	
    }	
	
}

?>
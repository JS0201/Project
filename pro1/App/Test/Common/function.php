<?php 

/**
 * 站点URL
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string|boolean $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 * @return string
 */
function UU($url='',$vars='',$suffix=true,$domain=false) {
	
	$run_mode=C('run_mode');
	
	
	if(empty($run_mode)){
		
		$url=U($url,$vars,$suffix,$domain);
		
	}elseif($run_mode==1){
		//伪静态
		
	}else{
		
		 
	
		if($vars['id'] && $vars['catid']){
			
			$url=get_url_show($vars['id']).C('HTML_FILE_SUFFIX');
			
		}elseif($vars['catid']){
			
			$url=rtrim(get_url_list($vars['catid']),'index');
			
			if(substr_count('list_')!==false){
				
				$arr=explode('/',$url);
				unset($arr[count($arr)-1]);
				$url=implode('/',$arr);
				
			}
	
			
			
		}
		
	}
	
	return $url;
}



/**
 * 前台模板路径
 * @param string $tplname  模板路径
 * @return string
 */
function tpl($tplname){
	
	$tplarr=explode('.',$tplname);
	if(empty($tplname)){
		exit('模板不能为空，请选择模板！');
	}else{
		if(!file_exists(APP_PATH.'Home/View/'.C('DEFAULT_THEME').'/'.$tplname)){
		echo $tplname.'模板不存在！';
		exit();
		}
	}

	
	return '/'.$tplarr[0];
	
}


/**
 * TODO 基础分页的相同代码封装，使前台的代码更少
 * @param $count 要分页的总记录数
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getpage($count, $pagesize = 10) {
    $p = new Think\Page($count, $pagesize);
    $p->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
    $p->setConfig('prev', '上一页');
    $p->setConfig('next', '下一页');
    $p->setConfig('last', '末页');
    $p->setConfig('first', '首页');
    $p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
    $p->lastSuffix = false;//最后一页不显示为总页数
    return $p;
}



/**
 * 生成静态列表页
 * @param string $id  栏目ID
 * @return string
 */
function create_list($id){
	$t=M('category');

	
	if(!empty($_GET['p'])){
	
		return create_page($id,$_GET['p']);
	
	}	
	
	
	$path_id=$t->where(array('id'=>$id,'display'=>1))->getfield('path_id');
	
	$path_id=explode(',',$path_id);
	

	$html_url=array();
	foreach($path_id as $k=>$v){
		if($v){
			
			$row=$t->where(array('id'=>$v,'display'=>1))->field('id,path_id,name,dir_name')->find();
			
			if($row['dir_name']){
				
				$html_url[]=trim($row['dir_name']);
				
			}else{
				
				$html_url[]=trim(GetPinyin($row['name']));
				
			}
		}
		
	}
	
	$url=implode('/',$html_url).'/index';
	
	
	
	return $url;
	
}



/**
 * 生成静态详细页
 * @param string $id  内容ID
 * @return string
 */
function create_show($id){
	$t=M('content');
	
	
	$info=$t->where(array('id'=>$id,'display'=>1))->field('id,path_id,add_time')->find();
	
	
	$path_id=explode(',',$info['path_id']);
	
	
	
	
	$html_url=array();
	foreach($path_id as $k=>$v){
		if($v){
			
			$row=M('category')->where(array('id'=>$v,'display'=>1))->field('id,path_id,name,dir_name')->find();
			
			if($row['dir_name']){
				
				$html_url[]=trim($row['dir_name']);
				
			}else{
				
				$html_url[]=trim(GetPinyin($row['name']));
				
			}
		}
		
	}
	
	//$url=implode('/',$html_url).'/'.date('Y/md',$info['add_time']).'/'.$info['id'];
	$url=implode('/',$html_url).'/'.date('Y',$info['add_time']).'/'.date('YmdHis',$info['add_time']).$info['id'];
	
	
	return $url;
	
}


/**
 * 生成列表分页
 * @param string $id  id
 * @param string $p  页数
 * @return string
 */

function create_page($id,$p=''){

	$t=M('category');
	

 
	$p=$p?$p:1;
	
	$path_id=$t->where(array('id'=>$id,'display'=>1))->getfield('path_id');
	
	
	$path_id=explode(',',$path_id);
	
	$html_url='';
	foreach($path_id as $k=>$v){
		if($v){
			
			$row=$t->where(array('id'=>$v,'display'=>1))->field('id,path_id,name,dir_name,modelid')->find();
			
			if($row['modelid']!=1){
			
				if($row['dir_name']){
				
					$html_url[]=trim($row['dir_name']);
					
				}else{
					
					$html_url[]=trim(GetPinyin($row['name']));
					
				}
				
			}else{
				
				continue;
			}
			
		}
		
	}

	$url=implode('/',$html_url).'/list_'.$id.'_'.$p;

	
	
	return $url;
}


/**
 * 获取静态列表地址
 * @param string $id  栏目ID
 * @return string
 */
function get_url_list($id){
	
	$url=create_list($id);

	$url=__ROOT__.'/'.HTML_PATH.$url;	
	
	return $url;
	
}



/**
 * 获取静态详细地址
 * @param string $id  栏目ID
 * @return string
 */
function get_url_show($id){
	
	$url=create_show($id);
	
	

	$url=__ROOT__.'/'.HTML_PATH.$url;	
	
	return $url;
	
}

?>
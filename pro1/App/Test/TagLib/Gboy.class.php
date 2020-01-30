<?php
namespace Home\TagLib;
use Think\Template\TagLib;
class Gboy extends TagLib {
	protected $tags = array(
	
		'sql' => array('attr' => 'db,where ,field, order , limit ,result', 'level' => 3, 'close' => 1),
		'content' => array('attr' => 'type,db,id,pid,path,field,order,limit,where,action,page,pagesize,echo,result,item,key', 'level' => 3, 'close' => 1), 
		
		'page' => array('attr' => 'db,id,action,field,function,type,echo', 'close' => 0), 
	);
	
	
	
	public function _sql($attr, $content) {
		$db = $attr['db'] ? $attr['db'] : 'content';
		$order = $attr['order'];
		$limit = $attr['limit'];
		$where = $attr['where'];
		$field = $attr['field']; 
		$result = $attr['return'] ? $attr['return'] : 'result'; 
		$str = "<?php ";
		$str.= "$" . $result . ' = M("' . $db . '")->where("' . $where . '")->order("' . $order . '")->limit("' . $limit . '")->field("' . $field . '")->select(); ';
		$str.= '?>';
		$str.= $content;
		return $str;
	}
	
	
	public function _content($attr, $content) {
	
		$echo = trim($attr['echo'])!=''?$attr['echo']:'1';
		$limit = trim($attr['limit']);
		$where = trim($attr['where']);
		$action = trim($attr['action']);
		$pid = trim($attr['pid']);
		$path = trim($attr['path']);
		$field = trim($attr['field']);
		$type = trim($attr['type']);
		$result = trim($attr['return']) ? $attr['return'] : 'result';
		$item = trim($attr['item']) ? $attr['item'] : 'v';
		$key = trim($attr['key']) ? $attr['key'] : 'key';
		$str = '';
		$map = '';
		
		if ($action == 'list') {
			
			/***********************内容列表**********************/
			
			$pagesize = trim($attr['pagesize'])?$attr['pagesize']:0;
			$page = trim($attr['page']);
			
			$catid=(int)$_GET['catid'];
			
			$db = trim($attr['db']) ? $attr['db'] : 'content';
			$order = trim($attr['order']) ? $attr['order'] : 'sort desc,id desc';
			$str = "<?php ";
			$str.= ' $map = "";';
			$str.= ' $map["display"] = 1 ;';
			
			$map['display']=1;
			
			if($pid=='' && $path==''){
				
				$str.= ' $map["path_id"] = array("like","%,' . $catid . ',%") ;';
				
				$map['path_id']=array('like','%,'.$catid.',%');
				
			}elseif($pid=='0'){
				
				
				
			}else{
				
				if ($pid) {
					$str.= ' $map["category_id"] = ' . $pid . ' ;';
					
					$map['category_id']=array('like','%,'.$pid.',%');
				}
				
				
				if ($path) {
					$str.= ' $map["path_id"] = array("like","%,' . $path . ',%") ;';
					
					$map['path_id']=array('like','%,'.$path.',%');
				}	
				
			}
			

			
			
			if (!empty($where)) {
				$str.= ' $map["_string"] = "(' . $where . ')" ;';
				
				$map['_string']='(' . $where . ')';
			}
			
			if($page){
				
				 $count=M($db)->where($map)->order($order)->limit($limit)->field($field)->count();
				 
				 $p=getpage($count,$pagesize);
				 
				 
				 $str.= '$p = getpage('.$count.','.$pagesize.');';
				
				 $limit=$p->firstRow.' , '.$p->listRows;
				 
			}
			
			
			$str.= "$" . $result . ' = M("' . $db . '")->where($map)->order("' . $order . '")->limit("' . $limit . '")->field("' . $field . '")->select(); ';
			
			if($page){
				$str.= "$" . $page . ' = $p->show();';
			}
			if ($echo) {
				
				$str.= 'foreach ($' . $result . ' as $'.$key.'=>$'.$item.'):';
				$str.= '?>';
				$str.= $content;
				$str.= '<?php endforeach ?>';
			} else {
				$str.= '?>';
				$str.= $content;
			}
			
			
			
			
			
			
		 
			
			
		} elseif ($action == 'category') {
			
			/***********************栏目列表**********************/
			
			$db = trim($attr['db']) ? $attr['db'] : 'category';
			$order = trim($attr['order']) ? $attr['order'] : 'sort asc,id asc';
			$str = "<?php ";
			$str.= ' $map = "";';
			$str.= ' $map["display"] = 1 ;';
			if ($pid) {
				$str.= ' $map["parent_id"] = ' . $pid . ' ;';
			}
			if ($path) {
				$str.= ' $map["path_id"] = array("like","%,' . $path . ',%") ;';
			}
			if (!empty($where)) {
				$str.= ' $map["_string"] = "(' . $where . ')" ;';
			}
		
			if ($type == 'top') {
				if ($pid) {
				} else {
					$w['id'] = $_GET['catid'];
				}
				$w['display'] = 1;
				$row = M($db)->where($w)->field($field)->find();
				$arr = explode(',', $row['path_id']);
				$str.= ' $map["id"] =' . $arr[1] . ' ;';
			}
			

			
			$str.= "$" . $result . ' = M("' . $db . '")->where($map)->order("' . $order . '")->limit("' . $limit . '")->field("' . $field . '")->select(); ';

			
			if ($echo) {
				$str.= 'foreach ($' . $result . ' as $'.$key.'=>$'.$item.'):';
				$str.= '?>';
				$str.= $content;
				$str.= '<?php endforeach ?>';
			} else {
				$str.= '?>';
				$str.= $content;
			}
			
		
		 
			
			
			
		} elseif($action == 'nav'){
			
			/***********************导航**********************/
			
			$db = trim($attr['db']) ? $attr['db'] : 'category';
			$order = trim($attr['order']) ? $attr['order'] : 'sort desc,id asc';
			$str = "<?php ";
			$str.= ' $map = "";';
			$str.= ' $map["display"] = 1 ;';
			$str.= ' $map["navshow"] = 1 ;';
		
			if (!empty($where)) {
				$str.= ' $map["_string"] = "(' . $where . ')" ;';
			}
		
			$str.= "$" . $result . ' = M("' . $db . '")->where($map)->order("' . $order . '")->limit("' . $limit . '")->field("' . $field . '")->select(); ';
			
			if ($echo) {
				$str.= 'foreach ($' . $result . ' as $'.$key.'=>$'.$item.'):';
				$str.= '?>';
				$str.= $content;
				$str.= '<?php endforeach ?>';
			}else{
				$str.= '?>';
				$str.= $content;
				
			}
			 
			
			
			
			
		} elseif($action == 'position'){
			
			/***********************面包屑**********************/
			
			
			
			$db = trim($attr['db']) ? $attr['db'] : 'category';
			$id = trim($attr['id']);
			
			$str = "<?php ";
			$str.= ' $map = "";';
			$str.= ' $map["display"] = 1 ;';
			if (!$id) {
				
				$id=(int)$_GET['catid'];
			}
			
			$row=M('category')->where(array('id'=>$id,'display'=>1))->field('id,parent_id,path_id,modelid')->find();
			
			if($row['modelid']==1){ //单页
				
				
				if(empty($row['parent_id'])){
					
					$path_id=explode(',',$row['path_id']);
					
					if($row_next=M($db)->where(array('display'=>1,'parent_id'=>$path_id[1],'modelid'=>1))->field('id,parent_id,path_id')->order('sort desc,id asc')->find()){
						
					
						
						$id=$row_next['id'];
								
					}
					
				}
				
				
			}
			
			
			$row=M('category')->where(array('id'=>$id,'display'=>1))->field('id,parent_id,path_id,modelid')->find();
			
			$path_id=trim($row['path_id'],'0,');
			$path_id=trim($path_id,',');
			
			 
			$str.= ' $map["id"] = array("in","' .$path_id.  '") ;';
			 
			$str.= "$" . $result . ' = M("' . $db . '")->where($map)->order("' . $order . '")->field("' . $field . '")->select(); ';

			
			
			
		
			$str.= 'foreach ($' . $result . ' as $'.$key.'=>$'.$item.'):';
			$str.= '?>';
			$str.= $content;
			$str.= '<?php endforeach ?>';
			 	
			
			
			
			
			
		}elseif($action == 'ads'){
			
			/***********************广告**********************/
			
			$db = trim($attr['db']) ? $attr['db'] : 'ads';
			$order = trim($attr['order']) ? $attr['order'] : 'sort desc,id desc';
			$id = trim($attr['id']);
			
			$str = "<?php ";
			$str.= ' $map = "";';
			$str.= ' $map["status"] = 1 ;';
			if ($id) {
				
				$str.= ' $map["id"] = ' . $id . ' ;';
			}
			
			if ($pid) {
				
				$str.= ' $map["position_id"] = ' . $pid . ' ;';
			}		
			
		 
			if (!empty($where)) {
				$str.= ' $map["_string"] = "(' . $where . ')" ;';
			} 
			
			 
			$str.= "$" . $result . ' = M("' . $db . '")->where($map)->order("' . $order . '")->field("' . $field . '")->select(); ';

			
			
			
		
			if ($echo) {
				$str.= 'foreach ($' . $result . ' as $'.$key.'=>$'.$item.'):';
				$str.= '?>';
				$str.= $content;
				$str.= '<?php endforeach ?>';
			}else{
				$str.= '?>';
				$str.= $content;
				
			}
			
		 	
		}elseif($action == 'tag'){
			
		/*********************** TAG标签 **********************/
			
			$db = trim($attr['db']) ? $attr['db'] : 'tag';
			$order = trim($attr['order']) ? $attr['order'] : 'num desc';
			
			$str = "<?php ";
		
			if ($pid) {
				
				$str.= ' $map["id"] = ' . $pid . ' ;';
				$str.= ' $map["display"] =1 ;';
				
				$str.= ' $tagrow=M("content")->where($map)->field("tag")->find();';
				$str.= '$' . $result . ' = explode(",",$tagrow[tag]); ';
				
			}else{
				
				$str.= "$" . $result . ' = M("' . $db . '")->order("' . $order . '")->limit("' . $limit . '")->field("' . $field . '")->select(); ';
				
			}
			
			
			
			if ($echo) {
				$str.= 'foreach ($' . $result . ' as $'.$key.'=>$'.$item.'):';
				$str.= '?>';
				$str.= $content;
				$str.= '<?php endforeach ?>';
			}else{
				$str.= '?>';
				$str.= $content;
				
			}
			
			
			
			
		}
		return $str;
	}
	
	
	
	
	
	public function _page($attr, $content) {
			
			$id = trim($attr['id']);
			$field = trim($attr['field']);
			$function = trim($attr['function']);
			$action = trim($attr['action'])?trim($attr['action']):'show';
			$type = trim($attr['type']);
			$echo = $attr['echo']==''?'1':$attr['echo'];
			
			
			
			if($action=='show'){
				
				$db = trim($attr['db']) ? $attr['db'] : 'content';
				
				
				$str = "<?php ";
				$str.= ' $map = "";';
				
				$str.= ' $map["display"] = 1 ;';
		
				
				if (empty($id)) {
					
					$id =(int)$_GET['id'];
					
					$str.= ' $map["id"] = ' . $id . ' ;';
					
					
				} 
				
				
				$str.= ' $'.$field.' =   M("' . $db . '")->where($map)->getfield("'.$field.'"); ';
			
				$echo_content=$field;	
				if($function){
				
					$field=str_replace('###','$'.$field,$function);
					
					
					if($echo){
						
						$str.= '  echo  '.$field.'; ';
						
					}else{
						
					
						$str.= '$'.$echo_content.'='.$field.'; ';
					}
					
					
				}else{
			
				
					if($echo){
						$str.= '  echo  $'.$field.'; ';
					}
					
				}
				
			
				$str.= '?>';
				
				
				
				
			}elseif($action=='prev_next'){
			
			/***********************上一篇，下一篇**********************/
			
			
			
			
			
			if(empty($id)){
				$id=(int)$_GET['id'];
			}
			
			$category_id=M('content')->where(array('id'=>$id,'display'=>1))->getfield('category_id');
			
			$str = "<?php ";
			
			$str.= ' $map = "";';
			//$str.= ' $map["category_id"] = ' . $category_id . ' ;';
			$str.= ' $map["display"] = 1 ;';
			
			$map='';
			//$map['category_id']=$category_id;
			$map['display']=1;
			
			if($type=='prev'){
				$order='sort desc,id asc';
				
				$str.= ' $map["id"] =array("gt",'.$id.')  ;';
				
				$map['id']=array('gt',$id);
				
			}elseif($type=='next'){
				$order='sort desc,id desc';
				$str.= ' $map["id"] =array("lt",'.$id.')  ;';
				
				$map['id']=array('lt',$id);
			}
			
			 
			$str.= ' $row=M("content")->where($map)->order("'.$order.'")->find() ;';
			
			$row=M("content")->where($map)->order($order)->find();
			
		
			 
			if($row){
				
				
				
				if($function){
					
					$prenext_title=str_replace('###','$row[title]',$function);
					
					$str.= ' echo "<a href=\"'.UU("content/show",array("id"=>$row["id"],"catid"=>$row["category_id"])).'\" title=\"$row[title]\">".'.$prenext_title.'."</a>" ;';
				}else{
					
					$str.= ' echo "<a href=\"'.UU("content/show",array("id"=>$row["id"],"catid"=>$row["category_id"])).'\" title=\"$row[title]\">{$row[title]}</a>" ;';
				}
				
			}else{
				
				$str.= ' echo "<span>没有了</span>" ;';
			}
			
			$str.= '?>';
			
		}else{
				
				$db = trim($attr['db']) ? $attr['db'] : 'category';
					
				$str = "<?php ";
				$str.= ' $map = "";';
				
				$str.= ' $map["display"] = 1 ;';
		
				
				if (empty($id)) {
					
					$id = $_GET['catid'];
				} 
				
				$row=M($db)->where(array('display'=>1,'id'=>$id))->field('id,modelid,parent_id,path_id')->find();
				

				
				if(empty($row['parent_id']) && $row['modelid']==1){
					
					$path_id=explode(',',$row['path_id']);
					
					if($row_next=M($db)->where(array('display'=>1,'parent_id'=>$path_id[1]))->field('id,parent_id,path_id')->order('sort desc,id asc')->find()){
						
					
						
						$str.= ' $map["id"] = ' . $row_next['id'] . ' ;';
						
						$map['id']=$row_next['id'];
								
					}else{
						
						$str.= ' $map["id"] = ' . $id . ' ;';
						$map['id']=$id;
					}
					
				}else{
				
					$str.= ' $map["id"] = ' . $id . ' ;';
					$map['id']=$id;
					
				}
				
				$info=M($db)->where($map)->find();
				
			
				if($field=='seotitle'){
					
					
					if($info['meta_title']){
						
						
						$str.= '$'.$field.' = M("' . $db . '")->where($map)->getfield("meta_title"); ';
						
					}else{
						
						$str.= '$'.$field.' = M("' . $db . '")->where($map)->getfield("name"); ';
					}
						
				}else{
					
					$str.= '$'.$field.'=   M("' . $db . '")->where($map)->getfield("'.$field.'"); ';
					
				}
				
			
				$echo_content=$field;	
				
				
				if($function){
				
					$field=str_replace('###','$'.$field,$function);
					
					
					if($echo){
						
						$str.= '  echo  '.$field.'; ';
						
					}else{
						
					
						$str.= '$'.$echo_content.'='.$field.'; ';
					}
					
					
				}else{
			
				
					if($echo){
						$str.= '  echo  $'.$field.'; ';
					}
					
				}
				
			
				$str.= '?>';
				
			}
			

		
			
			return $str;
		
		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
}

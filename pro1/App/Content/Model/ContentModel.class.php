<?php 
namespace Content\Model;
use Think\Model;
class ContentModel extends Model{

   protected $_validate = array(

     array('category_id','require','请选择分类',1), 
     array('title','require','请输入标题',1), 
	
   );

 
}

?>
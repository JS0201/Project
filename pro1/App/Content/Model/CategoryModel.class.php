<?php 

namespace Admin\Model;
use Think\Model;
class CategoryModel extends Model{

   protected $_validate = array(

     array('name','require','请输入分类名称',1), 
     array('modelid','require','请选择栏目模型',1), 
	
   );

 
}

?>
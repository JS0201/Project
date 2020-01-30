<?php 

namespace Member\Model;
use Think\Model;
class MemberGroupModel extends Model{

   protected $_validate = array(
     array('name','require','请输入等级名称',1), 
   );

   
  
}

?>
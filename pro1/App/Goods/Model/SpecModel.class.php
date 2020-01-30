<?php 
namespace Goods\Model;
use Think\Model;
class SpecModel extends Model{

	protected $_validate = array(
		array('name','require','请输入规格名称',1), 
	);

}

?>
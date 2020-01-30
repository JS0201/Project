<?php
namespace Goods\TagLib;
use Think\Template\TagLib;
class Goods extends TagLib {
	protected $tags = array(
	
		'sql' => array('attr' => 'db,where ,field, order , limit ,result', 'level' => 3, 'close' => 1),
	
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
	
	

	
	
	
	
}

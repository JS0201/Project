<?php

namespace Goods\Model;

use Think\Model;

class BrandModel extends Model {
    protected $_validate = array(
        
        array('name', 'require', '请输入品牌名称', 1),
                
                
                
    );
    protected $_auto = array( 
        
        
        
        
    );
}

?>
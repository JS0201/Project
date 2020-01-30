<?php

namespace Comment\Model;

use Think\Model;

class CommentModel extends Model {

    protected $_validate = array(
            
			
			
    );

	protected $_auto = array(
	
        array('datetime', 'time', 1, 'function'),
        array('clientip', 'getip', 1, 'function'),
		
	);
	
 

}

?>
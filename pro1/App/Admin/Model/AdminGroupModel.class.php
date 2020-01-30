<?php

namespace Admin\Model;

use Think\Model;

class AdminGroupModel extends Model {

    protected $_validate = array(
        array('title', 'require', '请输入权限组名', 1),
        array('description', 'require', '请输入权限描述', 1),
    );

}

?>
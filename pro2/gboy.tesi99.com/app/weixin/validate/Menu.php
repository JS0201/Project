<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\weixin\validate;
use think\Validate;

class Menu extends  Validate
{

    protected $rule = [
        ['title','require','{%menu_title_require}'],
        ['data','require','{%menu_data_require}'],
    ];


}
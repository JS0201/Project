<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\goods\validate;
use think\Validate;

class Category extends Validate{

    protected $rule = [
        ['parent_id'  ,'require','{%goods_category_id_require}'],
        ['name'  ,'require','{%goods_category_name_require}'],

    ];


}
<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\content\validate;
use think\Validate;

class Category extends Validate{

    protected $rule = [
        ['modelid'  ,'require|max:30','请选择模型'],
        ['name'  ,'require','{%class_name_require}'],

    ];


}

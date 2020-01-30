<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\ads\validate;
use think\Validate;

class Ads extends Validate{

    protected $rule = [
        ['title'  ,'require','{%ads_name_require}'],
        ['position_id'  ,'require','{%ads_position_require}'],
        ['thumb'  ,'require','{%ads_thumb_require}'],

    ];

}
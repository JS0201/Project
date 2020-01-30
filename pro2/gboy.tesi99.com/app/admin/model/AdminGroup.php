<?php
/**
 * gboyshop
 * ============================================================================
 * 版权所有 2010-2020 gboyshop，并保留所有权利。
 * ============================================================================
 * Author: gboy
 * Date: 2017/11/1
 */

namespace app\admin\model;

use think\Model;
class AdminGroup extends Model
{



    public function getRulesAttr($value, $data){


      return  explode(',',$value);

    }

    public function setRulesAttr($value, $data){


        return trim($value,',');



    }

}
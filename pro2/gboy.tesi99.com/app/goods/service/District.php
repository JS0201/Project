<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\goods\service;
use think\Model;
use think\Db;

class District extends Model
{
    public function initialize()
    {
        $this->model = model('goods/District');
    }

    public function getNameById($id, $name = 'name')
    {
        return $this->model->getNameById($id, $name);
    }
}
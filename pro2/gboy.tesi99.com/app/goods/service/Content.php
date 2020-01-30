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
class Content extends Model
{
    public function initialize()
    {
        $this->model = model('goods/Content');
    }
    public function getList($where = '', $select = '', $page = 1, $limit)
    {
        $start = ($page - 1) * $limit;
        $content = $this->model->getList($where, $select, $start, $limit);
        if($content) {
            foreach($content as $k => $v) {
                $content[$k]['add_time'] = date('Y-m-d', $v['add_time']);
            }
        }
        return $content;
    }


}
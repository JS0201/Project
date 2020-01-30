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
class Comment extends Model
{
    public function initialize()
    {
        $this->model = model('goods/Comment');
    }
    public function getComment($sku_id)
    {
        $sql = "select m.realname, m.face, c.content, c.created from gboy_comment as c left join gboy_member as m on m.id = c.member_id where c.sku_id = {$sku_id}";
        $result = $this->model->sql($sql);
        if($result) {
            foreach($result as $k => $v) {
                $result[$k]['created'] = date('Y-m-d', $v['created']);
            }
        }
        return $result;
    }

}
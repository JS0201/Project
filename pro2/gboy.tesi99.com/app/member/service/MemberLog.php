<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\member\service;
use think\Model;

class MemberLog extends Model{

    public function initialize()
    {
       $this->model = model('member/MemberLog');
    }


    public function get_list($sqlmap,$page=[]){
        $lists = $this->model->alias('l')->join("gboy_member m","m.id = l.mid")->field("l.*,m.username")->where($sqlmap)->order('l.id desc')->paginate($page);
        return $lists;
    }

    public function build_sqlmap($params){
        $sqlmap=[];

        if($params['start']) {
            $time[] = ["GT", strtotime($params['start'])];
        }
        if($params['end']) {
            $time[] = ["LT", strtotime($params['end'])];
        }
        if($time){
            $sqlmap['dateline'] = $time;
        }

        if($params['type']){
            $sqlmap['type'] = $params['type'];
        }

        if($params['keywords']){
            $keywords=trim($params['keywords']);
            $sqlmap['username'] = ['like','%'.$keywords.'%'] ;
        }
        return $sqlmap;
    }



}
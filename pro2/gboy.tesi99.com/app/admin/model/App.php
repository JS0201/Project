<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\admin\model;
use think\Model;

class App extends Model{

    protected $append=['ico','module_name'];



    public function getIcoAttr($value,$data)
    {


        $identifier=explode('.',$data['identifier']);
        $ico=__ROOT__.'static/images/'.$identifier[0].'/'.$identifier[1].'.png';

        return $ico;
    }


    public function getModuleNameAttr($value,$data){

        $identifier=explode('.',$data['identifier']);

        return $identifier[1];


    }


}
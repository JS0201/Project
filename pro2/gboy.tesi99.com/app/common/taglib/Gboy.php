<?php
namespace app\common\taglib;
use think\template\TagLib;

class Gboy extends TagLib{


    protected $tags = array(

        'content' => array('level' => 3, 'close' => 1),
        'goods' => array('level' => 3, 'close' => 1),

    );


    public function tagContent($attr, $content){

        //print_r($attr);

        $module = trim($attr['module']);
        $result = trim($attr['return']) ? $attr['return'] : 'result';
        $item = trim($attr['item']) ? $attr['item'] : 'v';
        $key = trim($attr['key']) ? $attr['key'] : 'key';

        $service=$module.'/'.ucfirst($attr['tagfile']);
        $method=$attr['method'];

        unset($attr['tagfile'],$attr['method'],$attr['module']);

        $str = "<?php ";
        $str.= "$" . $result.'=model("'.$service.'","Service")->'.$method.'('.array2html($attr).');';
        $str.= '?>';
        $str.= $content;



        return $str;

    }


    public function tagGoods($attr, $content){


        $module = trim($attr['module']);
        $result = trim($attr['return']) ? $attr['return'] : 'result';
        $item = trim($attr['item']) ? $attr['item'] : 'v';
        $key = trim($attr['key']) ? $attr['key'] : 'key';

        $service=$module.'/'.ucfirst($attr['tagfile']);
        $method=$attr['method'];

        unset($attr['tagfile'],$attr['method'],$attr['module']);

        $str = "<?php ";
        $str.= "$" . $result.'=model("'.$service.'","Service")->'.$method.'('.array2html($attr).');';
        $str.= '?>';
        $str.= $content;

        return $str;
    }



}
?>
<?php
use think\Lang;
use think\Request;

/**
 * 时间格式化
 * @param  string  $time	 时间戳
 * @param  string  $format 时间格式
 * @return date
 */
function getdatetime($time, $format = 'Y-m-d H:i:s') {
    return date($format, $time);
}

/**
 * 字符串加解密
 * @param  string  $string	 原始字符串
 * @param  string  $operation 加解密类型
 * @param  string  $key		 密钥
 * @param  integer $expiry	 有效期

 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($key != '' ? $key : 'asfasf');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 随机字符串
 * @param int $length 长度
 * @param int $numeric 类型(0：混合；1：纯数字)
 * @return string
 */
function random($length, $numeric = 0) {
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}


/*截取字符串*/
function cut_str($string, $sublen, $start = 0, $code = 'UTF-8'){
    if($code == 'UTF-8'){
        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        preg_match_all($pa, $string, $t_string);
        if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen));
        return join('', array_slice($t_string[0], $start, $sublen));
    }else{
        $start = $start*2;
        $sublen = $sublen*2;
        $strlen = strlen($string);
        $tmpstr = '';
        for($i=0; $i< $strlen; $i++){
            if($i>=$start && $i< ($start+$sublen)){
                if(ord(substr($string, $i, 1))>129){
                    $tmpstr.= substr($string, $i, 2);
                }
                else{
                    $tmpstr.= substr($string, $i, 1);
                }
            }
            if(ord(substr($string, $i, 1))>129) $i++;
        }
        return $tmpstr;
    }
}


/**
 * 验证码校验
 * @param int $code 验证码
 * @param int $id 标识
 * @return string
 */
function check_verify($code, $id = "") {
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 电子邮箱格式判断
 * @param  string $email 字符串
 * @return boolean
 */
function is_email($email) {
    if (!empty($email)) {
        return preg_match('/^[a-z0-9]+([\+_\-\.]?[a-z0-9]+)*@([a-z0-9]+[\-]?[a-z0-9]+\.)+[a-z]{2,6}$/i', $email);
    }
    return FALSE;
}

/**
 * 手机号码格式判断
 * @param string $string
 * @return boolean
 */
function is_mobile($string) {
    if (!empty($string)) {
        return preg_match('/^1[3|4|5|7|8][0-9]\d{8}$/', $string);
    }
    return FALSE;
}

/**
 * 邮政编码格式判断
 * @param string $string
 * @return boolean
 */
function is_zipcode($string) {
    if (!empty($string)) {
        return preg_match('/^[0-9][0-9]{5}$/', $string);
    }
    return FALSE;
}



function dhtmlspecialchars($string, $flags = null) {
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = dhtmlspecialchars($val, $flags);
        }
    } else {
        if ($flags === null) {
            $string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
            if (strpos($string, '&amp;#') !== false) {
                $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
            }
        } else {
            if (PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if (strtolower(CHARSET) == 'utf-8') {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }
    return $string;
}



/**
 * 上传
 * @param string $dirname 目录名称
 * @return array
 */
function uploads($file, $dirname = '', $subname = '', $filename = array('uniqid', ''), $saveext = '') {

    $x = substr($dirname, 0, 1);
    if ($x == '/') {

        $arr = explode('/', $dirname);

        $rootPath = $arr[1] . '/';

        $dirname = '';
        foreach ($arr as $k => $v) {

            if ($k > 1 && $v) {

                $dirname .= $v . '/';
            }
        }
    } else {

        $rootPath = 'uploads/';
    }



    $upload = new \Think\Upload(); // 实例化上传类
    $upload->maxSize = C('max_file_size'); // 设置附件上传大小
    $upload->exts = explode('|', C('upload_img_type')); // 设置附件上传类型

    $upload->rootPath = $rootPath; // 设置附件上传根目录
    $upload->savePath = $dirname ? $dirname . '/' : ''; // 设置附件上传（子）目录

    $upload->subName = $subname ? $subname : array('date', 'Ymd');
    $upload->thumb = true;
    $upload->saveExt = $saveext;
    $upload->saveName = $filename;
    $upload->replace = true;

    // 上传文件 
    $info = $upload->uploadOne($file);
    if (!$info) {// 上传错误提示错误信息
        showmessage($upload->getError(), '', 0, '', 'json');
    }

    $info['file_url'] = $upload->rootPath . $info['savepath'] . $info['savename'];

    return $info;
}

/**
 * 生成缩略图、水印
 * @param string $filename 文件名称
 * @param bool $isumb 是否裁剪
 * @param numeric $width 长度
 * @param numeric $height 高度
 * @param bool $ismark 是否水印
 * @return null 
 */
function thumb_mark($filename, $isumb = true, $width = 150, $height = 150, $ismark = false) {
    //如果是远程图片则跳过
    if (!preg_match('#^http:\/\/#i', $filename)) {

        $image = new \Think\Image();

        $watermark_logo = C('watermark_logo'); //水印图片

        $watermark_switch = C('watermark_switch'); //水印开关
        $watermark_position = C('watermark_position'); //水印位置
        $image_type = C('image_type'); //图片裁剪类型
        $watermark_type = C('watermark_type'); //水印类型,图片和文字
        $watermark_text = C('watermark_text'); //水印文字
        $watermark_color = C('watermark_color'); //水印颜色
        $watermark_font = C('watermark_font'); //水印字体大小

        if (!$watermark_position) {
            $watermark_position = rand(1, 9);
        }
        if ($isumb) {
            $image->open($filename)->thumb($width, $height, $image_type)->save($filename);
        }


        if ($ismark || $watermark_switch) {

            if ($watermark_type) {
                //图片水印
                $image->open($filename)->water($watermark_logo, $watermark_position)->save($filename);
            } else {

                //文字水印

                if (!$watermark_text) {
                    $watermark_text = '文字水印';
                }
                if (!$watermark_color) {
                    $watermark_color = '#000000';
                }
                if (!$watermark_font) {
                    $watermark_font = '20';
                }


                $image->open($filename)->text($watermark_text, 'data/watermark/1.ttf', $watermark_font, $watermark_color, $watermark_position)->save($filename);
            }
        }
    }
}

/**
 * 附件
 * @param array 数组 url和module必填
 * @return null 
 */
function attachment($conf = array()) {


    $url = $conf['url'];
    $module = $conf['module'];
    $name = $conf['name'] ? $conf['name'] : '';
    $catid = $conf['catid'];

    $is_mark = isset($conf['is_mark']) ? $conf['is_mark'] : 0;


    //如果是远程图片则跳过
    if (!preg_match('#^http:\/\/#i', $url)) {
        if (!$url || !$module) {

            showmessage('图片不存在或模型标识不存在');
        }

        $image = new \Think\Image();

        $image->open($url);


        $width = $image->width();
        $height = $image->height();
        $filetype = $image->mime();



        $picture_arr = explode('.', $url);
        $picture_arr2 = explode('/', $url);
        $fileext = $picture_arr[1];

        $filename = $picture_arr2[count($picture_arr2) - 1];



        $data = array();
        $data['mid'] = session('gboy_admin_login.id');
        $data['module'] = $module;
        $data['catid'] = $catid;
        $data['filename'] = $filename;
        $data['filepath'] = '';
        $data['filesize'] = filesize($url);
        $data['fileext'] = $fileext;
        $data['isimage'] = 1;
        $data['is_mark'] = $is_mark;
        $data['downloads'] = 0;
        $data['datetime'] = time();
        $data['clientip'] = getip();
        $data['use_nums'] = 1;
        $data['filetype'] = $filetype;
        $data['width'] = $width;
        $data['height'] = $height;
        $data['name'] = $name;
        $data['issystem'] = 1;
        $data['url'] = $url;

        M('attachment')->data($data)->add();
    }
}

/**
 * 获取客户端IP
 * @return string 
 */
function getip() {
    static $realip = NULL;
    if ($realip !== NULL) {
        return $realip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            /* 取X-Forwarded-For中第x个非unknown的有效IP字符? */
            foreach ($arr as $ip) {
                $ip = trim($ip);
                if ($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '127.0.0.1';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '127.0.0.1';
    return $realip;
}

/**
 * 是否ajax请求
 */
function is_ajax(){
	
	return Request::instance()->isAjax();
}

/**
 * 是否post请求
 */
function is_post(){
	
	return Request::instance()->isPost();
}

/**
 * 获取语言变量值
 * @param string    $name 语言变量名
 * @param string    $lang 模块
 * @return mixed
 */
function L($name, $module='')
{
    return Lang::to_get($name, $module);
}


/**
 * 通用提示页
 * @param  string  $msg    提示消息（支持语言包变量）
 * @param  integer $status 状态（0：失败；1：成功）
 * @param  string  $extra  附加数据
 * @param  string  $format 返回类型
 * @return mixed
 */
function showmessage($message, $jumpUrl = '-1', $status = 0, $extra = '', $format = '') {
    if (empty($format)) {
        $format = is_ajax() ? 'json' : 'html';
    }

    switch ($format) {
        case 'html':

            if (!defined('IN_ADMIN')) {
                if ($jumpUrl == '-1' || $jumpUrl == '') {
                    echo "<script>history.go(-1);</script>";
                } else {
                    redirect($jumpUrl);
                }
            }else{


                $view = new \think\View();

                echo  $view->fetch('template/showmessage.tpl',['message'=>$message,'url'=>$jumpUrl]);

            }

            break;
        case 'json':
            $result = array(
                'status' => $status,
                'referer' => $jumpUrl,
                'message' => $message,
                'result' => $extra
            );
            echo json_encode($result);
            exit;
            break;
        default:
            # code...
            break;
    }
    exit;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array		  返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array()) {
    if (is_array($tree)) {
        $refer = array();
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if (isset($reffer[$child])) {
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby = 'asc');
    }
    return $list;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author
 */
function list_to_tree($list, $pk = 'id', $pid = 'parent_id', $child = '_child', $root = 0) {
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = & $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[$data[$pk]] = & $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = & $refer[$parentId];
                    $parent[$child][$data[$pk]] = & $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc') {
    if (is_array($list)) {
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/**
 * 字节格式化
 * @access public
 * @param string $size 字节
 * @return string
 */
function byte_Format($size) {
    $kb = 1024;          // Kilobyte
    $mb = 1024 * $kb;    // Megabyte
    $gb = 1024 * $mb;    // Gigabyte
    $tb = 1024 * $gb;    // Terabyte

    if ($size < $kb)
        return $size . 'B';

    else if ($size < $mb)
        return round($size / $kb, 2) . 'KB';

    else if ($size < $gb)
        return round($size / $mb, 2) . 'MB';

    else if ($size < $tb)
        return round($size / $gb, 2) . 'GB';
    else
        return round($size / $tb, 2) . 'TB';
}

/**
 * 后台分页
 * @access public
 * @param string $param 参数
 * @param string $url 地址
 * @return string
 */
function page_url($param = array(), $url) {
    $url = (empty($url)) ? $_SERVER['REQUEST_URI'] : $url;
    $urls = parse_url($url);
    $_url = $urls['path'];
    parse_str($urls['query'], $_param);
    $params = array_merge($_param, $param);
    return $_url . '?' . http_build_query($params);
}

/*
 * 函数说明：截取指定长度的字符串
 * (UTF-8专用 汉字和大写字母长度算1，其它字符长度算0.5)
 *
 * @param  string  $sourcestr  原字符串
 * @param  int     $cutlength  截取长度
 * @param  string  $etc        省略字符...
 * @return string              截取后的字符串
 */

function restrlen($sourcestr, $cutlength = 10, $etc = '...') {
    $returnstr = '';
    $i = 0;
    $n = 0.0;
    $str_length = strlen($sourcestr); //字符串的字节数
    while (($n < $cutlength) and ( $i < $str_length)) {
        $temp_str = substr($sourcestr, $i, 1);
        $ascnum = ord($temp_str); //得到字符串中第$i位字符的ASCII码
        if ($ascnum >= 252) { //如果ASCII位高与252
            $returnstr = $returnstr . substr($sourcestr, $i, 6); //根据UTF-8编码规范，将6个连续的字符计为单个字符
            $i = $i + 6; //实际Byte计为6
            $n++; //字串长度计1
        } elseif ($ascnum >= 248) { //如果ASCII位高与248
            $returnstr = $returnstr . substr($sourcestr, $i, 5); //根据UTF-8编码规范，将5个连续的字符计为单个字符
            $i = $i + 5; //实际Byte计为5
            $n++; //字串长度计1
        } elseif ($ascnum >= 240) { //如果ASCII位高与240
            $returnstr = $returnstr . substr($sourcestr, $i, 4); //根据UTF-8编码规范，将4个连续的字符计为单个字符
            $i = $i + 4; //实际Byte计为4
            $n++; //字串长度计1
        } elseif ($ascnum >= 224) { //如果ASCII位高与224
            $returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i = $i + 3; //实际Byte计为3
            $n++; //字串长度计1
        } elseif ($ascnum >= 192) { //如果ASCII位高与192
            $returnstr = $returnstr . substr($sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i = $i + 2; //实际Byte计为2
            $n++; //字串长度计1
        } elseif ($ascnum >= 65 and $ascnum <= 90 and $ascnum != 73) { //如果是大写字母 I除外
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，大写字母计成一个高位字符
        } elseif (!(array_search($ascnum, array(37, 38, 64, 109, 119)) === FALSE)) { //%,&,@,m,w 字符按1个字符宽
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，这些字条计成一个高位字符
        } else { //其他情况下，包括小写字母和半角标点符号
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数计1个
            $n = $n + 0.5; //其余的小写字母和半角标点等与半个高位字符宽...
        }
    }
    if ($i < $str_length) {
        $returnstr = $returnstr . $etc; //超过长度时在尾处加上省略号
    }
    return $returnstr;
}

/**
 *  完全过虑PHP，JS，css
 *
 * @access    public
 * @param     string  $str  需要过滤的字符串
 * @return    string
 */
function clearhtml($str) {

    $str = strip_tags($str);

    //首先去掉头尾空格
    $str = trim($str);

    //接着去掉两个空格以上的
    $str = preg_replace('/\s(?=\s)/', '', $str);

    //最后将非空格替换为一个空格
    $str = preg_replace('/[\n\r\t]/', ' ', $str);

    $str = str_replace(array('&nbsp;', '　'), '', $str);

    return trim($str);
}

/**
 * xss过滤函数
 *
 * @param $string
 * @return string
 */
function remove_xss($string) {
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);
    $parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $parm2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $parm = array_merge($parm1, $parm2);

    for ($i = 0; $i < sizeof($parm); $i++) {
        $pattern = '/';
        for ($j = 0; $j < strlen($parm[$i]); $j++) {
            if ($j > 0) {
                $pattern .= '(';
                $pattern .= '(&#[x|X]0([9][a][b]);?)?';
                $pattern .= '|(&#0([9][10][13]);?)?';
                $pattern .= ')?';
            }
            $pattern .= $parm[$i][$j];
        }
        $pattern .= '/i';
        $string = preg_replace($pattern, '', $string);
    }
    return $string;
}

/**
 * 汉字转拼音
 * @param string $str 待转换的字符串
 * @param string $charset 字符串编码
 * @param bool $ishead 是否只提取首字母
 * @return string 返回结果
 */
function GetPinyin($str, $charset = "utf-8", $ishead = 0) {
    $restr = '';
    $str = trim($str);
    if ($charset == "utf-8") {
        $str = iconv("utf-8", "gb2312", $str);
    }
    $slen = strlen($str);
    $pinyins = array();
    if ($slen < 2) {
        return $str;
    }
    $fp = fopen('data/plugin/pinyin/pinyin.dat', 'r');
    while (!feof($fp)) {
        $line = trim(fgets($fp));
        $pinyins[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
    }
    fclose($fp);

    for ($i = 0; $i < $slen; $i++) {
        if (ord($str[$i]) > 0x80) {
            $c = $str[$i] . $str[$i + 1];
            $i++;
            if (isset($pinyins[$c])) {
                if ($ishead == 0) {
                    $restr .= $pinyins[$c];
                } else {
                    $restr .= $pinyins[$c][0];
                }
            } else {
                $restr .= "_";
            }
        } else if (preg_match("/[a-z0-9]/i", $str[$i])) {
            $restr .= $str[$i];
        } else {
            $restr .= "_";
        }
    }
    return $restr;
}

/**
 * 模拟访问的URL
 * @param string $str 访问的URL
 * @param string $charset post数据(不填则为GET)
 * @param string $ishead 提交的$cookies
 * @param bool $ishead cookies
 * @return string 返回结果
 */
function curl_request($url, $post = '', $cookie = '', $returnCookie = 0) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://" . $_SERVER['HTTP_HOST']);
    if ($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if ($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if ($returnCookie) {
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie'] = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    } else {
        return $data;
    }
}


/**
 * 对多位数组进行排序
 * @param $multi_array 数组
 * @param $sort_key需要传入的键名
 * @param $sort排序类型
 */
function multi_array_sort($multi_array, $sort_key, $sort = SORT_DESC) {
	if (is_array($multi_array)) {
		foreach ($multi_array as $row_array) {
			if (is_array($row_array)) {
				$key_array[] = $row_array[$sort_key];
			} else {
				return FALSE;
			}
		}
	} else {
		return FALSE;
	}
		array_multisort($key_array, $sort, $multi_array);
		return $multi_array;
}

/* 获取加密后的签名 */
function get_sign($params, $key, $sign_type = 'md5') {
    ksort($params, SORT_STRING);
    $arg = "";
    while (list ($k, $val) = each($params)) {
        if (empty($val) || $k == 'sign'|| in_array($k, array('m', 'c', 'a')))
            continue;
        $arg.=$k . "=" . urldecode(htmlspecialchars_decode($val)) . "&";
    }
    $arg = substr($arg, 0, count($arg) - 2);
    return $sign_type($arg . $key);
}

/**
 * 多维数组合并（支持多数组）
 * @return array
 */
function array_merge_multi () {
    $args = func_get_args();
    $array = array();
    foreach ( $args as $arg ) {
        if ( is_array($arg) ) {
            foreach ( $arg as $k => $v ) {
                if ( is_array($v) ) {
                    $array[$k] = isset($array[$k]) ? $array[$k] : array();
                    $array[$k] = array_merge_multi($array[$k], $v);
                } else {
                    $array[$k] = $v;
                }
            }
        }
    }
    return $array;
}


function sizecount($size) {
    if($size >= 1073741824) {
        $size = round($size / 1073741824 * 100) / 100 . ' GB';
    } elseif($size >= 1048576) {
        $size = round($size / 1048576 * 100) / 100 . ' MB';
    } elseif($size >= 1024) {
        $size = round($size / 1024 * 100) / 100 . ' KB';
    } else {
        $size = intval($size) . ' Bytes';
    }
    return $size;
}

function fileext($filename) {
    return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
}


/**
  +-----------------------------------------------------------------------------------------
 * 删除目录及目录下所有文件或删除指定文件
  +-----------------------------------------------------------------------------------------
 * @param str $path   待删除目录路径
 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
  +-----------------------------------------------------------------------------------------
 * @return bool 返回删除状态
  +-----------------------------------------------------------------------------------------
 */
function deldir($path, $delDir = FALSE) {
    if (is_array($path)) {
        foreach ($path as $subPath)
            deldir($subPath, $delDir);
    }
    if (is_dir($path)) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? deldir("$path/$item", $delDir) : unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir)
                return rmdir($path);
        }
    } else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
    clearstatcache();
}

/**
 * XML转数组
 * @param string $arr
 * @param boolean $isnormal
 * @return array
 */
function xml2array(&$xml, $isnormal = FALSE) {

    $xml_parser = new \Org\Util\Xml($isnormal);


    $data = $xml_parser->parse($xml);
    $xml_parser->destruct();
    return $data;
}


function xmltoarray($xml)
{       
   //将XML转为array        
   $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);       
	return $array_data;
}
/*
*
* 使用特定function对数组中所有元素做处理
* @param string &$array 要处理的字符串
* @param string $tocode 编码后
* @param string $oldcode 编码前
* @param string $function 要执行的函数
* @return boolean $apply_to_keys_also 是否也应用到key上
* @return array $array 是否也应用到key上
* @access public
*/
 
function arrayRecursive (&$array, $function, $tocode=false,$oldcode=false,$apply_to_keys_also = false){
 
	foreach ($array as $key => $value) {
		if (is_array($value)) {
		 
			arrayRecursive($array[$key], $function, $apply_to_keys_also);
		 
		} else {
		 
		if($tocode&&$oldcode) {
		 
			if(function_exists(mb_convert_encoding)) {
		 
			$value = mb_convert_encoding($value,$tocode,$oldcode);
		 
		}else{
		 
			return "error";
		 
		}
		 
		}
		 
		$array[$key] = $function($value);
	 
	}
	 
	 
	if ($apply_to_keys_also && is_string($key)) {
	 
		$new_key = $function($key);
	 
		if ($new_key != $key) {
		 
		$array[$new_key] = $array[$key];
		 
		unset($array[$key]);
		 
		}
	 
	}
	 
	}
 
return $array;
}
 
 
/**
* 将数组转换为JSON字符串（兼容中文）
* @param array $array 要转换的数组
* @return string 转换得到的json字符串
* @access public
**/

function json($array) {
	arrayRecursive($array, 'urlencode', true);
	$json = json_encode($array);
	return urldecode($json);
 
}

function dstripslashes($string) {
    if(empty($string)) return $string;
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = dstripslashes($val);
        }
    } else {
        $string = stripslashes($string);
    }
    return $string;
}

/**
 * 序列化
 * @param mixed $string 原始信息
 * @param intval $force
 * @return mixed
 */
function daddslashes($string, $force = 1) {
     if(is_array($string)) {
          $keys = array_keys($string);
          foreach($keys as $key) {
                $val = $string[$key];
                unset($string[$key]);
                $string[addslashes($key)] = daddslashes($val, $force);
          }
     } else {
          $string = addslashes($string);
     }
     return $string;
}


/**
* 转换数据为HTML代码
* @param array $data 数组
*/
function array2html($data) {
	if (is_array($data)) {
		$str = 'array(';
		foreach ($data as $key=>$val) {
			if (is_array($val)) {
				$str .= "'$key'=>".array2html($val).",";
			} else {
				if (strpos($val, '$')===0) {
					$str .= "'$key'=>$val,";
				} else {
					$str .= "'$key'=>'".daddslashes($val)."',";
				}
			}
		}
		$str = trim($str, ',');
		return $str.')';
	}
	return false;
}

/**
 * 数组转XML
 * @param array $arr
 * @param boolean $htmlon
 * @param boolean $isnormal
 * @param intval $level
 * @return type
 */
function array2xml($arr, $htmlon = TRUE, $isnormal = FALSE, $level = 1) {
    $s = $level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : '';
    $space = str_repeat("\t", $level);
    foreach ($arr as $k => $v) {
        if (!is_array($v)) {
            $s .= $space . "<item id=\"$k\">" . ($htmlon ? '<![CDATA[' : '') . $v . ($htmlon ? ']]>' : '') . "</item>\r\n";
        } else {
            $s .= $space . "<item id=\"$k\">\r\n" . array2xml($v, $htmlon, $isnormal, $level + 1) . $space . "</item>\r\n";
        }
    }
    $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
    return $level == 1 ? $s . "</root>" : $s;
}

/**
 * 对象转数组
 * @param $array
 * @return array
 */
function object2array($array) {
    if(is_object($array)) {
        $array = (array)$array;
    } if(is_array($array)) {
        foreach($array as $key=>$value) {
            $array[$key] = object2array($value);
        }
    }
    return $array;
}

/**
 * 获得文章内容里的外部资源
 * @param string $content
 * @return type
 */
function get_remote_file($content) {

    //初始化变量
    $content = stripslashes($content);
    $host = 'http://' . $_SERVER['HTTP_HOST'];

    //过滤图片文件
    $pic_arr = array();
    preg_match_all("/src=[\"|'|\s]{0,}(http:\/\/([^>]*)\.(gif|jpg|png|bmp))/isU", $content, $pic_arr);
    $pic_arr = array_unique($pic_arr[1]);


    //初始化下载类
    $htd = new \Org\Util\HttpDown();

    foreach ($pic_arr as $k => $v) {

        if (preg_match('#' . $host . '#i', $v))
            continue;
        if (!preg_match('#^http:\/\/#i', $v))
            continue;


        $htd->OpenUrl($v);


        $type = $htd->GetHead('content-type');


        if ($type == 'image/gif')
            $tempfile_ext = 'gif';

        else if ($type == 'image/png')
            $tempfile_ext = 'png';

        else if ($type == 'image/wbmp')
            $tempfile_ext = 'bmp';
        else
            $tempfile_ext = 'jpg';



        $ymd = date('Ymd');
        $upload_url = 'uploads/' . $ymd;


        if (!file_exists($upload_url)) {
            mkdir($upload_url);

            $fp = fopen($upload_url . '/index.htm', 'w');
            fclose($fp);
        }

        //上传文件名称
        $filename = time() + rand(1, 9999) . '.' . $tempfile_ext;

        //上传文件路径
        $save_url = $upload_url . '/' . $filename;

        //生成本地路径
        /*
          $self = explode('/', $_SERVER['PHP_SELF']);
          $self_size = count($self) - 2;
          $self_str  = '';
          for($i=0; $i<$self_size; $i++)
          {
          $self_str .= $self[$i].'/';
          }

          echo $self_str;

          $save_url = $self_str.$upload_url.'/'.$filename;
         */
        $save_url = $upload_url . '/' . $filename;
        $save_dir = $upload_url . '/' . $filename;

        $rs = $htd->SaveToBin($save_dir);
        if ($rs) {
            $content = str_replace(trim($v), $save_url, $content);
        }
    }

    $htd->Close();



    return $content;
}

/**
 * 内容获取图片
 * @param content $content
 * @return array
 */
function content_picture($content) {

    preg_match_all('/<img[^>]*src\s?=\s?[\'|"]([^\'|"]*)[\'|"]/is', $content, $picarr);

    return $picarr;
}

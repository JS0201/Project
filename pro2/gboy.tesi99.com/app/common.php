<?php

use think\Lang;
use think\Request;
use think\Db;
use think\Hook;
use think\config;

/**
 * 时间格式化
 * @param  string $time 时间戳
 * @param  string $format 时间格式
 * @return date
 */
function getdatetime($time, $format = 'Y-m-d H:i:s')
{
    return date($format, $time);
}


/**
 * 是否ajax请求
 */
function is_ajax()
{

    return Request::instance()->isAjax();
}

/**
 * 是否post请求
 */
function is_post()
{

    return Request::instance()->isPost();
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


/**
 * 获取语言变量值
 * @param string $name 语言变量名
 * @param string $lang 模块
 * @return mixed
 */
function L($name, $module = '')
{
    return lang($name, $module);
}


/**
 * 通用提示页
 * @param  string  $msg 提示消息（支持语言包变量）
 * @param  integer $status 状态（0：失败；1：成功）
 * @param  string  $extra 附加数据
 * @param  string  $format 返回类型
 * @return mixed
 */
function showmessage($message, $jumpUrl = '-1', $status = 0, $extra = '', $format = '')
{
    if(empty($format)) {
        $format = is_ajax() ? 'json' : 'html';
    }
    switch($format) {
        case 'html':

            if(!defined('IN_ADMIN')) {
                if($jumpUrl == '-1' || $jumpUrl == '') {
                    echo "<script>history.go(-1);</script>";
                } else {
                    redirect($jumpUrl);
                }

            } else {


                $view = new \think\View();


                if(stripos($jumpUrl, 'formhash') === false) {

                    //$jumpUrl = $jumpUrl . config('pathinfo_depr') . 'formhash' . config('pathinfo_depr') . FORMHASH;

                    if(stripos($jumpUrl, '?') === false){
                        $pathinfo_depr='?';
                    }else{
                        $pathinfo_depr='&';
                    }

                    $jumpUrl = $jumpUrl . $pathinfo_depr.'formhash='  . FORMHASH;
                }

                echo $view->fetch(ROOT_PATH . 'public/template/showmessage.tpl', ['message' => $message, 'url' => $jumpUrl]);

            }

            break;
        case 'json':
            $result = array('status' => $status, 'referer' => $jumpUrl, 'message' => $message, 'result' => $extra);
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
 * @param  array  $tree 原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list 过渡用的中间数组，
 * @return array          返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array())
{
    if(is_array($tree)) {
        $refer = array();
        foreach($tree as $key => $value) {
            $reffer = $value;
            if(isset($reffer[$child])) {
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
 * @param array  $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author
 */
function list_to_tree($list, $pk = 'id', $pid = 'parent_id', $child = '_child', $root = 0)
{
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if($root == $parentId) {
                $tree[$data[$pk]] = &$list[$key];
            } else {
                if(isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    $parent[$child][$data[$pk]] = &$list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array  $list 查询结果
 * @param string $field 排序的字段名
 * @param array  $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc')
{
    if(is_array($list)) {
        $refer = $resultSet = array();
        foreach($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch($sortby) {
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
        foreach($refer as $key => $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}


/**
 * [more_array_unique 二维数组去重保留key值]
 * @param  [type] $arr [description]
 * @return [type]      [description]
 */
function more_array_unique($arr)
{
    foreach($arr[0] as $k => $v) {
        $arr_inner_key[] = $k;
    }
    foreach($arr as $k => $v) {
        $v = join(',', $v);
        $temp[$k] = $v;
    }
    $temp = array_unique($temp);
    foreach($temp as $k => $v) {
        $a = explode(',', $v);
        $arr_after[$k] = array_combine($arr_inner_key, $a);
    }
    return $arr_after;
}

/**
 * 指定某字段的值删除二维数据
 * @param $multi_array 二维数组
 * @param $keys 指定键名
 * @param $value 指定值
 * @return array
 */
function multi_array_value_del(&$multi_array, $keys, $value)
{
    if($multi_array && is_array($multi_array)) {

        foreach($multi_array as $key => $val) {

            foreach($val as $k => $v) {

                if($v[$keys] == $value) {

                    unset($multi_array[$key][$k]);

                }
            }
            $multi_array[$key] = array_values($multi_array[$key]);
        }
    }
    return $multi_array;
}


/**
 * 多维数组合并（支持多数组）
 * @return array
 */
function array_merge_multi()
{
    $args = func_get_args();
    $array = array();
    foreach($args as $arg) {
        if(is_array($arg)) {
            foreach($arg as $k => $v) {
                if(is_array($v)) {
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


/**
 * 对多位数组进行排序
 * @param $multi_array 数组
 * @param $sort_key需要传入的键名
 * @param $sort排序类型
 */
function multi_array_sort($multi_array, $sort_key, $sort = SORT_DESC)
{
    if(is_array($multi_array)) {
        foreach($multi_array as $row_array) {
            if(is_array($row_array)) {
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

/**
 * 对多位数组重置键
 * @param $ar
 */
function multi_array_reset_key(&$ar) {
    if(! is_array($ar)) return;
    foreach($ar as $k=>&$v) {
        if(is_array($v)) multi_array_reset_key($v);
        if($k == 'children') $v = array_values($v);
    }
}


/**
 * XML转数组
 * @param string  $arr
 * @param boolean $isnormal
 * @return array
 */
function xml2array(&$xml, $isnormal = FALSE)
{

    $xml_parser = new \Org\Xml($isnormal);


    $data = $xml_parser->parse($xml);
    $xml_parser->destruct();
    return $data;
}


/**
 * 转换数据为HTML代码
 * @param array $data 数组
 */
function array2html($data) {
    if (is_array($data)) {
        $str = '[';
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
        return $str.']';
    }
    return false;
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
 * 列队遍历文件夹
 * @access public
 * @param array   $dir 路径
 * @param boolean $is_absolute 是否显示绝定路径
 * @return array
 */
function read_dir_queue($dir, $is_absolute = false)
{
    $files = array();
    $queue = array($dir);
    while($data = each($queue)) {
        $path = $data['value'];
        if(is_dir($path) && $handle = opendir($path)) {
            while($file = readdir($handle)) {
                if($file == '.' || $file == '..')
                    continue;
                if(!$is_absolute) {
                    $files[] = $real_path = $file;
                } else {
                    $files[] = $real_path = $path . DS . $file;
                }

                if(is_dir($real_path))
                    $queue[] = $real_path;
            }
        }
        closedir($handle);
    }
    return $files;
}


/**
 * 字符串加解密
 * @param  string  $string 原始字符串
 * @param  string  $operation 加解密类型
 * @param  string  $key 密钥
 * @param  integer $expiry 有效期
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key != '' ? $key : '#$%^&*(DFGHJ)');
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
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
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
function random($length, $numeric = 0)
{
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}


/**
 * 获取客户端IP
 * @return string
 */
function getip()
{
    static $realip = NULL;
    if($realip !== NULL) {
        return $realip;
    }
    if(isset($_SERVER)) {
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            /* 取X-Forwarded-For中第x个非unknown的有效IP字符? */
            foreach($arr as $ip) {
                $ip = trim($ip);
                if($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if(isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '127.0.0.1';
            }
        }
    } else {
        if(getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('HTTP_CLIENT_IP')) {
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
 * 字节格式化
 * @access public
 * @param string $size 字节
 * @return string
 */
function byte_Format($size)
{
    $kb = 1024;          // Kilobyte
    $mb = 1024 * $kb;    // Megabyte
    $gb = 1024 * $mb;    // Gigabyte
    $tb = 1024 * $gb;    // Terabyte

    if($size < $kb)
        return $size . 'B';

    else if($size < $mb)
        return round($size / $kb, 2) . 'KB';

    else if($size < $gb)
        return round($size / $mb, 2) . 'MB';

    else if($size < $tb)
        return round($size / $gb, 2) . 'GB'; else
        return round($size / $tb, 2) . 'TB';
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
 * 恢复数据库
 * @param $file  sql文件
 *
 */

function db_restore_file($file)
{
    $sqls = file_get_contents($file);
    $sqlarr = explode(";\n", $sqls);
    foreach($sqlarr as &$sql) {
        Db::execute($sql);
    }
}

/**
 * 设置configs.php
 * @param $data 数组
 * @return bool|int
 */
function set_conifg($data,$file='configs')
{
    $file = APP_PATH . 'extra/'.$file.'.php';
    if(file_exists($file)) {
        $configs = include $file;
    }
    $configs = array_merge($configs, $data);
    return file_put_contents($file, "<?php\t \n\n return  " . var_export($configs, true) . ";");
}

/**
 * 强制下载
 * @param string $filename 文件名
 * @param string $content 内容
 */
function force_download_content($filename, $content)
{
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Transfer-Encoding: binary");
    header("Content-Disposition: attachment; filename=$filename");
    echo $content;
    exit ();
}


/**
 * 列出本地目录的文件
 * @param string $path
 * @param string $pattern
 * @return array
 */
function list_file($path, $pattern = '*')
{
    if(strpos($pattern, '|') !== false) {
        $patterns = explode('|', $pattern);
    } else {
        $patterns [0] = $pattern;
    }
    $i = 0;
    $dir = array();
    if(is_dir($path)) {
        $path = rtrim($path, '/') . '/';
    }
    foreach($patterns as $pattern) {
        $list = glob($path . $pattern);
        if($list !== false) {
            foreach($list as $file) {
                $dir [$i] ['filename'] = basename($file);
                $dir [$i] ['path'] = dirname($file);
                $dir [$i] ['pathname'] = realpath($file);
                $dir [$i] ['owner'] = fileowner($file);
                $dir [$i] ['perms'] = substr(base_convert(fileperms($file), 10, 8), -4);
                $dir [$i] ['atime'] = fileatime($file);
                $dir [$i] ['ctime'] = filectime($file);
                $dir [$i] ['mtime'] = filemtime($file);
                $dir [$i] ['size'] = filesize($file);
                $dir [$i] ['type'] = filetype($file);
                $dir [$i] ['ext'] = is_file($file) ? strtolower(substr(strrchr(basename($file), '.'), 1)) : '';
                $dir [$i] ['isDir'] = is_dir($file);
                $dir [$i] ['isFile'] = is_file($file);
                $dir [$i] ['isLink'] = is_link($file);
                $dir [$i] ['isReadable'] = is_readable($file);
                $dir [$i] ['isWritable'] = is_writable($file);
                $i++;
            }
        }
    }
    $cmp_func = create_function('$a,$b', '
		if( ($a["isDir"] && $b["isDir"]) || (!$a["isDir"] && !$b["isDir"]) ){
			return  $a["filename"]>$b["filename"]?1:-1;
		}else{
			if($a["isDir"]){
				return -1;
			}else if($b["isDir"]){
				return 1;
			}
			if($a["filename"]  ==  $b["filename"])  return  0;
			return  $a["filename"]>$b["filename"]?-1:1;
		}
		');
    usort($dir, $cmp_func);
    return $dir;
}
function  phpqrcode($content,$name,$logo = true){

    include '../data/phpqrcode/phpqrcode.php';
    $value = $content; //二维码内容
    $errorCorrectionLevel = 'L';//容错级别
    $matrixPointSize = 6;//生成图片大小
    //生成二维码图片
    QRcode::png($value, $name, $errorCorrectionLevel, $matrixPointSize, 2);
    //$logo = 'Public/images/ewm_logo.png';//准备好的logo图片
    $QR = $name;//已经生成的原始二维码图

    if ($logo !== FALSE) {
        $QR = imagecreatefromstring(file_get_contents($QR));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);//二维码图片宽度
        $QR_height = imagesy($QR);//二维码图片高度
        $logo_width = imagesx($logo);//logo图片宽度
        $logo_height = imagesy($logo);//logo图片高度
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width/$logo_qr_width;
        $logo_qr_height = $logo_height/$scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
            $logo_qr_height, $logo_width, $logo_height);
    }
    //输出图片
    imagepng($QR, $name);
    return $name;

}

/**
 * 删除文件夹
 * @author rainfer <81818832@qq.com>
 * @param string
 * @param int
 */
function remove_dir($dir, $time_thres = -1)
{
    foreach(list_file($dir) as $f) {
        if($f ['isDir']) {
            remove_dir($f ['pathname'] . '/');
        } else if($f ['isFile'] && $f ['filename']) {
            if($time_thres == -1 || $f ['mtime'] < $time_thres) {
                @unlink($f ['pathname']);
            }
        }
    }
}


/**
 * 密码检测强度
 * @param $string
 * @return float|int
 */
function password_strength($str){
    $score=0;
    if(preg_match("/[0-9]+/",$str))
    {
        $score ++;
    }
    if(preg_match("/[0-9]{3,}/",$str))
    {
        $score ++;
    }
    if(preg_match("/[a-z]+/",$str))
    {
        $score ++;
    }
    if(preg_match("/[a-z]{3,}/",$str))
    {
        $score ++;
    }
    if(preg_match("/[A-Z]+/",$str))
    {
        $score ++;
    }
    if(preg_match("/[A-Z]{3,}/",$str))
    {
        $score ++;
    }
    if(preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/",$str))
    {
        $score += 2;
    }
    if(preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]{3,}/",$str))
    {
        $score ++ ;
    }
    if(strlen($str) >= 10)
    {
        $score ++;
    }

    return $score;
}


/**
 * 直接执行行为
 * @param $hook 钩子文件
 * @param $action 执行钩子方法
 * @param $params 参数
 */
function hook_exec($hook,$action,$params){

    Hook::exec($hook,$action,$params);

}



/**
 * 错误页面
 * @param $code 状态码
 */
function error_status($code){


    $error_html=ROOT_PATH.'data/error/'.$code.'.html';
    if(!is_file($error_html)){
        http_response_code('404');
    }else{

        http_response_code($code);
        include  ROOT_PATH.'data/error/'.$code.'.html';
    }
    exit();


}
/**
 * 上传图片处理
 */
function upload_img($data)
{
    $return = array('status' => true, 'msg' => '');
    if(!$data) {
        $return['status'] = false;
        $return['msg'] = '上传失败';
        return $return;
    }
    if($data['file']['error'] > 0) {
        $return['status'] = false;
        $return['msg'] = $data['file']['error'];
        return $return;
    }
    $allowed_types = array('jpg', 'gif', 'png', 'jpeg');
    preg_match('|\.(\w+)$|', $data['file']['name'], $ext);
    $ext = strtolower($ext[1]);
    if(!in_array($ext, $allowed_types)) {
        $return['status'] = false;
        $return['msg'] = '请选择正常的图片类型';
        return $return;
    }
    //验证大小
    return $return;

}
if(!function_exists("array_column"))
{

    function array_column($array,$column_name)
    {

        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }

}
function send_sms($phone,$content, $userid = '', $account = '', $password = ''){
    $post_data = array();
    $post_data['userid'] = $userid;
    $post_data['account'] = $account;
    $post_data['password'] = $password;
    $post_data['content'] = $content; //短信内容需要用urlencode编码下
    $post_data['mobile'] = $phone;
    $post_data['sendtime'] = ''; //不定时发送，值为0，定时发送，输入格式YYYYMMDDHHmmss的日期值
    $url='http://120.76.213.253:8888/sms.aspx?action=send';
    $o='';
    foreach ($post_data as $k=>$v)
    {
        $o.="$k=".urlencode($v).'&';
    }

    $post_data=substr($o,0,-1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。

    $result = curl_exec($ch);
    return $result;
}

function get_min_price(){

    $type=config('pr_r_type');
    $open_price=config('p_open_price');
    $max_pr=config('p_pr');

    $price_arr=array();

    if(empty($type)){
        //按全盘最低价
        //$min_price=M('trading_order')->where(array('order_status'=>3))->order('price asc')->getfield('price');
        $min_price=$open_price;
    }else{
        //按昨天最新成交价

        $start_time=strtotime(date('Y-m-d',strtotime('-1 day')));
        $end_time=strtotime(date('Y-m-d 23:59:59',strtotime('-1 day')));

        $sqlmap=array();
        $sqlmap['order_status']=3;
        //$sqlmap['order_ok_time']=array('between',array($start_time,$end_time));
        $sqlmap['order_ok_time']=array('lt',strtotime(date('Y-m-d')));
        $min_price=db('trading_order')->where($sqlmap)->order('id desc')->value('price');

        //没有相应价格就按全盘价
        if($min_price<=0){
            $min_price=$open_price;
        }
    }
    $price_arr['min_price']=$min_price;
    $price_arr['max_price']=$min_price*$max_pr;
    return $price_arr;

}
function build_order_no($suffix = 'o') {
    return $suffix.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

function dowith_sql($str)
{
    $str = str_replace("and","",$str);
    $str = str_replace("execute","",$str);
    $str = str_replace("update","",$str);
    $str = str_replace("count","",$str);
    $str = str_replace("chr","",$str);
    $str = str_replace("mid","",$str);
    $str = str_replace("master","",$str);
    $str = str_replace("truncate","",$str);
    $str = str_replace("char","",$str);
    $str = str_replace("declare","",$str);
    $str = str_replace("select","",$str);
    $str = str_replace("create","",$str);
    $str = str_replace("delete","",$str);
    $str = str_replace("insert","",$str);
    $str = str_replace("'","",$str);
    $str = str_replace("\"","",$str);
   $str = str_replace(" ","",$str);
   $str = str_replace("or","",$str);
   $str = str_replace("=","",$str);
   $str = str_replace("%20","",$str);
   return $str;
}

/* 后台操作记录
 * $action 操作类型 1常规操作、2敏感操作、3系统操作
 * $data 修改的数据&&操作的内容
 * $name 记录位置
 * $code 操作的ID
 * $table 操作的表名，修改时有效
 * $Primarykey 主键
 * $id 修改的数据ID
 */
function AdminLog($action = 1, $data, $nav = '未定义位置', $name = '', $code = '--', $table = '', $Primarykey = '', $id = ''){
    // 判断操作类型
    if($action == 1){ // 常规操作
        $content = $data;
    }
    elseif($action == 2){ // 敏感操作
        $content = $data;
    }
    elseif($action == 3){ // 系统操作
        if(is_array($data)){ // 判断是否需要对比数据
            // 读取修改前数据
            $get = AdminLoginGet('setting', 1);
            // 对比数据并生成内容
            $content = AdminLoginContent($get, $data);
            $content = $content ? $content : '未修改任何系统配置';
            $name = lang('admin_log_edit_site');
        }
        else{
            $content = $data;
        }
    }
    elseif($action == 4){ // 微信配置
        $action = 2;
        // 读取修改前数据
        $get = AdminLoginGet('setting', 1);
        // 对比数据并生成内容
        $content = AdminLoginContent($get, $data);
        $content = $content ? $content : '未修改任何微信配置';
        $name = lang('admin_log_edit_site');
    }

    $data = array(
        'admin_id' => ADMIN_ID,
        'action' => $action,
        'nav' => $nav,
        'name' => $name,
        'content' => $content,
        'code' => $code,
        'add_time' => time(),
    );
    Db::table('gboy_admin_log')->insert($data);
}

/* 后台操作记录 -- 读取数据
 * $table 操作的表名
 * $Primarykey 主键
 * $id 修改的数据ID
 */
function AdminLoginGet($table, $Primarykey = '', $id = ''){
    if(!$table) return false;
    // 拼接表名
    $table = config("database.prefix") . $table;
    // 判断数据
    if(!$Primarykey || !$id){ // 读取表所有数据
        $setting = Db::table($table)->select();
        foreach($setting as $value){
            $data[$value['key']] = array(
                'title' => $value['title'],
                'value' => $value['value'],
            );
        }
    }
    else{

    }
    // 判断是否读取到数据
    if(!$data) return false;
    return $data;
}

/* 后台操作记录 -- 对比数据并生成内容
 * $old_data 修改前数据
 * $data 修改后数据
 */
function AdminLoginContent($old_data, $data){
    // 循环新数据
    $content ='';
    foreach($data as $key => $val){
        if(($key != 'formhash') && ($old_data[$key]['value'] != $val)){
            $content .= ($content ? '|' : '') . $old_data[$key]['title'] . '(' .$old_data[$key]['value'] . '->' . $val .')' ;
        }
    }
    return $content;
}

/* 后台操作记录 -- 将数据格式化
 * $old_data 修改前数据
 * $data 修改后数据
 */
function AdminLoginFormat($data){
    // 判断数据是否数组
    if(!is_array($data)) return false;
    foreach($data as $val){
        $code .= $code ? ('|' . $val) : $val;
    }
    return $code;
}



function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    // 获取当前字符字数
    $stelen = strlen($str);
    if($stelen < $length) return $str;

    if (function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}


?>
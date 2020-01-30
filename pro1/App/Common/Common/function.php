<?php

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

function str($str)
{
    //特殊字符的过滤方法
    $str = strip_tags($str);
    $str = htmlspecialchars($str);
    $str = nl2br($str);
    $str = str_replace('`', '', $str);
    $str = str_replace('·', '', $str);
    $str = str_replace('~', '', $str);
    $str = str_replace('!', '', $str);
    $str = str_replace('！', '', $str);
    $str = str_replace('@', '', $str);
    $str = str_replace('#', '', $str);
    $str = str_replace('$', '', $str);
    $str = str_replace('￥', '', $str);
    $str = str_replace('%', '', $str);
    $str = str_replace('^', '', $str);
    $str = str_replace('……', '', $str);
    $str = str_replace('&', '', $str);
    $str = str_replace('*', '', $str);
    $str = str_replace('(', '', $str);
    $str = str_replace(')', '', $str);
    $str = str_replace('（', '', $str);
    $str = str_replace('）', '', $str);
    $str = str_replace('-', '', $str);
    $str = str_replace('_', '', $str);
    $str = str_replace('——', '', $str);
    $str = str_replace('+', '', $str);
    $str = str_replace('=', '', $str);
    $str = str_replace('|', '', $str);
    $str = str_replace('\\', '', $str);
    $str = str_replace('[', '', $str);
    $str = str_replace(']', '', $str);
    $str = str_replace('【', '', $str);
    $str = str_replace('】', '', $str);
    $str = str_replace('{', '', $str);
    $str = str_replace('}', '', $str);
    $str = str_replace(';', '', $str);
    $str = str_replace('；', '', $str);
    $str = str_replace(':', '', $str);
    $str = str_replace('：', '', $str);
    $str = str_replace('\'', '', $str);
    $str = str_replace('"', '', $str);
    $str = str_replace('“', '', $str);
    $str = str_replace('”', '', $str);
    $str = str_replace(',', '', $str);
    $str = str_replace('，', '', $str);
    $str = str_replace('<', '', $str);
    $str = str_replace('>', '', $str);
    $str = str_replace('《', '', $str);
    $str = str_replace('》', '', $str);
    $str = str_replace('.', '', $str);
    $str = str_replace('。', '', $str);
    $str = str_replace('/', '', $str);
    $str = str_replace('、', '', $str);
    $str = str_replace('?', '', $str);
    $str = str_replace('？', '', $str);
    //防sql防注入代码的过滤方法
    $str = str_replace('master','',$str);
    $str = str_replace('truncate','',$str);
    $str = str_replace('char','',$str);
    $str = str_replace('declare','',$str);
    $str = str_replace('select','',$str);
    $str = str_replace('create','',$str);
    $str = str_replace('delete','',$str);
    $str = str_replace('insert','',$str);
    return trim($str);
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
/*,'9'=>'B级小区','10'=>'C级小区','11'=>'D级小区','12'=>'E级小区','13'=>'F级小区'*/
function types_of($status=''){
    $status_array=array('1'=>'每日释放静态收益','2'=>'直推下单5%加速释放','3'=>'下单无限分享到锁仓钱包','4'=>'直推下单返锁仓钱包','5'=>'团队奖励释放','6'=>'红利董事每日全网分红');
    if($status==''){
        return $status_array;
    }else{
        return $status_array[$status];
    }
}
function wallet_ypes_of($status=''){
    $status_array=array('bocai_wallet'=>'博彩钱包','shop_wallet'=>'商城钱包','lock_wallet'=>'锁仓钱包','usdt_wallet'=>'USDT钱包','futou_wallet'=>'复投钱包');
    if($status==''){
        return $status_array;
    }else{
        return $status_array[$status];
    }
}



function sf_types_of($status=''){
    $status_array=array('0'=>'认购HM','1'=>'奖励HM');
    if($status==''){
        return $status_array;
    }else{
        return $status_array[$status];
    }

}

function agency_level($status=''){
    $status_array=array('0'=>'游客','1'=>'初级经纪人','2'=>'中级经纪人','3'=>'高级经纪人');
    if($status==''){
        return $status_array;
    }else{
        return $status_array[$status];
    }

}
//boss团队名称
function boss_level($status=''){
    $status_array=array('0'=>'暂无等级','1'=>'初级主管','2'=>'中级主管','3'=>'高级主管','4'=>'红利董事');
    if($status==''){
        return $status_array;
    }else{
        return $status_array[$status];
    }

}



//后台操作日志
function admin_operating($ad_data){
    $log_data=array(
        'admin_id'=>$ad_data['admin_id'],
        'uid'=>$ad_data['uid'],
        'text'=>$ad_data['text'],
        'time'=>$ad_data['time'],
    );
    M('admin_operating')->data($log_data)->add();
}


/*
    //执行个人钱包操作动作
    $grqb_data['zh_types']='shop_integral';//购物积分字段
    $grqb_data['key_types']='shop_integral_key';//购物积分加密字段
    $grqb_data['uid']=$pid;//获取积分用户ID
    $grqb_data['types']='2';//累加积分
    $grqb_data['number']=$shop_jf;//本次产生的积分数量
    $grqb_data['text']='推荐会员注册时，系统发现推荐人购物积分余额数据异常，停止给此会员执行累加购物积分动作！';
    $this->personal_wallet($grqb_data);//调用个人钱包方法

*/

//累加个人钱包
function personal_wallet($qb_data){
    $qb_types=$qb_data['zh_types'];//账户类型
    $id=$qb_data['uid'];//会员id
    $types=$qb_data['types'];//1为减去积分，2为累加积分
    $number=$qb_data['number'];//交易数量
    $money_finance=M('money_finance');//个人财务表

        if ($types==1) {
            if($qb_types=="lock_wallet"){
            $money_finance->where(array('uid'=>$id))->setDec($qb_types,$number);//减去子积分
            M('money_freed')->where(array('uid'=>$id))->setDec("total",$number);//减去子积分
            }else{
                $money_finance->where(array('uid'=>$id))->setDec($qb_types,$number);//减去子积分
            }
        }elseif ($types==2) {
            if($qb_types=="lock_wallet") {
                $money_finance->where(array('uid' => $id))->setInc($qb_types, $number);//累加子积分
                M('money_freed')->where(array('uid'=>$id))->setInc("total",$number);//累加子积分
            }else{
                $money_finance->where(array('uid' => $id))->setInc($qb_types, $number);//累加子积分
            }
        }


        return true;

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
        return preg_match('/^1[3|4|5|7|8|9][0-9]\d{8}$/', $string);
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
 * 处理标签扩展（钩子）
 * @param string $tag 标签名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, $params = array()) {
    \Think\Hook::listen($hook, $params);
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
 * 栏目模型
 * @param int $type 类型(0：混合；1：纯数字)
 * @return array
 */
function site_model($type) {
    $model_type = array();
    $model_type[1] = '单页模型';
    $model_type[2] = '文章模型';
    $model_type[3] = '图片模型';
    $model_type[4] = '其它模型';

    if ($type) {

        return $model_type[$type];
    }
    return $model_type;
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
    $file_url_check=file_get_contents($info['file_url']);
    //preg_match("/<(\s)*?\? (.*?) /Uix", $file_url_check ,$match);
    preg_match("/fopen/is", $file_url_check ,$match);
    if($match[0]){
        list($uid) = explode("\t", authcode($_COOKIE['member_auth']));
        $file_open=fopen('data/erruser/'.$uid.'_'.time().'.txt','w+');
        fwrite($file_open, $info['file_url']);
        unlink($info['file_url']);
        showmessage('非法的图片', '', 0, '', 'json');
    }

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
 * 通用提示页
 * @param  string  $msg    提示消息（支持语言包变量）
 * @param  integer $status 状态（0：失败；1：成功）
 * @param  string  $extra  附加数据
 * @param  string  $format 返回类型
 * @return mixed
 */
function showmessage($message, $jumpUrl = '-1', $status = 0, $extra = '', $format = '') {
    if (empty($format)) {
        $format = IS_AJAX ? 'json' : 'html';
    }

    switch ($format) {
        case 'html':

            if (!defined('IN_ADMIN')) {
                if ($jumpUrl == '-1' || $jumpUrl == '') {
                    echo "<script>history.go(-1);</script>";
                } else {
                    redirect($jumpUrl);
                }
            } else {
//echo THINK_PATH . 'tpl/dispatch_jump.tpl';
                $root = __ROOT__;

                include THINK_PATH . 'Tpl/dispatch_jump.tpl';
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

/**
 * 前台404页面
 * @return string
 */
function _404() {

    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    include('404.html');
    exit();
}

/**
 * 获取状态中文信息
 * @param  string $ident 标识
 * @return [string]
 */
function ch_status($ident) {
    $arr = array(
        'cancel' => '已取消',
        'recycle' => '已回收',
        'delete' => '已删除',
        'create' => '<span style="color:red;">未付款</span>',
        'load_pay' => '待付款',
        'pay' => '已付款',
        'load_confirm' => '待确认',
        'part_confirm' => '部分确认',
        'all_confirm' => '已确认',
        'load_delivery' => '待发货',
        'part_delivery' => '部分发货',
        'all_delivery' => '已发货',
        'load_finish' => '待收货',
        'part_finish' => '部分完成',
        'all_finish' => '已完成',
        'receive' => '已收货',
        // 前台时间轴
        'time_cancel' => '取消订单',
        'time_recycle' => '回收订单',
        'time_create' => '提交订单',
        'time_pay' => '确认付款',
        'time_confirm' => '确认订单',
        'time_delivery' => '商品发货',
        'time_finish' => '确认收货',
    );
    return $arr[$ident];
}

function send_sms($phone,$content){
    $post_data = array();
    $post_data['userid'] = C('m_sms_id');
    $post_data['account'] = C('m_sms_user');
    $post_data['password'] = C('m_sms_pwd');
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
    //print_r($result);
    return $result;
}

function send_sms_all($phone,$content)
{
    require_once '/statics/msg/ChuanglanSmsApi.php';
    $clapi = new ChuanglanSmsApi();
    $result = $clapi->sendInternational($phone, $content);
    if (!is_null(json_decode($result))) {
        $output = json_decode($result, true);
        if (isset($output['code']) && $output['code'] == '0') {
            return "0";
        } else {
            return "1";
        }
    } else {
        return "1";
    }
}

/**
 * Thinkphp默认分页样式转Bootstrap分页样式
 * @author H.W.H
 * @param string $page_html tp默认输出的分页html代码
 * @return string 新的分页html代码
 */
function bootstrap_page_style($page_html){
    if ($page_html) {
        $page_show = str_replace('<div>','<nav><ul class="pagination">',$page_html);
        $page_show = str_replace('</div>','</ul></nav>',$page_show);

        $page_show = str_replace('<span class="current">','<li class="active"><a>',$page_show);
        $page_show = str_replace('</span>','</a></li>',$page_show);

        $page_show = str_replace(array('<a class="num"','<a class="prev"','<a class="next"','<a class="end"','<a class="first"'),'<li><a',$page_show);
        $page_show = str_replace('</a>','</a></li>',$page_show);
    }
    return $page_show;
}


function  phpqrcode($content,$name){
    include 'data/phpqrcode/phpqrcode.php';
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

/** * 判断是否为合法的身份证号码 * @param $mobile * @return int */
function isCreditNo($vStr){
    $vCity = array(
        '11','12','13','14','15','21','22',
        '23','31','32','33','34','35','36',
        '37','41','42','43','44','45','46',
        '50','51','52','53','54','61','62',
        '63','64','65','71','81','82','91'
    );
    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr))
        return false;
    if (!in_array(substr($vStr, 0, 2), $vCity))
        return false;
    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
    $vLength = strlen($vStr);
    if ($vLength == 18) {
        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
    } else {
        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
    }
    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday)
        return false;
    if ($vLength == 18) {
        $vSum = 0;
        for ($i = 17 ; $i >= 0 ; $i--) {
            $vSubStr = substr($vStr, 17 - $i, 1);
            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
        }
        if($vSum % 11 != 1)
            return false;
    }
    return true;
}



function yc_phone($str){
    $str=$str;
    $resstr=substr_replace($str,'****',3,4);
    return $resstr;
}

/**
 * 生成订单号
 */
function build_order_no($suffix = 'o') {
    return $suffix.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}



function get_min_price(){

    $type=C('pr_r_type');
    $open_price=C('p_open_price');
    $max_pr=C('p_pr');

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
        $min_price=M('trading_order')->where($sqlmap)->order('id desc')->getfield('price');

        //没有相应价格就按全盘价
        if($min_price<=0){
            $min_price=$open_price;
        }
    }




    $price_arr['min_price']=$min_price;
    $price_arr['max_price']=$min_price*$max_pr;



    return $price_arr;

}

//去掉\扛，用于显示数据
function escapeshow($str,$iseditor=false){
    if($iseditor){
        return stripslashes($str); //编辑器不转义html
    }else{
        return htmlspecialchars(stripslashes($str));
    }

}
//助记词
function getword()
{
    $str = "随着计算机和网络通信技术的迅猛的发展互联网技术的应用逐渐向人类的各种活动领域渗透其中所蕴藏的无限商机使得商家纷纷把目光投向电子商务电子商务正在以人们难以想象的速度向社会经济生活的各个方面渗透传统的金融也密切地注视到这股势不可挡的全球经济一体化网络化的潮流于是增值服务是以美术为卖点可以看作商品而游戏里的宝剑则不一种全新的金融服务经营理念——电子金融便应运而生从历史发展的进程来看，要理解电子金融必须从金融电子化和电子商务谈起所谓电子金融化是指金融企业采用除互联网技术以外的现代通信计算机和网络等信息技术手段提高传统金融服务业务的工作效率降低经营成本实现金融业务处理自动化金融企业管理信息化和决策科学化为客户提供更快捷、更方便的服务进而提升金融企业是市场竞争优势的行为电子金融是对金融电子化的一个超越与金融电子化有所不同电子金融运行的主要技术基础是日益完善的互联网技术由于互联网技术的全球连通性开放性快捷性和边际成本低廉的特征电子金融更加强调整个金融服务业务基于互联网技术的重组和创新使客户不受营业时间和营业地点的限制随时随地享受金融企业提供的各种高质量低成本的服务";
 
    $len = mb_strlen($str);
    $strs = array();
    for ($i = 0; $i < 20; $i++) {
        $rand = mt_rand(0, $len - 20);
        $strs[] = mb_substr($str, $rand, 2);
    }
    array_unique($strs);
    return (json_encode($strs));
}

function getworden()
{
    $str = "apple|car|tiger|mokey|flower|money|coin|btc|ltc|eth|eos|xrp|study|children|teacher|layer|sun|son|father|mother|good|cat|ok|bike|bad|news|paper|tea|school|desk|ear|noise|eye|love|Rice|Corn|Pear|Table|Chair|Sofa|Mirror|act|actor|add|adult|ago|agree|ahead|aid|aim|air|alike|alive|all|allege|allow|alound|dad|daile|dalysiler|danger|safently|safe|fic|in|order|paris|dad|white|berlin|class|help|cup|bread|beef|milk|water|egg|fish|tofu|meat|chicken|pork|mutton|label|form|else|ice|sick|dial|glass|neck|rub|guess|tailor|menu|noisy|fork|tea|toilet|long|wet|castle|late|rule|surf|exit|spread|from|never|pack|big|printer|gate|laugh|single|road|place|third|first|movie|carve|survey|lose|animal|rainy|shine|roof|visit|Africa|fat|soap|share|tomb|kite|honey|Mrs|father|symbol|pay|plural|cover|berry|pole|dare|thank|glad|tell|follow|less|worker|job|kilo|buy|barn|shut|stick|seal|sea|walnut|later|nurse|skate|fossil|divide|draw|chee|yet|yet|dim|alive|winner|robot|jeep|jacket|drive|nut|will|hang|coach|relay|solve|only|often|spirit|mirror|ninety|wine|huge|club|truck|regard|exam|scary|foxy|insect|blue|tiger|tired|eave|eave|face|cise|lead|eer|hotel|green|horse|busy|nobody|dle|fairs|right|riller|rne|pur|pose|ness|wit|ffe|that|shirt|south|dly|rade|penRed|blue|pi|rabbt|bird|ant|fish|eagle|der|hor|mot|dae|gsoe|kill|coda|dfsa|vidi|pepe|son|base|assi|rici|frien|wate|tofu|dog|green|jackt|shirt|scarf|hat|tie|bike|yacgt|cloth|ball|keif|knig|fore|spoon|ball|lite|kite|light|liv|room|natue|pank|nature|fruit|pet|shop|natu|theme|air|moon|seed|rose|grass|lsast|rods|leaf|west|north|hurt|sore|eight|four|eight|sisx|samll|nice|kind|smart|teent|old|fun|hin|exti|tasty|quixt|qixy|monsd|take|wath|kites|colle|stamp|picn|get|mage|cinew|warm|windy|clound|bye|cra|lst|whtr|crss|sige|smoke|wait|my|anewa|enjoy|exper|asse|eara|resp|devo|aver|extrew|food|surf|form|flure|splend|chare|poet|ligt|coure|pill|liver|wewr|cotr|faegkl|dier|paet|wasir|sufeer|baner|faer|gate|mate|loss|asai|gain|eye|ear|hera|headl|nose|mouth|leg|redl|blue|poink|fisl|boer|hair|yelle|hake|cara|story|ewenglh|dish|mager|fing|lion|sqid|shake|daeiu|badyl|girl|sight|daugh|kid|xiea|moe|tisxe|xice|tixt|actoe|airst|cleaner|toug|hot|soup|ice|xoke|shei|shiew|coat|cap|tiean|van|motore|ship|yacht|car|jeep|cloth|raim|trouse|yacht|picture|wall|floor|tablr|sher|tsoua|lamp|gift|touy|card|home|garden|study|gme|wash|umber|nest|spoom|foeer|por|key|doll|ballba|ganer|pesh|natue|zoo|ciern|juiey|gaer|meue|liop|pade|nue|pee|cacer|goure|wihter|soy|yam|haeric|chinne|xonds|cod|fave|white|garlic";
    $strarr = explode("|", $str);
    shuffle($strarr);
    array_unique($strarr);
    $strarr = array_slice($strarr, 3, 2);
    // var_dump($strarr);
    return (json_encode($strarr));
}
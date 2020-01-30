<?php
namespace form;

class Form {
	
	
    static public function input($type = 'text', $name, $value, $label, $description='', $attribute = array()) {
	    $attribute = self::_parseAttribute($attribute);
		if($type == 'hidden'){
			$string .= self::$type($name, $value, $attribute);
		}else{
	        $string = '<div class="form-group"><label class="col-sm-2 control-label no-padding-right">'.$label.'</label>';
	        $string .= '<div class="col-sm-10">'.(self::$type($name, $value, $attribute));
	        if($description) {
	            $string .= '<span class="help-inline col-xs-12 col-sm-7"><span class="middle">'.$description.'</span></span>';
	        }
			$string.='</div>';
			
	        $string .= '</div><div class="space-4"></div>';
		}
        return $string;
    }

    /**
     * 单行文本
     * @param string $name   表单名称
     * @param string $value  默认值
     * @param array $attribute  参数
        {
            color => 颜色值
            key   => 表单名
        }
     * @return string
     */
	static private function text($name, $value, $attribute) {
		$css='';
		if(isset($attribute['css'])) $css=$attribute['css'];
		
        $string = '<input class="col-xs-10 col-sm-5 '.$css.'" type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" tabindex="0"' .self::_buildAttribute($attribute).' />';
        if(isset($attribute['color'])) {
            $color_name = (isset($attribute['key'])) ? $attribute['key'] : $name.'_color';
            $string .= '<input class="color-picker input_cxcolor" type="text" name="'.$color_name.'" value="'.$attribute['color'].'" style="background-color: '.$attribute['color'].';">';
            if(!defined('COLOR_INIT')) {
                $string .= '<script type="text/javascript" charset="utf-8" src="'.__ROOT__.'statics/js/cxcolor/jquery.cxcolor.min.js"></script>';
                $string .= '<link type="text/css" href="'.__ROOT__.'statics/js/cxcolor/jquery.cxcolor.css"  rel="stylesheet">';
                $string .= '<script>$(".input_cxcolor").cxColor();</script>';
                define('COLOR_INIT', TRUE);
            }
        }
        return $string;
	}
    /**
     * 隐藏域
     * @param string $name   表单名称
     * @param string $value  默认值
     * @param array $attribute  参数
     * @return string
     */
	static private function hidden($name, $value, $attribute) {
        $string = '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
        return $string;
	}

    /**
     * 单行文本
     * @param string $name 表单名称
     * @param string $value 默认值
     * @param array $attribute 附加属性
     * @return string
     */
    static private function password($name, $value, $attribute) {
        isset($attribute['css']) ? $attribute['css']:$attribute['css']='';
        isset($attribute['placeholder']) ? $attribute['placeholder']:$attribute['placeholder']='';

        $string = '<input class="col-xs-10 col-sm-5" '.$attribute['css'].' type="password" name="'.$name.'" value="'.$value.'" '.$attribute['placeholder'].' tabindex="0"' ;

		$string .= self::_buildAttribute($attribute);
        $string .= '>';
        return $string;
    }

    /**
     * 开启/关闭
     * @param string $name  表单名称
     * @param string $value 默认值
     * @param array $attribute 附加属性
     * @return string
     */
    static public function enabled($name, $value = 1, $attribute = array()) {
        $radios = array(1 => '开启', 0 => '关闭');
        $string = '';
        foreach ($radios as $key => $radio) {
            $checked = ($value == $key) ? ' checked' : '';
            $string .= '<label class="select-wrap"><input class="select-btn" type="radio" name="'.$name.'" value="'.$key.'"'.$checked. self::_buildAttribute($attribute).'/>'.$radio.'</label>';
        }
        return $string;
    }

    /**
     * 单选框
     * @param string $name  表单名称
     * @param string|array $checked 默认值
     * @param array $default 选项列表
     * @param string|array $disabled 禁止项目
     * @param array $attribute 附加属性
     * @return mixed
     */
    static public function radio($name = '', $value, $attribute = array()) {
		$items='';
		$colspan='';
		$disabled='';
		$_disabled='';
		$string='';
	    extract($attribute);
		
	
	    if(!is_array($items) || empty($items)) return false;
		
	    $colspan = max(1, intval($colspan));
		if(isset($disabled) && $disabled) {
			$disabled = (!is_array($disabled) && !empty($disabled)) ? explode(",", $disabled) : (array) $disabled;
		}
	   
	    $i = 1;
		$string='<div class="col-xs-10 col-sm-5 gboy-padding-0">';
	
	    foreach( $items as $key => $item ) {
		   if(isset($disabled) && $disabled)  $_disabled = (in_array($key, $disabled)) ? ' disabled' : '';
		    $_checked = ($value == $key) ? ' checked' : '';
		    unset($attribute['items']);
		    $string .= '<label><input class="ace" type="radio" name="'.$name.'" value="'.$key.'"'.$_checked.$_disabled. self::_buildAttribute($attribute).' /><span class="lbl">&nbsp;'.$item.'</span></label>';
		    if($i % $colspan == 0) $string .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		    $i++;
	    }
		$string.="</div>";
	    return $string;
    }

    /**
     * 复选框
     * @param string $name  表单名称
     * @param string|array $checked 默认值
     * @param array $default 选项列表
     * @param string|array $disabled 禁止项目
     * @param array $attribute 附加属性
     * @return mixed
     */
    static public function checkbox($name = '', $value = '', $attribute = array()) {
		$items='';
		$colspan='';
		$disabled='';
		$string='';
	    extract($attribute);
	    if(!is_array($items) || empty($items)) return false;
		
	    $colspan = max(1, intval($colspan));
	    $disabled = (!is_array($disabled) && !empty($disabled)) ? explode(",", $disabled) : (array) $disabled;
	    $checked = (!is_array($value) && !empty($value)) ? explode(",", $value) : (array) $value;

	    $i = 1;
		$string='<div class="col-xs-10 col-sm-5 gboy-padding-0">';
	    foreach( $items as $key => $item ) {
		    $_disabled = (in_array($key, $disabled)) ? ' disabled' : '';
		    $_checked = (in_array($key, $checked)) ? ' checked' : '';
		    $string .= '<label><input class="ace" type="checkbox" name="'.$name.'" value="'.$key.'"'.$_checked.$_disabled.' /><span class="lbl">&nbsp;'.$item.'</span></label>';
		    if($i % $colspan == 0) $string .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		    $i++;
	    }
		$string.='</div>';
	    return $string;
    }

    /**
     * 多行文本
     * @param string $name  表单名称
     * @param string $value 默认值
     * @param array $attribute 附加属性
     * @return string
     */
    static public function textarea($name, $value, $attribute = array()) {
		$placeholder='';
		if(is_array($attribute))  extract($attribute);
       
        return '<textarea class="col-xs-10 col-sm-5" name="'.$name.'" placeholder="'. $placeholder .'" '.self::_buildAttribute($attribute).'>'.$value.'</textarea>';
    }

    /**
     * 文件框
     * @param string $name  表单名称
     * @param string $value 默认值
     * @param array $attribute 附加属性
     * @return string
     */
    static public function file($name = '', $value = '', $attribute = array()) {
		$string='<div class="col-xs-10 col-sm-5 gboy-padding-0">';
        $string .= '<div class="widget-main" data-title="'.$value.'"><input type="file"  name="'.$name.'"></div>';
		$string.='</div>';
        return $string;
    }


    /**
     * 上传
     * @param string $name  表单名称
     * @param string $value 默认值
     * @param array $attribute 附加属性
     * @return string
     */
    static public function upload($name = '', $value = '', $attribute = array()) {
        $num=0;
        $input='';
        $area='';
        extract($attribute);

        $string = '<div class="col-xs-10 col-sm-5 gboy-padding-0"><input class="col-xs-10 col-sm-10" type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" tabindex="0"'.self::_buildAttribute($attribute).' />';
        $string .= '<button onclick="uploadImage('.$num.',\''.$input.'\',\''.$area.'\')"  style="padding:2px 0 1px 0;" class="btn btn-default col-xs-2 col-sm-2" type="button">浏览...</button></div>';

        return $string;
    }






    /**
     * 下拉框
     * @param string $name  表单名称
     * @param string|array $selected 默认值
     * @param array $default 选项列表
     * @param array $attribute  附加属性
     * @return string
     */
    static public function select($name, $value, $attribute = array()) {
		$items='';

	    extract($attribute);
	    if(!is_array($items) || empty($items)) return false;
	    $string = '<div class="col-xs-10 col-sm-5 gboy-padding-0"><select class="form-control" name="'.$name.'">';
	    foreach( $items as $key => $item ) {

		    $selected = ((string)trim($value) == (string)trim($key) )? 'selected' : '';
		    $string .= '<option value="'.$key.'" '.$selected.' >'.$item.'</option>';

	    }
	    $string .= '</select></div>';
        return $string;
    }

    /**
     * 日历控件
     * @param string $name  表单名称
     * @param string $value 默认值
     * @param string $format 时间格式
     * @param array $attribute  附加属性
     * @return string
     */
    static public function calendar($name, $value, $attribute = array()) {
	    extract($attribute);
	    $format = (!empty($format)) ? $format : 'YYYY-MM-DD'; //YYYY-MM-DD hh:mm:ss
	    $placeholder = (!empty($placeholder)) ? $placeholder : $format;
	    $skin = (!empty($skin)) ? $skin : 'danlan';
	    $string = '<input class="col-xs-10 col-sm-5 " type="text" name="'.$name.'" value="'.$value.'" placeholder="'.$placeholder.'" tabindex="0" '.self::_buildAttribute($attribute).' onclick="laydate({istime: true, format: \''.$format.'\' ,max: laydate.now()})">';
	    if(!defined( 'INIT_CALENDAR')) {
		    $string .= '<script type="text/javascript" src="'.__ROOT__.'static/js/laydate/laydate.js"></script>';
		    $string .= '<script type="text/javascript">laydate.skin(\''.$skin.'\');</script>';
		    define('INIT_CALENDAR', true);

	    }
	    return $string;
    }

    /**
     * 编辑器
     * @param string $name   表单名称
     * @param string $value  默认值
     * @param string $width 宽度
     * @param string $height 高度
     * @return string
     */
    static public function editor($name, $value='', $width ='100%', $height = '500',$allow_image = FALSE,$umsuffix='',$diytag = false) {
        $id = random(8);
        $string = '<script type="text/plain" id="'.$id.'" name="'.$name.'" style="width:'.$width.';height:'.$height.';">'.$value.'</script>';
        if(!defined('INIT_EDITOR')) {
            $string .= '<script type="text/javascript" charset="utf-8" src="'.__ROOT__.'static/js/editor/ueditor.config.js"></script>';
            $string .= '<script type="text/javascript" charset="utf-8" src="'.__ROOT__.'static/js/editor/ueditor.all.min.js"></script>';
            if($diytag){
                $string .= '<script type="text/javascript" charset="utf-8" src="'.__ROOT__.'static/js/editor/tag.js"></script>';
            }

            $string .= '<script type="text/javascript" charset="utf-8" src="'.__ROOT__.'static/js/editor/lang/zh-cn/zh-cn.js"></script>';
            define('INIT_EDITOR', true);
        }
        $width = (!empty($width)) ? $width : '100%';
        $height = (!empty($height)) ? $height : '500';

        $string .= '<script type="text/javascript">';

        $string .= 'var ue'.$umsuffix.' = UE.getEditor(\''.$id.'\', {
              textarea:\''.$name.'\'
             ,serverUrl:"'.url("attachment/index/editor").'"
             ,initialFrameWidth:\''.$width.'\'
             
            ,initialFrameHeight:\''.$height.'\'';
        if(!$diytag){
            $string .= ',toolbars:[[
            \'fullscreen\', \'source\', \'|\', \'undo\', \'redo\', \'|\',
            \'bold\', \'italic\', \'underline\', \'fontborder\', \'strikethrough\', \'superscript\', \'subscript\', \'removeformat\', \'formatmatch\', \'autotypeset\', \'blockquote\', \'pasteplain\', \'|\', \'forecolor\', \'backcolor\', \'insertorderedlist\', \'insertunorderedlist\', \'selectall\', \'cleardoc\', \'|\',
            \'rowspacingtop\', \'rowspacingbottom\', \'lineheight\', \'|\',
            \'customstyle\', \'paragraph\', \'fontfamily\', \'fontsize\', \'|\',
            \'directionalityltr\', \'directionalityrtl\', \'indent\', \'|\',
            \'justifyleft\', \'justifycenter\', \'justifyright\', \'justifyjustify\', \'|\', \'touppercase\', \'tolowercase\', \'|\',
            \'link\', \'unlink\', \'anchor\', \'|\', \'imagenone\', \'imageleft\', \'imageright\', \'imagecenter\', \'|\',
            \'simpleupload\', \'insertimage\', \'emotion\',  \'map\', \'gmap\', \'insertframe\', \'insertcode\', \'webapp\', \'pagebreak\', \'template\', \'background\', \'|\',
            \'horizontal\', \'date\', \'time\', \'spechars\', \'snapscreen\', \'wordimage\', \'|\',
            \'inserttable\', \'deletetable\', \'insertparagraphbeforetable\', \'insertrow\', \'deleterow\', \'insertcol\', \'deletecol\', \'mergecells\', \'mergeright\', \'mergedown\', \'splittocells\', \'splittorows\', \'splittocols\', \'charts\', \'|\',
            \'print\', \'preview\', \'searchreplace\', \'drafts\', \'help\']]';
        }else{

            if(!defined('IN_ADMIN')){
                $string .= ',toolbar:[\'fontfamily\', \'fontsize\', \'bold\',\'image\']';
            }

        }
        $string .= '});';
        $string .= '</script>';

        return $string;
    }


    /**
     * 颜色选框
     * @param string $name   表单名称
     * @param string $value  默认值
     * @param array $attribute  参数
        {
            color => 颜色值
            key   => 表单名
        }
     * @return string
     */
    static public function color($name, $value, $attribute = array()) {
        $string = '<input class="color-choose input_cxcolor" type="text" name="'.$name.'" value="'.$value.'" style="background-color: '.$value.';" readonly="readonly" '.self::_buildAttribute($attribute).'>';
        if(!defined('COLOR_INIT')) {
            $string .= '<script type="text/javascript" charset="utf-8" src="'.__ROOT__.'statics/js/cxcolor/jquery.cxcolor.min.js"></script>';
            $string .= '<link type="text/css" href="'.__ROOT__.'static/js/cxcolor/jquery.cxcolor.css"  rel="stylesheet">';
            $string .= '<script>$(".input_cxcolor").cxColor();</script>';
            define('COLOR_INIT', TRUE);
        }
        return $string;
    }

    /**
     *
     * @param type $options
     * @return string|boolean
     */
    static private function _parseAttribute($options = array()) {
	    $igrnore = array('name', 'value', 'label', 'descript');
		if(is_array($options)){
			
			foreach( $options as $key => $option ) {
				if(in_array($key, $igrnore)) unset($options[$key]);
			}
		}
	    return $options;
    }

    static private function _buildAttribute($options = array()) {
	    $string = '';
		if(is_array($options)){
			
			foreach( $options as $key => $option ) {
				$string .= ' '.$key.'="'.$option.'"';
			}
		}
	    return $string;
    }
}
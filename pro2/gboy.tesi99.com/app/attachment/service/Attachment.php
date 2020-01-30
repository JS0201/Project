<?php 
/**
 * gboyshop
 * ============================================================================
 * 版权所有 2010-2020 gboyshop，并保留所有权利。
 * ============================================================================
 * Author: gboy      
 * Date: 2017年10月19日
 */
namespace app\attachment\service;
use think\Model;
use think\Db;

class Attachment extends Model
{
    protected $_config = [];
	public function __construct(){
	    
	    
	    $this->model = model('attachment/Attachment');

	}


    public function config($_config = []) {

        $this->_config = $_config;
        return $this;
    }

    /**
     * 附件上传接口
     * @param 变量名 $field
     * @param 密钥 $code
     * @return mixed
     */
    public function upload($field, $filed = null, $iswrite = TRUE) {

        if(empty($field)) {
            $this->errors = L('file_upload_empty','attachment');
            return FALSE;
        }
        if($this->_config['mid'] < 1) {
            $this->errors = L('no_promission_upload','attachment');
            return FALSE;
        }
        $upload = new \org\Uploader($this->_config);
        $result = $upload->upload($field);
        if($result === FALSE) {
            $this->errors = $upload->errors;
            return FALSE;
        }
        return $result;
        //水印
        //$this->watermark($result->getpathname());
        //写入数据库
        //$this->write($result, $iswrite);
        //$this->file = $this->write($result, $iswrite);
        //if(is_null($filed)) return '/'.str_replace('\\','/',$result->getpathname());
        //return $this;
    }
    /***
     * 删除阿里云文件
     *
     */
    public function deleteFile($file) {
        $upload = new \org\Uploader($this->_config);
        $result = $upload->delete($file);
        if($result === FALSE) {
            $this->errors = $upload->errors;
            return FALSE;
        }
        return $result;
    }

    /**
     * 传回调写入
     * @param array $files 文件信息
     * @return mixed
     */
    public function write($file = [], $iswrite = true) {


        $info=$file->getInfo();

        list($width, $height) = getimagesize($file->getPathname());


        if(empty($file)) {
            $this->errors = lang('_param_error_');
            return FALSE;
        }
        if(!isset($file->aid)) {
            $data = array(
                'module'   => isset($this->_config['module']) && $this->_config['module'] ? $this->_config['module'] : MODULE_NAME,
                'catid'    => 0,
                'mid'      => (int) $this->_config['mid'],
                'name'     => $info['name'],
                'filename' => $file->getFilename(),
                'filepath' => $file->getSaveName(),
                'filesize' => $file->getSize(),
                'fileext'  => $file->getExtension(),
                'isimage'  => 1,
                'filetype' => $info['type'],
                'md5'      => $file->md5(),
                'sha1'     => $file->sha1(),
                'width'    => (int) $width,
                'height'   => (int) $height,
                'url'      => '/'.str_replace('\\','/',$file->getpathname()),
                'datetime' => time(),
                'clientip' => getip(),
            );
            if(defined('IN_ADMIN')) {
                $data['issystem'] = 1;
                $data['mid'] = ADMIN_ID;
            }
            if($iswrite === true) Db::name('attachment')->data($data)->insert();
            return $data;
        }
        return $file;
    }


    /**
     *
     * @param $fileurl
     */
    public function thumb($fileurl){

        $thumb_switch=config('cache.thumb_switch');
        $thumb_width=config('cache.thumb_width');
        $thumb_height=config('cache.thumb_height');
        $thumb_type=config('cache.thumb_type');

        if($thumb_switch){

            $image = \think\Image::open($fileurl);

            $image->thumb($thumb_width, $thumb_height,$thumb_type)->save($fileurl);
        }


    }


    /**
     * 水印
     * @param $fileurl
     */
    public function watermark($fileurl){

        $watermark_switch=config('cache.watermark_switch');
        $watermark_type=config('cache.watermark_type');
        $watermark_text=config('cache.watermark_text');
        $watermark_color=config('cache.watermark_color');
        $watermark_font=config('cache.watermark_font');
        $watermark_logo=config('cache.watermark_logo');
        $watermark_position=config('cache.watermark_position');
        $watermark_alpha=config('cache.watermark_alpha');

        if($watermark_switch){

            $image = \think\Image::open($fileurl);

            if(empty($watermark_type)){

                $image->text($watermark_text,ROOT_PATH.'data/watermark/font.ttf',$watermark_font,$watermark_color,$watermark_position)->save($fileurl);
            }else{

                $image->water(ROOT_PATH.'data/watermark/'.$watermark_logo,$watermark_position,$watermark_alpha)->save($fileurl);

            }

        }

    }











}

?>
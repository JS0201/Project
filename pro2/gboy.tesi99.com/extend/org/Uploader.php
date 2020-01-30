<?php

namespace org;
use OSS\OssClient;
use OSS\Core\OssException;
class Uploader
{
    protected $config = [
        'path' => 'uploads',
        'size' => 2097152,
        'ext' => ['jpg', 'gif', 'png', 'jpeg'],
        'mid' => 0,
    ];

    public function __construct($config = [])
    {

        $this->config = array_merge($this->config, $config);
    }

    public function upload($filename)
    {
        if (isset($_FILES[$filename])) {
            $name = $_FILES[$filename]['name'];
            $format = strrchr($name, '.');//截取文件后缀名如 (.jpg)
            /*判断图片格式*/
            $allow_type = ['.jpg', '.jpeg', '.gif', '.bmp', '.png'];
            if (! in_array($format, $allow_type)) {
                $this->errors = '文件格式不在允许范围内哦';
                return false;
            }
            // 尝试执行
            $config = config('aliyun_oss');
            //实例化对象 将配置传入
            $ossClient = new OssClient($config['KeyId'], $config['KeySecret'], $config['Endpoint']);
            //这里是有sha1加密 生成文件名 之后连接上后缀
            $fileName = 'uplaod/image/' . date("Ymd") . '/' . sha1(date('YmdHis', time()) . uniqid()) . $format;
            //执行阿里云上传
            $result = $ossClient->uploadFile($config['Bucket'], $fileName, $_FILES[$filename]['tmp_name']);
            if($result) {
                /*组合返回数据*/
                $arr = [
                    'oss_url' => $result['info']['url'],  //上传资源地址
                    'relative_path' => $fileName     //数据库保存名称(相对路径)
                ];
                return $arr['oss_url'];
            }else{
                $this->errors = '上传图片失败';
                return false;
            }
        }
        $this->errors = L('file_upload_empty','attachment');
        return false;
    }

    public function delete($file) {
        if(!$file) {
            $this->errors = '参数异常';
            return false;
        }
        $arr = explode('/', $file);
        $object = 'upload/image/'.end($arr);
        $config = config('aliyun_oss');
        //实例化对象 将配置传入
        $ossClient = new OssClient($config['KeyId'], $config['KeySecret'], $config['Endpoint']);
        $ossClient->deleteObject($config['Bucket'], $object);
        return true;
    }
}
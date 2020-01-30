<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\admin\service;
use think\Model;
use \tp5er\Backup;
class Database extends Model
{

    public $config=[];

    public function initialize()
    {

        $this->config=array(
            'path'     => ROOT_PATH.'data/dbbak/',//数据库备份路径
            'part'     => 20971520,//数据库备份卷大小
            'compress' => 1,//数据库备份文件是否启用压缩 0不压缩 1 压缩
            'level'    => 9 //数据库备份文件压缩级别 1普通 4 一般  9最高
        );

        $this->db=new Backup($this->config);

    }


    public function data_list(){

        return $this->db->dataList();

    }


    public function file_ist(){

        return $this->db->fileList();

    }


    public function export($data){
        if(!empty($data['sql_file_name'])){
            $filename= ['name' => $data['sql_file_name'], 'part' => 1];
        }else{
            $filename=null;
        }
        if(!empty($data['vol_size'])){
            $this->config['part']=$data['vol_size']*1024;
        }
        $this->db=new Backup($this->config);
        switch($data['type']){
            case 0:
                $tables= $this->db->dataList();
            break;

            case 1:
                $tables_string='gboy_goods_category,gboy_goods_index,gboy_goods_sku,gboy_goods_spu,gboy_member,gboy_member_deposit,gboy_category,gboy_content';
                $table=explode(',',$tables_string);
                foreach($table as $k=>$v){
                    $tables[$k]=['name'=>$v];
                }
            break;

            case 2:
                $tables_string='gboy_goods_index,gboy_goods_sku,gboy_goods_spu,gboy_member';

                $table=explode(',',$tables_string);

                foreach($table as $k=>$v){
                    $tables[$k]=['name'=>$v];
                }

            break;

            case 3:
                $tables= $data['tables'];

                if(!$tables || !is_array($tables)){

                    $this->errors='请选择要备份的数据表';
                    return false;
                }else{
                    foreach($tables as $k=>$v){
                        $tables[$k]=['name'=>$v];
                    }
                }

                break;
        }


        foreach($tables as $k=>$table){
            if(false===$this->db->setFile($filename)->backup($table['name'], 0)){
               $this->errors = $table['name'] . '备份失败';
               return false;
            }
        }

        // 生成log记录
        $content = '文件名：' . $filename['name'] . '.sql.gz';
        $name = lang("admin_log_site_export_database");
        AdminLog(3, $content, lang('admin_log_site_admin_database'), $name);

        return true;
    }


    public function file_list(){

        return $this->db->fileList();
    }


    public function download($time){

        return $this->db->downloadFile($time);
    }


    public function delfile($time){

        return $this->db->delFile($time);
    }

    public function optimize($tables){

        $res = $this->db->optimize($tables);
        if(!$res) return $res;

        // 生成log记录
        $content = AdminLoginFormat($tables);
        $name = lang("admin_log_site_optimize_database");
        AdminLog(3, $content, lang('admin_log_site_admin_database'), $name);

        return $res;
    }

    public function repair($tables){
        $res = $this->db->repair($tables);
        if(!$res) return $res;

        // 生成log记录
        $content = AdminLoginFormat($tables);
        $name = lang("admin_log_site_repair_database");
        AdminLog(3, $content, lang('admin_log_site_admin_database'), $name);

        return $res;
    }



}
<?php

namespace Admin\Controller;

use Common\Controller\AdminController;
use Think\Db;
use Org\Util\Database;

class DatabaseController extends AdminController {

    public function _initialize() {
        parent::_initialize();

        $this->backup_path = 'data/backup' . DIRECTORY_SEPARATOR;
    }

    /* 数据库管理 */

    public function index() {


        $list = M()->query('SHOW TABLE STATUS');

        $this->assign('list', $list);
        $this->display('export');
    }

    /**
     * 优化表
     * @param  String $tables 表名
     * @author 
     */
    public function optimize($tables = null) {
        $tables = $_REQUEST['tables'];

        if ($tables) {

            if (is_array($tables)) {
                $tables = implode('`,`', $tables);
                $list = M()->query("OPTIMIZE TABLE `{$tables}`");
                if ($list) {

                    showmessage('数据表优化完成', '', 1);
                } else {
                    showmessage('数据表优化出错请重试');
                }
            } else {
                $list = M()->query("OPTIMIZE TABLE `{$tables}`");
                if ($list) {
                    showmessage("数据表'{$tables}'优化完成！", U('index'), 1);
                } else {

                    showmessage("数据表'{$tables}'优化出错请重试", U('index'));
                }
            }
        } else {

            showmessage("请指定要优化的表", U('index'));
        }
    }

    /**
     * 修复表
     * @param  String $tables 表名
     * @author 
     */
    public function repair($tables = null) {
        $tables = $_REQUEST['tables'];

        if ($tables) {

            if (is_array($tables)) {
                $tables = implode('`,`', $tables);
                $list = M()->query("REPAIR TABLE `{$tables}`");
                if ($list) {
                    showmessage('数据表修复完成', '', 1);
                } else {
                    showmessage('数据表修复出错请重试');
                }
            } else {
                $list = M()->query("REPAIR TABLE `{$tables}`");
                if ($list) {
                    showmessage("数据表'{$tables}'修复完成", U('index'), 1);
                } else {
                    showmessage("数据表'{$tables}'修复出错请重试", U('index'));
                }
            }
        } else {
            showmessage("请指定要优化的表", U('index'));
        }
    }

    /**
     * 备份数据库（全部）
     */
    public function export() {

        $data = new \Org\Util\Database();


        $dir = $this->backup_path;

        // 创建目录
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true) or showmessage('创建文件夹失败', U('index'));
        }
        $size = 2024;
        $sql = '';

        // 备份全部表
        if (!$tables = M()->query("show table status from " . C("DB_NAME"))) {

            showmessage('读取数据库结构失败！', U('index'));
        }
        // 插入dump信息
        $sql .= $data->_base();
        // 文件名前面部分
        $filename = $dir . date('YmdHis');
        // 查出所有表
        $tables = M()->query('SHOW TABLES');
        // 第几分卷
        $p = 1;
        // 循环所有表
        foreach ($tables as $value) {
            foreach ($value as $v) {

                // 获取表结构
                $sql .= $data->_insert_table_structure($v);
                // 单条记录
                $sql .= $data->_insert_record($v);
            }
        }
        /*
          // 如果大于分卷大小，则写入文件
          if (strlen ( $sql ) >= $size * 1024) {

          $file = $filename . "_v" . $p . ".sql";
          // 写入文件
          if (!$data->_write_file ( $sql, $file, $dir )) {

          $this->error("备份卷-" . $p . "-失败",U('index'));

          }
          // 下一个分卷
          $p ++;

          }
         */

        // sql大小不够分卷大小
        if ($sql != "") {
            $filename .= ".sql";
            if ($data->_write_file($sql, $filename, $dir)) {


                showmessage('数据备份完成', U('index'), 1);
            } else {


                showmessage('数据备份失败', U('index'));
            }
        }
    }

    /**
     * 数据还原
     */
    function import() {
        $dir = opendir($this->backup_path);
        $i = 0;
        while (($filename = readdir($dir)) !== false) {
            $file = fopen('data/backup/' . $filename, "r");
            if ($filename != '.' && $filename != '..') {
                $list[$i]['fileurl'] = $this->backup_path . $filename;
                $list[$i]['filename'] = $filename;
                $list[$i]['fileinfo'] = fstat($file);
            }
            $i++;
        }

        $this->assign('list', $list);
        $this->display();
    }

    //删除备份
    function del() {

        $filename = $_REQUEST['filename'];
        if (empty($filename))
            showmessage('请选择备份文件', U('import'));
        if (is_array($filename)) {
            foreach ($filename as $key => $v) {
                @unlink($this->backup_path . $v);
            }
        } else {
            @unlink($this->backup_path . $filename);
        }

        showmessage("备份文件删除成功", U('import'), 1);
    }

}

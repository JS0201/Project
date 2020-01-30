<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5
 * Time: 14:29
 */
namespace app\ninestar\model;
use think\Model;
use think\Db;
class Init extends Model{
    public $db;
    public $errors;
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->db = Db::connect([
            'type'=> 'mysql',
            'hostname' => '127.0.0.1',
            'database' => 'ninestar',
            'username' => 'root',
            'password' => '6379bd1c20',
            'hostport' => '',
            'params'          => [],
            'charset'         => 'utf8',
            'prefix'          => 'star_',
        ]);
    }
}
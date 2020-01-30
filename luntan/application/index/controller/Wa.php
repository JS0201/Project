<?php
namespace app\index\controller;
use think\Db;

class Wa
{
  public function demo()
  {
    //1.获取数据库的连接实例/对象
    $link = Db::connect();
    $result=$link->table('zh_user')->select();
    dump($result);
  }
}
?>
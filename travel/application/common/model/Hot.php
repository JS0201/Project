<?php 
//zh_user表的模型
namespace app\common\model;
use think\Model;

class Hot extends Model 
{
	protected $pk = 'id';  
	protected $table = 'zh_hot';  

	protected $autoWriteTimestamp = true; 
	protected $createTime = 'create_time'; 
	protected $updateTime = 'update_time'; 
	protected $dateFormat = 'Y年m月d日'; 
   
	
}
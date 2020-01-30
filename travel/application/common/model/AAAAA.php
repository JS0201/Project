<?php 
namespace app\common\model;

use think\Model;

class AAAAA extends Model 
{
	protected $pk = 'id';
	protected $table = 'zh_aaaaa';
	protected $autoWriteTimestamp = true;
	protected $createTime = 'create_time';
	protected $updateTime = 'update_time';
	protected $dateFormat = 'Y年m月d日';
	protected $auto = [];
	// 仅新增时设置  
	protected $insert = ['create_time','status'=>1];
	//仅更新时设置
	protected $update = ['update_time'];

	  

}
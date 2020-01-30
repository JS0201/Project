<?php
namespace app\common\controller;
use think\Controller;
use think\Config;
use think\Request;
class Common extends Controller
{
	
	public function __construct()
    {
		parent::__construct();
	    define('MODULE_NAME', Request::instance()->module());
	    define('CONTROLLER_NAME', Request::instance()->controller());
	    define('ACTION_NAME', Request::instance()->action());
	    define('__ROOT__', '/');
		//Config::load(APP_PATH.'extra/common.php');


	}



}

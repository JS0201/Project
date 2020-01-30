<?php 
namespace app\common\controller;
use think\Controller;
use think\Facade\Session;
use think\facade\Request;
use app\common\model\Article; 
use app\common\model\Max;
use app\admin\common\model\Site;  

class Base extends Controller
{
	
    protected function initialize()
    {
        
        //显示分类导航
        $this->showNav();
        
    }


    //显示分类导航
    protected function showNav()
    {
        $cateList = Article::all(function($query){
            $query->where('status',1)->order('sort','asc');
        });
        $this->view->assign('cateList', $cateList);
        
    }


   

}













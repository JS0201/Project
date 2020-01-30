<?php
namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\Max;
use app\common\model\Article;
use app\common\model\AAAAA;
use app\common\model\Hot;
use think\facade\Request; 
use think\Db;

class Index extends Base
{
    //首页
    public function index()
    {
        
        return $this->fetch('index',['title'=>'数据展示']) ;
    }

    public function max()
    {
    	$all = Max::all();
    	$this->view->assign('title', '销量前十');
		$this->view->assign('max', $all);
		return $this->view->fetch();

    }
     public function hot()
    {   
        $hot = Hot::all();
        $this->view->assign('title', '热度代表性');
        $this->view->assign('hot', $hot);
        return $this->view->fetch();

    }
     public function ten()
    {
        $this->view->assign('title', '5A景区');
        return $this->view->fetch();

    }
    public function demo()
    {
        $AA = AAAAA::all();
        $this->view->assign('title', '销量前十');
        $this->view->assign('AA', $AA);
        return $this->view->fetch();

    }
    public function zhanshi1()
    {
        $this->view->assign('title', '5A景区的月销量前十名');
        return $this->view->fetch();

    }
     public function zhanshi2()
    {
        $this->view->assign('title', '人气最高的城市排名');
        return $this->view->fetch();

    }
     public function zhanshi3()
    {
        $this->view->assign('title', '月销量前十景区');
        return $this->view->fetch();

    } public function zhanshi4()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    }
     public function zhanshi5()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    }
    public function zhanshi6()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    }
    public function zhanshi7()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    }
    public function zhanshi8()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    }
    public function zhanshi9()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    }
    public function zhanshi10()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    }
    public function zhanshi11()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    }
    public function zhanshi12()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    } 
    public function zhanshi13()
    {
        $this->view->assign('title', '数据展示');
        return $this->view->fetch();

    }
}










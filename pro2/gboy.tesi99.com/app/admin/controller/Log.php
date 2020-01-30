<?php
namespace app\admin\controller;
use app\common\controller\Init;

class Log extends Init
{
	public function __construct(){
		parent::__construct();
		$this->model = model('admin/Log');
	}
    public function index()
    {
        //dump($this->model->getAll());
        $list1 = $this->model->getAll(array('action' => 1));
        $list2 = $this->model->getAll(array('action' => 2));
        $list3 = $this->model->getAll(array('action' => 3));
        $this->assign('list1', $list1);
        $this->assign('list2', $list2);
        $this->assign('list3', $list3);
        return $this->fetch();
    }
}

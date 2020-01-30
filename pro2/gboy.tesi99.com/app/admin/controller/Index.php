<?php
namespace app\admin\controller;
use app\common\controller\Init;
use think\Loader;
class Index extends Init
{
	public function __construct(){
		parent::__construct();
		$this->node = model('admin/Node','service');
		$this->member_statistics = model('statistics/Member','service');
		$this->order_statistics = model('statistics/Order','service');
	}
    public function index()
    {
       $nodes=$this->node->fetch_all_by_ids($this->admin['rules']);
       $nodes = list_to_tree($nodes);
       $this->assign('nodes',$nodes);
       return $this->fetch();
    }
	public function main(){
	    //统计
        $order_statistics=$this->order_statistics->build_sqlmap(array('days' => 7))->output('sales');


        $member_statistics=$this->member_statistics->count();

        $member_difday=($member_statistics['yesterday']>0)?($member_statistics['day']-$member_statistics['yesterday'])/$member_statistics['yesterday']*100:0;


	    //最新会员
	    $member_list=model('member/Member')->field('username,mobile,register_time')->order('id desc')->limit(5)->select();

	    $order_list=model('order/Order')->field('sn,user,real_amount,system_time')->order('id desc')->limit(5)->select();


        $this->assign('member_difday',$member_difday);
        $this->assign('order_statistics',$order_statistics);
        $this->assign('member_statistics',$member_statistics);
        $this->assign('member_list',$member_list);
        $this->assign('order_list',$order_list);
		return $this->fetch();
	}
    /**
     * 导航
     */
	public function public_current_pos(){
        $m=input('m','');
        $c=input('c','');
        $a=input('a','');
        $sqlmap=[];
        $sqlmap['m']=$m;
        $sqlmap['c']=Loader::parseName($c, 0, true);
        $sqlmap['a']=$a;
        $node_path= $this->node->get_parent_node($sqlmap);
        echo json_encode($node_path);
    }

}

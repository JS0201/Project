<?php
namespace app\admin\controller;
use app\common\controller\Init;
class Cache extends Init{
	//初始化

	public function _initialize(){
        parent::_initialize();
		
		
	}
	
	
	public function index(){
		



	    return $this->fetch();
		
	}



    public function clear(){

	    $type=input('type/a');


        $del_type=[0=>'runtime',1=>'runtime/cache',2=>'runtime/log',3=>'runtime/temp','session'];

        foreach($del_type as $k=>$v){

            if(in_array($k,$type)){

                remove_dir(ROOT_PATH.'data/'.$v);
            }

        }




        showmessage(lang('_operation_success_'), url('index'), 1);

    }
	
	
}
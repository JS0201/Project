<?php
namespace app\admin\controller;
use app\common\controller\Init;
class Database extends Init{


	public function _initialize(){
        parent::_initialize();

		$this->service = model('admin/Database','service');
	}
	
	
	public function index(){

	    $this->assign('list',$this->service->data_list());
	    return $this->fetch();
		
	}


	public function export(){

	    if(is_post()){
	        if(!$this->service->export(input('post.'))){
                showmessage($this->service->errors);
            }
            showmessage('数据备份完成',url('export'),1);
        }else{
            $config=$this->service->config;
	        $config['filename']=date('Ymd-His');
            $config['vol_size']=$config['part']/1024;
            $this->assign('tables',$this->service->data_list())->assign('config',$config);
            return $this->fetch();
        }



    }


    public function import(){
        $list=$this->service->file_list();

        $this->assign('list',$list);
        return $this->fetch();
    }


    public function download(){

        $time=input('time/d');
	    $this->service->download($time);
    }



    public function delfile(){
        $time=input('time/d');
	    $this->service->delfile($time);

        showmessage('备份文件删除成功！',url('import'),1);
    }



	public function optimize(){
	    $tables=input('tables/a');

        if(!$this->service->optimize($tables)){
            showmessage('数据优化出错请重试！');
        }
        showmessage('数据表优化完成！',url('index'),1);
    }

    public function repair(){


        $tables=input('tables/a');

        if(!$this->service->repair($tables)){
            showmessage('数据表修复出错请重试！');
        }
        showmessage('数据表修复完成！',url('index'),1);
    }


	
	
	
	
}
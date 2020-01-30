<?php 

namespace Member\Service;
use Think\Model;
class MemberLogService extends Model{

	public function __construct() {
		
		$this->member = M('member');
		$this->model =  D('Member_log');
		
		
	}
	
	
	
    /**
     * 列表
     * @sqlmap  [array]   sql条件
     * @page  [array]   当前页
     * @limit  [array]   每页条数
     * @return [type]
     */
	
    public function get_lists($sqlmap,$page,$limit){
    	$logs = $this->model->where($sqlmap)->page($page)->limit($limit)->order('dateline desc')->select();
		
	
		
    	$lists = array();
    	foreach ($logs AS $log) {
    		$lists[] = array(
    			'id' => $log['id'],
    			'username' => $log['username'],
    			'dateline' => $log['dateline'],
    			'value' => $log['value'],
    			'msg' => $log['msg']
    		);
    	}
    	return $lists;
    }
	
	
	
    /**
     * 查询条件
     * @params  [array]   sql条件
     * @return [type]
     */
    public function build_sqlmap($params){
        //$sqlmap['type'] = 'money';
        if($params['start']) {
            $time[] = array("GT", strtotime($params['start']));
        }
        if($params['end']) {
            $time[] = array("LT", strtotime(date('Y-m-d 23:59:59',strtotime($params['end']))));
        }
        if($time){
            $sqlmap['dateline'] = $time;
        }
        if($params['keywords']){
            $mid = $this->member->where(array('username' => $params['keywords']))->getField('id');
            if($mid > 0){
                $sqlmap['mid'] = (int)$mid;
            }else{
                $this->errors = '请输入正确会员名';
                return false;
            }
        }
        return $sqlmap;
    }
	
   
    /**
     * 条数
     * @param  [arra]   sql条件
     * @return [type]
     */
    public function count($sqlmap = array()){
        $result = $this->model->where($sqlmap)->count();
      
        return $result;
    }
	
	
}

?>
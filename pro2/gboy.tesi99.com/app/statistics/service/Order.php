<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\statistics\service;
use think\Model;

class Order extends Model{



	protected $where = [];
	protected $result = [];
	
	
    public function initialize()
    {
       $this->model = model('order/Order');
    }

	
	
	

	/**
	 * 组装搜索条件
	 * @param  array  $params
	 *         				$params[buyer_id] : 会员主键id (int)
	 *         				$params[days] : 最近{多少}天 (int)
	 *         				$params[start_time] : 开始时间 (int, 时间戳)
	 *         				$params[end_time] : 结束时间 (int, 时间戳)
	 * @return [obj]
	 */
	public function build_sqlmap($params = array()) {
		if(isset($params['buyer_id']) && is_numeric($params['buyer_id'])) {
            $this->where['buyer_id'] = $params['buyer_id'];
        }
        if (isset($params['days']) && $params['days'] > 0) {
        	$days = $params['days'];
        	$days -= 1;
        	$this->where['search']['time'] = array('BETWEEN',array(strtotime("-{$params['days']}day",strtotime(date('Y-m-d 00:00:00'))) ,time()));
        	$this->where['search']['days'] = $params['days'];
        } else if (isset($params['start_time']) && isset($params['end_time'])) {
	        $this->where['search']['time'] = array('BETWEEN',array($params['start_time'] ,$params['end_time']));
			//两个时间戳之间的天数
	        $this->where['search']['days'] = round(($params['end_time'] - $params['start_time'])/86400);
        }
        return $this;
	}
	


	/**
	 * 销售统计 
	 * @return [result] (本日、本周、本月、本年[、日期搜索]):订单数,订单销售额,人均客单价,已取消订单
	 */
	public function sales() {
		// 按日期搜索
		if ($this->where['search']) {
			$days = $this->where['search']['days'];
			$search = $this->where['search']['time'];
			unset($this->where['search']);
			$sqlmap['status'] = 1;
			$sqlmap['system_time'] = $search;
			$field = "FROM_UNIXTIME(system_time,'%Y-%m-%d') days,SUM(real_amount) amount,count(distinct buyer_id) as peoples,count(id) as orders";
			$_searchs = $this->model->where($sqlmap)->field($field)->group('days')->select();
			foreach ($_searchs as $k => $val) {
				$val['average'] = sprintf("%.2f",$val['amount']/$val['peoples']);
				$_searchs[$val['days']] = $val;
				unset($_searchs[$k]);
			}
			for ($i = 0; $i <= $days; $i++) {
				$today = date('Y-m-d',strtotime("+{$i}day",$search[1][0]));
				$this->result['search']['dates'][$i] = $today;
				$_amounts[] = isset($_searchs[$today]['amount']) ? $_searchs[$today]['amount'] : '0.00';
				$_orders[] = isset($_searchs[$today]['orders']) ? $_searchs[$today]['orders'] : '0';
				$_averages[] = isset($_searchs[$today]['average']) ? $_searchs[$today]['average'] : '0.00';
			}
			$this->result['search']['series']['amounts']  = $_amounts;
			$this->result['search']['series']['orders']   = $_orders;
			$this->result['search']['series']['averages'] = $_averages;
		}

		// 本日查询条件
		$start = strtotime(date('Y-m-d 00:00:00'));
		$end   = strtotime(date('Y-m-d 23:59:59'));
		$today = array('BETWEEN',array($start, $end));
		// 本周查询条件
		$start = mktime(0, 0, 0,date("m"),date("d")-date("w")+1,date("Y"));
		$end   = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
		$week  = array('BETWEEN',array($start ,$end));
		// 本月查询条件
		$start = strtotime(date('Y-m-01 00:00:00'));
		$end   = strtotime(date('Y-m-d H:i:s'));
		$month = array('BETWEEN',array($start ,$end));
		// 本年查询条件
		$start = strtotime(date('Y-01-01 00:00:00'));
		$end   = strtotime(date('Y-m-d H:i:s'));
		$year  = array('BETWEEN',array($start ,$end));

		/* 订单数 */
		$sqlmap = $this->where;
        $sqlmap['system_time'] = $today;
		$this->result['today']['orders'] = (int) $this->model->where($sqlmap)->count();

		$sqlmap['system_time'] = $week;
		$this->result['week']['orders'] = (int) $this->model->where($sqlmap)->count();

		$sqlmap['system_time'] = $month;
		$this->result['month']['orders'] = (int) $this->model->where($sqlmap)->count();

		$sqlmap['system_time'] = $year;
		$this->result['year']['orders'] = (int) $this->model->where($sqlmap)->count();

		$sqlmap['system_time']=['between',[strtotime(date('Y-m-d',strtotime("-1 day"))),strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))]];
        $this->result['yesterday']['orders'] = (int) $this->model->where($sqlmap)->count();

        $this->result['count']['orders'] = (int) $this->model->count();

		/* 订单销售额 */ 
		$sqlmap = $this->where;
		$sqlmap['status'] = 1;
		$sqlmap['system_time'] = $today;
		$this->result['today']['amount'] = sprintf("%.2f",$this->model->where($sqlmap)->sum("real_amount"));

		$sqlmap['system_time'] = $week;
		$this->result['week']['amount'] = sprintf("%.2f",$this->model->where($sqlmap)->sum("real_amount"));

		$sqlmap['system_time'] = $month;
		$this->result['month']['amount'] = sprintf("%.2f",$this->model->where($sqlmap)->sum("real_amount"));

		$sqlmap['system_time'] = $year;
		$this->result['year']['amount'] = sprintf("%.2f",$this->model->where($sqlmap)->sum("real_amount"));

		/* 人均客单价 */
		$sqlmap = $this->where;
		$sqlmap['system_time'] = $today;
		$peoples = (int) $this->model->where($sqlmap)->count('distinct buyer_id');
		$this->result['today']['average'] = $peoples ? sprintf("%.2f",$this->result['today']['amount']/$peoples) : 0.00;

		$sqlmap['system_time'] = $week;
		$peoples = (int) $this->model->where($sqlmap)->count('distinct buyer_id');
		$data = $this->model->where($sqlmap)->select();
		$this->result['week']['average'] = $peoples ? sprintf("%.2f",$this->result['week']['amount']/$peoples) : 0.00;

		$sqlmap['system_time'] = $month;
		$peoples = $this->model->where($sqlmap)->count('distinct buyer_id');
		$this->result['month']['average'] = $peoples ? sprintf("%.2f",$this->result['month']['amount']/$peoples) : 0.00;

		$sqlmap['system_time'] = $year;
		$peoples = (int) $this->model->where($sqlmap)->count('distinct buyer_id');
		$this->result['year']['average'] = $peoples ? sprintf("%.2f",$this->result['year']['amount']/$peoples) : 0.00;

		/* 已取消订单 */
		$sqlmap = $this->where;
		$sqlmap['status'] = array('GT',1);
		$sqlmap['system_time'] = $today;
		$this->result['today']['cancels'] = (int) $this->model->where($sqlmap)->count();

		$sqlmap['system_time'] = $week;
		$this->result['week']['cancels'] = (int) $this->model->where($sqlmap)->count();

		$sqlmap['system_time'] = $month;
		$this->result['month']['cancels'] = (int) $this->model->where($sqlmap)->count();

		$sqlmap['system_time'] = $year;
		$this->result['year']['cancels'] = (int) $this->model->where($sqlmap)->count();
		return $this;
	}
   

	/**
     * 输出统计结果
     * @param  string $fun_name 要统计的方法名（多个用 ，分割），默认统计所有结果
     * @return [result]
     */
    public function output($fun_name = '') {
        if (empty($fun_name)) {
            $this->sales();
        } else {
        	$fun_names = explode(',', $fun_name);
        	foreach ($fun_names as $name) {
        		if (method_exists($this,$name)) {
        			$this->$name();
        		}
        	}
        }
        return $this->result;
    }

}
<?php
/**
 * 		订单统计服务层
 */
 
namespace Statistics\Service;

//use Think\Model;

class StatisticsService {
	
	protected $where = array();
	protected $result = array();
	protected $payments_ch = array();
	
	
	public function __construct() {
		$this->order_model = M('order');
	
		$this->payments_ch = array(	'bank' => '支付宝网银直连',
						'alipay_escow' => '支付宝担保交易',
						'ws_wap' => '支付宝手机支付',
						'alipay' => '支付宝即时到账',
						'wechat_qr' => '微信扫码支付',
						'wechat_js' => '微信手机支付',
						'jdpay'    =>  '京东支付'
					);
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
			$sqlquery = $this->order_model->where($sqlmap)->field($field)->group('days')->buildSql();
			$_searchs = $this->order_model->query($sqlquery);
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
		$this->result['today']['orders'] = (int) $this->order_model->where($sqlmap)->count();

		$sqlmap['system_time'] = $week;
		$this->result['week']['orders'] = (int) $this->order_model->where($sqlmap)->count();

		$sqlmap['system_time'] = $month;
		$this->result['month']['orders'] = (int) $this->order_model->where($sqlmap)->count();

		$sqlmap['system_time'] = $year;
		$this->result['year']['orders'] = (int) $this->order_model->where($sqlmap)->count();

		/* 订单销售额 */ 
		$sqlmap = $this->where;
		$sqlmap['status'] = 1;
		$sqlmap['system_time'] = $today;
		$sqlmap['pay_status'] = 1;
		$this->result['today']['amount'] = sprintf("%.2f",$this->order_model->where($sqlmap)->sum("real_amount"));

		$sqlmap['system_time'] = $week;
		$this->result['week']['amount'] = sprintf("%.2f",$this->order_model->where($sqlmap)->sum("real_amount"));

		$sqlmap['system_time'] = $month;
		$this->result['month']['amount'] = sprintf("%.2f",$this->order_model->where($sqlmap)->sum("real_amount"));

		$sqlmap['system_time'] = $year;
		$this->result['year']['amount'] = sprintf("%.2f",$this->order_model->where($sqlmap)->sum("real_amount"));

		/* 人均客单价 */
		$sqlmap = $this->where;
		$sqlmap['system_time'] = $today;
		$peoples = (int) $this->order_model->where($sqlmap)->count('distinct buyer_id');
		$this->result['today']['average'] = sprintf("%.2f",$this->result['today']['amount']/$peoples);

		$sqlmap['system_time'] = $week;
		$peoples = (int) $this->order_model->where($sqlmap)->count('distinct buyer_id');
		$data = $this->order_model->where($sqlmap)->select();
		$this->result['week']['average'] = sprintf("%.2f",$this->result['week']['amount']/$peoples);

		$sqlmap['system_time'] = $month;
		$peoples = $this->order_model->where($sqlmap)->count('distinct buyer_id');
		$this->result['month']['average'] = sprintf("%.2f",$this->result['month']['amount']/$peoples);

		$sqlmap['system_time'] = $year;
		$peoples = (int) $this->order_model->where($sqlmap)->count('distinct buyer_id');
		$this->result['year']['average'] = sprintf("%.2f",$this->result['year']['amount']/$peoples);

		/* 已取消订单 */
		$sqlmap = $this->where;
		$sqlmap['status'] = array('GT',1);
		$sqlmap['system_time'] = $today;
		$this->result['today']['cancels'] = (int) $this->order_model->where($sqlmap)->count();

		$sqlmap['system_time'] = $week;
		$this->result['week']['cancels'] = (int) $this->order_model->where($sqlmap)->count();

		$sqlmap['system_time'] = $month;
		$this->result['month']['cancels'] = (int) $this->order_model->where($sqlmap)->count();

		$sqlmap['system_time'] = $year;
		$this->result['year']['cancels'] = (int) $this->order_model->where($sqlmap)->count();
		return $this;
	}
	
	/* 地区订单统计 */
	/*
	public function districts() {
		// 组装省级地区为一维数组
		$districts = $this->load->service('admin/district')->get_children(0);
		$arr = $areas = array();
		foreach ($districts as $k => $v) {
			if ($v['id'] == 820000) {
				$macao = $v;
				continue;
			}
			$arr[] = $this->load->service('admin/district')->get_children($v['id']);
		}
		foreach ($arr as $val) {
			foreach ($val as $v) {
				$areas[] = $v;
			}
		}
		if ($macao) $areas[] = $macao;
		foreach ($areas as $key => $area) {
			$sqlmap['status'] = 1;
			$sqlmap['_string'] = "FIND_IN_SET($area[id], `address_district_ids`)";
			$areas[$key]['value'] = (int) $this->order_model->where($sqlmap)->count();
		}
		$this->result['districts'] = $areas;
		return $this;
	}
	*/

	/* 支付方式订单统计 */
	public function payments() {
		$payments = $this->load->service('pay/payment')->fetch_all();
		$payments = array_keys($payments);
		$pays_count = array();
		foreach ($payments as $k => $code) {
			$pays_count[$k]['code'] = $code;
			$pays_count[$k]['name'] = $this->payments_ch[$code];
			$pays_count[$k]['value'] = (int) $this->order_model->where(array('pay_method' => $code))->count();
		}
		$this->result['payments'] = $pays_count;
		return $this;
	}
	
	
	/**
     * 输出统计结果
     * @param  string $fun_name 要统计的方法名（多个用 ，分割），默认统计所有结果
     * @return [result]
     */
    public function output($fun_name = '') {
        if (empty($fun_name)) {
            //$this->sales()->districts()->payments();
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

    public function get_data(){
    	$datas = array();
		/* 订单提醒 */
		$datas['orders'] = D('Order/Order')->out_counts();
		/* 商品管理 */
		$datas['goods']['goods_in_sales'] = D('Goods/Goods_spu','Service')->count_spu_info(1);
		$datas['goods']['goods_load_online'] = D('Goods/Goods_spu','Service')->count_spu_info(0);
		$datas['goods']['goods_number_warning'] = D('Goods/Goods_spu','Service')->count_spu_info(2);
		/* 待处理咨询 */
		//$datas['consult_load_do'] = D('Goods/Goods_consult','Service')->handle();
		/* 资金管理 */
		$datas['sales'] = $this->output('sales');
		/* 注册会员总数 */
		$datas['member_total'] = M('member')->count();
		/* 数据库大小 */
		$querysql = "select round(sum(DATA_LENGTH/1024/1024)+sum(DATA_LENGTH/1024/1024),2) as db_length from information_schema.tables where table_schema='".C('DB_NAME')."'";
		$datas['dbsize'] = M('member')->query($querysql);
		return $datas;
    }


}
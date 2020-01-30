<?php	 

 return  array (
  'app_debug' => true,
  'group_id' => 
  array (
    0 => '',
    1 => '普通用户',
    2 => 'vip会员',
    3 => '初级合伙人',
    4 => '中级合伙人',
    5 => '高级合伙人',
  ),
  'money_type' => 
  array (
    0 => '',
    1 => 'money',
    2 => 'money',
    3 => 'shop_integral',
  ),
  'deposit_return' => 
  array (
    3 => 
    array (
      0 => '10',
      1 => '1000',
      2 => '2000',
    ),
    4 => 
    array (
      0 => '20',
      1 => '10000',
      2 => '20000',
    ),
    5 => 
    array (
      0 => '20',
      1 => '50000',
      2 => '100000',
    ),
  ),
  'transaction_type' => 
  array (
    0 => '',
    1 => '支付订单',
    2 => '直推收益',
    3 => '团队收益',
    4 => '押金退还',
    5 => '转账支出',
    6 => '转账收入',
    7 => '充值到账',
    8 => '提现',
    9 => '提现退回',
  ),
  'check_status' => 
  array (
    0 => '',
    1 => '审核中',
    2 => '审核通过',
    3 => '审核失败',
  ),
  'transaction_style' => '(2,3)',
  'received' => 
  array (
    0 => '',
    1 => '银行卡',
    2 => '支付宝',
    3 => '微信',
  ),
  'wx' => 
  array (
    'appId' => 'wx6c88d239b9306214',
    'secret' => 'ea6eaf64cdc9ad3a03e98a88c71b516d',
  ),
  'aliyun_oss' => 
  array (
    'KeyId' => 'LTAIYr4tUBcLEb2V',
    'KeySecret' => 'yyDCl7LJ1GZGhjRuF5ZXnT2H66p8Ky',
    'Endpoint' => 'oss-cn-shenzhen.aliyuncs.com',
    'Bucket' => 'tesi',
    'Url' => 'http://tesi.oss-cn-shenzhen.aliyuncs.com/',
  ),
  'app_trace' => false,
);
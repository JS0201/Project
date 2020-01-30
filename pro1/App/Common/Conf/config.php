<?php

return array(
    //'配置项'=>'配置值'
    /* 默认 */
    'TMPL_L_DELIM' => '<{',
    'TMPL_R_DELIM' => '}>',
    'TMPL_FILE_DEPR' => '_',
    'TMPL_PATH' => 'template',
    //'TMPL_DETECT_THEME' => true,  // 多端请设置true
    //'THEME_LIST'   => 'default,wap', //可用主题列表
    '_DEFAULT_THEME' => 'wap',
    'URL_MODEL' => 0,
    'URL_HTML_SUFFIX' => '', //html后缀
    'TMPL_CACHE_ON' => false, //关闭缓存
    'URL_CASE_INSENSITIVE' => true, //url不区分大小写
    'TMPL_STRIP_SPACE' => false, //是否去除模板文件里面的html空格与换行
    //语言包
    //语言包配置
    'LANG_SWITCH_ON'    => true,                 //开启语言包功能
    'LANG_AUTO_DETECT'  => true,                 // 自动侦测语言
    'DEFAULT_LANG'      => 'zh',              // 默认语言
    'LANG_LIST'         => 'en-us,zh-cn,zh-tw',  //必须写可允许的语言列表
    'VAR_LANGUAGE'      => 'l',                  // 默认语言切换变量

    /* 模块  */
    'MODULE_ALLOW_LIST' => array('Home',
        'Admin',
        'Goods',
        'Order',
        'Pay',
        'Content',
        'Member',
        'Ads',
        'Comment',
        'Statistics',
        'Peer',
        'Test'
    ),
    'DEFAULT_MODULE' => 'Member',
    /* 调试 */
//	'TMPL_EXCEPTION_FILE'=>'404.html', // 定义公共错误模板
    'SHOW_PAGE_TRACE' =>false, //开启调试信息
    'SHOW_ERROR_MSG' => false, // 显示错误信息
//    'ERROR_PAGE' => '404.html',
    'LOAD_EXT_CONFIG' => 'conn,config_cache,template',

    'usdt'=>[
        'url'=>"http://131.153.50.106:8080/coinServer/httpServer?",
        "app_key"=>'weijiaoyi',
        "app_secret"=>"dfd66b8c6f5a60fa6ec4201e0d4f909bc09e1793b383cb3cb8d92227bc63547c",
        "password"=>"",
    ],
);

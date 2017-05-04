<?php
return array (
	// 不区分大小写
	'URL_CASE_INSENSITIVE' => true,
	
    // 添加数据库配置信息
    'DB_TYPE'=>'mysql',// 数据库类型
    'DB_HOST'=>'127.0.0.1',// 服务器地址
    // 'DB_HOST'=>'localhost',// 服务器地址 
    'DB_NAME'=>'liuyan',// 数据库名
    // 'DB_NAME'=>'co',// 数据库名
    'DB_USER'=>'root',// 用户名
     'DB_PWD'=>'',// 用户名
//    'DB_PWD'=>'csens@a808',// 密码
    'DB_PORT'=>3306,// 端口
    'DB_PREFIX'=>'cos_',// 数据库表前缀
    'DB_CHARSET'=>'utf8',// 数据库字符集



    'LOAD_EXT_CONFIG' => 'email,verify,sdk_config,search_key', 
    'REAL_PATH' => $_SERVER['DOCUMENT_ROOT'].__ROOT__,    //网站绝对路劲

    'AUTH_CONFIG' => array(
        'AUTH_USER' => 'admin'
    ),

);

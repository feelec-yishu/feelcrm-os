<?php
return array
(
	'MODULE_ALLOW_LIST'		=> ['Index','Mobile','Weixin','Stage','Api','Crm','CrmMobile','CrmWeixin'],

	'DEFAULT_MODULE'		=> 'Index',

	'LOAD_EXT_CONFIG'		=> 'define,route,socket,orderby,trigger,audit,sla,database,crm_database,redis,session,crm_temp_cn,crm_temp_en,crm_temp_jp,crm_export,defaultField',

    'ERROR_PAGE'            => '/Public/error.html',

    'DEFAULT_APP'           => 'Feelcrm',

    'COOKIE_PREFIX'         =>  'feel_',      // Cookie前缀 避免冲突

	'COOKIE_HTTPONLY'       => 1,

//	  是否线上环境
	'ONLINE'                => false,

//    SQL解析缓存
    'DB_SQL_BUILD_CACHE'    => true,

    'DB_SQL_BUILD_LENGTH'   => 50,

//    TRACE信息
	'SHOW_PAGE_TRACE'       => false,

//    URL设置
    'URL_MODEL'             => 2,

    'URL_HTML_SUFFIX'       => '',

    /*'URL_ROUTER_ON'         => true,*/

    'URL_CASE_INSENSITIVE'  => false, //url是否区分大小写

    /*'URL_ROUTE_RULES'       => array(),*/

//    缓存设置
	'DATA_CACHE_SUBDIR'     => false,	// 使用子目录缓存 (自动根据缓存名称的哈希值创建子目录)

    'LOG_RECORD'            => true, // 开启日志记录

    'LOG_LEVEL'             =>'EMERG,ALERT,ERR', // 只记录EMERG ALERT ERR 错误

	'SHOW_ERROR_MSG'        => true, //在关闭Debug后，仍然在页面显示错误信息

	'NEED_TOKEN'            => true,//开启后，访问会员端需要token参数

	'WEB_SOURCE'            => 'FeelCRM',

	'SMS_SIGN'              => 'FeelCRM',

	'WEB_TITLE'             => 'FeelCRM 客户管理系统',

	'FAQ_TITLE'             => '客户服务支持中心 - FeelCRM 客户管理系统',

	'LOGIN_TITLE'           => '菲莱克斯，让工作更简单',

	'PC_ICON_HTTP'          => 'https://at.alicdn.com/t/font_545967_2xo963u1x5z.css',

	'MOBILE_ICON_HTTP'      => 'https://at.alicdn.com/t/font_732216_d94l47mv3bf.css',
);

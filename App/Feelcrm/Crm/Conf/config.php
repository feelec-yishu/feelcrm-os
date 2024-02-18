<?php
return [
	//后台公共文件路径
    'TMPL_PARSE_STRING'	=>  [

		'__PUBLIC__'		=> __ROOT__.'/Public',

		'CRM_PUBLIC_CSS'	=>__ROOT__.'/Public/crm/css',

        'CRM_PUBLIC_JS'		=> __ROOT__.'/Public/crm/js',

		'FACE_IMAGE' 		=> __ROOT__.'/Attachs/face',
    ],

	'LOAD_EXT_CONFIG'		=> 'defaultField,regionJson',

	'TMPL_ACTION_SUCCESS'	=> 'Public:success',

	'TMPL_ACTION_ERROR'	 	=> 'Public:error',

	'SESSION_AUTO_START'	=> true,//session自动开关
];
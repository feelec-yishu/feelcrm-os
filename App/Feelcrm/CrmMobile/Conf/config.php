<?php
return [

	'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称

	'DEFAULT_ACTION'        =>  'index', // 默认操作名称

    'TMPL_PARSE_STRING' =>  [

		'__PUBLIC__'		=> __ROOT__.'/Public',

        'MOBILE_PUBLIC_CSS' => __ROOT__.'/Public/mobile/css',

        'MOBILE_PUBLIC_IMG' => __ROOT__.'/Public/mobile/img',

        'MOBILE_PUBLIC_JS'	=> __ROOT__.'/Public/mobile/js',

		'FACE_IMAGE'		=> __ROOT__.'/Attachs/face',
    ],

	'SESSION_AUTO_START'	=> true,//session自动开关

	'URL_HTML_SUFFIX'		=> '',

	'TMPL_ACTION_SUCCESS'	=> 'Public:success',

	'TMPL_ACTION_ERROR'		=> 'Public:error',
];
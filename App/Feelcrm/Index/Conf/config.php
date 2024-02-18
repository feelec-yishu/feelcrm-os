<?php
return [
	//后台公共文件路径
    'TMPL_PARSE_STRING'	=>  [

		'__PUBLIC__'		=> __ROOT__.'/Public',

		'INDEX_PUBLIC_CSS'	=> __ROOT__.'/Public/index/css',

        'INDEX_PUBLIC_JS'	=> __ROOT__.'/Public/index/js',

        'INDEX_IMG'         => __ROOT__.'/Public/index/img',

		'FACE_IMAGE' 		=> __ROOT__.'/Attachs/face',
    ],

	'SESSION_AUTO_START'	=> true,//session自动开关

	'TMPL_ACTION_SUCCESS'	=> 'Public:success',

	'TMPL_ACTION_ERROR'		=> 'Public:error',

    /*'URL_ROUTE_RULES'       => [
	    'u-login'                           => ['Login/index'],
	    'u-log-in'                          => ['Login/loging'],
	    'u-logout'                          => ['Login/logout'],
	    'u-home'                            => ['Index/index'],
	    'u-reg'                             => ['Register/index'],
	    'u-reg-code'                        => ['Register/sendVerifyCode'],
	    'u-reg-submit'                      => ['Register/create'],
	    'u-reset'                           => ['Forget/index'],
	    'u-reset-pwd/:way'                  => ['Forget/resetPassword'],
	    'u-reset-submit'                    => ['Forget/resetPassword'],
	    'u-reset-code'                      => ['Forget/sendVerifyCode'],
	    'u-reset-success'                   => ['Forget/reset_success'],
	    'export-member'                     => ['Customer/export'],
    ]*/
];

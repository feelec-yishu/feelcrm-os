<?php
return [
    'URL_ROUTER_ON'         => true,

    'URL_ROUTE_RULES'       => [
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
	    # Mobile
	    'm-login'                           => ['Mobile/Login/index'],
	    'm-log-in'                          => ['Mobile/Login/loging'],
	    'm-logout'                          => ['Mobile/Login/logout'],
	    'm-home'                            => ['Mobile/Index/index'],
	    'm-reg'                             => ['Mobile/Register/index'],
	    'm-reg-code'                        => ['Mobile/Register/sendVerifyCode'],
	    'm-reg-submit'                      => ['Mobile/Register/create'],
	    # Weixin
	    'w-login'                           => ['Weixin/Login/index'],
	    'w-login/:login_token'              => ['Weixin/Login/index'],
	    'w-log-in'                          => ['Weixin/Login/loging'],
	    'w-logout'                          => ['Weixin/Login/logout'],
	    'w-home'                            => ['Weixin/Index/index'],
	    'w-reg'                             => ['Weixin/Register/index'],
	    'w-reg-code'                        => ['Weixin/Register/sendVerifyCode'],
	    'w-reg-submit'                      => ['Weixin/Register/create'],
    ]
];

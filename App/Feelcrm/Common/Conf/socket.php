<?php
// +----------------------------------------------------------------------

// | FeelCRM开源客户管理系统

// +----------------------------------------------------------------------

// | 欢迎阅读学习系统程序代码，您的建议反馈是我们前进的动力

// | 开源版本仅供技术交流学习，请务必保留界面版权logo

// | 商业版本务必购买商业授权，以免引起法律纠纷

// | 禁止对系统程序代码以任何目的，任何形式的再发布

// | gitee下载：https://gitee.com/feelcrm_gitee

// | github下载：https://github.com/feelcrm-github

// | 开源官网：https://www.feelcrm.cn

// | 成都菲莱克斯科技有限公司 版权所有 拥有最终解释权

// +----------------------------------------------------------------------
return [
	'SOCKET_PORT'      => 7000,
	'HTTP_PORT'        => 7050,
	'SOCKET_IO_URL'    => C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').':7000',
    'PUSH_URL'         => C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').':7050',//推送服务地址
	'WHITE_LIST'       => C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN'),
	// SSL 上下文
	'CONTEXT' => [
		'ssl' => [
			'local_cert'  => '/www/server/panel/vhost/cert/base.feelcrm.cc/fullchain.pem', // 服务器的证书绝对路径
			'local_pk'    => '/www/server/panel/vhost/cert/base.feelcrm.cc/privkey.pem', // 服务器的证书绝对路径
			'verify_peer' => false,
		]
	],
];

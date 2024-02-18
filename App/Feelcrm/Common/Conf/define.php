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

	'HTTP_PROTOCOL' => 'http',

	'HOST_DOMAIN'   => 'base.feelcrm.cc',

    'LANG_SWITCH_ON'   => true,

    'LANG_AUTO_DETECT' => true,

	'LANG_AUTO_BROWSER' => true,

    'LANG_LIST'        => 'zh-cn,en-us,ja-jp',

    'VAR_LANGUAGE'     => 'l',

    'DEFAULT_LANG'     => 'zh-cn',
	
	// Accept-Language转义为对应语言包名称
	'ACCEPT_LANGUAGE'  => [
		'zh'           => 'zh-cn',
		'zh-hans-cn'   => 'zh-cn',
		'zh-tw'        => 'zh-cn',
		'en-gb'        => 'en-us'
	]
];

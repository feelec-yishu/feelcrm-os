<?php
return [

//    SESSION配置
	'SESSION_AUTO_START'    => true, // 是否自动开启Session

	'SESSION_PREFIX'        => 'FeelCRM:V3:', //session前缀

	'SESSION_TYPE'          => 'Redis', //session类型

	'SESSION_REDIS_DB'      => 3, //session使用的redis数据库

	'SESSION_PERSISTENT'    => 1, //是否长连接(对于php来说0和1都一样)

	'SESSION_CACHE_TIME'    => 1, //连接超时时间(秒)

	'SESSION_EXPIRE'        => 28800, //session有效期(单位:秒) 0表示永久缓存
];

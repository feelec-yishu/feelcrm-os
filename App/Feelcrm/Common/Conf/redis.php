<?php
return [
    'DATA_CACHE_PREFIX'     => 'FeelCRM:V3:',//缓存前缀

    'DATA_CACHE_TYPE'       => 'Redis',//默认动态缓存为Redis

    'REDIS_RW_SEPARATE'     => false, //Redis读写分离 true 开启

    'REDIS_PREFIX'          => 'FeelCRM:V3:',//redis前缀

    'REDIS_TIMEOUT'         => '300',//超时时间

    'REDIS_PERSISTENT'      => false,//是否长连接 false=短连接

    'REDIS_HOST'            => '127.0.0.1', //redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；

    'REDIS_PORT'            => '6379',//端口号

    'REDIS_AUTH'            => '123456',//AUTH认证密码

    'REDIS_DBINDEX'         => 2,//Redis储存数据库编号

    'DATA_CACHE_TIME'       => 28800,      // 数据缓存有效期 0表示永久缓存
];

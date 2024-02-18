<?php
namespace WorkWeixin;

class Basic
{
    protected static $baseUrl = 'https://qyapi.weixin.qq.com';

    protected static $corpId = '';

    protected static $corpSecret = '';

    protected static $agentId = '';

    public function __construct($config)
    {
        self::$corpId = trim($config['corpid'],' ');

        self::$corpSecret = trim($config['secret'],' ');

        self::$agentId = trim($config['agentid'],' ');
    }
}

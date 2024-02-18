<?php
namespace Ding;

class Basic
{
    protected static $baseUrl = 'https://oapi.dingtalk.com';

    protected static $corpId = '';

    protected static $corpSecret = '';

    protected static $appKey = '';

    protected static $appSecret = '';

    protected static $agentId = '';

    public function __construct($dingConfig)
    {
        self::$corpId = trim($dingConfig['corpid'],' ');

        self::$corpSecret = trim($dingConfig['corp_secret'],' ');

        self::$appKey = trim($dingConfig['app_key'],' ');

        self::$appSecret = trim($dingConfig['app_secret'],' ');

        self::$agentId = trim($dingConfig['agentid'],' ');
    }
}
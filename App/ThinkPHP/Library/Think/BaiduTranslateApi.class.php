<?php
/***************************************************************************

 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
**************************************************************************/
namespace Think;

class BaiduTranslateApi
{
    protected $curl_timeout = 10;

    protected $url = 'http://api.fanyi.baidu.com/api/trans/vip/translate';

    protected $app_id = '20180907000204071';

    protected $secret_key = 'VWB_P8PrOK067osZSmhY';


    function __construct($config)
    {
        $this->app_id       = $config['app_id'];

        $this->secret_key   = $config['secret_key'];
    }


    //翻译入口
    function translate($query, $from, $to)
    {
        if(is_array($query))
        {
            $result = [];

            foreach($query as $k=>$v)
            {
                $args = [
                    'q'     => $v,
                    'appid' => $this->app_id,
                    'salt'  => rand(10000,99999),
                    'from'  => $from,
                    'to'    => $to,
                ];

                $args['sign'] = $this->buildSign($v,$args['salt']);

                $data[$k] = $this->call($args);

                $result[$k] = json_decode($data[$k], true);

            }
        }
        else
        {
            $args = [
                'q'     => $query,
                'appid' => $this->app_id,
                'salt'  => rand(10000,99999),
                'from'  => $from,
                'to'    => $to,
            ];

            $args['sign'] = $this->buildSign($query,$args['salt']);

            $result = $this->call($args);

            $result = json_decode($result, true);
        }

        return $result;
    }


//加密
    function buildSign($query,$salt)
    {
        $str = $this->app_id . $query . $salt . $this->secret_key;

        $result = md5($str);

        return $result;
    }


//发起网络请求
    function call($args=null, $method="post", $testflag = 0, $headers=array())
    {
        $result = false;

        $i = 0;

        while($result === false)
        {
            if($i > 1) break;

            if($i > 0) sleep(1);

            $result = $this->callOnce($this->url, $args, $method, false, $headers);

            $i++;
        }

        return $result;
    }



    function callOnce($url, $args=null, $method="post", $withCookie = false, $headers=array())
    {
        $ch = curl_init();

        if($method == "post")
        {
            $data = $this->convert($args);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            curl_setopt($ch, CURLOPT_POST, 1);
        }
        else
        {
            $data = $this->convert($args);

            if($data)
            {
                if(stripos($url, "?") > 0)
                {
                    $url .= "&$data";
                }
                else
                {
                    $url .= "?$data";
                }
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if(!empty($headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if($withCookie)
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }

        $r = curl_exec($ch);

        curl_close($ch);

        return $r;
    }



    function convert(&$args)
    {
        $data = '';

        if (is_array($args))
        {
            foreach ($args as $key=>$val)
            {
                if (is_array($val))
                {
                    foreach ($val as $k=>$v)
                    {
                        $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                    }
                }
                else
                {
                    $data .="$key=".rawurlencode($val)."&";
                }
            }
            return trim($data, "&");
        }

        return $args;
    }
}

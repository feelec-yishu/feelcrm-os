<?php
namespace WorkWeixin\api;

use WorkWeixin\Basic;

use WorkWeixin\util\Http;

use WorkWeixin\util\Cache;

use WorkWeixin\util\Log;

class Auth extends Basic
{
    public static function getAccessToken($company_id)
    {
        /**
         * 缓存accessToken。accessToken有效期为两小时，需要在失效前请求新的accessToken（注意：以下代码没有在失效前刷新缓存的accessToken）。
         */
        $accessToken = Cache::get('corp_access_token',$company_id);

        if(!$accessToken)
        {
            $response = Http::get('/cgi-bin/gettoken', ['corpid' => Basic::$corpId, 'corpsecret' => Basic::$corpSecret]);

            if($response->errcode > 0)
            {
                return $response;
            }
            else
            {
                $accessToken = $response->access_token;

                Cache::set('corp_access_token', $accessToken,$company_id);
            }
        }

        return $accessToken;
    }


	/**
	 * 缓存jsTicket。jsTicket有效期为两小时，需要在失效前请求新的jsTicket（注意：以下代码没有在失效前刷新缓存的jsTicket）。
	 * @param $accessToken
	 * @param $company_id
	 * @return bool|mixed
	 */
    public static function getTicket($accessToken,$company_id)
    {
        $jsticket = Cache::getJsTicket($company_id);

        if (!$jsticket)
        {
            $response = Http::get('/get_jsapi_ticket', ['type' => 'jsapi', 'access_token' => $accessToken]);

            self::check($response);

            $jsticket = $response->ticket;

            Cache::setJsTicket($jsticket,$company_id);
        }

        return $jsticket;
    }




    public static function curPageURL()
    {
        $pageURL = 'http';

        if (array_key_exists('HTTPS',$_SERVER)&&$_SERVER["HTTPS"] == "on")
        {
            $pageURL .= "s";
        }
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80")
        {
            $pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        }
        else
        {
            $pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }



    public static function getConfig($company_id)
    {
        $nonceStr = uniqid();

        $timeStamp = time();

        $url = self::curPageURL();

        $corpAccessToken = self::getAccessToken($company_id);

        if (!$corpAccessToken)
        {
            Log::e("[getConfig] ERR: no corp access token");
        }

        $ticket = self::getTicket($corpAccessToken,$company_id);

        $signature = self::sign($ticket, $nonceStr, $timeStamp, $url);

        $config = [
            'url'       => $url,
            'agentId'   => Basic::$agentId,
            'corpId'    => Basic::$corpId,
            'timeStamp' => $timeStamp,
            'nonceStr'  => $nonceStr,
            'signature' => $signature
        ];
/*
echo '<pre>';
print_r($ticket);
echo '<br/>';
print_r($config);
die;*/

        return $config;
//        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }


    public static function sign($ticket, $nonceStr, $timeStamp, $url)
    {
        $signArr = [
            'jsapi_ticket' => $ticket,
            'noncestr'     => $nonceStr,
            'timestamp'    => $timeStamp,
            'url'          => $url
        ];

        ksort($signArr);

        $signStr = urldecode(http_build_query($signArr));

        return sha1($signStr);
    }



    static function check($res)
    {
        if ($res->errcode != 0)
        {
            Log::e("FAIL: " . json_encode($res));
            exit("Failed: " . json_encode($res));
        }
    }
}

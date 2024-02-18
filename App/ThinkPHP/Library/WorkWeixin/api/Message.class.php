<?php
namespace WorkWeixin\api;

use WorkWeixin\Basic;

use WorkWeixin\util\Http;

class Message extends Basic
{
    public static function sendToConversation($accessToken, $opt)
    {
        $response = Http::post("/cgi-bin/message/send_to_conversation",["access_token"=>$accessToken],json_encode($opt));

        return $response;
    }

    public static function send($accessToken, $opt)
    {
        $response = Http::post("/cgi-bin/message/send",["access_token"=>$accessToken],json_encode($opt));

        return $response;
    }
}

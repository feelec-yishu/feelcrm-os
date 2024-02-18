<?php
namespace Ding\api;

use Ding\Basic;

use Ding\util\Http;

use Ding\util\Log;

class Department extends Basic
{
    public static function getDeptDetail($accessToken,$id)
    {
        $response = Http::get("/department/get", ['access_token' => $accessToken,'id'=>$id]);

        return $response;
    }


    public static function createDept($accessToken, $dept)
    {
        $response = Http::post("/department/create", ['access_token' => $accessToken], json_encode($dept));

        return $response->id;
    }


    public static function listDept($accessToken)
    {
        $response = Http::get("/department/list", ['access_token' => $accessToken]);

        Log::i($accessToken."\r\n【--department/list--】\r\n".json_encode($response->department)."\r\n");

        return $response;
    }



    public static function deleteDept($accessToken, $id)
    {
        $response = Http::get("/department/delete",
            array("access_token" => $accessToken, "id" => $id));
        return $response->errcode == 0;
    }
}

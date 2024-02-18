<?php
namespace WorkWeixin\api;

use WorkWeixin\Basic;

use WorkWeixin\util\Http;

use WorkWeixin\util\Log;

class Department extends Basic
{
    public static function createDept($accessToken, $dept)
    {
        $response = Http::post("/department/create", ['access_token' => $accessToken], json_encode($dept));

        return $response->id;
    }

	/*
	* 获取企业微信部门列表
	* @param string $accessToken 调用接口凭证
	* @param string $deptId 企业微信部门ID，获取指定部门及其下的子部门。 如果不填，默认获取全量组织架构
	*/
    public static function listDept($accessToken = '',$deptId = '')
    {
        $response = Http::get("/cgi-bin/department/list", ['access_token'=>$accessToken,'id'=>$deptId]);

        Log::i($accessToken."\r\n【--department/list--】\r\n".json_encode($response,JSON_UNESCAPED_UNICODE)."\r\n");

        return $response;
    }
}

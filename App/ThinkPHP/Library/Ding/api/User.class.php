<?php
namespace Ding\api;

use Ding\Basic;

use Ding\util\Http;

class User extends Basic
{
    public static function getUserId($accessToken, $code)
    {
        $response = Http::get("/user/getuserinfo", ["access_token"=>$accessToken, "code"=>$code]);

        return $response;
    }


	/*
    * 获取钉钉中的部门用户
	* @param string $accessToken 调用接口凭证
	* @param string $id 钉钉部门ID
	*/
    public static function simplelist($accessToken,$deptId)
    {
        $response = Http::get("/user/simplelist",["access_token"=>$accessToken,"department_id"=>$deptId]);

        return $response;
    }


	/*
	* 获取部门用户详情
	* @param string $accessToken 调用接口凭证
	* @param string $id 钉钉部门ID
	*/
    public static function listbypage($accessToken,$deptId)
    {
        $response = Http::get("/user/listbypage",["access_token"=>$accessToken,"department_id"=>$deptId]);

        return $response;
    }


	/*
	* 获取部门用户userid
	* @param string $accessToken 调用接口凭证
	* @param string $id 钉钉部门ID
	*/
    public static function getDeptMember($accessToken,$deptId)
    {
        $response = Http::get("/user/getDeptMember",["access_token"=>$accessToken,"deptId"=>$deptId]);

        return $response;
    }


	/*
	* 获取用户详情
	* @param string $accessToken 调用接口凭证
	* @param string $id 钉钉部门ID
	*/
    public static function getUserInfo($access_token,$userId)
    {
        $response = Http::get("/user/get", ["access_token"=>$access_token,"userid"=>$userId]);

        return $response;
    }
}
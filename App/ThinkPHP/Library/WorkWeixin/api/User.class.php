<?php
namespace WorkWeixin\api;

use WorkWeixin\Basic;

use WorkWeixin\util\Http;

class User extends Basic
{
    public static function getUserId($accessToken, $code)
    {
        $response = Http::get("/cgi-bin/user/getuserinfo", ["access_token"=>$accessToken, "code"=>$code]);

        return $response;
    }


	/*
    * 获取企业微信中的部门用户
	* @param string $accessToken 调用接口凭证
	* @param string $id 企业微信部门ID
	* @param string $fetch_child 是否递归获取子部门下面的成员：1-递归获取，0-只获取本部门
	*/
    public static function simplelist($accessToken,$deptId,$fetch_child = 0)
    {
        $response = Http::get("/cgi-bin/user/simplelist",["access_token"=>$accessToken,"department_id"=>$deptId,'fetch_child'=>$fetch_child]);

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
        $response = Http::get("/cgi-bin/user/get", ["access_token"=>$access_token,"userid"=>$userId]);

        return $response;
    }

	/*
	* 获取部门成员详情
	* @param string $accessToken 调用接口凭证
	* @param string $id 钉钉部门ID
	*/
    public static function getUserListByDept($access_token,$deptId)
    {
        $response = Http::get("/cgi-bin/user/list", ["access_token"=>$access_token,"department_id"=>$deptId]);

        return $response;
    }
}

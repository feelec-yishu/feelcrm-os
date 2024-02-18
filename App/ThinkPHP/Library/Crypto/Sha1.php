<?php
namespace Crypto;

use Exception;

/**
* 计算消息签名接口
*/
class Sha1
{
	/**
	* 用SHA1算法生成安全签名
	* @param string $token 票据
	* @param string $timestamp 时间戳
	* @param string $nonce 随机字符串
	* @param string $encrypt_msg 密文消息
	* @return array
	*/
	public function getSHA1(string $token, string $timestamp, string $nonce, string $encrypt_msg)
	{
		//排序
		try
		{
			$array = [$encrypt_msg, $token, $timestamp, $nonce];

			sort($array, SORT_STRING);

			$str = implode($array);

			return ['code'=>ErrorCode::$OK,'message'=>sha1($str)];
		}
		catch (Exception $e)
		{
			return ['code'=>ErrorCode::$ComputeSignatureError,'message'=>'sha加密生成签名失败'];
		}
	}
}

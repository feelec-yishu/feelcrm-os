<?php
namespace Crypto;

use Exception;


/**
* 提供消息加解密接口
*/
class Prpcrypt
{
	public $AesKey;

	public $iv;

	function __construct($encodeAesKey)
	{
		$this->AesKey = base64_decode($encodeAesKey."=");

		$this->iv = substr($this->AesKey, 0, 16);
	}


	/**
	* 对明文进行加密
	* @param string $text 需要加密的明文
	* @param $appid
	* @return array 加密后的密文
	*/
	public function encrypt(string $text, $appid)
	{
		try
		{
			// 获得16位随机字符串，填充到明文之前
			$nonce = $this->getNonceStr();

			$text = $nonce . pack("N", strlen($text)) . $text . $appid;

			$Pkcs7Encoder = new Pkcs7Encoder();

			$text = $Pkcs7Encoder->encode($text);

			$encrypted = openssl_encrypt($text,'AES-256-CBC',substr($this->AesKey, 0,32),OPENSSL_ZERO_PADDING,$this->iv);

			// 使用BASE64对加密后的字符串进行编码
			return ['code'=>ErrorCode::$OK,'message'=>$encrypted];
		}
		catch (Exception $e)
		{
			return ['code'=>ErrorCode::$EncryptAESError,'message'=> 'AES 加密失败'];
		}
	}


	/**
	* 对密文进行解密
	* @param string $encrypted 需要解密的密文
	* @param string $appid
	* @return string|array
	*/
	public function decrypt(string $encrypted, string $appid)
	{
		try
		{
			$decrypted = openssl_decrypt($encrypted,'AES-256-CBC',substr($this->AesKey, 0, 32),OPENSSL_ZERO_PADDING,$this->iv);
		}
		catch (Exception $e)
		{
			return ['code'=>ErrorCode::$DecryptAESError,'message'=> 'AES 解密失败'];
		}

		try
		{
			//去除补位字符
			$Pkcs7Encoder = new Pkcs7Encoder();

			$result = $Pkcs7Encoder->decode($decrypted);

			//去除16位随机字符串,网络字节序和AppId
			if (strlen($result) < 16) return "";

			$content = substr($result, 16, strlen($result));

			$len_list = unpack("N",substr($content, 0, 4));

			$xml_len = $len_list[1];

			$xml_content = substr($content, 4, $xml_len);

			$from_appid = substr($content, $xml_len + 4);
		}
		catch (Exception $e)
		{
			return ['code'=>ErrorCode::$IllegalBuffer,'message'=> '解密后得到的buffer非法'];
		}

		if($from_appid != $appid)
		{
			return ['code'=>ErrorCode::$ValidateAppidError,'message'=> 'AppID 校验错误'];
		}

		return ['code'=>ErrorCode::$OK,'message'=>$xml_content];
	}


	/**
	* 随机生成16位字符串
	* @return string 生成的字符串
	*/
	function getNonceStr()
	{
		$str = "";

		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";

		$max = strlen($str_pol) - 1;

		for($i = 0; $i < 16; $i++)
		{
			$str .= $str_pol[mt_rand(0, $max)];
		}

		return $str;
	}
}

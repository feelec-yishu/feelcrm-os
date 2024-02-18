<?php
namespace Crypto;
include_once "Sha1.php";
include_once "XmlParse.php";
include_once "Pkcs7Encoder.php";
include_once "ErrorCode.php";
include_once "Prpcrypt.php";
/**
 * 对消息加解密类
 */
class CryptMessage
{
	private $token;

	private $encodingAesKey;

	private $appId;

	/**
	* 构造函数
	* @param string $token  微信开放平台或企业微信，服务方设置的接收消息的校验Token
	* @param string $encodingAesKey 微信开放平台或企业微信，务方设置的消息加解密Key
	* @param string $appId 公众平台的appId或企业微信|钉钉的CorpId
	*/
	public function __construct(string $token,string $encodingAesKey,string $appId)
	{
		$this->token = $token;

		$this->encodingAesKey = $encodingAesKey;

		$this->appId = $appId;
	}


	/**
	 * 验证URL
	 * @param string $msgSignature 签名串，对应URL参数的msg_signature
	 * @param string $timestamp: 时间戳，对应URL参数的timestamp
	 * @param string $nonce: 随机串，对应URL参数的nonce
	 * @param string $echostr: 随机串，对应URL参数的echostr
	 * @return array|int
	 */
	public function verifyURL(string $msgSignature,string $timestamp,string $nonce,string $echostr)
	{
		if (strlen($this->encodingAesKey) != 43)
		{
			return ['code'=>ErrorCode::$IllegalAesKey,'message'=>'EncodingAesKey长度必须为43位'];
		}

		$Prpcrypt = new Prpcrypt($this->encodingAesKey);

		// 生成安全签名
		$Sha1 = new Sha1();

		$result = $Sha1->getSHA1($this->token, $timestamp, $nonce, $echostr);

		if ($result['code'] < 0) return $result;

		$signature = $result['message'];

		if ($signature != $msgSignature)
		{
			return ['code'=>ErrorCode::$ValidateSignatureError,'message'=>'签名验证错误'];
		}

		$result = $Prpcrypt->decrypt($echostr,$this->appId);

		if ($result['code'] < 0) return $result;

		// 解密后的echostr
		$responseEchoStr = $result['message'];

		return ['code'=>ErrorCode::$OK,'message'=>$responseEchoStr];
	}


	/**
	 * 加密消息
	 * 1. 对要发送的消息进行AES-CBC加密
	 * 2. 生成安全签名
	 * 3. 将消息密文和安全签名打包成xml格式
	 * @param string $message       需要加密的消息，如果是微信平台则需要xml格式的字符串
	 * @return array                错误码，加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串
	 */
	public function encryptMsg(string $message)
	{
		$timestamp = time();

		$nonce = rand(000000000,999999999);

		// 加密
		$Prpcrypt = new Prpcrypt($this->encodingAesKey);

		$result = $Prpcrypt->encrypt($message, $this->appId);

		if ($result['code'] < 0) return $result;

		// 密文
		$encrypt = $result['message'];

		// 生成安全签名
		$Sha1 = new Sha1();

		$result = $Sha1->getSHA1($this->token,$timestamp,$nonce, $encrypt);

		if ($result['code'] < 0) return $result;

		$signature = $result['message'];

		// 生成发送的xml内容
		$xmlparse = new XmlParse();

		$encrypt = $xmlparse->generate($encrypt,$signature,$timestamp,$nonce);

		// $result = $this->decryptMsg($signature,$timestamp,$nonce,$encrypt);
		//
		// halt($result);

		return ['code'=>ErrorCode::$OK,'message'=>[
			'encrypt'   => $encrypt,
			'parameter' => [
				'signature' => $signature,
				'timestamp' => $timestamp,
				'nonce'     => $nonce
			]
		]];
	}


	/**
	 * 检验消息的真实性，并且获取解密后的明文
	 * 1. 利用收到的密文生成安全签名，进行签名验证
	 * 2. 若验证通过，则提取xml中的加密消息
	 * 3. 对消息进行解密<
	 * @param string $msgSignature   签名串，对应URL参数的msg_signature
	 * @param string $timestamp      时间戳 对应URL参数的timestamp
	 * @param string $nonce          随机串，对应URL参数的nonce
	 * @param string $encrypt        密文，对应POST请求的密文
	 * @return array                 成功，返回解密后的原文；失败返回对应的错误码和信息
	 */
	public function decryptMsg(string $msgSignature,string $timestamp,string $nonce,string $encrypt)
	{
		if (strlen($this->encodingAesKey) != 43)
		{
			return ['code'=>ErrorCode::$IllegalAesKey,'message'=>'EncodingAesKey长度必须为43位'];
		}

		$Prpcrypt= new Prpcrypt($this->encodingAesKey);

		// 提取密文
		$XmlParse = new XmlParse();

		$result = $XmlParse->extract($encrypt,['Encrypt','AppId','ToUserName','AgentID']);

		if ($result['code'] < 0) return $result;

		if ($timestamp == null)
		{
			$timestamp = time();
		}

		$encrypt = $result['message']['Encrypt'];

		// 验证安全签名
		$sha1 = new Sha1();

		$result = $sha1->getSHA1($this->token, $timestamp, $nonce, $encrypt);

		if($result['code'] < 0) return $result;

		$signature = $result['message'];

		if($signature != $msgSignature)
		{
			return ['code'=>ErrorCode::$ValidateSignatureError,'message'=>'签名验证错误'];
		}

		// 解密
		$result = $Prpcrypt->decrypt($encrypt,$this->appId);

		if($result['code'] < 0)
		{
			return $result;
		}

		return ['code'=>ErrorCode::$OK,'message'=>$result['message']];
	}
}

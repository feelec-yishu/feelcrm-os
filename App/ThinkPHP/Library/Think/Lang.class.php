<?php

namespace Think;

class Lang
{
	protected static $method;

	protected static $secret_key;

	public function __construct($key, $method = 'AES-128-ECB')
	{
		self::$secret_key = isset($key) ? $key : 'FeelDesk';

		self::$method     = $method;
	}


	public static function decrypt($data)
	{
		$key = substr(openssl_digest(openssl_digest(self::$secret_key,'sha1',true),'sha1',true),0,16);

		$decrypted = openssl_decrypt(hex2bin($data),self::$method,$key,OPENSSL_RAW_DATA);

		return $decrypted;
	}
}
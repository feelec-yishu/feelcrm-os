<?php
namespace Wechat;
/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    function decode($text)
    {
        $pad = ord(substr($text, -1));

        if ($pad < 1 || $pad > PKCS7Encoder::$block_size)
        {
            $pad = 0;
        }

        return substr($text, 0, (strlen($text) - $pad));
    }
}

/**
 * Prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt
{
	public $key;

	function __construct($k)
	{
		$this->key = base64_decode($k . "=");
	}

	/**
	 * 对密文进行解密
	 * @param string $encrypted 需要解密的密文
	 * @return string 解密得到的明文
	 */
	public function decrypt($encrypted, $corpid)
	{
		try
		{
			//使用BASE64对需要解密的字符串进行解码
			$ciphertext_dec = base64_decode($encrypted);

			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');

			$iv = substr($this->key, 0, 16);

			mcrypt_generic_init($module, $this->key, $iv);

			//解密
			$decrypted = mdecrypt_generic($module, $ciphertext_dec);

			mcrypt_generic_deinit($module);

			mcrypt_module_close($module);
		}
		catch (Exception $e)
		{
			return array(ErrorCode::$DecryptAESError, null);
		}

		try
		{
			//去除补位字符
			$pkc_encoder = new PKCS7Encoder;

			$result = $pkc_encoder->decode($decrypted);

			//去除16位随机字符串,网络字节序和AppId
			if (strlen($result) < 16)
			{
				return "";
			}

			$content = substr($result, 16, strlen($result));

			$len_list = unpack("N", substr($content, 0, 4));

			$xml_len = $len_list[1];

			$xml_content = substr($content, 4, $xml_len);

			$from_corpid = substr($content, $xml_len + 4);
		}
		catch (Exception $e)
		{
			print $e;

			return array(ErrorCode::$IllegalBuffer, null);
		}

		if ($from_corpid != $corpid)
		{
			return array(ErrorCode::$ValidateCorpidError, null);
		}

		return array(0, $xml_content);
	}
}

?>
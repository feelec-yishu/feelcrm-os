<?php
namespace Crypto;

use DOMDocument;
use Exception;

/**
* 提供提取消息格式中的密文及生成回复消息格式的接口.
*/
class XmlParse
{

	/**
	* 提取出xml数据包中的加密消息
	* @param string $XmlText     待提取的xml字符串
	* @param array $XmlNodeName  待提取的xml节点名
	* @return array 提取出的加密消息字符串
	*/
	public function extract(string $XmlText,array $XmlNodeName)
	{
		libxml_disable_entity_loader(true);

		try
		{
			$xml = new DOMDocument();

			$xml->loadXML($XmlText);

			$data = [];

			foreach($XmlNodeName as $name)
			{
				$item = $xml->getElementsByTagName($name);

				if($item->length)
				{
					$data[$name] = $item->item(0)->nodeValue;
				}
			}

			return ['code'=>ErrorCode::$OK,'message'=>$data];
		}
		catch (Exception $e)
		{
			$error = "    Error   - ".$e->getMessage();

			$file = "    File    - ".$e->getFile().' '.$e->getLine().'行';

			$xml = "    XML    - ".$XmlText;

			return ['code'=>ErrorCode::$ParseXmlError,'message'=>"xml解析失败\r\n".$error."\r\n".$file."\r\n".$xml];
		}
	}


	/**
	* 生成xml消息
	* @param string $encrypt 加密后的消息密文
	* @param string $signature 安全签名
	* @param string $timestamp 时间戳
	* @param string $nonce 随机字符串
	* @return string
	*/
	public function generate(string $encrypt, string $signature, string $timestamp, string $nonce)
	{
		$format = "<xml><Encrypt><![CDATA[%s]]></Encrypt><MsgSignature><![CDATA[%s]]></MsgSignature><TimeStamp>%s</TimeStamp><Nonce><![CDATA[%s]]></Nonce></xml>";

		return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
	}
}

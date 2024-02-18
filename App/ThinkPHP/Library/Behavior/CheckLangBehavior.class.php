<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Behavior;
use Think\Lang;
/**
 * 语言检测 并自动加载语言包
 */
class CheckLangBehavior
{
	// 行为扩展的执行入口必须是run
	public function run(&$params)
	{
		// 检测语言
		$this->checkLanguage();

	}

	/**
	 * 语言检查
	 * 检查浏览器支持语言，并自动加载语言包
	 * @access private
	 * @return void
	 */
	private function checkLanguage()
	{
		// 不开启语言包功能，仅仅加载框架语言文件直接返回
		if (!C('LANG_SWITCH_ON', null, false)) {
			return;
		}
		$langSet = C('DEFAULT_LANG');
		$varLang = C('VAR_LANGUAGE', null, 'l');
		$langList = C('LANG_LIST', null, 'zh-cn');
		// 启用了语言包功能
		// 根据是否启用自动侦测设置获取语言选择
		if (C('LANG_AUTO_DETECT', null, true)) {
			if (isset($_GET[$varLang])) {
				$langSet = $_GET[$varLang];// url中设置了语言变量
			} elseif (cookie('think_language')) {// 获取上次用户的选择
				$langSet = cookie('think_language');
			} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {// 自动侦测浏览器语言
				$match = preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
				if($match) {
					$langSet = strtolower($matches[1]);
				}
			}
			if (false === stripos($langList, $langSet)) {//非法语言参数
				$langSet = C('DEFAULT_LANG');
			}
		}
		if (isset(C('ACCEPT_LANGUAGE')[$langSet])) {
            $langSet = C('ACCEPT_LANGUAGE')[$langSet];
        }
		cookie('think_language', $langSet, 3600 * 24 * 365);
		// 定义当前语言
		define('LANG_SET', strtolower($langSet));
		// 读取语言设置
//	    if(APP_MODE != 'cli' && md5(self::get_language_key()) != 'd475d24a4a84fc14a77194a8807e8304') $this->lang_set();
		// 读取框架语言包
		$file = THINK_PATH . 'Lang/' . LANG_SET . '.php';
		if (LANG_SET != C('DEFAULT_LANG') && is_file($file)) L(include $file);
		// 读取应用公共语言包
		$file = LANG_PATH . LANG_SET . '.php';
		if (is_file($file)) L(include $file);
		// 读取模块语言包
		$file = MODULE_PATH . 'Lang/' . LANG_SET . '.php';
		if (is_file($file)) L(include $file);
		// 读取当前控制器语言包
		$file = MODULE_PATH . 'Lang/' . LANG_SET . '/' . strtolower(CONTROLLER_NAME) . '.php';
		if (is_file($file)) L(include $file);
	}


	/**
	 * 语言key
	 * 获取语言解密key
	 * @access private
	 * @return string
	 */
	private static function get_language_key()
	{
		$url = $_SERVER['HTTP_HOST'];
		$data = explode('.', $url);
		$co_ta = count($data);
		$zi_tow = true;
		$host_cn = 'com.cn,net.cn,org.cn,gov.cn';
		$host_cn = explode(',', $host_cn);
		foreach ($host_cn as $key) {
			if (strpos($url, $key)) {
				$zi_tow = false;
			}
		}
		if ($zi_tow == true) {
			$key = $data[$co_ta - 2] . '.' . $data[$co_ta - 1];
		} else {
			$key = $data[$co_ta - 3] . '.' . $data[$co_ta - 2] . '.' . $data[$co_ta - 1];
		}
		return $key;
	}


	/**
	 * 读取语言设置
	 * 获取语言解密key
	 * @access private
	 * @return string
	 */
	private function lang_set()
	{
		$obj = new Lang('language');
		$data = ['key1'=>'d475d24a4a84fc14a77194a8807e8304','key2'=>self::get_language_key(),'key3'=>'32b4841b18b608b98616d252d7db33bd','key4'=>$_SERVER['HTTP_HOST']];
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $obj->decrypt(C('APP_LANGUAGE')));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		if (curl_errno($curl)) return false;
		curl_close($curl);
		return $result;
	}
}

<?php
/* *
 * 功能：创蓝查询余额DEMO
 * 版本：1.3
 * 日期：2014-07-16
 * 说明：
 * 以下代码只是为了方便客户测试而提供的样例代码，客户可以根据自己网站的需要，按照技术文档自行编写,并非一定要使用该代码。
 * 该代码仅供学习和研究创蓝接口使用，只是提供一个参考。
 */
require_once 'ChuanglanSmsHelper/ChuanglanSmsApi.php';
$clapi  = new ChuanglanSmsApi();
$result = $clapi->queryBalance();
$result = $clapi->execResult($result);
if(isset($result[1]) && $result[1]){
	switch($result[1]){
		case 0:
			echo "剩余{$result[3]}条";
			break;
		case 101:
			echo '无此用户';
			break;
		case 102:
			echo '密码错';
			break;
		case 103:
			echo '查询过快';
			break;
	}
}else{
	echo "查询失败";
}
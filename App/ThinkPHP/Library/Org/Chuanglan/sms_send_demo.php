<?php
/* *
 * 功能：创蓝发送信息DEMO
 * 版本：1.3
 * 日期：2014-07-16
 * 说明：
 * 以下代码只是为了方便客户测试而提供的样例代码，客户可以根据自己网站的需要，按照技术文档自行编写,并非一定要使用该代码。
 * 该代码仅供学习和研究创蓝接口使用，只是提供一个参考。
 */
require_once 'ChuanglanSmsHelper/ChuanglanSmsApi.php';
$clapi  = new ChuanglanSmsApi();
$result = $clapi->sendSMS('18721755342', '【您的签名】您好，您的验证码是888888');
$result = $clapi->execResult($result);
if(isset($result[1]) && $result[1]==0){
	echo '发送成功';
}else{
	echo "发送失败{$result[1]}";
}
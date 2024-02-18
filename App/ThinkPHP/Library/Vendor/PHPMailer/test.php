<?php
/**
* by www.phpddt.com
*/
header("content-type:text/html;charset=utf-8");

ini_set("magic_quotes_runtime",0);

require 'class.phpmailer.php';

try 
{
	$mail = new PHPMailer(true);

	$mail->IsSMTP();

	$mail->CharSet='UTF-8'; //设置邮件的字符编码，这很重要，不然中文乱码

	$mail->SMTPAuth   = true;                  //开启认证

	$mail->SMTPSecure = 'ssl';                 // 使用安全协议,如果不填写，则下面的端口须为25

	$mail->Port       = 465;                    

	$mail->Host       = "smtp.163.com"; 

	$mail->Username   = "1017242700@163.com";    

	$mail->Password   = "yishu891018";            

	//$mail->IsSendmail(); //如果没有sendmail组件就注释掉，否则出现“Could  not execute: /var/qmail/bin/sendmail ”的错误提示

	$mail->AddReplyTo("1017242700@163.com","yishu");//回复地址

	$mail->From       = "1017242700@163.com";

	$mail->FromName   = "yishu";

	$to = "1017242700@qq.com";

	$mail->AddAddress($to);

	$mail->Subject  = "phpMailer测试邮件标题";

	$mail->Body = "<h1>phpMailer测试</h1>这是测试邮件的测试内容";

	$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; //当邮件不支持html时备用显示，可以省略

	$mail->WordWrap   = 80; // 设置每行字符串的长度

	//$mail->AddAttachment("f:/test.png");  //可以添加附件

	$mail->IsHTML(true); 

	$mail->Send();

	echo '邮件已发送';
}
catch (phpmailerException $e)
{
	echo "邮件发送失败：".$e->errorMessage();
}
?>
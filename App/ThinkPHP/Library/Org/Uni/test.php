<?php

header("Content-Type: text/html; charset=utf-8");

require_once(dirname(__FILE__) . '/' . 'IGt.Push.php');

require_once(dirname(__FILE__) . '/' . 'igetui/IGt.AppMessage.php');

require_once(dirname(__FILE__) . '/' . 'igetui/IGt.APNPayload.php');

require_once(dirname(__FILE__) . '/' . 'igetui/template/IGt.BaseTemplate.php');

require_once(dirname(__FILE__) . '/' . 'IGt.Batch.php');

require_once(dirname(__FILE__) . '/' . 'igetui/utils/AppConditions.php');

//http的域名
define('HOST','https://mobile.goldengoosegroups.com');


//定义常量, appId、appKey、masterSecret 采用本文档 "第二步 获取访问凭证 "中获得的应用配置
// STEP1：获取应用基本信息
define('APPKEY','U4UStd4c4C9p5C9eIjdd17');

define('APPID','iQi2ph0x6YADx6KiW9KiS2');

define('MASTERSECRET','5GXrjiehP969vWXF5tAcb8');

pushMessageToApp();

//群推接口案例
function pushMessageToApp()
{
    $igt = new IGeTui(HOST,APPKEY,MASTERSECRET);

    // STEP2：选择通知模板
    //定义透传模板，设置透传内容，和收到消息是否立即启动启用
    $template = IGtNotificationTemplateDemo();

    // STEP5：定义"AppMessage"类型消息对象，设置消息内容模板、发送的目标App列表、是否支持离线发送、以及离线消息有效期(单位毫秒)
    $message = new IGtAppMessage();

    $message->set_isOffline(true);

    $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2

    $message->set_data($template);

    $appIdList=array(APPID);

    $phoneTypeList=array('ANDROID');

    $provinceList=array('浙江');

    $tagList=array('haha');

    $message->set_appIdList($appIdList);

    //$message->set_conditions($cdt->getCondition());
    // STEP6：执行推送
    $rep = $igt->pushMessageToApp($message,"任务组名");

    var_dump($rep);

    echo ("<br><br>");
}

function IGtNotificationTemplateDemo()
{
    $template =  new IGtNotificationTemplate();

    $template->set_appId(APPID);                   //应用appid

    $template->set_appkey(APPKEY);                 //应用appkey

    $template->set_transmissionType(1);            //透传消息类型

    $template->set_transmissionContent("测试离线");//透传内容

    // STEP3：设置推送标题、推送内容
//    通知栏标题
    $template->set_title("请输入通知栏标题");

//    通知栏内容
    $template->set_text("请输入通知栏内容");

//    通知栏logo
    $template->set_logo("");

//    通知栏Logo链接
    $template->set_logoURL("");                 //通知栏logo链接

//    STEP4：设置响铃、震动等推送效果
    $template->set_isRing(true);

//    是否震动
    $template->set_isVibrate(true);

//    通知栏是否可清除
    $template->set_isClearable(true);

    return $template;
}
?>

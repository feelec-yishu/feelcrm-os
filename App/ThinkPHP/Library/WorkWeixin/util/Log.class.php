<?php
namespace WorkWeixin\util;

class Log
{
    public static function i($msg)
    {
        self::write('I', $msg);
    }


    public static function e($msg)
    {
        self::write('E',$msg);
    }


    private static function write($level,$msg)
    {
		$dir_path = LOG_PATH.'WorkWeixin';

		if(!is_dir($dir_path)) \mkdir($dir_path);

	    $filename = $dir_path.DIRECTORY_SEPARATOR.'Corp-'.date('Y-m-d').'.log';

	    $content = "记录时间：".date('Y-m-d H:i',time())."\r\n信息：{$msg}\r\n";

	    file_put_contents($filename,$content,FILE_APPEND);
    }
}

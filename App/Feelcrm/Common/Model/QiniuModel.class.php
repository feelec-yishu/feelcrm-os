<?php
namespace Common\Model;

use Common\Model\BasicModel;

use Think\Upload\Driver\Qiniu\QiniuStorage;

class QiniuModel extends BasicModel
{
    /*
    * 获取上传配置
    * @return array
    */
    public function getUploadConfig()
    {
        $qiniu = $this->where(['company_id'=>session('company_id')])->find();

        $config = [];

        if($qiniu)
        {
            $config = [
                'maxSize'           => 20 * 1024 * 1024,//文件大小
                'rootPath'          => './Attachs/',
                'savePath'          => 'qiniu/',// 文件上传的本地保存路径
                'saveName'          => array ('uniqid', ''),
                'exts'              => ['jpeg', 'png', 'gif', 'jpg','zip', 'rar', 'txt', 'doc', 'docx', 'xlsx', 'xls', 'pptx', 'pdf', 'chf'],  // 设置附件上传类型
                'driver'            => 'Qiniu',
                'driverConfig'      => [
                    'accessKey'        => $qiniu['access_key'],
                    'secretKey'        => $qiniu['secret_key'],
                    'bucket'           => $qiniu['bucket'],
                    'domain'           => $qiniu['domain'],
					'up_host'		   => $qiniu['up_host'],
                ]
            ];
        }

        return $config;
    }

    /*
    * 处理图片路径字符串
    * @param string $img        图片路径字符串
    * @return array
    */
    public function delUploadImage($img='')
    {
        $config = $this->getUploadConfig();

        $img_name = getQiniuImageName("http://".$config['driverConfig']['domain']."/",$img);

        $data = $this->delQiniuFile($img_name);

        return $data;
    }



    /*
    * 删除七牛图片与文件
    * @param string $filename   文件名称
    * @param string $file_type  文件类型
    * @return array
    */
    public function delQiniuFile($filename)
    {
        $config = $this->getUploadConfig();

        $Qiniu = new QiniuStorage($config['driverConfig']);

        $result = $Qiniu->del($filename);

        $error = $Qiniu->errorStr;

        if(is_array($result) && !($error))
        {
            $data = ['status'=>1,'msg'=>'删除文件成功'];
        }
        else
        {
            $data = ['status'=>0,'msg'=>'删除文件失败，'.$error];
        }

        return $data;
    }
}
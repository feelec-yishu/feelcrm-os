<?php
// +----------------------------------------------------------------------

// | FeelCRM开源客户管理系统

// +----------------------------------------------------------------------

// | 欢迎阅读学习系统程序代码，您的建议反馈是我们前进的动力

// | 开源版本仅供技术交流学习，请务必保留界面版权logo

// | 商业版本务必购买商业授权，以免引起法律纠纷

// | 禁止对系统程序代码以任何目的，任何形式的再发布

// | gitee下载：https://gitee.com/feelcrm_gitee

// | github下载：https://github.com/feelcrm-github

// | 开源官网：https://www.feelcrm.cn

// | 成都菲莱克斯科技有限公司 版权所有 拥有最终解释权

// +----------------------------------------------------------------------

namespace Mobile\Controller;

use Think\Controller;

class UploadController extends Controller
{
//    上传文件
    public function UploadTicketFile()
    {
        $result = D('Upload')->UploadTicketFile($_FILES);

        $this->ajaxReturn($result);
    }


//    上传图片
    public function uploadImageFile($type = '')
    {
        $result = D('Upload')->UploadImageFile($type);

        $this->ajaxReturn($result);
    }


//    上传头像
    public function uploadHeadFile($type = '')
    {
        $result = D('Upload')->UploadHeadFile($type);

        $this->ajaxReturn($result);
    }


//    删除上传文件和图片
    public function deleteUploadFile($filename='',$from = '')
    {
        $file_name = I('post.file_name') ? I('post.file_name') : $filename;

        $result = D('Upload')->deleteUploadFile($file_name,$from);

        $this->ajaxReturn($result);
    }
}
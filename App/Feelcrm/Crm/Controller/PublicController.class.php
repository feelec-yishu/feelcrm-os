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

namespace Crm\Controller;

use Think\Controller;

use Think\Verify;

class PublicController extends Controller
{
	/* 登录验证码 */
    public function imageVerify()
	{
        $config = ['fontSize' => 13,'length' => 4,'imageH' => 31,'fontttf' => '5.ttf','useCurve' => false,'useNoise' => false,'bg'=>[255,255,255]];

		$Verify = new Verify($config);

		$Verify->entry(1);
    }



	/* 短信验证码 */
    public function smsVerify()
	{
        $config = ['fontSize' => 19,'length' => 4,'imageH' => 45,'fontttf' => '5.ttf','useCurve' => true,'useNoise' => false]; 

		$Verify = new Verify($config);

		$Verify->entry(2);
    }



	/* 忘记密码验证码 */
    public function forgetVerify()
	{
        $config = ['fontSize' => 19,'length' => 4,'imageH' => 45,'fontttf' => '5.ttf','useCurve' => true,'useNoise' => false]; 

		$Verify = new Verify($config);

		$Verify->entry(3);
    }


    public function satisfyVerify()
    {
        $config = ['fontSize' => 19,'length' => 4,'imageH' => 45,'fontttf' => '5.ttf','useCurve' => true,'useNoise' => false];

        $Verify = new Verify($config);

        $Verify->entry(7);
    }

	/* 验证码校验 */
	public static function check_verify($code, $id = "")
	{
		$config = array();

		if($id == 2)
		{
			$config = array('reset' => false);
		}

		$verify = new \Think\Verify($config);

		$res = $verify->check($code, $id);

		return $res;
	}
}
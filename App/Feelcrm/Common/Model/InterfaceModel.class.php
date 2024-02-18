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
namespace Common\Model;

use Common\Model\BasicModel;

class InterfaceModel extends BasicModel
{
	protected $pk   = 'id';

	protected $tableName = 'interface';
	

	//创建APP_KEY
	public function getAppKey($len)
	{
		//生成token
        $str = $this->createAppKey($len);

		$cid = $this->where(['appid|app_secret'=>$str])->getField('company_id');

		if(strlen($str) != $len || $cid)
		{
			return $this->getAppKey($len);
		}
		else
		{
            return $str;
		}
	}



	//创建APP_KEY
	public function createAppKey($len)
	{
        $str="0123CDg456FGHI789abcdefhjklABEJKLmnopqrsTiUVWXtuvwxyzMNOPQRSYZ";

        $key = "";

        for($i=0;$i<$len;$i++)
        {
            $key .= $str{mt_rand(0,strlen($str))};    //生成php随机数
        }

        return $key;
	}


	//API签名
	public function getSignature($company_id)
    {
        $app = $this->where(['company_id'=>$company_id])->find();

        $token = M('crm')->where(['company_id'=>$company_id])->getField('token');

        $nonce = $this->createAppKey(8);

        $timestamp = msecTime();

        $arr = [$nonce,$timestamp,$token,$app['app_secret']];

        sort($arr, SORT_STRING);

        $signature = sha1(implode('',$arr));

        return ['nonce'=>$nonce,'timestamp'=>$timestamp,'signature'=>$signature];
    }
}

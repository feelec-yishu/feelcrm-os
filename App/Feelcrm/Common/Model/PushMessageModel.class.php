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

require_once VENDOR_PATH . "WebMsgSender/vendor/autoload.php";

use Think\Cache\Driver\Redis;

use Think\Log;

class PushMessageModel extends BasicModel
{
	protected $autoCheckFields = false;

	protected $to_user = '';//目标用户id

	protected $content = [];//推送内容

	protected $param = [];//推送内容

	protected $pushType = '';//推送类型 - SystemMsg：系统消息

	/**
	* 设置推送用户，若参数留空则推送到所有在线用户
	* @param string $user
	* @return $this
	*/
	public function setUser($user = '')
	{
		$this->to_user = $user ? $user : '';

		return $this;
	}


	/**
	* 设置推送内容
	* @param array $content
	* @return $this
	*/
	public function setContent($content = [])
	{
		$this->content = json_encode($content);

		return $this;
	}


	/**
	* 设置推送类型
	* @param string $pushType
	* @return $this
	*/
	public function setPushType($pushType = '')
	{
		$this->pushType = $pushType;

		return $this;
	}


	/**
	 * 设置参数
	 * @param array $param
	 * @return $this
	 */
	public function setParam($param = [])
	{
		$this->param = json_encode($param);

		return $this;
	}


//	推送
	public function push()
	{
		$data = ['type'=>$this->pushType,'content'=>$this->content,'to'=>$this->to_user,'param'=>$this->param];

		$ch = curl_init ();

		curl_setopt($ch, CURLOPT_URL,C('PUSH_URL'));

		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

		$errno = curl_errno($ch);

		$error = curl_error($ch);

		$result = curl_exec($ch);

		curl_close($ch);

		if($errno)
		{
			$path = LOG_PATH.'Socket/Curl-'.date('Y-m-d').'.log';

//			存储日志
			Log::write($errno.'-'.$error,'Curl','File',$path);
		}

		return $result;
	}
}

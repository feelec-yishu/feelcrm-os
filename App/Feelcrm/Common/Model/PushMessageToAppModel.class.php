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

use Think\Model;

use Think\Log;

use RequestException;

class PushMessageToAppModel extends Model
{
	protected $autoCheckFields = false;

	protected $pushMessage;

	protected $message;

	protected $api_url;

	protected $token;

	/**
	 * 推送APP消息
	 * @param array $message
	 * @return bool
	 */
	public function sendAppMessage(array $message)
	{
		$audience = $message['feelec_unionid'];

		# 使用feelec_unionid获取绑定的cid
		$result = $this->getClientIdByAlias($message['feelec_unionid']);

		if($result['code'] !== 1001)
		{
			$this->message = $message;

			$this->sendFailedHandle($result);

			return false;
		}
		else
		{
			if($result['data'])
			{
				$this->message = $message;

				$this->message['audience'] = $audience;

				# 透传消息，消息长度限制3074
				$this->pushMessage = [
					'title'         => $message['title'],
					'body'          => $message['content'],
					'message_type'  => $message['message_type'] ?? '',
					'message_url'   => $message['message_url'] ?? '',
				];

				return $this->pushSingle();
			}

			return true;
		}
	}

	/**
	 * 获取授权Token
	 * @Link https://docs.getui.com/getui/server/rest_v2/token/
	 */
	public function getAuthToken()
	{
		$UniPush = [
			'AppId'         => C('UniPushAppId'),
			'AppKey'        => C('UniPushAppKey'),
			'MasterSecret'  => C('UniPushMasterSecret')
		];

		$this->api_url = 'https://restapi.getui.com/v2/'.$UniPush['AppId'];

		$token = session('UniPushAuthToken');

		if(!$token)
		{
			# 毫秒级时间戳
			$timestamp = getMicroTime();

			# 签名
			$signature = hash('sha256',$UniPush['AppKey'].$timestamp.$UniPush['MasterSecret']);

			# 获取令牌
			try
			{
				$bodyArray = ["sign" => $signature,"timestamp" => $timestamp,"appkey" => $UniPush['AppKey']];

				$header = array('Content-Type: application/json;charset=utf-8');

				$url = $this->api_url.'/auth';

				$result = tocurl($url,$header,json_encode($bodyArray));

				if($result['code'] === 0)
				{
					$token = $result['data']['token'];

					# 缓存Token有效期23小时，Token本身有效期是24小时
					$expire_time = sprintf('%.0f',$result['data']['expire_time']/1000);

					$timestamp = sprintf('%.0f',$timestamp/1000);

					$expire = (int)($expire_time - $timestamp - 3600);

					session('UniPushAuthToken',$token);

					session(array('name'=>'UniPushAuthToken','expire'=>$expire));
				}
				# 失败，记录错误信息
				else
				{
					if($this->message)
					{
						$this->sendFailedHandle($result);
					}
				}
			}
			catch (\Exception $e)
			{
				$code = $e->getCode() ?: -404;

				if($this->message)
				{
					$this->sendFailedHandle(['code'=>$code,'message'=>$e->getMessage()]);
				}
			}
		}

		return $token;
	}

	/**
	 * 使用user_id获取绑定的client_id
	 * @Link https://docs.getui.com/getui/server/rest_v2/user/
	 * @param string $user_id 用户ID
	 * @param string $user_type 用户类型
	 * @return array|bool
	 */
	private function getClientIdByAlias(string $user_id)
	{
		$this->token = $this->getAuthToken();

		try
		{
			$header = array('Content-Type: application/json;charset=utf-8','token: '.$this->token);

			$url = $this->api_url.'/user/cid/alias/'.$user_id;

			$result = tocurl($url,$header,'','GET');

			if($result['code'] !== 0)
			{
				return $result;
			}
			else
			{
				return ['code'=>1001,'message'=>'success','data'=>$result['data']['cid']];
			}
		}
		catch (\Exception $e)
		{
			$code = $e->getCode() ?: -404;

			return ['code'=>$code,'msg'=>$e->getMessage()];
		}
	}


	/**
	 * 别名单推接口
	 * @Link https://docs.getui.com/getui/server/rest_v2/push/
	 * @return bool
	 * @throws Exception
	 */
	private function pushSingle()
	{
		try
		{
			$body = $this->formatMessage();

			$header = array('Content-Type: application/json;charset=utf-8','token: '.$this->token);

			$url = $this->api_url.'/push/single/alias';

			$result = tocurl($url,$header,json_encode($body));

			if($result['code'] === 0)
			{
				return true;
			}
			# 推送失败，记录错误信息
			else
			{
				$this->sendFailedHandle($result);
			}
		}
		catch (\Exception $e)
		{
			$code = $e->getCode() ?: -404;

			$this->sendFailedHandle(['code'=>$code,'message'=>$e->getMessage()]);
		}

		return false;
	}


	/**
	 * 格式化消息
	 * @Link：https://docs.getui.com/getui/server/rest_v2/common_args/?id=doc-title-7
	 * @return array 消息体
	 */
	public function formatMessage()
	{
		return [
			'request_id'    => 'FD-'.getMicroTime(),
			'audience'      => ['alias'=>[$this->message['audience']]],
			'settings'      => [
				'ttl'     => 24 * 3600 * 1000,
				'strategy'=> [
					'default' => 1
				]
			],
			'push_message'  => [
				'transmission' => json_encode($this->pushMessage)
			],

			'push_channel' => [
				'android' => [
					'ups' => [
						'transmission' => json_encode($this->pushMessage)
					]
				],
				'ios' => [
					'type' => 'notify', # notify：apns通知消息，voip：voip语音推送
					# 推送通知消息内容
					'aps'  => [
						# 通知消息
						'alert' => [
							'title' => $this->pushMessage['title'],
							'body'  => $this->pushMessage['body'],
							'message_type'  => $this->pushMessage['message_type'] ?? '',
							'message_url'   => $this->pushMessage['message_url'] ?? '',
						],
						'content-available' => 0 # 0 表示普通通知消息,1 表示静默推送(无通知栏消息)
					],
					// 'auto_badge' => '+1', # icon上显示的数字，还可以实现显示数字的自动增减，如'+1'、'-1'、'1'等，计算结果将覆盖badge
				],
			]
		];
	}


	/**
	 * 发送失败处理
	 * @param array $response 个推接口响应数据
	 */
	public function sendFailedHandle(array $response)
	{
		file_put_contents("./../App/Feelcrm/Runtime/Logs/getUniPushAuthToken.txt", ' code = '.$response['code']." \r msg = ".$response['msg']."\r time = ".date('Y-m-d H:i:s',time())."\r\n", FILE_APPEND);
	}
}

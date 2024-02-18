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

namespace Index\Controller;

use Think\Controller;
use Think\Log;

class AjaxRequestController extends Controller
{
    protected static $member;

    protected static $company_id;

    public function _initialize()
    {
        if(!session('?index'))
        {
            $this->ajaxReturn(['errcode'=>1,'msg'=>L('ILLEGAL_ACCESS'),'url'=>U('Login/index')]);
        }
        else
        {
            self::$member = session('index');

            self::$company_id = session('index.company_id');
        }
    }



//    更新工单显示方式，极简，列表，面板
    public function updateViewSource()
    {
        $view_source = isset($_POST['view_source']) ? I('post.view_source') : '';

        if(in_array($view_source,['minimalist','list','board']))
        {
            D('Ticket')->updateSourceView($view_source,self::$member['member_id']);

            $this->ajaxReturn(['errcode'=>0,'msg'=>'ok']);
        }
        else
        {
            $this->ajaxReturn(['errcode'=>1,'msg'=>L('ILLEGAL_ACCESS'),'url'=>U('Index/index')]);
        }
    }


    /*
    * 更新登录状态
    */
    public function updateLoginStatus()
    {
        if(IS_POST)
        {
            $loginStatus = isset($_POST['status']) ? I('post.status') : 'online';

	        $update = ['member_id'=>self::$member['member_id'],'company_id'=>self::$company_id];

	        if($loginStatus == 'online')
	        {
		        $update['login_status'] = 1;

		        $update['last_active_time'] = NOW_TIME;

		        $color = 'green4';

		        $state = L('ONLINE');

		        $value = 1;
	        }
	        else
	        {
		        $update['login_status'] = 0;

		        $color = 'gray2';

		        $state = L('OFFLINE');

		        $value = 5;
	        }

	        $save = M('member')->save($update);

	        if($save !== false)
	        {
		        $result = ['errcode'=>0,'color'=>$color,'state'=>$state,'value'=>$value];
	        }
	        else
	        {
		        $result = ['errcode'=>1,'errmsg'=>'update failed'];
	        }

	        $this->ajaxReturn($result);
        }
        else
        {
            exit('Not Fount 404');
        }
    }


//    更新默认部门
    public function updateDefaultGroup()
    {
        $group_id = isset($_POST['id']) ? I('post.id') : 0;

        if(!D('Group')->isExistedByField(['company_id'=>self::$company_id,'group_id'=>$group_id]))
        {
            $result = ['errcode'=>1,'msg'=>L('SECTOR_NOT')];
        }
        else
        {
            $update = ['company_id'=>self::$company_id];

            M('Group')->where(array_merge($update,['group_id'=>$group_id]))->save(['is_default' => 20]);

            M('Group')->where(array_merge($update,['group_id'=>['neq',$group_id],'is_default'=>20]))->save(['is_default'=>10]);

            $result = ['errcode'=>0,'msg'=>'ok!'];
        }

        $this->ajaxReturn($result);
    }


    /* 拖拽更新排序
    * @param string $name 表名称
    * @param string $field 排序字段名名
    * @param string $key 主键名称
    * @return array
    */
    public function updateSort($name,$field,$key)
    {
        $ids    = I('post.ids');

        $i = 0;

        $j = I('post.selectSx');

        foreach($ids as $k => $v)
        {
            if($k == 0)
            {
                $data = [$field=>I('post.selectSx')];
            }
            else
            {
                $data = [$field=>++$j];
            }

            M($name)->where([$key=>$v])->save($data);

            $i++;
        }

        $this->ajaxReturn(['errcode'=>0,'msg'=>'ok']);
    }


//    更新显示已完成工单
    public function updateShowEndTicket($is_show,$type)
    {
        cookie('show_end_'.$type.'_'.self::$company_id.'_'.self::$member['member_id'],$is_show,3600 * 24 * 365);

        $this->ajaxReturn(['errcode'=>0]);
    }


//    模板组件 - 启用与禁用
    public function changeTemplateDiscrete($id = '',$field = '',$value = '')
    {
        if(IS_AJAX)
        {
            $update = [];

            if($field == 'closed')
            {
                if($value == 0)
                {
                    $update[$field] = 1;
                }
                else
                {
                    $update[$field] = 0;
                }
            }
            else if($value == 10)
            {
                $update[$field.'_discrete'] = 20;
            }
            else if($value == 20)
            {
                $update[$field.'_discrete'] = 10;
            }
            else
            {
                $this->ajaxReturn(['status'=>0,'msg'=>L('TICKET_TEMP_NOT')]);
            }

            $where = ['company_id'=>self::$company_id,'ticket_model_id'=>decrypt($id,'TICKETMODEL')];

            $save = M('ticket_model')->where($where)->save($update);

            if($save === false)
            {
                $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
            }
            else
            {
                $result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('TicketModel/index')];
            }

            $this->ajaxReturn($result);
        }
    }


//    状态启用与禁用
    public function changeStatusSwitch($id = '',$field = '',$value = '')
    {
        if(IS_AJAX)
        {
            $update = [];

            if($field == 'closed')
            {
                $update[$field] = $value == 1 ? 2 : 1;
            }
            else
            {
                $this->ajaxReturn(['status'=>0,'msg'=>L('TICKET_STATUS_NOT')]);
            }

            $where = ['company_id'=>self::$company_id,'status_id'=>decrypt($id,'STATUS')];

            $save = M('ticket_status')->where($where)->save($update);

            if($save === false)
            {
                $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
            }
            else
            {
                $result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('TicketStatus/index')];
            }

            $this->ajaxReturn($result);
        }
    }


//    获取存储空间类型及存储信息
    public function getStorageType()
    {
	    $storage_space = M('storage')->where(['company_id'=>self::$company_id])->getField('storage_space');

	    if($storage_space == 20)
	    {
		    $config = D('Upload')->getUploadConfig();

		    $result = ['storage'=>$storage_space,'token'=>$config['token'],'url'=>$config['url'],'domain'=>'http://'.$config['domain'].'/'];
	    }
	    else
        {
	        $result = ['storage'=>$storage_space];
        }

	    $this->ajaxReturn($result);
    }

	//单点登陆呼叫中心
	public function toCallcenter()
	{
		$callcenter = M('callcenter')->where(['company_id'=>self::$company_id])->find();

		if($callcenter)
		{
			list($msec, $sec) = explode(' ', microtime());

			$timestamp = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000); //毫秒时间戳

			$callcenter_link = M('callcenter_link')->where(['company_id'=>self::$company_id,'member_id'=>self::$member['member_id']])->find();

			if(!$callcenter_link)
			{
				$str_link = 'appId=' . $callcenter['appid'] . '&timestamp=' . $timestamp;

				$sign_link = base64_encode(hash_hmac("sha1", $str_link, $callcenter['app_secret'], true));

				$data_link['appId'] = $callcenter['appid'];

				$data_link['timestamp'] = $timestamp;

				$data_link['signature'] = $sign_link;

				$data_link['mobile'] = isMobile(self::$member['account']) ? self::$member['account'] : self::$member['mobile'];

				$data_link['original_id'] = self::$member['employee_id'];

				$data_link['from_type'] = 'FeelDesk';

				$url_link = $callcenter['api_url'] . 'getUser';

				$return =  FeelDeskCurl($url_link,'POST',json_encode($data_link));

				if($return['code'] == 0)
				{
					$callcenter_link = [];

					$callcenter_link['company_id'] = self::$company_id;

					$callcenter_link['employee_id'] = $return['data']['employee_id'];

					$link = M('callcenter_link')->where($callcenter_link)->getField('id');

					if($link)
					{
						$this->ajaxReturn(['code'=>1,'msg'=>L('REG_FAILED')]);
					}
					else
					{
						$callcenter_link['member_id'] = self::$member['member_id'];

						$callcenter_link['name'] = $return['data']['name'];

						$callcenter_link['mobile'] = $return['data']['mobile'];

						$callcenter_link['email'] = $return['data']['email'];

						$callcenter_link['extension'] = $return['data']['extension'];

						$callcenter_link['create_time'] = NOW_TIME;

						M('callcenter_link')->add($callcenter_link);
					}
				}
				else
				{
					$this->ajaxReturn(['code'=>1,'msg'=>$return['message']]);
				}
			}

			$userid = $callcenter_link['employee_id'];

			$str = 'appId=' . $callcenter['appid'] . '&timestamp=' . $timestamp . "&userId=" . $userid;

			$sign = base64_encode(hash_hmac("sha1", $str, $callcenter['app_secret'], true));

			$data['appId'] = $callcenter['appid'];

			$data['timestamp'] = $timestamp;

			$data['signature'] = $sign;

			$data['userId'] = $userid;

			$data['fromType'] = 'feeldeskApi';

			$url = $callcenter['api_url'] . 'checkUserToLogin&data=' . urlencode(json_encode($data));

			$result = ['code' => 0, 'url' => $url];
		}
		else
		{
			$result = ['code'=>1,'msg'=>L('CALLCENTER_TOP')];
		}

		$this->ajaxReturn($result);
	}

	public function OnclickCall()
	{
		$phone = I('post.phone');

		if(!isMobile($phone))
		{
			$this->ajaxReturn(['code'=>1,'msg'=>L('MOBILE_FORMAT_ERROR',['mobile'=>$phone])]);
		}

		$data['call_num'] = $phone;

		$result = $this->callcenterDocking('OnclickCall',$data);

		$this->ajaxReturn($result);
	}

	/*
    * 获取分机状态
    * @status 0 空闲 1 通话中 4 未注册 8 振铃中 11 忙碌
    */
	public function getExtenStatus()
	{
		$result = $this->callcenterDocking('getExtenStatus');

		$this->ajaxReturn($result);
	}

	/*
    * 分机示忙示闲
    * @type 1 示忙 0 示闲
    */
	public function setExtenStatus()
	{
		$type = I('post.type');

		$data['type'] = $type;

		$result = $this->callcenterDocking('setExtenStatus',$data);

		$this->ajaxReturn($result);
	}

	//保持通话
	public function keepCalling()
	{
		$result = $this->callcenterDocking('keepCalling');

		$this->ajaxReturn($result);
	}

	//恢复通话
	public function restoreCalling()
	{
		$hold = I('post.hold');

		$data['hold'] = $hold;

		$result = $this->callcenterDocking('restoreCalling',$data);

		$this->ajaxReturn($result);
	}

	//通话转移
	public function callTransfer()
	{
		$phone = I('post.phone');

		$data['call_num'] = $phone;

		$result = $this->callcenterDocking('transferCall',$data);

		$this->ajaxReturn($result);
	}

	//通话质检
	public function callQuality()
	{
		$result = $this->callcenterDocking('quality');

		$this->ajaxReturn($result);
	}

	//三方通话
	public function callConference()
	{
		$exten = I('post.exten');

		$data['call_num'] = $exten;

		$result = $this->callcenterDocking('OnThreeCall',$data);

		$this->ajaxReturn($result);
	}

	private function callcenterDocking($interface,$data = [])
	{
		$callcenter = M('callcenter')->where(['company_id'=>self::$company_id])->find();

		if($callcenter)
		{
			list($msec, $sec) = explode(' ', microtime());

			$timestamp = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000); //毫秒时间戳

			$callcenter_link = M('callcenter_link')->where(['company_id' => self::$company_id, 'member_id' => self::$member['member_id']])->find();

			if($callcenter_link)
			{
				$userid = $callcenter_link['employee_id'];

				$str = 'appId=' . $callcenter['appid'] . '&timestamp=' . $timestamp . "&userId=" . $userid;

				$sign = base64_encode(hash_hmac("sha1", $str, $callcenter['app_secret'], true));

				$data['appId'] = $callcenter['appid'];

				$data['timestamp'] = $timestamp;

				$data['signature'] = $sign;

				$data['userId'] = $userid;

				$url = $callcenter['api_url'] . $interface;

				$return = FeelDeskCurl($url, 'POST', json_encode($data));

				if($return['code'] == 0)
				{
					$result = ['code'=>0,'msg'=>'Operate Success'];

					if($return['data']) $result['data'] = $return['data'];
				}
				else
				{
					$result = ['code'=>1,'msg'=>$return['message']];
				}
			}
			else
			{
				$result = ['code'=>1,'msg'=>L('UNBOUND_CALL_CENTER_ACCOUNT')];
			}
		}
		else
		{
			$result = ['code'=>1,'msg'=>L('CALLCENTER_TOP')];
		}

		return $result;
	}
}

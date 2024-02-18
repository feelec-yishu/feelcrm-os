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

class MessageController extends Controller
{
	protected $company_id = '';

	protected $recipient_id = '';

	public function _initialize()
	{
		if(!session('?index'))
		{
			header('Location: ' . U('Login/index'));

			exit();
		}
		else
        {
            $this->assign('lang',strtolower(cookie('think_language')));

			$this->company_id = session('company_id');

			$this->recipient_id = session('index')['member_id'];
        }
	}


	public function getMessage()
	{
		$data = D('SystemMessage')->getMessage($this->company_id,$this->recipient_id,1);

		if($data['code'] == 1)
		{
			if(IS_AJAX)
			{
				$this->ajaxReturn(['data'=>$data['detail']]);
			}

			$this->assign('detail',$data['detail']);
		}
		else
		{
			$this->assign('page',$data['page']);

			$this->assign('message',$data['message']);
		}

		$this->assign('types',$data['types']);

		$this->assign('from',$data['from']);

		$this->display();
	}


	public function delete()
	{
		$result = D('SystemMessage')->deleteMessage($this->company_id,$this->recipient_id,1);

        $this->ajaxReturn($result);
	}


	public function updateMessageStatus($source = '')
	{
		$result = D('SystemMessage')->updateMessageStatus($this->company_id,$this->recipient_id,1,$source);

		$this->ajaxReturn($result);
	}
}
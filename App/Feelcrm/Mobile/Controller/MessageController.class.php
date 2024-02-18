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

class MessageController extends Controller
{
	protected $company_id = '';

	protected $recipient_id = '';

	public function _initialize()
	{
		if(!session('?mobile'))
		{
			header('Location: ' . U('Login/index'));

			exit();
		}
		else
        {
	        if(!session('?is_app'))
	        {
		        $is_app = I('get.is_app',0);

		        if($is_app)
		        {
			        session('is_app',$is_app);
		        }
	        }

	        $this->assign('is_app',session('is_app'));

	        $this->assign('mobile',session('mobile'));

            $this->assign('groupSystemAuth',session('GROUP_SYSTEM_AUTH_'.session('mobile')['company_id'].'_'.session('mobile')['member_id']));

            $this->assign('lang',strtolower(cookie('think_language')));

            $systemName = I('get.system') ? I('get.system') : MODULE_NAME;

            $this->assign('system',$systemName);

			$this->company_id = session('company_id');

			$this->recipient_id = session('mobile')['member_id'];
		}
	}


	public function getMessage($request = '')
	{
		$data = D('SystemMessage')->getMessageByMobile($this->company_id,$this->recipient_id,1,$request);

		if($data['data'])
		{
			$this->ajaxReturn($data);
		}
		else
		{
			$this->assign('unReadNumber',$data['unReadNumber']);
		}

        $this->display();
	}


    public function detail($id = 0)
    {
    	$data = D('SystemMessage')->getMessageDetailByMobile($this->company_id,$this->recipient_id,1,$id);

    	if($data['code'])
    	{
		    $this->assign('detail',$data['detail']);

		    $this->assign('ticket',$data['ticket']);

		    $this->display();
	    }
    	else
        {
	        $this->error($data['msg']);
	    }
    }


	public function delete()
	{
		$result = D('SystemMessage')->deleteMessageByMobile($this->company_id,$this->recipient_id,1);

        $this->ajaxReturn($result);
	}


	public function updateMessageStatus()
	{
		$result = D('SystemMessage')->updateMessageStatusByMobile($this->company_id,$this->recipient_id,1);

		$this->ajaxReturn($result);
	}
}

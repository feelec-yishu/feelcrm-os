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

use Index\Common\BasicController;

class HelpController extends BasicController
{
	protected $updateFirmFields = array('company_id','logo','logo_name','link');


	//设置公司信息
	public function setting()
	{
        $detail = M('Company')->where(["company_id"=>$this->_company_id])->find();

		if(IS_POST)
		{
			$data = $this->checkFirmEdit();

			$data['company_id'] = $this->_company_id;

			if(!$detail)
			{
				if($company_id = M('Company')->add($data))
				{
					$this->ajaxReturn(['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('setting/setFirm')]);
				}
				else
				{
					$this->ajaxReturn(['status'=>0,'msg'=>L('SUBMIT_FAILED')]);
				}
			}
			else
			{
				$save = D('Company')->save($data);

				if($save === false)
				{
					$this->ajaxReturn(['status'=>0,'msg'=>L('UPDATE_FAILED')]);
				}
				else
				{
					$this->ajaxReturn(['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('help/setting')]);
				}
			}
		}
		else
		{
			$token = C('NEED_TOKEN') ? '?token='.$detail['login_token'] : '';

			$login_token = C('NEED_TOKEN') ? '?login_token='.$detail['login_token'] : '';

			$this->assign('company',$detail);

			$this->assign('token',$token);

			$this->assign('login_token',$login_token);

			$this->display();
		}		
	}



    //设置公司验证
	public function checkFirmEdit()
	{
		$data = checkFields(I('post.company'), $this->updateFirmFields);

		$data['banner'] = I('post.banner') ? I('post.banner') : '';

		return $data;
	}
}

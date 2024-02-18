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

class NotifyController extends BasicController
{
	protected $_crm_filter = ['mail_notify','sms_notify','wechat_notify','dingtalk_notify','workwx_notify','allot_clue_notify','draw_clue_notify','transfer_clue_notify','follow_clue_notify','clue_notifier','new_clue_notify','allot_notify','draw_notify','transfer_notify','follow_notify','new_customer_notify','customer_notifier','new_order_notify','order_notifier','nofollow_customer_notify','nofollow_customer_notifier','noreturnvisit_customer_notify','noreturnvisit_customer_notifier','follow_comment_notify','contract_expire_notify','contract_expire_notifier','new_contract_notify','contract_notifier','receipt_examine_notify','invoice_examine_notify','examine_operate_notify'];

	public function config()
	{
		$company = M('company')->where(['company_id'=>$this->_company_id])->find();

		$config = $crmConfig = [];

		if($company['crm_auth'] == 10)
		{
			$crmConfig = getCrmDbModel('notify_config')->where(['company_id'=>$this->_company_id])->find();
		}

        if(IS_POST)
		{
            $result = false;

			if($company['crm_auth'] == 10)
			{
				$crmData = checkFields(I('post.crmConfig'),$this->_crm_filter);

				$crmData['clue_notifier'] = I('post.clue_notifier');

				$crmData['customer_notifier'] = I('post.customer_notifier');

				$crmData['nofollow_customer_notifier'] = I('post.nofollow_customer_notifier');

				$crmData['noreturnvisit_customer_notifier'] = I('post.noreturnvisit_customer_notifier');

				//$crmData['order_notifier'] = I('post.order_notifier');

				$crmData['contract_expire_notifier'] = I('post.contract_expire_notifier');

				//$crmData['contract_notifier'] = I('post.contract_notifier');

				$first_member = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'is_first'=>2])->getField('member_id');

				foreach($this->_crm_filter as $k3=>&$v3)
				{
					if(!in_array($v3,array_keys($crmData)))
					{
						if($v3 == 'clue_notifier' || $v3 == 'customer_notifier' || $v3 == 'order_notifier' || $v3 == 'nofollow_customer_notifier' || $v3 == 'noreturnvisit_customer_notifier' || $v3 == 'contract_expire_notifier' || $v3 == 'contract_notifier')
						{
							if(!$crmData[$v3])
							{
								$crmData[$v3] = $first_member;
							}
						}
						else
						{
							$crmData[$v3] = 20;
						}
					}
					else
					{
						if($v3 == 'clue_notifier' || $v3 == 'customer_notifier' || $v3 == 'order_notifier' || $v3 == 'nofollow_customer_notifier' || $v3 == 'noreturnvisit_customer_notifier' || $v3 == 'contract_expire_notifier' || $v3 == 'contract_notifier')
						{
							if(!$crmData[$v3])
							{
								$crmData[$v3] = $first_member;
							}
						}
						else
						{
							$crmData[$v3] = 10;
						}

					}
				}

				if($crmConfig)
				{
                    $result = getCrmDbModel('notify_config')->where(['company_id'=>$this->_company_id])->save($crmData);
				}
				else
				{
					$crmData['company_id'] = $this->_company_id;

                    $result = getCrmDbModel('notify_config')->add($crmData);
				}
			}

            if($result === false)
            {
                $this->ajaxReturn(['status'=>0,'msg'=>L('UPDATE_FAILED')]);
            }
            else
            {
                $this->ajaxReturn(['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('config')]);
            }
		}
		else
		{
			$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->field('name,member_id,is_first')->select();

			foreach($members as $key=>&$val)
			{
				$val['id'] = $val['member_id'];
			}

			$this->assign('members',json_encode($members));

			$this->assign('config',$config);

			$this->assign('crmConfig',$crmConfig);

			$this->assign('ticketAuth',$this->_company['ticket_auth']);

			$this->display();
		}
	}
}

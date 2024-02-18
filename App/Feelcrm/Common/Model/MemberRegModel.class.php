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

use phpmailerException;

class MemberRegModel extends BasicModel
{
	protected $autoCheckFields = false;

	protected static $_company = [];

	protected static $_insertMemberField = ['username','password','name'];

	protected static $_insertFirmFields = ['firm_name','firm_addr'];

	public function register($company_id)
	{
		$data = $this->checkCreate($company_id);

		if($data['status'] == 2)
		{
			$data['member']['is_first_customer'] = 2;

			if($member_id = M('Member')->add($data['member']))
			{
				saveFeelDeskEncodeId($company_id,$member_id,'Member');

				$data['firm']['access_token'] = getAccessToken($company_id,'FeelDesk');

				$firm_id = M('Firm')->add($data['firm']);//添加会员的公司信息

				M('Member')->where(['member_id'=>$member_id])->save(['firm_id'=>$firm_id]);//该会员的第一个账户

				saveFeelDeskEncodeId($company_id,$firm_id,'Firm');

//              创建默认角色 - 获取会员权限菜单
				$menu_id = D('UserMenu')->getMemberMenuIds();

//				创建默认角色 - 角色数据
				$role = ['company_id'=>$company_id,'firm_id'=>$firm_id,'name'=>'管理员','is_manager'=>10,'menu_id'=>json_encode($menu_id),'create_time'=>NOW_TIME];

				if($role_id = M('firm_role')->add($role))
				{
//					更新角色UUID
					saveFeelDeskEncodeId($company_id,$role_id,'FirmRole');

//				    创建会员角色数据
					$customer_role = ['company_id'=>$company_id,'firm_id'=>$firm_id,'member_id'=>$member_id,'role_id'=>$role_id];

					if($id = M('customer_role')->add($customer_role))
					{
						session('reg_code',null);

						session('reg_account',null);

						session('reg_code_lifetime',null);

						$result = ['status'=>2,'msg'=>L('REG_SUCCESS')];
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('REG_FAILED').'，会员角色创建失败'];
					}
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('REG_SUCCESS').'，会员角色创建失败'];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('REG_FAILED')];
			}
		}
		else
		{
			$result = $data;
		}

		return $result;
	}


	private function checkCreate($company_id)
	{
		$member = checkFields(I('post.register'), self::$_insertMemberField);

		$firm = checkFields(I('post.firm'), self::$_insertFirmFields);

		$code = (int) I('post.code','');

		if(!$code)
		{
			$result = ['status'=>0,'msg'=>L('ENTER_IMAGE_CODE')];
		}
		else if(intval((NOW_TIME - session('reg_code_lifetime'))/60) > 30)
		{
			session('reg_code',null);

			$result = ['status'=>0,'msg'=>L('CODE_EXPIRED')];
		}
		else if($code !== session('reg_code'))
		{
			$result = ['status'=>0,'msg'=>L('CODE_ERROR')];
		}
//		else if($member['username'] != session('reg_account'))
//		{
//			$result = ['status'=>0,'msg'=>'注册账号与验证账号不一致'];
//		}
		else
		{
			$member['account'] = $member['username'];

			$result = D('Member')->VerifyCreateData($member,2,$company_id,$firm);
		}

		return $result;
	}

	public function createDefaultRole($company_id,$member_id,$is_create_firm = false,$firm_id = 0,$firm = [])
	{
		if($is_create_firm == true)
		{
			saveFeelDeskEncodeId($company_id,$member_id,'Member');

			$firm['access_token'] = getAccessToken($company_id,'FeelDesk');

			$firm_id = M('Firm')->add($firm);//添加会员的公司信息

			saveFeelDeskEncodeId($company_id,$firm_id,'Firm');

			M('Member')->where(['member_id'=>$member_id])->setField('firm_id',$firm_id);//该会员的第一个账户
		}

//		查询会员管理员角色
		$role_id = M('firm_role')->where(['company_id'=>$company_id,'firm_id'=>$firm_id,'is_manager'=>10])->getField('role_id');

		if(!$role_id)
		{
//           创建默认角色 - 获取会员权限菜单
			$menu_id = D('UserMenu')->getMemberMenuIds();

//		     创建默认角色 - 角色数据
			$role = ['company_id'=>$company_id,'firm_id'=>$firm_id,'name'=>'管理员','is_manager'=>10,'menu_id'=>json_encode($menu_id),'create_time'=>NOW_TIME];

			$role_id = M('firm_role')->add($role);

//			 更新角色UUID
			saveFeelDeskEncodeId($company_id,$role_id,'FirmRole');
		}

		if($role_id)
		{
//			  创建会员角色数据
			$customer_role = ['company_id'=>$company_id,'firm_id'=>$firm_id,'member_id'=>$member_id,'role_id'=>$role_id];

			if($id = M('customer_role')->add($customer_role))
			{

				$result = ['status'=>2,'msg'=>L('REG_SUCCESS')];
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('REG_FAILED').'，会员角色创建失败'];
			}
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('REG_SUCCESS').'，会员角色创建失败'];
		}

		return $result;
	}
}

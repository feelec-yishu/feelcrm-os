<?php

namespace Common\Model;

class CustomerAuthModel extends BasicModel
{
	protected $autoCheckFields = false;

	/*
	* 检查会员权限
	* @param int   $action    菜单url
	* @param int   $firm_id   会员公司ID
	* @param int   $member_id 会员ID
	* @return bool
	*/
	public function checkCustomerAuth($action = '',$firm_id = 0,$member_id = 0)
	{
		if(!in_array($action,['Index/index','Index/welcome','Member/assign_staff','Clean/cache','Ticket/satisfy','Notice/index','Notice/detail','Setting/index']))
		{
			$menu_id = D('UserMenu')->isExistedByField(['menu_action'=>$action]);

//			根据会员ID，查询会员所属角色
			$role_id = M('customer_role')->where(['firm_id'=>$firm_id,'member_id'=>$member_id])->getField('role_id');

//			获取角色权限
			$roleAuthMenuId = M('firm_role')->where(['role_id'=>$role_id])->getField('menu_id');

			if(!in_array($menu_id,json_decode($roleAuthMenuId)))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return true;
		}
	}


	/*
	* 获取会员权限
	* @param int   $firm_id   会员公司ID
	* @param int   $member_id 会员ID
	* @return bool
	*/
	public function getCustomerAuth($firm_id = 0,$member_id = 0)
	{
//		  根据会员ID，查询会员所属角色
		$role_id = M('customer_role')->where(['firm_id'=>$firm_id,'member_id'=>$member_id])->getField('role_id');

//		  获取角色权限
		$roleAuthMenuId = M('firm_role')->where(['role_id'=>$role_id])->getField('menu_id');

		return json_decode($roleAuthMenuId,true);
	}
}

<?php
/**
 * Created by PhpStorm.
 * User: navyy
 * Date: 2017.07.21
 * Time: 9:56
 */
namespace Common\Model;

use Common\Model\BasicModel;

class CrmSelectFieldModel extends BasicModel
{
    protected $autoCheckFields = false;

    protected $temp = [];

	public function getCustomerAuth($member,$company_id,$member_id,$customer_auth = '',$showtype = 'index')
	{
		if($showtype == 'pool')
		{
			$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'poolAll',$member['role_id'],'crm');

			$this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'poolGroup',$member['role_id'],'crm');

			$this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'poolOwn',$member['role_id'],'crm');
		}
		elseif($showtype == 'clue')
		{
			$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'clueAll',$member['role_id'],'crm');

			$this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'clueGroup',$member['role_id'],'crm');

			$this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'clueOwn',$member['role_id'],'crm');
		}
		elseif($showtype == 'cluePool')
		{
			$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'cluePoolAll',$member['role_id'],'crm');

			$this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'cluePoolGroup',$member['role_id'],'crm');

			$this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'cluePoolOwn',$member['role_id'],'crm');
		}
		elseif($showtype == 'contract')
		{
			$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'contractsAll',$member['role_id'],'crm');

			$this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'contractsGroup',$member['role_id'],'crm');

			$this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'contractsOwn',$member['role_id'],'crm');
		}
		elseif($showtype == 'account')
		{
			$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'accountAll',$member['role_id'],'crm');

			$this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'accountGroup',$member['role_id'],'crm');

			$this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'accountOwn',$member['role_id'],'crm');
		}
		elseif($showtype == 'receipt')
		{
			$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'receiptAll',$member['role_id'],'crm');

			$this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'receiptGroup',$member['role_id'],'crm');

			$this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'receiptOwn',$member['role_id'],'crm');
		}
		elseif($showtype == 'invoice')
		{
			$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'invoiceAll',$member['role_id'],'crm');

			$this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'invoiceGroup',$member['role_id'],'crm');

			$this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'invoiceOwn',$member['role_id'],'crm');
		}
		else
		{
			$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'all',$member['role_id'],'crm');

			$this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'group',$member['role_id'],'crm');

			$this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'own',$member['role_id'],'crm');
		}
		
		if(I('get.customer_auth') || $customer_auth)
		{
			if(!$customer_auth)
			{
				$customer_auth = I('get.customer_auth');
			}

			if($customer_auth == 'pool')
			{
				if($this->all_view_auth)
				{
					$CrmmemberRoleCrmauth = CrmmemberRoleCrmauth($member,$company_id,$member_id,'all',$showtype);
				}
				else if($this->group_view_auth)
				{
					$CrmmemberRoleCrmauth = CrmmemberRoleCrmauth($member,$company_id,$member_id,'group',$showtype);
				}
				else
				{
					$CrmmemberRoleCrmauth = CrmmemberRoleCrmauth($member,$company_id,$member_id,'own',$showtype);
				}
			}
			else if($customer_auth == 'cluePool')
			{
				if($this->all_view_auth)
				{
					$CrmmemberRoleCrmauth = CrmmemberRoleCrmauth($member,$company_id,$member_id,'all',$showtype);
				}
				else if($this->group_view_auth)
				{
					$CrmmemberRoleCrmauth = CrmmemberRoleCrmauth($member,$company_id,$member_id,'group',$showtype);
				}
				else
				{
					$CrmmemberRoleCrmauth = CrmmemberRoleCrmauth($member,$company_id,$member_id,'own',$showtype);
				}
			}
			else if($customer_auth == 'all')
			{           
				if(!$this->all_view_auth)
				{
					return false;
				}
				
				$CrmmemberRoleCrmauth = CrmmemberRoleCrmauth($member,$company_id,$member_id,'all',$showtype);
			}
			else if($customer_auth == 'group')
			{
				if(!$this->group_view_auth)
				{
					return false;
				}		
				
				$CrmmemberRoleCrmauth = CrmmemberRoleCrmauth($member,$company_id,$member_id,'group',$showtype);
			}
			else
			{
				if(!$this->own_view_auth)
				{
					return false;
				}
				
				$CrmmemberRoleCrmauth = CrmmemberRoleCrmauth($member,$company_id,$member_id,'own',$showtype);
			}
			
			$memberRoleArr = $CrmmemberRoleCrmauth['field'];
			
			$users = $CrmmemberRoleCrmauth['users'];
			
			$members = $CrmmemberRoleCrmauth['members'];
			
		}
		else
		{
			$memberRoleArr = CrmmemberRoleCrmauth($member,$company_id,$member_id,'',$showtype);
			
			if($this->all_view_auth)
			{
				$customer_auth = 'all';
			}
			elseif($this->group_view_auth)
			{
				$customer_auth = 'group';
			}
			else
			{
				$customer_auth = 'own';
			}
		}
		
		return ['memberRoleArr'=>$memberRoleArr,'customer_auth'=>$customer_auth,'users'=>$users,'members'=>$members];
	}
}
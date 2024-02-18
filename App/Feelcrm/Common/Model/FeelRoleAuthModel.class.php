<?php

namespace Common\Model;

use Think\Model;

class FeelRoleAuthModel extends Model
{
    protected $autoCheckFields = false;

	/*
	* 通过角色ID查询角色权限，存入角色权限缓存
	* @param int $role_id       角色ID
	* @param string $source     系统
	* @return array
	*/
	public function getMenuIdsByRoleId($role_id = 0,$system = 'ticket')
	{
        if($system == 'crm')
        {
            $roleAuthModel = getCrmDbModel('role_auth');
        }
        else
        {
            $roleAuthModel = M('role_auth');
        }

		if(S('menuData/'.$system.'_role_auth_'.session('company_id').'_'.$role_id))
		{
			$auth = S('menuData/'.$system.'_role_auth_'.session('company_id').'_'.$role_id);
		}
		else
		{
			$roleMenuIds = $roleAuthModel->where(['company_id'=>session('company_id'),'role_id'=>$role_id])->getField('menu_id');

			$auth = json_decode($roleMenuIds,true);

			S('menuData/'.$system.'_role_auth_'.session('company_id').'_'.$role_id,$auth,3600);
		}

		return $auth;
    }
}

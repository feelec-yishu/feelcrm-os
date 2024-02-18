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

class RoleAuthModel extends BasicModel
{
	protected $pk   = 'role_id';

	protected $tableName = 'role_auth';

	/*
	* 分配给角色最高权限
	* @param int $company_id    公司ID
	* @param int $role_id       角色ID
	* @param int $systemAuth    系统权限
	*/
	public function grantHighestAuth($company_id = 0,$role_id = 0,$systemAuth = [])
	{
		/*$version_id = M('company_version')->where(['company_id'=>$company_id])->getField('version_id');

		$ticketVersionMenuIds = M('version_menu')->where(['version_id'=>$version_id])->getField('menu_id');*/

		$ticketVersionMenuIds = M('company_version')->where(['company_id'=>$company_id])->getField('system_menu');

		$auth_id = $this->where(['company_id'=>$company_id,'role_id'=>$role_id])->getField('id');

		if(!$auth_id)
		{
			$this->add(['company_id'=>$company_id,'role_id'=>$role_id,'menu_id'=>$ticketVersionMenuIds]);
		}
		else
		{
			$this->save(['id'=>$auth_id,'menu_id'=>$ticketVersionMenuIds]);
		}

//		CRM系统最高权限
		if($systemAuth['crm_auth'] == 10)
		{
			//$crmVersionMenuIds = getCrmDbModel('version_menu')->where(['version_id'=>$version_id])->getField('menu_id');

			$crmVersionMenuIds = M('company_version')->where(['company_id'=>$company_id])->getField('crm_menu');

			$auth_id = getCrmDbModel('role_auth')->where(['company_id'=>$company_id,'role_id'=>$role_id])->getField('id');

			if(!$auth_id)
			{
				getCrmDbModel('role_auth')->add(['company_id'=>$company_id,'role_id'=>$role_id,'menu_id'=>$crmVersionMenuIds]);
			}
			else
			{
				getCrmDbModel('role_auth')->save(['id'=>$auth_id,'menu_id'=>$crmVersionMenuIds]);
			}
		}
	}


	/*
	* 分配角色权限时获取角色权限菜单信息
	* @param int $role_id       角色ID
	* @param int $version_id    版本ID
	* @param int $menuName      菜单名称字段，用于多语言
	* @param int $system        系统
	*/
	public function getRoleAuthMenus($role_id = 0,$company_id = 0,$version_id = 0,$menuName = 'menu_name',$system = '')
	{
		if($system == 'crm')
		{
			$versionMenuModel = getCrmDbModel('version_menu');

			$menuModel = getCrmDbModel('menu');

			$versionMenu = M('company_version')->where(['company_id'=>$company_id])->getField('crm_menu');
		}
		else
		{
			$versionMenuModel = M('version_menu');

			$menuModel = M('menu');

			$versionMenu = M('company_version')->where(['company_id'=>$company_id])->getField('system_menu');
		}

//    	  角色 —— 权限菜单ID
		$auth = D('FeelRoleAuth')->getMenuIdsByRoleId($role_id,$system);

//        版本权限
		if(!$versionMenu) $versionMenu = $versionMenuModel->where(['version_id'=>$version_id])->getField('menu_id');

//	     当前角色权限菜单信息
		$menus = $menuModel->where(['menu_id'=>['in',json_decode($versionMenu,true)]])
			->field("menu_id,parent_id,{$menuName}")
			->select();

		$auth = $auth ? $auth : ['-1'];//此代码主要用于分配角色权限时，该角色没有权限需要显示菜单树

		$menus = getMenuTree($menus,'menu_id','parent_id','children',0,$auth);

		return $menus;
	}


	/*
	 * 更新角色权限
	 * @param int       $company_id 公司ID
	 * @param int       $role_id    角色ID
	 * @param string    $system     系统
	 * @return mix
	 */
	public function updateRoleAuthMenus($company_id,$role_id,$system)
	{
		if($system == 'crm')
		{
			$menuIds = I('post.crm',[]);

			$roleAuthModel = getCrmDbModel('role_auth');

			if(!$menuIds)
			{
				$isSwitchCrm = I('post.isSwitchCrm') ? I('post.isSwitchCrm') : 0;

				if($isSwitchCrm != 1)
				{
					$menuIds = $roleAuthModel->where(['company_id' => $company_id, 'role_id' => $role_id])->getField('menu_id');

					$menuIds = json_decode($menuIds, true);
				}
			}
		}
		else
		{
			$menuIds = I('post.ticket',[]);

			$roleAuthModel = M('role_auth');
		}

		sort($menuIds);

		$authMenuIds = json_encode($menuIds);

		$auth_id = $roleAuthModel->where(['company_id'=>$company_id,'role_id'=>$role_id])->getField('id');

		if($auth_id)
		{
			$result = $roleAuthModel->where(['id'=>$auth_id])->save(['menu_id'=>$authMenuIds]);
		}
		else
		{
			$result = $roleAuthModel->add(['company_id'=>$company_id,'role_id'=>$role_id,'menu_id'=>$authMenuIds]);
		}

		if($result !== false)
		{
//            更新角色权限缓存
			S('menuData/'.$system.'_role_auth_'.$company_id.'_'.$role_id,$menuIds,3600);
		}
	}


	/*
	* 通过Menu菜单校验角色操作权限
	* @param int $company_id 公司ID
	* @param string $action    菜单URL
	* @param int    $role_id   角色ID
	* @param string $system   系统 crm
	* @return int
	*/
	public function checkRoleAuthByMenu($company_id = 0,$action = '',$role_id = 0,$system = 'ticket')
	{
		$Model = M('menu');

		if($system == 'crm')
		{
			$Model = getCrmDbModel('menu');
		}

		if(!$action)
		{
			$action = strtolower(CONTROLLER_NAME . '/' . ACTION_NAME);
		}

		if(!in_array($action,['index/base','index/index','index/welcome','notice/index','notice/detail','ticket/service_report','customer/record','customer/getRecord']))
		{
			$auth = S('menuData/'.$system.'_role_auth_'.$company_id.'_'.$role_id);

			$menu_id = $Model->where(['menu_action'=>$action])->getField('menu_id');

			if(!in_array($menu_id,$auth))
			{
				return false;
			}

			return $menu_id;
		}

		return true;
	}
}

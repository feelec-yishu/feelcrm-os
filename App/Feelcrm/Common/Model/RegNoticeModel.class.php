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


class RegNoticeModel extends BasicModel
{
	protected $autoCheckFields = false;

	function notice($company,$company_id,$member,$regData=[])
	{
			M('company_audit')->add([
				'company_id'=>  $company_id,
				'open_time' => time(),
				'due_time'  => isset($regData['expire_time']) && $regData['expire_time'] ? $regData['expire_time'] : strtotime('+2 week'),
				'open_type' => 1,
				'activity'  => isset($regData['feelec_enable']) && $regData['feelec_enable'] == 20 ? 1 : 2
			]
		);

		file_put_contents("../App/Feelcrm/Runtime/Logs/createMerchant.txt", ' data = '.var_export($regData,true)."\r\n", FILE_APPEND);

		// 专业版
		$companyVersion = ['company_id'=>$company_id,'version_id'=>2,'create_time'=>time()];

		if(isset($regData['permissions']['feelec_menu']) && $regData['permissions']['feelec_menu'])
		{
			$menus = $regData['permissions']['feelec_menu'];

			if($menus['system'])
			{
				$menus['system'] = json_encode($menus['system']);

				$companyVersion['system_menu'] = $menus['system'];

				$version_menu = $menus['system'];
			}

			if($menus['crm'])
			{
				$menus['crm'] = json_encode($menus['crm']);

				$companyVersion['crm_menu'] = $menus['crm'];

				$crm_version_menu = $menus['crm'];
			}
		}

		M('company_version')->add($companyVersion);

		// 获取版本下的菜单
		if(!isset($version_menu) || !$version_menu)
		{
			$version_menu = M('version_menu')->where(['version_id'=>2])->getField('menu_id');
		}

		if(!isset($crm_version_menu) || !$crm_version_menu)
		{
			$crm_version_menu = getCrmDbModel('version_menu')->where(['version_id'=>2])->getField('menu_id');
		}

		// 设置角色权限
		M('RoleAuth')->add(['company_id'=>$company_id,'role_id'=>$member['role_id'],'menu_id'=>$version_menu]);

		getCrmDbModel('RoleAuth')->add(['company_id'=>$company_id,'role_id'=>$member['role_id'],'menu_id'=>$crm_version_menu]);

		// 开通工单和CRM、英文版、日文版
        // M('company')->where(['company_id'=>$company_id])->save(['ticket_auth'=>20,'crm_auth'=>10,'en_auth'=>10,'jp_auth'=>10]);
	}

	function noticeApi($company_id)
	{
		/* api创建商户 */

		//自动开通半个月
		$companyAuth = ['company_id'=>$company_id,'open_time'=>time(),'due_time'=>strtotime('+2 week'),'open_type'=>1,'activity'=>2];

		M('company_audit')->add($companyAuth);

		//专业版
		$companyVersion = ['company_id'=>$company_id,'version_id'=>2,'create_time'=>time()];

		M('company_version')->add($companyVersion);

		//获取版本下的菜单
		$version_menu = M('version_menu')->where(['version_id'=>2])->getField('menu_id');

		$crm_version_menu = getCrmDbModel('version_menu')->where(['version_id'=>2])->getField('menu_id');

		/* 获取超级管理员ID  */
		$role_id= M('role')->where(['company_id'=>$company_id])->order('role_id asc')->limit(1)->getField('role_id');

		$roleAuth = ['company_id'=>$company_id,'role_id'=>$role_id,'menu_id'=>$version_menu];

		$crmRoleAuth = ['company_id'=>$company_id,'role_id'=>$role_id,'menu_id'=>$crm_version_menu];

		M('RoleAuth')->add($roleAuth);

		getCrmDbModel('RoleAuth')->add($crmRoleAuth);

		/* 获取默认部门ID，打开默认部门crm权限  */
		$group_id= M('group')->where(['company_id'=>$company_id])->order('group_id asc')->limit(1)->getField('group_id');

		M('group')->save(['group_id'=>$group_id,'crm_auth'=>10]);

		/* api创建商户 */
	}
}

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

use Common\Model\BasicModel;

class RoleModel extends BasicModel
{
	protected $pk   = 'role_id';

	protected $tableName = 'role';


	/*
	* @param int $company_id 公司ID
	* @param array $deleteGroup 待删除的角色信息
	* @param int $transfer_group_id 转移数据的角色ID
	* @return array
	*/
	public function deleteRole($company_id,$deleteRole,$transfer_role_id,$sync_to_manage = false)
	{
		$where = ['company_id'=>$company_id,'role_id'=>$deleteRole['role_id']];

		$delete = M('role')->where($where)->delete();

		if($delete)
		{
            // 如果删除的角色是默认角色，则将转移的角色设为默认角色
			if($deleteRole['is_default'] == 10)
			{
				$this->where(['company_id'=>$company_id,'role_id'=>$transfer_role_id])->setField('is_default',10);
			}

            // 更新相关用户角色信息
			M('member')->where(['company_id'=>$company_id,'type'=>1,'role_id'=>$deleteRole['role_id']])->setField(['role_id'=>$transfer_role_id]);

			// 删除相关角色权限数据
			M('role_auth')->where(['company_id'=>$company_id,'role_id'=>$deleteRole['role_id']])->delete();

			$result = ['errcode'=>0,'msg'=>L('DELETE_SUCCESS')];
		}
		else
		{
			$result = ['errcode'=>1,'msg'=>L('DELETE_FAILED')];
		}

		return $result;
	}
}

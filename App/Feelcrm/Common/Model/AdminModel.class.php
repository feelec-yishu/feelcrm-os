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

class AdminModel extends BasicModel
{
	protected $pk   = 'admin_id';

	protected $tableName = 'admin';

    public $updatePasswordField = ['password','old','new','sure'];

	public function getAdminByUsername($username)
	{
		$res = $this->where(['username'=>$username])->find();

		return $res;
	}


	public function getAdminByEmail($email)
	{
		$res = $this->where(['email'=>$email])->find();

		return $res;
	}


	public function getAdminByMobile($mobile)
	{
		$res = $this->where(['mobile'=>$mobile])->find();

		return $res;
	}


	public function checkAdminExistByField($field = '',$value = '',$admin_id = 0)
    {
        return $this->where([$field=>$value,'admin_id'=>['neq',$admin_id]])->getField($field);
    }


    public function updatePassword($admin,$admin_id)
    {
        $data = $this->checkFields($admin, $this->updatePasswordField);

        $where = ['admin_id'=>$admin_id];

        $password = $this->where($where)->getField('password');

        if(!empty($data['new']))
        {
            if(!isPassword($data['new'])) return ['status'=>0,'msg'=>L('PASSWORD_FORMAT_ERROR')];

            if(md5($data['old']) != $password) return ['status'=>0,'msg'=>L('PASSWORD_ERROR')];

            $data['password'] = md5($data['new']);
        }
        else
        {
            return ['status'=>0,'msg'=>L('ENTER_NEW_PASSWORD')];
        }

        $save = $this->where($where)->save($data);

        if($save === false)
        {
            return ['status'=>0,'msg'=>L('UPDATE_FAILED')];
        }
        else
        {
            return ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('index')];
        }
    }
}

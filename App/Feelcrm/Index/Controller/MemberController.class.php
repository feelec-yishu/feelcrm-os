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
use Think\Page;

class MemberController extends BasicController
{
	protected $insertField = ['group_id','role_id','account','password','mobile','email','name','nickname','closed','accountsecret','wechat'];

	protected $updateField = ['group_id','role_id','mobile','email','name','nickname','closed','accountsecret','wechat'];

	/* 用户列表 */
	public function index($group_id = 0,$role_id = 0)
	{
		$model  = M('Member');

		$where = ['company_id'=>$this->_company_id];

		$role = D('Role')->where($where)->field('role_id,role_name')->fetchAll();

		$group = D('Group')->where($where)->field('group_id,group_name')->fetchAll();

		if($group_id)
		{
			$field[] = "find_in_set('{$group_id}',group_id)";

			$this->assign('group_id',$group_id);
		}

		if($role_id)
		{
			$field['role_id'] = $role_id;

			$this->assign('role_id',$role_id);
		}

		$field['company_id'] = $this->_company_id;

		$field['type'] = 1;

		$field['closed'] = 0;

		if($keyword = I('get.keyword'))
		{
			$field['account|name|nickname|mobile'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$count = $model->where($field)->count('member_id');

		$Page = new Page($count, 100);

		$list = $model->where($field)->limit($Page->firstRow, $Page->listRows)->select();

//        权限 - 创建用户
		$auth['create'] = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'member/create',$this->_member['role_id']);

//        权限 - 修改用户
		$auth['editor'] = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'member/editor',$this->_member['role_id']);

//        权限 - 删除用户
		$auth['delete'] = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'member/delete',$this->_member['role_id']);

//        权限 - 重置密码
		$auth['reset'] = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'member/reset',$this->_member['role_id']);

		$data = ['member'=>$list,'auth'=>$auth,'page'=>$Page->show(),'roles'=>$role,'groups'=>$group,];

		$this->assign('data',$data);

		$this->display();
	}


	/* 添加用户 */
	public function create()
	{
		if(IS_POST)
		{
			$member = $this->checkCreate();

            $member['company_id'] = $this->_company_id;

            $member_password = $member['nomd5_password'];

            unset($member['nomd5_password']);

			if($member_id = M('Member')->add($member))
			{
				saveFeelDeskEncodeId($this->_company_id,$member_id,'Member');

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'reloadType'=>'parent'];
			}
			else
			{
			    $result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
			}

            $this->ajaxReturn($result);
		}
		else
		{
		    $crm_auth = M('company')->where(['company_id'=>$this->_company_id])->getField('crm_auth');

			$where = ['company_id'=>$this->_company_id,'closed'=>0];

//			部门
			$department = M('Group')->where($where)->field('group_id,group_name,parent_id,level')->select();

			$departmentData = getSubjectTree($department,'group_id','parent_id','child');

			$role = D('Role')->where($where)->field('role_id,role_name')->fetchAll();

			$this->assign('roles',$role);

			$this->assign('jsonDepartmentData',json_encode($departmentData));

			$this->assign('crm_auth',$crm_auth);

			$this->display();
		}
	}


    private function checkCreate()
	{
		$data = checkFields(I('post.member'), $this->insertField);

		$data['group_id'] = I('post.group_id');

        $result = D('Member')->VerifyCreateData($data,1,$this->_company_id);

        if($result['status'] === 0) $this->ajaxReturn($result);

        return $result;
	}


	/* 编辑用户 */
	public function editor($id = '')
	{
	    $member_id = decrypt($id,'MEMBER');

        $where = ['company_id'=>$this->_company_id,'member_id'=>$member_id,'type'=>1];

	    if(!$detail = M('Member')->where($where)->field('password',true)->find())
	    {
		    $this->returnError(L('USER_NOT'),U('Index/welcome'));
	    }

		if(IS_POST)
		{
			$member = $this->checkEditor($member_id);
/*
			if(isset($workPhone) && $workPhone)
			{
				if(!$member['wechat'])
				{
					$this->ajaxReturn(['status'=>0,'msg'=>L('WECHAT_NUMBER').L('IS_REQUIRED')]);
				}

				if(!$member['accountsecret'])
				{
					$this->ajaxReturn(['status'=>0,'msg'=>'AccountSecret'.L('IS_REQUIRED')]);
				}
			}
*/

			$save = M('Member')->where($where)->save($member);

			if($save === false)
			{
				$result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
				$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'reloadType'=>'parent'];

				if($member_id == $this->member_id)
				{
                    session('index',M('member')->where($where)->field('password',true)->find());
                }
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$where = ['company_id'=>$this->_company_id,'closed'=>0];

//			部门
			$department = M('Group')->where($where)->field('group_id,group_name,parent_id,level,manager_id')->select();

			$departmentData = getSubjectTree($department,'group_id','parent_id','child');

			$group_id = M('member')->where(['company_id'=>$this->_company_id,'member_id'=>$member_id])->getField('group_id');

			$detail['group_id'] = json_encode(explode(',',$group_id));

//			角色
			$role = D('Role')->where($where)->field('role_id,role_name')->fetchAll();

			$this->assign('roles',$role);

			$this->assign('jsonDepartmentData',json_encode($departmentData));

			$this->assign('detail',$detail);

			$this->display();
		}
	}


    private function checkEditor($member_id)
	{
		$data = checkFields(I('post.member'), $this->updateField);

		$data['group_id'] = I('post.group_id');

		if(!$data['group_id'])
		{
			$result = ['status'=>0,'msg'=>L('SELECT_SECTOR')];
		}
		else if(!$data['role_id'])
		{
			$result = ['status'=>0,'msg'=>L('SELECT_ROLE')];
		}
		else
		{
			$result = D('Member')->VerifyEditorData($data,$member_id);
		}

        if($result['status'] === 0) $this->ajaxReturn($result);

        return $result;
	}


//    删除
    public function delete()
	{
	    if(IS_AJAX)
	    {
		    $id = IS_POST ? I('post.batch') : I('get.id');

            $result = D('Member')->deleteMemberForever($id,$this->_company_id);

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
    }


//    重置
	public function reset($id='')
	{
        if(IS_AJAX)
        {
            $result = D('Member')->resetPassword($id, $this->_company_id, 1);

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
	}
}

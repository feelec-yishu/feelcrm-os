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

use Crypto\CryptMessage;
use Index\Common\BasicController;

class RoleController extends BasicController
{
	protected static $_filter = ['role_name','is_supper','is_default','closed'];

    public function index($request = '',$page = 100)
	{
        if(IS_AJAX)
        {
            if($request == 'assign')
            {
                $count = M('member')->where(['company_id'=>$this->_company_id,'type'=>1])->count();

                $Page = new \Think\Page($count, $page);

                $member = M('member')->where(['company_id'=>$this->_company_id,'type'=>1])
                    ->field('member_id,role_id,name,mobile,email')
                    ->limit($Page->firstRow, $Page->listRows)->select();

                $result = ['data'=>$member,'pages'=>ceil($count/$page)];
            }
            else if($request == 'submit')
            {
                $userIds = I('post.userId');

                $role_id = I('get.id');

                if(!M('role')->where(['role_id'=>$role_id,'company_id'=>$this->_company_id])->getField('role_id'))
                {
                    $result = ['errcode'=>2,'msg'=>L('SELECT_ROLE')];
                }
                else if(!$userIds)
                {
                    $result = ['errcode'=>2,'msg'=>L('SELECT_USER')];
                }
                else
                {
                    M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>['in',$userIds]])->setField(['role_id'=>$role_id]);

                    M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'role_id'=>$role_id,'member_id'=>['not in',$userIds]])->setField(['role_id'=>0]);

                    $result = ['errcode'=>0,'msg'=>L('UPDATE_SUCCESS'),'isReload'=>1];
                }
            }
            else
            {
                $result = ['errcode'=>2,'msg'=>L('ILLEGAL')];
            }

            $this->ajaxReturn($result);
        }
        else
        {
            $model = M('Role');

            $where = [];

            if($keyword = I('get.keyword'))
            {
	            $where['role_name'] = ['LIKE', '%'.$keyword.'%'];

                $this->assign('keyword', $keyword);
            }

	        $where['company_id'] = $this->_company_id;

            $list = $model->where($where)->select();

//            权限 - 新增角色
	        $auth['create'] = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'role/create',$this->_member['role_id']);

//            权限 - 修改角色
	        $auth['editor'] = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'role/editor',$this->_member['role_id']);

//            权限 - 删除角色
	        $auth['delete'] = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'role/delete',$this->_member['role_id']);

//            权限 - 分配权限
	        $auth['auth'] = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'role/auth',$this->_member['role_id']);

//            权限 - 用户列表
	        $auth['member'] = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'member/index',$this->_member['role_id']);

	        $data = ['roles'=>$list,'auth'=>$auth,'roleJson'=>json_encode($list)];

	        $this->assign('data',$data);

            $this->display();
        }
    }


	public function create()
	{
		if(IS_POST)
		{
			$role = $this->checkRequestData('create');

			$role['company_id'] = $this->_company_id;

			if($role_id = M('Role')->add($role))
			{
				if($role['is_default'] == 10)
				{
					M('role')->where(['company_id'=>$this->_company_id,'is_default'=>10,'role_id'=>['neq',$role_id]])->setField(['is_default'=>20]);
				}

//                储存加密角色ID
				saveFeelDeskEncodeId($this->_company_id,$role_id,'Role');

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
			$this->display();
		}
	}


	public function editor($id = '')
	{
        $role_id = (int)decrypt($id,'ROLE');

		if(!$detail = M('Role')->where(['role_id'=>$role_id,'company_id'=>$this->_company_id])->find())
		{
			$this->returnError(L('ROLE_NOT'),U('Index/welcome'));
		}

		if(IS_POST)
		{
			$role = $this->checkRequestData('editor',$role_id);

			$role['role_id'] = $role_id;

			$save = M('Role')->save($role);

			if($save === false)
			{
			    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
				if($role['is_default'] == 10)
				{
					M('role')->where(['company_id'=>$this->_company_id,'is_default'=>10,'role_id'=>['neq',$role_id]])->setField(['is_default'=>20]);
				}

				$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'reloadType'=>'parent'];
			}

            $this->ajaxReturn($result);
		}
		else
		{
			$this->assign('detail',$detail);

			$this->display();
		}
	}


	private function checkRequestData($source = '',$role_id = 0)
	{
		$data = checkFields(I('post.role'), self::$_filter);

		$data['role_name'] = htmlspecialchars($data['role_name']);

		if(empty($data['role_name']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_ROLE_NAME')]);
		}

		$data['is_supper'] = $data['is_supper'] == 'on' ? 20 : 10;

		$data['closed'] = $data['closed'] == 'on' ? 0 : 1;

		if($source == 'editor')
		{
			if($data['is_default'] == 20)
			{
				$has_default = M('role')->where(['company_id'=>$this->_company_id,'is_default'=>10,'role_id'=>['neq',$role_id]])->getField('role_id');

				if(!$has_default)
				{
					$this->ajaxReturn(['status'=>0,'msg'=>L('DEFAULT_ROLE_NOTE1')]);
				}
			}
		}
		else
		{
			$data['create_time'] = NOW_TIME;

			$data['create_ip'] = get_client_ip();
		}

		return $data;
	}


	public function auth($id = '')
	{
        $role_id = decrypt($id,'ROLE');

        if($detail = M('role')->find($role_id))
		{
            $systemAuth = M('company')->where(['company_id'=>$this->_company_id])->field('ticket_auth,crm_auth')->find();

            if(IS_POST)
			{
				D('RoleAuth')->updateRoleAuthMenus($this->_company_id,$role_id,'ticket');

                if($systemAuth['crm_auth'] == 10)
                {
	                D('RoleAuth')->updateRoleAuthMenus($this->_company_id,$role_id,'crm');
                }

                $this->ajaxReturn(['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('role/auth',['id'=>encrypt($role_id,'ROLE')])]);
            }
			else
			{
                $version_id = M('company_version')->where(['company_id'=>$this->_company_id])->getField('version_id');

                $menuName = D('Menu')->getNameByLang('menu_name');

//    		      角色 —— 工单权限
               // if($systemAuth['ticket_auth'] == 10)
                //{
                    $ticketMenus = D('RoleAuth')->getRoleAuthMenus($role_id,$this->_company_id,$version_id,$menuName,'ticket');

                    $this->assign('ticketMenus',json_encode($ticketMenus));
               // }

//    		      角色 —— CRM权限
                if($systemAuth['crm_auth'] == 10)
                {
                    $crmMenus = D('RoleAuth')->getRoleAuthMenus($role_id,$this->_company_id,$version_id,$menuName,'crm');

                    $this->assign('crmMenus',json_encode($crmMenus));
                }

				$this->assign('role_name',$detail['role_name']);

				$this->assign('role_id',$role_id);

				$this->assign('detail',$detail);

				$this->assign('systemAuth',$systemAuth);

				$this->display();
            }
        }
		else
		{
            $this->returnError(L('ROLE_NOT'),U('Index/welcome'));
        }
    }


	public function delete()
	{
		$data = I('post.role');

		$data['role_id'] = decrypt($data['role_id'],'ROLE');

		if(!$detail = M('Role')->where(['role_id'=>$data['role_id'],'company_id'=>$this->_company_id])->find())
		{
			$result = ['errcode'=>1,'msg'=>L('ROLE_NOT')];
		}
		else if(!M('Role')->where(['role_id'=>$data['update_role_id'],'company_id'=>$this->_company_id])->getField('role_id'))
		{
			$result = ['errcode'=>1,'msg'=>L('ROLE_NOT')];
		}
		else if(empty($data['update_role_id']))
		{
			$result = ['errcode'=>1,'msg'=>L('SELECT_ROLE')];
		}
		else
		{
			$result = D('Role')->deleteRole($this->_company_id,$detail,$data['update_role_id']);
		}

		$this->ajaxReturn($result);
	}
}
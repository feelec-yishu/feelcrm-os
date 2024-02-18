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

class MemberModel extends BasicModel
{
	protected $pk   = 'member_id';

	protected $tableName = 'member';

    public $updateUserInfo = ['password','old','new','sure','email','mobile','name','nickname','face','face_name'];


    /*
	 * 检测字段值是否存在
	 * @param string $field         条件字段
	 * @param mix    $field         条件字段值
	 * @param int    $member_id   用户id
	 * @param int    $company_id    公司id
	 * @return mixed
  	 */
	public function getMemberByField($field='',$value='',$member_id='',$company_id='')
	{
        $where = [$field=>$value];

		if(empty($company_id))
		{
			if($member_id)
			{
                $where['member_id'] = ['neq',$member_id];
			}
		}
		else
		{
			if(empty($member_id))
			{
                $where['company_id'] = $company_id;
			}
			else
			{
                $where['company_id'] = $company_id;

                $where['member_id'] = ['neq',$member_id];
			}
		}

        $res = $this->where($where)->getField($this->pk);

		return $res;
	}



	/*
	 * 获取用户信息
	 * @param $field
	 */
	public function getUserInfoByUsername($username,$type,$company_id = 0,$isCheckClosed=false)
	{
	    $filter['account|mobile|email'] = $username;

	    $filter['type'] = $type;

	    $filter['member_type'] = 1;

		if($isCheckClosed) $filter['closed'] = 0;

	    if($company_id > 0) $filter['company_id'] = $company_id;

		return $this->where($filter)->field('member_id,company_id,account,password,closed')->find();
	}


    /* 通过用户ID检测用户是否存在 */
	public function checkMemberById($id = 0,$cid = 0,$type = 0)
    {
        $id = $this->where(['company_id'=>$cid,'member_id'=>$id,'type'=>$type])->getField('member_id');

        return $id;
    }



    /* 通过字段检查用户是否存在 */
    public function checkMemberByField($field = '',$value = '',$member_id = 0,$isCheckClosed=false)
    {
        $where = [$field=>$value];

        if($member_id > 0) $where['member_id'] = ['neq',$member_id];

	    if($isCheckClosed === true)
	    {
		    $where['closed'] = 0;
	    }

	    return $this->where($where)->getField('member_id');
    }


    public function VerifyCreateData($member = [],$type = 0,$company_id = 0,$firm = [],$firm_id = 0)
    {
        if(!$member['account']) return ['status'=>0,'msg'=>L('ACCOUNT_FORMAT_ERROR',['account'=>''])];

        // if(!isMobile($member['account']) && !isInternationalMobile($member['account']) && !isEmail($member['account']))
        // {
        //     return ['status'=>0,'msg'=>L('ACCOUNT_FORMAT_ERROR',['account'=>$member['account']])];
        // }

        if($this->checkMemberByField('account',$member['account'],0,true))
        {
	        return ['status'=>0,'msg'=>L('ACCOUNT_EXISTS')];
        }

        if(isMobile($member['account']) || isInternationalMobile($member['account']))
        {
            if($this->checkMemberByField('mobile',$member['account'],0,true))
            {
	            return ['status'=>0,'msg'=>L('ACCOUNT_EXISTS')];
            }

            $member['mobile'] = $member['account'];
        }

        if(isEmail($member['account']))
        {
            if($this->checkMemberByField('email',$member['account'],0,true))
            {
	            return ['status'=>0,'msg'=>L('ACCOUNT_EXISTS')];
            }

            $member['email'] = $member['account'];
        }

        if(empty($member['password'])) return ['status'=>0,'msg'=>L('ENTER_PASSWORD')];

        if(!isPassword($member['password'])) return ['status'=>0,'msg'=>L('PASSWORD_FORMAT_ERROR')];

        if(empty($member['name'])) return ['status'=>0,'msg'=>L('ENTER_NAME')];

		if($member['wechat'])
        {
            if($this->checkMemberByField('wechat',$member['wechat'],0,true))
            {
	            return ['status'=>0,'msg'=>L('WECHAT_NUMBER_EXISTS')];
            }
        }

        $member['company_id'] = $company_id;

        $member['type'] = $type;

        $member['nomd5_password'] = $member['password'];

        $member['password'] = md5($member['password']);

        $member['create_time'] = NOW_TIME;

        $member['create_ip'] = get_client_ip();

        if($type == 1)
        {
            if(!$member['role_id']) return ['status'=>0,'msg'=>L('SELECT_ROLE')];

            if(!$member['group_id']) return ['status'=>0,'msg'=>L('SELECT_SECTOR')];

            return $member;
        }
        else if($type == 2)
        {
            if($firm_id > 0)
            {
                $member['firm_id'] = $firm_id;

                return $member;
            }
            else
            {
                $member['is_first_customer'] = 2;

                if($firm)
                {
                    $firm['company_id'] = $company_id;

                    $firm['create_time'] = NOW_TIME;

                    $firm['create_ip'] = get_client_ip();
                }

                return ['status'=>2,'member'=>$member,'firm'=>$firm];
            }
        }
        else
        {
            return ['status'=>0,'msg'=>L('ILLEGAL')];
        }
    }


    public function VerifyEditorData($member=[],$member_id=0,$firm_id=0)
    {
        if(empty($member['name'])) return ['status'=>0,'msg'=>L('ENTER_NAME')];

        if($member['email'])
        {
            if(!isEmail($member['email'])) return ['status'=>0,'msg'=>L('MAIL_FORMAT_ERROR',['email'=>$member['email']])];

            if($this->checkMemberByField('account',$member['email'],$member_id,true)) return ['status'=>0,'msg'=>L('MAIL_EXISTS')];

            if($this->checkMemberByField('email',$member['email'],$member_id,true)) return ['status'=>0,'msg'=>L('MAIL_EXISTS')];
        }

        if($member['mobile'])
        {
            if((!isMobile($member['mobile'])) && (!isInternationalMobile($member['mobile'])))
            {
	            return ['status'=>0,'msg'=>L('MOBILE_FORMAT_ERROR',['mobile'=>$member['mobile']])];
            }

            if($this->checkMemberByField('account',$member['mobile'],$member_id,true))
            {
	            return ['status'=>0,'msg'=>L('MOBILE_EXISTS')];
            }

            if($this->checkMemberByField('mobile',$member['mobile'],$member_id,true))
            {
	            return ['status'=>0,'msg'=>L('MOBILE_EXISTS')];
            }
        }

		if($member['wechat'])
        {
            if($this->checkMemberByField('wechat',$member['wechat'],$member_id,true)) return ['status'=>0,'msg'=>L('WECHAT_NUMBER_EXISTS')];
        }

        if($firm_id > 0)
        {
            $member['update_time']  = NOW_TIME;
        }

	    if($member['password'])
	    {
		    $member['password'] = md5($member['password']);
	    }
	    else
        {
            unset($member['password']);
	    }

        return $member;
    }


    /*
	* 删除会员
	* @param void $id 用户的ID或ID数组
	* @param int  $company_id 公司ID
	*/
    public function deleteCustomer($id,$company_id)
    {
        if(!is_array($id)) $id = [$id];

        foreach($id as &$v)
        {
            $v = decrypt($v,'MEMBER');
        }

        $memberWhere = ['member_id' => ['in',$id],'company_id'=>$company_id,'type'=>2,'closed'=>0];

        if(count($id) !== (int) $this->where($memberWhere)->count('member_id'))
        {
            $result = ['status'=>0,'msg'=>L('CUSTOMER_NOT')];
        }
        else if($this->where($memberWhere)->setField(['closed'=>1,'del_time'=>time()]))
        {
            $result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('index')];
        }
        else
        {
            $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
        }

        return $result;
    }


    /*
	* 恢复会员
	* @param void $id 用户的ID或ID数组
	* @param int  $company_id 公司ID
	*/
    public function recoveryCustomer($id,$company_id)
    {
        if(!is_array($id)) $id = [$id];

        foreach($id as &$v)
        {
            $v = decrypt($v,'MEMBER');
        }

        $memberWhere = ['member_id' => ['in',$id],'company_id'=>$company_id,'type'=>2,'closed'=>1];

        if(count($id) !== (int) $this->where($memberWhere)->count())
        {
            $result = ['status'=>0,'msg'=>L('CUSTOMER_NOT')];
        }
        else if($this->where($memberWhere)->setField('closed',0))
        {
            $result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('index')];
        }
        else
        {
            $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
        }

        return $result;
    }


    /*
	* 游客转会员
	* @param void $id 用户的ID或ID数组
	* @param int  $company_id 公司ID
	*/
    public function switchToMember($id,$company_id)
    {
        if(!is_array($id)) $id = [$id];

        foreach($id as &$v)
        {
            $v = decrypt($v,'MEMBER');
        }

        $memberWhere = ['member_id' => ['in',$id],'company_id'=>$company_id,'type'=>2,'closed'=>0];

        if(count($id) !== (int) $this->where($memberWhere)->count())
        {
            $result = ['status'=>0,'msg'=>L('CUSTOMER_NOT')];
        }
        else if($this->where($memberWhere)->save(['member_type'=>1,'update_time'=>NOW_TIME]))
        {
//			 添加权限
            $members = M('member')->where($memberWhere)->field('firm_id,member_id')->order('firm_id')->select();

            foreach($members as $v)
            {
//				  创建默认角色、权限
	            D('MemberReg')->createDefaultRole($company_id,$v['member_id'],false,$v['firm_id']);
            }

            $result = ['status'=>1,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('visitor_list')];
        }
        else
        {
            $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
        }

        return $result;
    }


    /*
    * 永久刪除用戶及相关数据
    * @param void $id 用户的ID或ID数组
    * @param int  $company_id 公司ID
    */
    public function deleteMemberForever($id,$company_id)
    {
        if(!is_array($id)) $id = [$id];

        foreach($id as &$v)
        {
            $v = decrypt($v,'MEMBER');
        }

        $memberWhere = ['member_id' => ['in',$id],'company_id' => $company_id];

        if(count($id) !== (int) $this->where($memberWhere)->count())
        {
            return ['status'=>0,'msg'=>L('USER_NOT')];
        }

        if($this->where(['member_id' => ['in',$id],'company_id' => $company_id,'is_first'=>2,'type'=>1])->getField('member_id'))
        {
            return ['status'=>0,'msg'=>'默认账号不能删除'];
        }

//        条件 - 删除与用户相关的系统消息
	    /*$systemWhere = ['recipient_id|urge_member_id'=>['in',$id],'recipient'=>1];

//        删除与用户相关的系统消息
        M('system_message')->where($systemWhere)->delete();

//        删除与用户相关的数据
        $tableArr = [
        	'member',//用户
	        'faq_problem',//用户发布的FAQ
            'send_sms',//用户相关的短信通知
	        'send_weixin',//用户相关的微信通知
	        'sms_error',//用户相关的短信通知
	        'sms_success',//用户相关的短信通知
        ];

        $i = 0;

        foreach($tableArr as $tv)
        {
            M($tv)->where($memberWhere)->delete();

            $i++;
        }

        if($i == count($tableArr))
        {
            $result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>U('index')];
        }
        else
        {
            $result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
        }*/

	    //删除逻辑改为禁用


	    $save = $this->where($memberWhere)->save(['closed'=>1]);

	    if($save === false)
	    {
		    $result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
	    }
	    else
	    {
		    $result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>U('index')];
	    }

        return $result;
    }


    public function resetPassword($id,$company_id,$type)
    {
        $member_id = decrypt($id,'MEMBER');

        $where = ['member_id'=>$member_id,'company_id'=>$company_id,'type'=>$type];

        if($this->where($where)->getField($this->pk))
        {
            $save = $this->where($where)->setField('password',md5('123456'));

            if($save !== false)
            {
				$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('index')];
            }
            else
            {
                $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
            }
        }
        else
        {
            $result = ['status'=>0,'msg'=>L('USER_NOT')];
        }

        return $result;
    }


    public function updatePassword($member,$member_id,$company_id,$type)
    {
        $data = $this->checkFields($member, $this->updateUserInfo);

        $where = ['company_id'=>$company_id,'member_id'=>$member_id,'type'=>$type];

        $password = $this->where($where)->getField('password');

        if(!empty($data['new']))
        {
            if(!isPassword($data['new'])) return ['status'=>0,'msg'=>L('PASSWORD_FORMAT_ERROR')];

            if($data['new'] != $data['sure']) return ['status'=>0,'msg'=>L('TWO_PASSWORD')];

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
			return ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('userinfo')];
        }
    }


    public function getMemberList($where = [],$page = 8)
    {
        $count = $this->where($where)->count();

        $Page = new \Think\Page($count, $page);

        $lists = $this->where($where)
                     ->field('member_id,name,face,last_login_time')
                     ->limit($Page->firstRow, $Page->listRows)
                     ->select();

        foreach($lists as &$v)
        {
            $v['member_id'] = encrypt($v['member_id'],'MEMBER');

            $v['last_login_time'] = getDates($v['last_login_time']);
        }

        return ['data'=>$lists,'pages'=>ceil($count/$page)];
    }
}


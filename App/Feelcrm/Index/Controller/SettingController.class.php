<?php
namespace Index\Controller;

use Index\Common\BasicController;

class SettingController extends BasicController
{
    protected static $_updateCompanyField = ['company_id','logo','logo_name','logo_url','name','address','tel','mobile','link','access_token'];

    public function index()
	{
        $this->display();
    }


//    用户个人信息修改
    public function userinfo()
    {
        if(IS_POST)
        {
            $data = $this->checkRequestData();

            $where = ['company_id'=>$this->_company_id,'member_id'=>$this->member_id,'type'=>1];

            $save = D('Member')->where($where)->save($data);

            if($save === false)
            {
                $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
            }
            else
            {
                $result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('userinfo')];

                session('index',M('member')->field('password',true)->find($this->member_id));
            }

            $this->ajaxReturn($result);
        }
        else
        {
            $member = M('Member')->field('member_id,account,employee_id,name,nickname,email,mobile,face,face_name')
                ->find($this->member_id);

            $this->assign('member',$member);

            $this->display();
        }
    }


//    修改密码
    public function update_password()
    {
        if(IS_POST)
        {
            $result = D('Member')->updatePassword(I('post.member'),$this->member_id,$this->_company_id,1);

            $this->ajaxReturn($result);
        }
    }


//    设置公司信息
    public function update_company()
    {
        $detail = M('Company')->where(["company_id"=>$this->_company_id])->find();

        if(IS_AJAX)
        {
            $data = $this->checkRequestData('company');

            $data['company_id'] = $this->_company_id;

            if(!$detail)
            {
                if($company_id = M('Company')->add($data))
                {
                    $result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('update_company')];
                }
                else
                {
                    $result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
                }
            }
            else
            {
                $save = D('Company')->save($data);

                if($save === false)
                {
                    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
                }
                else
                {
                    $result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('update_company')];
                }
            }

            $this->ajaxReturn($result);
        }
        else
        {
	        $token = C('NEED_TOKEN') ? '?login_token='.$detail['login_token'] : '';

            $this->assign('company',$detail);

            $this->assign('token',$token);

            $this->display();
        }
    }


//    校验请求数据
	private function checkRequestData($source = '')
	{
		if($source == 'company')
		{
			$result = checkFields(I('post.company'), self::$_updateCompanyField);
		}
		else
		{
			$member = checkFields(I('post.member'), D('Member')->updateUserInfo);

			$result = D('Member')->VerifyEditorData($member,$this->member_id);

			if($result['status'] === 0) $this->ajaxReturn($result);
		}

		return $result;
	}
}
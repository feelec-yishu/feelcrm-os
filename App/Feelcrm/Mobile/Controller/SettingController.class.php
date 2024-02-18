<?php

namespace Mobile\Controller;

use Mobile\Common\BasicController;

class SettingController extends BasicController
{
    protected $updateField = ['old','new','sure','face','face_name','face_url','email','mobile','name','nickname'];

    protected $updateCompanyField = ['company_id','logo','logo_name','logo_url','name','address','tel','mobile','link','access_token'];

    public function index($source = '')
    {
//        知识库查询
	    $libraryQueryAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'library/query',$this->_mobile['role_id']);

//        更新公司权限
	    $updateCompanyAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'setting/update_company',$this->_mobile['role_id']);

        $this->assign('isLibraryAuth',$libraryQueryAuth);

        $this->assign('isUpdateCompanyAuth',$updateCompanyAuth);

        $this->assign('source',$source);

        $this->display();
    }


    public function userinfo()
    {
        if(IS_POST)
        {
            $data = checkFields(I('post.member'),$this->updateField);

            $field = array_keys($data)[0];

            if($field == 'name' && empty($data[$field]))
            {
                $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_NAME')]);
            }
            else if($field == 'mobile')
            {
                if(empty($data[$field])){$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_MOBILE')]);}

                if(!isMobile($data['mobile'])) $this->ajaxReturn(['status'=>0,'msg'=>L('MOBILE_FORMAT_ERROR',['mobile'=>$data['mobile']])]);

                if(D('Member')->checkMemberByField('account',$data['mobile'],$this->member_id,true)) $this->ajaxReturn(['status'=>0,'msg'=>L('MOBILE_EXISTS')]);

                if(D('Member')->checkMemberByField('mobile',$data['mobile'],$this->member_id,true)) $this->ajaxReturn(['status'=>0,'msg'=>L('MOBILE_EXISTS')]);
            }
            else if($field == 'email')
            {
                if(empty($data[$field])){$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_EMAIL')]);}

                if(!isEmail($data['email'])) $this->ajaxReturn(['status'=>0,'msg'=>L('MAIL_FORMAT_ERROR',['email'=>$data['email']])]);

                if(D('Member')->checkMemberByField('account',$data['email'],$this->member_id,true)) $this->ajaxReturn(['status'=>0,'msg'=>L('MAIL_EXISTS')]);

                if(D('Member')->checkMemberByField('email',$data['email'],$this->member_id,true)) $this->ajaxReturn(['status'=>0,'msg'=>L('MAIL_EXISTS')]);
            }

            if($data['face_name'] && ($data['face_name'] != $data['face_url']))
            {
                $data['face_name'] = $data['face_url'];

                unset($data['face_url']);

//                D('Upload')->deleteUploadFile($data['face_name']);//删除七牛中的原头像图片
            }

            $data['member_id'] = $this->member_id;

            $result = M('member')->save($data);

            if($result !== false)
            {
                session('mobile',M('member')->field('password',true)->find($this->member_id));

                $result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('setting/userinfo')];
            }
            else
            {
                $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
            }

            $this->ajaxReturn($result);
        }
        else
        {
            $this->display();
        }
    }


    public function update_company()
    {
        if(IS_POST)
        {
            $data = checkFields(I('post.company'),$this->updateCompanyField);

            if($data['logo_name'] && ($data['logo_name'] != $data['logo_url']))
            {
                $data['logo_name'] = $data['logo_url'];

                unset($data['logo_url']);

//                D('Upload')->deleteUploadFile($data['logo_name']);//删除七牛中的原头像图片
            }

            $data['company_id'] = $this->_company_id;

            $result = M('company')->save($data);

            if($result !== false)
            {
                $result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('setting/update_company')];
            }
            else
            {
                $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
            }

            $this->ajaxReturn($result);
        }
        else
        {
            $this->assign('company',$this->_company);

            $this->display();
        }
    }


    public function update_password()
    {
        if(IS_POST)
        {
            $result = D('Member')->updatePassword(I('post.member'),$this->member_id,$this->_company_id,1);

            $result['url'] = U('setting/index',['source'=>'set-window']);

            $this->ajaxReturn($result);
        }
        else
        {
            $this->display();
        }
    }

}

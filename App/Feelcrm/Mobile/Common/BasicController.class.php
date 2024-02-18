<?php

namespace Mobile\Common;

use Think\Controller;

use Common\Controller\CommonController;

class BasicController extends Controller
{
    protected $common,$_mobile,$member_id,$_company_id,$login_token,$_lang,$_sms,$_company;

	public function _initialize()
	{
        $this->common = new CommonController();

        $this->common->feelDeskInit('mobile',$this->_mobile,$this->member_id,$this->_company_id,$this->login_token,$this->_lang,$this->_sms,$this->_company);

//       浮标 - 创建工单权限
        if($this->_company['ticket_auth'] == 10)
        {
	        $createTicketAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'ticket/create',$this->_mobile['role_id']);

	        $this->assign('isCreateTicketAuth',$createTicketAuth);
        }

//        浮标 - 创建线索、客户权限
		if($this->_company['crm_auth'] == 10)
		{
			$createClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'clue/create',$this->_mobile['role_id'],'crm');

			$createCustomerAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'customer/create',$this->_mobile['role_id'],'crm');

			$createOpportunityAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'opportunity/create',$this->_mobile['role_id'],'crm');

			$this->assign('isCreateCrmClueAuth',$createClueAuth);

			$this->assign('isCreateCrmCustomerAuth',$createCustomerAuth);

			$this->assign('isCreateCrmOpportunityAuth',$createOpportunityAuth);
		}

		if(!session('?is_app'))
		{
			$is_app = I('get.is_app',0);

			if($is_app)
			{
				session('is_app',$is_app);
			}
		}

		$this->assign('is_app',session('is_app'));

        $this->assign('systemAuth',['ticket_auth'=>$this->_company['ticket_auth'],'crm_auth'=>$this->_company['crm_auth']]);

		//获取系统消息未读数量
		$messagefield = ['company_id'=>$this->_company_id,'recipient_id'=>$this->member_id,'recipient'=>1];

		$unReadNumber = M('system_message')->where(array_merge($messagefield,['read_status'=>1]))
			->field("sum(case msg_system when 'crm' then 1 else 0 end) crm")->find();

		$this->assign('unReadMessageNum',$unReadNumber['crm']);
	}
}

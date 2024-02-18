<?php
namespace CrmMobile\Controller;

use CrmMobile\Common\BasicController;

class IndexController extends BasicController
{
	protected $all_view_auth = [];

    protected $group_view_auth = [];

    protected $own_view_auth = [];

	public function _initialize()
    {
        parent::_initialize();

	    $this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'all',$this->_mobile['role_id'],'crm');

	    $this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'group',$this->_mobile['role_id'],'crm');

	    $this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'own',$this->_mobile['role_id'],'crm');

	    $this->assign('isAllViewAuth',$this->all_view_auth);

	    $this->assign('isGroupViewAuth',$this->group_view_auth);

	    $this->assign('isOwnViewAuth',$this->own_view_auth);
    }

    public function index()
    {
	    $isClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'clue/index',$this->_mobile['role_id'],'crm'); //线索权限

	    $isCustomerAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/index',$this->_mobile['role_id'],'crm'); //客户权限

		$isAgentAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/agent',$this->_mobile['role_id'],'crm'); //经销商权限

		$isContacterAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/contacter',$this->_mobile['role_id'],'crm'); //联系人权限

		$isFollowupAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/followup',$this->_mobile['role_id'],'crm'); //联系记录权限

		$isContractAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'contract/index',$this->_mobile['role_id'],'crm'); //合同权限

	    $isOpportunityAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'opportunity/index',$this->_mobile['role_id'],'crm'); //商机权限

	    $this->assign('isOpportunityAuth',$isOpportunityAuth);

	    $this->assign('isClueAuth',$isClueAuth);

	    $this->assign('isAgentAuth',$isAgentAuth);

	    $this->assign('isCustomerAuth',$isCustomerAuth);

	    $this->assign('isContacterAuth',$isContacterAuth);

	    $this->assign('isFollowupAuth',$isFollowupAuth);

	    $this->assign('isContractAuth',$isContractAuth);

        $this->display();
    }
}

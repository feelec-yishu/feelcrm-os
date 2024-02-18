<?php

namespace CrmMobile\Common;

use Think\Controller;

use Common\Controller\CommonController;

class BasicController extends Controller
{
    protected $common,$_mobile,$member_id,$_company_id,$login_token,$_lang,$_sms,$_company,$_crmsite,$_source_type;

	public function _initialize()
	{
        $this->common = new CommonController();

        $this->common->feelDeskInit('mobile',$this->_mobile,$this->member_id,$this->_company_id,$this->login_token,$this->_lang,$this->_sms,$this->_company,'CrmMobile');

		$source_type = I('get.source_type') ? I('get.source_type') : session('SOURCE_TYPE_'.$this->_company_id.'_'.$this->member_id);

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

        $this->assign('systemAuth',['ticket_auth'=>$this->_company['ticket_auth'],'crm_auth'=>$this->_company['crm_auth']]);

		//获取系统消息未读数量
		$messagefield = ['company_id'=>$this->_company_id,'recipient_id'=>$this->member_id,'recipient'=>1];

		$unReadNumber = M('system_message')->where(array_merge($messagefield,['read_status'=>1]))
			->field("sum(case msg_system when 'crm' then 1 else 0 end) crm")->find();

		$this->assign('unReadMessageNum',$unReadNumber['crm']);

		//地区
		$country_name = getCrmLanguageData('name');

		$country = getCrmDbModel('country')->field(['*',$country_name])->select();

		$this->assign('country',$country);

		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$this->_company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		$this->_crmsite = $crmsite;

		if(!$crmsite['regionType'])
		{
			$crmsite['regionType'] = 'world';
		}

		$this->assign('crmsite',$crmsite);

		if($crmsite['regionType'] == 'world')
		{
			if($crmsite['defaultCountry'])
			{
				$province_name = getCrmLanguageData('name');

				$province = getCrmDbModel('province')->field(['*',$province_name])->where(['country_code'=>$crmsite['defaultCountry']])->select();

				$this->assign('province',$province);
			}

			if($crmsite['defaultCountry'] && $crmsite['defaultProv'])
			{
				$city_name = getCrmLanguageData('name');

				$city = getCrmDbModel('city')
					->field(['*',$city_name])
					->where(['country_code'=>$crmsite['defaultCountry'],'province_code'=>$crmsite['defaultProv']])
					->select();

				$this->assign('city',$city);
			}

			if($crmsite['defaultCountry'] && $crmsite['defaultProv'] && $crmsite['defaultCity'])
			{
				$area_name = getCrmLanguageData('name');

				$area = getCrmDbModel('area')
					->field(['*',$area_name])
					->where(['country_code'=>$crmsite['defaultCountry'],'province_code'=>$crmsite['defaultProv'],'city_code'=>$crmsite['defaultCity']])
					->select();

				$this->assign('area',$area);
			}
		}
		else
		{
			if($crmsite['defaultCountry'])
			{
				$province_name = getCrmLanguageData('name');

				$province = getCrmDbModel('province')->field(['*',$province_name])->where(['country_code'=>1])->select();

				$this->assign('province',$province);
			}

			if($crmsite['defaultCountry'] && $crmsite['defaultProv'])
			{
				$city_name = getCrmLanguageData('name');

				$city = getCrmDbModel('city')
					->field(['*',$city_name])
					->where(['country_code'=>1,'province_code'=>$crmsite['defaultProv']])
					->select();

				$this->assign('city',$city);
			}

			if($crmsite['defaultCountry'] && $crmsite['defaultProv'] && $crmsite['defaultCity'])
			{
				$area_name = getCrmLanguageData('name');

				$area = getCrmDbModel('area')
					->field(['*',$area_name])
					->where(['country_code'=>1,'province_code'=>$crmsite['defaultProv'],'city_code'=>$crmsite['defaultCity']])
					->select();

				$this->assign('area',$area);
			}
		}
	}
}

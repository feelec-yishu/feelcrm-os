<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

class SettingController extends BasicController
{			
	
	public function index()
	{
		//CRM基本信息
			
		if(IS_POST)
		{
			$crmsite = I('post.crmsite');
			
			//$rOrderCode = I('post.rOrderCode');
			
			$rContractCode = I('post.rContractCode');

			$rClueCode = I('post.rClueCode');

			$rCustomerCode = I('post.rCustomerCode');

			$rOpportunityCode = I('post.rOpportunityCode');

			$rAccountCode = I('post.rAccountCode');
			
			$rReceiptCode = I('post.rReceiptCode');
			
			$rInvoiceCode = I('post.rInvoiceCode');
			
			$shipment_trade = I('post.shipment_trade');

			$kpiType = I('post.kpiType');
			
			if($shipment_trade)
			{
				$crmsite['shipment_trade'] = $shipment_trade;
			}

			if($kpiType)
			{
				$crmsite['kpiType'] = $kpiType;
			}
			
			if($rContractCode == 1 && $crmsite['contractCode'])
			{
				getCrmDbModel('contract')->where(['company_id'=>$this->_company_id,'isvalid'=>1])->save(['contract_prefix'=>$crmsite['contractCode']]);
			}

			if($rClueCode == 1 && $crmsite['clueCode'])
			{
				getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'isvalid'=>1])->save(['clue_prefix'=>$crmsite['clueCode']]);
			}

			if($rCustomerCode == 1 && $crmsite['customerCode'])
			{
				getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1])->save(['customer_prefix'=>$crmsite['customerCode']]);
			}

			if($rOpportunityCode == 1 && $crmsite['opportunityCode'])
			{
				getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'isvalid'=>1])->save(['opportunity_prefix'=>$crmsite['opportunityCode']]);
			}
			
			$crmsite = serialize($crmsite);
			
			$data['key'] = 'crmsite';
			
			$data['company_id'] = $this->_company_id;

			$data['value'] = $crmsite;

			$site = getCrmDbModel('setting')->where(['key'=>'crmsite','company_id'=>$this->_company_id])->find();

			if($site)
			{
				$updateSite = getCrmDbModel('Setting')->where(['key'=>'crmsite','company_id'=>$this->_company_id])->save(['value'=>$crmsite]);

				if($updateSite === false)
				{
					$result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
				}
				else
				{
					$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('setting/index')];
				}
			}
			else
			{
				if(getCrmDbModel('Setting')->add($data))
				{
					$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('setting/index')];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
				}
			}

			$this->ajaxReturn($result);
			
		}
		else
		{
			$trade_form = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'type'=>'shipment','form_name'=>'trade'])->field('form_id,form_option')->find(); //出货行业字段
		
			$trade_form_field = explode('|',$trade_form['form_option']);
			
			$trades = [];
			
			foreach($trade_form_field as $key=>&$val)
			{
				$trades[$key]['id'] = $val;
				
				$trades[$key]['name'] = $val;
			}
			
			$this->assign('trades',json_encode($trades));

			$kpi_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'kpi/index',$this->_member['role_id'],'crm');

			$this->assign('isKpiAuth',$kpi_auth);

			$this->display();
		}

	}
}

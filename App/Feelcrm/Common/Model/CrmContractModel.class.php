<?php

namespace Common\Model;

use Common\Model\BasicModel;

class CrmContractModel extends BasicModel
{
	protected $autoCheckFields = false;
	
	/*
	* 获取修改完成列表页的html
	* @param int $company_id 公司ID
	* @param int $contract_id 合同ID
	*/

	public function getContractListHtml($company_id,$contract_id)
	{
		$contract = getCrmDbModel('contract')->where(['company_id'=>$company_id,'contract_id'=>$contract_id,'isvalid'=>1])->find();

		$index = session('index');

		$show_list = CrmgetShowListField('contract',$company_id); //合同列表显示字段

		$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$contract['member_id']])->field('member_id,account,name')->find();

		$contract['member_name'] = $thisMember['name'];

		$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$contract['creater_id']])->field('member_id,account,name')->find();

		$contract['create_name'] = $createMember['name'];

		$customer_detail = CrmgetCrmDetailList('customer',$contract['customer_id'],$company_id,'name');

		$contract['customer_name'] = $customer_detail['name'];

		$contract['detail'] = CrmgetCrmDetailList('contract',$contract['contract_id'],$company_id,$show_list['form_name']);

		$html = '';
		
		$html .= '<td class="checkbox relative">';
		
		$html .= '<input type="checkbox" name="del[]" lay-skin="primary" value="'.encrypt($contract_id,'CONTRACT').'"><div class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
			
		$html .= '</td>';
		
		//$html .= '<td mini="contract" class="blue8">'.$contract['contract_prefix'].$contract['contract_no'].'</td>';

		foreach($show_list['form_list'] as $key => $val)
		{
			if(!$val['role_id'] || in_array($index['role_id'],explode(',',$val['role_id'])))
			{
				$html .= '<td mini="customer" ';

				if($val['form_type'] == 'textarea')
				{
					$html .= 'title="'.strip_tags($contract['detail'][$val['form_name']]).'" ';
				}

				if($val['form_name'] == 'name')
				{
					$html .= 'class="blue8"';
				}

				$html .= ' >';

				if($val['form_type'] == 'textarea')
				{
					$html .= mb_substr(strip_tags($contract['detail'][$val['form_name']]),0,20).'...';
				}
				else
				{
					$html .= $contract['detail'][$val['form_name']] ? $contract['detail'][$val['form_name']] : '--';
				}

				$html .= '</td>';
			}
		}
		
		$html .= '<td mini="elseCustomer" class="blue8">'.$contract['customer_name'].'</td>';

		$html .= '<td mini="contract">'.getDates($contract['createtime']).'</td>';

		$html .= '<td mini="contract">'.$contract['member_name'].'</td>';

		$html .= '<td mini="contract">'.$contract['create_name'].'</td> ';

		$html .= '<td mini="contract">'.getCrmEntryMethod($contract['entry_method']).'</td>';
		
		return $html;
	}

	public function getMobileListInfo($contract,$company_id)
	{
		$show_list = CrmgetShowListField('contract',$company_id); //订单列表显示字段

		foreach($contract as $key=>&$val)
		{
			$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

			$val['member_name'] = $thisMember['name'];

			$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

			$val['create_name'] = $createMember['name'];

			$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

			$val['customer_name'] = $customer_detail['name'];

			$val['detail'] = CrmgetCrmDetailList('contract',$val['contract_id'],$company_id,$show_list['form_name']);

			$val['contract_id'] = encrypt($val['contract_id'],'CONTRACT');

			$val['customer_id'] = encrypt($val['customer_id'],'CUSTOMER');
		}

		return $contract;
	}
}

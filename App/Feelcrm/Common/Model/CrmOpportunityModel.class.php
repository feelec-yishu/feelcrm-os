<?php

namespace Common\Model;

use Common\Model\BasicModel;

class CrmOpportunityModel extends BasicModel
{
	protected $autoCheckFields = false;
	
	/*
	* 获取修改完成列表页的html
	* @param int $company_id 公司ID
	* @param int $opportunity_id 商机ID
	*/

	public function getOpportunityListHtml($company_id,$opportunity_id)
	{
		$opportunity = getCrmDbModel('opportunity')->where(['company_id'=>$company_id,'opportunity_id'=>$opportunity_id,'isvalid'=>1])->find();

		$index = session('index');

		$show_list = CrmgetShowListField('opportunity',$company_id); //商机列表显示字段

		$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$opportunity['member_id']])->field('member_id,account,name')->find();

		$opportunity['member_name'] = $thisMember['name'];

		$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$opportunity['creater_id']])->field('member_id,account,name')->find();

		$opportunity['create_name'] = $createMember['name'];

		$customer_detail = CrmgetCrmDetailList('customer',$opportunity['customer_id'],$company_id,'name');

		$opportunity['customer_name'] = $customer_detail['name'];

		$opportunity['detail'] = CrmgetCrmDetailList('opportunity',$opportunity['opportunity_id'],$company_id,$show_list['form_name']);
		
		$html = '';

		$html .= '<td class="checkbox relative">';

		$html .= '<input type="checkbox" name="del[]" lay-skin="primary" value="'.encrypt($opportunity_id,'OPPORTUNITY').'" ><div class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';

		$html .= '</td>';

        //$html .= '<td onclick="clickOpenDetail(this,\'opportunity\')" class="blue8">'.$opportunity['opportunity_prefix'].$opportunity['opportunity_no'].'</td>';

		foreach($show_list['form_list'] as $key => $val)
		{
			if(!$val['role_id'] || in_array($index['role_id'],explode(',',$val['role_id'])))
			{
				$html .= '<td onclick="clickOpenDetail(this,\'opportunity\')" ';

				if($val['form_type'] == 'textarea')
				{
					$html .= 'title="'.strip_tags($opportunity['detail'][$val['form_name']]).'" ';
				}

				if($val['form_name'] == 'name')
				{
					$html .= 'class="blue8"';
				}

				$html .= ' >';

				if($val['form_type'] == 'textarea')
				{
					$html .= mb_substr(strip_tags($opportunity['detail'][$val['form_name']]),0,20).'...';
				}
				else
				{
					$html .= $opportunity['detail'][$val['form_name']] ? $opportunity['detail'][$val['form_name']] : '--';
				}

				$html .= '</td>';

				if($val['form_name'] == 'name')
				{
					$html .= '<td mini=\'elseCustomer\' class="blue8">'.$opportunity['customer_name'].'</td>';
				}
			}
		}

		$html .= '<td onclick="clickOpenDetail(this,\'opportunity\')">'.getDates($opportunity['lastfollowtime']).'</td>';

		$html .= '<td onclick="clickOpenDetail(this,\'opportunity\')">'.getDates($opportunity['nextcontacttime']).'</td>';

		$html .= '<td onclick="clickOpenDetail(this,\'opportunity\')">'.getDates($opportunity['createtime']).'</td>';

		$html .= '<td onclick="clickOpenDetail(this,\'opportunity\')">'.$opportunity['member_name'].'</td>';

		$html .= '<td onclick="clickOpenDetail(this,\'opportunity\')">'.$opportunity['create_name'].'</td>';

		$html .= '<td onclick="clickOpenDetail(this,\'opportunity\')">'.getCrmEntryMethod($opportunity['entry_method']).'</td>';

		return $html;
	}

	/*
	* 商机转移
	* @param int $member_id 用户id
	* @param int $now_member_id 当前登录人员id
	* @param int $customer_id 商机id
	*/
	public function transferOpportunity($company_id,$member_id,$now_member_id,$opportunity_id,$sms)
	{
		$field['company_id'] = $company_id;

		$field['isvalid'] = 1;

		$field['opportunity_id'] = $opportunity_id;

		if(getCrmDbModel('opportunity')->where($field)->save(['member_id'=>$member_id]))
		{
			$opportunity_detail = CrmgetCrmDetailList('opportunity',$opportunity_id,$company_id,'name');

			$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$company_id,'opportunity_id'=>$opportunity_id])->getField('customer_id');

			D('CrmLog')->addCrmLog('opportunity',8,$company_id,$now_member_id,$customer_id,0,0,0,$opportunity_detail['name'],0,$member_id,0,0,0,0,0,0,0,$opportunity_id);

			$result = ['status'=>2,'msg'=>L('SUCCESSFULLY_TRANSFERRED'),'url'=>U('index')];
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('TRANSFER_FAILED')];
		}

		return $result;
	}

	/*
	* 删除商机
	* @param int $member_id 用户id
	* @param int $now_member_id 当前登录人员id
	* @param int $opportunity_id 商机id
	*/
	public function deleteOpportunity($opportunity_id,$company_id,$member_id,$thisMember,$localurl,$delType = 'one')
	{
		$recover_opportunity = D('RoleAuth')->checkRoleAuthByMenu($thisMember['company_id'],'recover/opportunity',$thisMember['role_id'],'crm');

		$where = ['opportunity_id'=>$opportunity_id,'company_id'=>$company_id];

		$opportunity = getCrmDbModel('opportunity')->where($where)->field('customer_id,isvalid,opportunity_id,member_id')->find();

		if(!$opportunity)
		{
			return ['status'=>0,'msg'=>L('OPPORTUNITY_DOES_NOT_EXIST')];die;
		}

		if(getCrmDbModel('contract')->where(['company_id'=>$company_id,'opportunity_id'=>$opportunity_id,'isvalid'=>1])->find())
		{
			return ['status'=>0,'msg'=>L('OPPORTUNITY_HAS_CONTRACT')];
		}
		else
		{
			$opportunity['detail'] = CrmgetCrmDetailList('opportunity',$opportunity_id,$company_id);

			if($opportunity['isvalid'] == 1)
			{
				$delete = getCrmDbModel('opportunity')->where($where)->save(['isvalid'=>0]);
			}
			else
			{
				if($recover_opportunity) $localurl = U('Recover/opportunity');

				$delete = getCrmDbModel('opportunity')->where($where)->delete();

				if($delete)
				{
					getCrmDbModel('opportunity_detail')->where($where)->delete();

					getCrmDbModel('opportunity_product')->where($where)->delete();
				}
			}

			if($delete)
			{
				if($opportunity['isvalid'] == 1)
				{
					D('CrmLog')->addCrmLog('opportunity', 3, $company_id, $member_id, $opportunity['customer_id'], 0, 0, 0, $opportunity['detail']['name'], 0, 0, 0, 0, 0,0,0,0,0,$opportunity_id);
				}
				else
				{
					D('CrmLog')->addCrmLog('opportunity', 15, $company_id, $member_id, $opportunity['customer_id'], 0, 0, 0, $opportunity['detail']['name'], 0, 0, 0, 0, 0,0,0,0,0,$opportunity_id);
				}

				if($delType == 'one')
				{
					$result = ['status'=>3,'msg'=>L('DELETE_SUCCESS'),'reloadType'=>'parent','url'=>$localurl];
				}
				else
				{
					$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>$localurl];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
			}
		}

		return $result;
	}

	public function getMobileListInfo($opportunity,$company_id)
	{
		foreach($opportunity as $key=>&$val)
		{
			$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

			$val['member_name'] = $thisMember['name'];

			$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

			$val['create_name'] = $createMember['name'];

			$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

			$val['customer_name'] = $customer_detail['name'];

			$val['detail'] = CrmgetCrmDetailList('opportunity',$val['opportunity_id'],$company_id,'name,stage');

			$val['opportunity_id'] = encrypt($val['opportunity_id'],'OPPORTUNITY');

			$val['customer_id'] = encrypt($val['customer_id'],'CUSTOMER');
		}

		return $opportunity;
	}
}

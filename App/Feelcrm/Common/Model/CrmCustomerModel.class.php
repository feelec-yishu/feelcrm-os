<?php

namespace Common\Model;

use Common\Model\BasicModel;

class CrmCustomerModel extends BasicModel
{
	protected $autoCheckFields = false;

	/*
	* 获取修改完成列表页的html
	* @param int $company_id 公司ID
	* @param int $customer_id 客户ID
	* @param int $type 1 客户 2 客户池
	*/

	public function getCustomerListHtml($company_id,$customer_id,$type = 1)
	{
		$show_list = CrmgetShowListField('customer',$company_id); //客户列表显示字段

		$index = session('index');

		//除备注外全部客户自定义字段
		$allField = getCrmDbModel('define_form')->where(['type'=>'customer','company_id'=>$company_id,'closed'=>0,'form_name'=>['neq','remark']])->count();

		$customer = getCrmDbModel('customer')->where(['company_id'=>$company_id,'customer_id'=>$customer_id,'isvalid'=>'1'])->find();

		$haveField = 0;

		if($type == 1)
		{
			$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$customer['member_id']])->getField('name');

			$customer['member_name'] = $thisMember;
		}

		$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$customer['creater_id']])->getField('name');

		$customer['create_name'] = $createMember;

		$customer['detail'] = CrmgetCrmDetailList('customer',$customer['customer_id'],$company_id);

		$haveFieldArr = $customer['detail'];

		unset($haveFieldArr['remark']);

		$haveField = count(array_filter($haveFieldArr));

		$percent = round($haveField/$allField*100);

		if($percent >= 50)
		{
			$customer['percent'] = '<span class="green6">'.$percent.'%</span>';
		}
		else
		{
			$customer['percent'] = '<span class="red5">'.$percent.'%</span>';
		}

		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		$html = '';

		$html .= '<td class="checkbox relative">';

		$html .= '<input type="checkbox" name="del[]" lay-skin="primary" value="'.encrypt($customer_id,'CUSTOMER').'"><div class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';

		$html .= '</td>';

		/*$html .= '<td mini="customer">'.$customer['customer_prefix'].$customer['customer_no'].'</td>';*/

		foreach($show_list['form_list'] as $key => $val)
		{
			if(!$val['role_id'] || in_array($index['role_id'],explode(',',$val['role_id'])))
			{
				$html .= '<td mini="customer" ';

				if($val['form_type'] == 'textarea')
				{
					$html .= 'title="'.strip_tags($customer['detail'][$val['form_name']]).'" ';
				}

				if($val['form_name'] == 'name')
				{
					$html .= 'class="blue8"';
				}

				$html .= ' >';

				if($val['form_type'] == 'textarea')
				{
					$html .= mb_substr(strip_tags($customer['detail'][$val['form_name']]),0,20).'...';
				}
				else
				{
					$html .= $customer['detail'][$val['form_name']] ? $customer['detail'][$val['form_name']] : '--';
				}

				$html .= '</td>';

				if($val['form_name'] == 'name')
				{
					$html .= '<td mini="customer">'.$customer['percent'].'</td>';
				}
			}
		}

		$html .= '<td mini="customer">'.getDates($customer['lastfollowtime']).'</td>';

		$html .= '<td mini="customer">'.getDates($customer['nextcontacttime']).'</td>';

		//$html .= '<td mini="customer">';

		//$html .= $customer['is_losed'] == 1 ? '<span class="red1">'.L('YES').'</span>' : '<span class="blue1">'.L('NO').'</span>';

		//$html .= '</td>';

		$html .= '<td mini="customer">'.getDates($customer['createtime']).'</td>';

		if($type == 1)
		{
			$html .= '<td mini="customer">'.$customer['member_name'].'</td> ';
		}

		$html .= '<td mini="customer">';

		$html .= getCustomerCreateName($customer['creater_id'],$customer['create_name']);

		$html .= '</td>';

		$html .= '<td mini="customer">'.getCrmEntryMethod($customer['entry_method']).'</td>';

		//$html .= '<td class="listOperate hidden"><i class="iconfont icon-dian"></i><div class="operate hidden">';

		return $html;
	}

	public function addCustomerGive($operate_type,$company_id,$customer_id,$member_id,$target_id)
	{
		$data['company_id'] = $company_id;

		$data['customer_id'] = $customer_id;

		$data['operate_type'] = $operate_type;

		$data['member_id'] = $member_id;

		$data['target_id'] = $target_id;

		$data['is_looked'] = 0;

		$data['createtime'] = NOW_TIME;

		//如果已存在分配记录，去除其余用户的分配记录，并更新待办数量
		if($t_id = getCrmDbModel('customer_give')->where(['company_id'=>$company_id,'customer_id'=>$customer_id,'is_looked'=>0])->getField('target_id'))
		{
			getCrmDbModel('customer_give')->where(['company_id'=>$company_id,'customer_id'=>$customer_id,'is_looked'=>0])->delete();
		}

		$give_id = getCrmDbModel('customer_give')->add($data);

		return $give_id;
	}

	public function deleteCustomer($customer_id,$company_id,$member_id,$thisMember,$localurl,$delType = 'one')
	{
		$where = ['customer_id'=>$customer_id,'company_id'=>$company_id];

		$isvalidWhere = ['isvalid'=>1];

		$recover_customer = D('RoleAuth')->checkRoleAuthByMenu($thisMember['company_id'],'recover/customer',$thisMember['role_id'],'crm');

		$result = [];

		if(getCrmDbModel('contract')->where(array_merge($where,$isvalidWhere))->find()){

			return ['status'=>0,'msg'=>L('CUSTOMER_HAS_CONTRACT')];die;

		}else
		{
			$customer = getCrmDbModel('customer')->where($where)->field('isvalid,member_id,is_examine')->find();

			if(!$customer)
			{
				return ['status'=>0,'msg'=>L('CUSTOMER_NOT_EXIST')];die;
			}

			$customer['detail'] = CrmgetCrmDetailList('customer',$customer_id,$company_id);

			if($customer['isvalid'] == 1)
			{
				$delete = getCrmDbModel('customer')->where(array_merge($where,$isvalidWhere))->save(['isvalid'=>0]);

				if($delete)
				{
					$contacter = getCrmDbModel('contacter')->where(array_merge($where,$isvalidWhere))->field('contacter_id')->select();

					foreach($contacter as $key=>$val)
					{
						getCrmDbModel('contacter')->where(['company_id'=>$company_id,'isvalid'=>1,'customer_id'=>$customer_id,'contacter_id'=>$val['contacter_id']])->save(['isvalid'=>0]);

						$contacter['detail'] = CrmgetCrmDetailList('contacter',$val['contacter_id'],$company_id);

						D('CrmLog')->addCrmLog('contacter',3,$company_id,$member_id,$customer_id,$val['contacter_id'],0,0,$contacter['detail']['name']);
					}

					getCrmDbModel('followup')->where(array_merge($where,$isvalidWhere))->save(['isvalid'=>0]);

					D('CrmLog')->addCrmLog('customer',3,$company_id,$member_id,$customer_id,0,0,0,$customer['detail']['name']);

					//如果已存在分配记录，去除其余用户的分配记录，并更新待办数量
					if($t_id = getCrmDbModel('customer_give')->where(['company_id'=>$company_id,'customer_id'=>$customer_id,'is_looked'=>0])->getField('target_id'))
					{
						getCrmDbModel('customer_give')->where(['company_id'=>$company_id,'customer_id'=>$customer_id,'is_looked'=>0])->delete();
					}
				}
			}
			else
			{
				if($recover_customer) $localurl = U('Recover/customer');

				$delete = getCrmDbModel('customer')->where($where)->delete();

				if($delete)
				{
					getCrmDbModel('customer_detail')->where($where)->delete();

					getCrmDbModel('followup')->where($where)->delete();

					//删除联系人数据
					$contacter = getCrmDbModel('contacter')->where($where)->field('contacter_id')->select();

					if($contacter)
					{
						$contacter_arr = array_column($contacter, 'contacter_id');

						getCrmDbModel('contacter')->where(['company_id' => $company_id, 'contacter_id' => ['in', $contacter_arr]])->delete();

						getCrmDbModel('contacter_detail')->where(['company_id' => $company_id, 'contacter_id' => ['in', $contacter_arr]])->delete();
					}

					//删除客户分析数据
					$analysis = getCrmDbModel('analysis')->where($where)->field('analysis_id')->select();

					if($analysis)
					{
						$analysis_arr = array_column($analysis,'analysis_id');

						getCrmDbModel('analysis')->where(['company_id' => $company_id, 'analysis_id' => ['in', $analysis_arr]])->delete();

						getCrmDbModel('analysis_detail')->where(['company_id' => $company_id, 'analysis_id' => ['in', $analysis_arr]])->delete();
					}

					//删除竞争对手数据
					$competitor = getCrmDbModel('competitor')->where($where)->field('competitor_id')->select();

					if($competitor)
					{
						$competitor_arr = array_column($competitor, 'competitor_id');

						getCrmDbModel('competitor')->where(['company_id' => $company_id, 'competitor_id' => ['in', $competitor_arr]])->delete();

						getCrmDbModel('competitor_detail')->where(['company_id' => $company_id, 'competitor_id' => ['in', $competitor_arr]])->delete();
					}

					getCrmDbModel('customer_abandon')->where($where)->delete();

					getCrmDbModel('customer_lose')->where($where)->delete();

					getCrmDbModel('customer_invoiceinfo')->where($where)->delete();

					getCrmDbModel('customer_give')->where($where)->delete();

					getCrmDbModel('customer_follow_control')->where($where)->delete();

					getCrmDbModel('customer_nofollow_control')->where($where)->delete();

					getCrmDbModel('customer_noreturnvisit_control')->where($where)->delete();

					D('CrmLog')->addCrmLog('customer',15,$company_id,$member_id,$customer_id,0,0,0,$customer['detail']['name']);

				}
			}

			if($delete)
			{
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

	//检查是否超出领取限制
	public function checkDrawCount($company_id,$member_id,$count=1)
	{
		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		if($crmsite['custDrawT'] == 'day')
		{
			$where['createtime'] = [['egt',strtotime(date('Y-m-d 00:00:00'))],['elt',strtotime(date('Y-m-d 23:59:59'))]];
		}
		elseif($crmsite['custDrawT'] == 'week')
		{
			$week = strtotime(date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)));

			$where['createtime'] = [['egt',$week],['elt',NOW_TIME]];
		}
		elseif($crmsite['custDrawT'] == 'month')
		{
			$month = strtotime(date('Y-m-d', strtotime(date('Y-m', time()) . '-01 00:00:00')));

			$where['createtime'] = [['egt',$month],['elt',strtotime(date('Y-m-d 23:59:59'))]];
		}

		$where['log_type'] = 'customer';

		$where['operate_type'] = 5;

		$where['member_id'] = $member_id;

		$drawCount = getCrmDbModel('crmlog')->where($where)->count();

		$allCount = (int)$drawCount + $count;

		if($allCount > $crmsite['custDrawNum'])
		{
			$result = ['status'=>0,'msg'=>L('RECEIPT_LIMIT_EXCEEDED')];

			return $result;
		}

		return ['status'=>2];
	}

	//领取客户
	public function drawCustomer($company_id,$member_id,$customer_id,$sms)
	{
		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		$field['customer_id'] = $customer_id;

		$field['company_id'] = $company_id;

		$field['isvalid'] = 1;

		$customer = getCrmDbModel('customer')->where($field)->field('member_id,is_examine')->find();

		if(!$customer['member_id'])
		{
            if(getCrmDbModel('customer')->where($field)->save(['member_id'=>$member_id]))
            {
                getCrmDbModel('contacter')->where($field)->save(['member_id'=>$member_id]);

                getCrmDbModel('opportunity')->where($field)->save(['member_id'=>$member_id]);

                D('CrmCreateMessage')->createMessage(2,$sms,$member_id,$company_id,$member_id,$customer_id);

                $customer_detail = CrmgetCrmDetailList('customer',$customer_id,$company_id,'name,phone');

                D('CrmLog')->addCrmLog('customer',5,$company_id,$member_id,$customer_id,0,0,0,$customer_detail['name'],0,$member_id);

                $result = ['status'=>2,'msg'=>L('RECEIVED_SUCCESSFULLY'),'url'=>U('index')];
            }
            else
            {
                $result = ['status'=>0,'msg'=>L('CLAIM_FAILED')];
            }
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('CUSTOMER_HAS_BEEN_CLAIMED')];
		}

		return $result;
	}

	/*
	* 分配客户
	* @param int $member_id 用户id
	* @param int $now_member_id 当前登录人员id
	* @param int $customer_id 客户id
	*/
	public function allotCustomer($company_id,$member_id,$now_member_id,$customer_id,$sms)
	{
		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		$field['company_id'] = $company_id;

		$field['isvalid'] = 1;

		$field['customer_id'] = $customer_id;

		$customer = getCrmDbModel('customer')->where($field)->field('member_id,is_examine')->find();

		if(!$customer['member_id'])
		{
            if(getCrmDbModel('customer')->where($field)->save(['member_id'=>$member_id]))
            {
                getCrmDbModel('contacter')->where($field)->save(['member_id'=>$member_id]);

                getCrmDbModel('opportunity')->where($field)->save(['member_id'=>$member_id]);

                D('CrmCreateMessage')->createMessage(1,$sms,$member_id,$company_id,$now_member_id,$customer_id);

                D('CrmCustomer')->addCustomerGive('allot',$company_id,$customer_id,$now_member_id,$member_id);

                $customer_detail = CrmgetCrmDetailList('customer',$customer_id,$company_id,'name,phone');

                D('CrmLog')->addCrmLog('customer',6,$company_id,$now_member_id,$customer_id,0,0,0,$customer_detail['name'],0,$member_id);

                $result = ['status'=>2,'msg'=>L('ASSIGN_SUCCESS'),'url'=>U('pool')];
            }
            else
            {
                $result = ['status'=>0,'msg'=>L('ASSIGN_FAILED')];
            }
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('CUSTOMER_HAS_BEEN_ASSIGNED')];
		}

		return $result;
	}

	/*
	* 客户转移
	* @param int $member_id 用户id
	* @param int $now_member_id 当前登录人员id
	* @param int $customer_id 客户id
	*/
	public function transferCustomer($company_id,$member_id,$now_member_id,$customer_id,$sms)
	{
		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		$field['company_id'] = $company_id;

		$field['isvalid'] = 1;

		$field['customer_id'] = $customer_id;

		if(getCrmDbModel('customer')->where($field)->save(['member_id'=>$member_id]))
		{
			getCrmDbModel('contacter')->where($field)->save(['member_id'=>$member_id]);

			getCrmDbModel('order')->where($field)->save(['member_id'=>$member_id]);

			getCrmDbModel('opportunity')->where($field)->save(['member_id'=>$member_id]);

			D('CrmCreateMessage')->createMessage(3,$sms,$member_id,$company_id,$now_member_id,$customer_id);

			D('CrmCustomer')->addCustomerGive('transfer',$company_id,$customer_id,$now_member_id,$member_id);

			$customer_detail = CrmgetCrmDetailList('customer',$customer_id,$company_id,'name,phone');

			D('CrmLog')->addCrmLog('customer',8,$company_id,$now_member_id,$customer_id,0,0,0,$customer_detail['name'],0,$member_id);

			$result = ['status'=>2,'msg'=>L('SUCCESSFULLY_TRANSFERRED'),'url'=>U('index')];
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('TRANSFER_FAILED')];
		}

		return $result;
	}

	/*
	* 放弃客户
	* @param int $abandon_id 放弃原因id
	*/
	public function customerToPool($company_id,$now_member_id,$customer_id,$abandon_id)
	{
		$field['company_id'] = $company_id;

		$field['isvalid'] = 1;

		$field['customer_id'] = $customer_id;

		if(getCrmDbModel('order')->where($field)->find() || getCrmDbModel('customer')->where($field)->getField('is_trade'))
		{
			return ['status'=>0,'msg'=>L('CUSTOMERS_ALREADY_CANNOT_GIVE_UP')];
		}
		else
		{
			$member_id = getCrmDbModel('customer')->where($field)->getField('member_id');

			if(getCrmDbModel('customer')->where($field)->save(['member_id'=>0]))
			{
				getCrmDbModel('contacter')->where($field)->save(['member_id'=>0]);

				getCrmDbModel('opportunity')->where($field)->save(['member_id'=>0]);

				$customer_abandon['customer_id'] = $customer_id;

				$customer_abandon['abandon_id'] = $abandon_id;

				$customer_abandon['company_id'] = $company_id;

				$customer_abandon['member_id'] = $member_id;

				$customer_abandon['operator_id'] = $now_member_id;

				$customer_abandon['createtime'] = NOW_TIME;

				getCrmDbModel('customer_abandon')->add($customer_abandon);

				$customer_detail = CrmgetCrmDetailList('customer',$customer_id,$company_id,'name,phone');

				D('CrmLog')->addCrmLog('customer',9,$company_id,$now_member_id,$customer_id,0,0,0,$customer_detail['name']);

				$result = ['status'=>2,'msg'=>L('SUCCESSFULLY_ABANDON_CUSTOMERS'),'url'=>U('index')];
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('ABANDON_CUSTOMER_FAILURE')];
			}
		}

		return $result;
	}

	public function getMobileListInfo($customer,$company_id)
	{
		//除备注外全部客户自定义字段
		$allField = getCrmDbModel('define_form')->where(['type'=>'customer','company_id'=>$company_id,'closed'=>0,'form_name'=>['neq','remark']])->count();

		foreach($customer as $key=>&$val)
		{
			$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

			$val['member_name'] = $thisMember['name'];

			$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

			$val['create_name'] = $createMember['name'];

			$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id);

			$haveFieldArr = $val['detail'];

			unset($haveFieldArr['remark']);

			$haveField = count(array_filter($haveFieldArr));

			$percent = round($haveField/$allField*100);

			if($percent >= 50)
			{
				$val['percent'] = '<span class="percentlist green6">'.$percent.'%</span>';
			}
			else
			{
				$val['percent'] = '<span class="percentlist red5">'.$percent.'%</span>';
			}

			$contacter_detail = CrmgetCrmDetailList('contacter',$val['first_contact_id'],$company_id,'name,phone');

			$val['contacter_name'] = $contacter_detail['name'];

			$val['contacter_phone'] = $contacter_detail['phone'];

			$followup = getCrmDbModel('followup')->where(['company_id'=>$company_id,'customer_id'=>$val['customer_id'],'isvalid'=>1])->order('createtime desc')->find();

			if($followup)
			{
				$follow_member = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$followup['member_id']])->field('member_id,account,name')->find();

				$val['follow_member'] = $follow_member['name'];

				$val['follow_time'] = date('m/d H:i',$followup['createtime']);

				$val['follow_content'] = htmlspecialchars_decode($followup['content']);

				$val['follow_content'] = strip_tags($val['follow_content']);
			}

			$val['customer_id'] = encrypt($val['customer_id'],'CUSTOMER');
		}

		return $customer;
	}
}

<?php

namespace Common\Model;

use Common\Model\BasicModel;

class CrmClueModel extends BasicModel
{
	protected $autoCheckFields = false;
	
	/*
	* 获取修改完成列表页的html
	* @param int $company_id 公司ID
	* @param int $contract_id 合同ID
    * @param int $type 1 线索 2 线索池
	*/

	public function getClueListHtml($company_id,$clue_id,$type)
	{
		$clue = getCrmDbModel('clue')->where(['company_id'=>$company_id,'clue_id'=>$clue_id,'isvalid'=>1])->find();

		$index = session('index');

		$show_list = CrmgetShowListField('clue',$company_id); //线索列表显示字段

		if($type == 1)
		{
			$thisMember = M('Member')->where(['company_id' => $company_id, 'closed' => 0, 'type' => 1, 'member_id' => $clue['member_id']])->field('member_id,account,name')->find();

			$clue['member_name'] = $thisMember['name'];
		}

		$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$clue['creater_id']])->field('member_id,account,name')->find();

		$clue['create_name'] = $createMember['name'];

		$clue['detail'] = CrmgetCrmDetailList('clue',$clue['clue_id'],$company_id,$show_list['form_name']);
		
		$html = '';

		$html .= '<td class="checkbox relative">';
		
		$html .= '<input type="checkbox" name="del[]" lay-skin="primary" value="'.encrypt($clue_id,'CLUE').'"><div class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon layui-icon-ok"></i></div>';
			
		$html .= '</td>';
		
		/*$html .= '<td onclick="clickOpenDetail(this,\'clue\')" >'.$clue['clue_prefix'].$clue['clue_no'].'</td>';*/

		if($type == 1)
		{
			$html .= '<td onclick="clickOpenDetail(this,\'clue\')">' . getClueStatusName($clue['status'], 'html') . '</td>';
		}

		foreach($show_list['form_list'] as $key => $val)
		{
			if(!$val['role_id'] || in_array($index['role_id'],explode(',',$val['role_id'])))
			{
				$html .= '<td onclick="clickOpenDetail(this,\'clue\')" ';

				if($val['form_type'] == 'textarea')
				{
					$html .= 'title="'.strip_tags($clue['detail'][$val['form_name']]).'" ';
				}

				if($val['form_name'] == 'name')
				{
					$html .= 'class="blue8"';
				}

				$html .= ' >';

				if($val['form_type'] == 'textarea')
				{
					$html .= mb_substr(strip_tags($clue['detail'][$val['form_name']]),0,20).'...';
				}
				else
				{
					$html .= $clue['detail'][$val['form_name']] ? $clue['detail'][$val['form_name']] : '--';
				}

				$html .= '</td>';
			}
		}

		$html .= '<td onclick="clickOpenDetail(this,\'clue\')">'.getDates($clue['lastfollowtime']).'</td>';

		$html .= '<td onclick="clickOpenDetail(this,\'clue\')">'.getDates($clue['nextcontacttime']).'</td>';

		$html .= '<td onclick="clickOpenDetail(this,\'clue\')">'.getDates($clue['createtime']).'</td>';

		if($type == 1)
		{
			$html .= '<td onclick="clickOpenDetail(this,\'clue\')">' . $clue['member_name'] . '</td>';
		}

		$html .= '<td onclick="clickOpenDetail(this,\'clue\')">'.getCustomerCreateName($clue['creater_id'],$clue['create_name']).'</td>';

		$html .= '<td onclick="clickOpenDetail(this,\'clue\')">'.getCrmEntryMethod($clue['entry_method']).'</td>';

		return $html;
	}

	/*
	* 线索转换为客户
	* @param array $clue 线索数组
	* @param array $transform 提交参数
	* @param int $company_id 公司ID
	* @param int $member_id 用户id
	*/
	public function transformClue($clue_id,$clue,$detail,$transform,$company_id,$member_id)
	{
		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		$feelec_merchant_id = getCrmDbModel('clue')->where(['company_id'=>$company_id,'clue_id'=>$clue_id])->getField('feelec_merchant_id');

		//新建客户
		if($transform['type'] == 1)
		{
			//客户信息
			$customer = [
				'member_id'         => $clue['member_id'],
				'company_id'        => $company_id,
				'createtime'        => NOW_TIME,
				'creater_id'        => $member_id,
				'customer_prefix'   => $crmsite['customerCode'] ? $crmsite['customerCode'] : 'C-',
				'from_type'         => 'PC',
				'feelec_merchant_id' => $feelec_merchant_id ? $feelec_merchant_id : '',
			];

			$customer['customer_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$company_id), rand(0,9), 4));

			$customer_form = I('post.customer_form');

			$CustomerCheckForm = D('CrmDefineForm')->CheckForm($company_id,$customer_form,'customer');

			if($CustomerCheckForm['detail'])
			{
				$customer_detail = $CustomerCheckForm['detail'];
			}
			else
			{
				return $CustomerCheckForm;
			}
		}
		else //关联客户
		{
			if(!$transform['customer_id'])
			{
				return ['status'=>0,'msg'=>L('PLEASE_SELECT_CUSTOMER')];
			}

			$customer_id = $transform['customer_id'];
		}

		if($transform['create_contacter'] == 1) //创建联系人
		{
			//联系人信息
			$contacter_form = I('post.contacter_form');

			$ControllerCheckForm = D('CrmDefineForm')->CheckForm($company_id,$contacter_form,'contacter');

			if($ControllerCheckForm['detail'])
			{
				$contacter_detail = $ControllerCheckForm['detail'];
			}
			else
			{
				return $ControllerCheckForm;
			}
		}

		if(!$customer_id) $customer_id = getCrmDbModel('customer')->add($customer);

		if($customer_id)//客户
		{
			if($transform['type'] == 1)
			{
				saveFeelCRMEncodeId($customer_id,$company_id);

				foreach($customer_detail as &$v)
				{
					$v['customer_id'] = $customer_id;

					$v['company_id'] = $company_id;

					if(is_array($v['form_content']))
					{
						$v['form_content'] = implode(',',$v['form_content']);
					}

					getCrmDbModel('customer_detail')->add($v); //添加客户详情
				}
			}
			else
			{
				//客户没有关联商户id则关联
				if($feelec_merchant_id && !getCrmDbModel('customer')->where(['customer_id' => $customer_id, 'company_id' => $company_id])->getField('feelec_merchant_id'))
				{
					getCrmDbModel('customer')->where(['customer_id' => $customer_id, 'company_id' => $company_id])->save(['feelec_merchant_id' => $feelec_merchant_id]);
				}

				$customerData = [
					'name'  =>$detail['company'] ? $detail['company'] : $detail['name'],
					'phone' =>$detail['phone'] ? $detail['phone'] : '',
					'email' =>$detail['email'] ? $detail['email'] : '',
					'origin' =>$detail['source'] ? $detail['source'] : '',
					'industry' =>$detail['industry'] ? $detail['industry'] : '',
					'address' =>$detail['address'] ? $detail['address'] : '',
					'website' =>$detail['website'] ? $detail['website'] : '',
					'remark' =>$detail['remark'] ? $detail['remark'] : '',
				];

				//更新客户信息，已存在的信息不覆盖
				$define_form = getCrmDbModel('define_form')->where(['type'=>'customer','form_name'=>['in',['name','phone','email','origin','industry','address','website','remark']],'company_id'=>$company_id,'closed'=>0])->field('form_id,form_name')->select();

				foreach ($define_form as $key=>$val)
				{
					$form_content = getCrmDbModel('customer_detail')->where(['company_id'=>$company_id,'form_id'=>$val['form_id'],'customer_id'=>$customer_id])->find();

					if($customerData[$val['form_name']])
					{
						if($form_content)
						{
							//仅更新未完善信息
							if($transform['update'] == 1)
							{
								//客户信息内容未填写，保存
								if(!$form_content['form_content'])
								{
									getCrmDbModel('customer_detail')->where(['company_id'=>$company_id,'form_id'=>$val['form_id'],'customer_id'=>$customer_id])->save(['form_content'=>$customerData[$val['form_name']]]);
								}
							}
							else
							{
								getCrmDbModel('customer_detail')->where(['company_id'=>$company_id,'form_id'=>$val['form_id'],'customer_id'=>$customer_id])->save(['form_content'=>$customerData[$val['form_name']]]);
							}

						}
						else
						{
							//客户信息不存在，新增
							$form_data =[
								'company_id'    =>$company_id,
								'form_id'       =>$val['form_id'],
								'customer_id'   =>$customer_id,
								'form_content'  =>$customerData[$val['form_name']]
							];

							getCrmDbModel('customer_detail')->add($form_data);
						}
					}
				}
			}

			getCrmDbModel('clue')->where(['clue_id'=>$clue_id,'company_id'=>$company_id])->save(['customer_id'=>$customer_id,'status'=>2]);

			D('CrmLog')->addCrmLog('clue',16,$company_id,$member_id,$customer_id,0,0,0,$detail['name'],0,0,0,0,0,0,0,0,$clue_id);

			if($transform['type'] == 1)
			{
				D('CrmLog')->addCrmLog('customer', 1, $company_id, $member_id, $customer_id, 0, 0, 0, $customer_detail['name']['form_content']);
			}

			//同步联系记录
			if($transform['sync_follow'] == 1)
			{
				$follow = getCrmDbModel('followup')->where(['clue_id' => $clue_id, 'company_id' => $company_id, 'isvalid' => 1])->order('createtime desc')->select();

				foreach ($follow as $key => $val)
				{
					$followData = [
						'type'          => 'customer',
						'customer_id'   => $customer_id,
						'company_id'    => $company_id,
						'member_id'     => $val['member_id'],
						'cmncate_id'    => $val['cmncate_id'],
						'reply_id'      => $val['reply_id'],
						'content'       => $val['content'],
						'createtime'    => $val['createtime'],
					];

					getCrmDbModel('followup')->add($followData);

					//查询联系记录的评论记录并同步
					$followComment = getCrmDbModel('follow_comment')->where(['company_id' => $company_id, 'follow_id' => $val['follow_id'], 'isvalid' => 1])->order('createtime desc')->select();

					foreach ($followComment as $k1=>$v1)
					{
						$commentData = [
							'company_id'    => $company_id,
							'member_id'     => $v1['member_id'],
							'follow_id'     => $v1['follow_id'],
							'content'       => $v1['content'],
							'createtime'    => $v1['createtime'],
						];

						getCrmDbModel('follow_comment')->add($commentData);
					}
				}
			}

			if($contacter_detail)
			{
				$contacter = [];

				$contacter['company_id'] = $company_id;

				$contacter['customer_id'] = $customer_id;

				if($customer['member_id'])
				{
					$contacter['member_id'] = $customer['member_id'];
				}

				$contacter['creater_id'] = $member_id;

				$contacter['createtime'] = NOW_TIME;

				if($contacter_id = getCrmDbModel('contacter')->add($contacter)) //添加客户联系人
				{
					if($transform['type'] == 1)
					{
						getCrmDbModel('customer')->where(['customer_id' => $customer_id, 'company_id' => $company_id])->save(['first_contact_id' => $contacter_id]); //添加客户首要联系人id
					}

					foreach($contacter_detail as &$v)
					{
						$v['contacter_id'] = $contacter_id;

						$v['company_id'] = $company_id;

						if(is_array($v['form_content']))
						{
							$v['form_content'] = implode(',',$v['form_content']);
						}

						getCrmDbModel('contacter_detail')->add($v);  //添加联系人详情
					}

					D('CrmLog')->addCrmLog('contacter',1,$company_id,$member_id,$customer_id,$contacter_id,0,0,$contacter_detail['name']['form_content']);
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
				}
			}

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('Customer/detail',['id'=>encrypt($customer_id,'CUSTOMER')])];
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
		}

		return $result;
	}

	/*
	* 领取线索
	* @param int $company_id 公司ID
	* @param int $member_id 用户id
	* @param int $clue_id 线索id
	*/
	public function drawClue($company_id,$member_id,$clue_id,$sms)
	{
		$field['company_id'] = $company_id;

		$field['isvalid'] = 1;

		$field['clue_id'] = $clue_id;

		$clue = getCrmDbModel('clue')->where($field)->field('member_id')->find();

		if(!$clue['member_id'])
		{
			if(getCrmDbModel('clue')->where($field)->save(['member_id'=>$member_id]))
			{
				D('CrmCreateMessage')->createMessage(102,$sms,$member_id,$company_id,$member_id,0,0,0,0,0,0,$clue_id);

				$clue_detail = CrmgetCrmDetailList('clue',$clue_id,$company_id,'name,phone');

				D('CrmLog')->addCrmLog('clue',5,$company_id,$member_id,0,0,0,0,$clue_detail['name'],0,$member_id,0,0,0,0,0,0,$clue_id);

				$result = ['status'=>2,'msg'=>L('RECEIVED_SUCCESSFULLY'),'url'=>U('index')];
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('CLAIM_FAILED')];
			}
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('CLUE_HAS_BEEN_CLAIMED')];
		}

		return $result;
	}

	/*
	* 分配线索
	* @param int $now_member_id 当前登录人员id
	*/
	public function allotClue($company_id,$member_id,$now_member_id,$clue_id,$sms)
	{
		$field['company_id'] = $company_id;

		$field['isvalid'] = 1;

		$field['clue_id'] = $clue_id;

		$clue = getCrmDbModel('clue')->where($field)->field('member_id')->find();

		if(!$clue['member_id'])
		{
			if(getCrmDbModel('clue')->where($field)->save(['member_id'=>$member_id]))
			{
				D('CrmCreateMessage')->createMessage(101,$sms,$member_id,$company_id,$now_member_id,0,0,0,0,0,0,$clue_id);

				$clue_detail = CrmgetCrmDetailList('clue',$clue_id,$company_id,'name,phone');

				D('CrmLog')->addCrmLog('clue',6,$company_id,$now_member_id,0,0,0,0,$clue_detail['name'],0,$member_id,0,0,0,0,0,0,$clue_id);

				$result = ['status'=>2,'msg'=>L('ASSIGN_SUCCESS'),'url'=>U('pool')];
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('ASSIGN_FAILED')];
			}
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('CLUE_HAS_BEEN_ASSIGNED')];
		}

		return $result;
	}

	//线索转移
	public function transferClue($company_id,$member_id,$now_member_id,$clue_id,$sms)
	{
		$field['company_id'] = $company_id;

		$field['isvalid'] = 1;

		$field['clue_id'] = $clue_id;

		if(getCrmDbModel('clue')->where($field)->save(['member_id'=>$member_id]))
		{
			D('CrmCreateMessage')->createMessage(103,$sms,$member_id,$company_id,$now_member_id,0,0,0,0,0,0,$clue_id);

			$clue_detail = CrmgetCrmDetailList('clue',$clue_id,$company_id,'name,phone');

			D('CrmLog')->addCrmLog('clue',8,$company_id,$now_member_id,0,0,0,0,$clue_detail['name'],0,$member_id,0,0,0,0,0,0,$clue_id);

			$result = ['status'=>2,'msg'=>L('SUCCESSFULLY_TRANSFERRED'),'url'=>U('index')];
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('TRANSFER_FAILED')];
		}

		return $result;
	}

	public function clueToPool($company_id,$now_member_id,$clue_id,$abandon_id)
	{
		$field['company_id'] = $company_id;

		$field['isvalid'] = 1;

		$field['clue_id'] = $clue_id;

		$status = getCrmDbModel('clue')->where($field)->getField('status');

		if($status == 2)
		{
			return ['status'=>0,'msg'=>L('CLUE_ALREADY_CANNOT_GIVE_UP')];
		}
		else
		{
			$member_id = getCrmDbModel('clue')->where($field)->getField('member_id');

			if(getCrmDbModel('clue')->where($field)->save(['member_id'=>0]))
			{
				$clue_abandon['clue_id'] = $clue_id;

				$clue_abandon['abandon_id'] = $abandon_id;

				$clue_abandon['company_id'] = $company_id;

				$clue_abandon['member_id'] = $member_id;

				$clue_abandon['operator_id'] = $now_member_id;

				$clue_abandon['createtime'] = NOW_TIME;

				getCrmDbModel('clue_abandon')->add($clue_abandon);

				$clue_detail = CrmgetCrmDetailList('clue',$clue_id,$company_id,'name,phone');

				D('CrmLog')->addCrmLog('clue',9,$company_id,$now_member_id,0,0,0,0,$clue_detail['name'],0,0,0,0,0,0,0,0,$clue_id);

				$result = ['status'=>2,'msg'=>L('SUCCESSFULLY_ABANDON_CLUES'),'url'=>U('index')];
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('ABANDON_CLUE_FAILURE')];
			}
		}

		return $result;
	}
}

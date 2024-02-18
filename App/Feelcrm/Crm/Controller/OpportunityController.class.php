<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

use Think\Cache\Driver\Redis;
use Think\Page;

class OpportunityController extends BasicController
{
	protected $OpportunityFields = ['member_id','customer_id','opportunity_no','opportunity_prefix'];

	protected $company = [];

	protected $all_view_auth = [];

    protected $group_view_auth = [];

    protected $own_view_auth = [];

	public function _initialize()
    {
        parent::_initialize();

		$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'all',$this->_member['role_id'],'crm');

        $this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'group',$this->_member['role_id'],'crm');

        $this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'own',$this->_member['role_id'],'crm');

		$this->assign('isAllViewAuth',$this->all_view_auth);

        $this->assign('isGroupViewAuth',$this->group_view_auth);

        $this->assign('isOwnViewAuth',$this->own_view_auth);

		$this->_company = M('company')->where(['company_id'=>$this->_company_id])->find();
    }

	public function index()
	{
		$field = ['company_id'=>$this->_company_id,'isvalid'=>'1'];

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id);

		$customer_auth = $getCustomerAuth['customer_auth'];

		$memberRoleArr = $getCustomerAuth['memberRoleArr'];

		$this->assign('customer_auth',$customer_auth);

		if(!I('get.ImemberRole'))
		{
			$field['member_id'] = $memberRoleArr;
		}
		else
		{
			$ImemberRole = unserialize(urldecode(I('get.ImemberRole')));

			$field['member_id'] = $ImemberRole;
		}
		if(I('get.Itime')) $field['createtime'] = unserialize(urldecode(I('get.Itime')));

		if(!$field['member_id'])
		{
			$this->common->_empty();die;
		}

		//创建人维度查看商机权限
		$CreaterViewOpportunity = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CreaterViewOpportunity',$this->_member['role_id'],'crm');

		if($CreaterViewOpportunity)
		{
			$field['_string'] = getCreaterViewSql($field['member_id']);

			unset($field['member_id']);

			$this->assign('isCreaterView',$CreaterViewOpportunity);

			//我创建的
			if($customer_auth == 'own-create')
			{
				$field['creater_id'] = $memberRoleArr;
			}

			//我负责的
			if($customer_auth == 'own-charge')
			{
				$field['member_id'] = $memberRoleArr;
			}
		}

		if(I('get.ImemberId')) $field['member_id'] = I('get.ImemberId');

		$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$memberRoleArr,'closed'=>0])->field('name,member_id,group_id')->select();

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword = unserialize(urldecode(I('get.highKeyword')));

			if(!$highKeyword)
			{
				$highKeyword = I('get.highKeyword');

				$_GET['highKeyword'] = urlencode(serialize(I('get.highKeyword')));
			}

			$opportunityHighKey = D('CrmHighKeyword')->opportunityHighKey($this->_company_id,$highKeyword);

			if($opportunityHighKey['field'])
			{
				$field = array_merge($field,$opportunityHighKey['field']);
			}

			$this->assign('highKeyword',$highKeyword);

			if($highKeywordMemberId = I('get.highKeywordMemberId'))
			{
				$this->assign('highKeywordMemberId',$highKeywordMemberId);
			}

			if($highKeywordCreateId = I('get.highKeywordCreateId'))
			{
				$this->assign('highKeywordCreateId',$highKeywordCreateId);
			}

			if($highKeywordGroupId = I('get.highKeywordGroupId'))
			{
				$this->assign('highKeywordGroupId',$highKeywordGroupId);
			}
		}

		if($keyword = I('get.keyword'))
		{
			$keywordField = CrmgetDefineFormField($this->_company_id,'opportunity','name',$keyword);

			$keywordCondition['opportunity_id'] = $keywordField ? ['in',$keywordField] : '0';

			$keywordCondition['opportunity_no'] = ["like","%".$keyword."%"];

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			$this->assign('keyword', $keyword);
		}

		//预计成交
		if($predict_time = I('get.predict_time'))
		{
			$predict_time_sql = D('CrmHighKeyword')->getFieldSqlByTime('opportunity','predict_time',$predict_time,$this->_company_id);

			//组合数据查询条件
			$field['_string'] = $predict_time_sql;

			$this->assign('predict_time',$predict_time);
		}

		//排序
		$getSortBy = D('CrmHighKeyword')->getSortBy();

		$order = $getSortBy['order'];

		$sort_by = $getSortBy['sort_by'];

		$this->assign('sort_by',$sort_by);

		$nowPage = I('get.p');

		$count = getCrmDbModel('opportunity')->where($field)->count();

		$Page = new Page($count, 20);

        if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$this->assign('pageCount', $count);

		$opportunity = getCrmDbModel('opportunity')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		$show_list = CrmgetShowListField('opportunity',$this->_company_id); //合同列表显示字段

		$export_field = C('EXPORT_OPPORTUNITY_FIELD');

		$export_arr = C('EXPORT_OPPORTUNITY_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'opportunity');

		foreach($opportunity as $key=>&$val)
		{
			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

			$val['member_name'] = $thisMember['name'];

			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

			$val['create_name'] = $createMember['name'];

			$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name');

			$val['customer_name'] = $customer_detail['name'];

			$val['detail'] = CrmgetCrmDetailList('opportunity',$val['opportunity_id'],$this->_company_id,$show_list['form_name']);
		}

		$form_description = getCrmLanguageData('form_description');

		//查询筛选项
		$filterlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_name'=>['in','stage,predict_time']])
			->order('orderby asc')
			->select();

		foreach($filterlist as $k=>&$v)
		{
			if($v['form_name'] == 'stage') $v['option'] = explode('|',$v['form_option']);
		}

		$this->assign('filterlist',$filterlist);

		$defineformlist = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		foreach($defineformlist as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		$this->assign('defineformlist',$defineformlist);

		$selectCustomerCount =  getCrmDbModel('Customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->count();

		$selectCustomerPage = new Page($selectCustomerCount, 10);

		$selectCustomerlist =  getCrmDbModel('Customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->field('customer_id,customer_prefix,customer_no,first_contact_id,createtime')->limit($selectCustomerPage->firstRow, $selectCustomerPage->listRows)->order('createtime desc')->select();

		foreach($selectCustomerlist as $key => &$val)
		{
			$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name,phone');
		}

		if(strlen($selectCustomerPage->show()) > 16) $this->assign('selectCustomerPage', $selectCustomerPage->show()); // 赋值分页输出

		$this->assign('selectCustomer',$selectCustomerlist);

		$form_description = getCrmLanguageData('form_description');
		//查询筛选项
		$customerFilterlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_name'=>['in','customer_grade,importance']])
			->order('orderby asc')
			->select();

		foreach($customerFilterlist as $k=>&$v)
		{
			$v['option'] = explode('|',$v['form_option']);
		}

		$this->assign('customerFilterlist',$customerFilterlist);

		$role = D('Role')->where(['company_id'=>$this->_company_id])->field('role_id,role_name')->fetchAll();

		$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

		$memberCount =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->count();

		$memberPage = new Page($memberCount, 10);

		$list =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->limit($memberPage->firstRow, $memberPage->listRows)->select();

		if(strlen($memberPage->show()) > 16) $this->assign('memberPage', $memberPage->show()); // 赋值分页输出

		$this->assign('roleList',$role);

		$this->assign('groupList',$group);

		$this->assign('member',$list);

		$deleteOpportunityAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'opportunity/delete',$this->_member['role_id'],'crm'); //删除商机权限

		$exportOpportunityAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'opportunity/export',$this->_member['role_id'],'crm'); //导出商机权限

		$importOpportunityAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'opportunity/import',$this->_member['role_id'],'crm'); //导入商机权限

		if($importOpportunityAuth)
		{
			$importTemp = getCrmDbModel('temp_file')->where(['company_id'=>$this->_company_id,'type'=>'opportunity'])->getField('file_link');

			$this->assign('importTemp',$importTemp);
		}

		//转移权限
		//$transferOpportunityAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'opportunity/transfer',$this->_member['role_id'],'crm');

		$this->assign('isExportOpportunityAuth',$exportOpportunityAuth);

		$this->assign('isImportOpportunityAuth',$importOpportunityAuth);

        $this->assign('isDelOpportunityAuth',$deleteOpportunityAuth);

		//$this->assign('istransferOpportunityAuth',$transferOpportunityAuth);

		$this->assign('opportunity',$opportunity);

		$this->assign('members',$members);

		//取得所属部门和用户的数组转为json输出给模板
		$groupIds = '';
		foreach ($members as $key=>&$val)
		{
			$val['id'] = $val['member_id'];

			if($val['group_id'])
			{
				$groupIds .= $val['group_id'].',';
			}
		}

		if($highKeywordGroupId)
		{
			$highKeywordCurrMembers = getGroupMemberByGroups($this->_company_id,$highKeywordGroupId);

			$this->assign('highKeywordCurrMembers',json_encode($highKeywordCurrMembers['user']));
		}

		$groupIds = rtrim($groupIds,',');

		$groups = M('group')->where(['company_id'=>$this->_company_id,'closed'=>0,'group_id'=>['in',explode(',',$groupIds)]])->field('group_id as id,group_name as name')->select();

		$this->assign('highKeywordMembers',json_encode($members));

		$this->assign('highKeywordGroups',json_encode($groups));

		$this->assign('formList',$show_list['form_list']);

		$this->assign('export_list',$export_list);

		$this->assign('nowPage',$nowPage);

		session('opportunityExportWhere',$field);

		session('ExportOrder',$order);

		$this->display();

	}

	public function detail($id,$detailtype='',$detail_source = 'crm')
	{
		$opportunity_id = decrypt($id,'OPPORTUNITY');

		if($detailtype)
		{
			$detailtype = decrypt($detailtype,'OPPORTUNITY');

			$this->assign('detailtype',$detailtype);
		}
		else
		{
			$detailtype = 'index';

			$this->assign('detailtype','index');
		}

		$this->assign('detail_source',$detail_source);

		$opportunity = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id])->find();

		if(!$opportunity)
		{
			$this->common->_empty();

			die;
		}

		$isDetailAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'OpportunityDetail',$this->_member['role_id'],'crm'); //商机详情权限

		$isProductAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'OpportunityProduct',$this->_member['role_id'],'crm'); //商机产品权限

		$isContractAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'OpportunityContract',$this->_member['role_id'],'crm');//商机合同权限

		$this->assign('isDetailAuthView',$isDetailAuthView);

		$this->assign('isProductAuthView',$isProductAuthView);

		$this->assign('isContractAuthView',$isContractAuthView);

		$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$opportunity['member_id']])->field('member_id,account,name,group_id')->find();

		$opportunity['member_name'] = $thisMember['name'];

		$opportunity['group_id'] = $thisMember['group_id'];

		$createMember = M('Member')->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 1, 'member_id' => $opportunity['creater_id']])->field('member_id,account,name')->find();

		$opportunity['creater_name'] = $createMember['name'];

		$follow = getCrmDbModel('followup')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'isvalid'=>1])->order('createtime desc')->getField('createtime');

		$opportunity['follow_time'] = $follow;

		$form_description = getCrmLanguageData('form_description');

		$opportunityform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		foreach($opportunityform as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		$opportunity['detail'] = CrmgetCrmDetailList('opportunity',$opportunity_id,$this->_company_id);

		$opportunityform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_type'=>['eq','textarea']])->order('orderby asc')->select();

		$this->assign('opportunityform',$opportunityform);

		$this->assign('opportunityform2',$opportunityform2);

		//查询商机阶段自定义字段
		$opportunityStageForm = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_name'=>'stage'])
			->find();

		$stageForm = explode('|',$opportunityStageForm['form_option']);

		$is_already = 0;

		foreach ($stageForm as $k1=>$v1)
		{
			//商机阶段-输单
			if($k1 == (count($stageForm) - 1))
			{
				$opportunityStageForm['stage_end']['value'] = $v1;

				$opportunityStageForm['stage_end']['is_already'] = $v1 == $opportunity['detail']['stage'] ? 1 : 0;
			}
			else
			{
				$opportunityStageForm['stage_list'][$k1]['value'] = $v1;

				if($v1 == $opportunity['detail']['stage'])
				{
					$is_already = 1;
				}

				$opportunityStageForm['stage_list'][$k1]['is_already'] = !$is_already ? 1 : 0;

				//商机阶段-赢单
				if($k1 == (count($stageForm) - 2) && $v1 == $opportunity['detail']['stage'])
				{
					break;
				}
			}
		}

		$this->assign('opportunityStageForm',$opportunityStageForm);

		$edit_opportunity_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'opportunity/edit', $this->_member['role_id'], 'crm');  //修改商户权限

		$this->assign('isEditOpportunityAuth', $edit_opportunity_auth);

		//合同数量
		if($isContractAuthView)
		{
			$contractfield = ['company_id'=>$this->_company_id,'isvalid'=>'1','opportunity_id'=>$opportunity_id];

			$contractCount = getCrmDbModel('contract')->where($contractfield)->count();

			$this->assign('contractCount',$contractCount);
		}

		$group = D('Group')->where(['company_id' => $this->_company_id])->field('group_id,group_name')->fetchAll();

		$this->assign('groupList', $group);

		if(!$detailtype || in_array($detailtype,['index','follow','analysis']))
		{
			if(!$isDetailAuthView)
			{
				$this->common->_empty();

				die;
			}

			if(isset($_GET['request']) && $_GET['request'] == 'crmlog')
			{
				$crmlog = D('CrmLog')->getCrmLog('opportunity', $opportunity_id, $this->_company_id);

				$result = ['data'=>$crmlog['crmlog'],'type'=>encrypt('index','OPPORTUNITY'),'pages'=>ceil($crmlog['count']/10)];

				$this->ajaxReturn($result);
			}
			else
			{
				$customer = getCrmDbModel('customer')->where(['company_id' => $this->_company_id, 'isvalid' => 1, 'customer_id' => $opportunity['customer_id']])->find();

				$customer['detail'] = CrmgetCrmDetailList('customer', $opportunity['customer_id'], $this->_company_id, 'name');

				$this->assign('customer', $customer);

				/*$transfer_opportunity = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'opportunity/transfer', $this->_member['role_id'], 'crm'); //转移权限

				$this->assign('istransferOpportunityAuth', $transfer_opportunity);

				$memberCount = M('Member')->where(['company_id' => $this->_company_id, 'type' => 1,'closed'=>0,'feelec_opened'=>10])->count();

				$memberPage = new Page($memberCount, 10);

				$list = M('Member')->where(['company_id' => $this->_company_id, 'type' => 1,'closed'=>0,'feelec_opened'=>10])->limit($memberPage->firstRow, $memberPage->listRows)->select();

				if (strlen($memberPage->show()) > 16) $this->assign('memberPage', $memberPage->show()); // 赋值分页输出

				$this->assign('member', $list);

				$group = D('Group')->where(['company_id' => $this->_company_id])->field('group_id,group_name')->fetchAll();

				$role = D('Role')->where(['company_id'=>$this->_company_id])->field('role_id,role_name')->fetchAll();

				$this->assign('groupList', $group);

				$this->assign('roleList',$role);*/

				$isFollowAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CustFollow',$this->_member['role_id'],'crm');//联系记录权限

				$this->assign('isFollowAuthView',$isFollowAuthView);

				//联系记录
				if ($isFollowAuthView)
				{
					$cmncate_field = getCrmLanguageData('cmncate_name');

					$cmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['company_id' => $this->_company_id, 'closed' => 0])->select();

					$follow = getCrmDbModel('followup')->where(['opportunity_id' => $opportunity_id, 'company_id' => $this->_company_id, 'isvalid' => 1])->order('createtime desc')->select();

					foreach ($follow as $k3 => &$v3)
					{
						$member = M('member')->where(['company_id' => $this->_company_id, 'member_id' => $v3['member_id'], 'type' => 1])->field('name,face')->find();

						$v3['member_name'] = $member['name'];

						$v3['member_face'] = $member['face'];

						if ($v3['cmncate_id']) {
							$cmncate_field = getCrmLanguageData('cmncate_name');

							$followCmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['cmncate_id' => $v3['cmncate_id'], 'company_id' => $this->_company_id])->find();

							$v3['cmncate_name'] = $followCmncate['cmncate_name'];
						}

						if ($v3['contacter_id'])
						{
							$v3['contacter_detail'] = CrmgetCrmDetailList('contacter', $v3['contacter_id'], $this->_company_id, 'name');
						}

						$v3['followComment'] = getCrmDbModel('follow_comment')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'isvalid' => 1])->order('createtime desc')->select();

						$uploadFiles = getCrmDbModel('upload_file')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'file_form' => 'follow'])->select();

						$v3['createFiles'] = $uploadFiles;
					}

					$fieldcontacter['company_id'] = $this->_company_id;

					$fieldcontacter['isvalid'] = '1';

					$fieldcontacter['customer_id'] = $opportunity['customer_id'];

					$contacter = getCrmDbModel('contacter')->where($fieldcontacter)->select();

					foreach ($contacter as $k4 => &$v4)
					{
						$v4['detail'] = CrmgetCrmDetailList('contacter', $v4['contacter_id'], $this->_company_id);
					}

					$members = D('Member')->where(['company_id' => $this->_company_id, 'type' => 1,'closed'=>0])->field('company_id,member_id,group_id,account,name,mobile,type,role_id,nickname,face,closed')->fetchAll();

					$commentFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/commentFollow', $this->_member['role_id'], 'crm');    //联系记录评论权限

					$this->assign('isCommentFollowAuth', $commentFollow_id);

					$this->assign('members', $members);

					$this->assign('contacter', $contacter);

					$this->assign('follow', $follow);

					$this->assign('cmncate', $cmncate);

					//商机附件
					$files =  getCrmDbModel('upload_file')->where(['opportunity_id' => $opportunity_id, 'company_id' => $this->_company_id])->order('create_time desc')->select();

					$this->assign('files', $files);
				}

				//失单
				$lose_field = getCrmLanguageData('lose_name');

				$lose = getCrmDbModel('lose')->where(['company_id' => $this->_company_id, 'closed' => 0])->field('lose_id,'.$lose_field)->select();

				$lose_log = getCrmDbModel('customer_lose')->where(['company_id' => $this->_company_id, 'opportunity_id' => $opportunity_id])->select();

				foreach ($lose_log as $losek => &$losev)
				{
					$losev['lose_name'] = getCrmDbModel('lose')->where(['company_id' => $this->_company_id, 'lose_id' => $losev['lose_id']])->getField($lose_field);

					$competitor_name = CrmgetCrmDetailList('competitor', $losev['competitor_id'], $this->_company_id, 'name');

					$losev['competitor_name'] = $competitor_name['name'];

					$losev['member_name'] = M('member')->where(['company_id' => $this->_company_id, 'type' => 1, 'member_id' => $losev['member_id']])->getField('name');

					$losev['operator_name'] = M('member')->where(['company_id' => $this->_company_id, 'type' => 1, 'member_id' => $losev['operator_id']])->getField('name');
				}

				//商机分析
				$analysis = getCrmDbModel('analysis')->where(['company_id' => $this->_company_id, 'opportunity_id' => $opportunity_id])->field('analysis_id')->find();

				$analysis_id = $analysis['analysis_id'];

				if ($analysis_id) {
					$analysis['detail'] = CrmgetCrmDetailList('analysis', $analysis_id, $this->_company_id);

					$this->assign('analysis_id', $analysis_id);
				} else {
					$analysis = [];

					$analysis['company_id'] = $this->_company_id;

					$analysis['customer_id'] = $opportunity['customer_id'];

					$analysis['opportunity_id'] = $opportunity_id;

					$analysis['createtime'] = NOW_TIME;

					$analysis['creater_id'] = $this->member_id;

					$analysis_id = getCrmDbModel('analysis')->add($analysis);

					$analysis['analysis_id'] = $analysis_id;
				}

				$analysisform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 'analysis', 'form_type' => ['neq', 'textarea']])->order('orderby asc')->select();

				foreach ($analysisform as $key => &$val) {
					if (in_array($val['form_type'], ['radio','select','checkbox','select_text'])) {
						$val['option'] = explode('|', $val['form_option']);
					}

					if ($val['form_type'] == 'region') {
						$analysis_detail = getCrmDbModel('analysis_detail')->where(['company_id' => $this->_company_id, 'form_id' => $val['form_id'], 'analysis_id' => $analysis_id])->find();

						if ($analysis_detail['form_content']) {
							$region_detail = explode(',', $analysis_detail['form_content']);

							$analysis[$val['form_name']]['defaultCountry'] = $region_detail[0];

							$analysis[$val['form_name']]['defaultProv'] = $region_detail[1];

							$analysis[$val['form_name']]['defaultCity'] = $region_detail[2];

							$analysis[$val['form_name']]['defaultArea'] = $region_detail[3];

						}
					}
				}

				$analysisform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 'analysis', 'form_type' => ['eq', 'textarea']])->order('orderby asc')->select();

				$edit_analysis_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'opportunity/edit_analysis', $this->_member['role_id'], 'crm');  //编辑客户分析权限

				$this->assign('isEditAnalysisAuth', $edit_analysis_auth);

				$this->assign('analysis', $analysis);

				$this->assign('analysisform', $analysisform);

				$this->assign('analysisform2', $analysisform2);

				$competitor = getCrmDbModel('competitor')->where(['company_id' => $this->_company_id, 'isvalid' => 1, 'opportunity_id' => $opportunity_id])->order('createtime desc')->select();

				foreach ($competitor as $k10 => &$v10) {
					$v10['detail'] = CrmgetCrmDetailList('competitor', $v10['competitor_id'], $this->_company_id);
				}
				//var_dump($competitor);die;
				$this->assign('competitor', $competitor);

				$lose_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'opportunity/lose', $this->_member['role_id'], 'crm'); //失单权限

				$this->assign('isLoseCustomerAuth', $lose_customer);

				$this->assign('loses', $lose);

				$this->assign('lose_log', $lose_log);
			}
		}

		if($detailtype =='product')
		{
			if(!$isProductAuthView)
			{
				$this->common->_empty();

				die;
			}

			$productfield = ['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id];

			$count = getCrmDbModel('opportunity_product')->where($productfield)->count();

			$Page = new Page($count, 20);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$product = getCrmDbModel('opportunity_product')->where($productfield)->limit($Page->firstRow, $Page->listRows)->select();

			$product_type_field = getCrmLanguageData('type_name');

			foreach($product as $k8=>&$v8)
			{
				$thisProduct = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'product_id'=>$v8['product_id']])->field('product_img,type_id')->find();

				$v8['product_img'] = $thisProduct['product_img'];

				$v8['detail'] = CrmgetCrmDetailList('product',$v8['product_id'],$this->_company_id);

				$v8['type_name'] = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'type_id'=>$thisProduct['type_id']])->getField($product_type_field);
			}

			$this->assign('product',$product);
		}

		//合同
		if($detailtype == 'contract')
		{
			if(!$isContractAuthView)
			{
				$this->common->_empty();

				die;
			}

			$Page = new Page($contractCount, 20);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$contract = getCrmDbModel('contract')->where($contractfield)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

			foreach($contract as $k6=>&$v6)
			{
				$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$v6['member_id']])->field('member_id,account,name')->find();

				$v6['member_name'] = $thisMember['name'];

				$v6['detail'] = CrmgetCrmDetailList('contract',$v6['contract_id'],$this->_company_id);
			}

			$show_list = CrmgetShowListField('contract',$this->_company_id); //列表显示字段

			$this->assign('formList',$show_list['form_list']);

			$this->assign('contract',$contract);
		}

		$this->assign('opportunity',$opportunity);

		$this->display();
	}

	public function edit_analysis($id="",$opportunity_id,$detailtype="",$detail_source="")
	{
		$analysis_id = decrypt($id,'OPPORTUNITY');

		$opportunity_id = decrypt($opportunity_id,'OPPORTUNITY');

		$detailtype = decrypt($detailtype,'OPPORTUNITY');

		$localurl = U('detail',['id'=>encrypt($opportunity_id,'OPPORTUNITY'),'detailtype'=>encrypt($detailtype,'OPPORTUNITY'),'detail_source'=>$detail_source]);

		if(IS_POST)
		{
			$data = $this->checkAnalysisEdit($analysis_id);

			$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id])->getField('customer_id');

			if(!$analysis_id)
			{
				$analysis_id = getCrmDbModel('analysis')->add(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'opportunity_id'=>$opportunity_id,'createtime'=>NOW_TIME,'creater_id'=>$this->member_id]);
			}

			getCrmDbModel('analysis_detail')->where(['analysis_id'=>$analysis_id,'company_id'=>$this->_company_id])->delete();

			foreach($data['analysis_detail'] as &$v)
			{
				$v['analysis_id'] = $analysis_id;

				$v['company_id'] = $this->_company_id;

				if(is_array($v['form_content']))
				{
					$v['form_content'] = implode(',',$v['form_content']);
				}

				getCrmDbModel('analysis_detail')->add($v);  //添加需求分析详情
			}

			$opportunity_detail = CrmgetCrmDetailList('opportunity',$opportunity_id,$this->_company_id,'name');

			D('CrmLog')->addCrmLog('analysis',2,$this->_company_id,$this->member_id,$customer_id,0,0,0,$opportunity_detail['name'],0,0,$analysis_id,0,0,0,0,0,0,$opportunity_id);

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];

			$this->ajaxReturn($result);

		}
		else
		{
			$analysis = getCrmDbModel('analysis')->where(['company_id'=>$this->_company_id,'analysis_id'=>$analysis_id,'opportunity_id'=>$opportunity_id])->find();

			$this->assign('detailtype',$detailtype);

			$this->assign('detail_source',$detail_source);

			$form_description = getCrmLanguageData('form_description');

			$analysisform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'analysis'])->order('orderby asc')->select();

			foreach($analysisform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$analysis_detail = getCrmDbModel('analysis_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'analysis_id'=>$analysis_id])->find();

				if($v['form_type']=='region')
				{
					if($analysis_detail['form_content'])
					{
						$region_detail = explode(',',$analysis_detail['form_content']);

						$analysis[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$analysis[$v['form_name']]['defaultProv'] = $region_detail[1];

						$analysis[$v['form_name']]['defaultCity'] = $region_detail[2];

						$analysis[$v['form_name']]['defaultArea'] = $region_detail[3];

					}
				}else
				{
					$analysis[$v['form_name']] = $analysis_detail['form_content'];
				}

			}

			$this->assign('opportunity_id',$opportunity_id);

			$this->assign('analysisform',$analysisform);

			$this->assign('analysis',$analysis);

			$this->display();
		}
	}

	public function checkAnalysisEdit($analysis_id)
	{
		$analysis_form = I('post.analysis_form');

		$AnalysisCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$analysis_form,'analysis',$this->_member,$analysis_id);

		if($AnalysisCheckForm['detail'])
		{
			$analysis_detail = $AnalysisCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($AnalysisCheckForm);
		}

		return ['analysis_detail'=>$analysis_detail];
	}

	public function create_competitor($id,$detailtype="")
	{
		$opportunity_id = decrypt($id,'OPPORTUNITY');

		$detailtype = decrypt($detailtype,'OPPORTUNITY');

		$localurl = U('detail',['id'=>encrypt($opportunity_id,'OPPORTUNITY'),'detailtype'=>encrypt($detailtype,'OPPORTUNITY')]);

		$this->assign('opportunity_id',$opportunity_id);

		$this->assign('detailtype',$detailtype);

		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'competitor'];

		if(IS_POST)
		{
			$data = $this->checkCompetitorCreate($opportunity_id);

			$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id])->getField('customer_id');

			$data['competitor']['customer_id'] = $customer_id;

			if($competitor_id = getCrmDbModel('competitor')->add($data['competitor']))//竞争对手
			{
				foreach($data['competitor_detail'] as &$v)
				{
					$v['competitor_id'] = $competitor_id;

					$v['company_id'] = $this->_company_id;

					if(is_array($v['form_content']))
					{
						$v['form_content'] = implode(',',$v['form_content']);
					}

					getCrmDbModel('competitor_detail')->add($v);  //添加竞争对手详情
				}

				$opportunity_detail = CrmgetCrmDetailList('opportunity',$opportunity_id,$this->_company_id,'name');

				D('CrmLog')->addCrmLog('competitor',1,$this->_company_id,$this->member_id,$customer_id,0,0,0,$opportunity_detail['name'],0,0,0,$competitor_id,0,0,0,0,0,$opportunity_id);

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];

			}
			else
			{
				$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$form_description = getCrmLanguageData('form_description');

			$competitorform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($competitorform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$this->assign('competitorform',$competitorform);

			$this->display();
		}
	}
	public function checkCompetitorCreate($opportunity_id)
	{
		$competitor = I('post.competitor') ? I('post.competitor') : [];

		if(!$competitor['opportunity_id'])
		{
			if($opportunity_id)
			{
				$competitor['opportunity_id'] = $opportunity_id;
			}
			else
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('PARAMETER_ERROR')]);
			}
		}

		$competitor['company_id'] = $this->_company_id;

		$competitor['creater_id'] = $this->member_id;

		$competitor['createtime'] = NOW_TIME;

		$competitor_form = I('post.competitor_form');

		$CompetitorCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$competitor_form,'competitor',$this->_member);

		if($CompetitorCheckForm['detail'])
		{
			$competitor_detail = $CompetitorCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($CompetitorCheckForm);
		}

		return ['competitor'=>$competitor,'competitor_detail'=>$competitor_detail];
	}
	public function edit_competitor($id="",$opportunity_id,$detailtype="",$detail_source="")
	{
		$competitor_id = decrypt($id,'OPPORTUNITY');

		if(!$competitor_id)
		{
			$this->common->_empty();

			die;
		}

		$opportunity_id = decrypt($opportunity_id,'OPPORTUNITY');

		$detailtype = decrypt($detailtype,'OPPORTUNITY');

		$localurl = U('detail',['id'=>encrypt($opportunity_id,'OPPORTUNITY'),'detailtype'=>encrypt($detailtype,'OPPORTUNITY'),'detail_source'=>$detail_source]);

		$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id])->getField('customer_id');

		if(IS_POST)
		{
			$data = $this->checkCompetitorEdit($competitor_id);

			getCrmDbModel('competitor_detail')->where(['competitor_id'=>$competitor_id,'company_id'=>$this->_company_id])->delete();

			foreach($data['competitor_detail'] as &$v)
			{
				$v['competitor_id'] = $competitor_id;

				$v['company_id'] = $this->_company_id;

				if(is_array($v['form_content']))
				{
					$v['form_content'] = implode(',',$v['form_content']);
				}

				getCrmDbModel('competitor_detail')->add($v);  //添加联系人详情
			}

			$opportunity_detail = CrmgetCrmDetailList('opportunity',$opportunity_id,$this->_company_id,'name');

			D('CrmLog')->addCrmLog('competitor',2,$this->_company_id,$this->member_id,$customer_id,0,0,0,$opportunity_detail['name'],0,0,0,$competitor_id,0,0,0,0,0,$opportunity_id);

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];

			$this->ajaxReturn($result);

		}
		else
		{
			$competitor = getCrmDbModel('competitor')->where(['company_id'=>$this->_company_id,'competitor_id'=>$competitor_id,'opportunity_id'=>$opportunity_id])->find();

			$this->assign('detailtype',$detailtype);

			$this->assign('detail_source',$detail_source);

			$form_description = getCrmLanguageData('form_description');

			$competitorform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'competitor'])->order('orderby asc')->select();

			foreach($competitorform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$competitor_detail = getCrmDbModel('competitor_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'competitor_id'=>$competitor_id])->find();

				if($v['form_type']=='region')
				{
					if($competitor_detail['form_content'])
					{
						$region_detail = explode(',',$competitor_detail['form_content']);

						$competitor[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$competitor[$v['form_name']]['defaultProv'] = $region_detail[1];

						$competitor[$v['form_name']]['defaultCity'] = $region_detail[2];

						$competitor[$v['form_name']]['defaultArea'] = $region_detail[3];

					}
				}else
				{
					$competitor[$v['form_name']] = $competitor_detail['form_content'];
				}
			}

			$this->assign('competitorform',$competitorform);

			$this->assign('competitor',$competitor);

			$this->display();
		}
	}
	public function checkCompetitorEdit($competitor_id)
	{
		$competitor_form = I('post.competitor_form');

		$CompetitorCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$competitor_form,'competitor',$this->_member,$competitor_id);

		if($CompetitorCheckForm['detail'])
		{
			$competitor_detail = $CompetitorCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($CompetitorCheckForm);
		}

		return ['competitor_detail'=>$competitor_detail];
	}

	public function delete_competitor($id,$detailtype='')
	{
		if(IS_AJAX)
		{
			$competitor_id = decrypt($id,'OPPORTUNITY');

			$competitor = getCrmDbModel('competitor')->where(['company_id'=>$this->_company_id,'competitor_id'=>$competitor_id,'isvalid'=>'1'])->find();

			$detailtype = decrypt($detailtype,'OPPORTUNITY');

			$localurl = U('detail',['id'=>encrypt($competitor['opportunity_id'],'OPPORTUNITY'),'detailtype'=>encrypt($detailtype,'OPPORTUNITY')]);

			$where = ['competitor_id'=>$competitor_id,'company_id'=>$this->_company_id,'isvalid'=>'1'];

			if(getCrmDbModel('competitor')->where($where)->getField('competitor_id'))
			{
				if(getCrmDbModel('competitor')->where($where)->save(['isvalid'=>0]))
				{
					$opportunity_detail = CrmgetCrmDetailList('opportunity',$competitor['opportunity_id'],$this->_company_id,'name');

					D('CrmLog')->addCrmLog('competitor',3,$this->_company_id,$this->member_id,$competitor['customer_id'],0,0,0,$opportunity_detail['name'],0,0,0,$competitor_id,0,0,0,0,0,$competitor['opportunity_id']);

					$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>$localurl];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('WRONG_COMPETITOR')];
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$this->common->_empty();
		}
	}

	public function create($id='',$detailtype='',$jump = '')
	{
		$customer_id = decrypt($id,'CUSTOMER');

		$localurl = U('index');

		$detailtype = decrypt($detailtype,'CUSTOMER');

		if($detailtype)
		{
			$localurl = U('Customer/detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

			$this->assign('customer_id',$customer_id);

			$this->assign('detailtype',$detailtype);
		}

		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity'];

		if(IS_POST)
		{
			$data = $this->checkCreate();

			if($opportunity_id = getCrmDbModel('opportunity')->add($data['opportunity']))//添加商机
            {
				foreach($data['opportunity_detail'] as &$v)
                {
                    $v['opportunity_id'] = $opportunity_id;

                    $v['company_id'] = $this->_company_id;

                    if(is_array($v['form_content']))
                    {
                        $v['form_content'] = implode(',',$v['form_content']);
                    }

                    getCrmDbModel('opportunity_detail')->add($v); //添加合同详情
                }

				foreach($data['opportunityPro'] as &$v2)
				{
					$v2['opportunity_id'] = $opportunity_id;

					getCrmDbModel('opportunity_product')->add($v2); //添加合同产品
				}

	            D('CrmLog')->addCrmLog('opportunity',1,$this->_company_id,$this->member_id,$data['opportunity']['customer_id'],0,0,0,$data['opportunity_detail']['name']['form_content'],0,0,0,0,0,0,0,0,0,$opportunity_id);

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];
			}
			else
            {
                $result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
            }

            $this->ajaxReturn($result);
		}
		else
		{
			$form_description = getCrmLanguageData('form_description');

			$opportunityform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($opportunityform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$field['company_id'] = $this->_company_id;

			$field['isvalid'] = '1';

			$field['closed'] = 0;


			$count = getCrmDbModel('product')->where($field)->count();

			$Page = new Page($count, 15);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$product = getCrmDbModel('product')->where($field)->limit($Page->firstRow, $Page->listRows)->select();

			$product_type_field = getCrmLanguageData('type_name');

			foreach($product as $key=>&$val)
			{
				$product_detail = getCrmDbModel('product_detail')->where(['company_id'=>$this->_company_id,'product_id'=>$val['product_id']])->select();

				foreach($product_detail as $k1=>&$v1)
				{
					$form_name = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'form_id'=>$v1['form_id'],'type'=>'product'])->field('form_name')->find();

					$val[$form_name['form_name']] = $v1['form_content'];
				}

				$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'type_id'=>$val['type_id']])->find();

				$val['type_name'] = $product_type['type_name'];
			}


			$this->assign('product',$product);

			$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

			$product_type = CrmfetchAll($product_type,'type_id');

			$product_type_h = $this->resultCategory($product_type,count($product_type));

			$this->assign('product_type_h',$product_type_h);

			$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id);

			$memberRoleArr = $getCustomerAuth['memberRoleArr'];

			$selectCustomerCount =  getCrmDbModel('Customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->count();

			$selectCustomerPage = new Page($selectCustomerCount, 10);

			$selectCustomerlist =  getCrmDbModel('Customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->field('customer_id,customer_prefix,customer_no,first_contact_id,createtime')->limit($selectCustomerPage->firstRow, $selectCustomerPage->listRows)->order('createtime desc')->select();

			foreach($selectCustomerlist as $key => &$val)
			{
				$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name,phone');

				//$val['contacter'] = CrmgetCrmDetailList('contacter',$val['first_contact_id'],$this->_company_id,'name,phone');
			}

			if(strlen($selectCustomerPage->show()) > 16) $this->assign('selectCustomerPage', $selectCustomerPage->show()); // 赋值分页输出

			$this->assign('selectCustomer',$selectCustomerlist);

			$form_description = getCrmLanguageData('form_description');
			//查询筛选项
			$customerFilterlist = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_name'=>['in','customer_grade,importance']])
				->order('orderby asc')
				->select();

			foreach($customerFilterlist as $k=>&$v)
			{
				$v['option'] = explode('|',$v['form_option']);
			}

			$this->assign('customerFilterlist',$customerFilterlist);

			$this->assign('opportunityform',$opportunityform);

			$this->display();
		}
	}

	public function checkCreate()
	{
		$opportunity = checkFields(I('post.opportunity'), $this->OpportunityFields);

		if(!$opportunity['customer_id'])
		{
			 $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_SELECT_CUSTOMER')]);
		}

		$customer_member_id = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$opportunity['customer_id']])->getField('member_id');

		$opportunity['member_id'] = $customer_member_id ? $customer_member_id : 0;

		$opportunity['company_id'] = $this->_company_id;//所属公司ID

		$opportunity['createtime'] = NOW_TIME;

		$opportunity['creater_id'] = $this->member_id;

		$opportunity['entry_method'] = 'CREATE';

		if($this->_crmsite['opportunityCode'])
		{
			$opportunity['opportunity_prefix'] = $this->_crmsite['opportunityCode'];
		}
		else
		{
			$opportunity['opportunity_prefix'] = 'S-';
		}

		$opportunity['opportunity_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$this->_company_id), rand(0,9), 4));

		$opportunity_form = I('post.opportunity_form');

		$OpportunityCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$opportunity_form,'opportunity',$this->_member);

		if($OpportunityCheckForm['detail'])
		{
			$opportunity_detail = $OpportunityCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($OpportunityCheckForm);
		}

		$opportunityPro = I('post.opportunityPro');

		if($opportunityPro)
		{
			foreach($opportunityPro as $key=>&$val)
			{
				$val['product_id'] = decrypt($val['product_id'],'PRODUCT');

				$val['customer_id'] = $opportunity['customer_id'];

				$val['company_id'] = $this->_company_id;
			}
		}

		return ['opportunity'=>$opportunity,'opportunity_detail'=>$opportunity_detail,'opportunityPro'=>$opportunityPro];

	}

	public function edit($id,$detailtype="")
	{
		$opportunity_id = decrypt($id,'OPPORTUNITY');

		$detailtype = decrypt($detailtype,'OPPORTUNITY');

		$localurl = U('index');

		if($detailtype)
		{
			$localurl = U('detail',['id'=>encrypt($opportunity_id,'OPPORTUNITY'),'detailtype'=>encrypt($detailtype,'OPPORTUNITY')]);

			$this->assign('detailtype',$detailtype);
		}

		$opportunity = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'isvalid'=>'1'])->find();

		if(IS_POST)
		{
			$data = $this->checkEdit($opportunity_id);

			getCrmDbModel('opportunity_detail')->where(['opportunity_id'=>$opportunity_id,'company_id'=>$this->_company_id])->delete();

			foreach($data['opportunity_detail'] as &$v)
			{
				$v['opportunity_id'] = $opportunity_id;

				$v['company_id'] = $this->_company_id;

				if(is_array($v['form_content']))
				{
					$v['form_content'] = implode(',',$v['form_content']);
				}

				getCrmDbModel('opportunity_detail')->add($v);  //添加商机详情
			}

			getCrmDbModel('opportunity_product')->where(['opportunity_id'=>$opportunity_id,'company_id'=>$this->_company_id])->delete();

			foreach($data['opportunityPro'] as &$v2)
			{
				$v2['opportunity_id'] = $opportunity_id;

				getCrmDbModel('opportunity_product')->add($v2); //添加商机产品
			}

			D('CrmLog')->addCrmLog('opportunity',2,$this->_company_id,$this->member_id,$opportunity['customer_id'],0,0,0,$data['opportunity_detail']['name']['form_content'],0,0,0,0,0,0,0,0,0,$opportunity_id);

			$html = D('CrmOpportunity')->getOpportunityListHtml($this->_company_id,$opportunity_id);

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl,'html'=>$html];

			$this->ajaxReturn($result);
		}
		else
		{
			$form_description = getCrmLanguageData('form_description');

			$opportunityform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity'])->order('orderby asc')->select();

			foreach($opportunityform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$opportunity_detail = getCrmDbModel('opportunity_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'opportunity_id'=>$opportunity_id])->find();

				if($v['form_type']=='region')
				{
					if($opportunity_detail['form_content'])
					{
						$region_detail = explode(',',$opportunity_detail['form_content']);

						$opportunity[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$opportunity[$v['form_name']]['defaultProv'] = $region_detail[1];

						$opportunity[$v['form_name']]['defaultCity'] = $region_detail[2];

						$opportunity[$v['form_name']]['defaultArea'] = $region_detail[3];

					}
				}else
				{
					$opportunity[$v['form_name']] = $opportunity_detail['form_content'];
				}
			}

			$opportunityPro = getCrmDbModel('opportunity_product')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id])->select();

			$product_type_field = getCrmLanguageData('type_name');

			foreach($opportunityPro as $k1=>&$v1)
			{
				$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'product_id'=>$v1['product_id'],'isvalid'=>'1'])->find();

				$v1['type_name'] = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'type_id'=>$product['type_id']])->getField($product_type_field);

				$v1['detail'] = CrmgetCrmDetailList('product',$v1['product_id'],$this->_company_id);
			}

			$this->assign('opportunityPro',$opportunityPro);

			$field['company_id'] = $this->_company_id;

			$field['isvalid'] = '1';

			$field['closed'] = 0;

			$count = getCrmDbModel('product')->where($field)->count();

			$Page = new Page($count, 15);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$product = getCrmDbModel('product')->where($field)->limit($Page->firstRow, $Page->listRows)->select();

			$product_type_field = getCrmLanguageData('type_name');

			foreach($product as $key=>&$val)
			{
				$product_detail = getCrmDbModel('product_detail')->where(['company_id'=>$this->_company_id,'product_id'=>$val['product_id']])->select();

				foreach($product_detail as $k1=>&$v1)
				{
					$form_name = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'form_id'=>$v1['form_id'],'type'=>'product'])->field('form_name')->find();

					$val[$form_name['form_name']] = $v1['form_content'];
				}

				$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'type_id'=>$val['type_id']])->find();

				$val['type_name'] = $product_type['type_name'];

				$opportunity_pro = getCrmDbModel('opportunity_product')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'product_id'=>$val['product_id']])->find();

				if($opportunity_pro) $val['opportunity_pro'] = 1;
			}

			$this->assign('product',$product);

			$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

			$product_type = CrmfetchAll($product_type,'type_id');

			$product_type_h = $this->resultCategory($product_type,count($product_type));

			$this->assign('product_type_h',$product_type_h);


			$members = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1])->field('member_id,account,name')->select();

			foreach ($members as $key=>&$val)
			{
				$val['id'] = $val['member_id'];
			}

			$this->assign('opportunityform',$opportunityform);

			$this->assign('opportunity',$opportunity);

			$this->display();
		}
	}

	public function checkEdit($opportunity_id)
	{
		$opportunity_form = I('post.opportunity_form');

		$OpportunityCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$opportunity_form,'opportunity',$this->_member,$opportunity_id);

		if($OpportunityCheckForm['detail'])
		{
			$opportunity_detail = $OpportunityCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($OpportunityCheckForm);
		}

		$opportunityPro = I('post.opportunityPro');

		if($opportunityPro)
		{
			$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'isvalid'=>'1'])->getField('customer_id');

			foreach($opportunityPro as $key=>&$val)
			{
				$val['product_id'] = decrypt($val['product_id'],'PRODUCT');

				$val['customer_id'] = $customer_id;

				$val['company_id'] = $this->_company_id;
			}
		}

		return ['opportunity_detail'=>$opportunity_detail,'opportunityPro'=>$opportunityPro];
	}

	public function transfer() //商机转移
	{
		if(IS_AJAX)
		{
			$ids = I('post.ids');

			$member_id = I('post.member_id');

			$member_id = decrypt($member_id,'MEMBER');

			if($member_id)
			{
				if(count($ids) > 0)
				{
					foreach($ids as $k=>$v)
					{
						$opportunity_id = decrypt($v,'OPPORTUNITY');

						$result = D('CrmOpportunity')->transferOpportunity($this->_company_id,$member_id,$this->member_id,$opportunity_id,$this->_sms);

						if($result['status'] != 2)
						{
							$this->ajaxReturn($result);
						}
					}
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_OPPORTUNITY_TRANSFER')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('SELECT_USER_TRANSFER')];
			}

			$this->ajaxReturn($result);

		}
		else
		{
			$this->common->_empty();
		}
	}

	public function lose() //失单
	{
		if(IS_AJAX)
		{
			$ids = I('post.ids');

			$lose_id = I('post.lose_id');

			$competitor_id = I('post.competitor_id');

			if(!$lose_id)
			{
				$result = ['status'=>0,'msg'=>L('SELECT_REASON_FOR_LOSS')];
			}
			// else if(!$competitor_id)
			// {
			// $result = ['status'=>0,'msg'=>'请选择竞争对手'];
			// }
			else
			{
				$field['company_id'] = $this->_company_id;

				$field['isvalid'] = 1;

				$result = [];

				if(count($ids) > 0)
				{
					$form_description = getCrmLanguageData('form_description');

					//查询商机阶段自定义字段
					$opportunityStageForm = getCrmDbModel('define_form')
						->field(['*',$form_description])
						->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_name'=>'stage'])
						->find();

					$stageForm = explode('|',$opportunityStageForm['form_option']);

					foreach ($stageForm as $k1=>$v1)
					{
						//商机阶段-输单
						if($k1 == (count($stageForm) - 1))
						{
							$stage_end = $v1;
						}
					}

					foreach($ids as $k=>$v)
					{
						$opportunity_id = decrypt($v,'OPPORTUNITY');

						$field['opportunity_id'] = $opportunity_id;

						if(getCrmDbModel('contract')->where($field)->find())
						{
							$result = ['status'=>0,'msg'=>L('OPPORTUNITY_CANNOT_BE_LOST')];
						}
						else
						{
							$member_id = getCrmDbModel('opportunity')->where($field)->getField('member_id');

							if(getCrmDbModel('opportunity')->where($field)->save(['is_losed'=>1]))
							{
								$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id])->getField('customer_id');

								$customer_lose['customer_id'] = $customer_id;

								$customer_lose['opportunity_id'] = $opportunity_id;

								$customer_lose['lose_id'] = $lose_id;

								$customer_lose['competitor_id'] = $competitor_id;

								$customer_lose['company_id'] = $this->_company_id;

								$customer_lose['member_id'] = $member_id;

								$customer_lose['operator_id'] = $this->member_id;

								$customer_lose['createtime'] = NOW_TIME;

								getCrmDbModel('customer_lose')->add($customer_lose);

								$opportunityStageFormId = getCrmDbModel('define_form')
									->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_name'=>'stage'])
									->getField('form_id');

								getCrmDbModel('opportunity_detail')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'form_id'=>$opportunityStageFormId])->save(['form_content'=>$stage_end]);

								$opportunity_detail = CrmgetCrmDetailList('opportunity',$opportunity_id,$this->_company_id,'name');

								D('CrmLog')->addCrmLog('opportunity',13,$this->_company_id,$this->member_id,$customer_id,0,0,0,$opportunity_detail['name'],0,0,0,0,0,0,0,0,0,$opportunity_id);

								$result = ['status'=>2,'msg'=>L('OPPORTUNITY_LOST'),'url'=>U('index')];
							}
							else
							{
								$result = ['status'=>0,'msg'=>L('LOST_ORDER_FAILED')];
							}
						}
					}
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_THE_OPPORTUNITY_TO_BE_LOST')];
				}
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$this->common->_empty();
		}
	}

	public function delete($id='')
	{
		if(IS_AJAX)
	    {
			$ids = I('post.ids');

		    $localurl = U('index');

		    if($ids && count($ids) > 0)
			{
				foreach($ids as $k=>$v)
				{
					$opportunity_id = decrypt($v,'OPPORTUNITY');

					$result = D('CrmOpportunity')->deleteOpportunity($opportunity_id,$this->_company_id,$this->member_id,$this->_member,$localurl,'arr');
				}
			}
			else
			{
				if($id)
				{
					$opportunity_id = decrypt($id,'OPPORTUNITY');

					$result = D('CrmOpportunity')->deleteOpportunity($opportunity_id,$this->_company_id,$this->member_id,$this->_member,$localurl,'one');
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_OPPORTUNITY_DELETE')];
				}
			}

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
	}

	public function resultCategory($data,$num = 0,$type_id = 0)
	{
		$i = 1;

		$count = count($data);

		foreach($data as $key=>$val)
		{
			if($type_id == $val['type_id'])
			{
				$prolink = '<a href="'.U('AjaxRequest/getProductList',['id'=>encrypt($val['type_id'],'PRODUCT')]).'" data-id="'.encrypt($val['type_id'],'PRODUCT').'" class="product-link product-link-curr">';
			}
			else
			{
				$prolink = '<a href="'.U('AjaxRequest/getProductList',['id'=>encrypt($val['type_id'],'PRODUCT')]).'" data-id="'.encrypt($val['type_id'],'PRODUCT').'" class="product-link">';
			}

			if($num > 1)
			{
				$arr[] .= '<ul class="protree-container-ul protree-children"><li class="protree-node  protree-open "><i class="protree-icon protree-ocl"></i>'.$prolink.'<i class="iconfont icon-2"></i>'.$val['type_name'].'</a>';
			}
			else
			{
				if($val['subClass'])
				{
					$arr[] .= '<ul class="protree-container-ul protree-children"><li class="protree-node  protree-open protree-last"><i class="protree-icon protree-ocl" ></i>'.$prolink.'<i class="iconfont icon-2"></i>'.$val['type_name'].'</a>';
				}
				else
				{
					if($i == $count)
					{
						$arr[] .= '<ul class="protree-container-ul protree-children"><li class="protree-node  protree-leaf protree-last"><i class="protree-icon protree-ocl" ></i>'.$prolink.'<i class="iconfont icon-2"></i>'.$val['type_name'].'</a>';
					}
					else
					{
						$arr[] .= '<ul class="protree-container-ul protree-children"><li class="protree-node  protree-leaf "><i class="protree-icon protree-ocl" ></i>'.$prolink.'<i class="iconfont icon-2"></i>'.$val['type_name'].'</a>';
					}

				}

			}


			if(count($val['subClass']) > 1)
			{
				$num = count($val['subClass']);
			}
			else
			{
				$num = 0;
			}

			if($val['subClass'])
			{
				$arr = array_merge($arr,$this->resultCategory($val['subClass'],$num,$type_id));
			}

			$arr[] .= '</li></ul>';

			$i++;
		}
		return $arr;
	}

	public function to_export()
	{
		$tableHeader = session('OpportunityExcelData')['header'];

		$letter      = session('OpportunityExcelData')['letter'];

		$excelData   = session('OpportunityExcelData')['excelData'];

		$filename = date('ymdhis').time();

		D('Excel')->exportExcel($filename.'.xls',$tableHeader,$letter,$excelData,'feelcrm');
	}

	public function common_export($tableHeader,$exportList)
	{
		$letter = $excelData = [];

		$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

		for($i=0;$i<count($tableHeader);$i++)
		{
			$letter[$i] = substr($str,$i,1);
		}

//            整合表格数据，列内容
		foreach($exportList as $k=>$v)
		{
			$excelData[] = $v;
		}

		$excel = ['header' => $tableHeader, 'letter' => $letter, 'excelData' => $excelData];

		session('OpportunityExcelData',$excel);

		$this->ajaxReturn(['msg'=>'success']);
	}

	public function export($action = '')
	{
		if(IS_AJAX)
        {
			$exportData = $tableHeader = [];

			if($action == 'index')
			{
				$export_field = C('EXPORT_OPPORTUNITY_FIELD');

				$export_arr = C('EXPORT_OPPORTUNITY_ARR');

				$where = session('opportunityExportWhere');

				$pagecount = 20;

				$data = D('CrmExport')->getDataList($where,$pagecount,'opportunity');

				if($data['msg'])
				{
					$this->ajaxReturn(['msg'=>$data['msg']]);die;
				}
				else
				{
					$opportunity = $data['data'];
				}

				$exportList = $data['exportList'];

				$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'opportunity',$exportList);

				$show_list = $export_list['show_list'];

				$tableHeader = $export_list['tableHeader'];

				$exportData = D('CrmExport')->getOpportunityList($opportunity,$this->_company_id,$exportList,$show_list);
			}
			else
			{
				$this->ajaxReturn(['msg'=>L('EXPORT_FAILED')]);die;
			}

            $this->common_export($tableHeader,$exportData);
        }
        else
        {
            $this->to_export();
        }
	}
}

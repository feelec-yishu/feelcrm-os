<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

use Think\Cache\Driver\Redis;
use Think\Page;

class ClueController extends BasicController
{
	protected $ClueFields = ['member_id','customer_id','clue_no','clue_prefix'];

	protected $company = [];

	protected $all_view_auth = [];

    protected $group_view_auth = [];

    protected $own_view_auth = [];

	public function _initialize()
    {
        parent::_initialize();

		$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'clueAll',$this->_member['role_id'],'crm');

        $this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'clueGroup',$this->_member['role_id'],'crm');

        $this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'clueOwn',$this->_member['role_id'],'crm');

		$this->assign('isAllViewAuth',$this->all_view_auth);

        $this->assign('isGroupViewAuth',$this->group_view_auth);

        $this->assign('isOwnViewAuth',$this->own_view_auth);

		$this->_company = M('company')->where(['company_id'=>$this->_company_id])->find();

	    $this->assign('company',$this->_company);
    }

	public function index()
	{
		$field = ['company_id'=>$this->_company_id,'isvalid'=>'1'];

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,'','clue');

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

		//创建人维度查看线索权限
		$CreaterViewClue = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CreaterViewClue',$this->_member['role_id'],'crm');

		if($CreaterViewClue)
		{
			$field['_string'] = getCreaterViewSql($field['member_id']);

			unset($field['member_id']);

			$this->assign('isCreaterView',$CreaterViewClue);

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

		$clue_status = isset($_GET['clue_status']) ? I('get.clue_status') : '-1,1';

		if($clue_status)
		{
			$field['status'] = ['in',$clue_status];
		}

		$this->assign('clue_status',$clue_status);

		$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'member_id'=>$memberRoleArr])->field('name,member_id,group_id')->select();

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword = unserialize(urldecode(I('get.highKeyword')));

			if(!$highKeyword)
			{
				$highKeyword = I('get.highKeyword');

				$_GET['highKeyword'] = urlencode(serialize(I('get.highKeyword')));
			}

			$clueHighKey = D('CrmHighKeyword')->clueHighKey($this->_company_id,$highKeyword);

			if($clueHighKey['field'])
			{
				$field = array_merge($field,$clueHighKey['field']);
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
			$keywordField = CrmgetDefineFormField($this->_company_id,'clue','name,phone,email,company',$keyword);

			$keywordCondition['clue_id'] = $keywordField ? ['in',$keywordField] : '0';

			$keywordCondition['clue_no'] = ["like","%".$keyword."%"];

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			$this->assign('keyword', $keyword);
		}

		//排序
		$getSortBy = D('CrmHighKeyword')->getSortBy();

		$order = $getSortBy['order'];

		$sort_by = $getSortBy['sort_by'];

		$this->assign('sort_by',$sort_by);

		$nowPage = I('get.p');

		$count = getCrmDbModel('clue')->where($field)->count();

		$Page = new Page($count, 20);

        if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$this->assign('pageCount',$count);

		$clue = getCrmDbModel('clue')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		$show_list = CrmgetShowListField('clue',$this->_company_id); //线索列表显示字段

		$export_field = C('EXPORT_CLUE_INDEX_FIELD');

		$export_arr = C('EXPORT_CLUE_INDEX_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'clue');

		foreach($clue as $key=>&$val)
		{
			$val['member_name'] = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->getField('name');

			$val['create_name'] = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->getField('name');

			$val['detail'] = CrmgetCrmDetailList('clue',$val['clue_id'],$this->_company_id,$show_list['form_name']);
		}

		$form_description = getCrmLanguageData('form_description');
		
		$defineformlist = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		foreach($defineformlist as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		$this->assign('defineformlist',$defineformlist);

		//查询放弃原因
		$abandon_field = getCrmLanguageData('abandon_name');

		$abandon = getCrmDbModel('abandon')->where(['company_id' => $this->_company_id, 'closed' => 0])
			->field('abandon_id,'.$abandon_field)
			->select();

		$this->assign('abandons', $abandon);

		$role = D('Role')->where(['company_id'=>$this->_company_id])->field('role_id,role_name')->fetchAll();

		$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

		$memberCount =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->count();

		$memberPage = new Page($memberCount, 10);

		$list =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->limit($memberPage->firstRow, $memberPage->listRows)->select();

		if(strlen($memberPage->show()) > 16) $this->assign('memberPage', $memberPage->show()); // 赋值分页输出

		$this->assign('roleList',$role);

		$this->assign('groupList',$group);

		$this->assign('member',$list);

		$deleteClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'clue/delete',$this->_member['role_id'],'crm'); //删除线索权限

		$exportClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'clue/export',$this->_member['role_id'],'crm'); //导出线索权限

		$importClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'clue/import',$this->_member['role_id'],'crm'); //导入线索权限

		if($importClueAuth)
		{
			$importTemp = getCrmDbModel('temp_file')->where(['company_id'=>$this->_company_id,'type'=>'clue'])->getField('file_link');

			$this->assign('importTemp',$importTemp);
		}

		//转移权限
		$transferClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'clue/transfer',$this->_member['role_id'],'crm');

		//放入公海权限
		$toPoolClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'clue/toPool',$this->_member['role_id'],'crm');

		$this->assign('isExportClueAuth',$exportClueAuth);

		$this->assign('isImportClueAuth',$importClueAuth);

        $this->assign('isDelClueAuth',$deleteClueAuth);

        $this->assign('istransferClueAuth',$transferClueAuth);

        $this->assign('istoPoolClueAuth',$toPoolClueAuth);

		$this->assign('clue',$clue);

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

		session('clueExportWhere',$field);

		session('ExportOrder',$order);

		$this->display();

	}

	public function pool()
	{
		$field = ['company_id'=>$this->_company_id,'isvalid'=>'1','member_id'=>''];

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,'','cluePool');

		$customer_auth = $getCustomerAuth['customer_auth'];

		$memberRoleArr = $getCustomerAuth['memberRoleArr'];

		$this->assign('customer_auth',$customer_auth);

		$field['creater_id'] = $memberRoleArr;

		$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'member_id'=>$memberRoleArr])->field('name,member_id,group_id')->select();

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword = unserialize(urldecode(I('get.highKeyword')));

			if(!$highKeyword)
			{
				$highKeyword = I('get.highKeyword');

				$_GET['highKeyword'] = urlencode(serialize(I('get.highKeyword')));
			}

			$clueHighKey = D('CrmHighKeyword')->clueHighKey($this->_company_id,$highKeyword,'pool');

			if($clueHighKey['field'])
			{
				$field = array_merge($field,$clueHighKey['field']);
			}

			$this->assign('highKeyword',$highKeyword);

			if($highKeywordMemberId = I('get.highKeywordMemberId'))
			{
				$this->assign('highKeywordMemberId',$highKeywordMemberId);
			}

			if($highKeywordGroupId = I('get.highKeywordGroupId'))
			{
				$this->assign('highKeywordGroupId',$highKeywordGroupId);
			}
		}

		if($keyword = I('get.keyword'))
		{
			$keywordField = CrmgetDefineFormField($this->_company_id,'clue','name,,phone,email,company',$keyword);

			$keywordCondition['clue_id'] = $keywordField ? ['in',$keywordField] : '0';

			$keywordCondition['clue_no'] = ["like","%".$keyword."%"];

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			$this->assign('keyword', $keyword);
		}

		//排序
		$getSortBy = D('CrmHighKeyword')->getSortBy();

		$order = I('get.sort_by') ? $getSortBy['order'] : 'createtime desc';

		$sort_by = I('get.sort_by') ? $getSortBy['sort_by'] : 'createtime-desc';

		$this->assign('sort_by',$sort_by);

		$nowPage = I('get.p');

		$count = getCrmDbModel('clue')->where($field)->count();

		$Page = new Page($count, 20);

		$this->assign('pageCount', $count);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$clue = getCrmDbModel('clue')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		$show_list = CrmgetShowListField('clue',$this->_company_id); //线索列表显示字段

		$export_field = C('EXPORT_CLUE_POOL_FIELD');

		$export_arr = C('EXPORT_CLUE_POOL_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'clue');

		foreach($clue as $key=>&$val)
		{
			$val['create_name'] = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->getField('name');

			$val['detail'] = CrmgetCrmDetailList('clue',$val['clue_id'],$this->_company_id,$show_list['form_name']);
		}

		$form_description = getCrmLanguageData('form_description');

		$defineformlist = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		foreach($defineformlist as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		$this->assign('defineformlist',$defineformlist);

		$role = D('Role')->where(['company_id'=>$this->_company_id])->field('role_id,role_name')->fetchAll();

		$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

		$memberCount =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->count();

		$memberPage = new Page($memberCount, 10);

		$list =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->limit($memberPage->firstRow, $memberPage->listRows)->select();

		if(strlen($memberPage->show()) > 16) $this->assign('memberPage', $memberPage->show()); // 赋值分页输出

		$this->assign('roleList',$role);

		$this->assign('groupList',$group);

		$this->assign('member',$list);

		$isAllViewAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'cluePoolAll',$this->_member['role_id'],'crm');

		$isGroupViewAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'cluePoolGroup',$this->_member['role_id'],'crm');

		$isOwnViewAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'cluePoolOwn',$this->_member['role_id'],'crm');

		$this->assign('isAllViewAuth',$isAllViewAuth);

		$this->assign('isGroupViewAuth',$isGroupViewAuth);

		$this->assign('isOwnViewAuth',$isOwnViewAuth);

		$drawClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'clue/draw',$this->_member['role_id'],'crm'); //领取线索权限

		$allotClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'clue/allot',$this->_member['role_id'],'crm'); //分配线索权限

		$deleteClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'clue/delete',$this->_member['role_id'],'crm'); //删除线索权限

		$exportClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'clue/export_pool',$this->_member['role_id'],'crm'); //导出线索池权限

		$importClueAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'clue/import_pool',$this->_member['role_id'],'crm'); //导入线索池权限

		if($importClueAuth)
		{
			$importTemp = getCrmDbModel('temp_file')->where(['company_id'=>$this->_company_id,'type'=>'cluePool'])->getField('file_link');

			$this->assign('importTemp',$importTemp);
		}

		$this->assign('isDrawClueAuth',$drawClueAuth);

		$this->assign('isAllotClueAuth',$allotClueAuth);

		$this->assign('isExportCluePoolAuth',$exportClueAuth);

		$this->assign('isImportCluePoolAuth',$importClueAuth);

		$this->assign('isDelClueAuth',$deleteClueAuth);

		$this->assign('clue',$clue);

		$this->assign('members',$members);

		//查询放弃原因
		$abandon_field = getCrmLanguageData('abandon_name');

		$abandon = getCrmDbModel('abandon')->where(['company_id' => $this->_company_id, 'closed' => 0])
			->field('abandon_id,'.$abandon_field)
			->select();

		$this->assign('abandons', $abandon);

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

		session('cluePoolExportWhere',$field);

		session('ExportOrder',$order);

		$this->display();

	}

	public function detail($id,$detailtype='',$detail_source = 'crm')
	{
		$clue_id = decrypt($id,'CLUE');

		if($detailtype)
		{
			$detailtype = decrypt($detailtype,'CLUE');

			$this->assign('detailtype',$detailtype);
		}
		else
		{
			$detailtype = 'index';

			$this->assign('detailtype','index');
		}

		$this->assign('detail_source',$detail_source);

		$isDetailAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'ClueDetail',$this->_member['role_id'],'crm');//线索详情权限

		$isFollowAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'ClueFollow',$this->_member['role_id'],'crm');//线索联系记录权限

		$this->assign('isDetailAuthView',$isDetailAuthView);

		$this->assign('isFollowAuthView',$isFollowAuthView);

		$clue = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'clue_id'=>$clue_id])->find();

		if(!$clue)
		{
			$this->common->_empty();

			die;
		}

		$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$clue['member_id']])->field('member_id,account,name,group_id')->find();

		$clue['member_name'] = $thisMember['name'];

		$clue['group_id'] = $thisMember['group_id'];

		$clue['detail'] = CrmgetCrmDetailList('clue',$clue_id,$this->_company_id);

		if($clue['customer_id']) $clue['customer'] = CrmgetCrmDetailList('customer',$clue['customer_id'],$this->_company_id,'name');

		$form_description = getCrmLanguageData('form_description');

		$clueform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		foreach ($clueform as $key => &$val)
		{
			if (in_array($val['form_type'], ['radio','select','checkbox','select_text']))
			{
				$val['option'] = explode('|', $val['form_option']);
			}

			if ($val['form_type'] == 'region')
			{
				$clue_detail = getCrmDbModel('clue_detail')->where(['company_id' => $this->_company_id, 'form_id' => $val['form_id'], 'clue_id' => $clue_id])->find();

				if ($clue_detail['form_content'])
				{
					$region_detail = explode(',', $clue_detail['form_content']);

					$clue[$val['form_name']]['defaultCountry'] = $region_detail[0];

					$clue[$val['form_name']]['defaultProv'] = $region_detail[1];

					$clue[$val['form_name']]['defaultCity'] = $region_detail[2];

					$clue[$val['form_name']]['defaultArea'] = $region_detail[3];

				}
			}
		}

		$clueform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue','form_type'=>['eq','textarea']])->order('orderby asc')->select();

		$this->assign('clueform',$clueform);

		$this->assign('clueform2',$clueform2);

		if(!$detailtype || $detailtype =='index')
		{
			if(isset($_GET['request']) && $_GET['request'] == 'crmlog')
			{
				$crmlog = D('CrmLog')->getCrmLog('clue', $clue_id, $this->_company_id);

				$result = ['data'=>$crmlog['crmlog'],'type'=>encrypt('index','CLUE'),'pages'=>ceil($crmlog['count']/10)];

				$this->ajaxReturn($result);
			}
			else
			{
				//查询放弃原因
				$abandon_field = getCrmLanguageData('abandon_name');

				$abandon = getCrmDbModel('abandon')->where(['company_id' => $this->_company_id, 'closed' => 0])
					->field('abandon_id,'.$abandon_field)
					->select();

				$abandon_log = getCrmDbModel('clue_abandon')->where(['company_id' => $this->_company_id, 'clue_id' => $clue_id])->select();

				foreach ($abandon_log as $abk => &$abv)
				{
					$abv['abandon_name'] = getCrmDbModel('abandon')
						->where(['company_id' => $this->_company_id, 'abandon_id' => $abv['abandon_id']])
						->getField($abandon_field);

					$abv['member_name'] = M('member')->where(['company_id' => $this->_company_id, 'type' => 1, 'member_id' => $abv['member_id']])->getField('name');

					$abv['operator_name'] = M('member')->where(['company_id' => $this->_company_id, 'type' => 1, 'member_id' => $abv['operator_id']])->getField('name');
				}

				$this->assign('abandons', $abandon);

				$this->assign('abandon_log', $abandon_log);

				//联系记录
				if ($isFollowAuthView) {

					$cmncate_field = getCrmLanguageData('cmncate_name');

					$cmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['company_id' => $this->_company_id, 'closed' => 0])->select();

					$follow = getCrmDbModel('followup')->where(['clue_id' => $clue_id, 'company_id' => $this->_company_id, 'isvalid' => 1])->order('createtime desc')->select();

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

						$v3['followComment'] = getCrmDbModel('follow_comment')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'isvalid' => 1])->order('createtime desc')->select();

						$uploadFiles = getCrmDbModel('upload_file')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'file_form' => 'follow'])->select();

						$v3['createFiles'] = $uploadFiles;
					}

					$members = D('Member')->where(['company_id' => $this->_company_id, 'type' => 1,'closed'=>0])->field('company_id,member_id,group_id,account,name,mobile,type,role_id,nickname,face,closed')->fetchAll();

					$this->assign('members', $members);

					$commentFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/commentFollow', $this->_member['role_id'], 'crm');    //联系记录评论权限

					$this->assign('isCommentFollowAuth', $commentFollow_id);

					$this->assign('follow', $follow);

					$this->assign('cmncate', $cmncate);

					//线索附件
					$files =  getCrmDbModel('upload_file')->where(['clue_id' => $clue_id, 'company_id' => $this->_company_id])->order('create_time desc')->select();

					$this->assign('files', $files);
				}

				$memberCount = M('Member')->where(['company_id' => $this->_company_id, 'type' => 1,'closed'=>0,'feelec_opened'=>10])->count();

				$memberPage = new Page($memberCount, 10);

				$list = M('Member')->where(['company_id' => $this->_company_id, 'type' => 1,'closed'=>0,'feelec_opened'=>10])->limit($memberPage->firstRow, $memberPage->listRows)->select();

				if (strlen($memberPage->show()) > 16) $this->assign('memberPage', $memberPage->show()); // 赋值分页输出

				$this->assign('member', $list);

				$createMember = M('Member')->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 1, 'member_id' => $clue['creater_id']])->field('member_id,account,name')->find();

				$clue['creater_name'] = $createMember['name'];

				$group = D('Group')->where(['company_id' => $this->_company_id])->field('group_id,group_name')->fetchAll();

				$role = D('Role')->where(['company_id'=>$this->_company_id])->field('role_id,role_name')->fetchAll();

				$this->assign('groupList', $group);

				$this->assign('roleList',$role);

				$edit_clue_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'clue/edit', $this->_member['role_id'], 'crm'); //修改线索权限

				$this->assign('isEditClueAuth', $edit_clue_id);

				if($clue['member_id'] > 0)
				{
					$transfer_clue = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'clue/transfer', $this->_member['role_id'], 'crm'); //转移线索权限

					$toPool_clue = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'clue/toPool', $this->_member['role_id'], 'crm'); //放弃线索权限

					$this->assign('istransferClueAuth', $transfer_clue);

					$this->assign('istoPoolClueAuth', $toPool_clue);
				}
				else
				{
					$draw_clue_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'clue/draw', $this->_member['role_id'], 'crm'); //领取线索权限

					$allot_clue_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'clue/allot', $this->_member['role_id'], 'crm'); //分配线索权限

					$this->assign('isDrawClueAuth', $draw_clue_id);

					$this->assign('isAllotClueAuth', $allot_clue_id);
				}
			}
		}

		$this->assign('clue',$clue);

		$this->display();
	}

	public function create()
	{
		$localurl = U('index');

		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue'];

		if(IS_POST)
		{
			$data = $this->checkCreate();

			if($clue_id = getCrmDbModel('clue')->add($data['clue']))//添加线索
            {
	            saveFeelCRMEncodeId($clue_id,$this->_company_id,'Clue');

				foreach($data['clue_detail'] as &$v)
                {
                    $v['clue_id'] = $clue_id;

                    $v['company_id'] = $this->_company_id;

                    if(is_array($v['form_content']))
                    {
                        $v['form_content'] = implode(',',$v['form_content']);
                    }

                    getCrmDbModel('clue_detail')->add($v); //添加线索详情
                }

				//记录操作日志
				D('CrmLog')->addCrmLog('clue',1,$this->_company_id,$this->member_id,0,0,0,0,$data['clue_detail']['name']['form_content'],0,0,0,0,0,0,0,0,$clue_id);

	            if(!$data['clue']['member_id'])
	            {
		            $localurl = U('pool');

		            $clue_notifier = getCrmDbModel('notify_config')->where(['company_id'=>$this->_company_id])->getField('clue_notifier');

		            if($clue_notifier)
		            {
			            $clue_notifier = explode(',',$clue_notifier);

			            foreach($clue_notifier as $key=>$val)
			            {
				            D('CrmCreateMessage')->createMessage(105,$this->_sms,$val,$this->_company_id,$this->member_id,0,0,0,0,0,0,$clue_id);
			            }
		            }
		            else
		            {
			            $first_member = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'is_first'=>2])->getField('member_id');

			            D('CrmCreateMessage')->createMessage(105,$this->_sms,$first_member,$this->_company_id,$this->member_id,0,0,0,0,0,0,$clue_id);
		            }
	            }

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
			
			$clueform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($clueform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$this->member_id])->field('member_id,account,name')->find();

            $members = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'feelec_opened'=>10])->field('member_id,account,name')->select();

			foreach($members as $key=>&$val)
			{
				$val['id'] = $val['member_id'];
			}

            $this->assign('thisMember',$thisMember);

            $this->assign('members',$members);

			$this->assign('clueform',$clueform);

			$this->display();
		}
	}

	public function checkCreate()
	{
		$clue = checkFields(I('post.clue'), $this->ClueFields);

		/*if(!$clue['member_id'])
		{
			 $this->ajaxReturn(['status'=>0,'msg'=>L('SELECT_LEADER')]);
		}*/

		$clue['company_id'] = $this->_company_id;//所属公司ID

		$clue['createtime'] = NOW_TIME;

		$clue['creater_id'] = $this->member_id;

		$clue['entry_method'] = 'CREATE';

		if($this->_crmsite['clueCode'])
		{
			$clue['clue_prefix'] = $this->_crmsite['clueCode'];
		}
		else
		{
			$clue['clue_prefix'] = 'X-';
		}

		$clue['clue_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$this->_company_id), rand(0,9), 4));

		$clue_form = I('post.clue_form');

		$ClueCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$clue_form,'clue',$this->_member);

		if($ClueCheckForm['detail'])
		{
			$clue_detail = $ClueCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ClueCheckForm);
		}

		$clue['status'] = -1;

		return ['clue'=>$clue,'clue_detail'=>$clue_detail];

	}


	public function edit($id,$detailtype="")
	{
		$clue_id = decrypt($id,'CLUE');

		$detailtype = decrypt($detailtype,'CLUE');

		$localurl = U('index');

		if($detailtype)
		{
			$localurl = U('detail',['id'=>encrypt($clue_id,'CLUE'),'detailtype'=>encrypt($detailtype,'CLUE')]);

			$this->assign('detailtype',$detailtype);
		}

		$clue = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'clue_id'=>$clue_id,'isvalid'=>'1'])->find();

		if(IS_POST && $clue)
		{
			$data = $this->checkEdit($clue_id);

			getCrmDbModel('clue_detail')->where(['clue_id'=>$clue_id,'company_id'=>$this->_company_id])->delete();

			foreach($data as &$v)
			{
				$v['clue_id'] = $clue_id;

				$v['company_id'] = $this->_company_id;

				if(is_array($v['form_content']))
				{
					$v['form_content'] = implode(',',$v['form_content']);
				}

				$form_id = getCrmDbModel('clue_detail')->add($v);  //添加线索详情
			}

			if($form_id)
			{
				D('CrmLog')->addCrmLog('clue',2,$this->_company_id,$this->member_id,0,0,0,0,$data['name']['form_content'],0,0,0,0,0,0,0,0,$clue_id);

				if($clue['member_id'] > 0)
				{
					$html = D('CrmClue')->getClueListHtml($this->_company_id, $clue_id,1);
				}
				else
				{
					$html = D('CrmClue')->getClueListHtml($this->_company_id, $clue_id,2);
				}

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl,'html'=>$html];
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}

			$this->ajaxReturn($result);

		}
		else
		{
			$this->common->_empty();
		}
	}

	public function checkEdit($clue_id)
	{
		$clue_form = I('post.clue_form');

		$ClueCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$clue_form,'clue',$this->_member,$clue_id);

		if($ClueCheckForm['detail'])
		{
			$clue_detail = $ClueCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ClueCheckForm);
		}

		return $clue_detail;
	}

	public function delete($id='',$type='')
	{
		$type = decrypt($type,'CLUE');

		$localurl = U($type);

		if(IS_AJAX)
	    {
			$ids = I('post.ids');

		    $recover_clue = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'recover/clue',$this->_member['role_id'],'crm');

		    if($ids && count($ids) > 0)
			{
				foreach($ids as $k=>$v)
				{
					$clue_id = decrypt($v,'CLUE');

					$where = ['clue_id'=>$clue_id,'company_id'=>$this->_company_id];

					$clue = getCrmDbModel('clue')->where($where)->field('clue_id,isvalid,member_id')->find();

					if(!$clue)
					{
						$this->ajaxReturn(['status'=>0,'msg'=>L('CLUE_DOES_NOT_EXIST')]);
					}

					$clue['detail'] = CrmgetCrmDetailList('clue',$clue_id,$this->_company_id);

					if($clue['isvalid'] == 1)
					{
						$delete = getCrmDbModel('clue')->where($where)->save(['isvalid'=>0]);
					}
					else
					{
						if($recover_clue) $localurl = U('Recover/clue');

						$delete = getCrmDbModel('clue')->where($where)->delete();

						if($delete)
						{
							getCrmDbModel('clue_detail')->where($where)->delete();
						}
					}

					if($delete)
					{
						if($clue['isvalid'] == 1)
						{
							D('CrmLog')->addCrmLog('clue', 3, $this->_company_id, $this->member_id, 0, 0, 0, 0, $clue['detail']['name'], 0, 0, 0, 0, 0,0,0,0,$clue_id);
						}
						else
						{
							D('CrmLog')->addCrmLog('clue', 15, $this->_company_id, $this->member_id, 0, 0, 0, 0, $clue['detail']['name'], 0, 0, 0, 0, 0,0,0,0,$clue_id);
						}

						$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>$localurl];
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
					}
				}
			}
			else
			{
				if($id)
				{
					$clue_id = decrypt($id,'CLUE');

					$where = ['clue_id'=>$clue_id,'company_id'=>$this->_company_id];

					$clue = getCrmDbModel('clue')->where($where)->field('clue_id,isvalid,member_id')->find();

					if(!$clue)
					{
						$this->ajaxReturn(['status'=>0,'msg'=>L('CLUE_DOES_NOT_EXIST')]);
					}

					$clue['detail'] = CrmgetCrmDetailList('clue',$clue_id,$this->_company_id);

					if($clue['isvalid'] == 1)
					{
						$delete = getCrmDbModel('clue')->where($where)->save(['isvalid'=>0]);
					}
					else
					{
						if($recover_clue) $localurl = U('Recover/clue');

						$delete = getCrmDbModel('clue')->where($where)->delete();

						if($delete)
						{
							getCrmDbModel('clue_detail')->where($where)->delete();
						}
					}

					if($delete)
					{
						if($clue['isvalid'] == 1)
						{
							D('CrmLog')->addCrmLog('clue', 3, $this->_company_id, $this->member_id, 0, 0, 0, 0, $clue['detail']['name'], 0, 0, 0, 0, 0,0,0,0,$clue_id);
						}
						else
						{
							D('CrmLog')->addCrmLog('clue', 15, $this->_company_id, $this->member_id, 0, 0, 0, 0, $clue['detail']['name'], 0, 0, 0, 0, 0,0,0,0,$clue_id);
						}

						$result = ['status'=>3,'msg'=>L('DELETE_SUCCESS'),'url'=>$localurl];
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
					}
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_CONTRACT_DELETE')];
				}
			}

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
	}

	public function draw() //线索领取
	{
		if(IS_AJAX)
		{
			$ids = I('post.ids');

			$result = [];

			if(count($ids) > 0)
			{
				foreach($ids as $k=>$v)
				{
					$clue_id = decrypt($v,'CLUE');

					$result = D('CrmClue')->drawClue($this->_company_id,$this->member_id,$clue_id,$this->_sms);

					if($result['status'] != 2)
					{
						$this->ajaxReturn($result);
					}
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('SELECT_THE_CLUE_TO_RECEIVE')];
			}

			$this->ajaxReturn($result);

		}
		else
		{
			$this->common->_empty();
		}
	}

	public function allot() //线索分配
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
						$clue_id = decrypt($v,'CLUE');

						$result = D('CrmClue')->allotClue($this->_company_id,$member_id,$this->member_id,$clue_id,$this->_sms);

						if($result['status'] != 2)
						{
							$this->ajaxReturn($result);
						}
					}
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_CLUE_ASSIGNED')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('SELECT_USER_ASSIGNED')];
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$this->common->_empty();
		}
	}

	//线索转客户
	public function transform($id)
	{
		$clue_id = decrypt($id,'CLUE');

		$clue = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'clue_id'=>$clue_id,'isvalid'=>1])->field('member_id,status')->find();

		if(!$clue)
		{
			$this->common->_empty();

			die;
		}

		$detail = CrmgetCrmDetailList('clue',$clue_id,$this->_company_id);

		if(IS_POST)
		{
			if(!$clue['member_id'] || $clue['status'] == 2)
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('CLUE_STATE_WRONG')]);
			}

			$transform = I('post.transform');

			$result = D('CrmClue')->transformClue($clue_id,$clue,$detail,$transform,$this->_company_id,$this->member_id);

			$this->ajaxReturn($result);
		}
		else
		{
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

			$contacterData = [
				'name'  =>$detail['name'] ? $detail['name'] : '',
				'phone' =>$detail['phone'] ? $detail['phone'] : '',
				'email' =>$detail['email'] ? $detail['email'] : '',
				'wechat' =>$detail['wechat'] ? $detail['wechat'] : '',
				'qq' =>$detail['qq'] ? $detail['qq'] : '',
			];

			$form_description = getCrmLanguageData('form_description');

			$customerform = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer'])
				->order('orderby asc')
				->select();

			foreach($customerform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				if($v['form_type']=='region')
				{
					$form_id = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'form_name'=>$v['form_name'],'type'=>'clue'])->getField('form_id');

					$clue_detail = getCrmDbModel('clue_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$form_id,'clue_id'=>$clue_id])->find();

					if($clue_detail['form_content'])
					{
						$region_detail = explode(',',$clue_detail['form_content']);

						$customerData[$v['form_name']] = [];

						$customerData[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$customerData[$v['form_name']]['defaultProv'] = $region_detail[1];

						$customerData[$v['form_name']]['defaultCity'] = $region_detail[2];

						$customerData[$v['form_name']]['defaultArea'] = $region_detail[3];
					}
				}
			}

			$contacterform = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>'contacter'])
				->order('orderby asc')
				->select();

			foreach($contacterform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$this->assign('customerform',$customerform);

			$this->assign('contacterform',$contacterform);

			$this->assign('customerData',$customerData);

			$this->assign('contacterData',$contacterData);

			//客户列表
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

			$this->display();
		}
	}

	public function transfer() //线索转移
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
						$clue_id = decrypt($v,'CLUE');

						$result = D('CrmClue')->transferClue($this->_company_id,$member_id,$this->member_id,$clue_id,$this->_sms);

						if($result['status'] != 2)
						{
							$this->ajaxReturn($result);
						}
					}
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_CLUE_TRANSFER')];
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

	public function toPool() //放弃线索
	{
		if(IS_AJAX)
		{
			$ids = I('post.ids');

			$abandon_id = I('post.abandon_id');

			if(!$abandon_id)
			{
				$result = ['status'=>0,'msg'=>L('SELECT_REASON_GIVING_UP')];
			}
			else
			{
				if(count($ids) > 0)
				{
					foreach($ids as $k=>$v)
					{
						$clue_id = decrypt($v,'CLUE');

						$result = D('CrmClue')->clueToPool($this->_company_id,$this->member_id,$clue_id,$abandon_id);

						if($result['status'] != 2)
						{
							$this->ajaxReturn($result);
						}

					}

				}else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_CLUE_GIVE_UP')];
				}
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$this->common->_empty();
		}
	}

	public function to_export()
	{
		$tableHeader = session('ClueExcelData')['header'];

		$letter      = session('ClueExcelData')['letter'];

		$excelData   = session('ClueExcelData')['excelData'];

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

		session('ClueExcelData',$excel);

		$this->ajaxReturn(['msg'=>'success']);
	}

	public function export($action = '')
	{
		if(IS_AJAX)
        {
			$exportData = $tableHeader = [];

			if($action == 'index')
			{
				$export_field = C('EXPORT_CLUE_INDEX_FIELD');

				$export_arr = C('EXPORT_CLUE_INDEX_ARR');

				$where = session('clueExportWhere');

				$pagecount = 20;

				$data = D('CrmExport')->getDataList($where,$pagecount,'clue');

				if($data['msg'])
				{
					$this->ajaxReturn(['msg'=>$data['msg']]);die;
				}
				else
				{
					$clue = $data['data'];
				}

				$exportList = $data['exportList'];

				$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'clue',$exportList);

				$show_list = $export_list['show_list'];

				$tableHeader = $export_list['tableHeader'];

				$exportData = D('CrmExport')->getClueList($clue,$this->_company_id,$exportList,$show_list);
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

	public function export_pool($action = '')
	{
		if(IS_AJAX)
		{
			$exportData = $tableHeader = [];

			if($action == 'pool')
			{
				$export_field = C('EXPORT_CLUE_POOL_FIELD');

				$export_arr = C('EXPORT_CLUE_POOL_ARR');

				$where = session('cluePoolExportWhere');

				$pagecount = 20;

				$data = D('CrmExport')->getDataList($where,$pagecount,'clue');

				if($data['msg'])
				{
					$this->ajaxReturn(['msg'=>$data['msg']]);die;
				}
				else
				{
					$clue = $data['data'];
				}

				$exportList = $data['exportList'];

				$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'clue',$exportList);

				$show_list = $export_list['show_list'];

				$tableHeader = $export_list['tableHeader'];

				$exportData = D('CrmExport')->getClueList($clue,$this->_company_id,$exportList,$show_list);
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

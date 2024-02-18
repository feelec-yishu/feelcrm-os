<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

use Think\Aes;
use Think\Page;

class CustomerController extends BasicController
{
	protected $CustomerFields = ['member_id','customer_type','nextcontacttime','first_contact_id','is_locking','is_trade','customer_prefix','customer_no','from_type','originalId'];

	protected $FollowFields = ['cmncate_id','reply_id','nextcontacttime','content','contacter_id','customer_id'];

	protected $commentFields = ['follow_id','content'];

	protected $ticket_status = [];

	protected $first_status = [];

	protected $end_status = [];

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

		$this->assign('company',$this->_company);
	}

	public function index()
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		if($this->_crmsite['customerReseller'] == 1)
		{
			$field['customer_type'] = ['neq','agent'];
		}

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

		//创建人维度查看客户权限
		$CreaterViewCustomer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CreaterViewCustomer',$this->_member['role_id'],'crm');

		if($CreaterViewCustomer)
		{
			$field['_string'] = getCreaterViewSql($field['member_id']);

			unset($field['member_id']);

			$this->assign('isCreaterView',$CreaterViewCustomer);

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

		if(I('get.IuserCust')) $field['customer_id'] = unserialize(urldecode(I('get.IuserCust')));

		if(I('get.IisTrade')) $field['is_trade'] = I('get.IisTrade');

		if(I('get.IsourceName'))
		{
			if(I('get.IsourceName') != 'ANALYSISSOURCENAME')
			{
				$origin_customer = CrmgetDefineFormHighField($this->_company_id, 'customer', 'origin', I('get.IsourceName'));
			}
			else
			{
				$origin_customer = CrmgetDefineFormHighField($this->_company_id, 'customer', 'origin', '');
			}

			$field['customer_id'] = ['in',implode(',',$origin_customer)];
		}

		if(I('get.IisAdd') && I('get.Itime') && I('get.ImemberId'))
		{
			$field['creater_id'] = I('get.ImemberId');
		}

		if(I('get.IisDraw') && I('get.IdrawArr') && I('get.ImemberId'))
		{
			$field['customer_id'] = ['in',implode(',',unserialize(urldecode(I('get.IdrawArr'))))];
		}

		if(I('get.IisAllot') && I('get.IallotArr') && I('get.ImemberId'))
		{
			$field['customer_id'] = ['in',implode(',',unserialize(urldecode(I('get.IallotArr'))))];
		}

		if(I('get.IisTransfer') && I('get.ItransferArr') && I('get.ImemberId'))
		{
			$field['customer_id'] = ['in',implode(',',unserialize(urldecode(I('get.ItransferArr'))))];
		}

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword = unserialize(urldecode(I('get.highKeyword')));

			if(!$highKeyword)
			{
				$highKeyword = I('get.highKeyword');

				$_GET['highKeyword'] = urlencode(serialize(I('get.highKeyword')));
			}

			$customerHighKey = D('CrmHighKeyword')->customerHighKey($this->_company_id,$highKeyword,$memberRoleArr);

			if($customerHighKey['field'])
			{
				$field = array_merge($field,$customerHighKey['field']);
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
			$keywordField = CrmgetDefineFormField($this->_company_id,'customer','name,phone,email,weixin',$keyword);

			$contacterField = CrmgetDefineFormField($this->_company_id,'contacter','name,phone,email,wechat',$keyword);

			$keywordCondition['customer_id'] = $keywordField ? ['in',$keywordField] : '0';

			$contacterField = checkContacterSqlField($this->_company_id,$contacterField);

			$keywordCondition['first_contact_id'] = $contacterField ? ['in',$contacterField] : '-1';

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			$this->assign('keyword', $keyword);
		}

		//排序
		$getSortBy = D('CrmHighKeyword')->getSortBy();

		$order = $getSortBy['order'];

		$sort_by = $getSortBy['sort_by'];

		$this->assign('sort_by',$sort_by);

		//var_dump(I('get.ImemberRole'));die;
		$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'member_id'=>$memberRoleArr])->field('name,member_id,group_id')->select();

		$nowPage = I('get.p');

		$count = getCrmDbModel('customer')->where($field)->count();

		$Page = new Page($count, 20);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$this->assign('pageCount', $count);

		$customer = getCrmDbModel('customer')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		$show_list = CrmgetShowListField('customer',$this->_company_id); //客户列表显示字段

		$export_field = C('EXPORT_CUSTOMER_INDEX_FIELD');

		$export_arr = C('EXPORT_CUSTOMER_INDEX_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'customer');

		//除备注外全部客户自定义字段
		$allField = getCrmDbModel('define_form')
			->where(['type'=>'customer','company_id'=>$this->_company_id,'closed'=>0,'form_name'=>['neq','remark']])
			->count();

		foreach($customer as $key=>&$val)
		{
			$haveField = 0;

			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

			$val['member_name'] = $thisMember['name'];

			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

			$val['create_name'] = $createMember['name'];

			$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id);

			$haveFieldArr = $val['detail'];

			unset($haveFieldArr['remark']);

			$haveField = count(array_filter($haveFieldArr));

			$percent = round($haveField/$allField*100);

			if($percent >= 50)
			{
				$val['percent'] = '<span class="green6">'.$percent.'%</span>';
			}
			else
			{
				$val['percent'] = '<span class="red5">'.$percent.'%</span>';
			}

			// $contacter_detail = CrmgetCrmDetailList('contacter',$val['first_contact_id'],$this->_company_id,'name,phone');

			// $val['contacter_name'] = $contacter_detail['name'];

			// $val['contacter_phone'] = $contacter_detail['phone'];

		}

		$role = D('Role')->where(['company_id'=>$this->_company_id])->field('role_id,role_name')->fetchAll();

		$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

		$memberCount =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->count();

		$memberPage = new Page($memberCount, 10);

		$list =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->limit($memberPage->firstRow, $memberPage->listRows)->select();

		if(strlen($memberPage->show()) > 16) $this->assign('memberPage', $memberPage->show()); // 赋值分页输出

		$this->assign('roleList',$role);

		$this->assign('groupList',$group);

		$this->assign('member',$list);

		$company = M('company')->where(['company_id'=>$this->_company_id])->find();

		if($company['ticket_auth'] == 10)
		{
			$this->assign('isTicketAuth','10');
		}

		$form_description = getCrmLanguageData('form_description');

		//查询筛选项
		$filterlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_name'=>['in','customer_grade,importance']])
			->order('orderby asc')
			->select();

		foreach($filterlist as $k=>&$v)
		{
			$v['option'] = explode('|',$v['form_option']);
		}

		$this->assign('filterlist',$filterlist);

		$defineformlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_type'=>['neq','textarea']])
			->order('orderby asc')
			->select();

		foreach($defineformlist as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		$abandon_field = getCrmLanguageData('abandon_name');

		$abandon = getCrmDbModel('abandon')->where(['company_id'=>$this->_company_id,'closed'=>0])->field('abandon_id,'.$abandon_field)->select();

		//删除客户权限
		$delete_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/delete',$this->_member['role_id'],'crm');

		//生成工单账号权限
		$createFeeldesk_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/createFeeldesk',$this->_member['role_id'],'crm');

		//转移权限
		$transfer_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/transfer',$this->_member['role_id'],'crm');

		//放入客户池权限
		$toPool_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/toPool',$this->_member['role_id'],'crm');

		//导出客户权限
		$export_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/export',$this->_member['role_id'],'crm');

		//导入客户权限
		$import_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/import',$this->_member['role_id'],'crm');

		if($import_customer)
		{
			$importTemp = getCrmDbModel('temp_file')->where(['company_id'=>$this->_company_id,'type'=>'customer'])->getField('file_link');

			$this->assign('importTemp',$importTemp);
		}

		$this->assign('isDelCustomerAuth',$delete_customer_id);

		$this->assign('iscreateFeeldeskAuth',$createFeeldesk_customer);

		$this->assign('istransferCustomerAuth',$transfer_customer);

		$this->assign('istoPoolCustomerAuth',$toPool_customer);

		$this->assign('isExportCustomerAuth',$export_customer);

		$this->assign('isImportCustomerAuth',$import_customer);

		$this->assign('defineformlist',$defineformlist);

		$this->assign('regionJson',C('regionJson'));

		$this->assign('customer',$customer);

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

		$this->assign('abandons',$abandon);

		$this->assign('formList',$show_list['form_list']);

		$this->assign('export_list',$export_list);

		$this->assign('nowPage',$nowPage);

		session('customerExportWhere',$field);

		session('ExportOrder',$order);

		$this->display();

	}

	public function agent()
	{
		if($this->_crmsite['customerReseller'] != 1)
		{
			$this->common->_empty();
		}

		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		$field['customer_type'] = 'agent';

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

		//创建人维度查看客户权限
		$CreaterViewCustomer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CreaterViewCustomer',$this->_member['role_id'],'crm');

		if($CreaterViewCustomer)
		{
			$field['_string'] = getCreaterViewSql($field['member_id']);

			unset($field['member_id']);

			$this->assign('isCreaterView',$CreaterViewCustomer);

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

		if(I('get.IuserCust')) $field['customer_id'] = unserialize(urldecode(I('get.IuserCust')));

		if(I('get.IisTrade')) $field['is_trade'] = I('get.IisTrade');

		if(I('get.IsourceName'))
		{
			if(I('get.IsourceName') != 'ANALYSISSOURCENAME')
			{
				$origin_customer = CrmgetDefineFormHighField($this->_company_id, 'customer', 'origin', I('get.IsourceName'));
			}
			else
			{
				$origin_customer = CrmgetDefineFormHighField($this->_company_id, 'customer', 'origin', '');
			}

			$field['customer_id'] = ['in',implode(',',$origin_customer)];
		}

		if(I('get.IisAdd') && I('get.Itime') && I('get.ImemberId'))
		{
			$field['creater_id'] = I('get.ImemberId');
		}

		if(I('get.IisDraw') && I('get.IdrawArr') && I('get.ImemberId'))
		{
			$field['customer_id'] = ['in',implode(',',unserialize(urldecode(I('get.IdrawArr'))))];
		}

		if(I('get.IisAllot') && I('get.IallotArr') && I('get.ImemberId'))
		{
			$field['customer_id'] = ['in',implode(',',unserialize(urldecode(I('get.IallotArr'))))];
		}

		if(I('get.IisTransfer') && I('get.ItransferArr') && I('get.ImemberId'))
		{
			$field['customer_id'] = ['in',implode(',',unserialize(urldecode(I('get.ItransferArr'))))];
		}

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword = unserialize(urldecode(I('get.highKeyword')));

			if(!$highKeyword)
			{
				$highKeyword = I('get.highKeyword');

				$_GET['highKeyword'] = urlencode(serialize(I('get.highKeyword')));
			}

			$customerHighKey = D('CrmHighKeyword')->customerHighKey($this->_company_id,$highKeyword,$memberRoleArr);

			if($customerHighKey['field'])
			{
				$field = array_merge($field,$customerHighKey['field']);
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
			$keywordField = CrmgetDefineFormField($this->_company_id,'customer','name,phone,email,weixin',$keyword);

			$contacterField = CrmgetDefineFormField($this->_company_id,'contacter','name,phone,email,wechat',$keyword);

			$keywordCondition['customer_id'] = $keywordField ? ['in',$keywordField] : '0';

			$contacterField = checkContacterSqlField($this->_company_id,$contacterField);

			$keywordCondition['first_contact_id'] = $contacterField ? ['in',$contacterField] : '-1';

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			$this->assign('keyword', $keyword);
		}

		//排序
		$getSortBy = D('CrmHighKeyword')->getSortBy();

		$order = $getSortBy['order'];

		$sort_by = $getSortBy['sort_by'];

		$this->assign('sort_by',$sort_by);

		//var_dump(I('get.ImemberRole'));die;
		$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'member_id'=>$memberRoleArr])->field('name,member_id,group_id')->select();

		$nowPage = I('get.p');

		$count = getCrmDbModel('customer')->where($field)->count();

		$Page = new Page($count, 20);

		$this->assign('pageCount', $count);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$customer = getCrmDbModel('customer')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		$show_list = CrmgetShowListField('customer',$this->_company_id); //客户列表显示字段

		$export_field = C('EXPORT_CUSTOMER_INDEX_FIELD');

		$export_arr = C('EXPORT_CUSTOMER_INDEX_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'customer');

		//除备注外全部客户自定义字段
		$allField = getCrmDbModel('define_form')
			->where(['type'=>'customer','company_id'=>$this->_company_id,'closed'=>0,'form_name'=>['neq','remark']])
			->count();

		foreach($customer as $key=>&$val)
		{
			$haveField = 0;

			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

			$val['member_name'] = $thisMember['name'];

			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

			$val['create_name'] = $createMember['name'];

			$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id);

			$haveFieldArr = $val['detail'];

			unset($haveFieldArr['remark']);

			$haveField = count(array_filter($haveFieldArr));

			$percent = round($haveField/$allField*100);

			if($percent >= 50)
			{
				$val['percent'] = '<span class="green6">'.$percent.'%</span>';
			}
			else
			{
				$val['percent'] = '<span class="red5">'.$percent.'%</span>';
			}

			// $contacter_detail = CrmgetCrmDetailList('contacter',$val['first_contact_id'],$this->_company_id,'name,phone');

			// $val['contacter_name'] = $contacter_detail['name'];

			// $val['contacter_phone'] = $contacter_detail['phone'];

		}

		$role = D('Role')->where(['company_id'=>$this->_company_id])->field('role_id,role_name')->fetchAll();

		$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

		$memberCount =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->count();

		$memberPage = new Page($memberCount, 10);

		$list =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->limit($memberPage->firstRow, $memberPage->listRows)->select();

		if(strlen($memberPage->show()) > 16) $this->assign('memberPage', $memberPage->show()); // 赋值分页输出

		$this->assign('roleList',$role);

		$this->assign('groupList',$group);

		$this->assign('member',$list);

		$company = M('company')->where(['company_id'=>$this->_company_id])->find();

		if($company['ticket_auth'] == 10)
		{
			$this->assign('isTicketAuth','10');
		}

		$form_description = getCrmLanguageData('form_description');

		//查询筛选项
		$filterlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_name'=>['in','customer_grade,importance']])
			->order('orderby asc')
			->select();

		foreach($filterlist as $k=>&$v)
		{
			$v['option'] = explode('|',$v['form_option']);
		}

		$this->assign('filterlist',$filterlist);

		$defineformlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_type'=>['neq','textarea']])
			->order('orderby asc')
			->select();

		foreach($defineformlist as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		$abandon_field = getCrmLanguageData('abandon_name');

		$abandon = getCrmDbModel('abandon')->where(['company_id'=>$this->_company_id,'closed'=>0])->field('abandon_id,'.$abandon_field)->select();

		//删除客户权限
		$delete_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/delete',$this->_member['role_id'],'crm');

		//生成工单账号权限
		$createFeeldesk_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/createFeeldesk',$this->_member['role_id'],'crm');

		//转移权限
		$transfer_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/transfer',$this->_member['role_id'],'crm');

		//放入客户池权限
		$toPool_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/toPool',$this->_member['role_id'],'crm');

		//导出客户权限
		$export_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/export',$this->_member['role_id'],'crm');

		//导入客户权限
		$import_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/import',$this->_member['role_id'],'crm');

		if($import_customer)
		{
			$importTemp = getCrmDbModel('temp_file')->where(['company_id'=>$this->_company_id,'type'=>'customer'])->getField('file_link');

			$this->assign('importTemp',$importTemp);
		}

		$this->assign('isDelCustomerAuth',$delete_customer_id);

		$this->assign('iscreateFeeldeskAuth',$createFeeldesk_customer);

		$this->assign('istransferCustomerAuth',$transfer_customer);

		$this->assign('istoPoolCustomerAuth',$toPool_customer);

		$this->assign('isExportCustomerAuth',$export_customer);

		$this->assign('isImportCustomerAuth',$import_customer);

		$this->assign('defineformlist',$defineformlist);

		$this->assign('regionJson',C('regionJson'));

		$this->assign('customer',$customer);

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

		$this->assign('abandons',$abandon);

		$this->assign('formList',$show_list['form_list']);

		$this->assign('export_list',$export_list);

		$this->assign('nowPage',$nowPage);

		session('customerExportWhere',$field);

		session('ExportOrder',$order);

		$this->display('index');

	}

	public function pool()
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = '1';

		$field['member_id'] = '';

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,'','pool');

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

			//$customerHighKey = $this->customerHighKey($highKeyword,'','pool');

			$customerHighKey = D('CrmHighKeyword')->customerHighKey($this->_company_id,$highKeyword,'','pool');

			if($customerHighKey['field'])
			{
				$field = array_merge($field,$customerHighKey['field']);
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
			$keywordField = CrmgetDefineFormField($this->_company_id,'customer','name,phone,email,weixin',$keyword);

			$contacterField = CrmgetDefineFormField($this->_company_id,'contacter','name,phone,email,wechat',$keyword);

			$keywordCondition['customer_id'] = $keywordField ? ['in',$keywordField] : '0';

			$contacterField = checkContacterSqlField($this->_company_id,$contacterField);

			$keywordCondition['first_contact_id'] = $contacterField ? ['in',$contacterField] : '-1';

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

		$count = getCrmDbModel('customer')->where($field)->count();

		$Page = new Page($count, 20);

		$this->assign('pageCount', $count);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$customer = getCrmDbModel('customer')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		$show_list = CrmgetShowListField('customer',$this->_company_id); //客户列表显示字段

		$export_field = C('EXPORT_CUSTOMER_POOL_FIELD');

		$export_arr = C('EXPORT_CUSTOMER_POOL_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'customer');

		$allField = getCrmDbModel('define_form')
			->where(['type'=>'customer','company_id'=>$this->_company_id,'closed'=>0,'form_name'=>['neq','remark']])
			->count();

		foreach($customer as $key=>&$val)
		{
			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

			$val['create_name'] = $createMember['name'];

			$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id);

			$haveFieldArr = $val['detail'];

			unset($haveFieldArr['remark']);

			$haveField = count(array_filter($haveFieldArr));

			$percent = round($haveField/$allField*100);

			if($percent >= 50)
			{
				$val['percent'] = '<span class="green6">'.$percent.'%</span>';
			}
			else
			{
				$val['percent'] = '<span class="red5">'.$percent.'%</span>';
			}

			// $contacter_detail = CrmgetCrmDetailList('contacter',$val['first_contact_id'],$this->_company_id,'name,phone');

			// $val['contacter_name'] = $contacter_detail['name'];

			// $val['contacter_phone'] = $contacter_detail['phone'];

		}

		$role = D('Role')->where(['company_id'=>$this->_company_id])->field('role_id,role_name')->fetchAll();

		$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

		$memberCount =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->count();

		$memberPage = new Page($memberCount, 10);

		$list =  M('Member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->limit($memberPage->firstRow, $memberPage->listRows)->select();

		if(strlen($memberPage->show()) > 16) $this->assign('memberPage', $memberPage->show()); // 赋值分页输出

		$this->assign('roleList',$role);

		$this->assign('groupList',$group);

		$this->assign('member',$list);

		$form_description = getCrmLanguageData('form_description');

		//查询筛选项
		$filterlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_name'=>['in','customer_grade,importance']])
			->order('orderby asc')
			->select();

		foreach($filterlist as $k=>&$v)
		{
			$v['option'] = explode('|',$v['form_option']);
		}

		$this->assign('filterlist',$filterlist);

		$defineformlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_type'=>['neq','textarea']])
			->order('orderby asc')
			->select();

		foreach($defineformlist as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		$abandon_field = getCrmLanguageData('abandon_name');

		$abandon = getCrmDbModel('abandon')->where(['company_id'=>$this->_company_id,'closed'=>0])->field('abandon_id,'.$abandon_field)->select();

		$this->assign('abandons',$abandon);

		$isAllViewAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'poolAll',$this->_member['role_id'],'crm');

		$isGroupViewAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'poolGroup',$this->_member['role_id'],'crm');

		$isOwnViewAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'poolOwn',$this->_member['role_id'],'crm');

		$this->assign('isAllViewAuth',$isAllViewAuth);

		$this->assign('isGroupViewAuth',$isGroupViewAuth);

		$this->assign('isOwnViewAuth',$isOwnViewAuth);

		$draw_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/draw',$this->_member['role_id'],'crm'); //领取客户权限

		$allot_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/allot',$this->_member['role_id'],'crm'); //分配客户权限

		$delete_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/delete',$this->_member['role_id'],'crm'); //删除客户权限

		$export_pool = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/export_pool',$this->_member['role_id'],'crm'); //导出客户权限

		$import_pool = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/import_pool',$this->_member['role_id'],'crm'); //导入客户权限

		if($import_pool)
		{
			$importTemp = getCrmDbModel('temp_file')->where(['company_id'=>$this->_company_id,'type'=>'customerPool'])->getField('file_link');

			$this->assign('importTemp',$importTemp);
		}

		$this->assign('isExportPoolAuth',$export_pool);

		$this->assign('isImportPoolAuth',$import_pool);

		$this->assign('isDrawCustomerAuth',$draw_customer_id);

		$this->assign('isAllotCustomerAuth',$allot_customer_id);

		$this->assign('isDelCustomerAuth',$delete_customer_id);

        foreach($export_list as $key=>&$val)
        {
            if($val['define'] == 'is_examine')
            {
                unset($export_list[$key]);
            }
        }

		$this->assign('defineformlist',$defineformlist);

		$this->assign('regionJson',C('regionJson'));

		$this->assign('customer',$customer);

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

		session('poolExportWhere',$field);

		session('ExportOrder',$order);

		$this->display();

	}

	public function detail($id,$detailtype='',$detail_source = 'crm')
	{
		$customer_id = decrypt($id,'CUSTOMER');

		if($detailtype)
		{
			$detailtype = decrypt($detailtype,'CUSTOMER');

			$this->assign('detailtype',$detailtype);
		}
		else
		{
			$detailtype = 'index';

			$this->assign('detailtype','index');
		}

		$this->assign('detail_source',$detail_source);

		if(getCrmDbModel('customer_give')->where(['company_id'=>$this->_company_id,'target_id'=>$this->member_id,'customer_id'=>$customer_id,'is_looked'=>0])->getField('id'))
		{
			getCrmDbModel('customer_give')->where(['company_id'=>$this->_company_id,'target_id'=>$this->member_id,'customer_id'=>$customer_id,'is_looked'=>0])->save(['is_looked'=>1]);
		}

		$isDetailAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CustDetail',$this->_member['role_id'],'crm');//客户详情权限

		$isFollowAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CustFollow',$this->_member['role_id'],'crm');//客户联系记录权限

		$isOpportunityAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CustOpportunity',$this->_member['role_id'],'crm');//客户商机权限

		$isContractAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CustContract',$this->_member['role_id'],'crm');//客户合同权限

		$isShipmentAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CustShipment',$this->_member['role_id'],'crm');//客户出货权限

		$isContacterAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CustContacter',$this->_member['role_id'],'crm');//客户联系人权限

		$this->assign('isDetailAuthView',$isDetailAuthView);

		$this->assign('isFollowAuthView',$isFollowAuthView);

		$this->assign('isOpportunityAuthView',$isOpportunityAuthView);

		$this->assign('isContractAuthView',$isContractAuthView);

		$this->assign('isShipmentAuthView',$isShipmentAuthView);

		$this->assign('isContacterAuthView',$isContacterAuthView);

		$customer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id])->find();

		if(!$customer)
		{
			$this->common->_empty();

			die;
		}

		$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$customer['member_id']])->field('member_id,account,name,group_id')->find();

		$customer['member_name'] = $thisMember['name'];

		$customer['group_id'] = $thisMember['group_id'];

		$allField = getCrmDbModel('define_form')
			->where(['type'=>'customer','company_id'=>$this->_company_id,'closed'=>0,'form_name'=>['neq','remark']])
			->count();

		$customer['detail'] = CrmgetCrmDetailList('customer',$customer_id,$this->_company_id);

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

		$createMember = M('Member')->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 1, 'member_id' => $customer['creater_id']])->field('member_id,account,name')->find();

		$customer['creater_name'] = $createMember['name'];

		$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

		if ($customer['creater_id'] == '-5') {
			$customer_company_id = M('member')->where(['account' => $customer['detail']['phone'], 'closed' => 0, 'is_first' => 2])->getField('company_id');

			if (M('company_audit')->where(['company_id' => $customer_company_id, 'activity' => 2])->find()) {
				$CompanyClosed = 0;
			} else {
				$CompanyClosed = 1;
			}

			$this->assign('CompanyClosed', $CompanyClosed);
		}

		//联系人总数量
		if($isContacterAuthView)
		{
			$fieldcontacter = ['company_id'=>$this->_company_id,'isvalid'=>'1','customer_id'=>$customer_id];

			$contacterCount = getCrmDbModel('contacter')->where($fieldcontacter)->count();

			$this->assign('contacterCount',$contacterCount);
		}

		//商机数量
		if($isOpportunityAuthView)
		{
			$fieldopportunity = ['company_id'=>$this->_company_id,'isvalid'=>'1','customer_id'=>$customer_id];

			$opportunityCount = getCrmDbModel('opportunity')->where($fieldopportunity)->count();

			$this->assign('opportunityCount',$opportunityCount);
		}

		//合同数量
		if($isContractAuthView)
		{
			$contractfield = ['company_id'=>$this->_company_id,'isvalid'=>'1','customer_id'=>$customer_id];

			$contractCount = getCrmDbModel('contract')->where($contractfield)->count();

			$this->assign('contractCount',$contractCount);
		}

		//客户详情
		if(!$detailtype || in_array($detailtype,['index','follow','analysis']))
		{
			if(!$isDetailAuthView)
			{
				$this->common->_empty();

				die;
			}

			if($detail_source == 'ticket' && !$isDeskDetailAuthView)
			{
				$this->common->_empty();

				die;
			}

			if(isset($_GET['request']) && $_GET['request'] == 'crmlog')
			{
				$crmlog = D('CrmLog')->getCrmLog('customer', $customer_id, $this->_company_id);

				$result = ['data'=>$crmlog['crmlog'],'type'=>encrypt('index','CUSTOMER'),'pages'=>ceil($crmlog['count']/10)];

				$this->ajaxReturn($result);
			}
			else
			{
				$memberCount = M('Member')->where(['company_id' => $this->_company_id, 'type' => 1,'closed'=>0,'feelec_opened'=>10])->count();

				$memberPage = new Page($memberCount, 10);

				$list = M('Member')->where(['company_id' => $this->_company_id, 'type' => 1,'closed'=>0,'feelec_opened'=>10])->limit($memberPage->firstRow, $memberPage->listRows)->select();

				if (strlen($memberPage->show()) > 16) $this->assign('memberPage', $memberPage->show()); // 赋值分页输出

				$this->assign('member', $list);

				$form_description = getCrmLanguageData('form_description');

				$customerform = getCrmDbModel('define_form')
					->field(['*',$form_description])
					->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 'customer', 'form_type' => ['neq', 'textarea']])
					->order('orderby asc')
					->select();

				foreach ($customerform as $key => &$val) {
					if (in_array($val['form_type'], ['radio','select','checkbox','select_text'])) {
						$val['option'] = explode('|', $val['form_option']);
					}

					if ($val['form_type'] == 'region') {
						$customer_detail = getCrmDbModel('customer_detail')->where(['company_id' => $this->_company_id, 'form_id' => $val['form_id'], 'customer_id' => $customer_id])->find();

						if ($customer_detail['form_content']) {
							$region_detail = explode(',', $customer_detail['form_content']);

							$customer[$val['form_name']]['defaultCountry'] = $region_detail[0];

							$customer[$val['form_name']]['defaultProv'] = $region_detail[1];

							$customer[$val['form_name']]['defaultCity'] = $region_detail[2];

							$customer[$val['form_name']]['defaultArea'] = $region_detail[3];

						}
					}
				}

				$customerform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 'customer', 'form_type' => ['eq', 'textarea']])->order('orderby asc')->select();

				$abandon_field = getCrmLanguageData('abandon_name');

				$abandon = getCrmDbModel('abandon')->where(['company_id' => $this->_company_id, 'closed' => 0])
					->field('abandon_id,'.$abandon_field)
					->select();

				$abandon_log = getCrmDbModel('customer_abandon')->where(['company_id' => $this->_company_id, 'customer_id' => $customer_id])->select();

				foreach ($abandon_log as $abk => &$abv)
				{
					$abv['abandon_name'] = getCrmDbModel('abandon')
						->where(['company_id' => $this->_company_id, 'abandon_id' => $abv['abandon_id']])
						->getField($abandon_field);

					$abv['member_name'] = M('member')->where(['company_id' => $this->_company_id, 'type' => 1, 'member_id' => $abv['member_id']])->getField('name');

					$abv['operator_name'] = M('member')->where(['company_id' => $this->_company_id, 'type' => 1, 'member_id' => $abv['operator_id']])->getField('name');
				}

				$lose_field = getCrmLanguageData('lose_name');

				$lose = getCrmDbModel('lose')->where(['company_id' => $this->_company_id, 'closed' => 0])->field('lose_id,'.$lose_field)->select();

				$lose_log = getCrmDbModel('customer_lose')->where(['company_id' => $this->_company_id, 'customer_id' => $customer_id])->select();

				foreach ($lose_log as $losek => &$losev) {
					$losev['lose_name'] = getCrmDbModel('lose')->where(['company_id' => $this->_company_id, 'lose_id' => $losev['lose_id']])->getField($lose_field);

					$competitor_name = CrmgetCrmDetailList('competitor', $losev['competitor_id'], $this->_company_id, 'name');

					$losev['competitor_name'] = $competitor_name['name'];

					$losev['member_name'] = M('member')->where(['company_id' => $this->_company_id, 'type' => 1, 'member_id' => $losev['member_id']])->getField('name');

					$losev['operator_name'] = M('member')->where(['company_id' => $this->_company_id, 'type' => 1, 'member_id' => $losev['operator_id']])->getField('name');

					if($losev['opportunity_id'])
					{
						$opportunity_name = CrmgetCrmDetailList('opportunity',$losev['opportunity_id'],$this->_company_id,'name');

						$losev['opportunity_name'] = $opportunity_name['name'];
					}
				}

				//联系记录
				if ($isFollowAuthView || ($detail_source == 'ticket' && $isDeskFollowAuthView)) {

					$cmncate_field = getCrmLanguageData('cmncate_name');

					$cmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['company_id' => $this->_company_id, 'closed' => 0])->select();

					//客户跟进记录
					$follow_customer = getCrmDbModel('followup')->where(['customer_id' => $customer_id, 'company_id' => $this->_company_id, 'isvalid' => 1])->order('createtime desc')->select();

					//商机跟进记录
					if($fieldopportunity)
					{
						$opportunity = getCrmDbModel('opportunity')->where($fieldopportunity)->field('opportunity_id')->select();

						$opportunity_arr = array_column($opportunity,'opportunity_id');
					}

					$follow_opportunity = $opportunity_arr ? getCrmDbModel('followup')->where(['opportunity_id' => ['in',$opportunity_arr], 'company_id' => $this->_company_id, 'isvalid' => 1])->order('createtime desc')->select() : [];

					$follow = array_merge($follow_customer,$follow_opportunity);

					array_multisort(array_column($follow,'createtime'),SORT_DESC,$follow);

					$cmncate_field = getCrmLanguageData('cmncate_name');

					foreach ($follow as $k3 => &$v3)
					{
						$member = M('member')->where(['company_id' => $this->_company_id, 'member_id' => $v3['member_id'], 'type' => 1])->field('name,face')->find();

						$v3['member_name'] = $member['name'];

						$v3['member_face'] = $member['face'];

						if ($v3['cmncate_id'])
						{
							$followCmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['cmncate_id' => $v3['cmncate_id'], 'company_id' => $this->_company_id])->find();

							$v3['cmncate_name'] = $followCmncate['cmncate_name'];
						}

						if ($v3['contacter_id']) {
							$v3['contacter_detail'] = CrmgetCrmDetailList('contacter', $v3['contacter_id'], $this->_company_id, 'name');
						}

						$v3['followComment'] = getCrmDbModel('follow_comment')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'isvalid' => 1])->order('createtime desc')->select();

						$uploadFiles = getCrmDbModel('upload_file')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'file_form' => 'follow'])->select();

						$v3['createFiles'] = $uploadFiles;

						//重新赋值联系记录数组
						if($v3['opportunity_id'] > 0)
						{
							$opportunity_follow[] = $v3;
						}
						else
						{
							$customer_follow[] = $v3;
						}
					}

					$this->assign('customer_follow',$customer_follow);

					$this->assign('opportunity_follow',$opportunity_follow);

					//线索跟进记录
					$clue = getCrmDbModel('clue')->where(['customer_id' => $customer_id, 'company_id' => $this->_company_id, 'isvalid' => 1])->field('clue_id')->select();

					$clue_arr = array_column($clue,'clue_id');

					$clue_follow =  $clue_arr ? getCrmDbModel('followup')->where(['clue_id' => ['in',$clue_arr], 'company_id' => $this->_company_id, 'isvalid' => 1])->order('createtime desc')->select() : [];

					foreach ($clue_follow as $k3 => &$v3)
					{
						$member = M('member')->where(['company_id' => $this->_company_id, 'member_id' => $v3['member_id'], 'type' => 1])->field('name,face')->find();

						$v3['member_name'] = $member['name'];

						$v3['member_face'] = $member['face'];

						if ($v3['cmncate_id'])
						{
							$followCmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['cmncate_id' => $v3['cmncate_id'], 'company_id' => $this->_company_id])->find();

							$v3['cmncate_name'] = $followCmncate['cmncate_name'];
						}

						$v3['followComment'] = getCrmDbModel('follow_comment')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'isvalid' => 1])->order('createtime desc')->select();

						$uploadFiles = getCrmDbModel('upload_file')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'file_form' => 'follow'])->select();

						$v3['createFiles'] = $uploadFiles;
					}

					$this->assign('clue_follow',$clue_follow);

					$contacter = getCrmDbModel('contacter')->where($fieldcontacter)->select();

					foreach ($contacter as $k4 => &$v4) {
						$v4['detail'] = CrmgetCrmDetailList('contacter', $v4['contacter_id'], $this->_company_id);
					}

					$members = D('Member')->where(['company_id' => $this->_company_id, 'type' => 1,'closed'=>0])->field('company_id,member_id,group_id,account,name,mobile,type,role_id,nickname,face,closed')->fetchAll();

					$commentFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/commentFollow', $this->_member['role_id'], 'crm');    //联系记录评论权限

					$this->assign('isCommentFollowAuth', $commentFollow_id);

					$this->assign('members', $members);

					$this->assign('contacter', $contacter);

					$this->assign('follow', $follow);

					$this->assign('cmncate', $cmncate);
				}

				if($isFollowAuthView || $isContractAuthView)
				{
					$file_customer = $file_opportunity = $file_clue = $file_contract = [];

					if($isFollowAuthView)
					{
						//客户附件
						$file_customer = getCrmDbModel('upload_file')->where(['customer_id' => $customer_id, 'company_id' => $this->_company_id])->order('create_time desc')->select();

						//商机附件
						$file_opportunity = $opportunity_arr ? getCrmDbModel('upload_file')->where(['opportunity_id' => ['in',$opportunity_arr], 'company_id' => $this->_company_id])->order('create_time desc')->select() : [];

						//线索附件
						$file_clue =  $clue_arr ? getCrmDbModel('upload_file')->where(['clue_id' => ['in',$clue_arr], 'company_id' => $this->_company_id])->order('create_time desc')->select() : [];
					}

					if($isContractAuthView)
					{
						if($contractfield)
						{
							$contract = getCrmDbModel('contract')->where($contractfield)->field('contract_id')->select();

							$contract_arr = array_column($contract,'contract_id');
						}

						//合同附件
						$file_contract =  $contract_arr ? getCrmDbModel('upload_file')->where(['contract_id' => ['in',$contract_arr], 'company_id' => $this->_company_id])->order('create_time desc')->select() : [];
					}

					$files = array_merge($file_customer,$file_opportunity,$file_clue,$file_contract);

					array_multisort(array_column($files,'create_time'),SORT_DESC,$files);

					$this->assign('file_customer', $file_customer);

					$this->assign('file_opportunity', $file_opportunity);

					$this->assign('file_clue', $file_clue);

					$this->assign('file_contract', $file_contract);

					$this->assign('files', $files);
				}

				$this->assign('customerform', $customerform);

				$this->assign('customerform2', $customerform2);

				$edit_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/edit', $this->_member['role_id'], 'crm'); //修改客户权限

				$delete_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/delete', $this->_member['role_id'], 'crm'); //删除客户权限

				$createFeeldesk_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/createFeeldesk', $this->_member['role_id'], 'crm'); //生成工单账号权限

				$transfer_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/transfer', $this->_member['role_id'], 'crm'); //转移权限

				$toPool_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/toPool', $this->_member['role_id'], 'crm'); //放弃客户权限

				$draw_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/draw', $this->_member['role_id'], 'crm'); //领取客户权限

				$allot_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'customer/allot', $this->_member['role_id'], 'crm'); //分配客户权限

				$updateIsTrade = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'], 'UpdateIsTrade', $this->_member['role_id'], 'crm'); //修改成交状态权限

				$this->assign('isEditCustomerAuth', $edit_customer_id);

				$this->assign('isDrawCustomerAuth', $draw_customer_id);

				$this->assign('isAllotCustomerAuth', $allot_customer_id);

				$this->assign('isDelCustomerAuth', $delete_customer_id);

				$this->assign('iscreateFeeldeskAuth', $createFeeldesk_customer);

				$this->assign('istransferCustomerAuth', $transfer_customer);

				$this->assign('istoPoolCustomerAuth', $toPool_customer);

				$this->assign('isUpdateTradeAuth', $updateIsTrade);

				$this->assign('abandons', $abandon);

				$this->assign('abandon_log', $abandon_log);

				$this->assign('loses', $lose);

				$this->assign('lose_log', $lose_log);
			}
		}

		$this->assign('customer',$customer);

		$this->assign('groupList',$group);

		//联系人
		if($detailtype == 'contacter')
		{
			if(!$isContacterAuthView)
			{
				$this->common->_empty();

				die;
			}

			if($detail_source == 'ticket' && !$isDeskContacterAuthView)
			{
				$this->common->_empty();

				die;
			}

			$Page = new Page($contacterCount, 20);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$contacter = getCrmDbModel('contacter')->where($fieldcontacter)->limit($Page->firstRow, $Page->listRows)->select();

			foreach($contacter as $k4=>&$v4)
			{
				$v4['detail'] = CrmgetCrmDetailList('contacter',$v4['contacter_id'],$this->_company_id);
			}

			$show_list = CrmgetShowListField('contacter',$this->_company_id); //列表显示字段

			$this->assign('formList',$show_list['form_list']);

			$this->assign('contacter',$contacter);
		}

		//商机
		if($detailtype == 'opportunity')
		{
			if(!$isOpportunityAuthView)
			{
				$this->common->_empty();

				die;
			}

			$Page = new Page($opportunityCount, 20);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$opportunity = getCrmDbModel('opportunity')->where($fieldopportunity)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

			foreach($opportunity as $k4=>&$v4)
			{
				$v4['detail'] = CrmgetCrmDetailList('opportunity',$v4['opportunity_id'],$this->_company_id);

				$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$v4['member_id']])->field('member_id,account,name')->find();

				$v4['member_name'] = $thisMember['name'];

				$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$v4['creater_id']])->field('member_id,account,name')->find();

				$v4['create_name'] = $createMember['name'];
			}

			$show_list = CrmgetShowListField('opportunity',$this->_company_id); //列表显示字段

			$this->assign('formList',$show_list['form_list']);

			$this->assign('opportunity',$opportunity);
		}

		//合同
		if($detailtype == 'contract')
		{
			if(!$isContractAuthView)
			{
				$this->common->_empty();

				die;
			}

			if($detail_source == 'ticket' && !$isDeskContractAuthView)
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

		//进销存
		if($detailtype == 'shipment')
		{
			if(!$isShipmentAuthView)
			{
				$this->common->_empty();

				die;
			}

			if($detail_source == 'ticket' && !$isDeskShipmentAuthView)
			{
				$this->common->_empty();

				die;
			}

			$shipmentfield = ['company_id'=>$this->_company_id,'isvalid'=>'1','customer_id'=>$customer_id];

			$count = getCrmDbModel('shipment')->where($shipmentfield)->count();

			$Page = new Page($count, 10);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$shipment = getCrmDbModel('shipment')->where($shipmentfield)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

			foreach($shipment as $k6=>&$v6)
			{
				$v6['product'] = CrmgetCrmDetailList('product',$v6['product_id'],$this->_company_id,'name');

				$v6['detail'] = CrmgetCrmDetailList('shipment',$v6['shipment_id'],$this->_company_id);
			}

			$show_list = CrmgetShowListField('shipment',$this->_company_id); //列表显示字段

			$contractProduct = getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id])->select();

			$productStock = [];//产品库存

			foreach($contractProduct as $pk => $pv)
			{
				$productStock[$pv['product_id']]['product_id'] = $pv['product_id'];

				$productStock[$pv['product_id']]['num'] += $pv['num'] ? $pv['num'] : 0;
			}

			foreach($productStock as $sk => &$sv)
			{
				//已出货数量
				$shipment_num = getCrmDbModel('shipment')->where(['customer_id'=>$customer_id,'product_id'=>$sv['product_id'],'company_id'=>$this->_company_id,'isvalid'=>1])->sum('num');

				$shipment_num = $shipment_num ? $shipment_num : 0;

				$sv['shipment_num'] = $shipment_num;

				$sv['surplus_num'] = $sv['num'] - $shipment_num; //剩余数量

				$product_detail = CrmgetCrmDetailList('product',$sv['product_id'],$this->_company_id,'name');

				$sv['product_name'] = $product_detail['name'];
			}

			$this->assign('productStock',$productStock);

			$this->assign('formList',$show_list['form_list']);

			$this->assign('shipment',$shipment);
		}

		$role = D('Role')->where(['company_id' => $this->_company_id])->field('role_id,role_name')->fetchAll();

        $this->assign('roleList', $role);

		$this->display();
	}

	public function edit_analysis($id="",$customer_id,$detailtype="",$detail_source="")
	{
		$analysis_id = decrypt($id,'CUSTOMER');

		$customer_id = decrypt($customer_id,'CUSTOMER');

		$detailtype = decrypt($detailtype,'CUSTOMER');

		$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER'),'detail_source'=>$detail_source]);

		if(IS_POST)
		{
			$data = $this->checkAnalysisEdit($analysis_id);

			if(!$analysis_id)
			{
				$analysis_id = getCrmDbModel('analysis')->add(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'createtime'=>NOW_TIME,'creater_id'=>$this->member_id]);
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

			$customer_detail = CrmgetCrmDetailList('customer',$customer_id,$this->_company_id,'name');

			D('CrmLog')->addCrmLog('analysis',2,$this->_company_id,$this->member_id,$customer_id,0,0,0,$customer_detail['name'],0,0,$analysis_id,0);

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];

			$this->ajaxReturn($result);

		}
		else
		{
			$analysis = getCrmDbModel('analysis')->where(['company_id'=>$this->_company_id,'analysis_id'=>$analysis_id,'customer_id'=>$customer_id])->find();

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

			$this->assign('customer_id',$customer_id);

			$this->assign('regionJson',C('regionJson'));

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
		$customer_id = decrypt($id,'CUSTOMER');

		$detailtype = decrypt($detailtype,'CUSTOMER');

		$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

		$this->assign('customer_id',$customer_id);

		$this->assign('detailtype',$detailtype);

		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'competitor'];

		if(IS_POST)
		{
			$data = $this->checkCompetitorCreate($customer_id);

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

				$customer_detail = CrmgetCrmDetailList('customer',$customer_id,$this->_company_id,'name');

				D('CrmLog')->addCrmLog('competitor',1,$this->_company_id,$this->member_id,$customer_id,0,0,0,$customer_detail['name'],0,0,0,$competitor_id);

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

			$this->assign('regionJson',C('regionJson'));

			$this->assign('competitorform',$competitorform);

			$this->display();
		}
	}
	public function checkCompetitorCreate($customer_id)
	{
		$competitor = I('post.competitor') ? I('post.competitor') : [];

		if(!$competitor['customer_id'])
		{
			if($customer_id)
			{
				$competitor['customer_id'] = $customer_id;
			}else
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
	public function edit_competitor($id="",$customer_id,$detailtype="",$detail_source="")
	{
		$competitor_id = decrypt($id,'CUSTOMER');

		if(!$competitor_id)
		{
			$this->common->_empty();

			die;
		}

		$customer_id = decrypt($customer_id,'CUSTOMER');

		$detailtype = decrypt($detailtype,'CUSTOMER');

		$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER'),'detail_source'=>$detail_source]);

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

			$customer_detail = CrmgetCrmDetailList('customer',$customer_id,$this->_company_id,'name');

			D('CrmLog')->addCrmLog('competitor',2,$this->_company_id,$this->member_id,$customer_id,0,0,0,$customer_detail['name'],0,0,0,$competitor_id);

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];

			$this->ajaxReturn($result);

		}
		else
		{
			$competitor = getCrmDbModel('competitor')->where(['company_id'=>$this->_company_id,'competitor_id'=>$competitor_id,'customer_id'=>$customer_id])->find();

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

			$this->assign('regionJson',C('regionJson'));

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
			$competitor_id = decrypt($id,'CUSTOMER');

			$competitor = getCrmDbModel('competitor')->where(['company_id'=>$this->_company_id,'competitor_id'=>$competitor_id,'isvalid'=>'1'])->find();

			$detailtype = decrypt($detailtype,'CUSTOMER');

			$localurl = U('detail',['id'=>encrypt($competitor['customer_id'],'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

			$where = ['competitor_id'=>$competitor_id,'company_id'=>$this->_company_id,'isvalid'=>'1'];

			if(getCrmDbModel('competitor')->where($where)->getField('competitor_id'))
			{
				if(getCrmDbModel('competitor')->where($where)->save(['isvalid'=>0]))
				{
					$customer_detail = CrmgetCrmDetailList('customer',$competitor['customer_id'],$this->_company_id,'name');

					D('CrmLog')->addCrmLog('competitor',3,$this->_company_id,$this->member_id,$competitor['customer_id'],0,0,0,$customer_detail['name'],0,0,0,$competitor_id);

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

	public function contacter()
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = '1';

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
		if(I('get.Itime'))
		{
			$field['createtime'] = unserialize(urldecode(I('get.Itime')));
		}

		if(!$field['member_id'])
		{
			$this->common->_empty();die;
		}

		//创建人维度查看联系人权限
		$CreaterViewContacter = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CreaterViewContacter',$this->_member['role_id'],'crm');

		if($CreaterViewContacter)
		{
			$field['_string'] = getCreaterViewSql($field['member_id']);

			unset($field['member_id']);

			$this->assign('isCreaterView',$CreaterViewContacter);

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

		$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'member_id'=>$memberRoleArr])->field('name,member_id,group_id')->select();

		$selectCustomerCount =  getCrmDbModel('Customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->count();

		$selectCustomerPage = new Page($selectCustomerCount, 10);

		$selectCustomerlist =  getCrmDbModel('Customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->field('customer_id,customer_prefix,customer_no,first_contact_id,createtime')->limit($selectCustomerPage->firstRow, $selectCustomerPage->listRows)->order('createtime desc')->select();

		foreach($selectCustomerlist as $key => &$val)
		{
			$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name,phone');

			//$val['contacter'] = CrmgetCrmDetailList('contacter',$val['first_contact_id'],$this->_company_id,'name,phone');
		}

		if(strlen($selectCustomerPage->show()) > 16) $this->assign('selectCustomerPage', $selectCustomerPage->show()); // 赋值分页输出

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

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword = unserialize(urldecode(I('get.highKeyword')));

			if(!$highKeyword)
			{
				$highKeyword = I('get.highKeyword');

				$_GET['highKeyword'] = urlencode(serialize(I('get.highKeyword')));
			}

			$contacterHighKey = D('CrmHighKeyword')->contacterHighKey($this->_company_id,$highKeyword);

			if($contacterHighKey['field'])
			{
				$field = array_merge($field,$contacterHighKey['field']);
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
			$keywordField = CrmgetDefineFormField($this->_company_id,'contacter','name,phone,email,wechat',$keyword);

			$field['contacter_id'] = $keywordField ? ['in',$keywordField] : '0';

			$this->assign('keyword', $keyword);
		}

		$nowPage = I('get.p');

		$count = getCrmDbModel('contacter')->where($field)->count();

		$Page = new Page($count, 20);

		$this->assign('pageCount', $count);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$contacter = getCrmDbModel('contacter')->where($field)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

		$show_list = CrmgetShowListField('contacter',$this->_company_id); //客户列表显示字段

		$export_field = C('EXPORT_CONTACTER_FIELD');

		$export_arr = C('EXPORT_CONTACTER_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'contacter');

		foreach($contacter as $key=>&$val)
		{
			$val['detail'] = CrmgetCrmDetailList('contacter',$val['contacter_id'],$this->_company_id,$show_list['form_name']);

			$thisCustomer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$val['customer_id'],'isvalid'=>1])->field('first_contact_id')->find();

			$val['first_contact_id'] = $thisCustomer['first_contact_id'];

			$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name');

			$val['customer_name'] = $customer_detail['name'];
		}

		$form_description = getCrmLanguageData('form_description');

		$defineformlist = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contacter','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		foreach($defineformlist as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		$export_contacter = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/export_contacter',$this->_member['role_id'],'crm'); //导出联系人权限

		$this->assign('isExportContacterAuth',$export_contacter);

		$this->assign('defineformlist',$defineformlist);

		$this->assign('selectCustomer',$selectCustomerlist);

		$this->assign('contacter',$contacter);

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

		session('contacterExportWhere',$field);

		session('ExportOrder',null);

		$this->display();
	}

	public function create($type = '',$wechat='')
	{

		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer'];

		$type = decrypt($type,'CUSTOMER');

		if($wechat)
		{
			$this->assign('wechat',$wechat);
		}

		if(IS_POST)
		{
			$data = $this->checkCreate();

			if($customer_id = getCrmDbModel('customer')->add($data['customer']))//客户
			{
				saveFeelCRMEncodeId($customer_id,$this->_company_id);

				foreach($data['customer_detail'] as &$v)
				{
					$v['customer_id'] = $customer_id;

					$v['company_id'] = $this->_company_id;

					if(is_array($v['form_content']))
					{
						$v['form_content'] = implode(',',$v['form_content']);
					}

					getCrmDbModel('customer_detail')->add($v); //添加客户详情

				}

				if($data['customer']['customer_type'] == 'agent')
				{
					$region = explode(',',$data['customer_detail']['region']['form_content']);

					if($region)
					{
						$regionData['company_id'] = $this->_company_id;

						$regionData['country_code'] = $region[0];

						if($region[0] == 1)
						{
							$regionData['province_code'] = $region[1] ? $region[1] : '';

							if(!$region_id = getCrmDbModel('region_census')->where($regionData)->find())
							{
								$regionData['createtime'] = NOW_TIME;

								getCrmDbModel('region_census')->add($regionData);
							}
						}
						else
						{
							if(!$region_id = getCrmDbModel('region_census')->where($regionData)->find())
							{
								$regionData['createtime'] = NOW_TIME;

								getCrmDbModel('region_census')->add($regionData);
							}
						}
					}
				}

				D('CrmLog')->addCrmLog('customer',1,$this->_company_id,$this->member_id,$customer_id,0,0,0,$data['customer_detail']['name']['form_content']);

				if($data['contacter_detail'])
				{
					$contacter = [];

					$contacter['company_id'] = $this->_company_id;

					$contacter['customer_id'] = $customer_id;

					if($data['customer']['member_id'])
					{
						$contacter['member_id'] = $data['customer']['member_id'];
					}

					$contacter['creater_id'] = $this->member_id;

					$contacter['createtime'] = NOW_TIME;

					if($contacter_id = getCrmDbModel('contacter')->add($contacter)) //添加客户联系人
					{
						getCrmDbModel('customer')->where(['customer_id'=>$customer_id,'company_id'=>$this->_company_id])->save(['first_contact_id'=>$contacter_id]); //添加客户首要联系人id

						foreach($data['contacter_detail'] as &$v)
						{
							$v['contacter_id'] = $contacter_id;

							$v['company_id'] = $this->_company_id;

							if(is_array($v['form_content']))
							{
								$v['form_content'] = implode(',',$v['form_content']);
							}

							getCrmDbModel('contacter_detail')->add($v);  //添加联系人详情
						}

						D('CrmLog')->addCrmLog('contacter',1,$this->_company_id,$this->member_id,$customer_id,$contacter_id,0,0,$data['contacter_detail']['name']['form_content']);
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
					}
				}

				if($data['customer']['member_id'])
				{
					if($wechat)
					{
						$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'reloadType'=>'parent','url'=>U('customer/wechatmessage')];
					}
					elseif($data['customer']['customer_type'] == 'agent')
					{
						$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('agent')];
					}
					else
					{
						$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('index')];
					}
				}
				else
				{
					$customer_notifier = getCrmDbModel('notify_config')->where(['company_id'=>$this->_company_id])->getField('customer_notifier');

					if($customer_notifier)
					{
						$customer_notifier = explode(',',$customer_notifier);

						foreach($customer_notifier as $key=>$val)
						{
							D('CrmCreateMessage')->createMessage(5,$this->_sms,$val,$this->_company_id,$this->member_id,$customer_id);
						}
					}
					else
					{
						$first_member = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'is_first'=>2])->getField('member_id');

						D('CrmCreateMessage')->createMessage(5,$this->_sms,$first_member,$this->_company_id,$this->member_id,$customer_id);
					}
					//D('CrmCreateMessage')->createMessage(5,$this->_sms,0,$this->_company_id,$this->member_id,$customer_id);

					if($wechat)
					{
						$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'reloadType'=>'parent','url'=>U('customer/wechatmessage')];
					}
					else
					{
						$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('pool')];
					}
				}

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

			$customerform = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where($where)
				->order('orderby asc')
				->select();

			foreach($customerform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
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

			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$this->member_id])->field('member_id,account,name')->find();

			$members = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'feelec_opened'=>10])->field('member_id,account,name')->fetchAll();


			//接口传递数据
			if($apiData = I('get.apiData'))
			{
				$apiData = unserialize(urldecode(base64_decode($apiData)));

				$customerData = [
					'name'  =>$apiData['customer_name'] ? $apiData['customer_name'] : $apiData['contacter_name'],
					'phone' =>$apiData['contacter_phone'] ? $apiData['contacter_phone'] : '',
					'email' =>$apiData['contacter_email'] ? $apiData['contacter_email'] : ''
				];

				$contacterData = [
					'name'  =>$apiData['contacter_name'] ? $apiData['contacter_name'] : '',
					'phone' =>$apiData['contacter_phone'] ? $apiData['contacter_phone'] : '',
					'email' =>$apiData['contacter_email'] ? $apiData['contacter_email'] : '',
				];

				$this->assign('customerData',$customerData);

				$this->assign('contacterData',$contacterData);

				$this->assign('apiData',$apiData);

				$this->assign('fromType','API');
			}


			$this->assign('regionJson',C('regionJson'));

			$this->assign('thisMember',$thisMember);

			$this->assign('members',$members);

			$this->assign('customerform',$customerform);

			$this->assign('contacterform',$contacterform);

			$this->assign('type',$type);

			$name = getCrmLanguageData('name');

			$country = getCrmDbModel('country')->field(['*',$name])->select();

			$this->assign('country',$country);

			$this->display();
		}
	}

	public function checkCreate()
	{
		$customer = checkFields(I('post.customer'), $this->CustomerFields);

		$customer['company_id'] = $this->_company_id;//工单所属公司ID

		$customer['createtime'] = NOW_TIME;

		$customer['creater_id'] = $this->member_id;

		$customer['entry_method'] = 'CREATE';

		if($this->_crmsite['customerCode'])
		{
			$customer['customer_prefix'] = $this->_crmsite['customerCode'];
		}
		else
		{
			$customer['customer_prefix'] = 'C-';
		}

		$customer['customer_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$this->_company_id), rand(0,9), 4));

		$customer['from_type'] =  $customer['from_type'] ? $customer['from_type'] : 'PC';

		$customer_form = I('post.customer_form');

		$CustomerCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$customer_form,'customer',$this->_member);

		if($CustomerCheckForm['detail'])
		{
			$customer_detail = $CustomerCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($CustomerCheckForm);
		}

		return ['customer'=>$customer,'customer_detail'=>$customer_detail,'contacter_detail'=>$contacter_detail];

	}


	public function edit($id,$type,$detailtype="")
	{
		$customer_id = decrypt($id,'CUSTOMER');

		$type = decrypt($type,'CUSTOMER');

		$localurl = U($type);

		$detailtype = decrypt($detailtype,'CUSTOMER');

		if($detailtype)
		{
			$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

			$this->assign('detailtype',$detailtype);
		}

		$customer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>'1'])->find();

		if(IS_POST)
		{
			$data = $this->checkEdit($customer_id);

			getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>'1'])->save($data['customer']);

			getCrmDbModel('customer_detail')->where(['customer_id'=>$customer_id,'company_id'=>$this->_company_id])->delete();

			foreach($data['customer_detail'] as &$v)
			{
				$v['customer_id'] = $customer_id;

				$v['company_id'] = $this->_company_id;

				if(is_array($v['form_content']))
				{
					$v['form_content'] = implode(',',$v['form_content']);
				}

				getCrmDbModel('customer_detail')->add($v);  //添加客户详情
			}

			if($data['customer']['customer_type'] == 'agent')
			{
				$region = explode(',',$data['customer_detail']['region']['form_content']);

				if($region)
				{
					$regionData['company_id'] = $this->_company_id;

					$regionData['country_code'] = $region[0];

					if($region[0] == 1)
					{
						$regionData['province_code'] = $region[1] ? $region[1] : '';

						if(!$region_id = getCrmDbModel('region_census')->where($regionData)->find())
						{
							$regionData['createtime'] = NOW_TIME;

							getCrmDbModel('region_census')->add($regionData);
						}
					}
					else
					{
						if(!$region_id = getCrmDbModel('region_census')->where($regionData)->find())
						{
							$regionData['createtime'] = NOW_TIME;

							getCrmDbModel('region_census')->add($regionData);
						}
					}
				}
			}

			D('CrmLog')->addCrmLog('customer',2,$this->_company_id,$this->member_id,$customer_id,0,0,0,$data['customer_detail']['name']['form_content']);

			if($customer['member_id'] > 0)
			{
				$html = D('CrmCustomer')->getCustomerListHtml($this->_company_id,$customer_id,1);
			}
			else
			{
				$html = D('CrmCustomer')->getCustomerListHtml($this->_company_id,$customer_id,2);
			}
			//var_dump($html);die;
			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl,'html'=>$html];

			$this->ajaxReturn($result);

		}
		else
		{
			//$customer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>'1'])->find();

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

				$customer_detail = getCrmDbModel('customer_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'customer_id'=>$customer_id])->find();

				if($v['form_type']=='region')
				{
					if($customer_detail['form_content'])
					{
						$region_detail = explode(',',$customer_detail['form_content']);

						$customer[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$customer[$v['form_name']]['defaultProv'] = $region_detail[1];

						$customer[$v['form_name']]['defaultCity'] = $region_detail[2];

						$customer[$v['form_name']]['defaultArea'] = $region_detail[3];

					}
				}
				else
				{
					$customer[$v['form_name']] = $customer_detail['form_content'];
				}
			}

			$this->assign('regionJson',C('regionJson'));

			$this->assign('customerform',$customerform);

			$this->assign('customer',$customer);

			$this->assign('type',$type);

			$this->display();
		}
	}

	public function checkEdit($customer_id)
	{
		$customer = checkFields(I('post.customer'), $this->CustomerFields);

		$customer_form = I('post.customer_form');

		$CustomerCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$customer_form,'customer',$this->_member,$customer_id);

		if($CustomerCheckForm['detail'])
		{
			$customer_detail = $CustomerCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($CustomerCheckForm);
		}

		return ['customer'=>$customer,'customer_detail'=>$customer_detail];
	}

	public function delete($id='',$type='index')
	{
		$ids = I('post.customer_ids');

		$type = decrypt($type,'CUSTOMER');

		$localurl = U($type);

		$result = [];

		if($ids && count($ids) > 0)
		{
			foreach($ids as $k=>$v)
			{
				$customer_id = decrypt($v,'CUSTOMER');

				$result = D('CrmCustomer')->deleteCustomer($customer_id,$this->_company_id,$this->member_id,$this->_member,$localurl,'arr');
			}
		}
		else
		{
			if($id)
			{
				$customer_id = decrypt($id,'CUSTOMER');

				$result = D('CrmCustomer')->deleteCustomer($customer_id,$this->_company_id,$this->member_id,$this->_member,$localurl,'one');
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('SELECT_CUSTOMER_DELETE')];
			}
		}

		$this->ajaxReturn($result);
	}

	public function create_contacter($id='',$detailtype='',$wechat='')
	{
		$customer_id = decrypt($id,'CUSTOMER');

		$localurl = U('contacter');

		$detailtype = decrypt($detailtype,'CUSTOMER');

		if($wechat)
		{
			$this->assign('wechat',$wechat);
		}

		if($detailtype)
		{
			$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

			$this->assign('customer_id',$customer_id);

			$this->assign('detailtype',$detailtype);
		}

		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contacter'];

		if(IS_POST)
		{
			$data = $this->checkContacter($customer_id);

			if($data['contacter_id'])
			{
				$wechat_defineform = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'type'=>'contacter','form_name'=>'wechat','closed'=>0])->getField('form_id');

				$wechat_field = ['company_id'=>$this->_company_id,'contacter_id'=>$data['contacter_id'],'form_id'=>$wechat_defineform];

				if($contacter_wechat = getCrmDbModel('contacter_detail')->where($wechat_field)->find())
				{
					if($contacter_wechat['form_content'])
					{
						$data['wechat'] = $contacter_wechat['form_content'].','.$data['wechat'];
					}

					if(getCrmDbModel('contacter_detail')->where($wechat_field)->setField('form_content',$data['wechat']))
					{
						$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'reloadType'=>'parent','url'=>U('customer/wechatmessage')];
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
					}
				}
				else
				{
					if(getCrmDbModel('contacter_detail')->add(['company_id'=>$this->_company_id,'contacter_id'=>$data['contacter_id'],'form_id'=>$wechat_defineform,'form_content'=>$data['wechat']]))
					{
						$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'reloadType'=>'parent','url'=>U('customer/wechatmessage')];
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
					}
				}
			}
			else
			{
				if($contacter_id = getCrmDbModel('contacter')->add($data['contacter']))//添加联系人
				{
					$first_contact_id = getCrmDbModel('customer')->where(['customer_id'=>$data['contacter']['customer_id'],'company_id'=>$this->_company_id])->getField('first_contact_id');

					if(!$first_contact_id)
					{
						getCrmDbModel('customer')->where(['customer_id'=>$data['contacter']['customer_id'],'company_id'=>$this->_company_id])->save(['first_contact_id'=>$contacter_id]); //添加客户首要联系人id
					}

					foreach($data['contacter_detail'] as &$v)
					{
						$v['contacter_id'] = $contacter_id;

						$v['company_id'] = $this->_company_id;

						if(is_array($v['form_content']))
						{
							$v['form_content'] = implode(',',$v['form_content']);
						}

						getCrmDbModel('contacter_detail')->add($v);  //添加联系人详情
					}

					D('CrmLog')->addCrmLog('contacter',1,$this->_company_id,$this->member_id,$customer_id,$contacter_id,0,0,$data['contacter_detail']['name']['form_content']);
					if($wechat)
					{
						$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'reloadType'=>'parent','url'=>U('customer/wechatmessage')];
					}
					else
					{
						$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];
					}
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
			$form_description = getCrmLanguageData('form_description');

			$contacterform = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where($where)
				->order('orderby asc')
				->select();

			foreach($contacterform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

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

			$this->assign('contacterform',$contacterform);

			$this->display();
		}
	}

	public function checkContacter($customer_id = '')
	{
		$contacter = I('post.contacter') ? I('post.contacter') : [];

		$editContacterWechat = I('post.editContacterWechat');

		if($editContacterWechat == 1)
		{

			$contacter_edit = I('post.contacter_edit');

			if(!$contacter['customer_id'])
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_SELECT_CUSTOMER')]);
			}

			if(!$contacter_edit['contacter_id'])
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('SELECT_CUSTOMER_CONTACT')]);
			}

			if(!$contacter_edit['wechat'])
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('WECHAT_ERROR')]);
			}

			return ['customer_id'=>$contacter['customer_id'],'contacter_id'=>$contacter_edit['contacter_id'],'wechat'=>$contacter_edit['wechat']];
		}
		else
		{
			if(!$contacter['customer_id'])
			{
				if($customer_id)
				{
					$contacter['customer_id'] = $customer_id;
				}else
				{
					$this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_SELECT_CUSTOMER')]);
				}
			}

			$thisMemberId = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$contacter['customer_id'],'isvalid'=>1])->getField('member_id');

			if($thisMemberId)
			{
				$contacter['member_id'] = $thisMemberId;
			}

			$contacter['company_id'] = $this->_company_id;

			$contacter['creater_id'] = $this->member_id;

			$contacter['createtime'] = NOW_TIME;

			$contacter_form = I('post.contacter_form');

			$ContacterCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$contacter_form,'contacter',$this->_member);

			if($ContacterCheckForm['detail'])
			{
				$contacter_detail = $ContacterCheckForm['detail'];
			}
			else
			{
				$this->ajaxReturn($ContacterCheckForm);
			}

			return ['contacter'=>$contacter,'contacter_detail'=>$contacter_detail];
		}
	}

	public function edit_contacter($id,$detailtype="")
	{
		$contacter_id = decrypt($id,'CONTACTER');

		$localurl = U('contacter');

		$contacter = getCrmDbModel('contacter')->where(['company_id'=>$this->_company_id,'contacter_id'=>$contacter_id,'isvalid'=>'1'])->find();

		$detailtype = decrypt($detailtype,'CUSTOMER');

		if($detailtype)
		{
			$localurl = U('detail',['id'=>encrypt($contacter['customer_id'],'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

			$this->assign('detailtype',$detailtype);
		}

		if(IS_POST)
		{
			$data = $this->checkContacterEdit($contacter_id);

			getCrmDbModel('contacter_detail')->where(['contacter_id'=>$contacter_id,'company_id'=>$this->_company_id])->delete();

			foreach($data['contacter_detail'] as &$v)
			{
				$v['contacter_id'] = $contacter_id;

				$v['company_id'] = $this->_company_id;

				if(is_array($v['form_content']))
				{
					$v['form_content'] = implode(',',$v['form_content']);
				}

				getCrmDbModel('contacter_detail')->add($v);  //添加联系人详情
			}

			D('CrmLog')->addCrmLog('contacter',2,$this->_company_id,$this->member_id,$contacter['customer_id'],$contacter_id,0,0,$data['contacter_detail']['name']['form_content']);

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];

			$this->ajaxReturn($result);

		}
		else
		{
			$form_description = getCrmLanguageData('form_description');

			$contacterform = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contacter'])
				->order('orderby asc')
				->select();

			foreach($contacterform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$contacter_detail = getCrmDbModel('contacter_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'contacter_id'=>$contacter_id])->find();

				if($v['form_type']=='region')
				{
					if($contacter_detail['form_content'])
					{
						$region_detail = explode(',',$contacter_detail['form_content']);

						$contacter[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$contacter[$v['form_name']]['defaultProv'] = $region_detail[1];

						$contacter[$v['form_name']]['defaultCity'] = $region_detail[2];

						$contacter[$v['form_name']]['defaultArea'] = $region_detail[3];

					}
				}else
				{
					$contacter[$v['form_name']] = $contacter_detail['form_content'];
				}

			}

			$customer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'isvalid'=>'1','customer_id'=>$contacter['customer_id']])->find();

			$where['company_id'] = $this->_company_id;

			$where['type'] = 'customer';

			$where['closed'] = 0;

			$where['form_name'] = array('in','name');

			$customer_form = getCrmDbModel('define_form')->where($where)->field('form_id,form_name')->select();

			foreach($customer_form as $k2=>&$v2)
			{
				$customer_detail = getCrmDbModel('customer_detail')->where(['customer_id'=>$contacter['customer_id'],'company_id'=>$this->_company_id,'form_id'=>$v2['form_id']])->find();

				$contacter['customer_'.$v2['form_name']] = $customer_detail['form_content'];
			}

			$this->assign('regionJson',C('regionJson'));

			$this->assign('contacterform',$contacterform);

			$this->assign('contacter',$contacter);

			$this->display();
		}
	}

	public function checkContacterEdit($contacter_id)
	{
		$contacter_form = I('post.contacter_form');

		$ContacterCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$contacter_form,'contacter',$this->_member,$contacter_id);

		if($ContacterCheckForm['detail'])
		{
			$contacter_detail = $ContacterCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ContacterCheckForm);
		}

		return ['contacter_detail'=>$contacter_detail];
	}

	public function delete_contacter($id='',$detailtype='')
	{
		if(IS_AJAX)
		{
			$recover_contacter = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'recover/contacter',$this->_member['role_id'],'crm');

			$ids = I('post.ids');

			$result = [];

			if($ids && count($ids) > 0)
			{
				foreach($ids as $k=>$v)
				{
					$contacter_id = decrypt($v, 'CONTACTER');

					$contacter = getCrmDbModel('contacter')->where(['company_id' => $this->_company_id, 'contacter_id' => $contacter_id])->field('contacter_id,customer_id,isvalid')->find();

					if (!$contacter) {
						$this->ajaxReturn(['status' => 0, 'msg' => L('CONTACT_DOES_NOT_EXIST')]);
					}

					$localurl = U('contacter');

					$where = ['contacter_id' => $contacter_id, 'company_id' => $this->_company_id, 'isvalid' => '1'];

					$contacter['detail'] = CrmgetCrmDetailList('contacter', $contacter_id, $this->_company_id);

					if ($contacter['isvalid'] == 1)
					{
						$delete = getCrmDbModel('contacter')->where($where)->save(['isvalid' => 0]);

						if($detailtype)
						{
							$localurl = U('detail',['id'=>encrypt($contacter['customer_id'],'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

							$this->assign('detailtype',$detailtype);
						}
					}
					else
					{
						if ($recover_contacter) $localurl = U('Recover/contacter');

						$delete = getCrmDbModel('contacter')->where(['contacter_id' => $contacter_id, 'company_id' => $this->_company_id])->delete();

						if ($delete) {
							getCrmDbModel('contacter_detail')->where(['company_id' => $this->_company_id, 'contacter_id' => $contacter_id])->delete();
						}
					}

					if ($delete)
					{
						if ($contacter['isvalid'] == 1)
						{
							D('CrmLog')->addCrmLog('contacter', 3, $this->_company_id, $this->member_id, $contacter['customer_id'], $contacter_id, 0, 0, $contacter['detail']['name']);
						}
						else
						{
							D('CrmLog')->addCrmLog('contacter', 15, $this->_company_id, $this->member_id, $contacter['customer_id'], $contacter_id, 0, 0, $contacter['detail']['name']);
						}

						$result = ['status' => 2, 'msg' => L('DELETE_SUCCESS'), 'url' => $localurl];
					} else {
						$result = ['status' => 0, 'msg' => L('DELETE_FAILED')];
					}
				}
			}
			else
			{
				$contacter_id = decrypt($id, 'CONTACTER');

				$contacter = getCrmDbModel('contacter')->where(['company_id' => $this->_company_id, 'contacter_id' => $contacter_id])->field('contacter_id,customer_id,isvalid')->find();

				if (!$contacter) {
					$this->ajaxReturn(['status' => 0, 'msg' => L('CONTACT_DOES_NOT_EXIST')]);
				}

				$localurl = U('contacter');

				$detailtype = decrypt($detailtype, 'CUSTOMER');

				if ($detailtype) {
					$localurl = U('detail', ['id' => encrypt($contacter['customer_id'], 'CUSTOMER'), 'detailtype' => encrypt($detailtype, 'CUSTOMER')]);

				}

				$where = ['contacter_id' => $contacter_id, 'company_id' => $this->_company_id, 'isvalid' => '1'];

				$contacter['detail'] = CrmgetCrmDetailList('contacter', $contacter_id, $this->_company_id);

				if ($contacter['isvalid'] == 1)
				{
					$delete = getCrmDbModel('contacter')->where($where)->save(['isvalid' => 0]);

					if($detailtype)
					{
						$localurl = U('detail',['id'=>encrypt($contacter['customer_id'],'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

						$this->assign('detailtype',$detailtype);
					}
				}
				else
				{
					if ($recover_contacter) $localurl = U('Recover/contacter');

					$delete = getCrmDbModel('contacter')->where(['contacter_id' => $contacter_id, 'company_id' => $this->_company_id])->delete();

					if ($delete) {
						getCrmDbModel('contacter_detail')->where(['company_id' => $this->_company_id, 'contacter_id' => $contacter_id])->delete();
					}
				}

				if ($delete)
				{
					if ($contacter['isvalid'] == 1)
					{
						D('CrmLog')->addCrmLog('contacter', 3, $this->_company_id, $this->member_id, $contacter['customer_id'], $contacter_id, 0, 0, $contacter['detail']['name']);
					}
					else
					{
						D('CrmLog')->addCrmLog('contacter', 15, $this->_company_id, $this->member_id, $contacter['customer_id'], $contacter_id, 0, 0, $contacter['detail']['name']);
					}

					$result = ['status' => 2, 'msg' => L('DELETE_SUCCESS'), 'url' => $localurl];
				}
				else
				{
					$result = ['status' => 0, 'msg' => L('DELETE_FAILED')];
				}
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$this->common->_empty();
		}
	}

	public function setFirst_contacter($id,$detailtype='')
	{
		if(IS_AJAX)
		{
			$contacter_id = decrypt($id,'CONTACTER');

			$contacter = getCrmDbModel('contacter')->where(['company_id'=>$this->_company_id,'contacter_id'=>$contacter_id,'isvalid'=>'1'])->find();

			$localurl = U('contacter');

			$detailtype = decrypt($detailtype,'CUSTOMER');

			if($detailtype)
			{
				$localurl = U('detail',['id'=>encrypt($contacter['customer_id'],'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

			}

			$where = ['contacter_id'=>$contacter_id,'company_id'=>$this->_company_id,'isvalid'=>'1'];

			if(getCrmDbModel('contacter')->where($where)->getField('contacter_id'))
			{
				if(getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$contacter['customer_id'],'isvalid'=>1])->save(['first_contact_id'=>$contacter_id]))
				{
					$contacter_detail = CrmgetCrmDetailList('contacter',$contacter_id,$this->_company_id);

					D('CrmLog')->addCrmLog('contacter',10,$this->_company_id,$this->member_id,$contacter['customer_id'],$contacter_id,0,0,$contacter_detail['name']);

					$result = ['status'=>2,'msg'=>L('SET_SUCCESSFULLY'),'url'=>$localurl];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SETUP_FAILED')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('WRONG_CONTACT')];
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$this->common->_empty();
		}
	}

	public function draw() //客户领取
	{
		if(IS_AJAX)
		{
			$ids = I('post.customer_ids');

			if(count($ids) > 0)
			{
				if($this->_crmsite['custLimit'] == 1)
				{
					//检查是否超出领取限制
					$checkDrawCount = D('CrmCustomer')->checkDrawCount($this->_company_id,$this->member_id,count($ids));

					if($checkDrawCount['msg'])
					{
						$this->ajaxReturn($checkDrawCount);
					}
				}

				foreach($ids as $k=>$v)
				{
					$customer_id = decrypt($v,'CUSTOMER');

					$result = D('CrmCustomer')->drawCustomer($this->_company_id,$this->member_id,$customer_id,$this->_sms);

					if($result['status'] != 2)
					{
						$this->ajaxReturn($result);
					}
				}

			}else
			{
				$result = ['status'=>0,'msg'=>L('SELECT_THE_CUSTOMER_TO_RECEIVE')];
			}

			$this->ajaxReturn($result);

		}
		else
		{
			$this->common->_empty();
		}
	}

	public function allot() //客户分配
	{
		if(IS_AJAX)
		{
			$ids = I('post.ids');

			$member_id = I('post.member_id');

			$member_id = decrypt($member_id,'MEMBER');

			if($member_id)
			{
				$result = [];

				if(count($ids) > 0)
				{
					foreach($ids as $k=>$v)
					{
						$customer_id = decrypt($v,'CUSTOMER');

						$result = D('CrmCustomer')->allotCustomer($this->_company_id,$member_id,$this->member_id,$customer_id,$this->_sms);

						if($result['status'] != 2)
						{
							$this->ajaxReturn($result);
						}
					}
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_CUSTOMER_ASSIGNED')];
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

	public function transfer() //客户转移
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
						$customer_id = decrypt($v,'CUSTOMER');

						$result = D('CrmCustomer')->transferCustomer($this->_company_id,$member_id,$this->member_id,$customer_id,$this->_sms);

						if($result['status'] != 2)
						{
							$this->ajaxReturn($result);
						}
					}
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_CUSTOMER_TRANSFER')];
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

	public function toPool() //放弃客户
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
				$result = [];

				if(count($ids) > 0)
				{
					foreach($ids as $k=>$v)
					{
						$customer_id = decrypt($v,'CUSTOMER');

						$result = D('CrmCustomer')->customerToPool($this->_company_id,$this->member_id,$customer_id,$abandon_id);

						if($result['status'] != 2)
						{
							$this->ajaxReturn($result);
						}
					}

				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_CLIENT_GIVE_UP')];
				}
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$this->common->_empty();
		}
	}

	public function lose() //客户失单
	{
		if(IS_AJAX)
		{
			$ids = I('post.customer_ids');

			$lose_id = I('post.lose_id');

			$competitor_id = I('post.competitor_id');

			$lose_closed = I('post.lose_closed');

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
					$customer_ids = [];

					foreach($ids as $k=>$v)
					{
						$customer_id = decrypt($v,'CUSTOMER');

						$field['customer_id'] = $customer_id;

						if(getCrmDbModel('order')->where($field)->find() || getCrmDbModel('customer')->where($field)->getField('is_trade'))
						{
							$result = ['status'=>0,'msg'=>L('CUSTOMERS_ALREADY_CANNOT_LOSE_OD')];
						}
						else
						{
							$member_id = getCrmDbModel('customer')->where($field)->getField('member_id');

							if(getCrmDbModel('customer')->where($field)->save(['is_losed'=>1]))
							{
								if($lose_closed == 1)
								{
									getCrmDbModel('customer')->where($field)->save(['member_id'=>0]);

									getCrmDbModel('contacter')->where($field)->save(['member_id'=>0]);
								}

								$customer_lose['customer_id'] = $customer_id;

								$customer_lose['lose_id'] = $lose_id;

								$customer_lose['competitor_id'] = $competitor_id;

								$customer_lose['company_id'] = $this-_company_id;

								$customer_lose['member_id'] = $member_id;

								$customer_lose['operator_id'] = $this->member_id;

								$customer_lose['createtime'] = NOW_TIME;

								getCrmDbModel('customer_lose')->add($customer_lose);

								$customer_detail = CrmgetCrmDetailList('customer',$customer_id,$this->_company_id);

								D('CrmLog')->addCrmLog('customer',13,$this->_company_id,$this->member_id,$customer_id,0,0,0,$customer_detail['name']);

								if($lose_closed == 1)
								{
									$result = ['status'=>2,'msg'=>L('CUSTOMER_LOST_ORDER_PLACED_POOL'),'url'=>U('index')];
								}
								else
								{
									$result = ['status'=>2,'msg'=>L('CUSTOMER_HAS_LOST_ORDER'),'url'=>U('index')];
								}
							}
							else
							{
								$result = ['status'=>0,'msg'=>L('LOST_ORDER_FAILED')];
							}
						}

					}

				}else
				{
					$result = ['status'=>0,'msg'=>L('SELECT_CUSTOMER_LOSE_ORDER')];
				}
			}
			$this->ajaxReturn($result);

		}
		else
		{
			$this->common->_empty();
		}
	}

	public function followup()
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

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

		if(I('get.ImemberId')) $field['member_id'] = I('get.ImemberId');

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword = unserialize(urldecode(I('get.highKeyword')));

			if(!$highKeyword)
			{
				$highKeyword = I('get.highKeyword');

				$_GET['highKeyword'] = urlencode(serialize(I('get.highKeyword')));
			}

			$folowHighKey = D('CrmHighKeyword')->folowHighKey($this->_company_id,$highKeyword);

			if($folowHighKey['field'])
			{
				$field = array_merge($field,$folowHighKey['field']);
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
			$field['content'] = ["like","%".$keyword."%"];

			$this->assign('keyword', $keyword);
		}

		$follow_type = I('get.follow_type') ? I('get.follow_type') : 'all';

		if($follow_type != 'all')
		{
			if($follow_type == 'customer')
			{
				$customer_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/index',$this->_member['role_id'],'crm');

				if($customer_view_auth)
				{
					$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id);

					$memberRoleArr = $getCustomerAuth['memberRoleArr'];

					$customer_arr = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->field('customer_id')->select();

					$customer_arr = array_column($customer_arr,'customer_id');

					$sql = $customer_arr ? 'customer_id in ('.implode(',',$customer_arr).')' : 'customer_id = -1';
				}
				else
				{
					$sql = 'customer_id = -1';
				}

				//组合数据查询条件
				$field['_string'] = $sql;
			}
			elseif($follow_type == 'customer_pool')
			{
				$customer_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/pool',$this->_member['role_id'],'crm');

				if($customer_view_auth)
				{
					$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,'','pool');

					$memberRoleArr = $getCustomerAuth['memberRoleArr'];

					$customer_arr = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>'','creater_id'=>$memberRoleArr])->field('customer_id')->select();

					$customer_arr = array_column($customer_arr,'customer_id');

					$sql = $customer_arr ? 'customer_id in ('.implode(',',$customer_arr).')' : 'customer_id = -1';
				}
				else
				{
					$sql = 'customer_id = -1';
				}

				//组合数据查询条件
				$field['_string'] = $sql;
			}
			elseif($follow_type == 'clue')
			{
				$clue_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'clue/index',$this->_member['role_id'],'crm');

				if($clue_view_auth)
				{
					$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,'','clue');

					$memberRoleArr = $getCustomerAuth['memberRoleArr'];

					$clue_arr = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->field('clue_id')->select();

					$clue_arr = array_column($clue_arr,'clue_id');

					$sql = $clue_arr ? 'clue_id in ('.implode(',',$clue_arr).')' : 'clue_id = -1';
				}
				else
				{
					$sql = 'clue_id = -1';
				}

				//组合数据查询条件
				$field['_string'] = $sql;
			}
			elseif($follow_type == 'clue_pool')
			{
				$clue_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'clue/pool',$this->_member['role_id'],'crm');

				if($clue_view_auth)
				{
					$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,'','cluePool');

					$memberRoleArr = $getCustomerAuth['memberRoleArr'];

					$clue_arr = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>'','creater_id'=>$memberRoleArr])->field('clue_id')->select();

					$clue_arr = array_column($clue_arr,'clue_id');

					$sql = $clue_arr ? 'clue_id in ('.implode(',',$clue_arr).')' : 'clue_id = -1';
				}
				else
				{
					$sql = 'clue_id = -1';
				}

				//组合数据查询条件
				$field['_string'] = $sql;
			}
			else
			{
				$field['type'] = $follow_type;
			}
		}

		$this->assign('follow_type',$follow_type);

		if($define_form = I('get.define_form'))
		{
			if($define_form['importance'])
			{
				//筛选所选时间段的所有数据
				$importance_form = getCrmDbModel('define_form')
					->where(['company_id'=>$this->_company_id,'type'=>'customer','form_name'=>'importance'])
					->field('form_id,form_type')->find();

				$form_content = getCrmDbModel('customer_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$importance_form['form_id'],'form_content'=>$define_form['importance']])->field('customer_id,form_content')->select();

				if($form_content)
				{
					$data_arr = array_column($form_content,'customer_id');

					if(in_array($follow_type,['customer','customer_pool']))
					{
						$data_arr = $customer_arr ? array_intersect($customer_arr, $data_arr) : ['-1'];//取得数组中的重复数据，返回重复数组
					}

					if($data_arr)
					{
						$importance_sql = '(customer_id in ('.implode(',',$data_arr).'))';

						if(in_array($follow_type,['all','opportunity']))
						{
							$opportunity = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'customer_id'=>['in',implode(',',$data_arr)]])->field('opportunity_id')->select();

							$opportunity_arr = array_column($opportunity,'opportunity_id');

							if($opportunity_arr) $importance_sql .= ' or (opportunity_id in ('.implode(',',$opportunity_arr).'))';
						}

						if(in_array($follow_type,['all','clue','clue_pool']))
						{
							$clue = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'customer_id'=>['in',implode(',',$data_arr)]])->field('clue_id')->select();

							$clue_arr1 = array_column($clue,'clue_id');

							if(in_array($follow_type,['clue','clue_pool']))
							{
								$clue_arr1 = $clue_arr ? array_intersect($clue_arr, $clue_arr1) : ['-1'];//取得数组中的重复数据，返回重复数组

								$importance_sql = $clue_arr1 ? '(clue_id in ('.implode(',',$clue_arr1).'))' : 'clue_id = -1';
							}
							else
							{
								if($clue_arr1) $importance_sql .= ' or (clue_id in ('.implode(',',$clue_arr1).'))';
							}
						}
					}
					else
					{
						$importance_sql = 'customer_id = -1';
					}
				}
				else
				{
					$importance_sql = 'customer_id = -1';
				}

				//组合数据查询条件
				$field['_string'] = $importance_sql;
			}

			$this->assign('define_form',$define_form);
		}

		//排序
		$getSortBy = D('CrmHighKeyword')->getSortBy();

		$order = $getSortBy['order'] == 'lastfollowtime desc' ? 'createtime desc' : $getSortBy['order'];

		$sort_by = $getSortBy['sort_by'] == 'followtime-desc' ? 'createtime-desc' : $getSortBy['sort_by'];

		$this->assign('sort_by',$sort_by);

		$nowPage = I('get.p');

		$count = getCrmDbModel('followup')->where($field)->count();

		$Page = new Page($count, 20);

		$this->assign('pageCount', $count);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$follow = getCrmDbModel('followup')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		$export_field = C('EXPORT_FOLLOW_FIELD');

		$export_arr = C('EXPORT_FOLLOW_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'follow');

		$cmncate_field = getCrmLanguageData('cmncate_name');

		foreach($follow as $key=>&$val)
		{
			$val['member_name'] = M('member')->where(['company_id'=>$this->_company_id,'member_id'=>$val['member_id'],'type'=>1])->getField('name');

			if($val['cmncate_id'])
			{
				$val['cmncate_name'] = getCrmDbModel('communicate')->where(['cmncate_id'=>$val['cmncate_id'],'company_id'=>$this->_company_id])->getField($cmncate_field);
			}

			if($val['clue_id'] > 0)
			{
				$val['follow_type'] = L('CLUE');

				$clue_detail = CrmgetCrmDetailList('clue',$val['clue_id'],$this->_company_id,'name');

				$val['belong_name'] = $clue_detail['name'];
			}
			elseif($val['opportunity_id'] > 0)
			{
				$val['follow_type'] = L('OPPORTUNITY');

				$opportunity_detail = CrmgetCrmDetailList('opportunity',$val['opportunity_id'],$this->_company_id,'name');

				$val['belong_name'] = $opportunity_detail['name'];
			}
			else
			{
				$val['follow_type'] = L('CUSTOMER');

				$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name');

				$val['belong_name'] = $customer_detail['name'];
			}

			$val['comment'] = getCrmDbModel('follow_comment')->where(['company_id'=>$this->_company_id,'follow_id'=>$val['follow_id'],'isvalid'=>1])->order('createtime desc')->field('content')->select();

			$val['content'] = htmlspecialchars_decode($val['content']);

            if($val['content'] && !strip_tags($val['content']))
            {
                $val['content'] = L('IMAGES');
            }
            else
            {
                $val['content'] = strip_tags($val['content']);
            }

		}

		//查询重要程度筛选项
		$form_description = getCrmLanguageData('form_description');

		$filterlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_name'=>['in','importance']])
			->order('orderby asc')
			->select();

		foreach($filterlist as $k=>&$v)
		{
			$v['option'] = explode('|',$v['form_option']);
		}

		$this->assign('filterlist',$filterlist);

		$cmncate_field = getCrmLanguageData('cmncate_name');

		$cmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

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

		//线索列表
		$getClueAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,'','clue');

		$clueMemberRoleArr = $getClueAuth['memberRoleArr'];

		$selectClueCount =  getCrmDbModel('Clue')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$clueMemberRoleArr])->count();

		$selectCluePage = new Page($selectClueCount, 10);

		$selectCluelist =  getCrmDbModel('Clue')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$clueMemberRoleArr])->field('clue_id,clue_prefix,clue_no,createtime')->limit($selectCluePage->firstRow, $selectCluePage->listRows)->order('createtime desc')->select();

		foreach($selectCluelist as $key => &$val)
		{
			$val['detail'] = CrmgetCrmDetailList('clue',$val['clue_id'],$this->_company_id,'name,phone,company');
		}

		if(strlen($selectCluePage->show()) > 16) $this->assign('selectCluePage', $selectCluePage->show()); // 赋值分页输出

		$this->assign('selectClue',$selectCluelist);

		$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$memberRoleArr,'closed'=>0])->field('name,member_id,group_id')->select();

		$commentFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/commentFollow',$this->_member['role_id'],'crm'); //联系记录评论权限

		$export_follow = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'customer/export_follow',$this->_member['role_id'],'crm');   //导出联系记录权限

		$this->assign('isExportFollowAuth',$export_follow);

		$this->assign('isCommentFollowAuth',$commentFollow_id);

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

		$this->assign('cmncate',$cmncate);

		$this->assign('follow',$follow);

		$this->assign('export_list',$export_list);

		$this->assign('nowPage',$nowPage);

		session('followupExportWhere',$field);

		session('ExportOrder',null);

		$this->display();
	}

	public function create_follow($id='',$detailtype='',$sourcetype='customer')
	{
		$localurl = U('followup');

		if($sourcetype == 'clue')
		{
			$id = decrypt($id, 'CLUE');

			$detailtype = decrypt($detailtype, 'CLUE');

			if ($detailtype)
			{
				$localurl = U('clue/detail', ['id' => encrypt($id, 'CLUE'), 'detailtype' => encrypt($detailtype, 'CLUE')]);
			}
		}
		elseif($sourcetype == 'opportunity')
		{
			$id = decrypt($id, 'OPPORTUNITY');

			$detailtype = decrypt($detailtype, 'OPPORTUNITY');

			if ($detailtype)
			{
				$localurl = U('opportunity/detail', ['id' => encrypt($id, 'OPPORTUNITY'), 'detailtype' => encrypt($detailtype, 'OPPORTUNITY')]);
			}
		}
		else
		{
			$id = decrypt($id, 'CUSTOMER');

			$detailtype = decrypt($detailtype, 'CUSTOMER');

			if ($detailtype)
			{
				$localurl = U('detail', ['id' => encrypt($id, 'CUSTOMER'), 'detailtype' => encrypt($detailtype, 'CUSTOMER')]);
			}
		}

		$this->assign('detailtype', $detailtype);

		$this->assign('sourcetype', $sourcetype);

		if(IS_POST)
		{
			$result = D('CrmFollow')->createFollow($id,$this->_company_id,$this->member_id,$sourcetype);

			if($result['status'] !== 0)
			{
				$result['url'] = $localurl;
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$cmncate_field = getCrmLanguageData('cmncate_name');

			$cmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

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

			//线索列表
			$getClueAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,'','clue');

			$clueMemberRoleArr = $getClueAuth['memberRoleArr'];

			$selectClueCount =  getCrmDbModel('Clue')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$clueMemberRoleArr])->count();

			$selectCluePage = new Page($selectClueCount, 10);

			$selectCluelist =  getCrmDbModel('Clue')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$clueMemberRoleArr])->field('clue_id,clue_prefix,clue_no,createtime')->limit($selectCluePage->firstRow, $selectCluePage->listRows)->order('createtime desc')->select();

			foreach($selectCluelist as $key => &$val)
			{
				$val['detail'] = CrmgetCrmDetailList('clue',$val['clue_id'],$this->_company_id,'name,phone,company');
			}

			if(strlen($selectCluePage->show()) > 16) $this->assign('selectCluePage', $selectCluePage->show()); // 赋值分页输出

			$this->assign('selectClue',$selectCluelist);

			$this->assign('cmncate',$cmncate);

			$this->display();
		}
	}

	public function edit_follow($id,$detailtype='')
	{
		$follow_id = decrypt($id,'FOLLOW');

		$followup = getCrmDbModel('followup')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'follow_id'=>$follow_id])->find();

		if(!$followup)
		{
			$this->common->_empty();die;
		}

		$localurl = U('followup');

		if($followup['clue_id'] > 0)
		{
			$detailsource = $sourcetype = 'clue';

			if(decrypt($detailtype,'CLUE'))
			{
				$detailtype = decrypt($detailtype,'CLUE');

				$localurl = U('clue/detail',['id'=>encrypt($followup['clue_id'],'CLUE'),'detailtype'=>encrypt($detailtype,'CLUE')]);

				$this->assign('detailtype',$detailtype);
			}
			elseif(decrypt($detailtype,'CUSTOMER'))
			{
				$detailsource = 'customer';

				$detailtype = decrypt($detailtype,'CUSTOMER');

				$customer_id = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'clue_id'=>$followup['clue_id']])->getField('customer_id');

				$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

				$this->assign('detailtype',$detailtype);
			}
		}
		elseif($followup['opportunity_id'] > 0)
		{
			$detailsource = $sourcetype = 'opportunity';

			if(decrypt($detailtype,'OPPORTUNITY'))
			{
				$detailtype = decrypt($detailtype,'OPPORTUNITY');

				$localurl = U('opportunity/detail',['id'=>encrypt($followup['opportunity_id'],'OPPORTUNITY'),'detailtype'=>encrypt($detailtype,'OPPORTUNITY')]);

				$this->assign('detailtype',$detailtype);
			}
			elseif(decrypt($detailtype,'CUSTOMER'))
			{
				$detailsource = 'customer';

				$detailtype = decrypt($detailtype,'CUSTOMER');

				$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$followup['opportunity_id']])->getField('customer_id');

				$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

				$this->assign('detailtype',$detailtype);
			}
		}
		else
		{
			$detailsource = $sourcetype = 'customer';

			$detailtype = decrypt($detailtype,'CUSTOMER');

			if($detailtype)
			{
				$localurl = U('detail',['id'=>encrypt($followup['customer_id'],'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

				$this->assign('detailtype',$detailtype);
			}
		}

		$this->assign('detailsource',strtoupper($detailsource));

		if(IS_POST)
		{
			$result = D('CrmFollow')->editFollow($follow_id,$this->_company_id,$followup,$this->member_id,$sourcetype);

			if($result['status'] !== 0)
			{
				$result['url'] = $localurl;
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$cmncate_field = getCrmLanguageData('cmncate_name');

			$cmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			if($followup['cmncate_id'] && $followup['reply_id'])
			{
				$reply_field = getCrmLanguageData('reply_content');

				$reply = getCrmDbModel('communicate_reply')->field(['*',$reply_field])->where(['company_id'=>$this->_company_id,'cmncate_id'=>$followup['cmncate_id'],'closed'=>0])->select();

				$this->assign('reply',$reply);
			}

			if($followup['customer_id'] > 0 || $followup['opportunity_id'] > 0)
			{
				$fieldcontacter['company_id'] = $this->_company_id;

				$fieldcontacter['isvalid'] = '1';

				if($followup['customer_id'])
				{
					$fieldcontacter['customer_id'] = $followup['customer_id'];
				}
				else
				{
					$fieldcontacter['customer_id'] = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$followup['opportunity_id']])->getField('customer_id');
				}

				$contacter = getCrmDbModel('contacter')->where($fieldcontacter)->select();

				foreach ($contacter as $k => &$v)
				{
					$v['detail'] = CrmgetCrmDetailList('contacter', $v['contacter_id'], $this->_company_id);
				}

				$this->assign('contacter', $contacter);
			}

			//附件
			$followup['createFiles'] = getCrmDbModel('upload_file')->where(['company_id'=>$this->_company_id,'follow_id'=>$followup['follow_id'],'file_form'=>'follow'])->select();

			$followup['qiniu_domain'] = M('qiniu')->where(['company_id'=>$this->_company_id])->getField('domain');

			$this->assign('followup',$followup);

			$this->assign('cmncate',$cmncate);

			$this->display();
		}
	}

	public function commentFollow()
	{
		$comment = $this->checkComment();

		$follow_member_id = $comment['follow_member_id'];

		$follow_content = getCrmDbModel('followup')->where(['company_id'=>$this->_company_id,'follow_id'=>$comment['follow_id'],'isvalid'=>1])->getField('content');

		$follow_content = mb_substr(strip_tags(htmlspecialchars_decode($follow_content)),0,200);

		unset($comment['follow_member_id']);

		if($comment_id = getCrmDbModel('follow_comment')->add($comment))
		{
			D('CrmCreateMessage')->createMessage(14,$this->_sms,$follow_member_id,$this->_company_id,$this->member_id,0,0,0,0,0,0,0,$comment_id);

			D('CrmLog')->addCrmLog('comment',1,$this->_company_id,$this->member_id,0,0,0,0,$follow_content,$comment['follow_id']);

			$result = ['errcode'=>0,'msg'=>L('COMMENT_SUCCESS')];
		}
		else
		{
			$result = ['errcode'=>1,'msg'=>L('COMMENT_FAILED')];
		}

		$this->ajaxReturn($result);
	}

	public function checkComment()
	{
		$data = checkFields(I('post.comment'),$this->commentFields);

		$where = ['company_id'=>$this->_company_id,'follow_id'=>$data['follow_id'],'isvalid'=>1];

		$follow_member_id = 0;

		if(!$follow_member_id = getCrmDbModel('followup')->where($where)->getField('member_id'))
		{
			$this->ajaxReturn(['errcode'=>1,'msg'=>L('CONTACT_RECORD_DOES_NOT_EXIST')]);
		}
		elseif(empty(trim($data['content'])))
		{
			$this->ajaxReturn(['errcode'=>1,'msg'=>L('ENTER_COMMENT_CONTENT')]);
		}
		else
		{
			$data['company_id'] = $this->_company_id;

			$data['member_id'] = $this->member_id;

			$data['createtime'] = NOW_TIME;

			$data['follow_member_id'] = $follow_member_id;

			$data['content'] = trim($data['content']);
		}

		return $data;
	}

	public function delete_comment($id,$comment_id,$detailtype='',$sourcetype='customer')
	{
		if(IS_AJAX)
		{
			$localurl = U('followup');

			if($sourcetype == 'clue')
			{
				$clue_id = decrypt($id,'CLUE');

				if(decrypt($detailtype,'CLUE'))
				{
					$detailtype = decrypt($detailtype,'CLUE');

					$localurl = U('clue/detail',['id'=>encrypt($clue_id,'CLUE'),'detailtype'=>encrypt($detailtype,'CLUE')]);
				}
				elseif(decrypt($detailtype,'CUSTOMER'))
				{
					$detailtype = decrypt($detailtype,'CUSTOMER');

					$customer_id = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'clue_id'=>$clue_id])->getField('customer_id');

					$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);
				}
			}
			elseif($sourcetype == 'opportunity')
			{
				$opportunity_id = decrypt($id,'OPPORTUNITY');

				if(decrypt($detailtype,'OPPORTUNITY'))
				{
					$detailtype = decrypt($detailtype,'OPPORTUNITY');

					$localurl = U('opportunity/detail',['id'=>encrypt($opportunity_id,'OPPORTUNITY'),'detailtype'=>encrypt($detailtype,'OPPORTUNITY')]);
				}
				elseif(decrypt($detailtype,'CUSTOMER'))
				{
					$detailtype = decrypt($detailtype,'CUSTOMER');

					$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id])->getField('customer_id');

					$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);
				}
			}
			else
			{
				$customer_id = decrypt($id,'CUSTOMER');

				$detailtype = decrypt($detailtype,'CUSTOMER');

				if($detailtype)
				{
					$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);
				}
			}

			$comment_id = decrypt($comment_id,'COMMENT');

			$where = ['comment_id'=>$comment_id,'company_id'=>$this->_company_id,'isvalid'=>1];

			if($follow_id = getCrmDbModel('follow_comment')->where($where)->getField('follow_id'))
			{
				if(getCrmDbModel('follow_comment')->where($where)->save(['isvalid'=>0]))
				{
					$follow_content = getCrmDbModel('followup')->where(['company_id'=>$this->_company_id,'follow_id'=>$follow_id,'isvalid'=>1])->getField('content');

					D('CrmLog')->addCrmLog('comment',3,$this->_company_id,$this->member_id,0,0,0,0,$follow_content,$follow_id);

					$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>$localurl];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('PARAMETER_ERROR')];
			}

			$this->ajaxReturn($result);

		}
		else
		{
			$this->common->_empty();
		}
	}

	public function delete_follow($id='',$follow_id='',$detailtype='')
	{
		if(IS_AJAX)
		{
			$recover_follow = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'recover/follow',$this->_member['role_id'],'crm');

			$localurl = U('followup');

			$ids = I('post.ids');

			$result = [];

			if($ids && count($ids) > 0)
			{
				foreach($ids as $k=>$v)
				{
					$follow_id = decrypt($v,'FOLLOW');

					$where = ['company_id'=>$this->_company_id,'follow_id'=>$follow_id];

					$follow = getCrmDbModel('followup')->where($where)->find();

					if(!$follow)
					{
						$this->ajaxReturn(['status' => 0, 'msg' => L('CONTACT_RECORD_DOES_NOT_EXIST')]);
					}

					if($follow['isvalid'] == 1)
					{
						$delete = getCrmDbModel('followup')->where($where)->save(['isvalid'=>0]);
					}
					else
					{
						if ($recover_follow) $localurl = U('Recover/follow');

						$delete = getCrmDbModel('followup')->where($where)->delete();

						if($delete)
						{
							getCrmDbModel('follow_comment')->where($where)->delete();
						}
					}

					if($delete)
					{
						if($follow['clue_id'] > 0)
						{
							$clue_detail = CrmgetCrmDetailList('clue', $follow['clue_id'], $this->_company_id,'name');

							D('CrmLog')->addCrmLog('follow', 3, $this->_company_id, $this->member_id, 0, 0, 0, 0, $clue_detail['name'], $follow_id,0,0,0,0,0,0,0,$follow['clue_id']);
						}
						elseif($follow['opportunity_id'] > 0)
						{
							$opportunity_detail = CrmgetCrmDetailList('opportunity', $follow['opportunity_id'], $this->_company_id,'name');

							D('CrmLog')->addCrmLog('follow', 3, $this->_company_id, $this->member_id, 0, 0, 0, 0, $opportunity_detail['name'], $follow_id,0,0,0,0,0,0,0,0,$follow['opportunity_id']);
						}
						else
						{
							$customer_detail = CrmgetCrmDetailList('customer', $follow['customer_id'], $this->_company_id,'name');

							D('CrmLog')->addCrmLog('follow', 3, $this->_company_id, $this->member_id, $follow['customer_id'], 0, 0, 0, $customer_detail['name'], $follow_id);
						}

						$result = ['status' => 2, 'msg' => L('DELETE_SUCCESS'), 'url' => $localurl];
					}
					else
					{
						$result = ['status' => 0, 'msg' => L('DELETE_FAILED')];
					}
				}
			}
			else
			{
				$follow_id = decrypt($follow_id, 'FOLLOW');

				$where = ['company_id' => $this->_company_id, 'isvalid' => '1', 'follow_id' => $follow_id];

				if ($follow = getCrmDbModel('followup')->where($where)->find())
				{
					if($follow['clue_id'] > 0)
					{
						if(decrypt($detailtype,'CLUE'))
						{
							$clue_id = decrypt($id, 'CLUE');

							$detailtype = decrypt($detailtype,'CLUE');

							$localurl = U('clue/detail',['id'=>encrypt($clue_id,'CLUE'),'detailtype'=>encrypt($detailtype,'CLUE')]);
						}
						elseif(decrypt($detailtype,'CUSTOMER'))
						{
							$detailtype = decrypt($detailtype,'CUSTOMER');

							$customer_id = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'clue_id'=>$follow['clue_id']])->getField('customer_id');

							$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);
						}
					}
					elseif($follow['opportunity_id'] > 0)
					{
						if(decrypt($detailtype,'OPPORTUNITY'))
						{
							$opportunity_id = decrypt($id, 'OPPORTUNITY');

							$detailtype = decrypt($detailtype,'OPPORTUNITY');

							$localurl = U('opportunity/detail',['id'=>encrypt($opportunity_id,'OPPORTUNITY'),'detailtype'=>encrypt($detailtype,'OPPORTUNITY')]);
						}
						elseif(decrypt($detailtype,'CUSTOMER'))
						{
							$detailtype = decrypt($detailtype,'CUSTOMER');

							$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$follow['opportunity_id']])->getField('customer_id');

							$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);
						}
					}
					else
					{
						$customer_id = decrypt($id, 'CUSTOMER');

						$detailtype = decrypt($detailtype, 'CUSTOMER');

						if ($detailtype)
						{
							$localurl = U('detail', ['id' => encrypt($customer_id, 'CUSTOMER'), 'detailtype' => encrypt($detailtype, 'CUSTOMER')]);
						}
					}

					if (getCrmDbModel('followup')->where($where)->save(['isvalid' => 0]))
					{
						if($follow['clue_id'] > 0)
						{
							$clue_detail = CrmgetCrmDetailList('clue', $follow['clue_id'], $this->_company_id,'name');

							D('CrmLog')->addCrmLog('follow', 3, $this->_company_id, $this->member_id, 0, 0, 0, 0, $clue_detail['name'], $follow_id,0,0,0,0,0,0,0,$follow['clue_id']);
						}
						elseif($follow['opportunity_id'] > 0)
						{
							$opportunity_detail = CrmgetCrmDetailList('opportunity', $follow['opportunity_id'], $this->_company_id,'name');

							D('CrmLog')->addCrmLog('follow', 3, $this->_company_id, $this->member_id, 0, 0, 0, 0, $opportunity_detail['name'], $follow_id,0,0,0,0,0,0,0,0,$follow['opportunity_id']);
						}
						else
						{
							$customer_detail = CrmgetCrmDetailList('customer', $customer_id, $this->_company_id,'name');

							D('CrmLog')->addCrmLog('follow', 3, $this->_company_id, $this->member_id, $customer_id, 0, 0, 0, $customer_detail['name'], $follow_id);
						}

						$result = ['status' => 2, 'msg' => L('DELETE_SUCCESS'), 'url' => $localurl];
					}
					else
					{
						$result = ['status' => 0, 'msg' => L('DELETE_FAILED')];
					}
				}
				else
				{
					$result = ['status' => 0, 'msg' => L('PARAMETER_ERROR')];
				}
			}

			$this->ajaxReturn($result);
		}
		else
		{
			$this->common->_empty();
		}
	}


	public function wechatmessage()
	{
		$wechat_form_id = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>'contacter','form_name'=>'wechat'])->getField('form_id');

		$wechats = getCrmDbModel('phone_listfriend')->where(['company_id'=>$this->_company_id])->field('wechatid,apiid,wechataccountid,alias,nickname,avatar,conremark')->select();

		foreach($wechats as $key=>&$val)
		{
			if(!$val['alias'])
			{
				$val['alias'] = $val['wechatid'];
			}
			$contacter = getCrmDbModel('contacter')
				->alias('a')
				->join(C('CRM_DB_CONFIG.DB_NAME').'.'.C('CRM_DB_CONFIG.DB_PREFIX').'contacter_detail as b on a.contacter_id = b.contacter_id','left')
				->where(['a.company_id'=>$this->_company_id,'a.isvalid'=>1,'b.form_id'=>$wechat_form_id,'b.form_content'=>["like","%".$val['alias']."%"]])
				->field('a.contacter_id,a.customer_id,b.form_content')
				->find();

			$val['contacter_detail'] = CrmgetCrmDetailList('contacter',$contacter['contacter_id'],$this->_company_id,'name');

			$customer_member_id = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$contacter['customer_id'],'isvalid'=>1])->getField('member_id');

			$val['customer_member'] = M('member')->where(['company_id'=>$this->_company_id,'member_id'=>$customer_member_id,'type'=>1])->getField('name');

			$customer_detail = CrmgetCrmDetailList('customer',$contacter['customer_id'],$this->_company_id,'name');

			$val['customer_name'] = $customer_detail['name'];

			$val['customer_id'] = $contacter['customer_id'];

			$wechat_friendmessage = getCrmDbModel('phone_listfriendmessage')->where(['company_id'=>$this->_company_id,'wechatfriendid'=>$val['apiid']])->field('content,issend,createtime')->select();

			$val['wechat_count_friend'] = getCrmDbModel('phone_listfriendmessage')->where(['company_id'=>$this->_company_id,'wechatfriendid'=>$val['apiid'],'issend'=>0])->field('content,issend,createtime')->count();

			$val['wechat_count_member'] = getCrmDbModel('phone_listfriendmessage')->where(['company_id'=>$this->_company_id,'wechatfriendid'=>$val['apiid'],'issend'=>1])->field('content,issend,createtime')->count();

			$val['wechat_friendmessage'] = $wechat_friendmessage;

			$wechat_member = getCrmDbModel('phone_listwechat')->where(['company_id'=>$this->_company_id,'apiid'=>$val['wechataccountid']])->field('wechatid,apiid,nickname,alias,avatar')->find();

			$val['member_wechatid'] = $wechat_member['wechatid'];

			$val['member_nickname'] = $wechat_member['nickname'];

			$val['member_alias'] = $wechat_member['alias'];

			$val['member_avatar'] = $wechat_member['avatar'];

			if(!$wechat_friendmessage)
			{
				unset($wechats[$key]);
			}
		}

		$this->assign('wechats',$wechats);

		$this->display();
	}

	public function createFeeldesk($id='')
	{
		if($this->_company['ticket_auth'] != 10)
		{
			$result = ['status'=>0,'msg'=>L('PAGE_FAULT')];

			$this->ajaxReturn($result);
		}

		$ids = I('post.customer_ids');

		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		$result = [];

		if($ids && count($ids) > 0)
		{
			foreach($ids as $k=>$v)
			{
				$customer_id = decrypt($v,'CUSTOMER');

				$field['customer_id'] = $customer_id;

				if(!getCrmDbModel('customer')->where($field)->getField('ticket_customer_id'))
				{
					$data = $this->checkCreateFeeldesk($customer_id);

					$data['member']['is_first_customer'] = 2;

					$crm_source = 'feelcrm';

					$data['member']['crm_id'] = 'crm_'.$crm_source.'_'.$customer_id;

					if($member_id = M('Member')->add($data['member']))
					{
						getCrmDbModel('customer')->where($field)->setField('ticket_customer_id',$member_id); //更改工单账户客户id

//					      创建会员公司、默认角色、权限
						D('MemberReg')->createDefaultRole($this->_company_id,$member_id,true,0,$data['firm']);

						$result = ['status'=>2,'msg'=>L('ADDED_SUCCESSFULLY_USED_LOGIN'),'url'=>U('index')];
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('ADD_FAILED')];
					}

				}
				else
				{
					$result = ['status'=>0,'msg'=>L('CUSTOMER_HAS_ADDED_MEMBER')];
				}

			}

		}else
		{
			if($id)
			{
				$customer_id = decrypt($id,'CUSTOMER');

				$field['customer_id'] = $customer_id;

				if(!getCrmDbModel('customer')->where($field)->getField('ticket_customer_id'))
				{
					$data = $this->checkCreateFeeldesk($customer_id);

					$data['member']['is_first_customer'] = 2;

					$crm_source = 'feelcrm';

					$data['member']['crm_id'] = 'crm_'.$crm_source.'_'.$customer_id;

					if($member_id = M('Member')->add($data['member']))
					{
						getCrmDbModel('customer')->where($field)->setField('ticket_customer_id',$member_id); //更改工单账户客户id

//					      创建会员公司、默认角色、权限
						D('MemberReg')->createDefaultRole($this->_company_id,$member_id,true,0,$data['firm']);

						$result = ['status'=>2,'msg'=>L('ADDED_SUCCESSFULLY_USED_LOGIN'),'url'=>U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt('index','CUSTOMER')])];
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('ADD_FAILED')];
					}

				}
				else
				{
					$result = ['status'=>0,'msg'=>L('CUSTOMER_HAS_ADDED_MEMBER')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('SELECT_CUSTOMER_ADD_MEMBER')];
			}
		}

		$this->ajaxReturn($result);
	}

	public function checkCreateFeeldesk($customer_id)
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		$field['ticket_customer_id'] = 0;

		$field['customer_id'] = $customer_id;

		$customer = getCrmDbModel('customer')->where($field)->find();

		$customer['detail'] = CrmgetCrmDetailList('customer',$customer_id,$this->_company_id,'name,phone,email,address');

		$where['company_id'] = $this->_company_id;

		$where['type'] = 'contacter';

		$where['closed'] = 0;

		$where['form_name'] = array('in','name,phone,email');

		$contacter_form = getCrmDbModel('define_form')->where($where)->field('form_id,form_name')->select();

		foreach($contacter_form as $k2=>&$v2)
		{
			$contacter_detail = getCrmDbModel('contacter_detail')->where(['contacter_id'=>$customer['first_contact_id'],'company_id'=>$this->_company_id,'form_id'=>$v2['form_id']])->find();

			$customer['contacter_'.$v2['form_name']] = $contacter_detail['form_content'];
		}

		if(!$customer['contacter_phone'] && !$customer['contacter_email'] && !$customer['detail']['phone'] && !$customer['detail']['email'])
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('NO_INFO_GENERATE_TICKET_ACCOUNT')]);
		}

		if($customer['detail']['phone'])
		{
			$member['account'] = $customer['detail']['phone'];
		}
		elseif($customer['detail']['email'])
		{
			$member['account'] = $customer['detail']['email'];
		}
		elseif($customer['contacter_phone'])
		{
			$member['account'] = $customer['contacter_phone'];
		}
		else
		{
			$member['account'] = $customer['contacter_email'];
		}

		$member['password'] = '123456';

		$member['name'] = $customer['contacter_name'] ? $customer['contacter_name'] : $customer['detail']['name'];


		$firm['firm_name'] = $customer['detail']['name'];

		$firm['firm_link'] = '';

		$firm['firm_addr'] = $customer['detail']['address'];

		$result = D('Member')->VerifyCreateData($member,2,$this->_company_id,$firm);

		if($result['status'] === 0) $this->ajaxReturn($result);

		return $result;
	}

	public function to_export()
	{
		$tableHeader = session('CustomerExcelData')['header'];

		$letter      = session('CustomerExcelData')['letter'];

		$excelData   = session('CustomerExcelData')['excelData'];

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

		session('CustomerExcelData',$excel);

		$this->ajaxReturn(['msg'=>'success']);
	}

	public function export($action = '')
	{
		if(IS_AJAX)
		{
			$exportData = $tableHeader = [];

			if($action == 'index')
			{
				$export_field = C('EXPORT_CUSTOMER_INDEX_FIELD');

				$export_arr = C('EXPORT_CUSTOMER_INDEX_ARR');

				$where = session('customerExportWhere');

				$pagecount = 20;

				$data = D('CrmExport')->getDataList($where,$pagecount,'customer');

				if($data['msg'])
				{
					$this->ajaxReturn(['msg'=>$data['msg']]);die;
				}
				else
				{
					$customer = $data['data'];
				}

				$exportList = $data['exportList'];

				$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'customer',$exportList);

				$show_list = $export_list['show_list'];

				$tableHeader = $export_list['tableHeader'];

				$exportData = D('CrmExport')->getCustomerList($customer,$this->_company_id,$exportList,$show_list);
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
				$export_field = C('EXPORT_CUSTOMER_POOL_FIELD');

				$export_arr = C('EXPORT_CUSTOMER_POOL_ARR');

				$where = session('poolExportWhere');

				$pagecount = 20;

				$data = D('CrmExport')->getDataList($where,$pagecount,'customer');

				if($data['msg'])
				{
					$this->ajaxReturn(['msg'=>$data['msg']]);die;
				}
				else
				{
					$customer = $data['data'];
				}

				$exportList = $data['exportList'];

				$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'customer',$exportList);

				$show_list = $export_list['show_list'];

				$tableHeader = $export_list['tableHeader'];

				$exportData = D('CrmExport')->getCustomerList($customer,$this->_company_id,$exportList,$show_list);
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

	public function export_contacter($action = '')
	{
		if(IS_AJAX)
		{
			$exportData = $tableHeader = [];

			if($action == 'contacter')
			{
				$export_field = C('EXPORT_CONTACTER_FIELD');

				$export_arr = C('EXPORT_CONTACTER_ARR');

				$where = session('contacterExportWhere');

				$pagecount = 20;

				$data = D('CrmExport')->getDataList($where,$pagecount,'contacter');

				if($data['msg'])
				{
					$this->ajaxReturn(['msg'=>$data['msg']]);die;
				}
				else
				{
					$contacter = $data['data'];
				}

				$exportList = $data['exportList'];

				$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'contacter',$exportList);

				$show_list = $export_list['show_list'];

				$tableHeader = $export_list['tableHeader'];

				$exportData = D('CrmExport')->getContacterList($contacter,$this->_company_id,$exportList,$show_list);

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

	public function export_follow($action = '')
	{
		if(IS_AJAX)
		{
			$exportData = $tableHeader = [];

			if($action == 'followup')
			{
				$export_field = C('EXPORT_FOLLOW_FIELD');

				$export_arr = C('EXPORT_FOLLOW_ARR');

				$where = session('followupExportWhere');

				$pagecount = 20;

				$data = D('CrmExport')->getDataList($where,$pagecount,'followup');

				if($data['msg'])
				{
					$this->ajaxReturn(['msg'=>$data['msg']]);die;
				}
				else
				{
					$follow = $data['data'];
				}

				$exportList = $data['exportList'];

				$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'followup',$exportList);

				$show_list = $export_list['show_list'];

				$tableHeader = $export_list['tableHeader'];

				$exportData = D('CrmExport')->getFollowupList($follow,$this->_company_id,$exportList,$show_list);
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

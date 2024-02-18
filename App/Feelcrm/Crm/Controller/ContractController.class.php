<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

use Think\Cache\Driver\Redis;
use Think\Page;

class ContractController extends BasicController
{
	protected $ContractFields = ['member_id','customer_id','opportunity_id','contract_no','contract_prefix'];

	protected $AccountFields = ['customer_id','contract_id','member_id','account_no','account_money','account_time','remark','account_finished'];

	protected $company = [];

	protected $all_view_auth = [];

    protected $group_view_auth = [];

    protected $own_view_auth = [];

	public function _initialize()
    {
        parent::_initialize();

		$this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'contractsAll',$this->_member['role_id'],'crm');

        $this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'contractsGroup',$this->_member['role_id'],'crm');

        $this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'contractsOwn',$this->_member['role_id'],'crm');

		$this->assign('isAllViewAuth',$this->all_view_auth);

        $this->assign('isGroupViewAuth',$this->group_view_auth);

        $this->assign('isOwnViewAuth',$this->own_view_auth);

		$this->_company = M('company')->where(['company_id'=>$this->_company_id])->find();
    }

	public function index()
	{
		$field = ['company_id'=>$this->_company_id,'isvalid'=>'1'];

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,'','contract');

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

		//创建人维度查看合同权限
		$CreaterViewContract = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CreaterViewContract',$this->_member['role_id'],'crm');

		if($CreaterViewContract)
		{
			$field['_string'] = getCreaterViewSql($field['member_id']);

			unset($field['member_id']);

			$this->assign('isCreaterView',$CreaterViewContract);

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

		if(I('get.IsourceName'))
		{
			$origin_customer = CrmgetDefineFormHighField($this->_company_id,'customer','origin',I('get.IsourceName'));

			foreach($origin_customer as $key=>$val)
			{
				if(!getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$val,'isvalid'=>1,'member_id'=>$field['member_id']])->getField('customer_id'))
				{
					unset($origin_customer[$key]);
				}
			}

			$field['customer_id'] = ['in',implode(',',$origin_customer)];
		}

		$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$memberRoleArr,'closed'=>0])->field('name,member_id,group_id')->select();

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword = unserialize(urldecode(I('get.highKeyword')));

			if(!$highKeyword)
			{
				$highKeyword = I('get.highKeyword');

				$_GET['highKeyword'] = urlencode(serialize(I('get.highKeyword')));
			}

			$contractHighKey = D('CrmHighKeyword')->contractHighKey($this->_company_id,$highKeyword);

			if($contractHighKey['field'])
			{
				$field = array_merge($field,$contractHighKey['field']);
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
			$keywordField = CrmgetDefineFormField($this->_company_id,'contract','name',$keyword);

			$keywordCondition['contract_id'] = $keywordField ? ['in',$keywordField] : '0';

			$keywordCondition['contract_no'] = ["like","%".$keyword."%"];

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			$this->assign('keyword', $keyword);
		}

		$nowPage = I('get.p');

		$count = getCrmDbModel('contract')->where($field)->count();

		$Page = new Page($count, 20);

		$this->assign('pageCount', $count);

		//$Page->totalRows;
        if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$contract = getCrmDbModel('contract')->where($field)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

		$show_list = CrmgetShowListField('contract',$this->_company_id); //合同列表显示字段

		$export_field = C('EXPORT_CONTRACT_FIELD');

		$export_arr = C('EXPORT_CONTRACT_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'contract');

		foreach($contract as $key=>&$val)
		{
			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

			$val['member_name'] = $thisMember['name'];

			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

			$val['create_name'] = $createMember['name'];

			$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name');

			$val['customer_name'] = $customer_detail['name'];

			$val['detail'] = CrmgetCrmDetailList('contract',$val['contract_id'],$this->_company_id,$show_list['form_name']);

			$val['receipt_money'] = getCrmDbModel('receipt')->where(['company_id'=>$this->_company_id,'customer_id'=>$val['customer_id'],'contract_id'=>$val['contract_id'],'isvalid'=>1,'status'=>2])->sum('receipt_money');

			$val['receipt_money'] = $val['receipt_money'] ? $val['receipt_money'] : '0.00';

			$val['uncollected_money'] = sprintf("%1\$.2f",$val['detail']['money'] - $val['receipt_money']);
		}

		$form_description = getCrmLanguageData('form_description');

		//查询筛选项
		$filterlist = getCrmDbModel('define_form')
			->field(['*',$form_description])
			->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contract','form_name'=>['in','sign_time,start_time,end_time']])
			->order('orderby asc')
			->select();

		$this->assign('filterlist',$filterlist);

		$defineformlist = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contract','form_type'=>['neq','textarea']])->order('orderby asc')->select();

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

		$deleteContractAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'contract/delete',$this->_member['role_id'],'crm'); //删除合同权限

		$exportContractAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'contract/export',$this->_member['role_id'],'crm'); //导出合同权限

		$importContractAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'contract/import',$this->_member['role_id'],'crm'); //导入合同权限

		if($importContractAuth)
		{
			$importTemp = getCrmDbModel('temp_file')->where(['company_id'=>$this->_company_id,'type'=>'contract'])->getField('file_link');

			$this->assign('importTemp',$importTemp);
		}

		$this->assign('isExportContractAuth',$exportContractAuth);

		$this->assign('isImportContractAuth',$importContractAuth);

        $this->assign('isDelContractAuth',$deleteContractAuth);

		$this->assign('contract',$contract);

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

		session('contractExportWhere',$field);

		session('ExportOrder',null);

		$this->display();

	}

	public function detail($id,$detailtype='',$detail_source = 'crm')
	{
		$contract_id = decrypt($id,'CONTRACT');

		if($detailtype)
		{
			$detailtype = decrypt($detailtype,'CONTRACT');

			$this->assign('detailtype',$detailtype);
		}
		else
		{
			$detailtype = 'index';

			$this->assign('detailtype','index');
		}

		$this->assign('detail_source',$detail_source);

		$contract = getCrmDbModel('contract')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id])->find();

		if(!$contract)
		{
			$this->common->_empty();

			die;
		}

		$isDetailAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'ContractDetail',$this->_member['role_id'],'crm'); //合同详情权限

		$isProductAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'ContractProduct',$this->_member['role_id'],'crm'); //合同产品权限

		$this->assign('isDetailAuthView',$isDetailAuthView);

		$this->assign('isProductAuthView',$isProductAuthView);

		$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$contract['member_id']])->field('member_id,account,name,group_id')->find();

		$contract['member_name'] = $thisMember['name'];

		$contract['group_id'] = $thisMember['group_id'];

		$form_description = getCrmLanguageData('form_description');

		$contractform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contract','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		$contract['detail'] = CrmgetCrmDetailList('contract',$contract_id,$this->_company_id);

		$contractform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contract','form_type'=>['eq','textarea']])->order('orderby asc')->select();

		$this->assign('contractform',$contractform);

		$this->assign('contractform2',$contractform2);

		if(!$detailtype || $detailtype =='index')
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
				$crmlog = D('CrmLog')->getCrmLog('contract', $contract_id, $this->_company_id);

				$result = ['data'=>$crmlog['crmlog'],'type'=>encrypt('index','CONTRACT'),'pages'=>ceil($crmlog['count']/10)];

				$this->ajaxReturn($result);
			}
			else
			{
				$createMember = M('Member')->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 1, 'member_id' => $contract['creater_id']])->field('member_id,account,name')->find();

				$contract['creater_name'] = $createMember['name'];

				$contract['contract_img'] = json_decode($contract['contract_img']);

				$uploadFiles = getCrmDbModel('upload_file')->where(['company_id' => $this->_company_id, 'contract_id' => $contract['contract_id'], 'file_form' => 'contract'])->select();

				$contract['createFiles'] = $uploadFiles;

				$customer = getCrmDbModel('customer')->where(['company_id' => $this->_company_id, 'isvalid' => 1, 'customer_id' => $contract['customer_id']])->find();

				$customer['detail'] = CrmgetCrmDetailList('customer', $contract['customer_id'], $this->_company_id, 'name');

				$crmlog = D('CrmLog')->getCrmLog('contract', $contract_id, $this->_company_id);

				$this->assign('crmlog', $crmlog);

				$group = D('Group')->where(['company_id' => $this->_company_id])->field('group_id,group_name')->fetchAll();

				$this->assign('customer', $customer);

				$this->assign('groupList', $group);

				if($contract['opportunity_id'])
				{
					$opportunity['detail'] = CrmgetCrmDetailList('opportunity', $contract['opportunity_id'], $this->_company_id, 'name');

					$this->assign('opportunity',$opportunity);
				}
			}
		}

		if($detailtype =='product')
		{
			if(!$isProductAuthView)
			{
				$this->common->_empty();

				die;
			}

			if($detail_source == 'ticket' && !$isDeskProductAuthView)
			{
				$this->common->_empty();

				die;
			}

			$productfield = ['company_id'=>$this->_company_id,'contract_id'=>$contract_id];

			$count = getCrmDbModel('contract_product')->where($productfield)->count();

			$Page = new Page($count, 20);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$product = getCrmDbModel('contract_product')->where($productfield)->limit($Page->firstRow, $Page->listRows)->select();

			foreach($product as $k8=>&$v8)
			{
				$thisProduct = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'product_id'=>$v8['product_id']])->field('product_img')->find();

				$v8['product_img'] = $thisProduct['product_img'];

				$v8['detail'] = CrmgetCrmDetailList('product',$v8['product_id'],$this->_company_id);
			}

			$this->assign('product',$product);
		}

		$this->assign('contract',$contract);

		$this->display();
	}

	public function create($id='',$detailtype='',$jump = '')
	{
		$localurl = U('index');

		if(decrypt($detailtype,'CUSTOMER'))
		{
			$customer_id = decrypt($id,'CUSTOMER');

			$detailtype = decrypt($detailtype,'CUSTOMER');

			$localurl = U('Customer/detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

			$this->assign('customer_id',$customer_id);

			$opportunity = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>1])->field('opportunity_id')->select();

			foreach ($opportunity as $key=>&$val)
			{
				$val['detail'] = CrmgetCrmDetailList('opportunity',$val['opportunity_id'],$this->_company_id,'name');
			}

			$this->assign('opportunity',$opportunity);

			$this->assign('detailtype',$detailtype);
		}
		elseif(decrypt($detailtype,'OPPORTUNITY'))
		{
			$opportunity_id = decrypt($id,'OPPORTUNITY');

			$detailtype = decrypt($detailtype,'OPPORTUNITY');

			$localurl = U('Opportunity/detail',['id'=>encrypt($opportunity_id,'OPPORTUNITY'),'detailtype'=>encrypt($detailtype,'OPPORTUNITY')]);

			$customer_id = getCrmDbModel('Opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'isvalid'=>1])->getField('customer_id');

			$contractPro = getCrmDbModel('opportunity_product')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id])->select();

			$product_type_field = getCrmLanguageData('type_name');

			foreach($contractPro as $k1=>&$v1)
			{
				$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'product_id'=>$v1['product_id'],'isvalid'=>'1'])->find();

				$product_detail = CrmgetCrmDetailList('product',$v1['product_id'],$this->_company_id,'name,list_price');

				$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'type_id'=>$product['type_id']])->find();

				$v1['product_name'] = $product_detail['name'];

				$v1['list_price'] = number_format($product_detail['list_price'],2);

				$v1['type_name'] = $product_type['type_name'];
			}

			$this->assign('contractPro',$contractPro);

			$this->assign('customer_id',$customer_id);

			$this->assign('opportunity_id',$opportunity_id);

			$this->assign('detailtype',$detailtype);
		}

		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contract'];

		if(IS_POST)
		{
			$data = $this->checkCreate();

			if($contract_id = getCrmDbModel('contract')->add($data['contract']))//添加合同
            {
				foreach($data['contract_detail'] as &$v)
                {
                    $v['contract_id'] = $contract_id;

                    $v['company_id'] = $this->_company_id;

                    if(is_array($v['form_content']))
                    {
                        $v['form_content'] = implode(',',$v['form_content']);
                    }

                    getCrmDbModel('contract_detail')->add($v); //添加合同详情
                }

				// 储存上传文件信息
				$files = isset($_POST['file']) ? I('post.file') : [];

				$this->saveUploadFile($files,$this->_company_id,$contract_id,'contract');

				D('CrmLog')->addCrmLog('contract',1,$this->_company_id,$this->member_id,$data['contract']['customer_id'],0,0,0,$data['contract_detail']['name']['form_content'],0,0,0,0,$contract_id,0,0,0,0,$data['contract']['opportunity_id']);

				foreach($data['contractPro'] as &$v2)
				{
					$v2['contract_id'] = $contract_id;

					getCrmDbModel('contract_product')->add($v2); //添加合同产品
				}

                getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$data['contract']['customer_id'],'isvalid'=>1,'is_trade'=>0])->save(['is_trade'=>1]);

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

			$contractform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($contractform as $k=>&$v)
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


			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$this->member_id])->field('member_id,account,name')->find();

            $members = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'feelec_opened'=>10])->field('member_id,account,name')->select();

			foreach($members as $key=>&$val)
			{
				$val['id'] = $val['member_id'];
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

            $this->assign('thisMember',$thisMember);

            $this->assign('member',$members);

            $this->assign('members',json_encode($members));

			$this->assign('contractform',$contractform);

			$this->display();
		}
	}

	public function checkCreate()
	{
		$contract = checkFields(I('post.contract'), $this->ContractFields);

		if(!$contract['customer_id'])
		{
			 $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_SELECT_CUSTOMER')]);
		}

		if(!$contract['member_id'])
		{
			 $this->ajaxReturn(['status'=>0,'msg'=>L('SELECT_LEADER')]);
		}

		$contract['company_id'] = $this->_company_id;//所属公司ID

        $contract['createtime'] = NOW_TIME;

        $contract['creater_id'] = $this->member_id;

		$contract['entry_method'] = 'CREATE';

		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$this->_company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		if($crmsite['contractCode'])
		{
			$contract['contract_prefix'] = $crmsite['contractCode'];
		}
		else
		{
			$contract['contract_prefix'] = 'H-';
		}

		$contract['contract_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$this->_company_id), rand(0,9), 4));

		$contract_form = I('post.contract_form');

		$ContractCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$contract_form,'contract',$this->_member);

		if($ContractCheckForm['detail'])
		{
			$contract_detail = $ContractCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ContractCheckForm);
		}

		$contract['status'] = 2;

		$photo = I('post.photo');

		if($photo)
		{
			$contract['contract_img'] = json_encode($photo);
		}

		$contractPro = I('post.contractPro');

		if(!$contractPro) $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_ADD_PRODUCT_FIRST')]);

		foreach($contractPro as $key=>&$val)
		{
			if(!$val['unit_price']) $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_FILL_IN_THE_PRICE')]);

			if(!$val['num']) $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_FILL_IN_THE_QUANTITY')]);

			$val['product_id'] = decrypt($val['product_id'],'PRODUCT');

			$val['customer_id'] = $contract['customer_id'];

			$val['company_id'] = $this->_company_id;
		}

		return ['contract'=>$contract,'contract_detail'=>$contract_detail,'contractPro'=>$contractPro];

	}

	public function saveUploadFile($files = [],$company_id=0,$contract_id=0,$source = 'contract')
    {
        if(!empty($files))
        {
            $file = [];

            foreach($files['links'] as $k=>$v)
            {
                $file[$k]['company_id'] = $company_id;

                $file[$k]['contract_id']  = $contract_id;

                $file[$k]['file_link']  = $v;

                $file[$k]['save_name']  = $files['saves'][$k];

                $file[$k]['file_name']  = $files['names'][$k];

                $file[$k]['file_size']  = $files['sizes'][$k];

                $file[$k]['file_type']  = $files['types'][$k];

                $file[$k]['file_form']  = $source;

                $file[$k]['create_time'] = NOW_TIME;
            }

            getCrmDbModel('upload_file')->addAll($file);
        }
    }


	public function edit($id,$detailtype="")
	{
		$contract_id = decrypt($id,'CONTRACT');

		$detailtype = decrypt($detailtype,'CONTRACT');

		$localurl = U('index');

		if($detailtype)
		{
			$localurl = U('detail',['id'=>encrypt($contract_id,'CONTRACT'),'detailtype'=>encrypt($detailtype,'CONTRACT')]);

			$this->assign('detailtype',$detailtype);
		}

		$contract = getCrmDbModel('contract')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id,'isvalid'=>'1'])->find();

		if(IS_POST)
		{
			$data = $this->checkEdit($contract_id);

			if($contract['status'] == -1)
			{
				$data['contract']['status'] = 1;
			}

			$save = getCrmDbModel('contract')->where(['contract_id'=>$contract_id,'company_id'=>$this->_company_id,'isvalid'=>'1'])->save($data['contract']);

			if($save === false)
			{
			    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
				getCrmDbModel('contract_detail')->where(['contract_id'=>$contract_id,'company_id'=>$this->_company_id])->delete();

				foreach($data['contract_detail'] as &$v)
				{
					$v['contract_id'] = $contract_id;

					$v['company_id'] = $this->_company_id;

					if(is_array($v['form_content']))
					{
						$v['form_content'] = implode(',',$v['form_content']);
					}

					getCrmDbModel('contract_detail')->add($v);  //添加订单详情
				}

				//            更新上传文件信息
				$files = isset($_POST['file']) ? I('post.file') : [];

				$delFiles = isset($_POST['delFile']) ? I('post.delFile') : [];

				$this->updateUploadFile($files,$delFiles,$contract_id,$this->_company_id,'contract');

				D('CrmLog')->addCrmLog('contract',2,$this->_company_id,$this->member_id,$contract['customer_id'],0,0,0,$data['contract_detail']['name']['form_content'],0,0,0,0,$contract_id,0,0,0,0,$contract['opportunity_id']);

				getCrmDbModel('contract_product')->where(['contract_id'=>$contract_id,'company_id'=>$this->_company_id])->delete();

				foreach($data['contractPro'] as &$v2)
				{
					$v2['contract_id'] = $contract_id;

					getCrmDbModel('contract_product')->add($v2); //添加合同产品

				}

				$html = D('CrmContract')->getContractListHtml($this->_company_id,$contract_id);

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl,'html'=>$html];
			}

			$this->ajaxReturn($result);

		}
		else
		{
			$form_description = getCrmLanguageData('form_description');

			$contractform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contract'])->order('orderby asc')->select();

			foreach($contractform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$contract_detail = getCrmDbModel('contract_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'contract_id'=>$contract_id])->find();

				if($v['form_type']=='region')
				{
					if($contract_detail['form_content'])
					{
						$region_detail = explode(',',$contract_detail['form_content']);

						$contract[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$contract[$v['form_name']]['defaultProv'] = $region_detail[1];

						$contract[$v['form_name']]['defaultCity'] = $region_detail[2];

						$contract[$v['form_name']]['defaultArea'] = $region_detail[3];

					}
				}else
				{
					$contract[$v['form_name']] = $contract_detail['form_content'];
				}
			}

			$contract['contract_img'] = json_decode($contract['contract_img']);

			$contractPro = getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id])->select();

			$product_type_field = getCrmLanguageData('type_name');

			foreach($contractPro as $k1=>&$v1)
			{
				$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'product_id'=>$v1['product_id'],'isvalid'=>'1'])->find();

				$productform = getCrmDbModel('define_form')->field('form_id,form_option,form_type,form_name')->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'product'])->order('orderby asc')->select();

				foreach($productform as $k2=>&$v2)
				{
					if(in_array($v2['form_type'],['radio','select','checkbox','select_text']))
					{
						$v2['option'] = explode('|',$v2['form_option']);
					}

					$product_detail = getCrmDbModel('product_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v2['form_id'],'product_id'=>$v1['product_id']])->find();

					$product[$v2['form_name']] = $product_detail['form_content'];

					$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'type_id'=>$product['type_id']])->find();

					$product['type_name'] = $product_type['type_name'];
				}

				$v1['product_name'] = $product['name'];

				$v1['list_price'] = number_format($product['list_price'],2);

				$v1['type_name'] = $product['type_name'];
			}

			$this->assign('contractPro',$contractPro);

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

				$contract_pro = getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id,'product_id'=>$val['product_id']])->find();

				if($contract_pro) $val['contract_pro'] = 1;

			}

			$this->assign('product',$product);

			$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

			$product_type = CrmfetchAll($product_type,'type_id');

			$product_type_h = $this->resultCategory($product_type,count($product_type));

			$this->assign('product_type_h',$product_type_h);



			//合同附件
            $contract['createFiles'] = getCrmDbModel('upload_file')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract['contract_id'],'file_form'=>'contract'])->select();

            $contract['qiniu_domain'] = M('qiniu')->where(['company_id'=>$this->_company_id])->getField('domain');
			//var_dump($contract);die;
			$members = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'feelec_opened'=>10])->field('member_id,account,name')->select();

			foreach ($members as $key=>&$val)
			{
				$val['id'] = $val['member_id'];
			}

			$memberRoleArr = CrmmemberRoleCrmauth($this->_member,$this->_company_id,$this->member_id);

			$this->assign('member',$members);

			$this->assign('members',json_encode($members));

			$this->assign('contractform',$contractform);

			$this->assign('contract',$contract);

			$this->display();
		}
	}

	public function checkEdit($contract_id)
	{
		$contract = checkFields(I('post.contract'), $this->ContractFields);

		if(!$contract['member_id'])
		{
			 $this->ajaxReturn(['status'=>0,'msg'=>L('SELECT_LEADER')]);
		}

		$contract_form = I('post.contract_form');

		$ContractCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$contract_form,'contract',$this->_member,$contract_id);

		if($ContractCheckForm['detail'])
		{
			$contract_detail = $ContractCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ContractCheckForm);
		}

		$photo = I('post.photo');

		if($photo)
		{
			$contract['contract_img'] = json_encode($photo);
		}

		$contractPro = I('post.contractPro');

		if(!$contractPro) $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_ADD_PRODUCT_FIRST')]);

		foreach($contractPro as $key=>&$val)
		{
			if(!$val['unit_price']) $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_FILL_IN_THE_PRICE')]);

			if(!$val['num']) $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_FILL_IN_THE_QUANTITY')]);

			$val['product_id'] = decrypt($val['product_id'],'PRODUCT');

			$val['customer_id'] = decrypt($val['customer_id'],'CUSTOMER');

			$val['company_id'] = $this->_company_id;
		}

		return ['contract'=>$contract,'contract_detail'=>$contract_detail,'contractPro'=>$contractPro];
	}

	public function updateUploadFile($files = [],$delFiles = [],$contract_id=0,$company_id=0,$source = 'contract')
    {
        getCrmDbModel('upload_file')->where(['contract_id'=>$contract_id])->delete();

        if(!empty($files))
        {
            $file = [];

            foreach($files['links'] as $k=>$v)
            {
                $file[$k]['company_id'] = $company_id;

                $file[$k]['contract_id']  = $contract_id;

                $file[$k]['file_link']  = $v;

                $file[$k]['save_name']  = $files['saves'][$k];

                $file[$k]['file_name']  = $files['names'][$k];

                $file[$k]['file_size']  = $files['sizes'][$k];

                $file[$k]['file_type']  = $files['types'][$k];

                $file[$k]['file_form']  = $source;

                $file[$k]['create_time'] = NOW_TIME;
            }

            getCrmDbModel('upload_file')->addAll($file);
        }

        if(!empty($delFiles))
        {
            foreach($delFiles as $v)
            {
                D('Upload')->deleteUploadFile($v);
            }
        }
    }

	public function delete($id='')
	{
		if(IS_AJAX)
	    {
			$ids = I('post.contract_ids');

		    $recover_contract = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'recover/contract',$this->_member['role_id'],'crm');

		    $localurl = U('index');

		    if($ids && count($ids) > 0)
			{
				foreach($ids as $k=>$v)
				{
					$contract_id = decrypt($v,'CONTRACT');

					$where = ['contract_id'=>$contract_id,'company_id'=>$this->_company_id];

					$contract = getCrmDbModel('contract')->where($where)->field('customer_id,isvalid,member_id')->find();

					if(!$contract)
					{
						$this->ajaxReturn(['status'=>0,'msg'=>L('CONTRACT_DOES_NOT_EXIST')]);
					}

					$contract['detail'] = CrmgetCrmDetailList('contract',$contract_id,$this->_company_id);

					if($contract['isvalid'] == 1)
					{
						$delete = getCrmDbModel('contract')->where($where)->save(['isvalid'=>0]);
					}
					else
					{
						if($recover_contract) $localurl = U('Recover/contract');

						$delete = getCrmDbModel('contract')->where($where)->delete();

						if($delete)
						{
							getCrmDbModel('contract_detail')->where($where)->delete();

							getCrmDbModel('contract_product')->where($where)->delete();
						}
					}

					if($delete)
					{
						if($contract['isvalid'] == 1)
						{
							D('CrmLog')->addCrmLog('contract', 3, $this->_company_id, $this->member_id, $contract['customer_id'], 0, 0, 0, $contract['detail']['name'], 0, 0, 0, 0, $contract_id);
						}
						else
						{
							D('CrmLog')->addCrmLog('contract', 15, $this->_company_id, $this->member_id, $contract['customer_id'], 0, 0, 0, $contract['detail']['name'], 0, 0, 0, 0, $contract_id);
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
					$contract_id = decrypt($id,'CONTRACT');

					$where = ['contract_id'=>$contract_id,'company_id'=>$this->_company_id];

					$contract = getCrmDbModel('contract')->where($where)->field('customer_id,isvalid,member_id')->find();

					if(!$contract)
					{
						$this->ajaxReturn(['status'=>0,'msg'=>L('CONTRACT_DOES_NOT_EXIST')]);
					}

					$contract['detail'] = CrmgetCrmDetailList('contract',$contract_id,$this->_company_id);

					if($contract['isvalid'] == 1)
					{
						$delete = getCrmDbModel('contract')->where($where)->save(['isvalid'=>0]);
					}
					else
					{
						if($recover_contract) $localurl = U('Recover/contract');

						$delete = getCrmDbModel('contract')->where($where)->delete();

						if($delete)
						{
							getCrmDbModel('contract_detail')->where($where)->delete();

							getCrmDbModel('contract_product')->where($where)->delete();
						}
					}

					if($delete)
					{
						if($contract['isvalid'] == 1)
						{
							D('CrmLog')->addCrmLog('contract', 3, $this->_company_id, $this->member_id, $contract['customer_id'], 0, 0, 0, $contract['detail']['name'], 0, 0, 0, 0, $contract_id);
						}
						else
						{
							D('CrmLog')->addCrmLog('contract', 15, $this->_company_id, $this->member_id, $contract['customer_id'], 0, 0, 0, $contract['detail']['name'], 0, 0, 0, 0, $contract_id);
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
		$tableHeader = session('ContractExcelData')['header'];

		$letter      = session('ContractExcelData')['letter'];

		$excelData   = session('ContractExcelData')['excelData'];

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

		session('ContractExcelData',$excel);

		$this->ajaxReturn(['msg'=>'success']);
	}

	public function export($action = '')
	{
		if(IS_AJAX)
        {
			$exportData = $tableHeader = [];

			if($action == 'index')
			{
				$export_field = C('EXPORT_CONTRACT_FIELD');

				$export_arr = C('EXPORT_CONTRACT_ARR');

				$where = session('contractExportWhere');

				$pagecount = 20;

				$data = D('CrmExport')->getDataList($where,$pagecount,'contract');

				if($data['msg'])
				{
					$this->ajaxReturn(['msg'=>$data['msg']]);die;
				}
				else
				{
					$contract = $data['data'];
				}

				$exportList = $data['exportList'];

				$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'contract',$exportList);

				$show_list = $export_list['show_list'];

				$tableHeader = $export_list['tableHeader'];

				$exportData = D('CrmExport')->getContractList($contract,$this->_company_id,$exportList,$show_list);
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

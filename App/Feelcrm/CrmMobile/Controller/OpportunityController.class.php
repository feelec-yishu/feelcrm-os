<?php
namespace CrmMobile\Controller;

use CrmMobile\Common\BasicController;

use Think\Cache\Driver\Redis;
use Think\Page;

class OpportunityController extends BasicController
{
	protected $OpportunityFields = ['member_id','customer_id','opportunity_no','opportunity_prefix'];

	protected $company = [];

	public function _initialize()
    {
        parent::_initialize();

		$this->_company = M('company')->where(['company_id'=>$this->_company_id])->find();
    }

    public function index()
    {
        $field = ['company_id'=>$this->_company_id,'isvalid'=>'1'];

		$memberRoleArr = CrmmemberRoleCrmauth($this->_mobile,$this->_company_id,$this->member_id);

		$field['member_id'] = $memberRoleArr;

		if(!$field['member_id'])
		{
			$this->common->_empty();die;
		}

	    //创建人维度查看商机权限
	    $CreaterViewOpportunity = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CreaterViewOpportunity',$this->_mobile['role_id'],'crm');

	    if($CreaterViewOpportunity)
	    {
		    $field['_string'] = getCreaterViewSql($field['member_id']);

		    unset($field['member_id']);
	    }

		$count = getCrmDbModel('Opportunity')->where($field)->count();

		$Page = new Page($count, 10);

        if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$opportunity = getCrmDbModel('Opportunity')->where($field)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

	    $opportunity = D('CrmOpportunity')->getMobileListInfo($opportunity,$this->_company_id);

		// 分页流加载
		if(isset($_GET['request']) && $_GET['request'] == 'flow')
        {
            $result = ['data'=>$opportunity,'pages'=>ceil($count/10)];

            $this->ajaxReturn($result);
        }
		else
		{
			$create_opportunity = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'opportunity/create',$this->_mobile['role_id'],'crm'); //创建商机权限

			$this->assign('isCreateOpportunityAuth',$create_opportunity);

			$this->assign('opportunity',$opportunity);

			$this->display();
		}
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

	    $isDetailAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'OpportunityDetail',$this->_mobile['role_id'],'crm');//商机详情权限

	    $isProductAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'OpportunityProduct',$this->_mobile['role_id'],'crm');//商机产品权限

		$isContractAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'OpportunityContract',$this->_mobile['role_id'],'crm'); //商机合同权限

	    $isFollowAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CustFollow',$this->_mobile['role_id'],'crm');//联系记录权限

		$this->assign('isDetailAuthView',$isDetailAuthView);

		$this->assign('isProductAuthView',$isProductAuthView);

		$this->assign('isContractAuthView',$isContractAuthView);

		$this->assign('isFollowAuthView',$isFollowAuthView);

		$edit_opportunity_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'opportunity/edit',$this->_mobile['role_id'],'crm'); //修改商机权限

		$this->assign('isEditOpportunityAuth',$edit_opportunity_id);

		$opportunity = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'isvalid'=>'1'])->find();

		if(!$opportunity)
		{
			$this->common->_empty();

			die;
		}

		$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$opportunity['member_id']])->field('member_id,account,name,group_id')->find();

	    $opportunity['member_name'] = $thisMember['name'];

	    $opportunity['group_id'] = $thisMember['group_id'];

		$form_description = getCrmLanguageData('form_description');

	    $opportunityform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_type'=>['neq','textarea']])->order('orderby asc')->select();

	    $opportunity['detail'] = CrmgetCrmDetailList('opportunity',$opportunity_id,$this->_company_id);

		$opportunityform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_type'=>['eq','textarea']])->order('orderby asc')->select();

		$this->assign('opportunityform',$opportunityform);

		$this->assign('opportunityform2',$opportunityform2);

		if($isDetailAuthView)
		{
			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$opportunity['creater_id']])->field('member_id,account,name')->find();

			$opportunity['creater_name'] = $createMember['name'];

			$customer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'customer_id'=>$opportunity['customer_id']])->find();

			$customer['detail'] = CrmgetCrmDetailList('customer',$opportunity['customer_id'],$this->_company_id,'name');

			$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

			$this->assign('customer',$customer);

			$this->assign('groupList',$group);

			//失单
			$lose_field = getCrmLanguageData('lose_name');

			$lose = getCrmDbModel('lose')->where(['company_id' => $this->_company_id, 'closed' => 0])->field('lose_id,'.$lose_field)->select();

			//商机分析
			$analysis = getCrmDbModel('analysis')->where(['company_id' => $this->_company_id, 'opportunity_id' => $opportunity_id])->field('analysis_id')->find();

			$analysis_id = $analysis['analysis_id'];

			if ($analysis_id)
			{
				$analysis['detail'] = CrmgetCrmDetailList('analysis', $analysis_id, $this->_company_id);

				$this->assign('analysis_id', $analysis_id);
			}
			else
			{
				$analysis = [];

				$analysis['company_id'] = $this->_company_id;

				$analysis['customer_id'] = $opportunity['customer_id'];

				$analysis['opportunity_id'] = $opportunity_id;

				$analysis['createtime'] = NOW_TIME;

				$analysis['creater_id'] = $this->member_id;

				$analysis_id = getCrmDbModel('analysis')->add($analysis);

				$analysis['analysis_id'] = $analysis_id;
			}

			$analysisform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 'analysis'])->order('orderby asc')->select();

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

			$analysisform1 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 'analysis', 'form_type' => ['neq', 'textarea']])->order('orderby asc')->select();

			$analysisform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id' => $this->_company_id, 'closed' => 0, 'type' => 'analysis', 'form_type' => ['eq', 'textarea']])->order('orderby asc')->select();

			$edit_analysis_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'], 'opportunity/edit_analysis', $this->_mobile['role_id'], 'crm');  //编辑客户分析权限

			$this->assign('isEditAnalysisAuth', $edit_analysis_auth);

			$this->assign('analysis', $analysis);

			$this->assign('analysisform', $analysisform);

			$this->assign('analysisform1', $analysisform1);

			$this->assign('analysisform2', $analysisform2);

			$competitor = getCrmDbModel('competitor')->where(['company_id' => $this->_company_id, 'isvalid' => 1, 'opportunity_id' => $opportunity_id])->order('createtime desc')->select();

			foreach ($competitor as $k10 => &$v10) {
				$v10['detail'] = CrmgetCrmDetailList('competitor', $v10['competitor_id'], $this->_company_id);
			}
			//var_dump($competitor);die;
			$this->assign('competitor', $competitor);

			$competitorform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'competitor'])->order('orderby asc')->select();

			foreach($competitorform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$this->assign('competitorform',$competitorform);

			//查询商机阶段自定义字段
			$opportunityStageForm = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_name'=>'stage'])
				->find();

			$opportunityStageForm = explode('|',$opportunityStageForm['form_option']);

			$this->assign('opportunityStageForm',$opportunityStageForm);

			$this->assign('opportunityStageEnd',$opportunityStageForm[count($opportunityStageForm) - 1]);

			$lose_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'], 'opportunity/lose', $this->_mobile['role_id'], 'crm'); //失单权限

			$CreateCompetitor = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'], 'opportunity/create_competitor', $this->_mobile['role_id'], 'crm'); //添加竞争对手权限

			$this->assign('isLoseCustomerAuth', $lose_customer);

			$this->assign('isCreateCompetitorAuth', $CreateCompetitor);

			$this->assign('loses', $lose);
		}
		else
		{
			$this->common->_empty();

			die;
		}

		$this->assign('opportunity',$opportunity);

	    //联系记录
	    if($isFollowAuthView)
	    {
		    $cmncate_field = getCrmLanguageData('cmncate_name');

		    $cmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

		    $follow = getCrmDbModel('followup')->where(['opportunity_id'=>$opportunity_id,'company_id'=>$this->_company_id,'isvalid'=>1])->order('createtime desc')->select();

		    foreach($follow as $k3=>&$v3)
		    {
			    $member = M('member')->where(['company_id'=>$this->_company_id,'member_id'=>$v3['member_id'],'type'=>1])->field('name,face')->find();

			    $v3['member_name'] = $member['name'];

			    $v3['member_face'] = $member['face'];

			    if($v3['cmncate_id'])
			    {
				    $followCmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['cmncate_id'=>$v3['cmncate_id'],'company_id'=>$this->_company_id])->find();

				    $v3['cmncate_name'] = $followCmncate['cmncate_name'];
			    }

			    $v3['followComment'] = getCrmDbModel('follow_comment')->where(['company_id'=>$this->_company_id,'follow_id'=>$v3['follow_id'],'isvalid'=>1])->order('createtime desc')->select();

			    $v3['countComment'] = count($v3['followComment']);

			    $uploadFiles = getCrmDbModel('upload_file')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'file_form' => 'follow'])->select();

			    $v3['createFiles'] = $uploadFiles;
		    }

		    $members = D('Member')->where(['company_id'=>$this->_company_id,'type'=>1])->field('company_id,member_id,group_id,account,name,mobile,type,role_id,nickname,face,closed')->fetchAll();

		    $commentFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/commentFollow',$this->_mobile['role_id'],'crm'); //联系记录评论权限

		    $createFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/create_follow',$this->_mobile['role_id'],'crm'); //联系记录添加权限

		    $editFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/edit_follow',$this->_mobile['role_id'],'crm'); //联系记录修改权限

		    $deleteFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/delete_follow',$this->_mobile['role_id'],'crm'); //联系记录删除权限

		    $deleteComment_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/delete_comment',$this->_mobile['role_id'],'crm'); //删除评论权限

		    $this->assign('isCommentFollowAuth',$commentFollow_id);

		    $this->assign('isCreateFollowAuth',$createFollow_id);

		    $this->assign('isEditFollowAuth',$editFollow_id);

		    $this->assign('isDeleteFollowAuth',$deleteFollow_id);

		    $this->assign('isDeleteCommentAuth',$deleteComment_id);

		    $this->assign('members',$members);

		    $this->assign('follow',$follow);

		    $this->assign('cmncate',$cmncate);

		    //附件
		    $files =  getCrmDbModel('upload_file')->where(['opportunity_id' => $opportunity_id, 'company_id' => $this->_company_id])->order('create_time desc')->select();

		    $this->assign('files', $files);
	    }

		if($isProductAuthView)
		{
			$productfield = ['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id];

			$count = getCrmDbModel('opportunity_product')->where($productfield)->count();

			$Page = new Page($count, 20);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$product = getCrmDbModel('opportunity_product')->where($productfield)->limit($Page->firstRow, $Page->listRows)->select();

			foreach($product as $k8=>&$v8)
			{
				$thisProduct = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'product_id'=>$v8['product_id']])->field('product_img')->find();

				$v8['product_img'] = $thisProduct['product_img'];

				$v8['detail'] = CrmgetCrmDetailList('product',$v8['product_id'],$this->_company_id);
			}

			$this->assign('product',$product);
		}

		if($isContractAuthView)
		{
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && $detailtype == 'contract')
			{
				$contractField = ['company_id'=>$this->_company_id,'customer_id'=>$opportunity['customer_id'],'opportunity_id'=>$opportunity_id,'isvalid'=>1];

				$count = getCrmDbModel('contract')->where($contractField)->count();

				$Page = new Page($count, 10);

				if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

				$contract = getCrmDbModel('contract')->where($contractField)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

				foreach($contract as $k6=>&$v6)
				{
					$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$v6['member_id']])->field('member_id,account,name')->find();

					$v6['member_name'] = $thisMember['name'];

					$v6['detail'] = CrmgetCrmDetailList('contract',$v6['contract_id'],$this->_company_id,'name');

					$v6['status'] = getFinanceExamineName($v6['status']);

					$v6['contract_id'] = encrypt($v6['contract_id'],'CONTRACT');
				}

				// 分页流加载

				$result = ['data'=>$contract,'pages'=>ceil($count/10)];

				$this->ajaxReturn($result);
			}
			else
			{
				$isCreateContractAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'Contract/create',$this->_mobile['role_id'],'crm'); //创建合同权限

				$this->assign('isCreateContractAuthView',$isCreateContractAuthView);
			}
		}

		$this->display();
    }

	public function create($id='',$detailtype='')
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

                    getCrmDbModel('opportunity_detail')->add($v); //添加商机详情
                }

				foreach($data['opportunityPro'] as &$v2)
				{
					$v2['opportunity_id'] = $opportunity_id;

					getCrmDbModel('opportunity_product')->add($v2); //添加商机产品
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

			$Page = new Page($count, 10);

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

			$product_type_h = resultCategory($product_type,'parent');

			$this->assign('product_type_h',$product_type_h);


			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$this->member_id])->field('member_id,account,name')->find();

            $members = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'feelec_opened'=>10])->field('member_id,account,name')->fetchAll();

            $this->assign('thisMember',$thisMember);

            $this->assign('members',$members);

			$this->assign('opportunityform',$opportunityform);

			$this->display();
		}
	}

	public function checkCreate()
	{
		//var_dump(I('post.'));die;
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

		$OpportunityCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$opportunity_form,'opportunity',$this->_mobile);

		if($OpportunityCheckForm['detail'])
		{
			$opportunity_detail = $OpportunityCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($OpportunityCheckForm);
		}

		$opportunityPro = I('post.orderPro');

		$product = [];

		if($opportunityPro)
		{
			foreach($opportunityPro as $key=>&$val)
			{
				$product[$key]['product_id'] = $val;

				$product[$key]['customer_id'] = $opportunity['customer_id'];

				$product[$key]['company_id'] = $this->_company_id;
			}
		}

		return ['opportunity'=>$opportunity,'opportunity_detail'=>$opportunity_detail,'opportunityPro'=>$product];

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

			$save = getCrmDbModel('opportunity')->where(['opportunity_id'=>$opportunity_id,'company_id'=>$this->_company_id,'isvalid'=>'1'])->save($data['opportunity']);

			if($save === false)
			{
			    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
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

					getCrmDbModel('opportunity_product')->add($v2); //添加合同产品
				}

				D('CrmLog')->addCrmLog('opportunity',2,$this->_company_id,$this->member_id,$opportunity['customer_id'],0,0,0,$data['opportunity_detail']['name']['form_content'],0,0,0,0,0,0,0,0,0,$opportunity_id);

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];
			}

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

			$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

			$product_type = CrmfetchAll($product_type,'type_id');

			$product_type_h = resultCategory($product_type,'parent');

			$this->assign('product_type_h',$product_type_h);

			$this->assign('opportunityform',$opportunityform);

			$this->assign('opportunity',$opportunity);

			$this->display();
		}
	}

	public function checkEdit($opportunity_id)
	{
		$opportunity_form = I('post.opportunity_form');

		$OpportunityCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$opportunity_form,'opportunity',$this->_mobile,$opportunity_id);

		if($OpportunityCheckForm['detail'])
		{
			$opportunity_detail = $OpportunityCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($OpportunityCheckForm);
		}

		$opportunityPro = I('post.orderPro');

		$product = [];

		if($opportunityPro)
		{
			$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'isvalid'=>'1'])->getField('customer_id');

			foreach($opportunityPro as $key=>&$val)
			{
				$product[$key]['product_id'] = $val;

				$product[$key]['customer_id'] = $customer_id;

				$product[$key]['company_id'] = $this->_company_id;
			}
		}

		return ['opportunity_detail'=>$opportunity_detail,'opportunityPro'=>$product];
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
			$this->common->_empty();
		}
	}

	public function checkAnalysisEdit($analysis_id)
	{
		$analysis_form = I('post.analysis_form');

		$AnalysisCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$analysis_form,'analysis',$this->_mobile,$analysis_id);

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
			$this->common->_empty();
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
			$competitor = getCrmDbModel('competitor')->where(['company_id'=>$this->_company_id,'competitor_id'=>$competitor_id,'customer_id'=>$customer_id])->find();

			$this->assign('detailtype',$detailtype);

			$this->assign('detail_source',$detail_source);

			$form_description = getCrmLanguageData('form_description');

			$competitorform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'competitor'])->order('orderby asc')->select();

			$competitorform1 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'competitor','form_type'=>['neq','textarea']])->order('orderby asc')->select();

			$competitorform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'competitor','form_type'=>['eq','textarea']])->order('orderby asc')->select();

			foreach($competitorform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

			}

			$competitor['detail'] = CrmgetCrmDetailList('competitor',$competitor_id,$this->_company_id);

			$edit_competitor_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'opportunity/edit_competitor',$this->_mobile['role_id'],'crm'); //修改竞争对手权限

			$this->assign('isEditCompetitorAuth',$edit_competitor_auth);

			$this->assign('competitorform',$competitorform);

			$this->assign('competitorform1',$competitorform1);

			$this->assign('competitorform2',$competitorform2);

			$this->assign('competitor',$competitor);

			$this->assign('detailtype',$detailtype);

			$this->assign('opportunity_id',$opportunity_id);

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

	public function lose() //失单
	{
		if(IS_AJAX)
		{
			$id = I('post.id');

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

				$opportunity_id = decrypt($id,'OPPORTUNITY');

				if($opportunity_id)
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

}

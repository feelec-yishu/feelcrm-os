<?php
namespace CrmMobile\Controller;

use CrmMobile\Common\BasicController;

use Think\Page;

class ClueController extends BasicController
{
	protected $ClueFields = ['member_id','nextcontacttime','clue_prefix','clue_no','from_type','original_id'];

	protected $FollowFields = ['cmncate_id','reply_id','nextcontacttime','content','customer_id'];

	protected $commentFields = ['follow_id','content'];

	protected $company = [];

	public function _initialize()
    {
        parent::_initialize();

	    $this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'clueAll',$this->_mobile['role_id'],'crm');

	    $this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'clueGroup',$this->_mobile['role_id'],'crm');

	    $this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'clueOwn',$this->_mobile['role_id'],'crm');

	    $this->assign('isAllViewAuth',$this->all_view_auth);

	    $this->assign('isGroupViewAuth',$this->group_view_auth);

	    $this->assign('isOwnViewAuth',$this->own_view_auth);

		$this->_company = M('company')->where(['company_id'=>$this->_company_id])->find();
    }

	public function index()
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_mobile,$this->_company_id,$this->member_id,'','clue');

		$memberRoleArr = $getCustomerAuth['memberRoleArr'];

		if(!session('Mobilefield')['ImemberRole'])
		{
			$field['member_id'] = $memberRoleArr;
		}
		else
		{
			$ImemberRole = session('Mobilefield')['ImemberRole'];

			if(!$memberRoleArr)
			{
				$this->common->_empty();die;
			}

			if(is_array($ImemberRole))
			{
				$ids = is_array($memberRoleArr) ? array_intersect(explode(',',$ImemberRole[1]),explode(',',$memberRoleArr[1])) : array_intersect(explode(',',$ImemberRole[1]),[$memberRoleArr]);

				$field['member_id'] = ['in',$ids];
			}
			else
			{
				$ids = is_array($memberRoleArr) ? array_intersect([$ImemberRole],explode(',',$memberRoleArr[1])) : array_intersect([$ImemberRole],[$memberRoleArr]);

				$field['member_id'] = ['in',$ids];
			}
		}

		if(I('get.customer_auth'))
		{
			$customer_auth = I('get.customer_auth');

			if(I('get.customer_auth') == 'pool')
			{
				$field['member_id'] = 0;

				$getCustomerPoolAuth = D('CrmSelectField')->getCustomerAuth($this->_mobile,$this->_company_id,$this->member_id,'','cluePool');

				$memberPoolRoleArr = $getCustomerPoolAuth['memberRoleArr'];

				$field['creater_id'] = $memberPoolRoleArr;
			}
			else
			{
				$field['member_id'] = $memberRoleArr;
			}
		}
		else
		{
			$customer_auth = $getCustomerAuth['customer_auth'];
		}

		//创建人维度查看客户权限
		$CreaterViewClue = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CreaterViewClue',$this->_mobile['role_id'],'crm');

		if($CreaterViewClue)
		{
			if(I('get.customer_auth') != 'pool')
			{
				$field['_string'] = getCreaterViewSql($field['member_id']);

				unset($field['member_id']);
			}

			$this->assign('isCreaterView',$CreaterViewClue);
		}

		if(session('Mobilefield')['Itime'])
		{
			if(session('Mobilefield')['time_range'] == 'all')
			{
				$field['createtime'] = ['elt',NOW_TIME];
			}
			else
			{
				$field['createtime'] = session('Mobilefield')['Itime'];
			}
		}

		if(I('get.cc'))
		{
			session('MobileClueCC',I('get.cc'));

			foreach(I('get.cc') as $key=>$val)
			{
				if($val)
				{
					$ccList[$key]['member_id'] = $val;

					$ccList[$key]['member_name'] = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val])->getField('name');
				}
			}
		}
		else
		{
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && implode(',',session('MobileClueCC')))
			{
				$field['member_id'] = ['in',implode(',',session('MobileClueCC'))];
			}
			else
			{
				session('MobileClueCC',null);
			}
		}

		if(I('get.creater'))
		{
			session('MobileClueCreater',I('get.creater'));

			foreach(I('get.creater') as $key=>$val)
			{
				if($val)
				{
					$createrList[$key]['member_id'] = $val;

					$createrList[$key]['member_name'] = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val])->getField('name');
				}
			}
		}
		else
		{
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && implode(',',session('MobileClueCreater')))
			{
				$field['creater_id'] = ['in',implode(',',session('MobileClueCreater'))];
			}
			else
			{
				session('MobileClueCreater',null);
			}
		}

		if(I('get.highKeyword'))
		{
			session('CluehighKeyword',I('get.highKeyword'));
		}
		else
		{
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && session('CluehighKeyword'))
			{
				$highKeyword = session('CluehighKeyword');

				$highKeyword['condition'] = 1;

				$clueHighKey = D('CrmHighKeyword')->clueHighKey($this->_company_id,$highKeyword);

				if($clueHighKey['field'])
				{
					$field = array_merge($field,$clueHighKey['field']);
				}
				//var_dump($field);die;
			}
			else
			{
				session('CluehighKeyword',null);
			}
		}

		if(isset($_GET['request']) && $_GET['request'] == 'flow')
		{
			$highKeyword = session('CluehighKeyword');

			if(!$highKeyword['status'])
			{
				$field['status'] = ['in','-1,1'];
			}
		}

		if(I('get.SelectedscreenFixed')) $SelectedscreenFixed = I('get.SelectedscreenFixed');

		if(I('get.Selectedscreen')) $Selectedscreen = I('get.Selectedscreen');

		if(!$field['member_id'] && $field['member_id'] === false)
		{
			$this->common->_empty();die;
		}

		$FormData['groups'] = M('group')->where(['company_id'=>$this->_company_id,'closed'=>0])
			->field('group_id,group_name')
			->order('orderby asc')
			->select();

        $FormData['members'] = D('Member')
			->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$memberRoleArr,'feelec_opened'=>10])
			->field('member_id,account,type,name,face,group_id')
			->order('member_id asc')
			->fetchAll();

		if($keyword = I('get.keyword'))
		{
			$keywordField = CrmgetDefineFormField($this->_company_id,'clue','name,phone,email,company',$keyword);

			$keywordCondition['clue_id'] = $keywordField ? ['in',$keywordField] : '0';

			$keywordCondition['clue_no'] = ["like","%".$keyword."%"];

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			$this->assign('keyword', $keyword);
		}

		$order = 'createtime desc';

		if($sort = I('get.sort'))
		{
			switch ($sort)
			{
				case 'followtime-asc':

					$order = 'lastfollowtime asc';

                break;
				case 'followtime-desc':

					$order = 'lastfollowtime desc';

                break;
				case 'createtime-asc':

					$order = 'createtime asc';

                break;
				case 'createtime-desc':

					$order = 'createtime desc';

                break;
			}
		}
		else
		{
			$sort = 'createtime-desc';
		}

		$count = getCrmDbModel('clue')->where($field)->count();

		$Page = new Page($count, 10);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$clue = getCrmDbModel('clue')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		foreach($clue as $key=>&$val)
		{
			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

			$val['member_name'] = $thisMember['name'];

			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

			$val['create_name'] = $createMember['name'];

			$val['detail'] = CrmgetCrmDetailList('clue',$val['clue_id'],$this->_company_id);

			$followup = getCrmDbModel('followup')->where(['company_id'=>$this->_company_id,'clue_id'=>$val['clue_id'],'isvalid'=>1])->order('createtime desc')->find();

			if($followup)
			{
				$follow_member = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$followup['member_id']])->field('member_id,account,name')->find();

				$val['follow_member'] = $follow_member['name'];

				$val['follow_time'] = date('m/d H:i',$followup['createtime']);

				$val['follow_content'] = htmlspecialchars_decode($followup['content']);

				$val['follow_content'] = strip_tags($val['follow_content']);
			}

			$val['clue_id'] = encrypt($val['clue_id'],'CLUE');
		}

		// 分页流加载
		if(isset($_GET['request']) && $_GET['request'] == 'flow')
        {
            $result = ['data'=>$clue,'type'=>encrypt('index','CUSTOMER'),'pages'=>ceil($count/10)];

            $this->ajaxReturn($result);
        }
		else
		{
			$isCluePoolAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'clue/pool',$this->_mobile['role_id'],'crm');

			$this->assign('isCluePoolAuth',$isCluePoolAuth);
			
			$form_description = getCrmLanguageData('form_description');

			$clueform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue','form_type'=>['not in',['region','textarea']]])->order('orderby asc')->select();

			foreach($clueform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$this->assign('clueform',$clueform);

			$this->assign('sort',$sort);

			$this->assign('customer_auth',$customer_auth);

			$this->assign('FormData',$FormData);

			$this->assign('ccList',$ccList);

			$this->assign('cc',I('get.cc'));

			$this->assign('createrList',$createrList);

			$this->assign('creater',I('get.creater'));

			$this->assign('Selectedscreen',$Selectedscreen);

			$this->assign('SelectedscreenFixed',$SelectedscreenFixed);

			$this->assign('clue',$clue);

			$this->display();
		}

	}

    public function create($type = '')
    {
		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue'];

		$type = decrypt($type,'CUSTOMER');

        if(IS_POST)
        {
			$data = $this->checkCreate();

			if($clue_id = getCrmDbModel('clue')->add($data['clue']))//线索
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

				$isCluePoolAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'clue/pool',$this->_mobile['role_id'],'crm');

				if(!$data['clue']['member_id'] && $isCluePoolAuth)
				{
					$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('index',['customer_auth'=>'pool'])];
				}
				else
				{
					$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('index')];
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
		
			$clueform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($clueform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$this->member_id])->field('member_id,account,name')->find();

			$members = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'feelec_opened'=>10])->field('member_id,account,name')->fetchAll();
			
			$this->assign('thisMember',$thisMember);

			$this->assign('members',$members);

			$this->assign('clueform',$clueform);

			$this->assign('type',$type);

	        $country_name = getCrmLanguageData('name');

	        $country = getCrmDbModel('country')->field(['*',$country_name])->select();

			$this->assign('country',$country);

			$this->display();
		}
    }

	public function checkCreate()
	{
		$clue = checkFields(I('post.clue'), $this->ClueFields);

		$clue['company_id'] = $this->_company_id;//工单所属公司ID

		$clue['createtime'] = NOW_TIME;

		$clue['creater_id'] = $this->member_id;

		$clue['status'] = -1;

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

        $ClueCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$clue_form,'clue',$this->_mobile);

		if($ClueCheckForm['detail'])
		{
			$clue_detail = $ClueCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ClueCheckForm);
		}

		$clue['from_type'] = $clue['from_type'] ? $clue['from_type'] : 'MOBILE';

		return ['clue'=>$clue,'clue_detail'=>$clue_detail];

	}

   	public function edit($id,$detailtype="")
	{
		$clue_id = decrypt($id,'CLUE');

		$detailtype = decrypt($detailtype,'CLUE');

		if($detailtype)
		{
			$localurl = U('detail',['id'=>encrypt($clue_id,'CLUE'),'detailtype'=>encrypt($detailtype,'CLUE')]);

			$this->assign('detailtype',$detailtype);
		}

		if(IS_POST)
		{
			$data = $this->checkEdit($clue_id);

			if($data['clue'])
			{
				getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'clue_id'=>$clue_id,'isvalid'=>'1'])->save($data['customer']);
			}

			getCrmDbModel('clue_detail')->where(['clue_id'=>$clue_id,'company_id'=>$this->_company_id])->delete();

			foreach($data['clue_detail'] as &$v)
			{
				$v['clue_id'] = $clue_id;

				$v['company_id'] = $this->_company_id;

				if(is_array($v['form_content']))
				{
					$v['form_content'] = implode(',',$v['form_content']);
				}

				getCrmDbModel('clue_detail')->add($v);  //添加线索详情
			}

			D('CrmLog')->addCrmLog('clue',2,$this->_company_id,$this->member_id,0,0,0,0,$data['clue_detail']['name']['form_content'],0,0,0,0,0,0,0,0,$clue_id);

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];

			$this->ajaxReturn($result);

		}
		else
		{
			$clue = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'clue_id'=>$clue_id,'isvalid'=>'1'])->find();

			$form_description = getCrmLanguageData('form_description');
			
			$clueform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue'])->order('orderby asc')->select();

			foreach($clueform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$clue_detail = getCrmDbModel('clue_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'clue_id'=>$clue_id])->find();

				if($v['form_type']=='region')
				{
					if($clue_detail['form_content'])
					{
						$region_detail = explode(',',$clue_detail['form_content']);

						$clue[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$clue[$v['form_name']]['defaultProv'] = $region_detail[1];

						$clue[$v['form_name']]['defaultCity'] = $region_detail[2];

						$clue[$v['form_name']]['defaultArea'] = $region_detail[3];

					}
				}else
				{
					$clue[$v['form_name']] = $clue_detail['form_content'];
				}
			}

			$this->assign('clueform',$clueform);

			$this->assign('clue',$clue);

			$this->display();
		}
	}

	public function checkEdit($clue_id)
	{
		$clue = checkFields(I('post.customer'), $this->ClueFields);

		$clue_form = I('post.clue_form');

		$ClueCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$clue_form,'clue',$this->_mobile,$clue_id);

		if($ClueCheckForm['detail'])
		{
			$clue_detail = $ClueCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ClueCheckForm);
		}

		return ['clue'=>$clue,'clue_detail'=>$clue_detail];
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

	    $isDetailAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'ClueDetail',$this->_mobile['role_id'],'crm');//线索详情权限

	    $isFollowAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'ClueFollow',$this->_mobile['role_id'],'crm');//线索联系记录权限

	    $this->assign('isDetailAuthView',$isDetailAuthView);

		$this->assign('isFollowAuthView',$isFollowAuthView);

        $clue = getCrmDbModel('clue')->where(['company_id'=>$this->_company_id,'clue_id'=>$clue_id,'isvalid'=>'1'])->find();

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

		//客户详情

		if($isDetailAuthView)
		{
			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$clue['creater_id']])->field('member_id,account,name')->find();

			$clue['creater_name'] = $createMember['name'];

			$follow = getCrmDbModel('followup')->where(['company_id'=>$this->_company_id,'clue_id'=>$clue_id,'isvalid'=>1])->order('createtime desc')->getField('createtime');

			$clue['follow_time'] = $follow;

			$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

			$form_description = getCrmLanguageData('form_description');
			
			$clueform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue','form_type'=>['neq','textarea']])->order('orderby asc')->select();

			$clueform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue','form_type'=>['eq','textarea']])->order('orderby asc')->select();

			$abandon_field = getCrmLanguageData('abandon_name');

			$abandon = getCrmDbModel('abandon')->where(['company_id'=>$this->_company_id,'closed'=>0])->field('abandon_id,'.$abandon_field)->select();

			$abandon_log = getCrmDbModel('clue_abandon')->where(['company_id'=>$this->_company_id,'clue_id'=>$clue_id])->select();

			foreach($abandon_log as $abk => &$abv)
			{
				$abv['abandon_name'] = getCrmDbModel('abandon')->where(['company_id'=>$this->_company_id,'abandon_id'=>$abv['abandon_id']])->getField($abandon_field);

				$abv['member_name'] = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$abv['member_id']])->getField('name');

				$abv['operator_name'] = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$abv['operator_id']])->getField('name');
			}

			$this->assign('clueform',$clueform);

			$this->assign('clueform2',$clueform2);

			if($clue['member_id'] > 0)
			{
				$transfer_clue = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'], 'clue/transfer', $this->_mobile['role_id'], 'crm'); //转移线索权限

				$toPool_clue = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'], 'clue/toPool', $this->_mobile['role_id'], 'crm'); //放弃线索权限

				$this->assign('istransferClueAuth', $transfer_clue);

				$this->assign('istoPoolClueAuth', $toPool_clue);
			}
			else
			{
				$draw_clue_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'], 'clue/draw', $this->_mobile['role_id'], 'crm'); //领取线索权限

				$allot_clue_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'], 'clue/allot', $this->_mobile['role_id'], 'crm'); //分配线索权限

				$this->assign('isDrawClueAuth', $draw_clue_id);

				$this->assign('isAllotClueAuth', $allot_clue_id);
			}

			$edit_clue_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'clue/edit',$this->_mobile['role_id'],'crm'); //修改线索权限

			$clue_transform_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'clue/transform',$this->_mobile['role_id'],'crm'); //线索转客户权限

			$this->assign('isEditClueAuth',$edit_clue_id);

			$this->assign('isTransformClueAuth',$clue_transform_id);

			$this->assign('abandons',$abandon);

			$this->assign('abandon_log',$abandon_log);

			$this->assign('groupList',$group);
		}
		else
		{
			$this->common->_empty();

			die;
		}

		$this->assign('clue',$clue);

		//联系记录
		if($isFollowAuthView)
		{
			$cmncate_field = getCrmLanguageData('cmncate_name');
		
			$cmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			$follow = getCrmDbModel('followup')->where(['clue_id'=>$clue_id,'company_id'=>$this->_company_id,'isvalid'=>1])->order('createtime desc')->select();

			$cmncate_field = getCrmLanguageData('cmncate_name');

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

			//线索附件
			$files =  getCrmDbModel('upload_file')->where(['clue_id' => $clue_id, 'company_id' => $this->_company_id])->order('create_time desc')->select();

			$this->assign('files', $files);
		}

		$this->display();
    }

	public function draw() //线索领取
	{
		if(IS_AJAX)
		{
			$clue_id = I('post.clue_id');

			$clue_id = decrypt($clue_id,'CLUE');

			if($clue_id)
			{
				$result = D('CrmClue')->drawClue($this->_company_id,$this->member_id,$clue_id,$this->_sms);
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
			$allot = I('post.update_member');

			$member_id = $allot['member_id'];

			$clue_id = $allot['clue_id'];

			if($member_id)
			{
				if($clue_id)
				{
					$result = D('CrmClue')->allotClue($this->_company_id,$member_id,$this->member_id,$clue_id,$this->_sms);
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

			$this->assign('customerData',$customerData);

			$this->assign('contacterData',$contacterData);

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

			//客户列表
			$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_mobile,$this->_company_id,$this->member_id);

			$memberRoleArr = $getCustomerAuth['memberRoleArr'];

			$selectCustomerCount =  getCrmDbModel('Customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->count();

			$selectCustomerPage = new Page($selectCustomerCount, 10);

			$selectCustomerlist =  getCrmDbModel('Customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->field('customer_id,customer_prefix,customer_no,first_contact_id,createtime')->limit($selectCustomerPage->firstRow, $selectCustomerPage->listRows)->order('createtime desc')->select();

			foreach($selectCustomerlist as $key => &$val)
			{
				$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name,phone');
			}

			if(strlen($selectCustomerPage->show()) > 16) $this->assign('selectCustomerPage', $selectCustomerPage->show()); // 赋值分页输出

			$this->assign('selectCustomer',$selectCustomerlist);

			$this->display();
		}
	}

	public function transfer() //线索转移
	{
		if(IS_AJAX)
		{
			$transfer = I('post.update_member');

			$member_id = $transfer['member_id'];

			$clue_id = $transfer['clue_id'];

			if($member_id)
			{
				if($clue_id)
				{
					$result = D('CrmClue')->transferClue($this->_company_id,$member_id,$this->member_id,$clue_id,$this->_sms);
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
			$topool = I('post.topool');

			$abandon_id = $topool['abandon_id'];

			$clue_id = $topool['clue_id'];

			if(!$abandon_id)
			{
				$result = ['status'=>0,'msg'=>L('SELECT_REASON_GIVING_UP')];
			}
			else
			{
				if($clue_id)
				{
					$result = D('CrmClue')->clueToPool($this->_company_id,$this->member_id,$clue_id,$abandon_id);
				}
				else
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
}

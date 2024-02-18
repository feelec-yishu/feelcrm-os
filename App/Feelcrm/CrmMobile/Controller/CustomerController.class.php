<?php
namespace CrmMobile\Controller;

use CrmMobile\Common\BasicController;

use Think\Cache\Driver\Redis;
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

	public function _initialize()
    {
        parent::_initialize();

	    $this->all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'all',$this->_mobile['role_id'],'crm');

	    $this->group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'group',$this->_mobile['role_id'],'crm');

	    $this->own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'own',$this->_mobile['role_id'],'crm');

		$this->assign('isAllViewAuth',$this->all_view_auth);

        $this->assign('isGroupViewAuth',$this->group_view_auth);

        $this->assign('isOwnViewAuth',$this->own_view_auth);

		$this->_company = M('company')->where(['company_id'=>$this->_company_id])->find();
    }

	public function index()
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		if($this->_crmsite['customerReseller'] == 1)
		{
			$field['customer_type'] = ['neq','agent'];
		}

		//$memberRoleArr = CrmmemberRoleCrmauth($this->_mobile,$this->_company_id,$this->member_id);

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_mobile,$this->_company_id,$this->member_id);

		$customer_auth = $getCustomerAuth['customer_auth'];

		$memberRoleArr = $getCustomerAuth['memberRoleArr'];

		if(!session('Mobilefield')['ImemberRole'])
		{
			$field['member_id'] = $memberRoleArr;
		}
		else
		{
			$ImemberRole = session('Mobilefield')['ImemberRole'];

			$field['member_id'] = $ImemberRole;
		}

		if(I('get.customer_auth'))
		{
			if(I('get.customer_auth') == 'pool')
			{
				$customer_auth = I('get.customer_auth');

				$field['member_id'] = 0;

				$getCustomerPoolAuth = D('CrmSelectField')->getCustomerAuth($this->_mobile,$this->_company_id,$this->member_id,'','pool');

				$memberPoolRoleArr = $getCustomerPoolAuth['memberRoleArr'];

				$field['creater_id'] = $memberPoolRoleArr;
			}
			else
			{
				$customer_auth = $getCustomerAuth['customer_auth'];

				$field['member_id'] = $memberRoleArr;
			}
		}
		elseif(session('Mobilefield')['customer_auth'])
		{
			$customer_auth = session('Mobilefield')['customer_auth'];
		}

		//创建人维度查看客户权限
		$CreaterViewCustomer = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CreaterViewCustomer',$this->_mobile['role_id'],'crm');

		if($CreaterViewCustomer)
		{
			if(I('get.customer_auth') != 'pool')
			{
				$field['_string'] = getCreaterViewSql($field['member_id']);

				unset($field['member_id']);
			}

			$this->assign('isCreaterView', $CreaterViewCustomer);
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
			session('MobileCustomerCC',I('get.cc'));

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
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && implode(',',session('MobileCustomerCC')))
			{
				$field['member_id'] = ['in',implode(',',session('MobileCustomerCC'))];
			}
			else
			{
				session('MobileCustomerCC',null);
			}
		}

		if(I('get.creater'))
		{
			session('MobileCustomerCreater',I('get.creater'));

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
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && implode(',',session('MobileCustomerCreater')))
			{
				$field['creater_id'] = ['in',implode(',',session('MobileCustomerCreater'))];
			}
			else
			{
				session('MobileCustomerCreater',null);
			}
		}

		if(I('get.highKeyword'))
		{
			session('CustomerhighKeyword',I('get.highKeyword'));
		}
		else
		{
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && session('CustomerhighKeyword'))
			{
				$highKeyword = session('CustomerhighKeyword');
				// var_dump($highKeyword);die;
				$customerHighKey = $this->customerHighKey($highKeyword,$memberRoleArr);

				if($customerHighKey['field'])
				{
					$field = array_merge($field,$customerHighKey['field']);
				}
				//var_dump($field);die;
			}
			else
			{
				session('CustomerhighKeyword',null);
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
			$keywordField = CrmgetDefineFormField($this->_company_id,'customer','name,phone,email',$keyword);

			$contacterField = CrmgetDefineFormField($this->_company_id,'contacter','name,phone,email',$keyword);

			$keywordCondition['customer_id'] = $keywordField ? ['in',$keywordField] : '0';

			$contacterField = checkContacterSqlField($this->_company_id,$contacterField);

			$keywordCondition['first_contact_id'] = $contacterField ? ['in',$contacterField] : '-1';

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			//$field['customer_id'] = $keywordField ? ['in',$keywordField] : '0';

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

		$count = getCrmDbModel('customer')->where($field)->count();

		$Page = new Page($count, 10);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$customer = getCrmDbModel('customer')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		$customer = D('CrmCustomer')->getMobileListInfo($customer,$this->_company_id);

		// 分页流加载
		if(isset($_GET['request']) && $_GET['request'] == 'flow')
        {
            $result = ['data'=>$customer,'type'=>encrypt('index','CUSTOMER'),'pages'=>ceil($count/10)];

            $this->ajaxReturn($result);
        }
		else
		{
			$isCustomerPoolAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/pool',$this->_mobile['role_id'],'crm');

			$this->assign('isCustomerPoolAuth',$isCustomerPoolAuth);
			
			$form_description = getCrmLanguageData('form_description');

			$customerform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_type'=>['not in',['region','textarea']]])->order('orderby asc')->select();

			foreach($customerform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$this->assign('customerform',$customerform);

			$this->assign('sort',$sort);

			$this->assign('customer_auth',$customer_auth);

			$this->assign('FormData',$FormData);

			$this->assign('ccList',$ccList);

			$this->assign('cc',I('get.cc'));

			$this->assign('createrList',$createrList);

			$this->assign('creater',I('get.creater'));

			$this->assign('Selectedscreen',$Selectedscreen);

			$this->assign('SelectedscreenFixed',$SelectedscreenFixed);

			$this->assign('customer',$customer);

			$this->display();
		}

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

		//$memberRoleArr = CrmmemberRoleCrmauth($this->_mobile,$this->_company_id,$this->member_id);

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_mobile,$this->_company_id,$this->member_id);

		$customer_auth = $getCustomerAuth['customer_auth'];

		$memberRoleArr = $getCustomerAuth['memberRoleArr'];

		if(!session('Mobilefield')['ImemberRole'])
		{
			$field['member_id'] = $memberRoleArr;
		}
		else
		{
			$ImemberRole = session('Mobilefield')['ImemberRole'];

			$field['member_id'] = $ImemberRole;
		}

		if(I('get.customer_auth'))
		{
			if(I('get.customer_auth') == 'pool')
			{
				$customer_auth = I('get.customer_auth');

				$field['member_id'] = 0;
			}
			else
			{
				$customer_auth = $getCustomerAuth['customer_auth'];

				$field['member_id'] = $memberRoleArr;
			}
		}
		elseif(session('Mobilefield')['customer_auth'])
		{
			$customer_auth = session('Mobilefield')['customer_auth'];
		}

		//创建人维度查看客户权限
		$CreaterViewCustomer = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CreaterViewCustomer',$this->_mobile['role_id'],'crm');

		if($CreaterViewCustomer)
		{
			if(I('get.customer_auth') != 'pool')
			{
				$field['_string'] = getCreaterViewSql($field['member_id']);

				unset($field['member_id']);
			}

			$this->assign('isCreaterView',$CreaterViewCustomer);
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
			session('MobileCustomerCC',I('get.cc'));

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
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && implode(',',session('MobileCustomerCC')))
			{
				$field['member_id'] = ['in',implode(',',session('MobileCustomerCC'))];
			}
			else
			{
				session('MobileCustomerCC',null);
			}
		}

		if(I('get.creater'))
		{
			session('MobileCustomerCreater',I('get.creater'));

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
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && implode(',',session('MobileCustomerCreater')))
			{
				$field['creater_id'] = ['in',implode(',',session('MobileCustomerCreater'))];
			}
			else
			{
				session('MobileCustomerCreater',null);
			}
		}

		if(I('get.highKeyword'))
		{
			session('CustomerhighKeyword',I('get.highKeyword'));
		}
		else
		{
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && session('CustomerhighKeyword'))
			{
				$highKeyword = session('CustomerhighKeyword');
				// var_dump($highKeyword);die;
				$customerHighKey = $this->customerHighKey($highKeyword,$memberRoleArr);

				if($customerHighKey['field'])
				{
					$field = array_merge($field,$customerHighKey['field']);
				}
				//var_dump($field);die;
			}
			else
			{
				session('CustomerhighKeyword',null);
			}
		}

		if(I('get.SelectedscreenFixed')) $SelectedscreenFixed = I('get.SelectedscreenFixed');

		if(I('get.Selectedscreen')) $Selectedscreen = I('get.Selectedscreen');

		if(!$field['member_id'] && $field['member_id'] === false)
		{
			$this->common->_empty();die;
		}

		$FormData['groups'] = M('group')->where(['company_id'=>$this->_company_id,'closed'=>0,'ticket_auth'=>10])
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
			$keywordField = CrmgetDefineFormField($this->_company_id,'customer','name,phone,email',$keyword);

			$contacterField = CrmgetDefineFormField($this->_company_id,'contacter','name,phone,email',$keyword);

			$keywordCondition['customer_id'] = $keywordField ? ['in',$keywordField] : '0';

			$contacterField = checkContacterSqlField($this->_company_id,$contacterField);

			$keywordCondition['first_contact_id'] = $contacterField ? ['in',$contacterField] : '-1';

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			//$field['customer_id'] = $keywordField ? ['in',$keywordField] : '0';

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

		$count = getCrmDbModel('customer')->where($field)->count();

		$Page = new Page($count, 10);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$customer = getCrmDbModel('customer')->where($field)->limit($Page->firstRow, $Page->listRows)->order($order)->select();

		$customer = D('CrmCustomer')->getMobileListInfo($customer,$this->_company_id);

		// 分页流加载
		if(isset($_GET['request']) && $_GET['request'] == 'flow')
        {
            $result = ['data'=>$customer,'type'=>encrypt('index','CUSTOMER'),'pages'=>ceil($count/10)];

            $this->ajaxReturn($result);
        }
		else{

			$isCustomerPoolAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/pool',$this->_mobile['role_id'],'crm');

			$this->assign('isCustomerPoolAuth',$isCustomerPoolAuth);

			$form_description = getCrmLanguageData('form_description');
			
			$customerform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_type'=>['not in',['region','textarea']]])->order('orderby asc')->select();

			foreach($customerform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$this->assign('customerform',$customerform);

			$this->assign('sort',$sort);

			$this->assign('customer_auth',$customer_auth);

			$this->assign('FormData',$FormData);

			$this->assign('ccList',$ccList);

			$this->assign('cc',I('get.cc'));

			$this->assign('createrList',$createrList);

			$this->assign('creater',I('get.creater'));

			$this->assign('Selectedscreen',$Selectedscreen);

			$this->assign('SelectedscreenFixed',$SelectedscreenFixed);

			$this->assign('customer',$customer);

			$this->display('index');
		}

	}

	public function customerHighKey($highKeyword,$memberRoleArr=[],$type = 'index')
	{
		if($highKeyword['is_trade'] == 1)
		{
			$field['is_trade'] = 1;
		}
		elseif($highKeyword['is_trade'] == 0 && $highKeyword['is_trade'] != '' && $highKeyword['is_trade'] != null)
		{
			$field['is_trade'] = 0;
		}

		if($highKeyword['customer_no'])
		{
			$field['customer_no'] = ['like',"%{$highKeyword['customer_no']}%"];
		}

		$customer_arr = [];

		$contacter_arr = [];

		$keynum = 0;

		foreach($highKeyword['define_form'] as $key=>&$val)
		{
			if($val)
			{
				$customer_name = '';

				$this_form_type = getCrmDbModel('define_form')->where(['type'=>'customer','form_name'=>$key,'company_id'=>$this->_company_id,'closed'=>0])->getField('form_type');

				if($this_form_type == 'region')
				{
					$thisval = '';

					if($highKeyword['define_form'][$key]['defaultCountry'])
					{
						$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

						if($highKeyword['define_form'][$key]['defaultProv'])
						{
							$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
						}

						if($highKeyword['define_form'][$key]['defaultCity'])
						{
							$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
						}

						$customer_name = CrmgetDefineFormHighField($this->_company_id,'customer',$key,$thisval);
					}
					$val = $thisval;
				}
				else
				{
					if($this_form_type == 'checkbox')
					{
						$val = implode(",", $val);
					}
					$customer_name = CrmgetDefineFormHighField($this->_company_id,'customer',$key,$val);
				}

				if($customer_name && count($customer_name) >0 )
				{
					$customer_arr = array_merge($customer_arr,$customer_name);
				}
			}

			if($val)
			{
				$keynum ++;
			}
		}

		if($highKeyword['contacter_name'])
		{
			$contacter_name = CrmgetDefineFormHighField($this->_company_id,'contacter','name',$highKeyword['contacter_name']);

			if($contacter_name && count($contacter_name) >0 )
			{
				$contacter_arr = array_merge($contacter_arr,$contacter_name);
			}
		}

		if($highKeyword['contacter_phone'])
		{
			$contacter_phone = CrmgetDefineFormHighField($this->_company_id,'contacter','phone',$highKeyword['contacter_phone']);

			if($contacter_phone && count($contacter_phone) >0 )
			{
				$contacter_arr = array_merge($contacter_arr,$contacter_phone);
			}
		}

		if($highKeyword['contacter_email'])
		{
			$contacter_email = CrmgetDefineFormHighField($this->_company_id,'contacter','email',$highKeyword['contacter_email']);

			if($contacter_email && count($contacter_email) >0 )
			{
				$contacter_arr = array_merge($contacter_arr,$contacter_email);
			}
		}

		$contacter_arr = array_unique($contacter_arr);//去除数组中重复数据，返回数组

		$customer_info_arr = [];

		foreach($contacter_arr as $key=>$val)
		{
			if($memberRoleArr)
			{
				$customer_info = getCrmDbModel('contacter')->where(['contacter_id'=>$val,'member_id'=>$memberRoleArr,'isvalid'=>1,'company_id'=>$this->_company_id])->getField('customer_id');
			}
			else
			{
				$customer_info = getCrmDbModel('contacter')->where(['contacter_id'=>$val,'isvalid'=>1,'company_id'=>$this->_company_id])->getField('customer_id');
			}

			if($customer_info)
			{
				$customer_info_arr[] .= $customer_info;
			}
		}

		$customer_arr = array_merge($customer_arr,$customer_info_arr);

		$customer_arr1 = array_unique($customer_arr);//去除数组中重复数据，返回数组

		$customer_arr = array_diff_assoc($customer_arr, $customer_arr1);//取得数组中的重复数据，返回重复数组

		if(!$customer_arr && $customer_arr1)
		{
			$customer_arr = $customer_arr1;
		}

		if(count($customer_arr) > 0)
		{
			$field['customer_id'] = ['in',implode(',',$customer_arr)];
		}
		else
		{
			if($keynum > 0)
			{
				$field['customer_id'] = '-1';
			}
		}

		return ['field'=>$field];
	}

    public function contacter()
    {
        $field['company_id'] = $this->_company_id;

		$field['isvalid'] = '1';

		$memberRoleArr = CrmmemberRoleCrmauth($this->_mobile,$this->_company_id,$this->member_id);

		if(!session('Mobilefield')['ImemberRole'])
		{
			$field['member_id'] = $memberRoleArr;
		}
		else
		{
			$ImemberRole = session('Mobilefield')['ImemberRole'];

			$field['member_id'] = $ImemberRole;
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

		if(!$field['member_id'])
		{
			$this->common->_empty();die;
		}

	    //创建人维度查看联系人权限
	    $CreaterViewContacter = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CreaterViewContacter',$this->_mobile['role_id'],'crm');

	    if($CreaterViewContacter)
	    {
		    $field['_string'] = getCreaterViewSql($field['member_id']);

		    unset($field['member_id']);
	    }

	    $customers = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'member_id'=>$memberRoleArr])->field('customer_id')->select();

	    $customerIds = [];

	    foreach($customers as $custK => &$custV)
	    {
		    $customerIds[$custK] = $custV['customer_id'];
	    }

		if(I('get.ImemberRole'))
		{
			$field['customer_id'] = ['in',implode(',',$customerIds)];
		}

		$count = getCrmDbModel('contacter')->where($field)->count();

		$Page = new Page($count, 10);

        if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$contacter = getCrmDbModel('contacter')->where($field)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

		$show_list = CrmgetShowListField('contacter',$this->_company_id); //客户列表显示字段

		foreach($contacter as $key=>&$val)
		{
			$val['detail'] = CrmgetCrmDetailList('contacter',$val['contacter_id'],$this->_company_id,$show_list['form_name']);

			$thisCustomer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$val['customer_id'],'isvalid'=>1])->field('first_contact_id')->find();

			$val['first_contact_id'] = $thisCustomer['first_contact_id'];

			$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name');

			$val['customer_name'] = $customer_detail['name'];

			if($val['contacter_id'] == $val['first_contact_id'])
			{
				$val['contacter_id'] = encrypt($val['contacter_id'],'CONTACTER');

				$val['first_contact_id'] = $val['contacter_id'];
			}
			else
			{
				$val['contacter_id'] = encrypt($val['contacter_id'],'CONTACTER');
			}

			$val['customer_id'] = encrypt($val['customer_id'],'CUSTOMER');

			$val['member_name'] = M('member')->where(['company_id'=>$this->_company_id,'member_id'=>$val['member_id'],'type'=>1])->getField('name');
		}

		$form_description = getCrmLanguageData('form_description');
		
		$contacterform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contacter','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		foreach($contacterform as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		// 分页流加载
		if(isset($_GET['request']) && $_GET['request'] == 'flow')
        {
            $result = ['data'=>$contacter,'pages'=>ceil($count/10)];

            $this->ajaxReturn($result);
        }
		else{

			$this->assign('contacterform',$contacterform);

			$this->assign('contacter',$contacter);

			$this->assign('formList',$show_list['form_list']);

			$createContacter_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/create_contacter',$this->_mobile['role_id'],'crm');	 //联系人添加权限

			$editContacter_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/edit_contacter',$this->_mobile['role_id'],'crm'); //联系人修改权限

			$deleteContacter_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/delete_contacter',$this->_mobile['role_id'],'crm'); //删除联系人权限

			$this->assign('isCreateContacterAuth',$createContacter_id);

			$this->assign('isEditContacterAuth',$editContacter_id);

			$this->assign('isDeleteContacterAuth',$deleteContacter_id);

			$this->display();
		}
    }


    public function followup()
    {
        $field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		$memberRoleArr = CrmmemberRoleCrmauth($this->_mobile,$this->_company_id,$this->member_id);

		if(!session('Mobilefield')['ImemberRole'])
		{
			$field['member_id'] = $memberRoleArr;
		}
		else
		{
			$ImemberRole = session('Mobilefield')['ImemberRole'];

			$field['member_id'] = $ImemberRole;
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

		if(!$field['member_id'])
		{
			$this->common->_empty();die;
		}

		if(I('get.ImemberId')) $field['member_id'] = I('get.ImemberId');

		$count = getCrmDbModel('followup')->where($field)->count();

		$Page = new Page($count, 10);

        if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$follow = getCrmDbModel('followup')->where($field)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

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
				$val['follow_type'] = L('CONTACT_THE_CLUE');

				$clue_detail = CrmgetCrmDetailList('clue',$val['clue_id'],$this->_company_id,'name');

				$val['belong_name'] = $clue_detail['name'];

				$val['clue_id'] = encrypt($val['clue_id'],'CLUE');
			}
			elseif($val['opportunity_id'] > 0)
			{
				$val['follow_type'] = L('CONTACT_THE_OPPORTUNITY');

				$opportunity_detail = CrmgetCrmDetailList('opportunity',$val['opportunity_id'],$this->_company_id,'name');

				$val['belong_name'] = $opportunity_detail['name'];

				$val['opportunity_id'] = encrypt($val['opportunity_id'],'OPPORTUNITY');
			}
			else
			{
				$val['follow_type'] = L('CONTACT_THE_CUSTOMER');

				$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name');

				$val['belong_name'] = $customer_detail['name'];

				$val['customer_id'] = encrypt($val['customer_id'],'CUSTOMER');
			}

			$val['createtime'] = getDates($val['createtime']);

			$val['follow_id'] = encrypt($val['follow_id'],'FOLLOW');

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

		// 分页流加载
		if(isset($_GET['request']) && $_GET['request'] == 'flow')
        {
            $result = ['data'=>$follow,'pages'=>ceil($count/10)];

            $this->ajaxReturn($result);
        }
		else
		{
			$commentFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/commentFollow',$this->_mobile['role_id'],'crm');		//联系记录评论权限

			$this->assign('isCommentFollowAuth',$commentFollow_id);

			$this->assign('follow',$follow);

			$this->display();
		}
    }


    public function create($type = '',$customerData='',$contacterData='',$fromType='')
    {
		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer'];

		$type = decrypt($type,'CUSTOMER');

        if(IS_POST)
        {
            $data = I('post.');

			//var_dump($data);die;

            if($data['type'])
            {
                if($data['type'] == 'province')
                {
                    $where = ['country_code'=>$data['country_id']];

	                $province_name = getCrmLanguageData('name');

                    $province = getCrmDbModel('province')->field(['*',$province_name])->where($where)->select();

                    $this->ajaxReturn(['code'=>0,'data'=>$province]);
                }

                if($data['type'] == 'city')
                {
                    $where = ['country_code'=>$data['country_id'],'province_code'=>$data['province_id']];

	                $city_name = getCrmLanguageData('name');

                    $city = getCrmDbModel('city')->field(['*',$city_name])->where($where)->select();

                    $this->ajaxReturn(['code'=>0,'data'=>$city]);
                }
            }else
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
						if($data['customer']['customer_type'] == 'agent')
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

						$isCustomerPoolAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/pool',$this->_mobile['role_id'],'crm');

						if($isCustomerPoolAuth)
						{
							$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('index',['customer_auth'=>'pool'])];
						}
						else
						{
							$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('index')];
						}
					}

				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
				}

				$this->ajaxReturn($result);
			}

        }else{
			$form_description = getCrmLanguageData('form_description');
		
			$customerform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($customerform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}
			
			$contacterform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>'contacter'])->order('orderby asc')->select();

			foreach($contacterform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$this->member_id])->field('member_id,account,name')->find();

			$members = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'feelec_opened'=>10])->field('member_id,account,name')->fetchAll();

			if($customerData)
			{
				$customerData =  unserialize(urldecode(base64_decode($customerData)));

				$this->assign('customerData',$customerData);
			}

			if($contacterData)
			{
				$contacterData =  unserialize(urldecode(base64_decode($contacterData)));

				$this->assign('contacterData',$contacterData);
			}

			if($fromType)
			{
				$this->assign('fromType',$fromType);
			}

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
			
			$this->assign('thisMember',$thisMember);

			$this->assign('members',$members);

			$this->assign('customerform',$customerform);

			$this->assign('contacterform',$contacterform);

			$this->assign('type',$type);

	        $country_name = getCrmLanguageData('name');

	        $country = getCrmDbModel('country')->field(['*',$country_name])->select();

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

		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$this->_company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		if($crmsite['customerCode'])
		{
			$customer['customer_prefix'] = $crmsite['customerCode'];
		}
		else
		{
			$customer['customer_prefix'] = 'C-';
		}

		$customer['customer_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$this->_company_id), rand(0,9), 4));

		$customer_form = I('post.customer_form');

        $CustomerCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$customer_form,'customer',$this->_mobile);

		if($CustomerCheckForm['detail'])
		{
			$customer_detail = $CustomerCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($CustomerCheckForm);
		}

		$customer['from_type'] = $customer['from_type'] ? $customer['from_type'] : 'MOBILE';

		return ['customer'=>$customer,'customer_detail'=>$customer_detail,'contacter_detail'=>$contacter_detail];

	}

   	public function edit($id,$detailtype="")
	{
		$customer_id = decrypt($id,'CUSTOMER');

		$detailtype = decrypt($detailtype,'CUSTOMER');

		if($detailtype)
		{
			$localurl = U('detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

			$this->assign('detailtype',$detailtype);
		}

		if(IS_POST)
		{
			$data = $this->checkEdit($customer_id);

			if($data['customer'])
			{
				getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>'1'])->save($data['customer']);
			}

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

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];

			$this->ajaxReturn($result);

		}
		else
		{
			$customer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>'1'])->find();

			$form_description = getCrmLanguageData('form_description');
			
			$customerform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer'])->order('orderby asc')->select();

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
				}else
				{
					$customer[$v['form_name']] = $customer_detail['form_content'];
				}
			}

			$this->assign('customerform',$customerform);

			$this->assign('customer',$customer);

			$updateIsTrade = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'], 'UpdateIsTrade', $this->_mobile['role_id'], 'crm'); //修改成交状态权限

			$this->assign('isUpdateTradeAuth', $updateIsTrade);

			$this->display();
		}
	}

	public function checkEdit($customer_id)
	{
		$customer = checkFields(I('post.customer'), $this->CustomerFields);

		$customer_form = I('post.customer_form');

		$CustomerCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$customer_form,'customer',$this->_mobile,$customer_id);

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
		$type = decrypt($type,'CUSTOMER');

        $field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

        $result = [];

		if($id)
		{
			$customer_id = decrypt($id,'CUSTOMER');

			$field['customer_id'] = $customer_id;

			if(getCrmDbModel('order')->where($field)->find()){

				$result = ['status'=>0,'msg'=>L('CUSTOMER_HAS_UNDELETED_ORDERS')];

			}else
			{
				if(getCrmDbModel('customer')->where($field)->save(['isvalid'=>0]))
				{

					$customer['detail'] = CrmgetCrmDetailList('customer',$customer_id,$this->_company_id);

					D('CrmLog')->addCrmLog('customer',3,$this->_company_id,$this->member_id,$customer_id,0,0,0,$customer['detail']['name']);

					$contacter = getCrmDbModel('contacter')->where($field)->field('contacter_id')->select();

					foreach($contacter as $key=>$val)
					{
						getCrmDbModel('contacter')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'customer_id'=>$customer_id,'contacter_id'=>$val['contacter_id']])->save(['isvalid'=>0]);

						$contacter['detail'] = CrmgetCrmDetailList('contacter',$val['contacter_id'],$this->_company_id);

						D('CrmLog')->addCrmLog('contacter',3,$this->_company_id,$this->member_id,$customer_id,$val['contacter_id'],0,0,$contacter['detail']['name']);
					}

					getCrmDbModel('followup')->where($field)->save(['isvalid'=>0]);

					$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>U($type)];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
				}
			}
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('SELECT_CUSTOMER_DELETE')];
		}

        $this->ajaxReturn($result);
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

	    $isDetailAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CustDetail',$this->_mobile['role_id'],'crm');//客户详情权限

	    $isFollowAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CustFollow',$this->_mobile['role_id'],'crm');//客户联系记录权限

	    $isOpportunityAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CustOpportunity',$this->_mobile['role_id'],'crm');//客户商机权限

	    $isContractAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CustContract',$this->_mobile['role_id'],'crm');//客户合同权限

	    $isShipmentAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CustShipment',$this->_mobile['role_id'],'crm');//客户进销存权限

	    $this->assign('isDetailAuthView',$isDetailAuthView);

		$this->assign('isFollowAuthView',$isFollowAuthView);

		$this->assign('isOpportunityAuthView',$isOpportunityAuthView);

		$this->assign('isContractAuthView',$isContractAuthView);

		$this->assign('isShipmentAuthView',$isShipmentAuthView);

        $customer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>'1'])->find();

		if(!$customer)
		{
			$this->common->_empty();

			die;
		}

		//有分配记录，清除并更新待办
	    if(getCrmDbModel('customer_give')->where(['company_id'=>$this->_company_id,'target_id'=>$this->member_id,'customer_id'=>$customer_id,'is_looked'=>0])->getField('id'))
	    {
		    getCrmDbModel('customer_give')->where(['company_id'=>$this->_company_id,'target_id'=>$this->member_id,'customer_id'=>$customer_id,'is_looked'=>0])->save(['is_looked'=>1]);
	    }

        $thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$customer['member_id']])->field('member_id,account,name,group_id')->find();

		$customer['member_name'] = $thisMember['name'];

		$customer['group_id'] = $thisMember['group_id'];

		$allField = getCrmDbModel('define_form')->where(['type'=>'customer','company_id'=>$this->_company_id,'closed'=>0,'form_name'=>['neq','remark']])->count();

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

		if($isDetailAuthView){

			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$customer['creater_id']])->field('member_id,account,name')->find();

			$customer['creater_name'] = $createMember['name'];

			$follow = getCrmDbModel('followup')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>1])->order('createtime desc')->getField('createtime');

			$customer['follow_time'] = $follow;

			$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

			$form_description = getCrmLanguageData('form_description');
			
			$customerform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_type'=>['neq','textarea']])->order('orderby asc')->select();

			$customerform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_type'=>['eq','textarea']])->order('orderby asc')->select();

			$abandon_field = getCrmLanguageData('abandon_name');

			$abandon = getCrmDbModel('abandon')->where(['company_id'=>$this->_company_id,'closed'=>0])->field('abandon_id,'.$abandon_field)->select();

			$abandon_log = getCrmDbModel('customer_abandon')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id])->select();

			foreach($abandon_log as $abk => &$abv)
			{
				$abv['abandon_name'] = getCrmDbModel('abandon')->where(['company_id'=>$this->_company_id,'abandon_id'=>$abv['abandon_id']])->getField($abandon_field);

				$abv['member_name'] = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$abv['member_id']])->getField('name');

				$abv['operator_name'] = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$abv['operator_id']])->getField('name');
			}

			$lose_field = getCrmLanguageData('lose_name');

			$lose = getCrmDbModel('lose')->where(['company_id'=>$this->_company_id,'closed'=>0])->field('lose_id,'.$lose_field)->select();

			$lose_log = getCrmDbModel('customer_lose')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id])->select();

			foreach($lose_log as $losek => &$losev)
			{
				$losev['lose_name'] = getCrmDbModel('lose')->where(['company_id'=>$this->_company_id,'lose_id'=>$losev['lose_id']])->getField($lose_field);

				$competitor_name = CrmgetCrmDetailList('competitor',$losev['competitor_id'],$this->_company_id,'name');

				$losev['competitor_name'] = $competitor_name['name'];

				$losev['member_name'] = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$losev['member_id']])->getField('name');

				$losev['operator_name'] = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$losev['operator_id']])->getField('name');
			}

			/*$competitor = getCrmDbModel('competitor')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>1])->select();

			foreach($competitor as $compk => &$comv)
			{
				$competitor_name = CrmgetCrmDetailList('competitor',$comv['competitor_id'],$this->_company_id,'name');

				$comv['competitor_name'] = $competitor_name['name'];
			}*/

			$this->assign('customerform',$customerform);

			$this->assign('customerform2',$customerform2);

			$edit_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/edit',$this->_mobile['role_id'],'crm'); //修改客户权限

			$delete_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/delete',$this->_mobile['role_id'],'crm'); //删除客户权限

			$transfer_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/transfer',$this->_mobile['role_id'],'crm'); //转移权限

			$toPool_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/toPool',$this->_mobile['role_id'],'crm'); //放弃客户权限

			$draw_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/draw',$this->_mobile['role_id'],'crm'); //领取客户权限

			$allot_customer_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/allot',$this->_mobile['role_id'],'crm'); //分配客户权限

			$lose_customer = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/lose',$this->_mobile['role_id'],'crm');	 //失单权限

			$this->assign('isDrawCustomerAuth',$draw_customer_id);

			$this->assign('isAllotCustomerAuth',$allot_customer_id);

			$this->assign('isDelCustomerAuth',$delete_customer_id);

			$this->assign('isEditCustomerAuth',$edit_customer_id);

			$this->assign('istransferCustomerAuth',$transfer_customer);

			$this->assign('istoPoolCustomerAuth',$toPool_customer);

			$this->assign('isLoseCustomerAuth',$lose_customer);

			$this->assign('abandons',$abandon);

			$this->assign('abandon_log',$abandon_log);

			$this->assign('loses',$lose);

			$this->assign('lose_log',$lose_log);

			//$this->assign('competitors',$competitor);

			$this->assign('groupList',$group);


			//联系人
			$fieldcontacter['company_id'] = $this->_company_id;

			$fieldcontacter['isvalid'] = '1';

			$fieldcontacter['customer_id'] = $customer_id;

			$contacter = getCrmDbModel('contacter')->where($fieldcontacter)->select();

			foreach($contacter as $k4=>&$v4)
			{
				$v4['detail'] = CrmgetCrmDetailList('contacter',$v4['contacter_id'],$this->_company_id);
			}

			$show_list = CrmgetShowListField('contacter',$this->_company_id); //列表显示字段

			$this->assign('contacterformList',$show_list['form_list']);

			$this->assign('contacter',$contacter);

			$createContacter_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/create_contacter',$this->_mobile['role_id'],'crm'); //联系人添加权限

			$editContacter_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/edit_contacter',$this->_mobile['role_id'],'crm'); //联系人修改权限

			$deleteContacter_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/delete_contacter',$this->_mobile['role_id'],'crm'); //删除联系人权限

			$this->assign('isCreateContacterAuth',$createContacter_id);

			$this->assign('isEditContacterAuth',$editContacter_id);

			$this->assign('isDeleteContacterAuth',$deleteContacter_id);
		}
		else
		{
			$this->common->_empty();

			die;
		}

		$this->assign('customer',$customer);

		//联系记录
		if($isFollowAuthView)
		{
			$cmncate_field = getCrmLanguageData('cmncate_name');
		
			$cmncate = getCrmDbModel('communicate')->field(['*',$cmncate_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

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

			//$follow = getCrmDbModel('followup')->where(['customer_id'=>$customer_id,'company_id'=>$this->_company_id,'isvalid'=>1])->order('createtime desc')->select();

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

				if($v3['contacter_id'])
				{
					$v3['contacter_detail'] = CrmgetCrmDetailList('contacter',$v3['contacter_id'],$this->_company_id,'name');
				}

				$v3['followComment'] = getCrmDbModel('follow_comment')->where(['company_id'=>$this->_company_id,'follow_id'=>$v3['follow_id'],'isvalid'=>1])->order('createtime desc')->select();

				$v3['countComment'] = count($v3['followComment']);

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

				$v3['countComment'] = count($v3['followComment']);

				$uploadFiles = getCrmDbModel('upload_file')->where(['company_id' => $this->_company_id, 'follow_id' => $v3['follow_id'], 'file_form' => 'follow'])->select();

				$v3['createFiles'] = $uploadFiles;
			}

			$this->assign('clue_follow',$clue_follow);

			$fieldcontacter['company_id'] = $this->_company_id;

			$fieldcontacter['isvalid'] = '1';

			$fieldcontacter['customer_id'] = $customer_id;

			$contacter = getCrmDbModel('contacter')->where($fieldcontacter)->select();

			foreach($contacter as $k4=>&$v4)
			{
				$v4['detail'] = CrmgetCrmDetailList('contacter',$v4['contacter_id'],$this->_company_id);
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

			$this->assign('contacter',$contacter);

			$this->assign('follow',$follow);

			$this->assign('cmncate',$cmncate);
		}

	    //商机
	    if($isOpportunityAuthView)
	    {
		    if(isset($_GET['request']) && $_GET['request'] == 'flow' && $detailtype == 'opportunity')
		    {
			    $count = getCrmDbModel('opportunity')->where($fieldopportunity)->count();

			    $Page = new Page($count, 10);

			    if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			    $opportunity = getCrmDbModel('opportunity')->where($fieldopportunity)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

			    foreach($opportunity as $k6=>&$v6)
			    {
				    $thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$v6['member_id']])->field('member_id,account,name')->find();

				    $v6['member_name'] = $thisMember['name'];

				    $v6['detail'] = CrmgetCrmDetailList('opportunity',$v6['opportunity_id'],$this->_company_id,'name,stage');

				    $v6['opportunity_id'] = encrypt($v6['opportunity_id'],'OPPORTUNITY');
			    }

			    // 分页流加载

			    $result = ['data'=>$opportunity,'formList'=>$show_list['form_list'],'pages'=>ceil($count/10)];

			    $this->ajaxReturn($result);
		    }
		    else
		    {
			    $isCreateOpportunityAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'Opportunity/create',$this->_mobile['role_id'],'crm'); //创建商机权限

			    $this->assign('isCreateOpportunityAuthView',$isCreateOpportunityAuthView);
		    }
	    }

		//合同
		if($isContractAuthView)
		{
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && $detailtype == 'contract')
			{
				$count = getCrmDbModel('contract')->where($contractfield)->count();

				$Page = new Page($count, 10);

				if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

				$contract = getCrmDbModel('contract')->where($contractfield)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

				foreach($contract as $k6=>&$v6)
				{
					$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$v6['member_id']])->field('member_id,account,name')->find();

					$v6['member_name'] = $thisMember['name'];

					$v6['detail'] = CrmgetCrmDetailList('contract',$v6['contract_id'],$this->_company_id);

					$v6['contract_id'] = encrypt($v6['contract_id'],'CONTRACT');
				}

				// 分页流加载

				$result = ['data'=>$contract,'formList'=>$show_list['form_list'],'pages'=>ceil($count/10)];

				$this->ajaxReturn($result);
			}
			else
			{
				$isCreateContractAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'Contract/create',$this->_mobile['role_id'],'crm'); //创建合同权限

				$this->assign('isCreateContractAuthView',$isCreateContractAuthView);
			}
		}

		if($isShipmentAuthView)
		{
			if(isset($_GET['request']) && $_GET['request'] == 'flow' && $detailtype == 'shipment')
			{
				$shipmentfield = ['company_id'=>$this->_company_id,'isvalid'=>'1','customer_id'=>$customer_id];

				$count = getCrmDbModel('shipment')->where($shipmentfield)->count();

				$Page = new Page($count, 5);

				if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

				$shipment = getCrmDbModel('shipment')->where($shipmentfield)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

				foreach($shipment as $k6=>&$v6)
				{
					$v6['product'] = CrmgetCrmDetailList('product',$v6['product_id'],$this->_company_id,'name');

					$v6['detail'] = CrmgetCrmDetailList('shipment',$v6['shipment_id'],$this->_company_id);

					$v6['createtime'] = getDates($v6['createtime']);

					$v6['shipment_id'] = encrypt($v6['shipment_id'],'SHIPMENT');
				}

				$show_list = CrmgetShowListField('shipment',$this->_company_id); //列表显示字段

				$result = ['data'=>$shipment,'formList'=>$show_list['form_list'],'pages'=>ceil($count/5)];

				$this->ajaxReturn($result);

			}
			else
			{
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

				$createShipment_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'shipment/create',$this->_mobile['role_id'],'crm'); //创建出货信息权限

				$editShipment_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'shipment/edit',$this->_mobile['role_id'],'crm'); //修改出货信息权限

				$deleteShipment_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'shipment/delete',$this->_mobile['role_id'],'crm'); //删除出货信息权限

				$this->assign('isCreateShipmentAuth',$createShipment_id);

				$this->assign('isEditShipmentAuth',$editShipment_id);

				$this->assign('isDeleteShipmentAuth',$deleteShipment_id);
			}
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

		$this->display();

    }

	public function draw() //客户领取
	{
		if(IS_AJAX)
		{
			$customer_id = I('post.customer_id');

			$customer_id = decrypt($customer_id,'CUSTOMER');

			if($customer_id)
			{
				if($this->_crmsite['custLimit'] == 1)
				{
					//检查是否超出领取限制
					$checkDrawCount = D('CrmCustomer')->checkDrawCount($this->_company_id,$this->member_id);

					if($checkDrawCount['msg'])
					{
						$this->ajaxReturn($checkDrawCount);
					}
				}

				$result = D('CrmCustomer')->drawCustomer($this->_company_id,$this->member_id,$customer_id,$this->_sms);
			}
			else
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
			$allot = I('post.update_member');

			$member_id = $allot['member_id'];

			$customer_id = $allot['customer_id'];

			if($member_id)
			{
				if($customer_id)
				{
					$result = D('CrmCustomer')->allotCustomer($this->_company_id,$member_id,$this->member_id,$customer_id,$this->_sms);
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
			$transfer = I('post.update_member');

			$member_id = $transfer['member_id'];

			$customer_id = $transfer['customer_id'];

			if($member_id)
			{
				if($customer_id)
				{
					$result = D('CrmCustomer')->transferCustomer($this->_company_id,$member_id,$this->member_id,$customer_id,$this->_sms);
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
			$topool = I('post.topool');

			$abandon_id = $topool['abandon_id'];

			$customer_id = $topool['customer_id'];

			if(!$abandon_id)
			{
				$result = ['status'=>0,'msg'=>L('SELECT_REASON_GIVING_UP')];
			}
			else
			{
				if($customer_id)
				{
					$result = D('CrmCustomer')->customerToPool($this->_company_id,$this->member_id,$customer_id,$abandon_id);
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

			$this->assign('clue_id', $id);
		}
		elseif($sourcetype == 'opportunity')
		{
			$id = decrypt($id, 'OPPORTUNITY');

			$detailtype = decrypt($detailtype, 'OPPORTUNITY');

			if ($detailtype)
			{
				$localurl = U('opportunity/detail', ['id' => encrypt($id, 'OPPORTUNITY'), 'detailtype' => encrypt($detailtype, 'OPPORTUNITY')]);
			}

			$this->assign('opportunity_id', $id);
		}
		else
		{
			$id = decrypt($id, 'CUSTOMER');

			$detailtype = decrypt($detailtype, 'CUSTOMER');

			if ($detailtype)
			{
				$localurl = U('detail', ['id' => encrypt($id, 'CUSTOMER'), 'detailtype' => encrypt($detailtype, 'CUSTOMER')]);
			}

			$this->assign('customer_id', $id);
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

			$fieldcontacter['company_id'] = $this->_company_id;

			$fieldcontacter['isvalid'] = '1';

			$fieldcontacter['customer_id'] = $followup['customer_id'];

			$contacter = getCrmDbModel('contacter')->where($fieldcontacter)->select();

			foreach($contacter as $k=>&$v)
			{
				$v['detail'] = CrmgetCrmDetailList('contacter',$v['contacter_id'],$this->_company_id);
			}

			$editFollow_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/edit_follow',$this->_mobile['role_id'],'crm'); //联系记录修改权限

			$this->assign('isEditFollowAuth',$editFollow_id);

			$this->assign('contacter',$contacter);

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

				$detailtype = decrypt($detailtype,'CLUE');

				if($detailtype)
				{
					$localurl = U('clue/detail',['id'=>encrypt($clue_id,'CLUE'),'detailtype'=>encrypt($detailtype,'CLUE')]);
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

	public function delete_follow($id,$follow_id,$detailtype='')
	{
		if(IS_AJAX)
		{
			$follow_id = decrypt($follow_id, 'FOLLOW');

			$where = ['company_id' => $this->_company_id, 'isvalid' => '1', 'follow_id' => $follow_id];

			if ($follow = getCrmDbModel('followup')->where($where)->find())
			{
				if($follow['clue_id'] > 0)
				{
					$clue_id = decrypt($id, 'CLUE');

					$detailtype = decrypt($detailtype, 'CLUE');

					if ($detailtype)
					{
						$localurl = U('clue/detail', ['id' => encrypt($clue_id, 'CLUE'), 'detailtype' => encrypt($detailtype, 'CLUE')]);
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
						$clue_detail = CrmgetCrmDetailList('clue', $clue_id, $this->_company_id,'name');

						D('CrmLog')->addCrmLog('follow', 3, $this->_company_id, $this->member_id, 0, 0, 0, 0, $clue_detail['name'], $follow_id,0,0,0,0,0,0,0,$clue_id);
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

            $this->ajaxReturn($result);

		}
		else
		{
			$this->common->_empty();
		}
	}

	public function create_contacter($id='',$detailtype='')
	{
		$customer_id = decrypt($id,'CUSTOMER');

		$localurl = U('contacter');

		$detailtype = decrypt($detailtype,'CUSTOMER');

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

			if($contacter_id = getCrmDbModel('contacter')->add($data['contacter']))//客户
            {

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
			
			$contacterform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($contacterform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$this->assign('contacterform',$contacterform);

			$this->display();
		}
	}

	public function checkContacter($customer_id = '')
	{
		$contacter = I('post.contacter');

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

		$ContacterCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$contacter_form,'contacter',$this->_mobile);

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

			$contacterform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contacter'])->order('orderby asc')->select();

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

			$editContacter_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/edit_contacter',$this->_mobile['role_id'],'crm'); //联系人修改权限

			$this->assign('isEditContacterAuth',$editContacter_id);

			$this->assign('contacterform',$contacterform);

			$this->assign('contacter',$contacter);

			$this->display();
		}
	}

	public function checkContacterEdit($contacter_id)
	{
		$contacter_form = I('post.contacter_form');

		$ContacterCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$contacter_form,'contacter',$this->_mobile,$contacter_id);

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

	public function delete_contacter($id,$detailtype='')
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
				if(getCrmDbModel('contacter')->where($where)->save(['isvalid'=>0]))
				{
					$contacter['detail'] = CrmgetCrmDetailList('contacter',$contacter_id,$this->_company_id);

					D('CrmLog')->addCrmLog('contacter',3,$this->_company_id,$this->member_id,$contacter['customer_id'],$contacter_id,0,0,$contacter['detail']['name']);

					$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>$localurl];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
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

	public function edit_analysis($id="",$customer_id)
	{
		$analysis_id = decrypt($id,'CUSTOMER');

		$customer_id = decrypt($customer_id,'CUSTOMER');

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

			$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS')];

			$this->ajaxReturn($result);

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

	public function create_competitor($id)
	{
		$customer_id = decrypt($id,'CUSTOMER');

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

	}
	public function checkCompetitorCreate($customer_id)
	{
		$competitor = I('post.competitor');

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

		$CompetitorCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$competitor_form,'competitor',$this->_mobile);

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

			$edit_competitor_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/edit_competitor',$this->_mobile['role_id'],'crm'); //修改竞争对手权限

			$delete_competitor_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'customer/delete_competitor',$this->_mobile['role_id'],'crm'); //删除竞争对手权限

			$this->assign('isEditCompetitorAuth',$edit_competitor_auth);

			$this->assign('isDeleteCompetitorAuth',$delete_competitor_auth);

			$this->assign('competitorform',$competitorform);

			$this->assign('competitorform1',$competitorform1);

			$this->assign('competitorform2',$competitorform2);

			$this->assign('competitor',$competitor);

			$this->assign('detailtype',$detailtype);

			$this->assign('customer_id',$customer_id);

			$this->display();
		}
	}

	public function checkCompetitorEdit($competitor_id)
	{

		$competitor_form = I('post.competitor_form');

		$CompetitorCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$competitor_form,'competitor',$this->_mobile,$competitor_id);

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

	/* 客户对话记录 */
	public function getRecord($id,$visitorid)
	{
		//$id = decrypt($id,'RECORD');

		//$visitorId = decrypt($visitorid,'RECORD');

	    $record = M('echat_record')->where(['company_id'=>$this->_company_id,'visitorId'=>$visitorid,'id'=>$id])->find();

	    $this->assign('record',$record);

		$this->display();
	}
}

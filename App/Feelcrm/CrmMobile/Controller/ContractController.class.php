<?php
namespace CrmMobile\Controller;

use CrmMobile\Common\BasicController;

use Think\Cache\Driver\Redis;
use Think\Page;

class ContractController extends BasicController
{
	protected $ContractFields = ['member_id','customer_id','opportunity_id','contract_no','contract_prefix'];

	protected $ticket_status = [];

    protected $first_status = [];

    protected $end_status = [];

	protected $company = [];

	public function _initialize()
    {
        parent::_initialize();

		$this->_company = M('company')->where(['company_id'=>$this->_company_id])->find();
    }

    public function index()
    {
        $field = ['company_id'=>$this->_company_id,'isvalid'=>'1'];

		$memberRoleArr = CrmmemberRoleCrmauth($this->_mobile,$this->_company_id,$this->member_id,'','contract');

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

	    //创建人维度查看合同权限
	    $CreaterViewContract = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'CreaterViewContract',$this->_mobile['role_id'],'crm');

	    if($CreaterViewContract)
	    {
		    $field['_string'] = getCreaterViewSql($field['member_id']);

		    unset($field['member_id']);
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

		$members = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'member_id'=>$memberRoleArr])->field('name,member_id')->select();

		$count = getCrmDbModel('contract')->where($field)->count();

		$Page = new Page($count, 10);

        if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$contract = getCrmDbModel('contract')->where($field)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

		$contract = D('CrmContract')->getMobileListInfo($contract,$this->_company_id);

		// 分页流加载
		if(isset($_GET['request']) && $_GET['request'] == 'flow')
        {
            $result = ['data'=>$contract,'pages'=>ceil($count/10)];

            $this->ajaxReturn($result);
        }
		else
		{

			$create_contract = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'contract/create',$this->_mobile['role_id'],'crm'); //创建合同权限

			$this->assign('isCreateContractAuth',$create_contract);

			$this->assign('contract',$contract);

			$this->assign('members',$members);

			$this->display();
		}
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

	    $isDetailAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'ContractDetail',$this->_mobile['role_id'],'crm');//合同详情权限

	    $isProductAuthView = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'ContractProduct',$this->_mobile['role_id'],'crm');//合同产品权限

		$this->assign('isDetailAuthView',$isDetailAuthView);

		$this->assign('isProductAuthView',$isProductAuthView);

		$edit_contract_id = D('RoleAuth')->checkRoleAuthByMenu($this->_mobile['company_id'],'contract/edit',$this->_mobile['role_id'],'crm'); //修改合同权限

		$this->assign('isEditContractAuth',$edit_contract_id);

		$contract = getCrmDbModel('contract')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id,'isvalid'=>'1'])->find();

		if(!$contract)
		{
			$this->common->_empty();

			die;
		}

		$thisMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$contract['member_id']])->field('member_id,account,name,group_id')->find();

		$contract['member_name'] = $thisMember['name'];

		$contract['group_id'] = $thisMember['group_id'];

		$form_description = getCrmLanguageData('form_description');
		
		$contractform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contract','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		$contract['detail'] = CrmgetCrmDetailList('contract',$contract_id,$this->_company_id);

		$contractform2 = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contract','form_type'=>['eq','textarea']])->order('orderby asc')->select();

		$this->assign('contractform',$contractform);

		$this->assign('contractform2',$contractform2);

		if($isDetailAuthView)
		{
			$createMember = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$contract['creater_id']])->field('member_id,account,name')->find();

			$contract['creater_name'] = $createMember['name'];

			$uploadFiles = getCrmDbModel('upload_file')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract['contract_id'],'file_form'=>'contract'])->select();

			$contract['contract_img'] = json_decode($contract['contract_img']);

			$contract['createFiles'] = $uploadFiles;

			$customer = getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'customer_id'=>$contract['customer_id']])->find();

			$customer['detail'] = CrmgetCrmDetailList('customer',$contract['customer_id'],$this->_company_id);

			if($contract['opportunity_id'])
			{
				$opportunity = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'opportunity_id'=>$contract['opportunity_id']])->find();

				$opportunity['detail'] = CrmgetCrmDetailList('opportunity',$contract['opportunity_id'],$this->_company_id);

				$this->assign('opportunity',$opportunity);
			}

			$group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

			$this->assign('customer',$customer);

			$this->assign('groupList',$group);
		}
		else
		{
			$this->common->_empty();

			die;
		}

		$this->assign('contract',$contract);

		if($isProductAuthView)
		{
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

		$this->display();
    }

	public function create($id='',$detailtype='')
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

				D('CrmLog')->addCrmLog('contract',1,$this->_company_id,$this->member_id,$data['contract']['customer_id'],0,0,0,$data['contract_detail']['name']['form_content'],0,0,0,0,$contract_id);

				foreach($data['contractPro'] as &$v2)
				{
					$v2['contract_id'] = $contract_id;

					getCrmDbModel('contract_product')->add($v2); //添加合同产品
				}

				getCrmDbModel('customer')->where(['company_id'=>$this->_company_id,'customer_id'=>$data['contract']['customer_id'],'isvalid'=>1])->save(['is_trade'=>1]);

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

			$this->assign('contractform',$contractform);

			$this->display();
		}
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

	public function checkCreate()
	{
		//var_dump(I('post.'));die;
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

		$contract['status'] = 2;

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

		$photo = I('post.photo');

		if($photo)
		{
			$contract['contract_img'] = json_encode($photo);
		}

		$contract_form = I('post.contract_form');

        $ContractCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$contract_form,'contract',$this->_mobile);

		if($ContractCheckForm['detail'])
		{
			$contract_detail = $ContractCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ContractCheckForm);
		}

		$contractPro = I('post.orderPro');

		if(!$contractPro) $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_ADD_PRODUCT_FIRST')]);

		$product = [];

		foreach($contractPro as $key=>&$val)
		{
			/*if(!$val['unit_price'])
			{
				$this->ajaxReturn(['status'=>0,'msg'=>'请填写售价']);
			}

			if(!$val['num'])
			{
				$this->ajaxReturn(['status'=>0,'msg'=>'请填写数量']);
			} */

			//$val['product_id'] = decrypt($val['product_id'],'PRODUCT');

			$price = CrmgetCrmDetailList('product',$val,$this->_company_id,'list_price');

			$product[$key]['unit_price'] = $price['list_price'];

			$product[$key]['total_price'] = $price['list_price'];

			$product[$key]['num'] = 1;

			$product[$key]['product_id'] = $val;

			$product[$key]['customer_id'] = $contract['customer_id'];

			$product[$key]['company_id'] = $this->_company_id;
		}

		return ['contract'=>$contract,'contract_detail'=>$contract_detail,'contractPro'=>$product];

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

					getCrmDbModel('contract_detail')->add($v);  //添加合同详情
				}

				//            更新上传文件信息
				$files = isset($_POST['file']) ? I('post.file') : [];

				$delFiles = isset($_POST['delFile']) ? I('post.delFile') : [];

				$this->updateUploadFile($files,$delFiles,$contract_id,$this->_company_id,'contract');

				D('CrmLog')->addCrmLog('contract',2,$this->_company_id,$this->member_id,$contract['customer_id'],0,0,0,$data['contract_detail']['name']['form_content'],0,0,0,0,$contract_id);

				getCrmDbModel('contract_product')->where(['contract_id'=>$contract_id,'company_id'=>$this->_company_id])->delete();

				foreach($data['contractPro'] as &$v2)
				{
					$v2['contract_id'] = $contract_id;

					getCrmDbModel('contract_product')->add($v2); //添加合同产品
				}

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];
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

			//合同附件
            $contract['createFiles'] = getCrmDbModel('upload_file')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract['contract_id'],'file_form'=>'contract'])->select();

            $contract['qiniu_domain'] = M('qiniu')->where(['company_id'=>$this->_company_id])->getField('domain');

			$memberRoleArr = CrmmemberRoleCrmauth($this->_mobile,$this->_company_id,$this->member_id);

			$contractPro = getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id])->select();

			$product_type_field = getCrmLanguageData('type_name');

			foreach($contractPro as $k1=>&$v1)
			{
				$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'product_id'=>$v1['product_id'],'isvalid'=>'1'])->find();

				$productform = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'product'])->order('orderby asc')->select();

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

			$members = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'feelec_opened'=>10])->field('member_id,account,name')->fetchAll();

			$this->assign('members',$members);

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

		$photo = I('post.photo');

		if($photo)
		{
			$contract['contract_img'] = json_encode($photo);
		}

		$contract_form = I('post.contract_form');

		$ContractCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$contract_form,'contract',$this->_mobile,$contract_id);

		if($ContractCheckForm['detail'])
		{
			$contract_detail = $ContractCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ContractCheckForm);
		}

		$contractPro = I('post.orderPro');

		if(!$contractPro) $this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_ADD_PRODUCT_FIRST')]);

		$product = [];

		$customer_id = getCrmDbModel('contract')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id,'isvalid'=>'1'])->getField('customer_id');

		foreach($contractPro as $key=>&$val)
		{
			/*if(!$val['unit_price'])
			{
				$this->ajaxReturn(['status'=>0,'msg'=>'请填写售价']);
			}

			if(!$val['num'])
			{
				$this->ajaxReturn(['status'=>0,'msg'=>'请填写数量']);
			} */

			//$val['product_id'] = decrypt($val['product_id'],'PRODUCT');

			$price = CrmgetCrmDetailList('product',$val,$this->_company_id,'list_price');

			$contract_product = getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id,'product_id'=>$val])->find();

			if($contract_product)
			{
				$product[$key]['unit_price'] = $contract_product['unit_price'];

				$product[$key]['total_price'] = $contract_product['total_price'];

				$product[$key]['num'] = $contract_product['num'];
			}
			else
			{
				$product[$key]['unit_price'] = $price['list_price'];

				$product[$key]['total_price'] = $price['list_price'];

				$product[$key]['num'] = 1;
			}

			$product[$key]['product_id'] = $val;

			$product[$key]['customer_id'] = $customer_id;

			$product[$key]['company_id'] = $this->_company_id;
		}

		return ['contract'=>$contract,'contract_detail'=>$contract_detail,'contractPro'=>$product];
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
}

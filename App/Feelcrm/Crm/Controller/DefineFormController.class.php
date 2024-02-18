<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

class DefineFormController extends BasicController
{
	protected $DefineFormFields = ['form_id','type','form_name','form_description','name_en','name_jp','form_explain','form_type','form_option','is_required','is_unique','show_list','closed','orderby','is_default'];

	protected $DefineFormType = ['clue','customer','opportunity','contacter','product','contract','order','analysis','competitor','shipment'];

	//线索
	public function clue()
	{
		$where['type'] = 'clue';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp|form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('clueform',$list);

		$this->assign('clueFormJson',json_encode($list));

		$this->display();
	}

	public function customer()
	{
		$where['type'] = 'customer';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp|form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('customerform',$list);

		$this->assign('customerFormJson',json_encode($list));

		$this->display();
	}

	public function opportunity()
	{
		$where['type'] = 'opportunity';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp|form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('opportunityform',$list);

		$this->assign('opportunityFormJson',json_encode($list));

		$this->display();
	}

	public function contacter()
	{
		$where['type'] = 'contacter';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp|form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('contacterform',$list);

		$this->assign('contacterFormJson',json_encode($list));

		$this->display();
	}

	public function product()
	{
		$where['type'] = 'product';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp||form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('productform',$list);

		$this->assign('productFormJson',json_encode($list));

		$this->display();
	}

	public function order()
	{
		$where['type'] = 'order';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp||form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('orderform',$list);

		$this->assign('orderFormJson',json_encode($list));

		$this->display();
	}

	public function analysis()
	{
		$where['type'] = 'analysis';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp|form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('analysisform',$list);

		$this->assign('analysisFormJson',json_encode($list));

		$this->display();
	}

	public function competitor()
	{
		$where['type'] = 'competitor';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp|form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('competitorform',$list);

		$this->assign('competitorFormJson',json_encode($list));

		$this->display();
	}

	public function contract()
	{
		$where['type'] = 'contract';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp|form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('contractform',$list);

		$this->assign('contractFormJson',json_encode($list));

		$this->display();
	}

	//出货
	public function shipment()
	{
		$where['type'] = 'shipment';

		$where['company_id'] = $this->_company_id;

		if($keyword = I('get.keyword'))
		{
			$where['form_name|form_description|name_en|name_jp|form_type'] = array('LIKE', '%' . $keyword . '%');

			$this->assign('keyword', $keyword);
		}

		$field = getCrmLanguageData('form_description');

		$list = getCrmDbModel('define_form')->field(['*',$field])->where($where)->order('orderby')->select();

		$this->assign('shipmentform',$list);

		$this->assign('shipmentFormJson',json_encode($list));

		$this->display();
	}

	//创建自定义字段
	public function create($type = '')
	{
		$type = decrypt($type,'DEFINEFORM');

		if(IS_POST)
		{
			$define_form = $this->checkCreate();

			$form_view_range = I('post.form_view_range');

			$form_edit_auth = I('post.form_edit_auth');

			if($form_view_range)
			{
				$define_form['role_id'] = $form_view_range;
			}

			if($form_edit_auth)
			{
				$define_form['member_id'] = $form_edit_auth;
			}

			$define_form['company_id'] = $this->_company_id;

			if($form_id = getCrmDbModel('define_form')->add($define_form))
			{
				if(in_array($define_form['type'],['clue','customer','contacter','opportunity','product','contract']))
				{
					if($define_form['type'] == 'contacter')
					{
						D('Excel')->createCrmImportTemp($this->_company_id,'customer');
					}
					else
					{
						D('Excel')->createCrmImportTemp($this->_company_id,$define_form['type']);
					}
				}
				
			    $result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U($type)];
			}
			else
			{
			    $result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
			}

            $this->ajaxReturn($result);
		}
		else
		{
			$this->assign('type',$type);

			$role = M('Role')->where(['company_id'=>$this->_company_id,'closed'=>0])->field('role_id,role_name')->select();

			foreach ($role as $key => &$val)
			{
				$val['id'] = $val['role_id'];
				$val['name'] = $val['role_name'];
			}

			$this->assign('roleList',json_encode($role));

			$member = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->field('member_id,name')->select();

			foreach ($member as $key=>&$val)
			{
				$val['id'] = $val['member_id'];
			}

			$this->assign('memberList',json_encode($member));

			$this->display();
		}
	}

	private function checkCreate()
	{
		$data = checkFields(I('post.define_form'), $this->DefineFormFields);

        $data['type'] = decrypt($data['type'],'DEFINEFORM');

		if(!in_array($data['type'],$this->DefineFormType))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('SUBMIT_FAILED')]);
		}

		if(empty($data['form_type']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('SELECT_FORM_TYPE')]);
		}

		if(empty($data['form_name']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_FIELD_NAME')]);
		}
		else if(!isFormField($data['form_name']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('FIELD_NAME_NOT')]);
		}

		if(empty($data['form_description']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_FORM_NAME')]);
		}

		if(in_array($data['form_type'], array('radio','select','checkbox','select_text')) && empty($data['form_option']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_FIELD_OPTION')]);
		}

		$data['is_required'] = (int) $data['is_required'];

		if($data['is_required'] != 0 && $data['is_required'] != 1)
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('FORM_REQUIRE_NOT')]);
		}

		$data['closed'] = (int) $data['closed'];

		if($data['closed'] != 0 && $data['closed'] != 1)
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('FORM_ENABLE_NOT')]);
		}

		$where = ["form_name"=>$data['form_name'],"company_id"=>$this->_company_id,"type"=>$data['type']];

		if(getCrmDbModel('define_form')->where($where)->getField('form_id'))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('FIELD_NAME_REPEAT')]);
		}

		$where = ["form_description"=>$data['form_description'],"company_id"=>$this->_company_id,"type"=>$data['type']];

		if(getCrmDbModel('define_form')->where($where)->getField('form_id'))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('DUPLICATE_FORM_NAME')]);
		}

		$data['orderby'] = (int) $data['orderby'];

		return $data;
	}

	public function edit($id='',$type='')
	{
		$form_id = decrypt($id,'DEFINEFORM');

		$type = decrypt($type,'DEFINEFORM');

		$where = ['company_id'=>$this->_company_id,'form_id'=>$form_id,'type'=>$type];

	    if(!$detail = getCrmDbModel('define_form')->where($where)->find()) $this->error(L('FORM_NOT'),'',3);

		if(IS_POST)
		{
			$define_form = $this->checkEdit($form_id);

			$form_view_range = I('post.form_view_range');

			$form_edit_auth = I('post.form_edit_auth');

			if(isset($_POST['form_view_range']))
			{
				$define_form['role_id'] = $form_view_range;
			}

			if(isset($_POST['form_edit_auth']))
			{
				$define_form['member_id'] = $form_edit_auth;
			}

			$save = getCrmDbModel('define_form')->where($where)->save($define_form);

			if($save === false)
			{
			    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
				if(in_array($define_form['type'],['clue','customer','contacter','opportunity','product','contract']))
				{
					if($define_form['type'] == 'contacter')
					{
						D('Excel')->createCrmImportTemp($this->_company_id,'customer');
					}
					else
					{
						D('Excel')->createCrmImportTemp($this->_company_id,$define_form['type']);
					}
				}

				$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U($type)];
			}

            $this->ajaxReturn($result);
		}
		else
		{
			$role = M('Role')->where(['company_id'=>$this->_company_id,'closed'=>0])->field('role_id,role_name')->select();

			foreach ($role as $key => &$val)
			{
				$val['id'] = $val['role_id'];
				$val['name'] = $val['role_name'];
			}

			$this->assign('roleList',json_encode($role));

			$member = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->field('member_id,name')->select();

			foreach ($member as $key=>&$val)
			{
				$val['id'] = $val['member_id'];
			}

			$this->assign('memberList',json_encode($member));

			if($detail['role_id'])
			{
				$memberRole = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10,'role_id'=>['in',$detail['role_id']]])->field('member_id,name')->select();

				foreach ($memberRole as $key=>&$val)
				{
					$val['id'] = $val['member_id'];
				}

				$this->assign('memberRole',json_encode($memberRole));
			}

			$this->assign('type',$type);

			$this->assign('detail',$detail);

			$this->display();
		}
	}

	private function checkEdit($id)
	{
		$data = checkFields(I('post.define_form'), $this->DefineFormFields);

		$data['form_id'] = decrypt($data['form_id'],'DEFINEFORM');

		$data['type'] = decrypt($data['type'],'DEFINEFORM');

		if(!in_array($data['type'],$this->DefineFormType))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('SUBMIT_FAILED')]);
		}

		if(empty($data['form_name'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_FIELD_NAME')]);

		$where = ["form_name"=>$data['form_name'],"company_id"=>$this->_company_id,"type"=>$data['type']];

		if($define_form_id = getCrmDbModel('define_form')->where($where)->getField('form_id'))
		{
			if($define_form_id != $id)
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('FIELD_NAME_REPEAT')]);
			}
		}

		if(empty($data['form_description']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_FORM_NAME')]);
		}

		$where = ["form_description"=>$data['form_description'],"company_id"=>$this->_company_id,"type"=>$data['type']];

		if($define_form_id = getCrmDbModel('define_form')->where($where)->getField('form_id'))
		{
			if($define_form_id != $id)
			{
				$this->ajaxReturn(['status' => 0, 'msg' => L('DUPLICATE_FORM_NAME')]);
			}
		}

		if(in_array($data['form_type'], array('radio','select','checkbox','select_text')) && empty($data['form_option']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_FIELD_OPTION')]);
		}

		$data['is_required'] = (int) $data['is_required'];

		if($data['is_required'] != 0 && $data['is_required'] != 1)
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('FORM_REQUIRE_NOT')]);
		}

		$data['closed'] = (int) $data['closed'];

		if($data['closed'] != 0 && $data['closed'] != 1)
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('FORM_ENABLE_NOT')]);
		}

		$data['orderby'] = (int) $data['orderby'];

		unset($data['form_name'],$data['form_type']);

		return $data;
	}

	//删除
	public function delete($id='',$type='')
	{
	    if(IS_AJAX)
	    {
	        $form_id = decrypt($id,'DEFINEFORM');

	        $type = decrypt($type,'DEFINEFORM');

			$where = ['form_id'=>$form_id,'company_id'=>$this->_company_id];

			if($define_form = getCrmDbModel('define_form')->where($where)->find())
			{
				$detail_id = getCrmDbModel($type.'_detail')->where(['form_id'=>$form_id])->getField($type.'_id');

				if($detail_id>0)
				{
					$result = ['status'=>0,'msg'=>L('FAILED_DELETE_FORM_DELETE_INFO')];
				}
				else if(getCrmDbModel('define_form')->where($where)->delete())
				{
					if(in_array($define_form['type'],['clue','customer','contacter','opportunity','product','contract']))
					{
						if($define_form['type'] == 'contacter')
						{
							D('Excel')->createCrmImportTemp($this->_company_id,'customer');
						}
						else
						{
							D('Excel')->createCrmImportTemp($this->_company_id,$define_form['type']);
						}
					}

					$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>U($type)];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('FORM_NOT')];
			}

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
    }


}

<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

class ProductController extends BasicController
{
	protected $ProductFields = ['type_id','closed'];

	public function _initialize()
    {
        parent::_initialize();

		$regionJson = C('regionJson');

		$this->assign('regionJson',$regionJson);

    }

	public function index($id = '')
	{
		$type_id = decrypt($id,'PRODUCT');

		if($type_id)
		{
			$type_array = $this->getTypeid($type_id,1);

			$type_array = implode(',',$type_array);

			$field['company_id'] = $this->_company_id;

			$field['isvalid'] = '1';

			$field['type_id'] = array('in',$type_array);

		}
		else
		{
			$field['company_id'] = $this->_company_id;

			$field['isvalid'] = '1';
		}

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword = unserialize(urldecode(I('get.highKeyword')));

			if(!$highKeyword)
			{
				$highKeyword = I('get.highKeyword');

				$_GET['highKeyword'] = urlencode(serialize(I('get.highKeyword')));
			}

			$productHighKey = D('CrmHighKeyword')->productHighKey($this->_company_id,$highKeyword);

			if($productHighKey['field'])
			{
				$field = array_merge($field,$productHighKey['field']);
			}

			$this->assign('highKeyword',$highKeyword);
		}
		else
		{
			if($keyword = I('get.keyword'))
			{
				$keywordField = CrmgetDefineFormField($this->_company_id,'product','name,product_num',$keyword);

				$field['product_id'] = $keywordField ? ['in',$keywordField] : '0';

				$this->assign('keyword', $keyword);
			}
		}

		$nowPage = I('get.p');

		$count = getCrmDbModel('product')->where($field)->count();

		$Page = new \Think\Page($count, 10);

		$this->assign('pageCount', $count);

        if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$product = getCrmDbModel('product')->where($field)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

		$show_list = CrmgetShowListField('product',$this->_company_id); //产品列表显示字段

		$export_field = C('EXPORT_PRODUCT_FIELD');

		$export_arr = C('EXPORT_PRODUCT_ARR');

		$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'product');

		$product_type_field = getCrmLanguageData('type_name');
		
		foreach($product as $key=>&$val)
		{
			$val['detail'] = CrmgetCrmDetailList('product',$val['product_id'],$this->_company_id,$show_list['form_description']);

			$type_name = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'type_id'=>$val['type_id']])->getField($product_type_field);

			$val['type_name'] = $type_name;
		}

		$form_description = getCrmLanguageData('form_description');
			
		$defineformlist = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'product','form_type'=>['neq','textarea']])->order('orderby asc')->select();

		foreach($defineformlist as $k=>&$v)
		{
			if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
			{
				$v['option'] = explode('|',$v['form_option']);
			}
		}

		$this->assign('defineformlist',$defineformlist);

		$export_product = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'product/export',$this->_member['role_id'],'crm'); //导出产品权限

		$import_product = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'product/import',$this->_member['role_id'],'crm'); //导入产品权限

		$this->assign('isExportProductAuth',$export_product);

		$this->assign('isImportProductAuth',$import_product);

		if($import_product)
		{
			$importTemp = getCrmDbModel('temp_file')->where(['company_id'=>$this->_company_id,'type'=>'product'])->getField('file_link');

			$this->assign('importTemp',$importTemp);
		}

		$this->assign('type_id',$type_id);

		$this->assign('product',$product);

		$type_field = getCrmLanguageData('type_name');

		$product_type = getCrmDbModel('product_type')->field(['*',$type_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

		$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

		$product_type = CrmfetchAll($product_type,'type_id');

		$product_type_h = $this->resultCategory($product_type,count($product_type),$type_id);

		$this->assign('product_type',$product_type);

		$this->assign('product_type_h',$product_type_h);

		$this->assign('formList',$show_list['form_list']);

		$this->assign('export_list',$export_list);

		$this->assign('nowPage',$nowPage);

		session('productExportWhere',$field);

		session('ExportOrder',null);

		$this->display();

	}

	public function getTypeid($type_id,$isfirst = 0)
	{
		//$type_array[] = $type_id;

		$isfirst != 1 || $type_array[] = $type_id;

		$product_type = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'closed'=>0,'parent_id'=>$type_id])->select();

		foreach($product_type as $key=>$val)
		{
			$type_array[] .= $val['type_id'];

			$count = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'closed'=>0,'parent_id'=>$val['type_id']])->count();

			if($count)
			{
				$type_array = array_merge($type_array,$this->getTypeid($val['type_id']));
			}
		}

		return $type_array;
	}

	public function resultCategory($data,$num = 0,$type_id = 0,$type = 0,$parent_id = 0,$lev = 0)
	{

		$i = 1;

		$count = count($data);

		foreach($data as $key=>$val)
		{
			if($lev > 0)
			{
				$pre = '';
				for($j = 1;$j<=$lev;$j++)
				{
					$pre .= '--';
				}
			}

			if($type != 1)
			{
				if($type_id == $val['type_id'])
				{
					$prolink = '<a href="'.U('index',['id'=>encrypt($val['type_id'],'PRODUCT')]).'" class="product-link product-link-curr">';
				}
				else
				{
					$prolink = '<a href="'.U('index',['id'=>encrypt($val['type_id'],'PRODUCT')]).'" class="product-link">';
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
			}
			else
			{
				$dataLevel = $lev + 1;

				if($parent_id == $val['type_id'])
				{
					$arr[] = '<option value="'.$val['type_id'].'" data-level="'.$dataLevel.'" selected >'.$pre.$val['type_name'].'</option>';
				}
				else
				{
					$arr[] = '<option value="'.$val['type_id'].'" data-level="'.$dataLevel.'" >'.$pre.$val['type_name'].'</option>';
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
				$arr = array_merge($arr,$this->resultCategory($val['subClass'],$num,$type_id,$type,$parent_id,$lev+1));
			}

			if($type != 1)
			{
				$arr[] .= '</li></ul>';
			}

			$i++;
		}
		return $arr;
	}



	//创建产品
	public function create()
	{
		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'product'];

		if(IS_POST)
		{
			$data = $this->checkCreate();

			if($product_id = getCrmDbModel('product')->add($data['product']))//产品
            {

				foreach($data['product_detail'] as &$v)
				{
					$v['product_id'] = $product_id;

					$v['company_id'] = $this->_company_id;

					if(is_array($v['form_content']))
					{
						$v['form_content'] = implode(',',$v['form_content']);
					}

					getCrmDbModel('product_detail')->add($v);  //添加产品详情
				}

				D('CrmLog')->addCrmLog('product',1,$this->_company_id,$this->member_id,0,0,0,$product_id,$data['product_detail']['name']['form_content']);

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('index')];

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
		
			$productform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($productform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$product_type_field = getCrmLanguageData('type_name');
			
			$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

			$product_type_h = $this->resultCategory($product_type,0,0,1);

			$this->assign('product_type',$product_type);

			$this->assign('product_type_h',$product_type_h);

			$this->assign('productform',$productform);

			$this->display();
		}
	}

	public function checkCreate()
	{
		$product = I('post.product');

		if(!$product['type_id'])
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('SELECT_PRODUCT_CATEGORY')]);
		}
		else
		{
			$product_type = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'closed'=>0,'type_id'=>$product['type_id']])->find();

			if(!$product_type)
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('PARAMETER_ERROR')]);
			}
		}

		$proPhoto = I('post.proPhoto');

		if($proPhoto)
		{
			$product['product_img'] = $proPhoto;
		}

		$product['company_id'] = $this->_company_id;

		$product['closed'] = 0;

		$product['createtime'] = NOW_TIME;

		$product_form = I('post.product_form');

		$ProductCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$product_form,'product',$this->_member);

		if($ProductCheckForm['detail'])
		{
			$product_detail = $ProductCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ProductCheckForm);
		}

		return ['product'=>$product,'product_detail'=>$product_detail];
	}

	public function edit($id)
	{
		$product_id = decrypt($id,'PRODUCT');

		if(IS_POST)
		{
			$data = $this->checkEdit($product_id);

			$save = getCrmDbModel('product')->where(['product_id'=>$product_id,'company_id'=>$this->_company_id,'isvalid'=>'1'])->save($data['product']);

			if($save === false)
			{
			    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
				getCrmDbModel('product_detail')->where(['product_id'=>$product_id,'company_id'=>$this->_company_id])->delete();

				foreach($data['product_detail'] as &$v)
				{
					$v['product_id'] = $product_id;

					$v['company_id'] = $this->_company_id;

					if(is_array($v['form_content']))
					{
						$v['form_content'] = implode(',',$v['form_content']);
					}

					getCrmDbModel('product_detail')->add($v);  //添加联系人详情
				}

				D('CrmLog')->addCrmLog('product',2,$this->_company_id,$this->member_id,0,0,0,$product_id,$data['product_detail']['name']['form_content']);

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>U('index')];
			}

			$this->ajaxReturn($result);

		}
		else
		{
			$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'product_id'=>$product_id,'isvalid'=>'1'])->find();
			
			$form_description = getCrmLanguageData('form_description');

			$productform = getCrmDbModel('define_form')->field(['*',$form_description])->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'product'])->order('orderby asc')->select();

			foreach($productform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$product_detail = getCrmDbModel('product_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'product_id'=>$product_id])->find();

				if($v['form_type']=='region')
				{
					if($product_detail['form_content'])
					{
						$region_detail = explode(',',$product_detail['form_content']);

						$product[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$product[$v['form_name']]['defaultProv'] = $region_detail[1];

						$product[$v['form_name']]['defaultCity'] = $region_detail[2];

						$product[$v['form_name']]['defaultArea'] = $region_detail[3];

					}
				}else
				{
					$product[$v['form_name']] = $product_detail['form_content'];
				}
			}

			$product_type_field = getCrmLanguageData('type_name');
			
			$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

			$product_type_h = $this->resultCategory($product_type,0,0,1,$product['type_id']);

			$this->assign('product_type',$product_type);

			$this->assign('product_type_h',$product_type_h);

			$this->assign('productform',$productform);

			$this->assign('product',$product);

			$this->display();
		}
	}

	public function checkEdit($product_id)
	{

		$product = I('post.product');

		if(!$product['type_id'])
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('SELECT_PRODUCT_CATEGORY')]);
		}
		else
		{
			$product_type = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'closed'=>0,'type_id'=>$product['type_id']])->find();

			if(!$product_type)
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('PARAMETER_ERROR')]);
			}
		}

		$proPhoto = I('post.proPhoto');

		if($proPhoto)
		{
			$product['product_img'] = $proPhoto;
		}

		$product_form = I('post.product_form');

		$ProductCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$product_form,'product',$this->_member,$product_id);

		if($ProductCheckForm['detail'])
		{
			$product_detail = $ProductCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ProductCheckForm);
		}

		return ['product'=>$product,'product_detail'=>$product_detail];
	}

	//删除
	public function delete($id='',$type_id='')
	{
	    if(IS_AJAX)
	    {
		    $ids = I('post.ids');

		    $recover_product = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'recover/product',$this->_member['role_id'],'crm');

		    $type_id = decrypt($type_id, 'PRODUCT');

		    $localurl = U('index',['id'=>encrypt($type_id,'PRODUCT')]);

		    if($ids && count($ids) > 0)
		    {
			    foreach($ids as $k=>$v)
			    {
				    $product_id = decrypt($v,'PRODUCT');

				    $where = ['company_id'=>$this->_company_id,'product_id'=>$product_id];

				    $product = getCrmDbModel('product')->where($where)->field('isvalid')->find();

				    if(!$product)
				    {
					    $this->ajaxReturn(['status'=>0,'msg'=>L('PRODUCT_DOES_NOT_EXIST')]);
				    }

				    if(getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'product_id'=>$product_id])->getField('id'))
				    {
						$this->ajaxReturn(['status'=>0,'msg'=>L('ALREADY_CONTRACT_RELATED_PRODUCT')]);
				    }

				    $product_detail = CrmgetCrmDetailList('product', $product_id, $this->_company_id,'name');

				    if($product['isvalid'] == 1)
				    {
					    $delete = getCrmDbModel('product')->where($where)->save(['isvalid'=>0]);
				    }
				    else
				    {
					    if($recover_product) $localurl = U('Recover/product');

					    $delete = getCrmDbModel('product')->where($where)->delete();

					    if($delete)
					    {
						    getCrmDbModel('product_detail')->where($where)->delete();
					    }
				    }

				    if($delete)
				    {
				    	if($product['isvalid'] == 1)
				    	{
						    D('CrmLog')->addCrmLog('product', 3, $this->_company_id, $this->member_id, 0, 0, 0, $product_id, $product_detail['name']);
					    }
				    	else
				        {
					        D('CrmLog')->addCrmLog('product', 15, $this->_company_id, $this->member_id, 0, 0, 0, $product_id, $product_detail['name']);
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
			    $product_id = decrypt($id, 'PRODUCT');

			    $where = ['product_id' => $product_id, 'company_id' => $this->_company_id];

		        $product = getCrmDbModel('product')->where($where)->field('isvalid')->find();

		        if(!$product)
		        {
			        $this->ajaxReturn(['status'=>0,'msg'=>L('PRODUCT_DOES_NOT_EXIST')]);
		        }

		        if(getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'product_id'=>$product_id])->getField('id'))
		        {
			        $this->ajaxReturn(['status'=>0,'msg'=>L('ALREADY_CONTRACT_RELATED_PRODUCT')]);
		        }

		        $product_detail = CrmgetCrmDetailList('product', $product_id, $this->_company_id,'name');

		        if($product['isvalid'] == 1)
		        {
			        $delete = getCrmDbModel('product')->where($where)->save(['isvalid'=>0]);
		        }
		        else
		        {
			        if($recover_product) $localurl = U('Recover/product');

			        $delete = getCrmDbModel('product')->where($where)->delete();

			        if($delete)
			        {
				        getCrmDbModel('product_detail')->where($where)->delete();
			        }
		        }

		        if($delete)
		        {
			        if($product['isvalid'] == 1)
			        {
				        D('CrmLog')->addCrmLog('product', 3, $this->_company_id, $this->member_id, 0, 0, 0, $product_id, $product_detail['name']);
			        }
			        else
			        {
				        D('CrmLog')->addCrmLog('product', 15, $this->_company_id, $this->member_id, 0, 0, 0, $product_id, $product_detail['name']);
			        }

			        $result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>$localurl];
		        }
		        else
		        {
			        $result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
		        }
		    }

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
    }

	public function closed($id,$type,$type_id='')
	{
		if(IS_AJAX)
		{
			$product_id = decrypt($id,'PRODUCT');

			$closed = decrypt($type,'PRODUCT');

			$type_id = decrypt($type_id,'PRODUCT');

			$where = ['product_id'=>$product_id,'company_id'=>$this->_company_id,'isvalid'=>1];

			if(getCrmDbModel('product')->where($where)->getField('product_id'))
			{
				if(getCrmDbModel('product')->where($where)->save(['closed'=>$closed]))
				{
					$product_detail = CrmgetCrmDetailList('product',$product_id,$this->_company_id);

					if($closed == 1)
					{
						D('CrmLog')->addCrmLog('product',12,$this->_company_id,$this->member_id,0,0,0,$product_id,$product_detail['name']);
					}
					else
					{
						D('CrmLog')->addCrmLog('product',11,$this->_company_id,$this->member_id,0,0,0,$product_id,$product_detail['name']);
					}

					$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('index',['id'=>encrypt($type_id,'PRODUCT')])];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
				}

			}
			else
			{
				$result = ['status'=>0,'msg'=>L('PRODUCT_DOES_NOT_EXIST')];
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
		$tableHeader = session('ProductExcelData')['header'];

		$letter      = session('ProductExcelData')['letter'];

		$excelData   = session('ProductExcelData')['excelData'];

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

		session('ProductExcelData',$excel);

		$this->ajaxReturn(['msg'=>'success']);
	}

	public function export($action = '')
	{
		if(IS_AJAX)
        {
			$exportData = $tableHeader = [];

			if($action == 'index')
			{
				$export_field = C('EXPORT_PRODUCT_FIELD');

				$export_arr = C('EXPORT_PRODUCT_ARR');

				$where = session('productExportWhere');

				$pagecount = 10;

				$data = D('CrmExport')->getDataList($where,$pagecount,'product');

				if($data['msg'])
				{
					$this->ajaxReturn(['msg'=>$data['msg']]);die;
				}
				else
				{
					$product = $data['data'];
				}

				$exportList = $data['exportList'];

				$export_list = D('CrmExport')->getExportList($export_field,$export_arr,$this->_company_id,'product',$exportList);

				$show_list = $export_list['show_list'];

				$tableHeader = $export_list['tableHeader'];

				$exportData = D('CrmExport')->getProductList($product,$this->_company_id,$exportList,$show_list);

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

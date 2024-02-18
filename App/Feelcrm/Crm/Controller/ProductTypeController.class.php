<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

class ProductTypeController extends BasicController
{
	protected $ProductTypeFields = ['type_name','name_en','name_jp','parent_id','type_describe','closed'];


	public function index()
	{
		$field = getCrmLanguageData('type_name');

		$product_type = getCrmDbModel('product_type')->field(['*',$field])
			->where(['company_id'=>$this->_company_id,'closed'=>0])
			->select();

		$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

		$product_type = CrmfetchAll($product_type,'type_id');

		$product_type_h = $this->resultCategory($product_type,1);

		$this->assign('product_type',$product_type);

		$this->assign('product_type_h',$product_type_h);

		$this->display();

	}


	public function resultCategory($data,$type,$parent_id = 0,$lev = 0)
	{
		//$arr = array();
		foreach($data as $key=>$val)
		{
			if($lev > 0)
			{
				$pre = '';
				for($i = 1;$i<=$lev;$i++)
				{
					$pre .= '--';
				}
			}
			if($type == 1)
			{
				if($pre)
				{
					$arr[] = '<tr><td>'.$pre.$val['type_name'].'</td><td>'.$val['type_describe'].'</td><td class="listOperate"><i class="iconfont icon-dian"></i><div class="operate hidden">'.FEELCRM('productType/edit',['id'=>encrypt($val['type_id'],'TYPE')],L('EDITOR'),'','edt').FEELCRM('productType/delete',['id'=>encrypt($val['type_id'],'TYPE')],L('DELETE'),'','async').'</div></td></tr>';
				}
				else
				{
					$arr[] = '<tr><td class="bold">'.$val['type_name'].'</td><td>'.$val['type_describe'].'</td><td class="listOperate"><i class="iconfont icon-dian"></i><div class="operate hidden">'.FEELCRM('productType/edit',['id'=>encrypt($val['type_id'],'TYPE')],L('EDITOR'),'','edt').FEELCRM('productType/delete',['id'=>encrypt($val['type_id'],'TYPE')],L('DELETE'),'','async').'</div></td></tr>';
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


			if($val['subClass'])
			{
				$arr = array_merge($arr,$this->resultCategory($val['subClass'],$type,$parent_id,$lev+1));
			}
		}
		return $arr;
	}

	//创建产品分类
	public function create()
	{
		if(IS_POST)
		{
			$type = $this->checkCreate();

			if($type_id = getCrmDbModel('product_type')->add($type))
			{
				D('Excel')->createCrmImportTemp($this->_company_id,'product');

			    $result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'reloadType'=>'parent','url'=>U('index')];
			}
			else
			{
			    $result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
			}

            $this->ajaxReturn($result);
		}
		else
		{
			$product_type_field = getCrmLanguageData('type_name');
			
			$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

			$product_type_h = $this->resultCategory($product_type,2);

			$this->assign('product_type',$product_type);

			$this->assign('product_type_h',$product_type_h);

			$this->display();
		}
	}


	private function checkCreate()
	{
		$data = checkFields(I('post.productType'), $this->ProductTypeFields);

		if(!$data['parent_id']) $data['parent_id'] = 0;

		if(empty($data['type_name'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_PRODUCT_CATEGORY_NAME')]);

		if(getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'type_name'=>$data['type_name'],'parent_id'=>$data['parent_id']])->getField('type_id'))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('TYPE_NAME_CANNOT_REPEAT')]);
		}

		if($this->_langAuth['en_auth'] == 10)
		{
			if(empty($data['name_en'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_EN_NAME')]);

			if(getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'name_en'=>$data['name_en'],'parent_id'=>$data['parent_id']])->getField('type_id'))
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('EN_NAME_CANNOT_REPEAT')]);
			}
		}

		if($this->_langAuth['jp_auth'] == 10)
		{
			if(empty($data['name_jp'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_JP_NAME')]);

			if(getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'name_jp'=>$data['name_jp'],'parent_id'=>$data['parent_id']])->getField('type_id'))
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('JP_NAME_CANNOT_REPEAT')]);
			}
		}

        $data['create_time'] = NOW_TIME;

        $data['company_id'] = $this->_company_id;

        return $data;
	}


	public function edit($id='')
	{
		$type_id = decrypt($id,'TYPE');

		$where = ['company_id'=>$this->_company_id,'type_id'=>$type_id];

	    if(!$detail = getCrmDbModel('product_type')->where($where)->find())
	    {
		    $this->error(L('PRODUCT_CATEGORY_DOES_NOT_EXIST'),'',3);
	    }

		if(IS_POST)
		{
			$product_type = $this->checkEdit($type_id);

			$save = getCrmDbModel('product_type')->where($where)->save($product_type);

			if($save === false)
			{
			    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
				D('Excel')->createCrmImportTemp($this->_company_id,'product');

				$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'reloadType'=>'parent','url'=>U('index')];
			}

            $this->ajaxReturn($result);
		}
		else
		{
			$product_type_field = getCrmLanguageData('type_name');
			
			$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'closed'=>0])->select();

			$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

			$parent_id = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'closed'=>0,'type_id'=>$type_id])->getField('parent_id');

			$product_type_h = $this->resultCategory($product_type,2,$parent_id);

			$this->assign('product_type',$product_type);

			$this->assign('product_type_h',$product_type_h);

			$this->assign('type',$detail);

			$this->display();
		}
	}


	private function checkEdit($type_id)
	{
		$data = checkFields(I('post.productType'), $this->ProductTypeFields);

		if($type_id == $data['parent_id']) $this->ajaxReturn(['status'=>0,'msg'=>L('MENU_NOTE4')]);

		if(!$data['parent_id']) $data['parent_id'] = 0;

		if(empty($data['type_name'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_PRODUCT_CATEGORY_NAME')]);

		if(getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'type_name'=>$data['type_name'],'parent_id'=>$data['parent_id'],'type_id'=>['neq',$type_id]])->getField('type_id'))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('TYPE_NAME_CANNOT_REPEAT')]);
		}

		if($this->_langAuth['en_auth'] == 10)
		{
			if(empty($data['name_en'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_EN_NAME')]);

			if(getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'name_en'=>$data['name_en'],'parent_id'=>$data['parent_id'],'type_id'=>['neq',$type_id]])->getField('type_id'))
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('EN_NAME_CANNOT_REPEAT')]);
			}
		}

		if($this->_langAuth['jp_auth'] == 10)
		{
			if(empty($data['name_jp'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_JP_NAME')]);

			if(getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'name_jp'=>$data['name_jp'],'parent_id'=>$data['parent_id'],'type_id'=>['neq',$type_id]])->getField('type_id'))
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('JP_NAME_CANNOT_REPEAT')]);
			}
		}

        return $data;
	}

	//删除
	public function delete($id='')
	{
	    if(IS_AJAX)
	    {
	        $type_id = decrypt($id,'TYPE');

			$where = ['type_id'=>$type_id,'company_id'=>$this->_company_id];

			if(getCrmDbModel('product_type')->where($where)->getField('type_id'))
			{
				if(getCrmDbModel('product_type')->where(['parent_id'=>$type_id,'company_id'=>$this->_company_id])->getField('type_id'))
				{

					$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];

				}
				else
				{
					if(getCrmDbModel('product_type')->where($where)->delete())
					{
						D('Excel')->createCrmImportTemp($this->_company_id,'product');

						$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>U('index')];
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
					}
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('PRODUCT_CATEGORY_DOES_NOT_EXIST')];
			}

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
    }
}

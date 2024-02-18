<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

use Think\Cache\Driver\Redis;

class ShipmentController extends BasicController
{
	protected $ShipmentFields = ['company_id','customer_id','product_id','num','money'];

	public function _initialize()
    {
        parent::_initialize();
    }

	public function create($id='',$detailtype='')
	{
		$customer_id = decrypt($id,'CUSTOMER');

		$detailtype = decrypt($detailtype,'CUSTOMER');

		$localurl = U('Customer/detail',['id'=>encrypt($customer_id,'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

		$this->assign('customer_id',$customer_id);

		$this->assign('detailtype',$detailtype);

		$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'shipment'];

		if(IS_POST)
		{
			$data = $this->checkCreate();

			if($shipment_id = getCrmDbModel('shipment')->add($data['shipment']))//添加出货信息
            {
				foreach($data['shipment_detail'] as &$v)
                {
                    $v['shipment_id'] = $shipment_id;

                    $v['company_id'] = $this->_company_id;

                    if(is_array($v['form_content']))
                    {
                        $v['form_content'] = implode(',',$v['form_content']);
                    }

                    getCrmDbModel('shipment_detail')->add($v); //添加出货详情
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
			
			$shipmentform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($shipmentform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}
			}

			$product = getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id])->field('product_id')->select();

			//$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'isvalid'=>'1','closed'=>0])->field('product_id,type_id')->select();
			//getPrint($product);
			$product = a_array_unique($product);

			foreach($product as $key=>&$val)
			{
				$detail = CrmgetCrmDetailList('product',$val['product_id'],$this->_company_id,'name');

				$val['name'] = $detail['name'];
			}

			$this->assign('product',$product);

			$this->assign('shipmentform',$shipmentform);

			$this->display();
		}
	}

	public function checkCreate()
	{
		$shipment = checkFields(I('post.shipment'), $this->ShipmentFields);

		if(!$shipment['customer_id'])
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('PLEASE_SELECT_CUSTOMER')]);
		}

		if(!$shipment['product_id'])
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('SELECT_PRODUCT_MODE')]);
		}

		if(!$shipment['num'])
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_SHIPPING_QUANTITY')]);
		}
		else
		{
			//产品总数
			$product_num = getCrmDbModel('contract_product')->where(['customer_id'=>$shipment['customer_id'],'product_id'=>$shipment['product_id'],'company_id'=>$this->_company_id])->sum('num');

			//已出货数量
			$shipment_num = getCrmDbModel('shipment')->where(['customer_id'=>$shipment['customer_id'],'product_id'=>$shipment['product_id'],'company_id'=>$this->_company_id,'isvalid'=>1])->sum('num');

			$surplus_num = $product_num - $shipment_num; //剩余数量

			if($shipment['num'] > $surplus_num)
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('SHIPMENT_GT_REMAINING')]);
			}
		}

		if(!$shipment['money'])
		{
			 $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_TOTAL_AMOUNT_SHIPMENT')]);
		}

		$shipment['company_id'] = $this->_company_id;//所属公司ID

		$shipment['isvalid'] = 1;

        $shipment['createtime'] = NOW_TIME;

        $shipment['creater_id'] = $this->member_id;

		$shipment_form = I('post.shipment_form');

		$ShipmentCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$shipment_form,'shipment',$this->_member);

		if($ShipmentCheckForm['detail'])
		{
			$shipment_detail = $ShipmentCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ShipmentCheckForm);
		}

		return ['shipment'=>$shipment,'shipment_detail'=>$shipment_detail];

	}

	public function edit($id,$detailtype="")
	{
		$shipment_id = decrypt($id,'SHIPMENT');

		if(!$shipment_id)
		{
			$this->common->_empty();
		}

		$detailtype = decrypt($detailtype,'SHIPMENT');

		$this->assign('detailtype',$detailtype);

		$shipment = getCrmDbModel('shipment')->where(['company_id'=>$this->_company_id,'shipment_id'=>$shipment_id,'isvalid'=>'1'])->find();

		$localurl = U('Customer/detail',['id'=>encrypt($shipment['customer_id'],'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

		if(IS_POST)
		{
			$data = $this->checkEdit($shipment_id,$shipment['customer_id']);

			$save = getCrmDbModel('shipment')->where(['shipment_id'=>$shipment_id,'company_id'=>$this->_company_id,'isvalid'=>'1'])->save($data['shipment']);

			if($save === false)
			{
			    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
				getCrmDbModel('shipment_detail')->where(['shipment_id'=>$shipment_id,'company_id'=>$this->_company_id])->delete();

				foreach($data['shipment_detail'] as &$v)
				{
					$v['shipment_id'] = $shipment_id;

					$v['company_id'] = $this->_company_id;

					if(is_array($v['form_content']))
					{
						$v['form_content'] = implode(',',$v['form_content']);
					}

					getCrmDbModel('shipment_detail')->add($v);  //添加出货详情
				}

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'url'=>$localurl];
			}

			$this->ajaxReturn($result);

		}
		else
		{
			$where = ['company_id'=>$this->_company_id,'closed' => 0,'type'=>'shipment'];

			$form_description = getCrmLanguageData('form_description');
			
			$shipmentform = getCrmDbModel('define_form')->field(['*',$form_description])->where($where)->order('orderby asc')->select();

			foreach($shipmentform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$shipment_detail = getCrmDbModel('shipment_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'shipment_id'=>$shipment_id])->find();

				if($v['form_type']=='region')
				{
					if($shipment_detail['form_content'])
					{
						$region_detail = explode(',',$shipment_detail['form_content']);

						$shipment[$v['form_name']]['defaultCountry'] = $region_detail[0];

						$shipment[$v['form_name']]['defaultProv'] = $region_detail[1];

						$shipment[$v['form_name']]['defaultCity'] = $region_detail[2];

						$shipment[$v['form_name']]['defaultArea'] = $region_detail[3];

					}
				}else
				{
					$shipment[$v['form_name']] = $shipment_detail['form_content'];
				}
			}

			$product = getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'customer_id'=>$shipment['customer_id']])->field('product_id')->select();

			$product = a_array_unique($product);

			//$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'isvalid'=>'1','closed'=>0])->field('product_id,type_id')->select();

			foreach($product as $key=>&$val)
			{
				$detail = CrmgetCrmDetailList('product',$val['product_id'],$this->_company_id,'name');

				$val['name'] = $detail['name'];
			}

			$this->assign('product',$product);

			$this->assign('shipmentform',$shipmentform);

			$this->assign('shipment',$shipment);

			$this->display();
		}
	}

	public function checkEdit($shipment_id,$customer_id)
	{
		$shipment = checkFields(I('post.shipment'), $this->ShipmentFields);

		if(!$shipment['product_id'])
		{
			 $this->ajaxReturn(['status'=>0,'msg'=>L('SELECT_PRODUCT_MODE')]);
		}

		if(!$shipment['num'])
		{
			 $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_SHIPPING_QUANTITY')]);
		}
		else
		{
			//产品总数
			$product_num = getCrmDbModel('contract_product')->where(['customer_id'=>$customer_id,'product_id'=>$shipment['product_id'],'company_id'=>$this->_company_id])->sum('num');

			//已出货数量
			$shipment_num = getCrmDbModel('shipment')->where(['customer_id'=>$customer_id,'product_id'=>$shipment['product_id'],'company_id'=>$this->_company_id,'isvalid'=>1,'shipment_id'=>['neq',$shipment_id]])->sum('num');

			$surplus_num = $product_num - $shipment_num; //剩余数量

			if($shipment['num'] > $surplus_num)
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('SHIPMENT_GT_REMAINING')]);
			}
		}

		if(!$shipment['money'])
		{
			 $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_TOTAL_AMOUNT_SHIPMENT')]);
		}

		$shipment_form = I('post.shipment_form');

		$ShipmentCheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$shipment_form,'shipment',$this->_member,$shipment_id);

		if($ShipmentCheckForm['detail'])
		{
			$shipment_detail = $ShipmentCheckForm['detail'];
		}
		else
		{
			$this->ajaxReturn($ShipmentCheckForm);
		}

		return ['shipment'=>$shipment,'shipment_detail'=>$shipment_detail];
	}

	public function delete($id='',$detailtype='')
	{
		if(IS_AJAX)
	    {
			$shipment_id = decrypt($id,'SHIPMENT');

			$detailtype = decrypt($detailtype,'SHIPMENT');

			$shipment = getCrmDbModel('shipment')->where(['company_id'=>$this->_company_id,'shipment_id'=>$shipment_id,'isvalid'=>'1'])->find();

			$localurl = U('Customer/detail',['id'=>encrypt($shipment['customer_id'],'CUSTOMER'),'detailtype'=>encrypt($detailtype,'CUSTOMER')]);

			if($shipment_id)
			{
				$shipment_id = decrypt($id,'SHIPMENT');

				$where = ['shipment_id'=>$shipment_id,'company_id'=>$this->_company_id,'isvalid'=>'1'];

				if(getCrmDbModel('shipment')->where($where)->getField('shipment_id'))
				{
					if(getCrmDbModel('shipment')->where($where)->save(['isvalid'=>0]))
					{
						$result = ['status'=>3,'msg'=>L('DELETE_SUCCESS'),'url'=>$localurl];
					}
					else
					{
						$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
					}
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('WRONG_INFORMATION')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('SELECT_SHIPPING_INFO_DELETED')];
			}

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
	}
}

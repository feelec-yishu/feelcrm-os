<?php
/**
 * Created by PhpStorm.
 * User: navyy
 * Date: 2017.07.21
 * Time: 9:56
 */
namespace Common\Model;

use Common\Model\BasicModel;

class CrmExportModel extends BasicModel
{
    protected $autoCheckFields = false;

    protected $temp = [];

    public function getExportList($export_field,$export_arr,$company_id,$type,$exportList = '')
	{
		$form_description = getCrmLanguageData('form_description');

		$define_list = getCrmDbModel('define_form')
			->field('form_name,'.$form_description)
			->where(['company_id'=>$company_id,'closed'=>0,'type'=>$type,'form_type'=>['neq','textarea']])
			->order('orderby asc')
			->select();

		$export_define = '';

		foreach($define_list as $key=>$val)
		{
			$export_define .= ','.$val['form_name'];
		}

		$export_field = str_replace("{DEFINE}",$export_define,$export_field);

		$export_field = explode(',',$export_field);

		if(!$exportList){

			foreach($export_field as $key => $val)
			{
				if(!$export_arr[$val])
				{
					foreach($define_list as $k1=>$v1)
					{
						if($val == $v1['form_name'])
						{
							$export_list[$key]['name'] = $v1['form_description'];

							$export_list[$key]['define'] = $v1['form_name'];
						}
					}
				}
				else
				{
					$export_list[$key]['name'] = L($export_arr[$val]);

					$export_list[$key]['define'] = $val;
				}
			}

			$new_key = 0;

			$sort_arr = [];

			//重新排序
			if($type == 'customer')
			{
				foreach ($export_list as $k=>$v)
				{
					if(in_array($export_list[$k]['define'],['percent']))
					{
						//赋值到新数组，方便取值
						$sort_arr[$export_list[$k]['define']] = $export_list[$k];

						continue;
					}

					$new_export_list[$new_key] = $export_list[$k];

					if($export_list[$k]['define'] == 'name')
					{
						$new_key ++;
						$new_export_list[$new_key] = $sort_arr['percent'];
					}

					$new_key ++;
				}

				$export_list = $new_export_list;
			}

			if($type == 'opportunity')
			{
				foreach ($export_list as $k=>$v)
				{
					if(in_array($export_list[$k]['define'],['customer_name']))
					{
						//赋值到新数组，方便取值
						$sort_arr[$export_list[$k]['define']] = $export_list[$k];

						continue;
					}

					$new_export_list[$new_key] = $export_list[$k];

					if($export_list[$k]['define'] == 'name')
					{
						$new_key ++;
						$new_export_list[$new_key] = $sort_arr['customer_name'];
					}

					$new_key ++;
				}

				$export_list = $new_export_list;
			}

			//重新排序
			if($type == 'contract')
			{
				foreach ($export_list as $k=>$v)
				{
					$new_export_list[$new_key] = $export_list[$k];

					$new_key ++;
				}

				$export_list = $new_export_list;
			}

			return $export_list;
		}
		else
		{
			foreach($export_field as $key => $val)
			{
				//重新排序的固定字段跳过循环
				if($type == 'customer')
				{
					if(in_array($val,['percent'])) continue;
				}

				if($type == 'opportunity')
				{
					if(in_array($val,['customer_name'])) continue;
				}

				if($type == 'contract')
				{

				}

				if(in_array($val,$exportList))
				{
					if(!$export_arr[$val])
					{
						foreach($define_list as $k1=>$v1)
						{
							if($val == $v1['form_name'])
							{
								$tableHeader[] = $v1['form_description'];
							}
						}
					}
					else
					{
						$tableHeader[] = L($export_arr[$val]);
					}
				}

				//重新排序
				if($type == 'customer')
				{
					if($val == 'name')
					{
						if(in_array('percent',$exportList)) $tableHeader[] = L($export_arr['percent']);
					}
				}

				if($type == 'opportunity')
				{
					if($val == 'name')
					{
						if(in_array('customer_name',$exportList)) $tableHeader[] = L($export_arr['customer_name']);
					}
				}

				if($type == 'contract')
				{
					if($val == 'money')
					{

					}
				}
			}

			return ['show_list'=>$define_list,'tableHeader'=>$tableHeader];
		}
	}

	public function getDataList($where,$pagecount,$type)
	{
		$exportList = I('get.exportList');

		if(!$exportList)
		{
			return ['msg'=>L('SELECT_EXPORT_DATA')];die;
		}

		$exporttype = I('get.exporttype');

		$startpage = (int) I('get.startpage');

		$endpage = (int) I('get.endpage');

		$nowpage = I('get.nowpage') ? (int) I('get.nowpage') - 1 : 0;

		if($exporttype == 'pagedata')
		{
			if(!$startpage)
			{
				return ['msg'=>L('ENTER_EXPORT_START_PAGE')];die;
			}
			elseif(!$endpage)
			{
				return ['msg'=>L('ENTER_EXPORT_END_PAGE')];die;
			}
			if($startpage >= $endpage)
			{
				return ['msg'=>L('END_PAGE_MUST_GT_START')];die;
			}
		}

		$order = session('ExportOrder') ? session('ExportOrder') : 'createtime desc';

		if($exporttype == 'alldata')
		{
			$data = getCrmDbModel($type)->where($where)->order($order)->select();
		}
		elseif($exporttype == 'thisdata')
		{
			$start = $nowpage * $pagecount;

			$count = $pagecount;

			$data = getCrmDbModel($type)->where($where)->limit($start,$count)->order($order)->select();
		}
		elseif($exporttype == 'pagedata')
		{
			$start = ($startpage - 1) * $pagecount;

			$count = ($endpage - ($startpage - 1)) * $pagecount;

			$data = getCrmDbModel($type)->where($where)->limit($start,$count)->order($order)->select();
		}

		return ['exportList'=>$exportList,'data'=>$data];
	}

	public function common_export($tableHeader,$exportList,$exportSession)
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

		session($exportSession,$excel);
	}

	public function to_export($exportSession)
	{
		$tableHeader = session($exportSession)['header'];

		$letter      = session($exportSession)['letter'];

		$excelData   = session($exportSession)['excelData'];

		$filename = date('ymdhis').time();

		D('Excel')->exportExcel($filename.'.xls',$tableHeader,$letter,$excelData,'feelcrm');
	}

	public function getClueList($clue,$company_id,$exportList,$show_list)
	{
		foreach($clue as $key=>&$val)
		{
			if(in_array('clue_no',$exportList))
			{
				$exportData[$key]['clue_no'] = $val['clue_prefix'].$val['clue_no'];
			}

			if(in_array('status',$exportList))
			{
				$exportData[$key]['status'] = getClueStatusName($val['status']);
			}

			$haveFieldArr = CrmgetCrmDetailList('clue',$val['clue_id'],$company_id);

			foreach($show_list as $k1=>$v1)
			{
				if(in_array($v1['form_name'],$exportList))
				{
					$exportData[$key][$v1['form_name']] = $haveFieldArr[$v1['form_name']];
				}
			}

			if(in_array('lastfollowtime',$exportList)){$exportData[$key]['lastfollowtime'] = date('Y-m-d H:i',$val['lastfollowtime']);}

			if(in_array('nextcontacttime',$exportList)){$exportData[$key]['nextcontacttime'] = date('Y-m-d H:i',$val['nextcontacttime']);}

			if(in_array('createtime',$exportList)){$exportData[$key]['createtime'] = date('Y-m-d H:i',$val['createtime']);}

			if(in_array('member_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,name')->find();

				$exportData[$key]['member_name'] = $thisMember['name'];
			}

			if(in_array('create_name',$exportList))
			{
				$exportData[$key]['create_name'] = getCustomerCreateName($val['creater_id'],'',$company_id);
			}
		}

		return $exportData;
	}

	public function getCustomerList($customer,$company_id,$exportList,$show_list)
	{
		//除备注外全部客户自定义字段
		$allField = getCrmDbModel('define_form')->where(['type'=>'customer','company_id'=>$company_id,'closed'=>0,'form_name'=>['neq','remark']])->count();

		foreach($customer as $key=>&$val)
		{
			$haveField = 0;

			if(in_array('customer_no',$exportList))
			{
				$exportData[$key]['customer_no'] = $val['customer_prefix'].$val['customer_no'];
			}

			$haveFieldArr = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id);

			foreach($show_list as $k1=>$v1)
			{
				if(in_array($v1['form_name'],$exportList))
				{
					$exportData[$key][$v1['form_name']] = $haveFieldArr[$v1['form_name']];
				}

				if($v1['form_name'] == 'name')
				{
					if(in_array('percent',$exportList))
					{
						unset($haveFieldArr['remark']);

						$haveField = count(array_filter($haveFieldArr));

						$percent = round($haveField/$allField*100);

						$exportData[$key]['percent'] = $percent.'%';
					}
				}
			}

			$contacter_detail = CrmgetCrmDetailList('contacter',$val['first_contact_id'],$company_id,'name,phone');

			if(in_array('contacter_name',$exportList)){$exportData[$key]['contacter_name'] = $contacter_detail['name'];}

			if(in_array('contacter_phone',$exportList)){$exportData[$key]['contacter_phone'] = $contacter_detail['phone'];}

			if(in_array('lastfollowtime',$exportList)){$exportData[$key]['lastfollowtime'] = date('Y-m-d H:i',$val['lastfollowtime']);}

			if(in_array('nextcontacttime',$exportList)){$exportData[$key]['nextcontacttime'] = date('Y-m-d H:i',$val['nextcontacttime']);}

			if(in_array('is_losed',$exportList)){$exportData[$key]['is_losed'] = $val['is_losed'] == 1 ? L('YES') : L('NO');}

			if(in_array('is_examine',$exportList)){$exportData[$key]['is_examine'] = $val['is_examine'] == 1 ? L('YES') : L('NO');}

			if(in_array('createtime',$exportList)){$exportData[$key]['createtime'] = date('Y-m-d H:i',$val['createtime']);}

			if(in_array('member_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

				$exportData[$key]['member_name'] = $thisMember['name'];
			}

			if(in_array('create_name',$exportList))
			{
				$exportData[$key]['create_name'] = getCustomerCreateName($val['creater_id'],'',$company_id);
			}
		}

		return $exportData;
	}

	public function getOpportunityList($opportunity,$company_id,$exportList,$show_list)
	{
		foreach($opportunity as $key=>&$val)
		{
			$opportunity_detail = CrmgetCrmDetailList('opportunity',$val['opportunity_id'],$company_id);

			if(in_array('opportunity_no',$exportList)){$exportData[$key]['opportunity_no'] = $val['opportunity_prefix'].$val['opportunity_no'];}

			foreach($show_list as $k1=>$v1)
			{
				if(in_array($v1['form_name'],$exportList)){$exportData[$key][$v1['form_name']] = $opportunity_detail[$v1['form_name']];}

				if($v1['form_name'] == 'name')
				{
					if(in_array('customer_name',$exportList))
					{
						$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

						$exportData[$key]['customer_name'] = $customer_detail['name'];
					}
				}
			}

			if(in_array('lastfollowtime',$exportList)){$exportData[$key]['lastfollowtime'] = date('Y-m-d H:i',$val['lastfollowtime']);}

			if(in_array('nextcontacttime',$exportList)){$exportData[$key]['nextcontacttime'] = date('Y-m-d H:i',$val['nextcontacttime']);}

			if(in_array('createtime',$exportList)){$exportData[$key]['createtime'] = date('Y-m-d H:i',$val['createtime']);}

			if(in_array('member_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

				$exportData[$key]['member_name'] = $thisMember['name'];
			}

			if(in_array('create_name',$exportList))
			{
				$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

				$exportData[$key]['create_name'] = $createMember['name'];
			}
		}

		return $exportData;
	}

	public function getContacterList($contacter,$company_id,$exportList,$show_list)
	{
		foreach($contacter as $key=>&$val)
		{
			$contacter_detail = CrmgetCrmDetailList('contacter',$val['contacter_id'],$company_id,implode(",", $exportList));

			$thisCustomer = getCrmDbModel('customer')->where(['company_id'=>$company_id,'customer_id'=>$val['customer_id'],'isvalid'=>1])->field('first_contact_id')->find();

			$first_contact_id = $thisCustomer['first_contact_id'];

			foreach($show_list as $k1=>$v1)
			{
				if(in_array($v1['form_name'],$exportList))
				{
					$exportData[$key][$v1['form_name']] = $contacter_detail[$v1['form_name']];

					if($v1['form_name'] == 'name')
					{
						if($first_contact_id == $val['contacter_id'])
						{
							$exportData[$key][$v1['form_name']] .= '('.L('PRIMARY').')';
						}
					}
				}
			}
			if(in_array('customer_name',$exportList))
			{
				$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

				$exportData[$key]['customer_name'] = $customer_detail['name'];
			}
		}

		return $exportData;
	}

	public function getFollowupList($follow,$company_id,$exportList,$show_list)
	{
		$j = 0;

		$cmncate_field = getCrmLanguageData('cmncate_name');

		foreach($follow as $key=>&$val)
		{
			$comment = getCrmDbModel('follow_comment')->where(['company_id'=>$company_id,'follow_id'=>$val['follow_id'],'isvalid'=>1])->order('createtime desc')->field('content')->select();

			if(empty($comment))
			{
				if(in_array('content',$exportList)){$exportData[$j]['content'] = $val['content'];}

				if(in_array('comment',$exportList)){$exportData[$j]['comment'] = '';}

				if(in_array('createtime',$exportList)){$exportData[$j]['createtime'] = date('Y-m-d H:i',$val['createtime']);}

				if(in_array('member_name',$exportList)){$exportData[$j]['member_name'] = M('member')->where(['company_id'=>$company_id,'member_id'=>$val['member_id'],'type'=>1])->getField('name');}

				if(in_array('customer_contacter',$exportList))
				{
					$contacter = CrmgetCrmDetailList('contacter',$val['contacter_id'],$company_id,'name');

					$exportData[$j]['customer_contacter'] = $contacter['name'];
				}

				if(in_array('cmncate_name',$exportList))
				{
					if($val['cmncate_id'])
					{
						$exportData[$j]['cmncate_name'] = getCrmDbModel('communicate')->where(['cmncate_id'=>$val['cmncate_id'],'company_id'=>$company_id])->getField($cmncate_field);
					}
					else
					{
						$exportData[$j]['cmncate_name'] = '';
					}
				}

				if($val['clue_id'] > 0)
				{
					if(in_array('follow_type',$exportList))
					{
						$exportData[$j]['follow_type'] = L('CLUE');
					}

					if(in_array('belong_name',$exportList))
					{
						$clue_detail = CrmgetCrmDetailList('clue',$val['clue_id'],$company_id,'name');

						$exportData[$j]['belong_name'] = $clue_detail['name'];
					}
				}
				else
				{
					if(in_array('follow_type',$exportList))
					{
						$exportData[$j]['follow_type'] = L('CUSTOMER');
					}

					if(in_array('belong_name',$exportList))
					{
						$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

						$exportData[$j]['belong_name'] = $customer_detail['name'];
					}
				}

				$j++;
			}
			else
			{
				$cmncate_field = getCrmLanguageData('cmncate_name');

				foreach($comment as $k1=>$v1)
				{
					if(in_array('content',$exportList)){$exportData[$j]['content'] = $val['content'];}

					if(in_array('comment',$exportList)){$exportData[$j]['comment'] = $v1['content'];}

					if(in_array('createtime',$exportList)){$exportData[$j]['createtime'] = date('Y-m-d H:i',$val['createtime']);}

					if(in_array('member_name',$exportList)){$exportData[$j]['member_name'] = M('member')->where(['company_id'=>$company_id,'member_id'=>$val['member_id'],'type'=>1])->getField('name');}

					if(in_array('customer_contacter',$exportList))
					{
						$contacter = CrmgetCrmDetailList('contacter',$val['contacter_id'],$company_id,'name');

						$exportData[$j]['customer_contacter'] = $contacter['name'];
					}

					if(in_array('cmncate_name',$exportList))
					{
						if($val['cmncate_id'])
						{
							$exportData[$j]['cmncate_name'] = getCrmDbModel('communicate')->where(['cmncate_id'=>$val['cmncate_id'],'company_id'=>$company_id])->getField($cmncate_field);
						}else
						{
							$exportData[$j]['cmncate_name'] = '';
						}
					}

					if($val['clue_id'] > 0)
					{
						if(in_array('follow_type',$exportList))
						{
							$exportData[$j]['follow_type'] = L('CLUE');
						}

						if(in_array('belong_name',$exportList))
						{
							$clue_detail = CrmgetCrmDetailList('clue',$val['clue_id'],$company_id,'name');

							$exportData[$j]['belong_name'] = $clue_detail['name'];
						}
					}
					else
					{
						if(in_array('follow_type',$exportList))
						{
							$exportData[$j]['follow_type'] = L('CUSTOMER');
						}

						if(in_array('belong_name',$exportList))
						{
							$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

							$exportData[$j]['belong_name'] = $customer_detail['name'];
						}
					}

					$j ++;

					if(!in_array('comment',$exportList))
					{
						break;
					}
				}
			}
		}

		return $exportData;
	}

	public function getOrderList($order,$company_id,$exportList,$show_list)
	{
		foreach($order as $key=>&$val)
		{
			if(in_array('order_no',$exportList)){$exportData[$key]['order_no'] = $val['order_prefix'].$val['order_no'];}

			if(in_array('contract_no',$exportList))
			{
				$order_contract = explode(',',$val['contract_id']);

				$contract_arr = '';

				foreach($order_contract as $k1=>$v1)
				{
					$contract = getCrmDbModel('contract')->where(['company_id'=>$company_id,'contract_id'=>$v1,'isvalid'=>1])->field('contract_prefix,contract_no')->find();

					if($contract)
					{
						$contract_arr .= $contract['contract_prefix'].$contract['contract_no'].',';
					}
				}

				$exportData[$key]['contract_no'] = $contract_arr;
			}

			$order_detail = CrmgetCrmDetailList('order',$val['order_id'],$company_id,implode(",", $exportList));

			foreach($show_list as $k1=>$v1)
			{
				if(in_array($v1['form_name'],$exportList)){$exportData[$key][$v1['form_name']] = $order_detail[$v1['form_name']];}
			}
			if(in_array('customer_name',$exportList))
			{
				$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

				$exportData[$key]['customer_name'] = $customer_detail['name'];
			}
			if(in_array('member_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

				$exportData[$key]['member_name'] = $thisMember['name'];
			}

			if(in_array('createtime',$exportList)){$exportData[$key]['createtime'] = date('Y-m-d H:i',$val['createtime']);}

			if(in_array('create_name',$exportList))
			{
				$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

				$exportData[$key]['create_name'] = $createMember['name'];
			}
		}

		return $exportData;
	}

	public function getProductList($product,$company_id,$exportList,$show_list)
	{
		$product_type_field = getCrmLanguageData('type_name');

		foreach($product as $key=>&$val)
		{
			if(in_array('type_name',$exportList))
			{
				$type_name = getCrmDbModel('product_type')->where(['company_id'=>$company_id,'type_id'=>$val['type_id']])->getField($product_type_field);

				$exportData[$key]['type_name'] = $type_name;
			}

			$product_detail = CrmgetCrmDetailList('product',$val['product_id'],$company_id,implode(",", $exportList));

			foreach($show_list as $k1=>$v1)
			{
				if(in_array($v1['form_name'],$exportList)){$exportData[$key][$v1['form_name']] = $product_detail[$v1['form_name']];}
			}

			if(in_array('closed',$exportList))
			{
				if($val['closed'] == 0)
				{
					$exportData[$key]['closed'] = L('PUT_ON_SHELF');
				}
				else
				{
					$exportData[$key]['closed'] = L('OFF_SHELF');
				}
			}
		}

		return $exportData;
	}

	public function getContractList($contract,$company_id,$exportList,$show_list)
	{
		foreach($contract as $key=>&$val)
		{
			$contract_detail = CrmgetCrmDetailList('contract',$val['contract_id'],$company_id);

			if(in_array('contract_no',$exportList)){$exportData[$key]['contract_no'] = $val['contract_prefix'].$val['contract_no'];}

			if(in_array('order_no',$exportList))
			{
				$contract_order = explode(',',$val['order_id']);

				$order_arr = '';

				foreach($contract_order as $k1=>$v1)
				{
					$order = getCrmDbModel('order')->where(['company_id'=>$company_id,'order_id'=>$v1,'isvalid'=>1])->field('order_prefix,order_no')->find();

					if($order)
					{
						$order_arr .= $order['order_prefix'].$order['order_no'].',';
					}
				}

				$exportData[$key]['order_no'] = $order_arr;
			}

			foreach($show_list as $k1=>$v1)
			{
				if(in_array($v1['form_name'],$exportList)){$exportData[$key][$v1['form_name']] = $contract_detail[$v1['form_name']];}
			}

			if(in_array('customer_name',$exportList))
			{
				$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

				$exportData[$key]['customer_name'] = $customer_detail['name'];
			}

			if(in_array('createtime',$exportList)){$exportData[$key]['createtime'] = date('Y-m-d H:i',$val['createtime']);}

			if(in_array('member_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

				$exportData[$key]['member_name'] = $thisMember['name'];
			}

			if(in_array('create_name',$exportList))
			{
				$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

				$exportData[$key]['create_name'] = $createMember['name'];
			}
		}

		return $exportData;
	}

	public function getAccountList($account,$company_id,$exportList,$show_list)
	{
		foreach($account as $key=>&$val)
		{
			if(in_array('account_no',$exportList)){$exportData[$key]['account_no'] = $val['account_prefix'].$val['account_no'];}

			if(in_array('account_money',$exportList)){$exportData[$key]['account_money'] = $val['account_money'];}

			$receipt_money = getCrmDbModel('receipt')->where(['company_id'=>$company_id,'customer_id'=>$val['customer_id'],'contract_id'=>$val['contract_id'],'account_id'=>$val['account_id'],'isvalid'=>1,'status'=>2])->sum('receipt_money');

			$receipt_money = $receipt_money ? $receipt_money : '0.00';

			if(in_array('receipt_money',$exportList)){$exportData[$key]['receipt_money'] = $receipt_money;}

			if(in_array('uncollected_money',$exportList)){$exportData[$key]['uncollected_money'] = sprintf("%1\$.2f",$val['account_money'] - $receipt_money);}

			if(in_array('account_speed',$exportList))
			{
				$exportData[$key]['account_speed'] = CrmgetPercentage($val['account_money'],$receipt_money);
			}

			if(in_array('account_time',$exportList)){$exportData[$key]['account_time'] = getDates($val['account_time'],0);}

			if(in_array('customer_name',$exportList))
			{
				$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

				$exportData[$key]['customer_name'] = $customer_detail['name'];
			}

			if(in_array('contract_name',$exportList))
			{
				$contract_detail = CrmgetCrmDetailList('contract',$val['contract_id'],$company_id,'name');

				$exportData[$key]['contract_name'] = $contract_detail['name'];
			}

			if(in_array('member_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

				$exportData[$key]['member_name'] = $thisMember['name'];
			}

			if(in_array('create_name',$exportList))
			{
				$createMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

				$exportData[$key]['create_name'] = $createMember['name'];
			}
		}

		return $exportData;
	}

	public function getReceiptList($receipt,$company_id,$exportList,$show_list)
	{
		foreach($receipt as $key=>&$val)
		{
			$contract_detail = CrmgetCrmDetailList('contract',$val['contract_id'],$company_id,'name,money');

			if(in_array('receipt_no',$exportList)){$exportData[$key]['receipt_no'] = $val['receipt_prefix'].$val['receipt_no'];}

			if(in_array('status',$exportList))
			{
				$status = getFinanceExamineName($val['status']);

				$exportData[$key]['status'] = $status;
			}

			if(in_array('account_money',$exportList))
			{
				if($val['account_id'])
				{
					$val['account_money'] = getCrmDbModel('account')->where(['company_id'=>$company_id,'customer_id'=>$val['customer_id'],'contract_id'=>$val['contract_id'],'account_id'=>$val['account_id'],'isvalid'=>1])->getField('account_money');
				}

				if(!$val['account_id'] || !$val['account_money'])
				{
					$val['account_money'] = $contract_detail['money'];
				}

				$exportData[$key]['account_money'] = $val['account_money'];
			}

			if(in_array('receipt_money',$exportList)){$exportData[$key]['receipt_money'] = $val['receipt_money'];}

			if(in_array('receipt_type',$exportList))
			{
				$receipt_type = getReceiptTypeName($val['receipt_type']);

				$exportData[$key]['receipt_type'] = $receipt_type;
			}

			if(in_array('receipt_time',$exportList)){$exportData[$key]['receipt_time'] = getDates($val['receipt_time'],0);}

			if(in_array('customer_name',$exportList))
			{
				$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

				$exportData[$key]['customer_name'] = $customer_detail['name'];
			}

			if(in_array('contract_name',$exportList))
			{
				$exportData[$key]['contract_name'] = $contract_detail['name'];
			}

			if(in_array('member_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

				$exportData[$key]['member_name'] = $thisMember['name'];
			}

			if(in_array('examine_name',$exportList))
			{
				$member = M('member')->where(['member_id'=>['in',$val['examine_id']],'type'=>1,'company_id'=>$company_id])->field('name,member_id')->select();

				$val['examine_name'] = '';

				foreach($member as $k=>$v)
				{
					$val['examine_name'] .= $v['name'].',';
				}

				$examine_name = rtrim($val['examine_name'],',');

				$exportData[$key]['examine_name'] = $examine_name;
			}

			if(in_array('create_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

				$exportData[$key]['create_name'] = $thisMember['name'];
			}
		}

		return $exportData;
	}

	public function getInvoiceList($invoice,$company_id,$exportList,$show_list)
	{
		foreach($invoice as $key=>&$val)
		{
			if(in_array('invoice_no',$exportList)){$exportData[$key]['invoice_no'] = $val['invoice_prefix'].$val['invoice_no'];}

			if(in_array('status',$exportList))
			{
				$status = getFinanceExamineName($val['status']);

				$exportData[$key]['status'] = $status;
			}

			if(in_array('invoice_money',$exportList)){$exportData[$key]['invoice_money'] = $val['invoice_money'];}

			if(in_array('invoice_time',$exportList)){$exportData[$key]['invoice_time'] = getDates($val['invoice_time'],0);}

			if(in_array('invoice_type',$exportList))
			{
				$invoice_type = getInvoiceTypeName($val['invoice_type']);

				$exportData[$key]['invoice_type'] = $invoice_type;
			}

			if(in_array('customer_name',$exportList))
			{
				$customer_detail = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

				$exportData[$key]['customer_name'] = $customer_detail['name'];
			}

			if(in_array('contract_name',$exportList))
			{
				$contract_detail = CrmgetCrmDetailList('contract',$val['contract_id'],$company_id,'name');

				$exportData[$key]['contract_name'] = $contract_detail['name'];
			}

			if(in_array('member_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name')->find();

				$exportData[$key]['member_name'] = $thisMember['name'];
			}

			if(in_array('examine_name',$exportList))
			{
				$member = M('member')->where(['member_id'=>['in',$val['examine_id']],'type'=>1,'company_id'=>$company_id])->field('name,member_id')->select();

				$val['examine_name'] = '';

				foreach($member as $k=>$v)
				{
					$val['examine_name'] .= $v['name'].',';
				}

				$examine_name = rtrim($val['examine_name'],',');

				$exportData[$key]['examine_name'] = $examine_name;
			}

			if(in_array('create_name',$exportList))
			{
				$thisMember = M('Member')->where(['company_id'=>$company_id,'closed'=>0,'type'=>1,'member_id'=>$val['creater_id']])->field('member_id,account,name')->find();

				$exportData[$key]['create_name'] = $thisMember['name'];
			}
		}

		return $exportData;
	}
}

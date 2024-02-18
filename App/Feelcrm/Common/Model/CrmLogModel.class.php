<?php
// +----------------------------------------------------------------------

// | FeelCRM开源客户管理系统

// +----------------------------------------------------------------------

// | 欢迎阅读学习系统程序代码，您的建议反馈是我们前进的动力

// | 开源版本仅供技术交流学习，请务必保留界面版权logo

// | 商业版本务必购买商业授权，以免引起法律纠纷

// | 禁止对系统程序代码以任何目的，任何形式的再发布

// | gitee下载：https://gitee.com/feelcrm_gitee

// | github下载：https://github.com/feelcrm-github

// | 开源官网：https://www.feelcrm.cn

// | 成都菲莱克斯科技有限公司 版权所有 拥有最终解释权

// +----------------------------------------------------------------------
namespace Common\Model;

use Common\Model\BasicModel;

class CrmLogModel extends BasicModel
{
    protected $connection = 'CRM_DB_CONFIG';

    protected $pk = 'log_id';

    protected $tableName = 'crmlog';

	/*
		$log_type  日志类型
		$operate_type  操作类型
		$company_id  公司id
		$member_id  操作人id
		$customer_id  客户id
		$contacter_id  联系人id
		$order_id  订单id
		$product_id  产品id
		$name  操作对象名称
		$follow_id  跟进记录id
		$target_id  目标id
		$analysis_id  客户分析id
		$competitor_id  竞争对手id
		$contract_id  合同id
		$account_id  应收款id
		$receipt_id  收款id
		$invoice_id  发票id
		$clue_id  线索id
		$opportunity_id  商机id
	*/

	function addCrmLog($log_type,$operate_type,$company_id=0,$member_id=0,$customer_id=0,$contacter_id=0,$order_id=0,$product_id=0,$name,$follow_id=0,$target_id=0,$analysis_id=0,$competitor_id=0,$contract_id=0,$account_id=0,$receipt_id=0,$invoice_id=0,$clue_id=0,$opportunity_id=0)
	{
		if(!$name)
		{
			$name = '<span></span>';
		}

		$memberName = getCustomerCreateName($member_id,'',$company_id,'html');

		if($log_type == 'customer')
		{
			$operateObject = L('CUSTOMER');
		}
		elseif($log_type == 'contacter')
		{
			$operateObject = L('CONTACT');
		}
		elseif($log_type == 'order')
		{
			$operateObject = L('ORDER');
		}
		elseif($log_type == 'product')
		{
			$operateObject = L('PRODUCT');
		}
		elseif($log_type == 'follow')
		{
			$operateObject = L('CONTACT_RECORD');
		}
		elseif($log_type == 'comment')
		{
			$operateObject = L('COMMENT');
		}
		elseif($log_type == 'analysis')
		{
			$operateObject = L('DEMAND_ANALYSIS');
		}
		elseif($log_type == 'competitor')
		{
			$operateObject = L('COMPETITOR');
		}
		elseif($log_type == 'contract')
		{
			$operateObject = L('CONTRACT');
		}
		elseif($log_type == 'account')
		{
			$operateObject = L('RECEIVABLES');
		}
		elseif($log_type == 'receipt')
		{
			$operateObject = L('RECEIVE_PAYMENT');
		}
		elseif($log_type == 'invoice')
		{
			$operateObject = L('INVOICE');
		}
		elseif($log_type == 'clue')
		{
			$operateObject = L('CLUE');
		}
		elseif($log_type == 'opportunity')
		{
			$operateObject = L('OPPORTUNITY');
		}

		switch($operate_type)
		{
			case 1:
				$operateName = L('ADDED');

				if($log_type == 'follow')
				{
					if($clue_id > 0)
					{
						$content = $memberName.L('FOR_CLUES').'<span class="blue8"> '.$name.' </span>'.$operateName.L('A').$operateObject;
					}
					elseif($opportunity_id > 0)
					{
						$content = $memberName.L('FOR_OPPORTUNITY').'<span class="blue8"> '.$name.' </span>'.$operateName.L('A').$operateObject;
					}
					else
					{
						$content = $memberName.L('FOR_CLIENTS').'<span class="blue8"> '.$name.' </span>'.$operateName.L('A').$operateObject;
					}

				}
				elseif($log_type == 'comment')
				{
					$content = $memberName.$operateObject.'了'.L('CONTACT_RECORD').'<span class="blue8"> '.$name.' </span>';
				}
				elseif($log_type == 'competitor')
				{
					//$content = $memberName.L('FOR_CLIENTS').'<span class="blue8"> '.$name.' </span>'.$operateName.L('ONE').$operateObject;
					$content = $memberName.L('FOR_OPPORTUNITY').'<span class="blue8"> '.$name.' </span>'.$operateName.L('ONE').$operateObject;
				}
				else
				{
					$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
				}
			break;
			case 2:
				$operateName = L('EDITED');

				if($log_type == 'follow')
				{
					if($clue_id > 0)
					{
						$content = $memberName.$operateName.L('CLUE').'<span class="blue8"> '.$name.' </span>'.L('OF').L('A').$operateObject;
					}
					elseif($opportunity_id > 0)
					{
						$content = $memberName.$operateName.L('OPPORTUNITY').'<span class="blue8"> '.$name.' </span>'.L('OF').L('A').$operateObject;
					}
					else
					{
						$content = $memberName.$operateName.L('CUSTOMER').'<span class="blue8"> '.$name.' </span>'.L('OF').L('A').$operateObject;
					}
				}
				elseif($log_type == 'analysis')
				{
					//$content = $memberName.$operateName.L('CUSTOMER').'<span class="blue8"> '.$name.' </span>'.L('OF').$operateObject;
					$content = $memberName.$operateName.L('OPPORTUNITY').'<span class="blue8"> '.$name.' </span>'.L('OF').$operateObject;
				}
				elseif($log_type == 'competitor')
				{
					//$content = $memberName.$operateName.L('CUSTOMER').'<span class="blue8"> '.$name.' </span>'.L('OF').L('ONE').$operateObject;
					$content = $memberName.$operateName.L('OPPORTUNITY').'<span class="blue8"> '.$name.' </span>'.L('OF').L('ONE').$operateObject;
				}
				else
				{
					$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
				}
			break;
			case 3:
				$operateName = L('DELETED');

				if($log_type == 'follow')
				{
					if($clue_id > 0)
					{
						$content = $memberName.$operateName.L('CLUE').'<span class="blue8"> '.$name.' </span>'.L('OF').L('A').$operateObject;
					}
					elseif($opportunity_id > 0)
					{
						$content = $memberName.$operateName.L('OPPORTUNITY').'<span class="blue8"> '.$name.' </span>'.L('OF').L('A').$operateObject;
					}
					else
					{
						$content = $memberName.$operateName.L('CUSTOMER').'<span class="blue8"> '.$name.' </span>'.L('OF').L('A').$operateObject;
					}
				}
				elseif($log_type == 'comment')
				{
					$content = $memberName.$operateName.L('CONTACT_RECORD').'<span class="blue8"> '.$name.' </span>'.L('OF').L('A').$operateObject;
				}
				elseif($log_type == 'competitor')
				{
					//$content = $memberName.$operateName.L('CUSTOMER').'<span class="blue8"> '.$name.' </span>'.L('OF').L('ONE').$operateObject;
					$content = $memberName.$operateName.L('OPPORTUNITY').'<span class="blue8"> '.$name.' </span>'.L('OF').L('ONE').$operateObject;
				}
				else
				{
					$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
				}

			break;
			case 4:
				$operateName = L('BACK_TO_NORMAL');

				if($log_type == 'follow')
				{
					if($clue_id > 0)
					{
						$content = $memberName.$operateName.L('CLUE').'<span class="blue8"> '.$name.' </span>'.L('OF').L('ONE').$operateObject;
					}
					elseif($opportunity_id > 0)
					{
						$content = $memberName.$operateName.L('OPPORTUNITY').'<span class="blue8"> '.$name.' </span>'.L('OF').L('ONE').$operateObject;
					}
					else
					{
						$content = $memberName.$operateName.L('CUSTOMER').'<span class="blue8"> '.$name.' </span>'.L('OF').L('ONE').$operateObject;
					}
				}
				else
				{
					$content = $memberName . $operateName . $operateObject . '<span class="blue8"> ' . $name . ' </span>';
				}
			break;
			case 14:
				$operateName = L('REVIEWED');

				$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
			break;
			case 15:
				$operateName = L('COMPLETELY_DELETED');

				$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
				break;
			case 5:
				$operateName = L('RECEIVED');

				$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
			break;
			case 6:
				$operateName = L('ASSIGNED');

				$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
			break;
			case 7:
				$operateName = L('RECYCLED');

				$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
			break;
			case 8:
				$operateName = L('TRANSFERRED');

				$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
			break;
			case 9:
				$operateName = L('GIVE_UP_CUSTOMERS');

				$content = $memberName.$operateName.'<span class="blue8"> '.$name.' </span>';
			break;
			case 10:
				$operateName = L('SET_PRIMARY_CONTACT');

				$content = $memberName.L('WILL').'<span class="blue7"> '.$name.' </span>'.$operateName;
			break;
			case 11:
				$operateName = L('ON_THE_SHELF');

				$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
			break;
			case 12:
				$operateName = L('NOT_AVAILABLE');

				$content = $memberName.$operateName.$operateObject.'<span class="blue8"> '.$name.' </span>';
			break;
			case 13:
				$operateName = L('HAS_LOST_ORDER');

				$content = $memberName.'：'.$operateObject.'<span class="blue8"> '.$name.' </span>'.$operateName;
			break;
			case 16:
				$operateName = L('CONVERT_TO_CUSTOMER');

				$customer = CrmgetCrmDetailList('customer',$customer_id,$company_id,'name');

				$content = $memberName.'：'.$operateObject.'<span class="blue8"> '.$name.' </span>'.$operateName.'<span class="blue8"> '.$customer['name'].' </span>';
			break;
		}

		$data = [];

		$data['log_type'] = $log_type;

		$data['operate_type'] = $operate_type;

		$data['company_id'] = $company_id;

		$data['member_id'] = $member_id;

		$data['customer_id'] = $customer_id ? $customer_id : 0;

		$data['contacter_id'] = $contacter_id ? $contacter_id : 0;

		$data['order_id'] = $order_id ? $order_id : 0;

		$data['product_id'] = $product_id ? $product_id : 0;

		$data['follow_id'] = $follow_id ? $follow_id : 0;

		$data['target_id'] = $target_id ? $target_id : 0;

		$data['analysis_id'] = $analysis_id ? $analysis_id : 0;

		$data['competitor_id'] = $competitor_id ? $competitor_id : 0;

		$data['contract_id'] = $contract_id ? $contract_id : 0;

		$data['account_id'] = $account_id ? $account_id : 0;

		$data['receipt_id'] = $receipt_id ? $receipt_id : 0;

		$data['invoice_id'] = $invoice_id ? $invoice_id : 0;

		$data['clue_id'] = $clue_id ? $clue_id : 0;

		$data['opportunity_id'] = $opportunity_id ? $opportunity_id : 0;

		$data['log_name'] = $name;

		$data['log_content'] = $content;

		$data['createtime'] = NOW_TIME;

		$this->add($data);
	}

	public function addCrmOperate($name,$type,$id,$company_id,$member_id,$reason='')
	{
		$dataOperate['company_id'] = $company_id;

		$dataOperate[$name.'_id'] = $id;

		$dataOperate['type'] = $type;

		$dataOperate['member_id'] = $member_id;

		$dataOperate['reason'] = $reason;

		$dataOperate['createtime'] = NOW_TIME;

		$operate_id = getCrmDbModel($name.'_operate')->add($dataOperate);

		return $operate_id;
	}

	public function getCrmLog($log_type,$id,$company_id,$Page = '')
	{
		$field = ['company_id'=>$company_id,$log_type.'_id'=>$id];

		$count = getCrmDbModel('crmlog')->where($field)->count();

		$Page = new \Think\Page($count, 10);

		$crmlog = getCrmDbModel('crmlog')->where($field)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

		foreach ($crmlog as $key=>&$val)
		{
			$val['member']['name'] = getCustomerCreateName($val['member_id'],'',$company_id);

			if($val['member_id'] > 0)
			{
				$val['member']['face'] = M('member')->where(['company_id'=>$company_id,'member_id'=>$val['member_id'],'type'=>1])->getField('face');
			}

			if($val['log_type'] == 'customer')
			{
				$operateObject = L('CUSTOMER');
			}
			elseif($val['log_type'] == 'contacter')
			{
				$operateObject = L('CONTACT');

				if($log_type == 'customer')
				{
					$contacter = CrmgetCrmDetailList('contacter',$val['contacter_id'],$company_id,'name');

					if($contacter['name'])
					{
						$operateObject .= '<span class="blue8"> ' . $contacter['name'] . '</span>';
					}
					else
					{
						$operateObject .= ' <span class="red1">'.$val['log_name'].'</span>';
					}
				}
			}
			elseif($val['log_type'] == 'order')
			{
				$operateObject = L('ORDER');

				if($log_type == 'customer')
				{
					$order = CrmgetCrmDetailList('order',$val['order_id'],$company_id,'name');

					if($order['name'])
					{
						$operateObject .= '<span class="blue8"> ' . $order['name'] . '</span>';
					}
					else
					{
						$operateObject .= ' <span class="red1">'.$val['log_name'].'</span>';
					}
				}
			}
			elseif($val['log_type'] == 'product')
			{
				$operateObject = L('PRODUCT');
			}
			elseif($val['log_type'] == 'follow')
			{
				$operateObject = L('CONTACT_RECORD');
			}
			elseif($val['log_type'] == 'comment')
			{
				$operateObject = L('COMMENT');
			}
			elseif($val['log_type'] == 'analysis')
			{
				$operateObject = L('DEMAND_ANALYSIS');
			}
			elseif($val['log_type'] == 'competitor')
			{
				$operateObject = L('COMPETITOR');

				if($log_type == 'customer' || $log_type == 'opportunity')
				{
					$competitor = CrmgetCrmDetailList('competitor',$val['competitor_id'],$company_id,'name');

					if($competitor['name'])
					{
						$operateObject .= '<span class="blue8"> ' . $competitor['name'] . '</span>';
					}
					else
					{
						$operateObject .= ' <span class="red1">'.$val['log_name'].'</span>';
					}
				}
			}
			elseif($val['log_type'] == 'contract')
			{
				$operateObject = L('CONTRACT');

				if($log_type == 'customer' || $log_type == 'opportunity')
				{
					$contract = CrmgetCrmDetailList('contract',$val['contract_id'],$company_id,'name');

					if($contract['name'])
					{
						$operateObject .= ' <a class="blue8" href="javascript:" mini="contract" data-id="' . encrypt($val['contract_id'], 'CONTRACT') . '" data-type="detailPop">' . $contract['name'] . '</a>';
					}
					else
					{
						$operateObject .= ' <a class="red1" href="javascript:">'.$val['log_name'].'</a>';
					}
				}
			}
			elseif($val['log_type'] == 'account')
			{
				$operateObject = L('RECEIVABLES');

				if($log_type == 'customer' || $log_type == 'contract')
				{
					$account = getCrmDbModel('account')->where(['company_id'=>$company_id,'account_id'=>$val['account_id']])->field('account_prefix,account_no')->find();
					if($account)
					{
						$operateObject .= ' <a class="blue8" href="javascript:" onclick="clickOpenDetailByA(this,\'account\');" data-id="'.encrypt($val['account_id'],'ACCOUNT').'" data-type="detailPop">'.$account['account_prefix'].$account['account_no'].'</a>';
					}
					else
					{
						$operateObject .= ' <a class="red1" href="javascript:">'.$val['log_name'].'</a>';
					}
				}
			}
			elseif($val['log_type'] == 'receipt')
			{
				$operateObject = L('RECEIVE_PAYMENT');

				if($log_type == 'customer' || $log_type == 'contract' || $log_type == 'account')
				{
					$receipt = getCrmDbModel('receipt')->where(['company_id'=>$company_id,'receipt_id'=>$val['receipt_id']])->field('receipt_prefix,receipt_no')->find();
					if($receipt)
					{
						$operateObject .= ' <a class="blue8" href="javascript:" onclick="clickOpenDetailByA(this,\'receipt\');" data-id="'.encrypt($val['receipt_id'],'RECEIPT').'" data-type="detailPop">'.$receipt['receipt_prefix'].$receipt['receipt_no'].'</a>';
					}
					else
					{
						$operateObject .= ' <a class="red1" href="javascript:">'.$val['log_name'].'</a>';
					}
				}
			}
			elseif($val['log_type'] == 'invoice')
			{
				$operateObject = L('INVOICE');

				if($log_type == 'customer' || $log_type == 'contract' || $log_type == 'receipt')
				{
					$invoice = getCrmDbModel('invoice')->where(['company_id'=>$company_id,'invoice_id'=>$val['invoice_id']])->field('invoice_prefix,invoice_no')->find();
					if($invoice)
					{
						$operateObject .= ' <a class="blue8" href="javascript:" onclick="clickOpenDetailByA(this,\'invoice\');" data-id="'.encrypt($val['invoice_id'],'INVOICE').'" data-type="detailPop">'.$invoice['invoice_prefix'].$invoice['invoice_no'].'</a>';
					}
					else
					{
						$operateObject .= ' <a class="red1" href="javascript:">'.$val['log_name'].'</a>';
					}
				}
			}
			elseif($val['log_type'] == 'clue')
			{
				$operateObject = L('CLUE');
			}
			elseif($val['log_type'] == 'opportunity')
			{
				$operateObject = L('OPPORTUNITY');

				if($log_type == 'customer')
				{
					$opportunity = CrmgetCrmDetailList('opportunity',$val['opportunity_id'],$company_id,'name');

					if($opportunity['name'])
					{
						$operateObject .= '<span class="blue8"> ' . $opportunity['name'] . ' </span>';
					}
					else
					{
						$operateObject .= ' <span class="red1">'.$val['log_name'].'</span>';
					}
				}
			}

			switch($val['operate_type'])
			{
				case 1:
					$operateName = L('ADDED');

					if($val['log_type'] == 'follow')
					{
						$content = $operateName.L('A').$operateObject;
					}
					elseif($val['log_type'] == 'comment')
					{
						$content = $operateObject.'了'.L('CONTACT_RECORD');
					}
					elseif($val['log_type'] == 'competitor')
					{
						$content = $operateName.L('ONE').$operateObject;
					}
					else
					{
						$content = $operateName.$operateObject;
					}
					break;
				case 2:
					$operateName = L('EDITED');

					$content = $operateName.$operateObject;

					break;
				case 3:
					$operateName = L('DELETED');

					$content = $operateName.$operateObject;

					break;
				case 4:
					$operateName = L('BACK_TO_NORMAL');

					$content = $operateName.$operateObject;
					break;
				case 14:
					$operateName = L('REVIEWED');

					$content = $operateName.$operateObject;
					break;
				case 15:
					$operateName = L('COMPLETELY_DELETED');

					$content = $operateName.$operateObject;
					break;
				case 5:
					$operateName = L('RECEIVED');

					$content = $operateName.$operateObject;
					break;
				case 6:
					$operateName = L('ASSIGNED');

					$content = $operateName.$operateObject;
					break;
				case 7:
					$operateName = L('RECYCLED');

					$content = $operateName.$operateObject;
					break;
				case 8:
					$operateName = L('TRANSFERRED');

					$content = $operateName.$operateObject;
					break;
				case 9:
					$operateName = L('GIVE_UP');

					$content = $operateName.$operateObject;
					break;
				case 10:
					$operateName = L('SET_PRIMARY_CONTACT');

					$contacter = CrmgetCrmDetailList('contacter',$val['contacter_id'],$company_id,'name');

					$content = L('WILL').'<span class="blue8"> '.$contacter['name'].' </span>'.$operateName;
					break;
				case 11:
					$operateName = L('ON_THE_SHELF');

					$content = $operateName.$operateObject;
					break;
				case 12:
					$operateName = L('NOT_AVAILABLE');

					$content = $operateName.$operateObject;
					break;
				case 13:
					$operateName = L('HAS_LOST_ORDER');

					$content = $operateObject.$operateName;
					break;
				case 16:
					$operateName = L('CONVERT_TO_CUSTOMER');

					$customer = CrmgetCrmDetailList('customer',$val['customer_id'],$company_id,'name');

					$content = $operateObject.'<a class="blue8" href="javascript:" onclick="clickOpenDetailByA(this,\'clue\');" data-id="'.encrypt($val['clue_id'],'CLUE').'" data-type="detailPop"> '.$val['log_name'].' </a>'.$operateName.'<a class="blue8" href="javascript:" onclick="clickOpenDetailByA(this,\'customer\');" data-id="'.encrypt($val['customer_id'],'CUSTOMER').'" data-type="detailPop"> '.$customer['name'].' </a>';
					break;
			}

			$val['content'] = $content;

			$val['createtime'] = getDates($val['createtime']);
		}

		return ['crmlog'=>$crmlog,'count'=>$count];
	}
}

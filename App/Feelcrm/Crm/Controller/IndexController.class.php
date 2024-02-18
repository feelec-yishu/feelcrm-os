<?php
/*
 * Feelcrm客户管理系统为菲莱克斯（成都）科技有限公司出品，未经授权许可不得使用！
 * 官网：www.feelcrm.cn
 * 作者：Wangwei
 * 邮箱: wangwei@feelec.net
 * 	 QQ: 1287566676
 */

namespace Crm\Controller;

use Crm\Common\BasicController;

class IndexController extends BasicController
{
	public function index($redirect_url = '')
	{
        $redirect_url = $redirect_url ? U($redirect_url) : U('index/welcome');

		if(isset($_GET['request']) && $_GET['request'] == 'flow')
        {
			$Itime = urldecode(I('get.Itime'));

			$Itime = str_replace("&quot;","\"",$Itime);

			$createtime = unserialize($Itime);

            if($_GET['source'] == 'all')
            {
				$view_data = CrmmemberRoleCrmauth($this->_member,$this->_company_id,$this->member_id,'all');

				$events = $this->getEvent(['company_id'=>$this->_company_id,'createtime'=>$createtime]);

                $result = ['data'=>$events['events'],'pages'=>$events['pages']];
            }
            elseif($_GET['source'] == 'group')
			{
				$view_data = CrmmemberRoleCrmauth($this->_member,$this->_company_id,$this->member_id,'group');

				$events = $this->getEvent(['member_id'=>$view_data['field'],'company_id'=>$this->_company_id,'createtime'=>$createtime]);

				$result = ['data'=>$events['events'],'pages'=>$events['pages']];
			}
			else
            {
				$view_data = CrmmemberRoleCrmauth($this->_member,$this->_company_id,$this->member_id,'own');

				$events = $this->getEvent(['member_id'=>$view_data['field'],'company_id'=>$this->_company_id,'createtime'=>$createtime]);

                $result = ['data'=>$events['events'],'pages'=>$events['pages']];
            }

            $this->ajaxReturn($result);
        }

		if($source_type = I('get.source_type'))
		{
			$redirect_url .= '?source_type='.$source_type;

			if($apiData = I('get.apiData'))
			{
				$redirect_url .= '&apiData='.$apiData;
			}
		}

        $this->assign('redirect_url',$redirect_url);

		$this->display();
    }

	public function getEvent($where=[],$page = 5)
	{
		$count = getCrmDbModel('crmlog')->where($where)->count();

		$Page = new \Think\Page($count, $page);

		$event = getCrmDbModel('crmlog')->where($where)->limit($Page->firstRow, $Page->listRows)->order('createtime desc')->select();

		$events = [];

		foreach($event as $key => &$val)
		{
			if($val['member_id'] > 0)
			{
				$member = M('Member')->where(['company_id'=>$this->_company_id,'closed'=>0,'type'=>1,'member_id'=>$val['member_id']])->field('member_id,account,name,role_id')->find();

				$role = D('Role')->where($where)->field('role_id,role_name')->fetchAll();

				$memberName = '<span class="blue8">'.$member['name'].'</span>--'.$role[$member['role_id']]['role_name'];
			}else {
				$memberName = getCustomerCreateName($val['member_id'],'',0,'html');
			}

			$val['memberName'] = $memberName;

			if($val['log_type'] == 'customer')
			{
				$operateObject = L('CUSTOMER');

				$eventId = encrypt($val['customer_id'],'CUSTOMER');
			}
			elseif($val['log_type'] == 'contacter')
			{
				$operateObject = L('CONTACT');

				$eventId = $val['contacter_id'];
			}
			elseif($val['log_type'] == 'order')
			{
				$operateObject = L('ORDER');

				$eventId = encrypt($val['order_id'],'ORDER');
			}
			elseif($val['log_type'] == 'product')
			{
				$operateObject = L('PRODUCT');

				$eventId = $val['product_id'];
			}
			elseif($val['log_type'] == 'follow')
			{
				$operateObject = L('CONTACT_RECORD');

				if($val['clue_id'] > 0)
				{
					$eventId = encrypt($val['clue_id'],'CLUE');
				}
				else
				{
					$eventId = encrypt($val['customer_id'],'CUSTOMER');
				}
			}
			elseif($val['log_type'] == 'comment')
			{
				$operateObject = L('COMMENT');

				$eventId = encrypt($val['follow_id'],'FOLLOW');
			}
			elseif($val['log_type'] == 'analysis')
			{
				$operateObject = L('DEMAND_ANALYSIS');

				//$eventId = encrypt($val['customer_id'],'CUSTOMER');
				$eventId = encrypt($val['opportunity_id'],'OPPORTUNITY');
			}
			elseif($val['log_type'] == 'competitor')
			{
				$operateObject = L('COMPETITOR');

				//$eventId = encrypt($val['customer_id'],'CUSTOMER');
				$eventId = encrypt($val['opportunity_id'],'OPPORTUNITY');
			}
			elseif($val['log_type'] == 'contract')
			{
				$operateObject = L('CONTRACT');

				$eventId = encrypt($val['contract_id'],'CONTRACT');
			}
			elseif($val['log_type'] == 'account')
			{
				$operateObject = L('RECEIVABLES');

				$eventId = encrypt($val['account_id'],'ACCOUNT');
			}
			elseif($val['log_type'] == 'receipt')
			{
				$operateObject = L('RECEIVE_PAYMENT');

				$eventId = encrypt($val['receipt_id'],'RECEIPT');
			}
			elseif($val['log_type'] == 'invoice')
			{
				$operateObject = L('INVOICE');

				$eventId = encrypt($val['invoice_id'],'INVOICE');
			}
			elseif($val['log_type'] == 'clue')
			{
				$operateObject = L('CLUE');

				$eventId = encrypt($val['clue_id'],'CLUE');
			}
			elseif($val['log_type'] == 'opportunity')
			{
				$operateObject = L('OPPORTUNITY');

				$eventId = encrypt($val['opportunity_id'],'OPPORTUNITY');
			}

			$val['operateObject'] = $operateObject;

			$val['eventId'] = $eventId;

			switch($val['operate_type'])
			{
				case 1:
					if($val['log_type'] == 'comment')
					{
						$operateName = L('COMMENTED');

						$val['eventTypeLang'] = $operateName.L('CONTACT_RECORD').'<span class="blue8"> <a href="javascript:">'.$val['log_name'].'</a> </span>';
					}
					else
					{
						$operateName = L('ADDED');

						if($val['log_type'] == 'follow')
						{
							if($val['clue_id'] > 0)
							{
								$val['eventTypeLang'] = L('FOR_CLUES').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'clue\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.$operateName.L('A').$operateObject;
							}
							elseif($val['opportunity_id'] > 0)
							{
								$val['eventTypeLang'] = L('FOR_OPPORTUNITY').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'opportunity\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.$operateName.L('A').$operateObject;
							}
							else
							{
								$val['eventTypeLang'] = L('FOR_CLIENTS').'<span class="blue8"> <a href="javascript:" mini="customer" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.$operateName.L('A').$operateObject;
							}
						}
						elseif($val['log_type'] == 'competitor')
						{
							//$val['eventTypeLang'] = L('FOR_CLIENTS').'<span class="blue8"> <a href="javascript:" mini="customer" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.$operateName.L('ONE').$operateObject;
							$val['eventTypeLang'] = L('FOR_OPPORTUNITY').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'opportunity\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.$operateName.L('ONE').$operateObject;
						}
						else
						{
							$val['eventTypeLang'] = $operateName.$operateObject;
						}
					}
				break;
				case 2:
					$operateName = L('EDITED');

					if($val['log_type'] == 'follow')
					{
						if($val['clue_id'] > 0)
						{
							$val['eventTypeLang'] = $operateName.L('CLUE').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'clue\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('A').$operateObject;
						}
						elseif($val['opportunity_id'] > 0)
						{
							$val['eventTypeLang'] = $operateName.L('OPPORTUNITY').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'opportunity\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('A').$operateObject;
						}
						else
						{
							$val['eventTypeLang'] = $operateName.L('CUSTOMER').'<span class="blue8"> <a href="javascript:" mini="customer" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('A').$operateObject;
						}
					}
					elseif($val['log_type'] == 'analysis')
					{
						//$val['eventTypeLang'] = $operateName.L('CUSTOMER').'<span class="blue8"> <a href="javascript:" mini="customer" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').$operateObject;
						$val['eventTypeLang'] = $operateName.L('OPPORTUNITY').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'opportunity\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').$operateObject;
					}
					elseif($val['log_type'] == 'competitor')
					{
						//$val['eventTypeLang'] = $operateName.L('CUSTOMER').'<span class="blue8"> <a href="javascript:" mini="customer" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('ONE').$operateObject;
						$val['eventTypeLang'] = $operateName.L('OPPORTUNITY').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'opportunity\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('ONE').$operateObject;
					}
					else
					{
						$val['eventTypeLang'] = $operateName.$operateObject;
					}
				break;
				case 3:
					$operateName = L('DELETED');

					if($val['log_type'] == 'follow')
					{
						if($val['clue_id'] > 0)
						{
							$val['eventTypeLang'] = $operateName.L('CLUE').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'clue\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('A').$operateObject;
						}
						elseif($val['opportunity_id'] > 0)
						{
							$val['eventTypeLang'] = $operateName.L('OPPORTUNITY').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'opportunity\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('A').$operateObject;
						}
						else
						{
							$val['eventTypeLang'] = $operateName.L('CUSTOMER').'<span class="blue8"> <a href="javascript:" mini="customer" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('A').$operateObject;
						}
					}
					elseif($val['log_type'] == 'comment')
					{
						$val['eventTypeLang'] = $operateName.L('CONTACT_RECORD').'<span class="blue8"> <a href="javascript:">'.$val['log_name'].'</a> </span>'.L('OF').L('A').$operateObject;
					}
					elseif($val['log_type'] == 'competitor')
					{
						//$val['eventTypeLang'] = $operateName.L('CUSTOMER').'<span class="blue8"> <a href="javascript:" mini="customer" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('ONE').$operateObject;
						$val['eventTypeLang'] = $operateName.L('OPPORTUNITY').'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'opportunity\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.L('OF').L('ONE').$operateObject;
					}
					else
					{
						$val['eventTypeLang'] = $operateName.$operateObject;
					}

				break;
				case 4:
					$operateName = L('BACK_TO_NORMAL');

					$val['eventTypeLang'] = $operateName.$operateObject;
				break;
				case 14:
					$operateName = L('REVIEWED');

					$val['eventTypeLang'] = $operateName.$operateObject;
				break;
				case 15:
					$operateName = L('COMPLETELY_DELETED');

					$val['eventTypeLang'] = $operateName.$operateObject;
					break;
				case 5:
					$operateName = L('RECEIVED');

					$val['eventTypeLang'] = $operateName.$operateObject;
				break;
				case 6:
					$operateName = L('ASSIGNED');

					$val['eventTypeLang'] = $operateName.$operateObject;
				break;
				case 7:
					$operateName = L('RECYCLED');

					$val['eventTypeLang'] = $operateName.$operateObject;
				break;
				case 8:
					$operateName = L('TRANSFERRED');

					$val['eventTypeLang'] = $operateName.$operateObject;
				break;
				case 9:
					$operateName = L('GIVE_UP_CUSTOMERS');

					$val['eventTypeLang'] = $operateName;
				break;
				case 10:
					$operateName = L('SET_PRIMARY_CONTACT');

					$val['eventTypeLang'] = L('WILL').'<span class="blue8"> <a href="javascript:">'.$val['log_name'].'</a> </span>'.$operateName;
				break;
				case 11:
					$operateName = L('ON_THE_SHELF');

					$val['eventTypeLang'] = $operateName.$operateObject;
				break;
				case 12:
					$operateName = L('NOT_AVAILABLE');

					$val['eventTypeLang'] = $operateName.$operateObject;
				break;
				case 13:
					$operateName = L('HAS_LOST_ORDER');

					//$val['eventTypeLang'] = $operateObject.'<span class="blue8"> <a href="javascript:" mini="customer" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.$operateName;
					$val['eventTypeLang'] = $operateObject.'<span class="blue8"> <a href="javascript:" onclick="clickOpenDetailByA(this,\'opportunity\');" data-id="'.$eventId.'">'.$val['log_name'].'</a> </span>'.$operateName;
				break;
				case 16:
					$operateName = L('CONVERT_TO_CUSTOMER');

					$customer = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name');

					$val['eventTypeLang'] = $operateObject.'<a class="blue8" href="javascript:" onclick="clickOpenDetailByA(this,\'clue\');" data-id="'.encrypt($val['clue_id'],'CLUE').'" > '.$val['log_name'].' </a>'.$operateName.'<a class="blue8" href="javascript:" onclick="clickOpenDetailByA(this,\'customer\');" data-id="'.encrypt($val['customer_id'],'CUSTOMER').'" > '.$customer['name'].' </a>';

				break;
			}

			$val['create_at'] = date('H:i',$val['createtime']);

            $events[date('Y-m-d',$val['createtime'])][] = $val;
		}

		return ['events'=>$events,'pages'=>ceil($count/$page)];
	}

	public function welcome()
	{
	    $customer_auth = isset($_GET['customer_auth']) && $_GET['customer_auth'] ? I('get.customer_auth') : 'own';

        $time_range = isset($_GET['time_range']) && $_GET['time_range'] ? I('get.time_range') : 'today';

        $commonWhere = ['company_id'=>$this->_company_id,'isvalid'=>1];

//        客户查看权限

		$all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'all',$this->_member['role_id'],'crm');

		$group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'group',$this->_member['role_id'],'crm');

		$own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'own',$this->_member['role_id'],'crm');

        $users = $members = [];

	    $getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$this->_company_id,$this->member_id,$customer_auth);

		$users = $getCustomerAuth['users'];

		$members = $getCustomerAuth['members'];

		$where['member_id'] = $getCustomerAuth['memberRoleArr'];

        if($time_range == 'today')
        {
            $where['createtime'] = [['egt',strtotime(date('Y-m-d 00:00:00'))],['elt',strtotime(date('Y-m-d 23:59:59'))]];
        }
        else if($time_range == 'week')
        {
            $week = strtotime(date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)));

            $where['createtime'] = [['egt',$week],['elt',NOW_TIME]];
        }
        else if($time_range == 'month')
        {
            $month = strtotime(date('Y-m-d', strtotime(date('Y-m', time()) . '-01 00:00:00')));

            $where['createtime'] = [['egt',$month],['elt',strtotime(date('Y-m-d 23:59:59'))]];
        }
		else if($time_range == 'custom')
        {
            $custom_time = I('get.custom_time');

            $customTime = explode(' - ',$custom_time);

			$starttime = strtotime($customTime[0].' 00:00:00');

			$endtime = strtotime($customTime[1].' 23:59:59');

            $where['createtime'] = [['egt',$starttime],['elt',$endtime]];

            $this->assign('custom_time',$custom_time);
        }
        else
        {
            $where['createtime'] = ['elt',NOW_TIME];
        }

		$customerWhere = $where;

		if($this->_crmsite['customerReseller'] == 1)
		{
			$customerWhere['customer_type'] = ['neq','agent'];
		}
		else
		{
			//$customerWhere['customer_type'] = 'agent';
		}

		//创建人维度查看客户权限
		$CreaterViewCustomer = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CreaterViewCustomer',$this->_member['role_id'],'crm');

		if($CreaterViewCustomer)
		{
			$customerWhere['_string'] = getCreaterViewSql($customerWhere['member_id']);

			unset($customerWhere['member_id']);
		}

//        客户
	    $customer = getCrmDbModel('customer')->where(array_merge($commonWhere,$customerWhere))->select();

//	     联系人
        $customerIds = [];

	    foreach($customer as $ck=>$cv)
	    {
            $customerIds[$ck] = $cv['customer_id'];
        }

        $contactWhere = ['customer_id'=>['in',implode(',',$customerIds)],'createtime'=>$where['createtime']];

        $contact = getCrmDbModel('contacter')->where(array_merge($commonWhere,$contactWhere))->select();

//        联系记录
        $followWhere = ['createtime'=>$where['createtime'],'member_id'=>$where['member_id']];

        $follow = getCrmDbModel('followup')->where(array_merge($commonWhere,$followWhere))->select();
/*
//        订单
        $order = getCrmDbModel('order')->where(array_merge($commonWhere,$followWhere))->field('order_id')->select();

//        订单金额
        $price_form_id = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'type'=>'order','form_name'=>'price'])->getField('form_id');

        $order_form = getCrmDbModel('order_detail')->where(['form_id'=>$price_form_id])->field('order_id,form_content')->select();

        $order_ids = [];

        foreach($order as $ok=>$ov)
        {
            $order_ids[$ok] = $ov['order_id'];
        }

        $orderTotalMoney = 0;

        foreach($order_form as $ofk=>$ofv)
        {
            if(in_array($ofv['order_id'],$order_ids))
            {
               $orderTotalMoney += $ofv['form_content'];
            }
        }
*/

		$contractWhere = $where;

		//创建人维度查看合同权限
		$CreaterViewContract = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CreaterViewContract',$this->_member['role_id'],'crm');

		if($CreaterViewContract)
		{
			$contractWhere['_string'] = getCreaterViewSql($contractWhere['member_id']);

			unset($contractWhere['member_id']);
		}

//		合同
        $contract = getCrmDbModel('contract')->where(array_merge($commonWhere,$contractWhere))->field('contract_id')->select();
		//        合同金额
        $money_form_id = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'type'=>'contract','form_name'=>'money'])->getField('form_id');

        $contract_form = getCrmDbModel('contract_detail')->where(['form_id'=>$money_form_id])->field('contract_id,form_content')->select();

        $contract_ids = [];

        foreach($contract as $ok=>$ov)
        {
            $contract_ids[$ok] = $ov['contract_id'];
        }

        $contractTotalMoney = 0;

        foreach($contract_form as $ofk=>$ofv)
        {
            if(in_array($ofv['contract_id'],$contract_ids))
            {
               $contractTotalMoney += $ofv['form_content'];
            }
        }

		//创建人维度查看客户权限
		if($CreaterViewCustomer)
		{
			$viewWhere = ['member_id|creater_id'=>$where['member_id'], 'member_id'=>['gt',0]];
		}
		else
		{
			$viewWhere = ['member_id'=>$where['member_id']];
		}

//        今日待跟进的客户
        $followCustomers = getCrmDbModel('customer')
            ->where([
                'company_id'=>$this->_company_id,
                'isvalid'=>1,
	            $viewWhere,
                'customer_type'=>$customerWhere['customer_type'],
                'nextcontacttime'=>[['egt',strtotime(date('Y-m-d 00:00:00'))],['elt',strtotime(date('Y-m-d 23:59:59'))]]
            ])->field('customer_id,member_id,first_contact_id,customer_no,customer_prefix')->select();


//        3天内回收的客户
        $recoverCustomers = getCrmDbModel('customer_recover')
            ->where([
                'company_id'=>$this->_company_id,
                'member_id'=>$where['member_id'],
                'customer_type'=>$customerWhere['customer_type'],
                'recover_time'=>[['egt',strtotime(date('Y-m-d 23:59:59')) - 24 * 60 * 60 * 3],['elt',time()]]
            ])->select();

        $customers = ['follow'=>$followCustomers,'recover'=>$recoverCustomers];

//        客户名称
        /*$name_form_id = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'type'=>'customer','form_name'=>'name'])->getField('form_id');

        $customer_detail = getCrmDbModel('customer_detail')->where(['form_id'=>$name_form_id])->field('customer_id,form_content')->select();

//        首要联系人
        $contact_form = getCrmDbModel('define_form')
            ->where(['company_id'=>$this->_company_id,'type'=>'contacter','form_name'=>['in',['name','phone']]])
            ->field('form_id,form_name')->select();*/

        foreach($customers as $k=>&$v)
        {
            foreach($v as &$v1)
            {
				$v1['detail'] = CrmgetCrmDetailList('customer',$v1['customer_id'],$this->_company_id,'name,phone');

	            $v1['member_name'] = M('member')->where(['member_id'=>$v1['member_id']])->getField('name');

				if($k == 'recover')
				{
					$v1['customer_prefix'] = getCrmDbModel('customer')->where(['customer_id'=>$v1['customer_id'],'company_id'=>$this->_company_id])->getField('customer_prefix');

					$v1['customer_no'] = getCrmDbModel('customer')->where(['customer_id'=>$v1['customer_id'],'company_id'=>$this->_company_id])->getField('customer_no');
				}
            }
        }

//        员工行动统计
        foreach($users as &$u)
        {
            $actionWhere = ['member_id'=>$u['member_id'],'createtime'=>$where['createtime'],'isvalid'=>1];

            $follow_recode = getCrmDbModel('followup')->where($actionWhere)->select();

			$recode_customer[] = '';

			foreach($follow_recode as $rek => $rev)
			{
				$recode_customer[$rek] = $rev['customer_id'];
			}

			$recode_customer = array_unique($recode_customer);

			$actionWhereCust['member_id'] = $u['member_id'];

			$actionWhereCust['isvalid'] = 1;

			$actionWhereCust['customer_id'] = ['in',implode(',',$recode_customer)];

			$u['IuserCust'] = $actionWhereCust['customer_id'];

			$u['follow_customer'] = getCrmDbModel('Customer')->where($actionWhereCust)->count();

			$u['follow_recode'] = count($follow_recode);
        }

//        客户来源统计
        $source_form_id = getCrmDbModel('define_form')
            ->where(['company_id'=>$this->_company_id,'type'=>'customer','form_name'=>'origin'])
            ->getField('form_id');

        $source = getCrmDbModel('customer_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$source_form_id])->field('customer_id,form_content')->select();

        $source1 = $source2 = [];

        foreach($source as $s)
        {
			$source1[$s['form_content']][] = $s['customer_id'];
        }

        $i = 0;

        foreach($source1 as $k1=>$s1)
        {
            $source2[$i] = ['source_name'=>$k1];

            foreach($s1 as $s2)
            {
                $kehu = getCrmDbModel('customer')->where(['customer_id'=>$s2,$viewWhere,'isvalid'=>1,'createtime'=>$where['createtime'],'customer_type'=>$customerWhere['customer_type']])->field('customer_id,is_trade')->find();

				if(count($kehu) > 0)
				{
					$source2[$i]['customer_num'] += 1;

					if($kehu['is_trade'] == 1)
					{
						$source2[$i]['trade_num'] += 1;
					}
					else
					{
						$source2[$i]['trade_num'] += 0;
					}
				}
				else
				{
					$source2[$i]['customer_num'] += 0;

					$source2[$i]['trade_num'] += 0;
				}
            }

            $i++;
        }

//        客户订单统计
        $customers1 = M('member')
            ->alias('a')
            ->join(C('CRM_DB_CONFIG.DB_NAME').'.'.C('CRM_DB_CONFIG.DB_PREFIX').'customer as b on a.member_id = b.member_id','left')
            ->where(['a.company_id'=>$this->_company_id,'a.type'=>1,'a.closed'=>0,'b.isvalid'=>1,'b.member_id'=>$where['member_id'],'b.createtime'=>$where['createtime'],'b.customer_type'=>$customerWhere['customer_type']])
            ->field('a.member_id,a.name,b.customer_id,b.is_trade')
            ->select();

        $customers2 = $customers3 = [];

        foreach($customers1 as $c)
        {
            $customers2[$c['member_id']][] = $c;
        }

        $i = 0;
        foreach($customers2 as $c2)
        {
            $customers3[$i] = ['name'=>$c2[0]['name'],'customer_num'=>count($c2),'member_id'=>$c2[0]['member_id']];

            foreach($c2 as $c3)
            {
                if($c3['is_trade'] == 1)
                {
                    $customers3[$i]['trade_num'] += 1;
                }
                else
                {
                    $customers3[$i]['trade_num'] += 0;
                }
            }

            $i++;
        }

		$crmlog = getCrmDbModel('crmlog')->where(['company_id'=>$this->_company_id])->order('createtime desc')->limit('0,20')->select();

		$this->assign('crmlog',$crmlog);

        $this->assign('customer',$customer);

        $this->assign('customers',$customers);

        $this->assign('contact',$contact);

        $this->assign('follow',$follow);

        //$this->assign('order',$order);

        $this->assign('contract',$contract);

        $this->assign('users',$users);

        $this->assign('customer_source',$source2);

        $this->assign('customer_trade',$customers3);

        //$this->assign('orderTotalMoney',$orderTotalMoney);

        $this->assign('contractTotalMoney',$contractTotalMoney);

        $this->assign('customer_auth',$customer_auth);

        $this->assign('time_range',$time_range);

        $this->assign('isAllViewAuth',$all_view_auth);

        $this->assign('isGroupViewAuth',$group_view_auth);

        $this->assign('isOwnViewAuth',$own_view_auth);

		$this->assign('nowTime',date('Y-m-d',time()));

        $this->assign('yesTime',date('Y-m-d',strtotime('-1 day')));

		$this->assign('ImemberRole',urlencode(serialize($getCustomerAuth['memberRoleArr'])));

		$this->assign('Itime',urlencode(serialize($where['createtime'])));

        $this->display();
	}
}

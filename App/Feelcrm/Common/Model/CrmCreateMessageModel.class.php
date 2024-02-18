<?php
/**
 * Created by PhpStorm.
 * User: navyy
 * Date: 2017.07.21
 * Time: 9:56
 */
namespace Common\Model;

use Common\Model\BasicModel;

class CrmCreateMessageModel extends BasicModel
{
    protected $autoCheckFields = false;

    protected $temp = [];

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        if(cookie('think_language') == 'en-us')
        {
            $this->temp = C('CRM_TEMP_EN');
        }
        else if(cookie('think_language') == 'ja-jp')
        {
            $this->temp = C('CRM_TEMP_JP');
        }
        else
        {
            $this->temp = C('CRM_TEMP_CN');
        }
    }

    /*
    * 创建通知消息
    * @param int    $acts      		通知类型 1 分配客户 2 领取客户 3 转移客户 4 待跟进通知 5 新客户通知 6 新订单通知 7 未跟进通知 8 未回访通知 9 新合同通知 10 合同到期提醒 11收款审核提醒 12 发票审核提醒 13 审核结果通知 14 联系记录评论通知  101-分配线索 102-领取线索 103-转移客户 104-待跟进线索通知 105-新线索通知
    * @param int    $member_id      接收人ID
    * @param int    $sms            短信开关
    * @param int    $operate_id     操作人ID
    * @param int    $customer_id    客户ID
    * @param int    $order_id       订单ID
    * @param int    $contract_id    合同ID
    * @param int    $receipt_id     收款ID
    * @param int    $invoice_id     发票ID
    * @param int    $examine_operate_id   审核操作ID
    * @param int    $clue_id        线索ID
    * @param int    $comment_id     评论ID
    */
    public function createMessage($acts,$sms,$member_id,$company_id,$operate_id,$customer_id = 0,$order_id = 0,$contract_id = 0,$receipt_id = 0,$invoice_id = 0,$examine_operate_id = 0,$clue_id = 0,$comment_id = 0)
    {
//        获取通知配置
        $noticeConfig = getCrmDbModel('notify_config')
            ->where(['company_id'=>$company_id])
            ->find();

		if(($acts == 1 && $noticeConfig['allot_notify'] != 10) || ($acts == 2 && $noticeConfig['draw_notify'] != 10) || ($acts == 3 && $noticeConfig['transfer_notify'] != 10) || ($acts == 4 && $noticeConfig['follow_notify'] != 10) || ($acts == 5 && $noticeConfig['new_customer_notify'] != 10) || ($acts == 6 && $noticeConfig['new_order_notify'] != 10) || ($acts == 7 && $noticeConfig['nofollow_customer_notify'] != 10) || ($acts == 8 && $noticeConfig['noreturnvisit_customer_notify'] != 10) || ($acts == 9 && $noticeConfig['contract_expire_notify'] != 10) || ($acts == 10 && $noticeConfig['new_contract_notify'] != 10) || ($acts == 11 && $noticeConfig['receipt_examine_notify'] != 10) || ($acts == 12 && $noticeConfig['invoice_examine_notify'] != 10) || ($acts == 13 && $noticeConfig['examine_operate_notify'] != 10) || ($acts == 101 && $noticeConfig['allot_clue_notify'] != 10) || ($acts == 102 && $noticeConfig['draw_clue_notify'] != 10) || ($acts == 103 && $noticeConfig['transfer_clue_notify'] != 10) || ($acts == 104 && $noticeConfig['follow_clue_notify'] != 10) || ($acts == 105 && $noticeConfig['new_clue_notify'] != 10) || ($acts == 14 && $noticeConfig['follow_comment_notify'] != 10))
		{
			return false;
		}

		$first_member = M('member')->where(['company_id'=>$company_id,'type'=>1,'is_first'=>2])->getField('member_id');

		//$member = M('member')->field('company_id,name,mobile,email,open_id,type,dingtalk_id')->find($member_id);
		$member = M('member')->where(['company_id'=>$company_id,'member_id'=>$member_id])->field('company_id,name,mobile,email,open_id,type,dingtalk_id,work_user_id')->find();

		if($member)
		{
			$systemtmp = $this->getSystemTemp($acts,$company_id,$customer_id,$order_id,$contract_id,$receipt_id,$invoice_id,$examine_operate_id,$clue_id,$comment_id);

			$this->buildSystem($acts,$company_id,$systemtmp['title'],$systemtmp['content'],$systemtmp['msg_url'],$member_id,$customer_id,$order_id,$systemtmp['msg_type'],$systemtmp['category'],$contract_id,$receipt_id,$invoice_id,$clue_id,$comment_id);
		}
    }

	/*
	* 获取默认系统消息模板内容
	* acts			模板类型
	*/
	public function getSystemTemp($acts,$company_id,$customer_id = 0,$order_id = 0,$contract_id = 0,$receipt_id = 0,$invoice_id = 0,$examine_operate_id = 0,$clue_id = 0,$comment_id = 0)
	{
        $title = $content = '';

		if($comment_id)
		{
			$comment = getCrmDbModel('follow_comment')->where(['company_id'=>$company_id,'comment_id'=>$comment_id,'isvalid'=>1])->field('member_id,follow_id,content,createtime')->find();

			$followup = getCrmDbModel('followup')->where(['company_id'=>$company_id,'follow_id'=>$comment['follow_id']])->field('type,clue_id,customer_id,opportunity_id,member_id,content')->find();

			$customer_id = $followup['customer_id'] ? $followup['customer_id'] : 0;

			$clue_id = $followup['clue_id'] ? $followup['clue_id'] : 0;

			if($followup['opportunity_id'])
			{
				$customer_id = getCrmDbModel('opportunity')->where(['opportunity_id'=>$followup['opportunity_id'],'company_id'=>$company_id])->getField('customer_id');

				$opportunity['detail'] = CrmgetCrmDetailList('opportunity',$followup['opportunity_id'],$company_id,'name');
			}
		}

        if($customer_id && $customer_id > 0)
        {
	        $customer = getCrmDbModel('customer')->where(['customer_id'=>$customer_id,'company_id'=>$company_id])->field('customer_prefix,customer_no')->find();

	        $customer['detail'] = CrmgetCrmDetailList('customer',$customer_id,$company_id,'name');

	        $action = "<a href='javascript:' action='".C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Customer/detail/id/".encrypt($customer_id,'CUSTOMER')."' mini='msgCustomerDetail' data-tit='".$customer['detail']['name']."'><span style='color:#FF5722'>".$customer['detail']['name']."</span></a>";

	        $msg_url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Customer/detail/id/".encrypt($customer_id,'CUSTOMER');
        }

		if($order_id && $order_id > 0)
		{
			$order = getCrmDbModel('order')->where(['order_id'=>$order_id,'company_id'=>$company_id])->field('order_prefix,order_no')->find();

			$order['detail'] = CrmgetCrmDetailList('order',$order_id,$company_id,'name');

			$action = "<a href='javascript:' action='".C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Order/detail/id/".encrypt($order_id,'ORDER')."' mini='msgOrderDetail' data-tit='".$order['detail']['name']."'><span style='color:#FF5722'>".$order['detail']['name']."</span></a>";

			$msg_url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Order/detail/id/".encrypt($order_id,'ORDER');
		}

		if($contract_id && $contract_id > 0)
		{
			$contract = getCrmDbModel('contract')->where(['contract_id'=>$contract_id,'company_id'=>$company_id])->field('contract_prefix,contract_no')->find();

			$contract['detail'] = CrmgetCrmDetailList('contract',$contract_id,$company_id,'name');

			$action = "<a href='javascript:' action='".C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Contract/detail/id/".encrypt($contract_id,'CONTRACT')."' mini='msgContractDetail' data-tit='".$contract['detail']['name']."'><span style='color:#FF5722'>".$contract['detail']['name']."</span></a>";

			$msg_url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Contract/detail/id/".encrypt($contract_id,'CONTRACT');

			if($examine_operate_id && $examine_operate_id > 0)
			{
				$contract_operate = getCrmDbModel('contract_operate')->where(['company_id'=>$company_id,'contract_id'=>$contract_id,'operate_id'=>$examine_operate_id])->field('type,member_id,reason')->find();

				$examine_type = L('CONTRACT');

				$examine_no = $contract['contract_prefix'].$contract['contract_no'];

				$examine_operate = getFinanceExamineName($contract_operate['type'],'operate');

				$category = 'crm_contract';
			}
		}

		if($receipt_id && $receipt_id > 0)
		{
			$receipt = getCrmDbModel('receipt')->where(['receipt_id'=>$receipt_id,'company_id'=>$company_id])->field('receipt_prefix,receipt_no,receipt_money,status')->find();

			$action = "<a href='javascript:' action='".C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Receipt/detail/id/".encrypt($receipt_id,'RECEIPT')."' mini='msgCrmDetail' data-tit='".$receipt['detail']['name']."'><span style='color:#FF5722'>".$receipt['detail']['name']."</span></a>";

			$msg_url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Receipt/detail/id/".encrypt($receipt_id,'RECEIPT');

			if($examine_operate_id && $examine_operate_id > 0)
			{
				$receipt_operate = getCrmDbModel('receipt_operate')->where(['company_id'=>$company_id,'receipt_id'=>$receipt_id,'operate_id'=>$examine_operate_id])->field('type,member_id,reason')->find();

				$examine_type = L('RECEIVE_PAYMENT');

				$examine_no = $receipt['receipt_prefix'].$receipt['receipt_no'];

				$examine_operate = getFinanceExamineName($receipt_operate['type'],'operate');

				$category = 'crm_finance';
			}
		}

		if($invoice_id && $invoice_id > 0)
		{
			$invoice = getCrmDbModel('invoice')->where(['invoice_id'=>$invoice_id,'company_id'=>$company_id])->field('invoice_prefix,invoice_no,invoice_money')->find();

			$action = "<a href='javascript:' action='".C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Invoice/detail/id/".encrypt($invoice_id,'INVOICE')."' mini='msgCrmDetail' data-tit='".$invoice['detail']['name']."'><span style='color:#FF5722'>".$invoice['detail']['name']."</span></a>";

			$msg_url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Invoice/detail/id/".encrypt($invoice_id,'INVOICE');

			if($examine_operate_id && $examine_operate_id > 0)
			{
				$invoice_operate = getCrmDbModel('invoice_operate')->where(['company_id'=>$company_id,'invoice_id'=>$invoice_id,'operate_id'=>$examine_operate_id])->field('type,member_id,reason')->find();

				$examine_type = L('INVOICE');

				$examine_no = $invoice['invoice_prefix'].$invoice['invoice_no'];

				$examine_operate = getFinanceExamineName($invoice_operate['type'],'operate');

				$category = 'crm_finance';
			}
		}

		if($clue_id && $clue_id > 0)
		{
			$clue = getCrmDbModel('clue')->where(['clue_id'=>$clue_id,'company_id'=>$company_id])->field('clue_prefix,clue_no')->find();

			$clue['detail'] = CrmgetCrmDetailList('clue',$clue_id,$company_id,'name');

			$action = "<a href='javascript:' action='".C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Clue/detail/id/".encrypt($clue_id,'CLUE')."' mini='msgCrmDetail' data-tit='".$clue['detail']['name']."'><span style='color:#FF5722'>".$clue['detail']['name']."</span></a>";

			$msg_url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Clue/detail/id/".encrypt($clue_id,'CLUE');
		}

 		switch($acts)
		{
			//客户分配
			case 1:

				$title		= $this->temp['ALLOT_CUSTOMER_MSG']['title'];

				$content	= $this->temp['ALLOT_CUSTOMER_MSG']['content'];

				$title		= str_replace("{{customer.name}}","<span style='color:#128cf6'>".$customer['detail']['name']."</span>",$title);

				$content 	= str_replace("{{customer.name}}",$action,$content);

				$msg_type = 'crm_allot_customer';

				$category = 'crm_customer';

				break;

			case 2://客户领取

				$title		= $this->temp['DRAW_CUSTOMER_MSG']['title'];

				$content	= $this->temp['DRAW_CUSTOMER_MSG']['content'];

				$title		= str_replace("{{customer.name}}","<span style='color:#128cf6'>".$customer['detail']['name']."</span>",$title);

				$content 	= str_replace("{{customer.name}}",$action,$content);

				$msg_type = 'crm_draw_customer';

				$category = 'crm_customer';

				break;

			case 3://客户转移

				$title		= $this->temp['TRANSFER_CUSTOMER_MSG']['title'];

				$content	= $this->temp['TRANSFER_CUSTOMER_MSG']['content'];

				$title		= str_replace("{{customer.name}}","<span style='color:#128cf6'>".$customer['detail']['name']."</span>",$title);

				$content 	= str_replace("{{customer.name}}",$action,$content);

				$msg_type = 'crm_transfer_customer';

				$category = 'crm_customer';

				break;

			case 4://客户待跟进

				$title		= $this->temp['FOLLOW_CUSTOMER_MSG']['title'];

				$content	= $this->temp['FOLLOW_CUSTOMER_MSG']['content'];

				$title		= str_replace("{{customer.name}}","<span style='color:#128cf6'>".$customer['detail']['name']."</span>",$title);

				$content 	= str_replace("{{customer.name}}",$action,$content);

				$msg_type = 'crm_follow_customer';

				$category = 'crm_customer';

				break;

			case 5://新客户通知

				$title		= $this->temp['NEW_CUSTOMER_MSG']['title'];

				$content	= $this->temp['NEW_CUSTOMER_MSG']['content'];

				$title		= str_replace("{{customer.name}}","<span style='color:#128cf6'>".$customer['detail']['name']."</span>",$title);

				$content 	= str_replace("{{customer.name}}",$action,$content);

				$msg_type = 'crm_new_customer';

				$category = 'crm_customer';

				break;

			case 6://新订单通知

				$title		= $this->temp['NEW_ORDER_MSG']['title'];

				$content	= $this->temp['NEW_ORDER_MSG']['content'];

				$title		= str_replace("{{order.name}}","<span style='color:#128cf6'>".$order['detail']['name']."</span>",$title);

				$content 	= str_replace("{{order.name}}",$action,$content);

				$msg_type = 'crm_new_order';

				$category = 'crm_order';

				break;

			case 7://客户未跟进通知

				$title		= $this->temp['NOFOLLOW_CUSTOMER_MSG']['title'];

				$content	= $this->temp['NOFOLLOW_CUSTOMER_MSG']['content'];

				$title		= str_replace("{{customer.name}}","<span style='color:#128cf6'>".$customer['detail']['name']."</span>",$title);

				$content 	= str_replace("{{customer.name}}",$action,$content);

				$msg_type = 'crm_nofollow_customer';

				$category = 'crm_customer';

				break;

			case 8://客户未回访通知

				$title		= $this->temp['NORETURNVISIT_CUSTOMER_MSG']['title'];

				$content	= $this->temp['NORETURNVISIT_CUSTOMER_MSG']['content'];

				$title		= str_replace("{{customer.name}}","<span style='color:#128cf6'>".$customer['detail']['name']."</span>",$title);

				$content 	= str_replace("{{customer.name}}",$action,$content);

				$msg_type = 'crm_noreturnvisit_customer';

				$category = 'crm_customer';

				break;

			case 9://新合同通知

				$title		= $this->temp['NEW_CONTRACT_MSG']['title'];

				$content	= $this->temp['NEW_CONTRACT_MSG']['content'];

				$title		= str_replace("{{contract.name}}","<span style='color:#128cf6'>".$contract['detail']['name']."</span>",$title);

				$content 	= str_replace("{{contract.name}}",$action,$content);

				$msg_type = 'crm_new_contract';

				$category = 'crm_contract';

				break;

			case 10://合同到期提醒

				$title		= $this->temp['CONTRACT_EXPIRE_MSG']['title'];

				$content	= $this->temp['CONTRACT_EXPIRE_MSG']['content'];

				$title		= str_replace("{{contract.name}}","<span style='color:#128cf6'>".$contract['detail']['name']."</span>",$title);

				$content 	= str_replace("{{contract.name}}",$action,$content);

				$msg_type = 'crm_contract_expire';

				$category = 'crm_contract';

				break;

			case 11://收款审核提醒

				$title		= $this->temp['RECEIPT_EXAMINE_MSG']['title'];

				$content	= $this->temp['RECEIPT_EXAMINE_MSG']['content'];

				$title		= str_replace("{{receipt.receipt_no}}","<span style='color:#128cf6'>".$receipt['receipt_prefix'].$receipt['receipt_no']."</span>",$title);

				$content 	= str_replace("{{receipt.receipt_no}}",$action,$content);

				$msg_type = 'crm_receipt_examine';

				$category = 'crm_finance';

				break;

			case 12://发票审核提醒

				$title		= $this->temp['INVOICE_EXAMINE_MSG']['title'];

				$content	= $this->temp['INVOICE_EXAMINE_MSG']['content'];

				$title		= str_replace("{{invoice.invoice_no}}","<span style='color:#128cf6'>".$invoice['invoice_prefix'].$invoice['invoice_no']."</span>",$title);

				$content 	= str_replace("{{invoice.invoice_no}}",$action,$content);

				$msg_type = 'crm_invoice_examine';

				$category = 'crm_finance';

				break;

			case 13://审核结果通知

				$title		= $this->temp['EXAMINE_OPERATE_MSG']['title'];

				$content	= $this->temp['EXAMINE_OPERATE_MSG']['content'];

				$title		= str_replace("{{examine.type}}",$examine_type,$title);

				$title		= str_replace("{{examine.examine_no}}","<span style='color:#128cf6'>".$examine_no."</span>",$title);

				$title		= str_replace("{{examine.operate_type}}",$examine_operate,$title);

				$content 	= str_replace("{{examine.type}}",$examine_type,$content);

				$content 	= str_replace("{{examine.examine_no}}",$action,$content);

				$content 	= str_replace("{{examine.operate_type}}",$examine_operate,$content);

				$msg_type = 'crm_examine_operate';

				break;

		    //联系记录评论通知
		    case 14:

			    $title		= $this->temp['FOLLOW_COMMENT_MSG']['title'];

			    $content	= $this->temp['FOLLOW_COMMENT_MSG']['content'];

			    if($followup['type'] == 'clue')
			    {
				    $name = $clue['detail']['name'];
			    }
			    elseif($followup['type'] == 'customer')
			    {
				    $name = $customer['detail']['name'];
			    }
			    elseif($followup['type'] == 'opportunity')
			    {
				    $name = $opportunity['detail']['name'];

				    //$action = "<a href='javascript:' action='".C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Opportunity/detail/id/".encrypt($followup['opportunity_id'],'OPPORTUNITY')."' mini='msgCrmDetail' data-tit='".$opportunity['detail']['name']."'><span style='color:#FF5722'>".$opportunity['detail']['name']."</span></a>";

				    $action = "<a href='javascript:' action='".C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm/Customer/detail/id/".encrypt($customer_id,'CUSTOMER')."' mini='msgCrmDetail' data-tit='".$opportunity['detail']['name']."'><span style='color:#FF5722'>".$opportunity['detail']['name']."</span></a>";
			    }

			    $title		= str_replace("{{follow.type}}",L(strtoupper($followup['type'])),$title);

			    $title		= str_replace("{{follow.name}}","<span style='color:#128cf6'>".$name."</span>",$title);

			    $content 	= str_replace("{{follow.type}}",L(strtoupper($followup['type'])),$content);

			    $content 	= str_replace("{{follow.name}}",$action,$content);

			    $msg_type = 'crm_follow_comment';

			    $category = 'crm_comment';

			    break;

		    //线索分配
		    case 101:

			    $title		= $this->temp['ALLOT_CLUE_MSG']['title'];

			    $content	= $this->temp['ALLOT_CLUE_MSG']['content'];

			    $title		= str_replace("{{clue.name}}","<span style='color:#128cf6'>".$clue['detail']['name']."</span>",$title);

			    $content 	= str_replace("{{clue.name}}",$action,$content);

			    $msg_type = 'crm_allot_clue';

			    $category = 'crm_clue';

			    break;

		    case 102://线索领取

			    $title		= $this->temp['DRAW_CLUE_MSG']['title'];

			    $content	= $this->temp['DRAW_CLUE_MSG']['content'];

			    $title		= str_replace("{{clue.name}}","<span style='color:#128cf6'>".$clue['detail']['name']."</span>",$title);

			    $content 	= str_replace("{{clue.name}}",$action,$content);

			    $msg_type = 'crm_draw_clue';

			    $category = 'crm_clue';

			    break;

		    case 103://客户转移

			    $title		= $this->temp['TRANSFER_CLUE_MSG']['title'];

			    $content	= $this->temp['TRANSFER_CLUE_MSG']['content'];

			    $title		= str_replace("{{clue.name}}","<span style='color:#128cf6'>".$clue['detail']['name']."</span>",$title);

			    $content 	= str_replace("{{clue.name}}",$action,$content);

			    $msg_type = 'crm_transfer_clue';

			    $category = 'crm_clue';

			    break;

		    case 104://客户待跟进

			    $title		= $this->temp['FOLLOW_CLUE_MSG']['title'];

			    $content	= $this->temp['FOLLOW_CLUE_MSG']['content'];

			    $title		= str_replace("{{clue.name}}","<span style='color:#128cf6'>".$clue['detail']['name']."</span>",$title);

			    $content 	= str_replace("{{clue.name}}",$action,$content);

			    $msg_type = 'crm_follow_clue';

			    $category = 'crm_clue';

			    break;

		    case 105://新客户通知

			    $title		= $this->temp['NEW_CLUE_MSG']['title'];

			    $content	= $this->temp['NEW_CLUE_MSG']['content'];

			    $title		= str_replace("{{clue.name}}","<span style='color:#128cf6'>".$clue['detail']['name']."</span>",$title);

			    $content 	= str_replace("{{clue.name}}",$action,$content);

			    $msg_type = 'crm_new_clue';

			    $category = 'crm_clue';

			    break;
		}

		return ['title'=>$title,'content'=>$content,'msg_url'=>$msg_url,'msg_type'=>$msg_type,'category'=>$category];
	}

	public function buildSystem($acts,$company_id,$title,$content,$msg_url,$member_id,$customer_id = 0,$order_id = 0,$msg_type = '',$category = '',$contract_id = 0,$receipt_id = 0,$invoice_id = 0,$clue_id = 0,$comment_id = 0)
	{
		$msg = [];

		$msg['company_id'] = $company_id;

		$msg['category'] = $category;

		$msg['msg_title'] = $title;

		$msg['msg_content'] = $content;

		$msg['msg_url'] = $msg_url;

		$msg['ticket_id'] = 0;

		$msg['customer_id'] = $customer_id;

		$msg['order_id'] = $order_id;

		$msg['contract_id'] = $contract_id;

		$msg['receipt_id'] = $receipt_id;

		$msg['invoice_id'] = $invoice_id;

		$msg['clue_id'] = $clue_id;

		$msg['comment_id'] = $comment_id;

		$msg['msg_type'] = $msg_type;

		$msg['recipient'] = 1;

		$msg['recipient_id'] = $member_id ? $member_id : 0;

		$msg['msg_system'] = 'crm';

		$msg['create_time'] = time();

		$id = M('SystemMessage')->add($msg);

		$feelec_merchant_id = M('company')->where(['company_id'=>$company_id])->getField('feelec_unionid');

		if($feelec_merchant_id)
		{
			$title = strip_tags($msg['msg_title']);

			$title = explode('：',$title,2);

			//	    向客户端推送消息
			$message = ['category'=>$category,'id'=>$id,'title'=>$msg['msg_title'],'msg_title'=>$title[0],'msg_content'=>$title[1],'url'=>$msg_url];
		}
		else
		{
			//	    向客户端推送消息
			$message = ['category'=>$category,'id'=>$id,'title'=>$msg['msg_title'],'url'=>$msg_url];
		}

		$result = D('PushMessage')->setUser($member_id)->setContent($message)->setPushType('systemMsg')->push();

//		推送成功 - 改变提醒状态
		if($result == 'Success')
		{
			D('SystemMessage')->where(['msg_id'=>$id])->setField('is_remind',2);
		}
	}


	/*
	* 获取通知模板链接
	* @param int    $company_id     公司ID
	* @param int    $customer_id    客户ID
	* @param int    $order_id    	订单ID
	* @param int    $contract_id    合同ID
	* @param int    $msg_type    	消息类型
	* @param string $template_type  模板类型 WeChat 微信模板，DingTalk 钉钉模板
	* @return string $url           模板链接
	*/
    public function getTemplateUrl($company_id,$template_type,$msg_type,$customer_id = 0,$order_id = 0,$contract_id = 0,$receipt_id = 0,$invoice_id = 0,$clue_id = 0)
    {
        $url = '';

        $login_token = M('Company')->where(['company_id'=>$company_id])->getField('login_token');

        if($template_type == 'WeChat')
        {
			if($msg_type == 'customer')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/CrmWeixin/Customer/detail.html?id=".encrypt($customer_id,'CUSTOMER')."&login_token=".$login_token;
			}
			elseif($msg_type == 'order')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/CrmWeixin/Order/detail.html?id=".encrypt($order_id,'ORDER')."&login_token=".$login_token;
			}
			elseif($msg_type == 'contract')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/CrmWeixin/Contract/detail.html?id=".encrypt($contract_id,'CONTRACT')."&login_token=".$login_token;
			}
			elseif($msg_type == 'receipt')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/CrmWeixin/Receipt/detail.html?id=".encrypt($receipt_id,'RECEIPT')."&login_token=".$login_token;
			}
			elseif($msg_type == 'invoice')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/CrmWeixin/Invoice/detail.html?id=".encrypt($invoice_id,'INVOICE')."&login_token=".$login_token;
			}
			elseif($msg_type == 'clue')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/CrmWeixin/Clue/detail.html?id=".encrypt($clue_id,'CLUE')."&login_token=".$login_token;
			}
        }

        if($template_type == 'DingTalk')
        {
			if($msg_type == 'customer')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/Ding/loginByDingTalk?token={$login_token}&_customerId=".encrypt($customer_id,'CUSTOMER');
			}
			elseif($msg_type == 'order')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/Ding/loginByDingTalk?token={$login_token}&_orderId=".encrypt($order_id,'ORDER');
			}
			elseif($msg_type == 'contract')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/Ding/loginByDingTalk?token={$login_token}&_contractId=".encrypt($contract_id,'CONTRACT');
			}
			elseif($msg_type == 'receipt')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/Ding/loginByDingTalk?token={$login_token}&_receiptId=".encrypt($receipt_id,'RECEIPT');
			}
			elseif($msg_type == 'invoice')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/Ding/loginByDingTalk?token={$login_token}&_invoiceId=".encrypt($invoice_id,'INVOICE');
			}
			elseif($msg_type == 'clue')
			{
				$url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/Ding/loginByDingTalk?token={$login_token}&_clueId=".encrypt($clue_id,'CLUE');
			}
        }

	    if($template_type == 'WorkWeixin')
	    {
		    if($msg_type == 'customer')
		    {
			    $url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/WorkWeixin/loginByWorkWeixin?token={$login_token}&_customerId=".encrypt($customer_id,'CUSTOMER');
		    }
		    elseif($msg_type == 'order')
		    {
			    $url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/WorkWeixin/loginByWorkWeixin?token={$login_token}&_orderId=".encrypt($order_id,'ORDER');
		    }
		    elseif($msg_type == 'contract')
		    {
			    $url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/WorkWeixin/loginByWorkWeixin?token={$login_token}&_contractId=".encrypt($contract_id,'CONTRACT');
		    }
		    elseif($msg_type == 'receipt')
		    {
			    $url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/WorkWeixin/loginByWorkWeixin?token={$login_token}&_receiptId=".encrypt($receipt_id,'RECEIPT');
		    }
		    elseif($msg_type == 'invoice')
		    {
			    $url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/WorkWeixin/loginByWorkWeixin?token={$login_token}&_invoiceId=".encrypt($invoice_id,'INVOICE');
		    }
		    elseif($msg_type == 'clue')
		    {
			    $url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Api/WorkWeixin/loginByWorkWeixin?token={$login_token}&_clueId=".encrypt($clue_id,'CLUE');
		    }
	    }

        return $url;
    }
}

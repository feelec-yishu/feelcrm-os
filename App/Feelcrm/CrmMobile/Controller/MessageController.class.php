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

namespace CrmMobile\Controller;

use Think\Controller;

class MessageController extends Controller
{
	protected $recipient_id = '';

	protected $recipient = '';

	public function _initialize()
	{
		if(!session('?mobile'))
		{
			header('Location: ' . U('Login/index'));

			exit();
		}
		else
        {
            $this->assign('mobile',session('mobile'));

            $this->assign('groupSystemAuth',session('GROUP_SYSTEM_AUTH_'.session('mobile')['company_id'].'_'.session('mobile')['member_id']));

            $this->assign('lang',strtolower(cookie('think_language')));

            $systemName = I('get.system') ? I('get.system') : MODULE_NAME;

            $this->assign('system',$systemName);

			$this->recipient = 1;

			$this->recipient_id = session('mobile')['member_id'];
		}
	}



	public function getMessage($request = '')
	{
        $field = ['company_id'=>session('mobile')['company_id'],'recipient_id'=>$this->recipient_id,'recipient'=>$this->recipient,'msg_system'=>'crm'];

        if($request == 'flow')
        {
            $count = D('SystemMessage')->where($field)->count();

            $Page = new \Think\Page($count, 8);

            $message = D('SystemMessage')->where($field)->limit($Page->firstRow, $Page->listRows)->order('msg_id desc')->select();

            foreach($message as &$v)
            {
				if($v['category'] == 'crm_customer')
	            {
		            $v['msg_name'] = L('CRM_CUSTOMER_NOTIFICATION');

					$v['title_name'] = "";

					$v['msg_item'] = "";

					if($v['customer_id'])
					{
						$customer = getCrmDbModel('customer')->where(['company_id'=>session('mobile')['company_id'],'customer_id'=>$v['customer_id']])->find();

						$customer_detail = CrmgetCrmDetailList('customer',$v['customer_id'],session('mobile')['company_id'],'name');

						$v['title_name'] = L('CLIENT_NAME').'：'.$customer_detail['name'];

						$member_name = M('member')->where(['company_id'=>session('mobile')['company_id'],'member_id'=>$customer['member_id']])->getField('name');

						$create_name = M('member')->where(['company_id'=>session('mobile')['company_id'],'member_id'=>$customer['creater_id']])->getField('name');

						$create_name = getCustomerCreateName($customer['creater_id'],$create_name);

						$v['msg_item'] = "<li>".L('LEADER')."：".$member_name."</li>";

						$v['msg_item'] .= "<li>".L('FOUNDER')."：".$create_name."</li>";
					}
	            }
	            else if($v['category'] == 'crm_order')
	            {
		            $v['msg_name'] = L('CRM_ORDER_NOTIFICATION');

					$v['title_name'] = "";

					$v['msg_item'] = "";

					if($v['order_id'])
					{
						$order = getCrmDbModel('order')->where(['company_id'=>session('mobile')['company_id'],'order_id'=>$v['order_id']])->find();

						$order_detail = CrmgetCrmDetailList('order',$v['order_id'],session('mobile')['company_id'],'name');

						$v['title_name'] = L('ORDER_NAME').'：'.$order_detail['name'];

						$member_name = M('member')->where(['company_id'=>session('mobile')['company_id'],'member_id'=>$order['member_id']])->getField('name');

						$create_name = M('member')->where(['company_id'=>session('mobile')['company_id'],'member_id'=>$order['creater_id']])->getField('name');

						$v['msg_item'] = "<li>".L('LEADER')."：".$member_name."</li>";

						$v['msg_item'] .= "<li>".L('FOUNDER')."：".$create_name."</li>";
					}
	            }
				else if($v['category'] == 'crm_contract')
				{
					$v['msg_name'] = L('CRM_CONTRACT_NOTIFICATION');

					$v['title_name'] = "";

					$v['msg_item'] = "";

					if($v['contract_id'])
					{
						$contract = getCrmDbModel('contract')->where(['company_id'=>session('mobile')['company_id'],'contract_id'=>$v['contract_id']])->find();

						$contract_detail = CrmgetCrmDetailList('contract',$v['contract_id'],session('mobile')['company_id'],'name');

						$v['title_name'] = L('CONTRACT_NAME').'：'.$contract_detail['name'];

						$member_name = M('member')->where(['company_id'=>session('mobile')['company_id'],'member_id'=>$contract['member_id']])->getField('name');

						$create_name = M('member')->where(['company_id'=>session('mobile')['company_id'],'member_id'=>$contract['creater_id']])->getField('name');

						$v['msg_item'] = "<li>".L('LEADER')."：".$member_name."</li>";

						$v['msg_item'] .= "<li>".L('FOUNDER')."：".$create_name."</li>";
					}
				}
				else if($v['category'] == 'crm_finance')
				{
					$v['title_name'] = $v['msg_item'] = "";

					if($v['receipt_id'] > 0)
					{
						$v['msg_name'] = L('CRM_RECEIPT_NOTIFICATION');

						$receipt = getCrmDbModel('receipt')->where(['company_id'=>session('mobile')['company_id'],'receipt_id'=>$v['receipt_id']])->find();

						$v['title_name'] = L('COLLECTION_NUMBER').'：'.$receipt['receipt_prefix'].$receipt['receipt_no'];

						$v['msg_item'] = "<li>".L('AMOUNT_RECEIVED')."：".$receipt['receipt_money']."</li>";

						$v['msg_item'] .= "<li>".L('COLLECTION_TIME')."：".getDates($receipt['receipt_time'],1)."</li>";
					}

					if($v['invoice_id'] > 0)
					{
						$v['msg_name'] = L('CRM_INVOICE_NOTIFICATION');

						$invoice = getCrmDbModel('invoice')->where(['company_id'=>session('mobile')['company_id'],'invoice_id'=>$v['invoice_id']])->find();

						$v['title_name'] = L('INVOICE_NUMBER').'：'.$invoice['invoice_prefix'].$invoice['invoice_no'];

						$v['msg_item'] = "<li>".L('INVOICE_AMOUNT')."：".$invoice['invoice_money']."</li>";

						$v['msg_item'] .= "<li>".L('BILLING_TIME')."：".getDates($invoice['invoice_time'],1)."</li>";
					}
				}
				else if($v['category'] == 'crm_clue')
				{
					$v['msg_name'] = L('CRM_CLUE_NOTIFICATION');

					$v['title_name'] = "";

					$v['msg_item'] = "";

					if($v['clue_id'])
					{
						$clue = getCrmDbModel('clue')->where(['company_id'=>session('mobile')['company_id'],'clue_id'=>$v['clue_id']])->find();

						$clue_detail = CrmgetCrmDetailList('clue',$v['clue_id'],session('mobile')['company_id'],'name');

						$v['title_name'] = L('CLUE_NAME').'：'.$clue_detail['name'];

						$member_name = M('member')->where(['company_id'=>session('mobile')['company_id'],'member_id'=>$clue['member_id']])->getField('name');

						$create_name = M('member')->where(['company_id'=>session('mobile')['company_id'],'member_id'=>$clue['creater_id']])->getField('name');

						$create_name = getCustomerCreateName($clue['creater_id'],$create_name);

						$v['msg_item'] = "<li>".L('LEADER')."：".$member_name."</li>";

						$v['msg_item'] .= "<li>".L('FOUNDER')."：".$create_name."</li>";
					}
				}
				else if($v['category'] == 'crm_comment')
				{
					$v['msg_name'] = L('CONTACT_RECORD_COMMENT_NOTIFICATION');

					$comment = getCrmDbModel('follow_comment')->where(['company_id'=>session('mobile')['company_id'],'comment_id'=>$v['comment_id'],'isvalid'=>1])->field('member_id,follow_id,content,createtime')->find();

					$followup = getCrmDbModel('followup')->where(['company_id'=>session('mobile')['company_id'],'follow_id'=>$comment['follow_id']])->field('type,clue_id,customer_id,opportunity_id,member_id,content')->find();

					$customer_id = $followup['customer_id'] ? $followup['customer_id'] : 0;

					$clue_id = $followup['clue_id'] ? $followup['clue_id'] : 0;

					if($followup['opportunity_id'])
					{
						$customer_id = getCrmDbModel('opportunity')->where(['opportunity_id'=>$followup['opportunity_id'],'company_id'=>session('mobile')['company_id']])->getField('customer_id');
					}

					if($followup['type'] == 'clue')
					{
						$clue = CrmgetCrmDetailList('clue',$clue_id,session('mobile')['company_id'],'name');

						$name = $clue['name'];
					}
					elseif($followup['type'] == 'customer')
					{
						$customer = CrmgetCrmDetailList('customer',$customer_id,session('mobile')['company_id'],'name');

						$name = $customer['name'];
					}
					elseif($followup['type'] == 'opportunity')
					{
						$opportunity = CrmgetCrmDetailList('opportunity',$followup['opportunity_id'],session('mobile')['company_id'],'name');

						$name = $opportunity['name'];
					}

					$v['title_name'] = L('CONTACT_PERSON').'：'.L(strtoupper($followup['type'])); //联系对象

					$v['msg_item'] = "<li>".L(strtoupper($followup['type'])).L('NAME2')."：".$name."</li>"; //联系对象名称

					//联系记录
					$v['msg_item'] .= "<li>".L('CONTACT_RECORD')."：".mb_substr(strip_tags($followup['content']),0,20)."</li>";

					//评论内容
					$v['msg_item'] .= "<li>".L('COMMENT_CONTENT')."：".mb_substr(strip_tags($comment['content']),0,20)."</li>";
				}

	            $v['msg_content'] = strip_tags($v['msg_content']);

                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }

            $this->ajaxReturn(['data'=>$message,'pages'=>ceil($count/8)]);
        }
        else
        {
            unset($field['msg_system']);

            $unReadNumber = M('system_message')->where(array_merge($field,['read_status'=>1]))
                ->field("sum(case msg_system when 'ticket' then 1 else 0 end) ticket,sum(case msg_system when 'crm' then 1 else 0 end) crm")->find();

            $this->assign('unReadNumber',$unReadNumber);

	        $this->assign('unReadMessageNum',$unReadNumber['crm']);
        }

        $this->display();
	}



    public function detail($id = 0)
    {
        $field = ['msg_id'=>$id,'company_id'=>session('mobile')['company_id'],'recipient_id'=>$this->recipient_id,'recipient'=>$this->recipient,'msg_system'=>'crm'];

        $detail = M('system_message')->where($field)->find();

        if($detail)
        {
            M('system_message')->where($field)->save(['read_status'=>2,'is_remind'=>2]);

			$detail['msg_item'] = '';

			if($detail['category'] == 'crm_customer')
			{
				if($detail['customer_id'])
				{
					$customer = getCrmDbModel('customer')->where(['company_id'=>session('mobile')['company_id'],'customer_id'=>$detail['customer_id']])->find();

					$customer_detail = CrmgetCrmDetailList('customer',$detail['customer_id'],session('mobile')['company_id'],'name');

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('CLIENT_NAME').'</span><span>'.$customer_detail['name'].'</span></div>';

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('CUSTOMER_NUMBER').'</span><span>'.$customer['customer_prefix'].$customer['customer_no'].'</span></div>';
				}
			}
			else if($detail['category'] == 'crm_order')
			{

				if($detail['order_id'])
				{
					$order = getCrmDbModel('order')->where(['company_id'=>session('mobile')['company_id'],'order_id'=>$detail['order_id']])->find();

					$order_detail = CrmgetCrmDetailList('order',$detail['order_id'],session('mobile')['company_id'],'name');

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('ORDER_NAME').'</span><span>'.$order_detail['name'].'</span></div>';

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('ORDER_NUM').'</span><span>'.$order['order_prefix'].$order['order_no'].'</span></div>';
				}
			}
			else if($detail['category'] == 'crm_contract')
			{
				if($detail['contract_id'])
				{
					$contract = getCrmDbModel('contract')->where(['company_id'=>session('mobile')['company_id'],'contract_id'=>$detail['contract_id']])->find();

					$contract_detail = CrmgetCrmDetailList('contract',$detail['contract_id'],session('mobile')['company_id'],'name');

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('CONTRACT_NAME').'</span><span>'.$contract_detail['name'].'</span></div>';

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('CONTRACT_NO').'</span><span>'.$contract['contract_prefix'].$contract['contract_no'].'</span></div>';
				}
			}
			else if($detail['category'] == 'crm_finance')
			{
				if($detail['receipt_id'] > 0)
				{
					$receipt = getCrmDbModel('receipt')->where(['company_id'=>session('mobile')['company_id'],'receipt_id'=>$detail['receipt_id']])->find();

					$detail['msg_item'] = '<div class="message-detail-item"><span>'.L('COLLECTION_NUMBER').'</span><span>'.$receipt['receipt_prefix'].$receipt['receipt_no'].'</span></div>';

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('AMOUNT_RECEIVED').'</span><span>'.$receipt['receipt_money'].'</span></div>';

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('COLLECTION_TIME').'</span><span>'.getDates($receipt['receipt_time'],1).'</span></div>';
				}

				if($detail['invoice_id'] > 0)
				{
					$invoice = getCrmDbModel('invoice')->where(['company_id'=>session('mobile')['company_id'],'invoice_id'=>$detail['invoice_id']])->find();

					$detail['msg_item'] = '<div class="message-detail-item"><span>'.L('INVOICE_NUMBER').'</span><span>'.$invoice['invoice_prefix'].$invoice['invoice_no'].'</span></div>';

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('INVOICE_AMOUNT').'</span><span>'.$invoice['invoice_money'].'</span></div>';

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('BILLING_TIME').'</span><span>'.getDates($invoice['invoice_time'],1).'</span></div>';
				}
			}
			else if($detail['category'] == 'crm_clue')
			{
				if($detail['clue_id'])
				{
					$clue = getCrmDbModel('clue')->where(['company_id'=>session('mobile')['company_id'],'clue_id'=>$detail['clue_id']])->find();

					$clue_detail = CrmgetCrmDetailList('clue',$detail['clue_id'],session('mobile')['company_id'],'name');

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('CLUE_NAME').'</span><span>'.$clue_detail['name'].'</span></div>';

					$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('CLUE_NO').'</span><span>'.$clue['clue_prefix'].$clue['clue_no'].'</span></div>';
				}
			}
			else if($detail['category'] == 'crm_comment')
			{
				$comment = getCrmDbModel('follow_comment')->where(['company_id'=>session('mobile')['company_id'],'comment_id'=>$detail['comment_id'],'isvalid'=>1])->field('member_id,follow_id,content,createtime')->find();

				$followup = getCrmDbModel('followup')->where(['company_id'=>session('mobile')['company_id'],'follow_id'=>$comment['follow_id']])->field('type,clue_id,customer_id,opportunity_id,member_id,content')->find();

				$customer_id = $followup['customer_id'] ? $followup['customer_id'] : 0;

				$clue_id = $followup['clue_id'] ? $followup['clue_id'] : 0;

				if($followup['opportunity_id'])
				{
					$customer_id = getCrmDbModel('opportunity')->where(['opportunity_id'=>$followup['opportunity_id'],'company_id'=>session('mobile')['company_id']])->getField('customer_id');
				}

				if($followup['type'] == 'clue')
				{
					$clue = CrmgetCrmDetailList('clue',$clue_id,session('mobile')['company_id'],'name');

					$name = $clue['name'];
				}
				elseif($followup['type'] == 'customer')
				{
					$customer = CrmgetCrmDetailList('customer',$customer_id,session('mobile')['company_id'],'name');

					$name = $customer['name'];
				}
				elseif($followup['type'] == 'opportunity')
				{
					$opportunity = CrmgetCrmDetailList('opportunity',$followup['opportunity_id'],session('mobile')['company_id'],'name');

					$name = $opportunity['name'];
				}

				$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('CONTACT_PERSON').'</span><span>'.L(strtoupper($followup['type'])).'</span></div>'; //联系对象

				//联系对象名称
				$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L(strtoupper($followup['type'])).L('NAME2').'</span><span>'.$name.'</span></div>';

				//联系记录
				$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('CONTACT_RECORD').'</span><span>'.mb_substr(strip_tags($followup['content']),0,20).'</span></div>';

				//评论内容
				$detail['msg_item'] .= '<div class="message-detail-item"><span>'.L('COMMENT_CONTENT').'</span><span>'.mb_substr(strip_tags($comment['content']),0,20).'</span></div>';
			}

			$detail['msg_content'] = str_replace(C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/Crm",C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/CrmMobile",$detail['msg_content']);

            $this->assign('detail',$detail);

            $this->display();
        }
        else
        {
            $this->error(L('MSG_NOT'));
        }
    }



	public function delete()
	{
		$id = I('post.id');

		if($id > 0)
		{
			$result = D('SystemMessage')->deleteMessageByMobile(session('mobile')['company_id'],$this->recipient_id,$this->recipient);

            if($result)
            {
                $result = ['errcode'=>0,'msg'=>L('DELETE_SUCCESS'),'url'=>U('getMessage')];
            }
            else
            {
                $result = ['errcode'=>1,'msg'=>L('DELETE_FAILED')];
            }
		}
		else
		{
            $result = ['errcode'=>1,'msg'=>L('ILLEGAL_ACCESS')];
		}

        $this->ajaxReturn($result);
	}



	public function updateMessageStatus()
	{
        $type = I('post.type') ? I('post.type') : I('get.type');

		if(in_array($type,['delete','read']))
		{
            $where = ['company_id'=>session('mobile')['company_id'],'recipient_id'=>$this->recipient_id,'recipient'=>$this->recipient,'msg_system'=>'crm'];

            if($type == 'read')
            {
                $result = M('system_message')->where($where)->setField('read_status',2);
            }
            else
            {
                $result = M('system_message')->where($where)->delete();
            }

            if($result !== false)
            {
                $result = ['errcode'=>0,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('getMessage',['types'=>$type])];
            }
		}
		else
		{
            $result = ['errcode'=>1,'msg'=>L('ILLEGAL_ACCESS')];
		}

		$this->ajaxReturn($result);
	}
}

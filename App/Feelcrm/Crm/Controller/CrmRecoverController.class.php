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

namespace Crm\Controller;

use Think\Controller;

use Think\Log;

use Think\Cache\Driver\Redis;

class CrmRecoverController extends Controller
{
	public function index()
	{
		if(APP_MODE != 'crm')
		{
			header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码

			$this->display("Public:404");die;
		}

		//set_time_limit(0);

		//ignore_user_abort(true);

		//while(1)
		//{

		$redis = new Redis();

		if($redis->lLen('companyId') == 0)
		{
			$this->getUnseenCompanys(); //当队列长度为0时，读取未读邮件，并入队
		}

		while($redis->lLen('companyId')){

			$company_id = $redis->lpop('companyId');

			$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$company_id])->find();

			$crmsite = unserialize($crmsite['value']);

			if(!$crmsite['noFollowup'])
			{
				$crmsite['noFollowup'] = 3;
			}
			if(!$crmsite['noReturnvisit'])
			{
				$crmsite['noReturnvisit'] = 30;
			}
			if(!$crmsite['workStartTime'])
			{
				$crmsite['workStartTime'] = '09:00:00';
			}
			if(!$crmsite['workEndTime'])
			{
				$crmsite['workEndTime'] = '18:00:00';
			}
			if(!$crmsite['noFollowupDay'])
			{
				$crmsite['noFollowupDay'] = '7';
			}
			if(!$crmsite['noSignOrderDay'])
			{
				$crmsite['noSignOrderDay'] = '60';
			}

			$week_ago_time = strtotime(date('Y-m-d',strtotime('-7 days')));

			$workStartTime = strtotime(date('Y-m-d ',time()).$crmsite['workStartTime']);

			$sms = M('Sms')->where(['company_id'=>$company_id])->getField('is_open');

			$customer = getCrmDbModel('customer')->where(['company_id'=>$company_id,'isvalid'=>1,'member_id'=>['gt',0]])->field('customer_id,createtime,nextcontacttime,is_trade,member_id,first_contact_id,lastfollowtime')->select();

			$noticeConfig = getCrmDbModel('notify_config')->where(['company_id'=>$company_id])->field('nofollow_customer_notify,nofollow_customer_notifier,noreturnvisit_customer_notify,noreturnvisit_customer_notifier,contract_expire_notify,contract_expire_notifier')->find();

			$first_member = M('member')->where(['company_id'=>$company_id,'type'=>1,'is_first'=>2])->getField('member_id');

			$nofollow_customer_notifier = $noticeConfig['nofollow_customer_notifier'];

			$noreturnvisit_customer_notifier = $noticeConfig['noreturnvisit_customer_notifier'];

			$clue = getCrmDbModel('clue')->where(['company_id'=>$company_id,'isvalid'=>1,'member_id'=>['gt',0]])->field('clue_id,createtime,nextcontacttime,member_id')->select();

			foreach($clue as $clueK => $clueV)
			{
				//线索待跟进通知
				if($clueV['nextcontacttime'] - time() < 1800 && $clueV['nextcontacttime'] > 0)
				{
					$map = ['clue_id'=>$clueV['clue_id'],'member_id'=>$clueV['member_id'],'contact_time'=>$clueV['nextcontacttime']];

					$follow_control = getCrmDbModel('clue_follow_control')->where($map)->getField('id');

					if(!$follow_control)
					{
						D('CrmCreateMessage')->createMessage(104,$sms,$clueV['member_id'],$company_id,-1,0,0,0,0,0,0,$clueV['clue_id']);

						getCrmDbModel('clue_follow_control')->add($map);
					}
				}
			}

			foreach($customer as $custK => $custV)
			{
				//客户待跟进通知
				//G('begin');
				if($custV['nextcontacttime'] - time() < 1800 && $custV['nextcontacttime'] > 0)
				{
					$map = ['customer_id'=>$custV['customer_id'],'member_id'=>$custV['member_id'],'contact_time'=>$custV['nextcontacttime']];

					$follow_control = getCrmDbModel('customer_follow_control')->where($map)->getField('id');

					if(!$follow_control)
					{
						D('CrmCreateMessage')->createMessage(4,$sms,$custV['member_id'],$company_id,-1,$custV['customer_id']);

						getCrmDbModel('customer_follow_control')->add($map);
					}
				}
				// G('end');
				// file_put_contents("./Feeldesk/Runtime/Logs/Crm/recover.txt", "Time1====".G('begin','end').'s'."----".date('Y-m-d H:i:s')."-- \r\n\n", FILE_APPEND);

				// G('begin');
				$where = ['customer_id'=>$custV['customer_id'],'member_id'=>$custV['member_id'],'company_id'=>$company_id,'isvalid'=>1];

				/*$follow = getCrmDbModel('followup')->where($where)->field('follow_id,createtime')->order('createtime desc')->find();

				$opportunity = getCrmDbModel('opportunity')->where($where)->field('opportunity_id')->select();

				$opportunity_arr = array_column($opportunity,'opportunity_id');

				if($opportunity_arr)
				{
					$follow_opportunity = getCrmDbModel('followup')->where(['opportunity_id'=>['in',implode(',',$opportunity_arr)],'member_id'=>$custV['member_id'],'company_id'=>$company_id,'isvalid'=>1])->field('follow_id,createtime')->order('createtime desc')->find();

					if(!$follow && $follow_opportunity)
					{
						$follow = $follow_opportunity;
					}
					elseif($follow && $follow_opportunity && $follow_opportunity['createtime'] > $follow['createtime'])
					{
						$follow = $follow_opportunity;
					}
				}*/

				$customer_log = getCrmDbModel('crmlog')->where(['customer_id'=>$custV['customer_id'],'company_id'=>$company_id,'log_type'=>'customer','operate_type'=>['in','5,6,8'],'target_id'=>$custV['member_id']])->field('log_id,createtime')->order('createtime desc')->find();

				$follow_space = 0;

				$contract_space = 0;

				if($customer_log)
				{
					$log_space = floor((time()-$customer_log['createtime'])/86400); //省略小数
				}

				/*if($follow)
				{
					$follow_space = floor((time()-$follow['createtime'])/86400); //省略小数

					if($customer_log && $log_space < $follow_space)
					{
						$follow_space = $log_space;
					}
				}*/
				if($custV['lastfollowtime'])
				{
					$follow_space = floor((time()-$custV['lastfollowtime'])/86400); //省略小数

					if($customer_log && $log_space < $follow_space)
					{
						$follow_space = $log_space;
					}
				}
				elseif($customer_log)
				{
					$follow_space = floor((time()-$customer_log['createtime'])/86400); //省略小数
				}
				else
				{
					$follow_space = floor((time()-$custV['createtime'])/86400); //省略小数
				}

				// G('end');
				// file_put_contents("./Feeldesk/Runtime/Logs/Crm/recover.txt", "Time2====".G('begin','end').'s'."----".date('Y-m-d H:i:s')."-- \r\n\n", FILE_APPEND);

				// G('begin');
				//客户未跟进通知
				if((int)$follow_space >= (int)$crmsite['noFollowup'] && $custV['is_trade'] == 0 && $noticeConfig['nofollow_customer_notify'] == 10)
				{
					$noFollowmap = ['customer_id'=>$custV['customer_id'],'company_id'=>$company_id,'send_time'=>strtotime(date("Y-m-d"),time())];

					$nofollow_control = getCrmDbModel('customer_nofollow_control')->where($noFollowmap)->getField('id');

					if(!$nofollow_control && time() >= $workStartTime)
					{
						# 查询已通知且未查看的数量
						$message_where_count = ['company_id'=>$company_id,'customer_id'=>$custV['customer_id'],'recipient_id'=>$custV['member_id'],'recipient'=>1,'category'=>'crm_customer','msg_type'=>'crm_nofollow_customer','msg_system'=>'crm','read_status'=>1,'create_time'=>[['egt',$week_ago_time],['elt',time()]]];

						$message_where_delete = ['company_id'=>$company_id,'customer_id'=>$custV['customer_id'],'recipient_id'=>$custV['member_id'],'recipient'=>1,'category'=>'crm_customer','msg_type'=>'crm_nofollow_customer','msg_system'=>'crm','read_status'=>1,'create_time'=>['elt',$week_ago_time]];

						$count_message = M('system_message')->where($message_where_count)->count();

						# 同一用户有超过7条同一客户未查看通知，删除旧数据
						if($count_message >= 7)
						{
							M('system_message')->where($message_where_delete)->delete();
						}

						D('CrmCreateMessage')->createMessage(7,$sms,$custV['member_id'],$company_id,-1,$custV['customer_id']);

						if($nofollow_customer_notifier)
						{
							$nofollow_customer_notifier_arr = explode(',',$nofollow_customer_notifier);

							foreach($nofollow_customer_notifier_arr as $key=>$val)
							{
								if($val && $val != $custV['member_id'])
								{
									# 重新赋值通知人，删除旧数据
									$message_where_count['recipient_id'] = $message_where_delete['recipient_id'] = $val;

									$count_message = M('system_message')->where($message_where_count)->count();

									if($count_message >= 7)
									{
										M('system_message')->where($message_where_delete)->delete();
									}

									D('CrmCreateMessage')->createMessage(7,$sms,$val,$company_id,-1,$custV['customer_id']);
								}
							}
						}
						elseif($first_member)
						{
							# 重新赋值通知人，删除旧数据
							$message_where_count['recipient_id'] = $message_where_delete['recipient_id'] = $first_member;

							$count_message = M('system_message')->where($message_where_count)->count();

							if($count_message >= 7)
							{
								M('system_message')->where($message_where_delete)->delete();
							}

							D('CrmCreateMessage')->createMessage(7,$sms,$first_member,$company_id,-1,$custV['customer_id']);
						}

						# 删除旧数据
						getCrmDbModel('customer_nofollow_control')->where(['customer_id'=>$custV['customer_id'],'company_id'=>$company_id])->delete();

						getCrmDbModel('customer_nofollow_control')->add($noFollowmap);
					}
				}
				// G('end');
				// file_put_contents("./Feeldesk/Runtime/Logs/Crm/recover.txt", "Time3====".G('begin','end').'s'."----".date('Y-m-d H:i:s')."-- \r\n\n", FILE_APPEND);
				// G('begin');
				//客户未回访通知
				if((int)$follow_space >= (int)$crmsite['noReturnvisit'] && $custV['is_trade'] == 1 && $noticeConfig['noreturnvisit_customer_notify'] == 10)
				{
					$noReturnvisitmap = ['customer_id'=>$custV['customer_id'],'company_id'=>$company_id,'send_time'=>strtotime(date("Y-m-d"),time())];

					$noReturnvisit_control = getCrmDbModel('customer_noreturnvisit_control')->where($noReturnvisitmap)->getField('id');

					if(!$noReturnvisit_control && time() >= $workStartTime)
					{
						# 查询已通知且未查看的数量
						$message_where_count = ['company_id'=>$company_id,'customer_id'=>$custV['customer_id'],'recipient_id'=>$custV['member_id'],'recipient'=>1,'category'=>'crm_customer','msg_type'=>'crm_noreturnvisit_customer','msg_system'=>'crm','read_status'=>1,'create_time'=>[['egt',$week_ago_time],['elt',time()]]];

						$message_where_delete = ['company_id'=>$company_id,'customer_id'=>$custV['customer_id'],'recipient_id'=>$custV['member_id'],'recipient'=>1,'category'=>'crm_customer','msg_type'=>'crm_noreturnvisit_customer','msg_system'=>'crm','read_status'=>1,'create_time'=>['elt',$week_ago_time]];

						$count_message = M('system_message')->where($message_where_count)->count();

						# 同一用户有超过7条同一客户未查看通知，删除旧数据
						if($count_message >= 7)
						{
							M('system_message')->where($message_where_delete)->delete();
						}

						D('CrmCreateMessage')->createMessage(8,$sms,$custV['member_id'],$company_id,-1,$custV['customer_id']);

						if($noreturnvisit_customer_notifier)
						{
							$noreturnvisit_customer_notifier_arr = explode(',',$noreturnvisit_customer_notifier);

							foreach($noreturnvisit_customer_notifier_arr as $key=>$val)
							{
								if($val && $val != $custV['member_id'])
								{
									# 重新赋值通知人，删除旧数据
									$message_where_count['recipient_id'] = $message_where_delete['recipient_id'] = $val;

									$count_message = M('system_message')->where($message_where_count)->count();

									if($count_message >= 7)
									{
										M('system_message')->where($message_where_delete)->delete();
									}

									D('CrmCreateMessage')->createMessage(8,$sms,$val,$company_id,-1,$custV['customer_id']);
								}
							}
						}
						elseif($first_member)
						{
							# 重新赋值通知人，删除旧数据
							$message_where_count['recipient_id'] = $message_where_delete['recipient_id'] = $first_member;

							$count_message = M('system_message')->where($message_where_count)->count();

							if($count_message >= 7)
							{
								M('system_message')->where($message_where_delete)->delete();
							}

							D('CrmCreateMessage')->createMessage(8,$sms,$first_member,$company_id,-1,$custV['customer_id']);
						}

						# 删除旧数据
						getCrmDbModel('customer_noreturnvisit_control')->where(['customer_id'=>$custV['customer_id'],'company_id'=>$company_id])->delete();

						getCrmDbModel('customer_noreturnvisit_control')->add($noReturnvisitmap);
					}
				}
				// G('end');
				// file_put_contents("./Feeldesk/Runtime/Logs/Crm/recover.txt", "Time4====".G('begin','end').'s'."----".date('Y-m-d H:i:s')."-- \r\n\n", FILE_APPEND);
				// G('begin');
				if($crmsite['custRecover']==1 && $custV['is_trade'] == 0)
				{
					$contract = getCrmDbModel('contract')->where($where)->field('contract_id,createtime')->find();

					if(!$contract)
					{
						if($customer_log)
						{
							$contract_space = floor((time()-$customer_log['createtime'])/86400); //省略小数
						}
						else
						{
							$contract_space = floor((time()-$custV['createtime'])/86400); //省略小数
						}
					}

					//系统自动回收客户
					if((int)$follow_space >= (int)$crmsite['noFollowupDay'] || (int)$contract_space >= (int)$crmsite['noSignOrderDay'])
					{
						getCrmDbModel('customer')->where($where)->save(['member_id'=>0]);

						getCrmDbModel('contacter')->where($where)->save(['member_id'=>0]);

						getCrmDbModel('opportunity')->where($where)->save(['member_id'=>0]);

						getCrmDbModel('customer_recover')->add(['customer_id'=>$custV['customer_id'],'company_id'=>$company_id,'first_contact_id'=>$custV['first_contact_id'],'member_id'=>$custV['member_id'],'recover_time'=>time()]);

						$customer_detail = CrmgetCrmDetailList('customer',$custV['customer_id'],$company_id);

						$logcontent = L('SYSTEM_RECLAIMED_CUSTOMER').$customer_detail['name'];

						D('CrmLog')->addCrmLog('customer',7,$company_id,0,$custV['customer_id'],0,0,0,$customer_detail['name'],$logcontent);
					}
				}
				// G('end');
				// file_put_contents("./Feeldesk/Runtime/Logs/Crm/recover.txt", "Time5====".G('begin','end').'s'."----".date('Y-m-d H:i:s')."-- \r\n\n", FILE_APPEND);
			}

			//合同过期提醒
			if($noticeConfig['contract_expire_notify'] == 10)
			{
				$contractExpiretime = $crmsite['contractExpire'] ? (int) $crmsite['contractExpire'] : 3;

				$contract = getCrmDbModel('contract')->where(['company_id'=>$company_id,'isvalid'=>1])->field('contract_id,customer_id')->select();

				if($contract)
				{
					$contract_expire_notifier = $noticeConfig['contract_expire_notifier'];

					if($contract_expire_notifier)
					{
						$contract_expire_notifier = explode(',',$contract_expire_notifier);
					}

					foreach($contract as $contK => &$contV)
					{
						$contract_detail = CrmgetCrmDetailList('contract',$contV['contract_id'],$company_id,'end_time');
						//echo $contract_detail['end_time'].'///';
						$end_time = strtotime($contract_detail['end_time']);

						$expire_day = ceil(($end_time-time())/3600/24);

						if($expire_day >= 0 && $contractExpiretime >= $expire_day)
						{
							if($contract_expire_notifier)
							{
								foreach($contract_expire_notifier as $key=>$val)
								{
									$contractExpiremap = ['contract_id'=>$contV['contract_id'],'company_id'=>$company_id,'member_id'=>$val,'send_time'=>strtotime(date("Y-m-d"),time())];

									$contractExpire_control = getCrmDbModel('contractExpire_control')->where($contractExpiremap)->getField('id');

									if(!$contractExpire_control && time() >= $workStartTime && $val)
									{
										D('CrmCreateMessage')->createMessage(10,$sms,$val,$company_id,-1,$contV['customer_id'],0,$contV['contract_id']);

										getCrmDbModel('contractExpire_control')->add(['contract_id'=>$contV['contract_id'],'company_id'=>$company_id,'member_id'=>$val,'send_time'=>strtotime(date("Y-m-d"),time()),'create_time'=>time()]);
									}
								}
							}
							elseif($first_member)
							{
								$contractExpiremap = ['contract_id'=>$contV['contract_id'],'company_id'=>$company_id,'member_id'=>$first_member,'send_time'=>strtotime(date("Y-m-d"),time())];

								$contractExpire_control = getCrmDbModel('contractExpire_control')->where($contractExpiremap)->getField('id');

								if(!$contractExpire_control && time() >= $workStartTime)
								{
									D('CrmCreateMessage')->createMessage(10,$sms,$first_member,$company_id,-1,$contV['customer_id'],0,$contV['contract_id']);

									getCrmDbModel('contractExpire_control')->add(['contract_id'=>$contV['contract_id'],'company_id'=>$company_id,'member_id'=>$first_member,'send_time'=>strtotime(date("Y-m-d"),time()),'create_time'=>time()]);
								}
							}
						}
					}
				}
			}
		}

		//sleep(60);
		//}
	}

	public function getUnseenCompanys()
	{
		$redis = new Redis();

		$company = M('company')->where(['crm_auth'=>10,'closed'=>0])->field('company_id,name')->select();

		foreach($company as $k=>$v)
		{
			$activity = M('company_audit')->where(['company_id'=>$v['company_id']])->getField('activity');

			if($activity == 2)
			{
				$redis->rPush('companyId',$v['company_id']);
			}
		}
	}
}

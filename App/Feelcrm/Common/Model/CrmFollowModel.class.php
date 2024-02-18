<?php

namespace Common\Model;

use Common\Model\BasicModel;

class CrmFollowModel extends BasicModel
{
	protected $autoCheckFields = false;

	protected $FollowFields = ['cmncate_id','reply_id','nextcontacttime','content','contacter_id','customer_id','clue_id'];

	public function createFollow($id,$company_id,$member_id,$sourcetype)
	{
		$data = $this->checkFollow($id,$company_id,$member_id,$sourcetype);

		if($data['msg'])
		{
			return ['status'=>0,'msg'=>$data['msg']];
		}

		$reply = I('post.reply');

		if($reply == 1)
		{
			$reply_id = getCrmDbModel('communicate_reply')->where(['company_id'=>$company_id,'cmncate_id'=>$data['cmncate_id'],'reply_content'=>$data['content']])->getField('reply_id');

			if(!$reply_id)//查询是否已有快捷回复
			{
				$reply_content['company_id'] = $company_id;

				$reply_content['cmncate_id'] = $data['cmncate_id'];

				$reply_content['create_time'] = NOW_TIME;

				$reply_content['closed'] = 0;

				$reply_content['reply_content'] = $data['content'];

				if($reply_id = getCrmDbModel('communicate_reply')->add($reply_content)) //添加快捷回复
				{
					$data['reply_id'] = $reply_id;
				}

				if($follow_id = getCrmDbModel('followup')->add($data))//添加联系记录
				{
					$this->successOperate($company_id,$member_id,$data,$follow_id,$sourcetype,'create');

					$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS')];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
				}
			}
			else
			{
				$data['reply_id'] = $reply_id;

				if($follow_id = getCrmDbModel('followup')->add($data))//添加联系记录
				{
					$this->successOperate($company_id,$member_id,$data,$follow_id,$sourcetype,'create');

					$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS')];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
				}
			}

		}else
		{
			if($data['cmncate_id'] && !getCrmDbModel('communicate_reply')->where(['company_id'=>$company_id,'cmncate_id'=>$data['cmncate_id'],'reply_content'=>$data['content']])->getField('reply_id'))
			{
				unset($data['reply_id']);
			}

			if($follow_id = getCrmDbModel('followup')->add($data))//添加联系记录
			{
				$this->successOperate($company_id,$member_id,$data,$follow_id,$sourcetype,'create');

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS')];
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
			}
		}

		return $result;
	}

	public function editFollow($follow_id,$company_id,$followup,$member_id,$sourcetype)
	{
		$data = $this->checkEditFollow($company_id);

		if($data['msg'])
		{
			return ['status'=>0,'msg'=>$data['msg']];
		}

		$data['follow_id'] = $follow_id;

		$reply = I('post.reply');

		if($reply == 1)
		{
			$reply_id = getCrmDbModel('communicate_reply')->where(['company_id'=>$company_id,'cmncate_id'=>$data['cmncate_id'],'reply_content'=>$data['content']])->getField('reply_id');

			if(!$reply_id)//查询是否已有快捷回复
			{
				$reply_content['company_id'] = $company_id;

				$reply_content['cmncate_id'] = $data['cmncate_id'];

				$reply_content['create_time'] = NOW_TIME;

				$reply_content['closed'] = 0;

				$reply_content['reply_content'] = $data['content'];

				if($reply_id = getCrmDbModel('communicate_reply')->add($reply_content)) //添加快捷回复
				{
					$data['reply_id'] = $reply_id;
				}

				if(getCrmDbModel('followup')->save($data) === false)//添加联系记录
				{
					$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
				}
				else
				{
					$this->successOperate($company_id,$member_id,$data,$follow_id,$sourcetype,'edit',$followup);

					$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS')];
				}

			}else
			{
				$data['reply_id'] = $reply_id;

				if(getCrmDbModel('followup')->save($data) === false)//添加联系记录
				{
					$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
				}
				else
				{
					$this->successOperate($company_id,$member_id,$data,$follow_id,$sourcetype,'edit',$followup);

					$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS')];
				}
			}
		}
		else
		{
			if($data['cmncate_id'] && !getCrmDbModel('communicate_reply')->where(['company_id'=>$company_id,'cmncate_id'=>$data['cmncate_id'],'reply_content'=>$data['content']])->getField('reply_id'))
			{
				unset($data['reply_id']);
			}

			if(getCrmDbModel('followup')->save($data) === false)//添加联系记录
			{
				$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
			}
			else
			{
				$this->successOperate($company_id,$member_id,$data,$follow_id,$sourcetype,'edit',$followup);

				$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS')];
			}
		}

		return $result;
	}

	private function successOperate($company_id,$member_id,$data,$follow_id,$sourcetype,$type,$followup=[])
	{
		if($type == 'create')
		{
			$files = isset($_POST['file']) ? I('post.file') : [];

			D('Upload')->saveUploadFile($files, $company_id, 'follow', 0,$data['clue_id'],$data['customer_id'],$data['opportunity_id'],$follow_id);
		}
		else
		{
			$files = isset($_POST['file']) ? I('post.file') : [];

			$delFiles = isset($_POST['delFile']) ? I('post.delFile') : [];

			D('Upload')->updateUploadFile($files,$delFiles,$company_id,'follow',0,$followup['clue_id'],$followup['customer_id'],$followup['opportunity_id'],$follow_id);
		}

		if($sourcetype == 'clue')
		{
			if($followup) $data['clue_id'] = $followup['clue_id'];

			if($data['nextcontacttime'])
			{
				getCrmDbModel('clue')->where(['company_id'=>$company_id,'clue_id'=>$data['clue_id']])->save(['nextcontacttime'=>$data['nextcontacttime']]);
			}
			else
			{
				getCrmDbModel('clue')->where(['company_id'=>$company_id,'clue_id'=>$data['clue_id']])->save(['nextcontacttime'=>0]);
			}

			$clue_detail = CrmgetCrmDetailList('clue',$data['clue_id'],$company_id,'name');

			if($type == 'create')
			{
				$clue_status = getCrmDbModel('clue')->where(['company_id'=>$company_id,'clue_id'=>$data['clue_id']])->getField('status');

				if($clue_status == -1)
				{
					getCrmDbModel('clue')->where(['company_id'=>$company_id,'clue_id'=>$data['clue_id']])->save(['status'=>1]);
				}

				getCrmDbModel('clue')->where(['company_id'=>$company_id,'clue_id'=>$data['clue_id']])->save(['lastfollowtime'=>NOW_TIME]);

				D('CrmLog')->addCrmLog('follow',1,$company_id,$member_id,0,0,0,0,$clue_detail['name'],$follow_id,0,0,0,0,0,0,0,$data['clue_id']);
			}
			else
			{
				D('CrmLog')->addCrmLog('follow',2,$company_id,$member_id,0,0,0,0,$clue_detail['name'],$follow_id,0,0,0,0,0,0,0,$followup['clue_id']);
			}
		}
		elseif($sourcetype == 'opportunity')
		{
			if($followup) $data['opportunity_id'] = $followup['opportunity_id'];

			$customer_id = getCrmDbModel('opportunity')->where(['company_id'=>$company_id,'opportunity_id'=>$data['opportunity_id']])->getField('customer_id');

			//$customer_nextcontacttime = getCrmDbModel('customer')->where(['company_id'=>$company_id,'customer_id'=>$customer_id])->getField('nextcontacttime');

			if($data['nextcontacttime'])
			{
				getCrmDbModel('opportunity')->where(['company_id'=>$company_id,'opportunity_id'=>$data['opportunity_id']])->save(['nextcontacttime'=>$data['nextcontacttime']]);

				getCrmDbModel('customer')->where(['company_id'=>$company_id,'customer_id'=>$customer_id])->save(['nextcontacttime'=>$data['nextcontacttime']]);
			}
			else
			{
				getCrmDbModel('opportunity')->where(['company_id'=>$company_id,'opportunity_id'=>$data['opportunity_id']])->save(['nextcontacttime'=>0]);

				getCrmDbModel('customer')->where(['company_id'=>$company_id,'customer_id'=>$customer_id])->save(['nextcontacttime'=>0]);
			}

			$opportunity_detail = CrmgetCrmDetailList('opportunity',$data['opportunity_id'],$company_id,'name');

			if($type == 'create')
			{
				getCrmDbModel('opportunity')->where(['company_id'=>$company_id,'opportunity_id'=>$data['opportunity_id']])->save(['lastfollowtime'=>NOW_TIME]);

				getCrmDbModel('customer')->where(['company_id'=>$company_id,'customer_id'=>$customer_id])->save(['lastfollowtime'=>NOW_TIME]);

				D('CrmLog')->addCrmLog('follow',1,$company_id,$member_id,0,0,0,0,$opportunity_detail['name'],$follow_id,0,0,0,0,0,0,0,0,$data['opportunity_id']);
			}
			else
			{
				D('CrmLog')->addCrmLog('follow',2,$company_id,$member_id,0,0,0,0,$opportunity_detail['name'],$follow_id,0,0,0,0,0,0,0,0,$data['opportunity_id']);
			}
		}
		else
		{
			if($followup) $data['customer_id'] = $followup['customer_id'];

			if($data['nextcontacttime'])
			{
				getCrmDbModel('customer')->where(['company_id'=>$company_id,'customer_id'=>$data['customer_id']])->save(['nextcontacttime'=>$data['nextcontacttime']]);
			}
			else
			{
				getCrmDbModel('customer')->where(['company_id'=>$company_id,'customer_id'=>$data['customer_id']])->save(['nextcontacttime'=>0]);
			}

			$customer_detail = CrmgetCrmDetailList('customer',$data['customer_id'],$company_id,'name');

			if($type == 'create')
			{
				getCrmDbModel('customer')->where(['company_id'=>$company_id,'customer_id'=>$data['customer_id']])->save(['lastfollowtime'=>NOW_TIME]);

				D('CrmLog')->addCrmLog('follow',1,$company_id,$member_id,$data['customer_id'],0,0,0,$customer_detail['name'],$follow_id);
			}
			else
			{
				D('CrmLog')->addCrmLog('follow',2,$company_id,$member_id,$followup['customer_id'],0,0,0,$customer_detail['name'],$follow_id);
			}
		}

		return true;
	}

	public function checkFollow($id,$company_id,$member_id,$sourcetype)
	{
		$follow = checkFields(I('post.follow'), $this->FollowFields);

		if(!$follow['content'] && !$follow['reply_id'])
		{
			return ['status'=>0,'msg'=>L('FILL_IN_THE_COMMUNICATION_RECORD')];
		}
		if(!$follow['content'])
		{
			$follow['content'] = getCrmDbModel('communicate_reply')->where(['company_id'=>$company_id,'reply_id'=>$follow['reply_id'],'closed'=>0])->getField('reply_content');
		}

		if($sourcetype == 'clue')
		{
			if($id)
			{
				$follow['clue_id'] = $id;
			}
			elseif(!$follow['clue_id'])
			{
				return ['status'=>0,'msg'=>L('PLEASE_SELECT_CLUE')];
			}
		}
		elseif($sourcetype == 'opportunity')
		{
			if($id)
			{
				$follow['opportunity_id'] = $id;
			}
			elseif(!$follow['opportunity_id'])
			{
				return ['status'=>0,'msg'=>L('PLEASE_SELECT_OPPORTUNITY')];
			}
		}
		else
		{
			if(!$follow['clue_id'])
			{
				if($id)
				{
					$follow['customer_id'] = $id;
				}
				elseif(!$follow['customer_id'])
				{
					return ['status'=>0,'msg'=>L('PLEASE_SELECT_CUSTOMER')];
				}
			}
		}

		if($follow['clue_id'] && $follow['customer_id'])
		{
			return ['status'=>0,'msg'=>L('CANNOT_SELECT_CLUES_AND_CUSTOMERS')];
		}

		if($follow['clue_id'])
		{
			$follow['type'] = 'clue';
		}
		elseif($follow['opportunity_id'])
		{
			$follow['type'] = 'opportunity';
		}
		elseif($follow['customer_id'])
		{
			$follow['type'] = 'customer';
		}



		// if(!$follow['contacter_id'])
		// {
		// $this->ajaxReturn(['status'=>0,'msg'=>'请选择联系人']);
		// }

		$reply = I('post.reply');

		if($reply == 1 && !$follow['cmncate_id'])
		{
			return ['status'=>0,'msg'=>L('SELECT_COMMUNICATION_TYPE')];
		}

		if($follow['nextcontacttime'])
		{
			$follow['nextcontacttime'] = strtotime($follow['nextcontacttime']);
		}

		$follow['company_id'] = $company_id;

		$follow['member_id'] = $member_id;

		$follow['createtime'] = NOW_TIME;

		return $follow;
	}

	public function checkEditFollow($company_id)
	{
		$follow = checkFields(I('post.follow'), $this->FollowFields);

		if(!$follow['content'] && !$follow['reply_id'])
		{
			return ['status'=>0,'msg'=>L('FILL_IN_THE_COMMUNICATION_RECORD')];
		}
		if(!$follow['content'])
		{
			$follow['content'] = getCrmDbModel('communicate_reply')->where(['company_id'=>$company_id,'reply_id'=>$follow['reply_id'],'closed'=>0])->getField('reply_content');
		}

		// if(!$follow['contacter_id'])
		// {
		// $this->ajaxReturn(['status'=>0,'msg'=>'请选择联系人']);
		// }

		$reply = I('post.reply');

		if($reply == 1 && !$follow['cmncate_id'])
		{
			return ['status'=>0,'msg'=>L('SELECT_COMMUNICATION_TYPE')];
		}

		if($follow['nextcontacttime'])
		{
			$follow['nextcontacttime'] = strtotime($follow['nextcontacttime']);
		}

		return $follow;

	}

}

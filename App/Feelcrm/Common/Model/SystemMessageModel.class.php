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

use Think\Page;

class SystemMessageModel extends BasicModel
{
	protected $pk   = 'msg_id';

	protected $tableName = 'system_message';

	/*
	* PC端获取系统消息
	* @param int $company_id 公司ID
	* @param int $recipient_id 接受者ID
	* @param int $recipient 接受者类型 1 用户 2 会员、游客
	* @param int $rollPage 页码数量
	* @param int $listRows 每页显示数量
	* @return array
	*/
	public function getMessage($company_id = 0,$recipient_id = 0,$recipient = 0,$rollPage = 11,$listRows = 14)
	{
		$field = ['company_id'=>$company_id,'recipient_id'=>$recipient_id,'recipient'=>$recipient];

		$msg_id = I('get.msg_id');

		$types = I('get.types');

		if($types == 'unread') $field['read_status'] = 1;

		if($types == 'read') $field['read_status'] = 2;

		if($msg_id)
		{
			$field['msg_id'] = $msg_id;

			$detail = $this->where($field)->find();

			$result = ['code'=>1,'detail'=>$detail];

			$this->where(['msg_id'=>$msg_id])->setField('read_status',2);
		}
		else
		{
			$count = $this->where($field)->count('msg_id');

			$Page = new Page($count,$listRows);

			$message = $this->where($field)->limit($Page->firstRow, $Page->listRows)->order('msg_id desc')->fetchAll();

			$Page->rollPage = $rollPage;

			$Page->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');

			$result = ['code'=>2,'page'=>$Page->show(),'message'=>$message];
		}

		$result['types'] = $types;

		$result['from'] = I('get.from');

		return $result;
	}


	/*
	* PC端删除系统消息
	* @param int $company_id 公司ID
	* @param int $recipient_id 接受者ID
	* @param int $recipient 接受者类型 1 用户 2 会员、游客
	* @return array
	*/
	public function deleteMessage($company_id = 0,$recipient_id = 0,$recipient = 0)
	{
		$ids = I('post.ids');

		$type = I('post.type') ? I('post.type') : I('get.type');

		if(!$ids) $ids = explode(',',I('get.ids'));

		if(count($ids) > 0)
		{
			$result = $this->where(['company_id'=>$company_id,'recipient_id'=>$recipient_id,'recipient'=>$recipient,'msg_id'=>['in',$ids]])->delete();

			if($result)
			{
				$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>U('getMessage',['types'=>$type])];
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
			}
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('ILLEGAL_ACCESS')];
		}

		return $result;
	}


	/*
	* PC端更新系统消息
	* @param int    $company_id 公司ID
	* @param int    $recipient_id 接受者ID
	* @param int    $recipient 接受者类型 1 用户 2 会员、游客
	* @param string $source 读取标签 read_all 全部已读
	* @return array
	*/
	public function updateMessageStatus($company_id = 0,$recipient_id = 0,$recipient = 0,$source = '')
	{
		$where = ['company_id'=>$company_id,'recipient_id'=>$recipient_id,'recipient'=>$recipient];

		if($source != 'read_all')
		{
			$ids = I('post.ids');

			if(!$ids) $ids = explode(',',I('get.ids'));

			if(count($ids) > 0)
			{
				$where['msg_id'] = ['in',$ids];
			}
		}

		$result = $this->where($where)->setField('read_status',2);

		$type = I('post.type') ? I('post.type') : I('get.type');

		if($result !== false)
		{
			$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('getMessage',['types'=>$type])];
		}
		else
		{
			$result = ['status'=>0,'msg'=>L('ILLEGAL_ACCESS')];
		}

		return $result;
	}


	/*
	* 移动端获取系统消息
	* @param int    $company_id     公司ID
	* @param int    $recipient_id   接受者ID
	* @param int    $recipient      接受者类型，1 用户 2 会员、游客
	* @param string $request        请求类型，flow 流加载
	* @param string $msg_system     系统类型，ticket 工单系统
	* @return array
	*/
	public function getMessageByMobile($company_id = 0,$recipient_id = 0,$recipient = 0,$request = '',$msg_system = 'ticket')
	{
		$field = ['company_id'=>$company_id,'recipient_id'=>$recipient_id,'recipient'=>$recipient,'msg_system'=>$msg_system];

		if($request == 'flow')
		{
			$count = $this->where($field)->count('msg_id');

			$Page = new Page($count, 50);

			$message = $this->where($field)->limit($Page->firstRow, $Page->listRows)->order('read_status asc,msg_id desc')->select();

			foreach($message as &$v)
			{
				if($msg_system == 'ticket')
				{
					if($v['sub_ticket_id'])
					{
						$v['ticket'] = M('sub_ticket')->where(['ticket_id'=>$v['sub_ticket_id']])->field('member_id,process_id as dispose_id,title')->find();
					}
					else
					{
						$v['ticket'] = M('ticket')->where(['ticket_id'=>$v['ticket_id']])->field('member_id,dispose_id,title')->find();
					}

					$v['ticket']['title'] = !$v['ticket']['title'] ? '' : $v['ticket']['title'];

					if($v['category'] == 'handle')
					{
						$v['msg_name'] = L('TICKET_CIRCULATION_NOTICE');

						$publisher_name = M('member')->where(['member_id'=>$v['ticket']['member_id']])->getField('name');

						$handler_name = M('member')->where(['member_id'=>$v['ticket']['dispose_id']])->getField('name');

						if($handler_name)
						{
							$v['msg_item'] = "<li>".L('PUBLISHER')."：{$publisher_name}</li><li>".L('HANDLER')."：{$handler_name}</li>";
						}
						else
						{
							$v['msg_item'] = "<li>".L('PUBLISHER')."：{$publisher_name}</li>";
						}
					}
					else if($v['category'] == 'urge')
					{
						$v['msg_name'] = L('URGE_NOTIFY');

						$urge_name = M('member')->where(['member_id'=>$v['urge_member_id']])->getField('name');

						$v['msg_item'] = "<li>".L('PROMOTER')."：{$urge_name}</li>";
					}
					else if($v['category'] == 'end')
					{
						$v['msg_name'] = L('TICKET_END_NOTICE');
					}
				}

				$v['msg_content'] = strip_tags($v['msg_content']);

				$v['create_time'] = date('Y-m-d H:i',$v['create_time']);
			}

			return ['data'=>$message,'pages'=>ceil($count/8)];
		}
		else
		{
			unset($field['msg_system']);

			if($recipient == 1)
			{
				$fieldStr = "sum(case msg_system when 'ticket' then 1 else 0 end) ticket,sum(case msg_system when 'crm' then 1 else 0 end) crm";
			}
			else
			{
				$fieldStr = "sum(case msg_system when 'ticket' then 1 else 0 end) ticket";
			}

			$unReadNumber = $this->where(array_merge($field,['read_status'=>1]))->field($fieldStr)->find();

			return ['unReadNumber'=>$unReadNumber];
		}
	}


	/*
	* 移动端获取系统消息详情
	* @param int    $company_id     公司ID
	* @param int    $recipient_id   接受者ID
	* @param int    $recipient      接受者类型，1 用户 2 会员、游客
	* @param int    $id             消息ID
	* @return array
	*/
	public function getMessageDetailByMobile($company_id,$recipient_id,$recipient,$id = 0)
	{
		$field = ['company_id'=>$company_id,'recipient_id'=>$recipient_id,'recipient'=>$recipient,'msg_id'=>$id];

		$detail = $this->where($field)->find();

		if($detail)
		{
			$this->where($field)->save(['read_status'=>2,'is_remind'=>2]);

			if($detail['sub_ticket_id'])
			{
				$ticket = M('sub_ticket')->where(['ticket_id'=>$detail['sub_ticket_id']])->field('title,ticket_no')->find();
			}
			else
			{
				$ticket = M('ticket')->where(['ticket_id'=>$detail['ticket_id']])->field('title,ticket_no')->find();
			}

			$result = ['code'=>2,'detail'=>$detail,'ticket'=>$ticket];
		}
		else
		{
			$result = ['code'=>1,'msg'=>L('MSG_NOT')];
		}

		return $result;
	}


	/*
	* 移动端删除系统消息
	* @param int    $company_id     公司ID
	* @param int    $recipient_id   接受者ID
	* @param int    $recipient      接受者类型，1 用户 2 会员、游客
	* @return array
	*/
	public function deleteMessageByMobile($company_id = 0,$recipient_id = 0,$recipient = 0)
	{
		$id = I('post.id');

		if($id > 0)
		{
			$result = $this->where(['company_id'=>$company_id,'recipient_id'=>$recipient_id,'recipient'=>$recipient,'msg_id'=>$id])->delete();

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

		return $result;
	}


	/*
	* 移动端更新系统消息读取状态及删除全部消息
	* @param int    $company_id     公司ID
	* @param int    $recipient_id   接受者ID
	* @param int    $recipient      接受者类型，1 用户 2 会员、游客
	* @param int    $msg_system     系统类型，ticket 工单系统
	* @return array
	*/
	public function updateMessageStatusByMobile($company_id = 0,$recipient_id = 0,$recipient = 0,$msg_system = 'ticket')
	{
		$type = I('post.type') ? I('post.type') : I('get.type');

		if(in_array($type,['delete','read']))
		{
			$where = ['company_id'=>$company_id,'recipient_id'=>$recipient_id,'recipient'=>$recipient,'msg_system'=>$msg_system];

			if($type == 'read')
			{
				$result = $this->where($where)->setField('read_status',2);
			}
			else
			{
				$result = $this->where($where)->delete();
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

		return $result;
	}
}

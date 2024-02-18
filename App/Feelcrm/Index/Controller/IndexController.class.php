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

namespace Index\Controller;

use Index\Common\BasicController;

class IndexController extends BasicController
{
	public function index()
	{
		$request_source = I('get.request_source');

		$url = I('get.redirect_url');

		if($request_source == 'sso')
		{
			$redirect_url = $url ? '/index/base?redirect_url='.$url : '/index/base';
		}
		else
		{
			$redirect_url = $url ? $url : '/index/base';
		}

		$from_type = I('get.from_type') ? decrypt(I('get.from_type'),'FROMTYPE') : '';

        $systemAuth = M('company')->where(['company_id'=>$this->_company_id])->field('ticket_auth,crm_auth')->find();

        $this->assign('systemAuth',$systemAuth);

        $this->assign('redirect_url',$redirect_url);

        $this->assign('from_type',$from_type);

        $this->display();
    }


	public function welcome()
	{
        $select_auth_range = I('get.select_auth_range');

		$select_time_range = I('get.select_time_range');

        $customTime = $dayNumberArr = [];

        if($select_auth_range == 'all')
        {
            $field = ['company_id'=>$this->_company_id];
        }
        else if($select_auth_range == 'group')
        {
            $field = ['company_id'=>$this->_company_id,'group_id'=>['in',[$this->_member['group_id']]]];
        }
        else
        {
	        $select_auth_range = 'own';

            $field = ['company_id'=>$this->_company_id];

            $field1['member_id'] = $this->member_id;

            $field2['dispose_id'] = $field3['dispose_id'] = $this->member_id;
        }

        if($select_time_range == 'today')
        {
            $dayNumber = 1;
        }
        else if($select_time_range == 'month')
        {
            $dayNumber = date("t",time());
        }
        else if($select_time_range == 'datetime')
        {
            $custom_time = I('get.custom_time');

            $customTime = explode('~',$custom_time);

            $dayNumber = intval((strtotime($customTime[1])-strtotime($customTime[0]))/86400);//日期范围之间的天数，并无作用

            $dayNumberArr = getDateRange(trim($customTime[0]),trim($customTime[1]));

            $this->assign('custom_time',$custom_time);
        }
        else
        {
	        $select_time_range = 'week';

            $dayNumber = 7;
        }

        $days = [];

        if($customTime) // 指定日期范围工单数据
        {
            foreach($dayNumberArr as $dk=>$dv)
            {
                $days[$dk]['start']	= strtotime(date($dv));

                $days[$dk]['end']   = strtotime(date($dv.' 23:59:59'));
            }
        }
		else // $dayNumber天工单数据
        {
            for($i=$dayNumber;$i>=0;$i--)
            {
                $days[$i]['start']	= strtotime(date('Y-m-d',strtotime("-{$i} days")));

                $days[$i]['end']    = strtotime(date('Y-m-d 23:59:59',strtotime("-{$i} days")));
            }
        }

		$dateTimes = $releaseNumbers = $handleNumbers = $completeNumbers = [];

        foreach($days as $k=>$v)
        {
            $field1['create_time'] = [['egt',$v['start']],['elt',$v['end']]];

            $field2['dispose_time'] = [['egt',$v['start']],['elt',$v['end']]];

            $field3['end_time'] = [['egt',$v['start']],['elt',$v['end']]];

            $releaseNumbers[] = (int) D('Ticket')->where(array_merge($field,$field1))->count();//7日内每日发布的工单数

            $handleNumbers[] = (int) D('Ticket')->where(array_merge($field,$field2))->count();//7日内每日处理的工单数

            $completeNumbers[] = (int) D('Ticket')->where(array_merge($field,$field3))->count();//7日内每日完成的工单数

            $dateTimes[] = date('m-d',$v['start']);
        }

        $this->assign('dateTimes',json_encode($dateTimes));

        $this->assign('releaseNumbers',json_encode($releaseNumbers));

        $this->assign('handleNumbers',json_encode($handleNumbers));

        $this->assign('completeNumbers',json_encode($completeNumbers));

//        是否同步FeelChat
        $feelChat = M('feelchat')->where(['company_id'=>$this->_company_id])->field('close,is_sync')->find();

        $this->assign('feelChat',$feelChat);

//        公告信息
		$notice = M('Notice')->where(['company_id'=>$this->_company_id,'notice_type'=>10])
			->field('notice_id,notice_title')
			->order('create_time desc')
			->select();

//		 我处理的工单
		$ticketData['ticket'] = M('ticket')->where(['company_id'=>$this->_company_id,'dispose_id'=>$this->member_id,'end_time'=>0,'delete'=>2])
			->field('ticket_id,status_id,priority,member_id,title')
			->select();

//		 我处理的子工单
		$ticketData['subTicket'] = M('sub_ticket')->where(['company_id'=>$this->_company_id,'process_id'=>$this->member_id,'end_time'=>0,'is_delete'=>10])
			->field('ticket_id,status_id,member_id,title')
			->select();

//		工单状态
		$statusName = D('TicketStatus')->getNameByLang('status_name');

		$ticket_status = D('TicketStatus')->where(['company_id'=>$this->_company_id])->field("*,{$statusName}")->order('sort asc')->fetchAll();

//		 优先级
		$priority['data'] = D('Ticket')->getPriority();

		$priority['color'] = D('Ticket')->getPriorityColor();

//		 所有用户及会员
		$member = D('Member')->where(['company_id'=>$this->_company_id,'closed'=>0])
			->field('name,face,member_id')
			->fetchAll();

		$this->assign('ticket',$this->getTicketNumber());

		$this->assign('ticketData',$ticketData);

		$this->assign('members',$member);

		$this->assign('ticket_status',$ticket_status);

		$this->assign('priority',$priority);

		$this->assign('notice',$notice);

        $this->assign('select_auth_range',$select_auth_range);

        $this->assign('select_time_range',$select_time_range);

        $this->assign('nowTime',date('Y-m-d',time()));

        $this->assign('yesTime',date('Y-m-d',strtotime('-1 day')));

		$this->display();
	}


    public function base($redirect_url = '')
    {
        $redirect_url = $redirect_url ? U($redirect_url) : U('Index/index/welcome');

        $this->assign('redirect_url',$redirect_url);

        $this->display();
    }


	private function getTicketNumber()
	{
		$waitReplyTicketNumber = $timeoutTicketNumber = $groupTicketNumber = $waitAuditTicketNumber = 0;

		$param = ['company_id'=>$this->_company_id,'member_id'=>$this->member_id];

//        待回复的
		$waitReplyTicketAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'ticket/waitReplyTicket',$this->_member['role_id']);

		if($waitReplyTicketAuth)
		{
			$param['source']    = 'waitReplyTicket';

			$param['company_id'] = $this->_company_id;

			$param['member_id'] = $this->member_id;

			$waitReplyTicketNumber = D('Ticket')->getTicketNumber($param);//等待回复的工单数
		}

//        超时的工单
		$timeoutTicketAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'ticket/timeoutTicket',$this->_member['role_id']);

		if($timeoutTicketAuth)
		{
			$param['source']    = 'timeoutTicket';

			$param['company_id'] = $this->_company_id;

			$param['member_id'] = $this->member_id;

			$timeoutTicketNumber = D('Ticket')->getTicketNumber($param);
		}

//        所在组的
		$groupTicketAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'ticket/groupTicket',$this->_member['role_id']);

		if($groupTicketAuth)
		{
			$param['source']    = 'groupTicket';

			$param['company_id'] = $this->_company_id;

			$param['member_id'] = $this->member_id;

			$groupTicketNumber = D('Ticket')->getTicketNumber($param);
		}

//        待审核的
		$waitAuditTicketAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'ticket/waitAuditTicket',$this->_member['role_id']);

		if($waitAuditTicketAuth)
		{
			$param['source']    = 'waitAuditTicket';

			$param['company_id'] = $this->_company_id;

			$waitAuditTicketNumber = D('Ticket')->getTicketNumber($param);
		}

		$result = [
			'auth'      => [
				'wait_reply'    => $waitReplyTicketAuth,
				'timeout'       => $timeoutTicketAuth,
				'department'    => $groupTicketAuth,
				'wait_audit'    => $waitAuditTicketAuth,
			],
			'number'    => [
				'wait_reply'    => $waitReplyTicketNumber,
				'timeout'       => $timeoutTicketNumber,
				'department'    => $groupTicketNumber,
				'wait_audit'    => $waitAuditTicketNumber,
			]
		];

		return $result;
	}
}

<?php
namespace Mobile\Controller;

use Mobile\Common\BasicController;

class IndexController extends BasicController
{
    public function index()
    {
//		当日工单数据
	    $days['start'] = strtotime(date('Y-m-d 00:00:00'));

	    $days['end'] = strtotime(date('Y-m-d 23:59:59'));

	    $where1 = ['company_id'=>$this->_company_id,'create_time'=>[['egt',$days['start']],['elt',$days['end']]]];

	    $releaseNumber = D('Ticket')->where($where1)->count();//今日内发布的工单数

	    $where2 = ['company_id'=>$this->_company_id,'dispose_time'=>[['egt',$days['start']],['elt',$days['end']]]];

	    $handleNumber = D('Ticket')->where($where2)->count();//今日内处理的工单数

	    $where3 = ['company_id'=>$this->_company_id,'end_time'=>[['egt',$days['start']],['elt',$days['end']]]];

	    $completeNumber = D('Ticket')->where($where3)->count();//今日完成的工单数

	    $ticket = D('Ticket')->getTicketAuthByMobile($this->_company_id,$this->member_id,$this->_mobile['role_id']);

	    $this->assign('releaseNumber',$releaseNumber);

	    $this->assign('handleNumber',$handleNumber);

	    $this->assign('completeNumber',$completeNumber);

	    $this->assign('company',session('company'));

	    $this->assign('ticket',$ticket);

	    $this->display();
    }
}
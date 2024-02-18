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

class AreaModel extends BasicModel
{
	protected $autoCheckFields = false;

	/*
	* 更新工单区域组件、添加工单地区修改记录
	* @param array $data 请求的地区数据
	* @param int $ticket_id 工单ID
	*/
	public function updateTicketArea($data,$ticket_id)
	{
		$data['ticket_id'] = $ticket_id;

		$area = M('ticket_area')->where(['ticket_id'=>$ticket_id])->find();

		if($area)
		{
			$data['id'] = $area['id'];

			M('ticket_area')->save($data);
		}
		else if($data['country_code'])
		{
			M('ticket_area')->add($data);
		}

//	      区域修改记录
		if($data['country_code'] != $area['country_code'] || $data['province_code'] != $area['province_code'] || $data['city_code'] != $area['city_code'])
		{
			$before[] = M('country')->where(['code'=>$area['country_code']])->getField('name');

			$before[] = M('province')->where(['country_code'=>$area['country_code'],'code'=>$area['province_code']])->getField('name');

			$before[] = M('city')
				->where(['country_code'=>$area['country_code'],'province_code'=>$area['province_code'],'code'=>$area['city_code']])
				->getField('name');

			$modify_before = rtrim(implode(' - ',$before),' - ');

			$after[] = M('country')->where(['code'=>$data['country_code']])->getField('name');

			$after[] = M('province')->where(['country_code'=>$data['country_code'],'code'=>$data['province_code']])->getField('name');

			$after[] = M('city')
				->where(['country_code'=>$data['country_code'],'province_code'=>$data['province_code'],'code'=>$data['city_code']])
				->getField('name');

			$modify_after = rtrim(implode(' - ',$after),' - ');

			$modify = ['modify_before'=>$modify_before,'modify_after'=>$modify_after];

			return $modify;
		}
	}
}

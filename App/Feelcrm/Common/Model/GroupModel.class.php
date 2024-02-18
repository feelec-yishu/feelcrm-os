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

use Think\Cache\Driver\Redis;
use Think\Log;

class GroupModel extends BasicModel
{
	protected $pk   = 'group_id';

	protected $tableName = 'group';

//	获取工单处理部门管理员,目前仅适用于审核工单的审核人为部门主管的情况
	public function getProcessGroupManager($company_id = 0,$template_id = 0,$group_id = 0)
	{
		if(!$group_id)
		{
//            默认处理部门
			$handle_group_id = M('group')->where(['company_id'=>$company_id,'is_default'=>20,'ticket_auth'=>10])->getField('group_id');

			if($template_id > 0)
			{
				$where = ['company_id'=>$company_id,'channel'=>'template','route_value'=>$template_id];
			}
			else
			{
				$where = ['company_id'=>$company_id,'channel'=>'mail'];
			}

//		      路由处理部门
			$route_group_id = M('route')->where($where)->getField('group_id');

			$group_id = $route_group_id ? $route_group_id : $handle_group_id;
		}

		return $this->where(['group_id' =>$group_id])->getField('manager_id');
	}


	public function assignDisposeByGroupId($company_id,$template_id = 0,$group_id = 0)
    {
        if($group_id > 0)
        {
            $handle_group_id = $group_id;
        }
        else
        {
            if($template_id > 0)
            {
                $where = ['company_id'=>$company_id,'channel'=>'template','route_value'=>$template_id];
            }
            else
            {
                $where = ['company_id'=>$company_id,'channel'=>'mail'];
            }

            $group_id = M('route')->where($where)->getField('group_id');

            if($group_id > 0)
            {
	            $handle_group_id = $group_id;
            }
            else
            {
//                默认处理部门
	            $handle_group_id = M('group')->where(['company_id'=>$company_id,'is_default'=>20,'ticket_auth'=>10])->getField('group_id');
            }
        }

        $redis = new Redis();

//        部门下当前在线的客服
        if($redis->lLen($company_id.'_'.$handle_group_id.'_route_mail_disposeId') == 0)
        {
            $groupMemberWhere = [
                'company_id'        =>$company_id,
                "find_in_set('{$handle_group_id}',group_id)",
                'type'              =>1,
                'login_status'      =>1,//当前登录状态，1在线 2；
                'closed'            =>0,
            ];

            $groupMembers = M('member')->where($groupMemberWhere)->order('member_id asc')->field('member_id')->select();

	        if(!empty($groupMembers))
            {
                foreach($groupMembers as $m)
                {
                    $redis->rPush($company_id.'_'.$handle_group_id.'_route_mail_disposeId',$m['member_id']);
                }

//                缓存有效期2小时
	            $redis->expire($company_id.'_'.$handle_group_id.'_route_mail_disposeId',2*3600);
            }
        }

        $data = ['group_id'=>$handle_group_id,'wait_assign_status'=>0];

//        按客服ID轮流分配处理人
        $dispose_id = $redis->lpop($company_id.'_'.$handle_group_id.'_route_mail_disposeId');

        if($dispose_id > 0)
        {
            $data['recipient_id'] = $data['dispose_id'] = $dispose_id;

            $data['assign_time'] = NOW_TIME;

            $data['wait_assign_status'] = 1;
        }

        return $data;
    }


    /*
	* @param int $company_id 公司ID
	* @param array $deleteGroup 待删除的部门信息
	* @param int $transfer_group_id 转移数据的部门ID
	* @return array
	*/
    public function deleteGroup($company_id,$deleteGroup,$transfer_group_id,$sync_to_manage = false)
    {
	    $where = ['company_id'=>$company_id,'group_id'=>$deleteGroup['group_id']];

	    $delete = M('group')->where($where)->delete();

	    if($delete)
	    {
//            如果删除的部门是默认部门，则将转移的部门设为默认部门
		    if($deleteGroup['is_default'] == 20)
		    {
			    $this->where(['company_id'=>$company_id,'group_id'=>$transfer_group_id])->setField('is_default',20);
		    }

//            更新相关用户部门信息
		    $waitUpdateMembers = M('member')->where(['company_id'=>$company_id,'type'=>1,"find_in_set('{$deleteGroup['group_id']}',group_id)"])->field('member_id,group_id')->select();

		    foreach($waitUpdateMembers as &$v1)
		    {
			    $groupIds = explode(',',$v1['group_id']);

			    $v1['group_id'] = implode(',',array_unique(array_merge(array_diff($groupIds, [$deleteGroup['group_id']]),[$transfer_group_id])));

			    M('member')->save($v1);
		    }

		    $result = ['errcode'=>0,'msg'=>L('DELETE_SUCCESS')];
	    }
	    else
        {
            $result = ['errcode'=>1,'msg'=>L('DELETE_FAILED')];
	    }

	    return $result;
    }

	public function getParentIds($data,$id,$level = 1)
	{
		$function = __FUNCTION__;

		static $parentIds = [];

		foreach($data as $k => $v)
		{
			if ($v['group_id'] == $id)
			{
				$v['level'] = $level;

				if($v['parent_id'] > 0)
				{
					$parentIds[] = $v['parent_id'];
				}

				unset($data[$k]);

				$this->$function($data,$v['parent_id'],$level+1);
			}
		}

		sort($parentIds);

		return $parentIds;
	}

	/*
	* 更新子部门的层级
	* @param int    $parent_id  父级部门ID
	* @param int    $level      父级部门层级
	*/
	public function updateChildLevel($parent_id,$level)
	{
		$function = __FUNCTION__;

//	    查询是否存在子级
		$child_id = M('group')->where(['parent_id'=>['in',$parent_id]])->field('group_id')->select();

//	    存在子级 - 更新子级层级
		if($child_id)
		{
			$level++;

			$child_id = array_column($child_id,'group_id');

			M('group')->where(['group_id'=>['in',$child_id]])->setField(['level'=>$level]);

			$this->$function($child_id,$level);
		}
	}
}

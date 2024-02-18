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
namespace Index\Model;

use Common\Model\BasicModel;

class RouteModel extends BasicModel
{
    protected $pk = 'route_id';

    protected $tableName = 'route';


    public function updateRoute($channel,$data,$company_id)
    {
        $where = ['company_id'=>$company_id,'channel'=>$channel];

        $id = $this->where($where)->getField($this->pk);

        if($id > 0)
        {
            $result = $this->where([$this->pk=>$id])->save($data);
        }
        else
        {
            $result = $this->add($data);
        }

        if($result > 0)
        {
            $return = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'url'=>U('Route/index')];
        }
        else
        {
            $return = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
        }

        return $return;
    }

    /* 检测某个字段值是否存在 */
    public function isExistedByField($where = [])
    {
        $result = $this->where($where)->getField($this->pk);

        return $result;
    }
}

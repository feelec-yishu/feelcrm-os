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

class MenuModel extends BasicModel
{
    protected $pk = 'menu_id';

    protected $tableName = 'menu';

    protected $orderby = array('orderby'=>'asc');
   
	protected $cacheTime = 86400;

    public function checkAuth($auth) 
	{
        $data = $this->fetchAll();

        foreach ($data as $row) 
		{
            if ($auth == $row['menu_action']) 
			{
                return true;
            }
        }

        return false;
    }
}

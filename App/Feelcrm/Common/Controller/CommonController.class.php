<?php
namespace Common\Controller;

use Think\Controller;

use Think\Crypt\Driver\Crypt;

class CommonController extends Controller
{
    /* FeelDesk模块初始化
    * @param $source        来源
    * @param $user          登录用户信息
    * @param $user_id       登录用户ID
    * @param $company_id    登录用户所属公司ID
    * @param $token         唯一登录token
    * @param $lang          当前语言
    * @param $sms           短信开关
    */
    public function feelDeskInit($source,&$user,&$user_id,&$company_id,&$token,&$lang,&$sms,&$company,$systemHost = '')
    {
        if(!session('?'.$source))
        {
	        $host = C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN');

	        $url = $host."/u-login";
            if(in_array($source,['mobile']))
            {
	            $url = $host.'/m-login';
            }
            else if($systemHost == 'CrmMobile')
            {
                $url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN').'/Mobile';
            }
			if(!$systemHost)
			{
				$this->redirect($url);
			}
			else
			{
				header("location:".$url);die;
			}
        }
        else
        {
            $user = session($source);

            $user_id = $user['member_id'];

            $company_id = $user['company_id'];

	        session('company_id',$company_id);

	        $company = M('company')->where(['company_id'=>$company_id,'closed'=>0])->find();

	        session('company',$company);

	        /*if(!$company)
	        {
		        session('[destroy]');

		        if(!$systemHost)
		        {
			        $this->error(L('MERCHANT_NOT'),'Login/index',30);
		        }
		        else
		        {
			        $this->error(L('MERCHANT_NOT'),C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/login/index",30);
		        }
	        }*/

//            检查是否有相应语言版本权限
            if(!$lang = checkLangAuth()) $this->_empty();

            if(in_array($source,['index','mobile','wechat']))
            {
	            D('FeelRoleAuth')->getMenuIdsByRoleId($user['role_id'],'ticket');

                if($company['crm_auth'] == 10)
                {
                    D('FeelRoleAuth')->getMenuIdsByRoleId($user['role_id'],'crm');
                }

                $groupIds = M('member')->where(['member_id'=>$user['member_id']])->getField('group_id');

                if($groupIds)
                {
//                	部门 - 系统权限
	                $groupAuth = M('group')->where(['group_id'=>['in',$groupIds]])->field('ticket_auth,crm_auth')->select();

	                $groupTicketAuth = $groupCrmAuth  = 0;

	                foreach($groupAuth as $gv)
	                {
		                if($gv['crm_auth'] == 10)
		                {
			                $groupCrmAuth = 1;
		                }
	                }

	                if(in_array(MODULE_NAME,['Index','Mobile','Weixin']))
	                {
		                if(($company['ticket_auth'] != 10 || $groupTicketAuth != 1) && CONTROLLER_NAME == 'Index' && in_array(MODULE_NAME,['Mobile','Weixin']))
		                {
			                if(MODULE_NAME == 'Mobile')
			                {
				                $url = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN')."/CrmMobile";
			                }

			                header("location:".$url);die;
		                }

		                $hasAuth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'',$user['role_id'],'ticket');
	                }
	                else if(in_array(MODULE_NAME,['Crm','CrmMobile','CrmWeixin']) && $groupCrmAuth == 1)
	                {
		                $hasAuth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'',$user['role_id'],'crm');
	                }
	                else
	                {
		                $hasAuth = false;
	                }

	                if(!$hasAuth)
	                {
		                if(IS_AJAX)
		                {
			                $this->ajaxReturn(['status'=>0,'msg'=>L('NO_OPERATE')]);
		                }
		                else
		                {
			                $this->error(L('NO_AUTH'));die;
		                }
	                }

	                $groupSystemAuth = ['ticket_auth'=>$groupTicketAuth,'crm_auth'=>$groupCrmAuth];

	                session('GROUP_SYSTEM_AUTH_'.$company_id.'_'.$user['member_id'],$groupSystemAuth);

	                $this->assign('groupSystemAuth',$groupSystemAuth);
                }
                else
                {
					$this->_empty();
                }
            }

            $token = $company['login_token'];

            $user['firmLogo'] = $company['logo'];//公司LOGO

//            短信开关
            $sms = M('Sms')->where(['company_id'=>$company_id])->getField('is_open');

//            移动用户端底部导航权限
            if(in_array($source,['mobile','wechat']))
            {
                $user['menuAuth']['isCenterAuth'] = D('RoleAuth')->checkRoleAuthByMenu($user['company_id'],'setting/index',$user['role_id']);
            }

//            更新用户活动时间
            M('member')->where(['member_id'=>$user_id])->setField('last_active_time',time());

            session($source,$user);

            if($lang == 'zh-hans-cn') $lang = 'zh-cn';

            $this->assign('lang',$lang);

            $this->assign($source,$user);

            $systemName = I('get.system') ? I('get.system') : MODULE_NAME;

            $this->assign('system',$systemName);

            $this->assign('controllerAndAction','/'.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
        }
    }


    public function customerInit($id,&$firm,&$firm_id)
    {
        $firm_id = $id;

        $firm = D('Firm')->where(['firm_id'=>$id])->find();
    }


    public function _empty()
    {
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码

        $this->display("Public:404");
    }
}

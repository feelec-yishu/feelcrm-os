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

use  Think\Controller;

use  Crm\Controller\PublicController;

use Think\Crypt\Driver\Crypt;

class LoginController extends Controller
{
	public function index()
	{
		if(session('?index'))
		{
			header('Location: ' . U('index/index'));

			exit();
		}
		else
		{
            $this->assign('language',getLanguage());

            $this->assign('lang',strtolower(cookie('think_language')));

			$this->display();
		}
	}



//    登录
	public function loging()
	{
		$code = I('post.code');

		$check = new PublicController();

        $username = trim(I('post.username'));

        $password = md5(trim(I('post.password')));

		if(!$check->check_verify($code,1)) $this->ajaxReturn(['status'=>1,'msg'=>L('CODE_ERROR')]);

		if(!$username) $this->ajaxReturn(['status'=>1,'msg'=>L('USERNAME_NOTE')]);

		if(!$password) $this->ajaxReturn(['status'=>1,'msg'=>L('ENTER_PASSWORD')]);

		$member = D('Member')->getUserInfoByUsername($username,1);

		if((!$member) || $member['password'] != $password)
		{
			$this->ajaxReturn(['status'=>1,'msg'=>L('LOGIN_ERROR')]);
		}

		if($member['closed'] == 1)
		{
			$this->ajaxReturn(['status'=>1,'msg'=>L('DISABLED_ACCOUNT')]);
		}

//        更新登录时间
        $update = ['member_id'=>$member['member_id'],'login_time'=>NOW_TIME,'last_login_time'=>NOW_TIME,'last_active_time'=>NOW_TIME,'login_status'=>1,'login_ip'=>get_client_ip()];

		M("Member")->save($update);

		$member = M("Member")->field('password',true)->find($member['member_id']);

		session('index',$member);

        $setting = unserialize(M('Setting')->where(['key'=>'site'])->getField('value'));

//        登录超时，默认1小时过期
        $login_timeout = $setting['login_timeout'] ? $setting['login_timeout'] : 60;

        cookie('dt',Crypt::encrypt(NOW_TIME+$login_timeout*60,'LOGIN_TIME'));

		$this->ajaxReturn(['status'=>0,'msg'=>L('LOGIN_SUCCESS'),'url'=>U('Index/index')]);
	}



//    登录超时处理
    public function CrmisLoginTimeout()
    {
        $login_time = Crypt::decrypt(cookie('dt'),'LOGIN_TIME');//获取过期时间

        if(NOW_TIME > $login_time)
        {
            cookie(null,C('COOKIE_PREFIX'));

            session('[destroy]');

            $result = ['code'=>1,'msg'=>L('TIME_OUT'),'url'=>U('Login/index')];
        }
        else
        {
            $result = ['code'=>NOW_TIME.'^&^'.$login_time];
        }

        $this->ajaxReturn($result);
    }



	//退出登录
    public function logout()
	{
	    M('member')->where(['member_id'=>session('index.member_id')])->setField('login_status',0);

	    $msg = L('LOGIN_OUT_SUCCESS');

	    $lang = cookie('think_language');

        cookie('think_language',$lang,3600*24*365);

        session('[destroy]');

        $this->success($msg,U('Login/index'));
    }
}
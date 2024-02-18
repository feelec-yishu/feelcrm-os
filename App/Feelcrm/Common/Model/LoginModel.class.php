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

use Think\Cache\Driver\Redis;

class LoginModel extends BasicModel
{
	protected $autoCheckFields = false;

	/*
	* 移除处理人Redis队列中的指定处理人
	* @param int $member        当前用户信息
	* @param int $source        终端，index,mobile、wechat
	* @param bool $isLogout     是否退出登录
	*/
	public function removeDisposeInRedis($member,$source,$isLogout=false)
	{
		$redis = new Redis();

		$groupIds =  M('member')->where(['member_id'=>$member['member_id']])->getField('group_id');

		$groupIds = explode(',',$groupIds);

		foreach($groupIds as $v)
		{
			$key = $member['company_id'].'_'.$v.'_route_mail_disposeId';

			if($redis->lLen($key) > 0)
			{
				$redis->lRem("{$key}",$member['member_id'],0);

//				将退出的客服加入到离线列表中，以便下次登录后进入待分配队列，主要为了解决浏览器刷新断线的问题
				$redis->lRem($member['company_id'].'_'.$v.'_route_offline',$member['member_id'],0);

				$redis->rPush($member['company_id'].'_'.$v.'_route_offline',$member['member_id']);
			}
		}

		if($isLogout)
		{
			M('member')->save(['member_id'=>$member['member_id'],'login_status'=>0,'offline_time'=>time()]);

			$lang = cookie('think_language');

			cookie('think_language',$lang,3600*24*365);

			session($source,null);
		}
	}


	/*
	* 登录
	* @param string    $source 用户session名称
	* @param int       $type   用户类型
	* @param string    $token  登录token
	*/
	public function login($source,$type,$token = '',$open_id = '',$isCheckClosed = false)
	{
		$data = I('post.data');

		$account = trim($data['username']);

		$password = trim($data['password']);

		$isClient = isset($data['isClient']) ? $data['isClient'].'-' : '';

		if(!$account)
		{
			return ['status'=>0,'msg'=>L('ENTER').L('ACCOUNT'),'id'=>$isClient.'username'];
		}

		if(!$password)
		{
			return ['status'=>0,'msg'=>L('ENTER_PASSWORD'),'id'=>$isClient.'password'];
		}

//		仅用于微信端，会员端验证登录账号是否与Login token 所属的公司匹配
		if(C('NEED_TOKEN'))
		{
			if($token && in_array($source,['customer','wechat','wuser']))
			{
				$company_id = M('company')->where(['login_token'=>$token])->getField('company_id');

				if(!$company_id) return ['status'=>0,'msg'=>'Token Error'];
			}
		}
		else
		{
			$company_id = 0;
		}

		$member = D('Member')->getUserInfoByUsername($account,$type,$company_id,true);

		if(!$member || $member['password'] != md5($password))
		{
			return ['status'=>0,'msg'=>L('LOGIN_ERROR'),'id'=>$isClient.'username'];
		}

		if($member['closed'] == 1)
		{
			return ['status'=>0,'msg'=>L('DISABLED_ACCOUNT'),'id'=>$isClient.'username'];
		}

		$update = [
			'member_id'         => $member['member_id'],
			'login_time'        => NOW_TIME,
			'last_login_time'    => NOW_TIME,
			'last_active_time'  => NOW_TIME,
			'login_status'      => 1,
			'login_ip'          => get_client_ip()
		];

		if($open_id)
		{
			$update['open_id'] = $open_id;

			$update2 = ['open_id'=>$open_id,'member_id'=>['neq',$member['member_id']]];
		}

		$result = M("Member")->save($update);

		if(isset($update2) && $result !== false)
		{
//			置空相同open_id的其他账号
			D("Member")->where($update2)->setField('open_id','');
		}

		$member = D("Member")->where(['member_id'=>$member['member_id']])->field('password',true)->find();

		session($source,$member);

//		记住密码
		if(isset($data['remember']) && $data['remember'] === 'on')
		{
			cookie('user_login_cache',$data,['expire'=>3600 * 24 * 30]);
		}
		else
		{
			cookie('user_login_cache',null);
		}

		return ['status'=>2,'msg'=>L('LOGIN_SUCCESS'),'sort'=>$member['member_id'],'csort'=>$member['company_id']];
	}


	public function logout($member_id,$source)
	{
		M('member')->where(['member_id'=>$member_id])->setField('login_status',0);

		session($source,null);

		$lang = cookie('think_language');

		cookie('think_language',$lang,3600*24*365);
	}
}

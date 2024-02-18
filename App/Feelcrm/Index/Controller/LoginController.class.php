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

use  Think\Controller;

class LoginController extends Controller
{
	public function index()
	{
		if(session('?index'))
		{
			redirect('/u-home');
		}
		else
		{
			$login = cookie('user_login_cache');

			$this->assign('language',getLanguage());

            $this->assign('lang',strtolower(cookie('think_language')));

            $this->assign('login',$login);

			$this->display();
		}
	}


//    登录
	public function loging()
	{
		$result = D('Login')->login('index',1);

		$this->ajaxReturn($result);
	}


//	  退出登录
    public function logout()
	{
		D('Login')->removeDisposeInRedis(session('index'),'index',true);

        $this->success(L('LOGIN_OUT_SUCCESS'),'/u-login');
    }


    /*public function test()
    {
		$customer_detail = [
			'time'=>[
				'form_id'=>116,
				'form_content'=>'2021-04-22 00:00:00'
			],
			'duoxiang'=>[
				'form_id'=>117,
				'form_content'=>'CCC'
			],
			'name'=>[
				'form_id'=>7,
				'form_content'=>'1231546'
			],
			'test'=>[
				'form_id'=>141,
				'form_content'=>'A'
			],
			'testnumber'=>[
				'form_id'=>143,
				'form_content'=>''
			],
			'phone'=>[
				'form_id'=>134,
				'form_content'=>''
			],
			'testaaa'=>[
				'form_id'=>142,
				'form_content'=>''
			],
			'email'=>[
				'form_id'=>135,
				'form_content'=>''
			],
			'customer_status'=>[
				'form_id'=>2,
				'form_content'=>''
			],
			'industry'=>[
				'form_id'=>3,
				'form_content'=>''
			],
			'origin'=>[
				'form_id'=>4,
				'form_content'=>'电话营销'
			],
			'stage'=>[
				'form_id'=>36,
				'form_content'=>''
			],
			'region'=>[
				'form_id'=>31,
				'form_content'=>'1'
			],
			'address'=>[
				'form_id'=>8,
				'form_content'=>'123'
			],
			'website'=>[
				'form_id'=>37,
				'form_content'=>'http://www.baidu.com'
			],
			'customer_grade'=>[
				'form_id'=>6,
				'form_content'=>''
			],
			'remark'=>[
				'form_id'=>5,
				'form_content'=>'123'
			],
		];


	    $customer['company_id'] = 999;//工单所属公司ID

	    $customer['createtime'] = NOW_TIME;

	    $customer['creater_id'] = 1;

	    $customer['customer_prefix'] = 'C-';

	    $customer['customer_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).'999'), rand(0,9), 4));

	    $customer['from_type'] =  $customer['from_type'] ? $customer['from_type'] : 'PC';

	    if($customer_id = getCrmDbModel('customer')->add($customer))//客户
	    {
		    saveCustomerEmployeeId($customer_id, 999);

		    foreach ($customer_detail as &$v) {
			    $v['customer_id'] = $customer_id;

			    $v['company_id'] = 999;

			    if (is_array($v['form_content'])) {
				    $v['form_content'] = implode(',', $v['form_content']);
			    }

			    getCrmDbModel('customer_detail')->add($v); //添加客户详情

		    }

	    }
    }*/
}

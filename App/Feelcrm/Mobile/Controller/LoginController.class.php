<?php

namespace Mobile\Controller;

use  Think\Controller;

class LoginController extends Controller
{
	public function index()
	{
		if(session('?mobile'))
		{
			$this->redirect(U('index/index'));
		}
		else
		{
            $this->display();
		}
	}


//    登录
	public function loging()
	{
		$result = D('Login')->login('mobile',1);

		$this->ajaxReturn($result);
	}


//	  退出登录
	public function logout()
	{
		D('Login')->removeDisposeInRedis(session('mobile'),'mobile',true);

		$result = ['msg'=>L('LOGIN_OUT_SUCCESS'),'url'=>'/m-login'];

		$this->ajaxReturn($result);
	}
}
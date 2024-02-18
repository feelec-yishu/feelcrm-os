<?php

namespace Mobile\Controller;

use Think\Controller;

use Think\Verify;

class PublicController extends Controller
{
	/* 生成验证码 */
    public function verify()
	{
		session('[start]');

        $config = ['fontSize' => 19,'length' => 4,'imageH' => 39,'fontttf' => '5.ttf','useCurve' => true,'useNoise' => false,];

		$Verify = new Verify($config);

		$Verify->entry();
    }



	/* 验证码校验 */
	static function check_verify($code)
	{
		session('[start]');

		$verify = new \Think\Verify();

		$res = $verify->check($code);

		return $res;
	}
}
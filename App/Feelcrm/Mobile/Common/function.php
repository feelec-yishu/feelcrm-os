<?php
/**
 * 获取字符串第一个字符
 * @param string $str
 * @return string
 */
function getFirstStr($str)
{
	return strtoupper(mb_substr($str,0,1,'utf-8'));
}


/**
 * 获取CRM客户信息
 * @param string $type
 * @param string $get_id
 * @param int $company_id
 * @param array $formName
 * @return array
 */
function getCrmDetailList($type,$get_id,$company_id,$formName=[])
{
	$data = [];

	$form = getCrmDbModel('define_form')->where(['company_id'=>$company_id,'closed'=>0,'type'=>$type,'form_name'=>['in',$formName]])->field('form_id,form_name')->select();

	foreach($form as $k1=>&$v1)
	{
		$form_content= getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,$type.'_id'=>$get_id,'form_id'=>$v1['form_id']])->getField('form_content');

		$data[$v1['form_name']] = $form_content;
	}

	return $data;
}


function isLoginTimeout($login_time,$setTime)
{
	$index = session('mobile');

	$loginTimes = intval((NOW_TIME - $login_time)/60);

	if($loginTimes > (int) $setTime)
	{
		session('mobile',null);

		header('Location: ' . U('Login/index'));

		exit();
	}
}
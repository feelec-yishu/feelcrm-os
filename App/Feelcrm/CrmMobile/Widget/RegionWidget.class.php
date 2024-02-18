<?php
namespace CrmMobile\Widget;

use Think\Controller;

class RegionWidget extends Controller
{	
	public function getRegion($FormName,$defineForm,$reg = '')
	{		
		$this->assign('FormName',$FormName);

		$this->assign('defineForm',$defineForm);

		$form_name = $defineForm['form_name'];

		$this->assign('form_name',$form_name);

		$this->assign('reg',$reg);
		
		$this->display('Widget:getRegion');
	}
	
	public function getRegionEdit($FormName,$defineForm,$reg = '',$Datainfo)
	{
		$this->assign('FormName',$FormName);
		
		$this->assign('defineForm',$defineForm);

		$form_name = $defineForm['form_name'];

		$this->assign('form_name',$form_name);

		$this->assign('reg',$reg);
		
		$this->assign('Datainfo',$Datainfo);
		
		$this->display('Widget:getRegionEdit');
	}
}
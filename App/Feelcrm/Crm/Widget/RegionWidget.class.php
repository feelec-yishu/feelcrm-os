<?php
namespace Crm\Widget;

use Think\Controller;

class RegionWidget extends Controller
{	
	public function getRegion($FormName,$form_name,$reg = '')
	{		
		$this->assign('FormName',$FormName);
		
		$this->assign('form_name',$form_name);
		
		$this->assign('reg',$reg);
		
		$this->display('Widget:getRegion');
	}
	
	public function getRegionEdit($FormName,$form_name,$reg = '',$Datainfo)
	{
		$this->assign('FormName',$FormName);
		
		$this->assign('form_name',$form_name);
		
		$this->assign('reg',$reg);
		
		$this->assign('Datainfo',$Datainfo);
		
		$this->display('Widget:getRegionEdit');
	}
}
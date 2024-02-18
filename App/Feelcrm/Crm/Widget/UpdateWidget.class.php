<?php
namespace Crm\Widget;

use Think\Controller;

class UpdateWidget extends Controller
{	
	public function updateFormContent($dataInfo,$infoId,$formData,$formType,$t,$ft,$reg = '')
	{		
		$this->assign('dataInfo',$dataInfo);
		
		$this->assign('infoId',$infoId);
		
		$this->assign('formData',$formData);
		
		$this->assign('formType',$formType);
		
		$this->assign('t',$t);
		
		$this->assign('ft',$ft);
		
		$this->assign('reg',$reg);
		
		$this->display('Widget:updateFormContent');
	}

	public function selectTextForm($formType,$formData,$t,$dataInfo=[])
	{
		$this->assign('formType',$formType);

		$this->assign('formData',$formData);

		$this->assign('t',$t);

		$this->assign('dataInfo',$dataInfo);

		$this->display('Widget:selectTextForm');
	}
}
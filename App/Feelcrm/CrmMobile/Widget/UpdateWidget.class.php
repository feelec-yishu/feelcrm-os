<?php
namespace CrmMobile\Widget;

use Think\Controller;

class UpdateWidget extends Controller
{
	public function selectTextForm($formType,$formData,$t,$dataInfo=[])
	{
		$this->assign('formType',$formType);

		$this->assign('formData',$formData);

		$this->assign('t',$t);

		$this->assign('dataInfo',$dataInfo);

		$this->display('Widget:selectTextForm');
	}
}
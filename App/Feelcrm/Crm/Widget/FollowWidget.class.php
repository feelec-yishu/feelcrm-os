<?php
namespace Crm\Widget;

use Think\Controller;

class FollowWidget extends Controller
{
	public function createFollow()
	{
		$this->display('Widget:createFollow');
	}

	public function followList($follow,$dataInfo,$type='')
	{		
		$this->assign('follow',$follow);

		$this->assign('dataInfo',$dataInfo);

		$this->assign('followtype',$type);
		
		$this->display('Widget:followList');
	}

	public function fileList($files)
	{
		$this->assign('files',$files);

		$this->display('Widget:fileList');
	}
}
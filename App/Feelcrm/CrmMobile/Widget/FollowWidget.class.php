<?php
namespace CrmMobile\Widget;

use Think\Controller;

class FollowWidget extends Controller
{
	public function followList($follow)
	{		
		$this->assign('follow',$follow);
		
		$this->display('Widget:followList');
	}

	public function fileList($files)
	{
		$this->assign('files',$files);

		$this->display('Widget:fileList');
	}
}
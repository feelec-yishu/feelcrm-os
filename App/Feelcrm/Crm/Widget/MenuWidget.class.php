<?php
namespace Crm\Widget;

use Think\Controller;

class MenuWidget extends Controller
{
	protected static $company;

	protected static $member;

	public function _initialize()
	{
		self::$company = session('company');

		self::$member = session('index');
	}


	public function menu()
    {
        $lang = cookie('think_language');

	    $allMenus = getCrmDbModel('menu')->where(['apply' => 10,'is_show'=>1])->order('orderby asc')->select();

	    $allMenus = fetchAll($allMenus,'menu_id');

//        当前角色权限
	    $auth = S('menuData/crm_role_auth_'.self::$company['company_id'].'_'.self::$member['role_id']);

        //处理权限
        $menu = [];

        foreach($allMenus as $k=>&$v)
        {
            if($lang == 'en-us') $v['menu_name'] = $v['name_en'];

            if($lang == 'ja-jp') $v['menu_name'] = $v['name_jp'];

	        if(in_array($k,$auth))
	        {
		        $menu[$k] = $allMenus[$k];
	        }
        }

		$menusCrm = getMenuTree($menu);

        $this->assign('hour',date('H',time()));

        $this->assign('menusCrm',$menusCrm);

		$this->assign('language',getLanguage(self::$company));

		$isExamineCustomerAuth = D('RoleAuth')->checkRoleAuthByMenu(self::$company['company_id'],'customer/examine',self::$member['role_id'],'crm');

		$this->assign('isExamineCustomerAuth',$isExamineCustomerAuth);

        $this->display('Widget:menu');
    }
}
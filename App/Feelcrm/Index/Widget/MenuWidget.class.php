<?php
namespace Index\Widget;

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

	    $allMenus = D('Menu')->where(['apply'=>10,'is_show'=>1])->order('orderby asc')->fetchAll();

//        当前角色权限
	    $auth = S('menuData/ticket_role_auth_'.self::$company['company_id'].'_'.self::$member['role_id']);

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

        $menuList = getMenuTree($menu);

	    $this->assign('crmAuth',1);

//        组织架构权限
        $organizeAuth = D('RoleAuth')->checkRoleAuthByMenu(self::$company['company_id'],'organize/index',self::$member['role_id']);

        if($organizeAuth)
        {
            session('organize_menu'.self::$member['role_id'],$menuList[$organizeAuth]);

            $this->assign('organizeAuth',1);
        }

//        设置权限
        $settingAuth = D('RoleAuth')->checkRoleAuthByMenu(self::$company['company_id'],'setting/index',self::$member['role_id']);

        if($settingAuth)
        {
            session('setting_menu'.self::$member['role_id'],[$menuList[$settingAuth]]);

            $this->assign('settingAuth',1);
        }

//        接口配置权限
        $interfaceAuth = D('RoleAuth')->checkRoleAuthByMenu(self::$company['company_id'],'interface/index',self::$member['role_id']);

        if($interfaceAuth)
        {
            session('interface_menu'.self::$member['role_id'],$menuList[$interfaceAuth]);

            $this->assign('interfaceAuth',1);
        }

//        系统消息
        $message = D('SystemMessage')
		->where(['company_id'=>self::$company['company_id'],'recipient_id'=>self::$member['member_id'],'recipient'=>1,'read_status'=>1])
        ->limit(0,99)
        ->count('msg_id');

//        是否同步FeelChat
        $feelChat = M('feelchat')->where(['company_id'=>self::$company['company_id']])->field('close,is_sync')->find();

    /*
			$ssoData = [
				'appId'         => '',
				'timestamp'     => '',
				'signature'     => '',
				'user_type'     => 'user',
				'terminal'      => 'user',
				'username'      => '18284594895',
				'callback_url'  => 'ticket/template'
			];

			$ssoDataStr = http_build_query($ssoData);

			$this->assign('ssoData',$ssoDataStr);
	*/

//        登录状态
	    $login_status = M('member')->where(['member_id'=>self::$member['member_id']])->getField('login_status');

	    $this->assign('login_status',$login_status);

        $this->assign('hour',date('H',time()));

	    $this->assign('language',getLanguage(self::$company));

        $this->assign('countMessage',$message);

        $this->assign('feelChat',$feelChat);

        $this->display('Widget:menu');
    }


    public function ticket()
    {
        if(session('?ticket_menu'.self::$member['role_id']))
        {
            $this->assign('ticketMenu',session('ticket_menu'.self::$member['role_id']));
        }

        $this->display('Widget:ticket');
    }


    public function organize()
    {
        if(session('?organize_menu'.self::$member['role_id']))
        {
            $this->assign('organizeMenu',session('organize_menu'.self::$member['role_id']));
        }

        $this->display('Widget:organize');
    }


    public function setting()
    {
        if(session('?setting_menu'.self::$member['role_id']))
        {
            $this->assign('settingMenu',session('setting_menu'.self::$member['role_id']));
        }

        $this->display('Widget:setting');
    }


    public function interfaces()
    {
        if(session('?interface_menu'.self::$member['role_id']))
        {
            $this->assign('interfaceMenu',session('interface_menu'.self::$member['role_id']));
        }

        $this->display('Widget:interfaces');
    }
}

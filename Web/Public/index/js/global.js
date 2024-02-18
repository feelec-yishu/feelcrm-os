// +----------------------------------------------------------------------

// | FeelCRM开源客户管理系统

// +----------------------------------------------------------------------

// | 欢迎阅读学习系统程序代码，您的建议反馈是我们前进的动力

// | 开源版本仅供技术交流学习，请务必保留界面版权logo

// | 商业版本务必购买商业授权，以免引起法律纠纷

// | 禁止对系统程序代码以任何目的，任何形式的再发布

// | gitee下载：https://gitee.com/feelcrm_gitee

// | github下载：https://github.com/feelcrm-github

// | 开源官网：https://www.feelcrm.cn

// | 成都菲莱克斯科技有限公司 版权所有 拥有最终解释权

// +----------------------------------------------------------------------

$(function()
{
    //setInterval('getTicketNumber()',1000*10);

    $('.feeldesk-menu-first').unbind('click').click(function()
    {
        if($(this).find('.feeldesk-menu-second').css("display") === 'none')
        {
            $(this).find('.feeldesk-leftMenu-menu1 .layui-icon').html('&#xe61a;');
        }
        else
        {
            $(this).find('.feeldesk-leftMenu-menu1 .layui-icon').html('&#xe602;');
        }

        $(this).find('.feeldesk-menu-second').slideToggle('fast');

        $(this).find('.feeldesk-leftMenu-menu1').toggleClass('current');

        event.stopPropagation();
    });

    $('.feeldesk-menu-second').unbind('click').click(function()
    {
        event.stopPropagation();

        var thirdMenu = $(this).find('.feeldesk-menu-third');

        if(thirdMenu.length > 0)
        {
            $('.feeldesk-leftMenu-menu2').removeClass('current');

            if(thirdMenu.css("display") === 'none')
            {
                $(this).find('.feeldesk-leftMenu-menu2 .layui-icon').html('&#xe61a;');
            }
            else
            {
                $(this).find('.feeldesk-leftMenu-menu2 .layui-icon').html('&#xe602;');
            }

            thirdMenu.slideToggle('fast');

            $(this).find('.feeldesk-leftMenu-menu2').addClass('current');
        }
    });
});

function feeldeskActive(obj,l)
{
    event.stopPropagation();

    if(l === 2)
    {
        $('.ticket-second-menu,.feeldesk-leftMenu-menu2,.ticket-third-menu').removeClass('current');
    }
    else
    {
        $('.ticket-third-menu,.ticket-second-menu,.feeldesk-leftMenu-menu2').removeClass('current');

        $(obj).parents('.feeldesk-menu-third').prev('.feeldesk-leftMenu-menu2').addClass('current');
    }

    $(obj).addClass('current');

    showRightLoading();
}


function hideRightLoading()
{
    $('.global-loading').remove();
}


function showRightLoading()
{
    $('.ticket-main-right').after("<div class='global-loading'><i class='layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop'></i></div>");
}

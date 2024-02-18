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

/**
 * 倒计时
 * @param:: string mbinput
 */

$(function ()
{
    var cookie_value = getCookieValue('secondsremained');

    if(cookie_value > 0) countDownTime("send_code");
});

/*
 * 添加cookie的值
 * @param name cookie名
 * @param value cookie值
 * @param expire 有效期
 */
function addCookie(name,value,expire)
{
    //判断是否设置过期时间,0代表关闭浏览器时失效
    if(expire>0)
    {
        var date=new Date();

        date.setTime(date.getTime()+expire*1000);

        $.cookie(name, value, {expires: date});
    }
    else
    {
        $.cookie(name, value);
    }
}

/*修改cookie的值*/
function editCookie(name,value,expire)
{
    if(expire>0)
    {
        var date=new Date();

        date.setTime(date.getTime()+expire*1000); //单位是毫秒

        $.cookie(name, value, {expires: date});
    }
    else
    {
        $.cookie(name, value);
    }
}

function countDownTime(btn) 
{
    var obj = $('#'+btn);

    var countdown = getCookieValue('secondsremained');

    if(countdown == 0)
    {
        obj.removeAttr("disabled");

        obj.val(language.GET_CODE);

        obj.css({"background-color":"#fff","color":"rgb(102, 169, 219)",'border-color':'rgb(102, 169, 219)'});

        return;
    }
    else
    {
        obj.attr("disabled", "true");

        obj.val(language.RESEND+"("+countdown+")");

        obj.css({"background-color":"#d4d7d9","color":"#fff","border-color":'#d4d7d9'});

        countdown--;

        editCookie("secondsremained",countdown,countdown+2);
    }

    setTimeout(function(){ countDownTime(btn)}, 1000);
}

/*获取cookie*/
function getCookieValue(name)
{
    return $.cookie(name);
}
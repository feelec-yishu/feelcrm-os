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

$(function ()
{
    var cookie_value = getCookieValue('downTime');

    if(cookie_value > 0) countDownTime("sendCodeBtn");
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

/*
* 修改cookie的值
* @param name cookie名
* @param value cookie值
* @param expire 有效期
*/
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

/*获取cookie*/
function getCookieValue(name)
{
    return $.cookie(name);
}


/**
 * 开始倒计时
 * @param:: string mbinput
 */

function countDownTime(btn)
{
    var obj = $('#'+btn);

    var countdown = getCookieValue('downTime');

    if(countdown == 0)
    {
        obj.removeAttr("disabled");

        obj.val(language.GET_CODE);

        return;
    }
    else
    {
        obj.attr("disabled", "true");

        obj.val(language.RESEND+"("+countdown+")");

        countdown--;

        editCookie("downTime",countdown,countdown+2);
    }

    setTimeout(function(){ countDownTime(btn)}, 1000);
}


function sendVerifyCode()
{
    var phone = $("#mobile").val();

    var imgCode = $("#imgCode").val();

    if(!(/^1[345789]\d{9}$/.test(phone)))
    {
        layer.msg(language.MOBILE_FORMAT_ERROR,{time:1000,offset:'100px'});
    }
    else if(!imgCode)
    {
        layer.msg(language.ENTER_IMAGE_CODE,{time:1000,offset:'100px'});
    }
    else
    {
        var loading = layer.load(2,{offset:'100px'});

        $.post("/"+moduleName+"/Register/sendVerifyCode",{'mobile':phone,'imgCode':imgCode},function(data)
        {
            layer.close(loading);

            if(data.status == 0)
            {
                addCookie("downTime",60,60);

                countDownTime('sendCodeBtn');

                layer.msg(data.msg,{time:1000,offset:'100px'});
            }
            else
            {
                layer.msg(data.msg,{time:1000,offset:'100px'});
            }
        });
    }
}


/*刷新验证码*/
function getVerifyImage()
{
    var verifyURL = "/Public/regVerify";

    var time = new Date().getTime();

    $("#verifyImg").attr({"src" : verifyURL + "?" + time});
}

function register()
{
    var loading = layer.load(2,{offset:'100px'});

    $.post("/"+moduleName+"/Register/create",$('#registerForm').serialize(),function(data)
    {
        if(data.status == 0)
        {
            layer.close(loading);

            layer.msg(data.msg,{time:1000,offset:'100px'},function()
            {
                getVerifyImage();
            });
        }
        else
        {
            layer.msg(data.msg,{time:1000,offset:'100px'},function()
            {
                $.cookie('downTime',null);

                window.location.href = data.url;
            });
        }
    },'JSON');
}


$('#surePassword').blur(function()
{
    var password = $('#password').val();

    var surepwd = $('#surePassword').val();

    if(password != surepwd)
    {
        layer.msg(language.PASSWORD_NO_TWO,{time:1000,offset:'100px'});
    }
});

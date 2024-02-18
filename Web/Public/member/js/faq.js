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
var loginSlideVerify,slideVerify;

$(function ()
{
    var tab = $('.tab-item');

    var lw = parseInt(tab.eq(0).width() / 2);

    var rw = parseInt(tab.eq(2).width() / 2);

    tab.eq(0).css('left','-'+lw+'px');

    tab.eq(2).css('right','-'+rw+'px');

    $('#login,#reg-login,#reset-login').on('click',function()
    {
        $('.faq-shade,.faq-login').fadeIn();

        loginSlideVerify = new window.slideVerifyPlug('#login-slide-verify',
        {
            wrapWidth:'100%',//设置 容器的宽度 ，默认为 350 ，也可不用设，你自己css 定义好也可以，插件里面会取一次这个 容器的宽度
            initText:language.SLIDE_VERIFY,
            sucessText:language.VERIFY_PASS//设置 验证通过 显示的文字
        });
    });

    $('#close-login').on('click',function()
    {
        $('.faq-shade,.faq-login').fadeOut('700',function ()
        {
            loginSlideVerify.resetVerify();
        });
    });

    $("#login-form input").keydown(function(e)
    {
        if(e.keyCode===13) login();
    });

    $("#search").bind("input propertychange",function()
    {
        var keyword = $(this).val();

        if(keyword.length > 0)
        {
           var url = '/c-faq-search';
           var durl = '/c-faq-problem-detail';

            if(moduleName === 'Muser')
            {
                url = '/cm-faq-search';
                durl = '/cm-faq-problem-detail';
            }
            else if(moduleName === 'Wuser')
            {
                url = '/cw-faq-search';
                durl = '/cw-faq-problem-detail';
            }

            $.post(url,{keyword: $.trim(keyword)},function(result)
            {
                var problem = '';

                if(result.data.length > 0)
                {
                    $.each(result.data,function(k,v)
                    {
                        problem += "<div class='search-problem-item'><a href='"+durl+"/"+v.problem_id+"'>"+v.title+"</a></div>";
                    });

                    $(".search-result").html(problem).show();
                }
                else
                {
                    problem = '<div class="search-problem-item no-data"><a href="javascript:">没有找到与<span class="blue9"> '+keyword+' </span>相关的问题</a></div>';

                    $(".search-result").html(problem).show();
                }

            }, 'JSON');
        }
        else
        {
            $(".search-result").html('').hide();
        }
    });

    slideVerify = new window.slideVerifyPlug('#slide-verify',
    {
        wrapWidth:'100%',
        initText:language.SLIDE_VERIFY,
        sucessText:language.VERIFY_PASS
    });
});

var login = function ()
{
    var verify = loginSlideVerify.slideFinishState;

    if(false === verify)
    {
        layer.tips(language.SLIDE_VERIFY,'#login-slide-verify',{tips:3,time: 5000, skin:'login-tips'});

        return false;
    }

    var loading = layer.load(2,{offset:'180px'});

    var url = '/c-login';

    if(moduleName === 'Muser')
    {
        url = '/cm-login';
    }
    else if(moduleName === 'Wuser')
    {
        url = '/cw-login';
    }

    $.post(url,$('#login-form').serialize(),function(data)
    {
        if(data.status !== 2)
        {
            layer.close(loading);

            layer.tips(data.msg,'#'+data.id,{tips:3,time: 2000, skin:'login-tips'});

            loginSlideVerify.resetVerify();
        }
        else
        {
            window.location.reload();
        }
    },'JSON');
};

var getVerifyCode = function (source)
{
    var loading = layer.load(2,{offset:'15vw'});

    var verify = slideVerify.slideFinishState;

    if(false === verify)
    {
        feelDeskAlert(language.SLIDE_VERIFY);

        layer.close(loading);

        return false;
    }

    var url = {query:'/c-query-ticket-code',reg:'/c-reg-code',reset:'/c-reset-code'};

    if(moduleName === 'Muser')
    {
        url = {query:'/cm-query-ticket-code',reg:'/cm-reg-code',reset:'/cm-reset-code'};
    }
    else if(moduleName === 'Wuser')
    {
        url = {query:'/cw-query-ticket-code',reg:'/cw-reg-code',reset:'/cw-reset-code'};
    }

    var param = {username:$("div[id='username']").find('input').val()};

    if(source === 'reset')
    {
        param.way = $("input[name='way']").val();
    }

    $.post(url[source],param,function(result)
    {
        if(result.status !== 2)
        {
            feelDeskAlert(result.msg);
        }
        else
        {
            setCookie(countdown_name,60,60);

            countDownTime('code',countdown_name);

            feelDeskAlert(result.msg);

            slideVerify.resetVerify();
        }

        layer.close(loading);

    },'JSON');
};

var submitRequest = function (source)
{
    var loading = layer.load(2,{offset:'15vw'});

    var verify = slideVerify.slideFinishState;

    if(false === verify)
    {
        feelDeskAlert(language.SLIDE_VERIFY);

        layer.close(loading);

        return false;
    }

    var url = {query:'/c-faq-ticket',reg:'/c-reg-submit',reset:'/c-reset-submit',faq:'/c-faq',ticket:'/c-query-my-ticket'};

    if(moduleName === 'Muser')
    {
        url = {query:'/cm-faq-ticket',reg:'/cm-reg-submit',reset:'/cm-reset-submit',faq:'/cm-faq',ticket:'/cm-query-my-ticket'};
    }
    else if(moduleName === 'Wuser')
    {
        url = {query:'/cw-faq-ticket',reg:'/cw-reg-submit',reset:'/cw-reset-submit',faq:'/cw-faq',ticket:'/cw-query-my-ticket'};
    }

    $.post(url[source],$('#'+source+'-form').serialize(),function (result)
    {
        if(result.status !== 2)
        {
            feelDeskAlert(result.msg);
        }
        else
        {
            if(source === 'reg')
            {
                result.url = url.faq;

                feelDeskAlert(result.msg,result);

                return;
            }

            if(source === 'query')
            {
                result.url = url.ticket+'?username='+$("div[name='username']").find('input').val();
            }

            window.location.href = result.url;
        }

        layer.close(loading);
    });
};

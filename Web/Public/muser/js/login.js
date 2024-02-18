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
var slideVerify;

$(function ()
{
    $('#lang').on('click',function()
    {
        $(this).find('i').toggleClass('icon-up');

        $('#language').slideToggle('fast');
    });

    $('#contentImages').find('img').each(function(k,v)
    {
        $(this).attr('onclick',"openPhotoSwipe("+k+",'contentImages')");
    });

//   回复列表、工单属性标签页的切换
    $('.detail-tab li').on('click',function()
    {
        var value = $(this).data('value');

        var ticketDetail = $('.ticket-detail');

        $(this).addClass('current').siblings('li').removeClass('current');

        $(".detail-main").hide().siblings(".detail-main[data-value='"+value+"']").show();

        $('#'+value).show().siblings('div').hide();

        var headerHeight,ticketMainHeight,detailTabHeight,detailMainHeight;

        var style;

        headerHeight = $('.header')[0].getBoundingClientRect().height;

        detailTabHeight = $('.detail-tab')[0].getBoundingClientRect().height;

        if(value == 'ticket-detail')
        {
            $(".ticket-detail-main").addClass('detail-info-main');

            ticketDetail.css({'height':'calc(100% - '+headerHeight+'px)','overflow-x':'hidden'});

            $('.detail-info-main').css({'height':'calc(100% - '+detailTabHeight+'px)','overflow-x':'hidden'});
        }
        else
        {
            ticketMainHeight = headerHeight;

            ticketDetail.css({'height':'calc(100% - '+ticketMainHeight+'px)','overflow-x':'hidden'});

            style = {'height':'calc(100% - '+detailTabHeight+'px)','overflow-x':'hidden'};

            $(".ticket-detail-main").css(style).removeClass('detail-info-main');
        }
    });

    slideVerify = new window.slideVerifyPlug('#slide-verify',
    {
        wrapWidth:'100%',
        initText:language.SLIDE_VERIFY,
        sucessText:language.VERIFY_PASS
    });

    $("#search").bind("input propertychange",function()
    {
        var keyword = $(this).val();

        if(keyword.length > 0)
        {
            var url = {search:'/c-faq-search',detail:'/c-faq-problem-detail'};

            if(moduleName === 'Muser')
            {
                url = {search:'/cm-faq-search',detail:'/cm-faq-problem-detail'};
            }
            else if(moduleName === 'Wuser')
            {
                url = {search:'/cw-faq-search',detail:'/cw-faq-problem-detail'};
            }

            $.post(url.search,{keyword: $.trim(keyword)},function(result)
            {
                var problem = '';

                if(result.data.length > 0)
                {
                    $.each(result.data,function(k,v)
                    {
                        problem += "<div class='search-problem-item'><a href='"+url.detail+"/"+v.problem_id+"'>"+v.title+"</a></div>";
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
});

var getVerifyCode = function (source)
{
    var loading = layer.load(2);

    var verify = slideVerify.slideFinishState;

    if(false === verify)
    {
        layer.msg(language.SLIDE_VERIFY);

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

    if(source == 'reset')
    {
        param.way = $("input[name='way']").val();
    }

    $.post(url[source],param,function(result)
    {
        layer.msg(result.msg);

        if(result.status == 2)
        {
            setCookie(countdown_name,60,60);

            countDownTime('code',countdown_name);

            slideVerify.resetVerify();
        }

        layer.close(loading);

    },'JSON')
};

var submitRequest = function (source)
{
    var loading = layer.load(2);

    var verify = slideVerify.slideFinishState;

    if(false === verify)
    {
        layer.msg(language.SLIDE_VERIFY);

        layer.close(loading);

        return false;
    }

    var url = {query:'/c-faq-ticket',reg:'/c-reg-submit',reset:'/c-reset-submit',ticket:'/c-query-my-ticket'};

    if(moduleName === 'Muser')
    {
        url = {query:'/cm-faq-ticket',reg:'/cm-reg-submit',reset:'/cm-reset-submit',ticket:'/cm-query-my-ticket'};
    }
    else if(moduleName === 'Wuser')
    {
        url = {query:'/cw-faq-ticket',reg:'/cw-reg-submit',reset:'/cw-reset-submit',ticket:'/cw-query-my-ticket'};
    }

    $.post(url[source],$('#'+source+'-form').serialize(),function (result)
    {
        if(result.status == 2)
        {
            if(source == 'reg')
            {
                layer.msg(result.msg,{time:1500},function()
                {
                    window.location.href = '/c-login';
                });
            }

            if(source == 'query')
            {
                result.url = url.ticket+'?username='+$("div[name='username']").find('input').val();
            }

            window.location.href = result.url;
        }
        else
        {
            layer.msg(result.msg);
        }

        layer.close(loading);
    });
};

var login = function (source)
{
    var verify = slideVerify.slideFinishState;

    if(false === verify)
    {
        layer.msg(language.SLIDE_VERIFY);

        return false;
    }

    var forms = $('#login-form');

    var loading = layer.load(2);

    var url = {login:'/c-log-in',home:'/c-home'};

    if(moduleName === 'Muser')
    {
        url = {login:'/cm-log-in',home:'/cm-home'};
    }
    else if(moduleName === 'Wuser')
    {
        url = {login:'/cw-log-in',home:'/cw-home'};
    }

    if(source == 'wechat')
    {
        url.login = forms.attr('action');
    }

    $.post(url.login,forms.serialize(),function(data)
    {
        if(data.status != 2)
        {
            layer.close(loading);

            layer.msg(data.msg);

            slideVerify.resetVerify();
        }
        else
        {
            window.location.href = url.home;
        }
    },'JSON');
};

/*
* 添加cookie的值
* @param name cookie名
* @param value cookie值
* @param expire 有效期
*/
function setCookie(name,value,expire)
{
    //判断是否设置过期时间,0代表关闭浏览器时失效
    if(expire > 0)
    {
        $.cookie(name,value,{expires: (1/86400)*expire});
    }
    else
    {
        $.cookie(name,value);
    }
}

function countDownTime(btn,name)
{
    var obj = $('#'+btn);

    var countdown = $.cookie(name);

    var timeout = setInterval(function ()
    {
        countdown--;

        if (countdown > 0)
        {
            setCookie(name,countdown,countdown);

            obj.val(language.RESEND+"("+countdown+")");
        }
        else
        {
            clearInterval(timeout);

            obj.removeAttr("disabled").val(language.GET_CODE).removeAttr('disabled style');
        }
    }, 1000);

    obj.attr("disabled", "true").val(language.RESEND+"("+countdown+")").css({"background-color":"#e5e5e5","color":"#999",'cursor':'not-allowed'});
}

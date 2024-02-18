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

var socket;

var isApp = false;

$(document).ready(function ()
{
//   禁用回车提交表单
    $('form').keypress(function(e)
    {
        if (e.which === 13) return false;
    });

//   询问框 -- 异步请求
    $(document).on("click", "a[load='async']", function (e)
    {
        e.preventDefault();

        var action = $(this).attr('href');

        var title = $(this).html();

        var type = $(this).data('type');

        var content;

        if(title === language.RESET)
        {
            content = language.SURE+title+"？（"+language.DEFAULT_PASSWORD+" <span class='red1'>123456</span>）";
        }
        else if(type === 'delete-ticket')
        {
            content = language.SURE_DELETE_TICKET;
        }
        else
        {
            content = language.SURE+title+"？";
        }

        layer.confirm(content,
        {
            skin:'ticket-window',
            title:false,
            closeBtn:false,
            offset:['40%'],
            btnAlign: 'c',
            btn:[language.CANCEL,language.SURE],
            yes:function(index)
            {
                layer.close(index);
            },
            btn2:function()
            {
                var loading = layer.load(2,{offset:['40%']});

                $.get(action,function(data)
                {
                    if(data.status === 0)
                    {
                        layer.close(loading);

                        layer.msg(data.msg,{time:3000,offset:['100px']});
                    }
                    else
                    {
                        layer.msg(data.msg,{time:1500,shift:0,offset:['100px']},function()
                        {
                            window.location.href = data.url;
                        });
                    }

                },'JSON');

                return false
            }
        });
    });

//   刷新验证码
    $("#verifyImg").unbind('click').click(function()
    {
        var verifyURL = "/Public/verify";

        var time = new Date().getTime();

        $(this).attr({"src" : verifyURL + "?" + time});
    });

    $("#submitLogin").unbind('click').click(function()
    {
        login();
    });

    layui.use(['laydate','form'], function()
    {
        var form = layui.form;

        $("select[lay-filter='priority']").siblings('.layui-form-select').find('.layui-anim').each(function()
        {
            $(this).find('dd').eq(0).prepend("<span class='general'></span>");

            $(this).find('dd').eq(1).prepend("<span class='urgent'></span>");

            $(this).find('dd').eq(2).prepend("<span class='high'></span>");

            $(this).find('dd').eq(3).prepend("<span class='low'></span>");
        });

        form.on("select(group)", function(data)
        {
            var options = '';

            var group_id = data.value;

            if(group_id > 0)
            {
                $.post("/"+moduleName+"/Customer/create",{is_change:group_id},function(data)
                {
                    options += "<option value='0'>"+language.SELECT_SERVER+"</option>";

                    for(var i in data)
                    {
                        options += "<option value="+data[i].member_id+">"+data[i].name+"</option>";
                    }

                    $('#customer').html(options);

                    form.render();

                },'JSON');
            }
            else
            {
                options += "<option value='0'>"+language.SELECT_SERVER+"</option>";

                $('#customer').html(options);

                form.render();
            }
        });

        /* 时间日期 */
        var laydate = layui.laydate;

        var option1 = {elem:'#remindDate', type:'datetime',format: 'yyyy-MM-dd HH:00', trigger: 'click', btns: ['clear', 'confirm']};

        var option2 = {elem:'#datetime', type:'datetime',range:'~',format: 'yyyy-MM-dd HH:00', trigger: 'click', btns: ['clear', 'confirm']};

        laydate.render(option1);

        laydate.render(option2);

        /* 提交 */
        $('#submitForm').unbind('click').click(function()
        {
            var load = layer.load(2,{offset:['40%']});

            var obj = $("#feeldeskForm");

            var action = obj.attr('action');

            $.post(action,obj.serialize(),function(data)
            {
                if(data.status === 0)
                {
                    layer.close(load);

                    layer.msg(data.msg,{time:1000,offset:['40%']});
                }
                else
                {
                    layer.msg(data.msg,{time:1000,shift:0,offset:['40%']},function()
                    {
                        window.location.href = data.url;
                    });
                }
            },'JSON');
        });
    });


//   radio表单
    $(".radio").unbind('click').on('click',function()
    {
        var radio = $(this);

        var hasNameRadio = $(this).hasClass('name-radio');

        $(this).toggleClass('feeldesk-form-checked').next('.feeldesk-option-panel').slideToggle('fast');

        $(this).parents('.feeldesk-form-item').siblings().find('.feeldesk-input').removeClass('feeldesk-form-checked').next('.feeldesk-option-panel').slideUp('fast');

        $(".radioPanel").find('li').unbind('click').on('click',function()
        {
            var value = $(this).data('value');

            var name = $(this).data('name');

            $(this).siblings('li').find('span.iconfont').removeClass('icon-radio-checked');

            $(this).find('span.iconfont').addClass('icon-radio-checked');

            $(this).parent('.radioPanel').slideToggle('fast').find("input").val(value);

            if(hasNameRadio) value = name;

            radio.find('span').text(value);
        });
    });

//   checkbox表单
    $(".checkbox").unbind('click').on('click',function()
    {
        $(this).toggleClass('feeldesk-form-checked').next('.feeldesk-option-panel').slideToggle('fast');

        $(this).parents('.feeldesk-form-item').siblings().find('.feeldesk-input').removeClass('feeldesk-form-checked').next('.feeldesk-option-panel').slideUp('fast');

        $(".checkboxPanel").find('li').on('click',function()
        {
            var value = $(this).data('value');

            $(this).find('span.iconfont').toggleClass('icon-checkbox-checked');

            var oldValue = $(this).parent('.checkboxPanel').prev('.checkbox').find('span').text();

            var content;

            if($(this).find('span.iconfont').hasClass('icon-checkbox-checked'))
            {
                $(this).find("input").prop('checked',true);

                content = addContent(oldValue,value);
            }
            else
            {
                $(this).find("input").prop('checked',false);

                content = removeContent(oldValue,value);
            }

            $(this).parent('.checkboxPanel').prev('.checkbox').find('span').text(content);
        });
    });


//   清除缓存与退出登录
    var url;

    $("#clean").on('click',function()
    {
        url = "/"+moduleName+"/Clean/cache";

        show(language.CLEAR_ALL_CACHE,language.CLEAR_CACHE);
    });

    $("#logout").on('click',function()
    {
        url = "/"+moduleName+"/Login/logout?login_token="+$(this).data('value');

        show(language.LOGOUT_CLEAR,language.LOGOUT);
    });

//    微信解绑
    $("#unbind").on('click',function()
    {
        url = "/"+moduleName+"/Login/unbind?login_token="+$(this).data('value');

        show(language.UNBIND_CLEAR,language.UNBIND);
    });

    $(".footer-cancel,.setting-shade").on('click',function()
    {
        hide();
    });

    $("a[data-id='tolink']").on('click',function()
    {
        var loading = layer.load(2,{offset:['40%']});

        var uid = $(this).data('value');

        $.post(url,function(data)
        {
            layer.msg(data.msg,{time:1000,offset:['40%']},function()
            {
                if(data.url)
                {
                    if(isApp === true)
                    {
                        socket = io(socketUrl);

                        // Socket连接后退出登录
                        socket.on('connect', function ()
                        {
                            socket.emit('app_disconnect',{uid:uid});
                        });

                        // 收到Socket返回后跳转
                        socket.on('logout_complete',function()
                        {
                            window.location.href = data.url;
                        });
                    }
                    else
                    {
                        window.location.href = data.url;
                    }
                }
                else
                {
                    layer.close(loading);
                }
            });
        });

        hide();
    });

//    个人信息表单切换
    var userWrapper = $("#userWrapper");

    if(userWrapper.length > 0)
    {
        changeWindows(userWrapper,$('#user-input'),'member',$('#userBack'));
    }

//    公司信息表单切换
    var companyWrapper = $("#companyWrapper");

    if(companyWrapper.length > 0)
    {
        changeWindows(companyWrapper,$('#company-input'),'company',$('#companyBack'));
    }

//    通知中心获取通知消息
    var notifyCenter = $('#notifyCenter');

    if(notifyCenter.length > 0)
    {
        var headerHeight = $('header')[0].getBoundingClientRect().height;

        var footerHeight = $('.feeldesk-footer')[0].getBoundingClientRect().height;

        var totalHeight = headerHeight + footerHeight;

        $('.message-main').css('height','calc(100% - '+totalHeight+'px)');

        if($('.one-tab-item').length > 0)
        {
            $('.feeldesk-main').addClass('one-tab-main');
        }

        layui.use('flow', function()
        {
            var flow = layui.flow;

            flow.load(
            {
                elem: '#messageItem',
                scrollElem:'.feeldesk-main',
                isAuto:false,
                done: function(page, next)
                {
                    var lis = [];

                    $.get("/"+moduleName+"/Message/getMessage?p="+page+"&request=flow", function(data)
                    {
                        layui.each(data.data, function(index, item)
                        {
                            var tag = '';

                            var style = '';

                            if(item.read_status === '1')
                            {
                                style = "un-read";

                                tag = "<span></span>";
                            }

                            var items =
                                "<div class='message-item'>" +
                                "<div class='message-date'>"+item.create_time+"</div>" +
                                "<div class='message-box' data-href='"+"/"+moduleName+"/Message/detail?id="+item.msg_id+"'>" +
                                "<div class='box-head relative'>" +
                                "<i class='iconfont icon-message "+style+"'>"+tag+"</i>" +
                                "<span>"+item.msg_name+"</span>" +
                                "<a href='javascript:' class='iconfont icon-menu' data-value='"+item.msg_id+"'></a>" +
                                "</div>" +
                                "<div class='box-content'>" +
                                "<div class='message-title'>"+item.msg_title+"</div>" +
                                "<ul>" +
                                "<li>"+language.TICKET_TITLE+item.ticket.title+"</li>";

                                if(item.msg_item !== undefined)
                                {
                                    items += item.msg_item;
                                }

                                items += "<li>"+item.msg_content+"</li></ul></div><div class='box-footer'>"+language.DETAIL+"</div></div></div>";

                            lis.push(items);
                        });

                        next(lis.join(''), page < data.pages);
                    });

                    $(document).on('click','.message-box',function()
                    {
                        var url = $(this).data('href');

                        if (isApp === true)
                        {
                            uni.navigateTo({
                                url: '/pages/index/second?url='+url
                            });
                        }
                        else
                        {
                            window.location.href = $(this).data('href');
                        }
                    });

                    $(document).on('click','.icon-menu',function(e)
                    {
                        e.stopPropagation();

                        var value = $(this).data('value');

                        $('.message-shade').fadeIn('700').prev('.message-operate').slideDown('700').find('.delete-cancel').on('click',function()
                        {
                            $(this).parent('.message-operate').slideUp('700').next('.message-shade').fadeOut(700);
                        });

                        $('.message-delete').unbind('click').on('click',function()
                        {
                            layer.confirm(language.SURE_DELETE_MESSAGE,
                            {
                                skin:'msg-window',
                                title:false,
                                closeBtn:false,
                                offset:['40%'],
                                btnAlign: 'c',
                                btn:[language.CANCEL,language.SURE],
                                yes:function(index)
                                {
                                    layer.close(index);
                                },
                                btn2:function()
                                {
                                    var loading = layer.load(2,{offset:['40%']});

                                    $.post("/"+moduleName+"/Message/delete",{id:value},function(data)
                                    {
                                        if(data.errcode === 0)
                                        {
                                            layer.msg(data.msg,{time:1000,offset:['40%']},function()
                                            {
                                                window.location.reload();
                                            });
                                        }
                                        else
                                        {
                                            layer.close(loading);

                                            layer.msg(data.msg,{time:1500,offset:['40%']});
                                        }
                                    });

                                    return false
                                }
                            });
                        })
                    });
                }
            });
        });

        $('#all-read,#all-delete').unbind('click').on('click',function()
        {
            var type = $(this).data('type');

            var title;

            if(type === 'read')
            {
                title = language.READ_ALL_MESSAGE;
            }
            else
            {
                title = language.DELETE_ALL_MESSAGE;
            }

            layer.confirm(title,
            {
                skin:'msg-window',
                title:false,
                closeBtn:false,
                offset:['40%'],
                btnAlign: 'c',
                btn:[language.CANCEL,language.SURE],
                yes:function(index)
                {
                    layer.close(index);
                },
                btn2:function()
                {
                    var loading = layer.load(2,{offset:['40%']});

                    $.post("/"+moduleName+'/Message/updateMessageStatus',{type:type},function(data)
                    {
                        if(data.errcode === 0)
                        {
                            layer.msg(data.msg,{time:1000,offset:['40%']},function()
                            {
                                window.location.reload();
                            });
                        }
                        else
                        {
                            layer.close(loading);

                            layer.msg(data.msg,{time:1500,offset:['40%']});
                        }
                    });

                    return false
                }
            });
        });
    }

    slideVerify = new window.slideVerifyPlug('#slide-verify',
    {
        wrapWidth:'100%',
        initText:language.SLIDE_VERIFY,
        sucessText:language.VERIFY_PASS
    });
});

function changeWindows(wrapper,inputObj,fieldName,back)
{
    $('.to-windows').unbind('click').on('click',function()
    {
        wrapper.css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");

        var title = $(this).data('title');

        var name = fieldName+"["+$(this).data('name')+"]";

        var value = $(this).data('value');

        $('#windowTitle').text(title);

        inputObj.attr({'name':name,'value':value});
    });

    back.unbind('click').on('click',function()
    {
        wrapper.animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });
}

function show(html1,html2)
{
    $(".footer-title").html(html1);

    $(".footer-content").find('a').html(html2);

    $(".setting-shade").fadeIn(300);

    $(".set-footer").slideDown(300);
}

function hide()
{
    $(".setting-shade").fadeOut(300);

    $(".set-footer").slideUp(300);
}

function addContent(oldContent,newContent)
{
    var content;

    if(oldContent.indexOf('/') === -1)
    {
        if(!oldContent)
        {
            content = newContent;
        }
        else
        {
            content = oldContent+' / '+newContent;
        }
    }
    else
    {
        content = oldContent+' / '+newContent;
    }

    return content;
}

function removeContent(oldContent,newContent)
{
    var content;

    if(oldContent.indexOf('/') === -1)
    {
        content = oldContent.replace(newContent,'');
    }
    else
    {
        content = oldContent.replace(' / '+newContent,'');

        content = content.replace(newContent+' / ','');
    }

    return content;
}


const login = function (source)
{
    var verify = slideVerify.slideFinishState;

    if(false === verify)
    {
        layer.msg(language.SLIDE_VERIFY);

        return false;
    }

    var forms = $('#login-form');

    var loading = layer.load(2);

    var url = '/u-log-in';

    var hrefAction = '/u-home';

    if(moduleName === 'Weixin')
    {
        url = forms.attr('action');

        hrefAction = '/w-home';
    }
    else if(moduleName === 'Mobile')
    {
        url = '/m-log-in';

        hrefAction = '/m-home';
    }

    $.post(url,forms.serialize(),function(data)
    {
        if(data.status !== 2)
        {
            layer.close(loading);

            layer.msg(data.msg);

            slideVerify.resetVerify();
        }
        else
        {
            // 判断当前环境是否为APP
            if(isApp === true)
            {
                socket = io(socketUrl);

                // Socket连接后以uid登录
                socket.on('connect', function ()
                {
                    socket.emit('login',{uid:data.sort});

                    // 模拟点击，向App发送连接数据
                    $('#login-trigger').attr({"data-value":data.sort,"data-c-value":data.csort}).click();

                    // 接收APP返回的登录状态
                    socket.on('listion_app_login',function()
                    {
                        // 向App获取client_id
                        socket.emit('get_app_client_id',{uid:data.sort})

                        socket.on('login_complete',function()
                        {
                            window.location.href = hrefAction+'?is_app=1';
                        });
                    });
                });

                socket.on('connect_error', function(data)
                {
                    alert(data + ' - connect_error');
                });
            }
            else
            {
                window.location.href = hrefAction;
            }
        }
    },'JSON');
};


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

    var param = {username:$("#username").find('input').val()};

    var url = '/u-reg-code';

    if(moduleName === 'Mobile')
    {
        url = '/m-reg-code';
    }
    else if(moduleName === 'Wechat')
    {
        url = '/w-reg-code';
    }

    if(source === 'reset')
    {
        param.way = $("input[name='way").val();

        url = '/u-reset-code';

        if(moduleName === 'Mobile')
        {
            url = '/m-reset-code';
        }
        else if(moduleName === 'Wechat')
        {
            url = '/w-reset-code';
        }
    }

    $.post(url,param,function(result)
    {
        if(result.status !== 2)
        {
            layer.msg(result.msg);
        }
        else
        {
            setCookie(countdown_name,60,60);

            countDownTime('code',countdown_name);

            layer.msg(result.msg);

            slideVerify.resetVerify();
        }

        layer.close(loading);

    },'JSON');
};

const submitRequest = function (source,scene)
{
    const loading = layer.load(2);

    const verify = slideVerify.slideFinishState;

    if (false === verify)
    {
        layer.msg(language.SLIDE_VERIFY);

        layer.close(loading);

        return false;
    }

    const url = {reg:'/u-reg-submit',reset:'/u-reset-submit',login:'/u-login'};

    if(moduleName === 'Mobile')
    {
        url = {reg:'/m-reg-submit',reset:'/m-reset-submit',login:'/m-login'};
    }
    else if(moduleName === 'Wechat')
    {
        url = {reg:'/w-reg-submit',reset:'/w-reset-submit',login:'/w-login'};
    }

    $.post(url[source], $('#' + source + '-form').serialize(), function (result)
    {
        if (result.status === 2)
        {
            if (source === 'reg')
            {
                layer.msg(result.msg, {time: 1500}, function ()
                {
                    window.location.href = url.login;
                });
            }
        }
        else
        {
            layer.msg(result.msg);
        }

        layer.close(loading);
    });
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


//判断前一页是否存在，不存在则返回首页，存在则返回前一页
function goBack()
{
    if ((navigator.userAgent.indexOf('MSIE') >= 0) && (navigator.userAgent.indexOf('Opera') < 0))
    { // IE
        if(history.length > 0)
        {
            window.history.go( -1 );
        }
        else
        {
            window.location.href="/"+moduleName+"/Index/index";
        }
    }
    else
    { 	//非IE浏览器
        if (navigator.userAgent.indexOf('Firefox') >= 0 ||
            navigator.userAgent.indexOf('Opera') >= 0 ||
            navigator.userAgent.indexOf('Safari') >= 0 ||
            navigator.userAgent.indexOf('Chrome') >= 0 ||
            navigator.userAgent.indexOf('WebKit') >= 0){

            if(window.history.length > 1)
            {
                window.history.go( -1 );
            }
            else
            {
                window.location.href="/"+moduleName+"/Index/index";
            }
        }
        else
        {
            window.history.go( -1 );
        }
    }
}

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
$(document).ready(function ()
{
    $('.user-form-item,.faq-form-item').find('input').focus(function()
    {
        $(this).prev('i').css('color','#2c6ee5');
    }).blur(function (){
        $(this).prev('i').removeAttr('style');
    });

    $('.searchBtn').on('click',function()
    {
        $(this).parents('form').submit();
    });

    $('.crmSearchBtn').on('click',function()
    {
        $(this).parents('form').submit();
    });

    var tipsBox;

    $(".intro-icon").hover(function()
    {
        var content = $(this).data('note');

        tipsBox = layer.tips(content,$(this), {tips:2, time:1000000, skin:'form-tips'});

    },function ()
    {
        layer.close(tipsBox);
    });

    $(".form-intro-icon").hover(function()
    {
        var content = $(this).next('.ticket-form-intro').html();

        tipsBox = layer.tips(content,$(this), {tips:2, time:1000000, skin:'form-tips'});

    },function ()
    {
        layer.close(tipsBox);
    });

    layui.config({
        base: "/Public/js/layui/extends/"
    }).extend({
        notice: 'notice'
    });
});

$(document).on("click", "a[load='async']", function (e)
{
    e.preventDefault();

    var title=$(this).html().replace(/<\/?.+?>/g,"").replace(/ /g,"");

    var tit;

    if(title === language.RESET)
    {
        tit = language.SURE+" "+title+"？（"+language.DEFAULT_PASSWORD+" <span class='red1'>123456</span>）";
    }
    else
    {
        tit = language.SURE+" "+title+"?";
    }

    var action = $(this).attr('href');

    layer.confirm(tit,{title:language.PROMPT,offset:['15vw']},function(index)
    {
        layer.close(index);

        $.get(action,function(data)
        {
            if(data.status === 0)
            {
                feelDeskAlert(data.msg);
            }
            else
            {
                feelDeskAlert(data.msg,data);
            }

        },'JSON');
    });
});

$(document).on("click", "a[load='loading']", function ()
{
    layer.load(1,{offset:['20vw']});
});

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

/**
* @param msg {String}
* @param data {Object}
* @param data.no_data_html {String}
*/
function feelDeskAlert(msg,data)
{
    if(data !== undefined)
    {
        layer.msg(msg,{offset:'15vw',time:1000,closeBtn:0},function(index)
        {
            layer.close(index);

            layer.close('loading');

            if(data.hasOwnProperty('batchRemove') && data.batchRemove === 1)//批量删除，无刷新移除数据，目前仅用于知识库和FAQ的文章删除
            {
                $.each(data.ids, function (k, v)
                {
                    $("." + data.class + "[data-id='" + v + "']").remove();
                });

                if ($(document).find("." + data.class).length === 0)
                {
                    $("." + data.box).html(data.no_data_html);
                }

                $('#batchDelete').addClass('disabled');
            }
            else if (data.hasOwnProperty('isReload') && data.isReload === 1)
            {
                window.location.reload();
            }
            else if (data.hasOwnProperty('reloadType') && data.reloadType === 'parent')
            {
                // window.parent.location.href = data.url;
                window.parent.location.reload();
            }
            else if (data.hasOwnProperty('remove') && data.remove === 1)
            {
                $("." + data.class + "[data-id='" + data.id + "']").remove();
            }
            else if (data.hasOwnProperty('url') && data.url.length > 0)
            {
                if(data.download)
                {
                    window.open(data.url, '_blank');
                }
                else
                {
                    window.location.href = data.url;
                }
            }
            else
            {
                return false;
            }
        });
    }
    else
    {
        layer.alert(msg,{offset:'15vw', title:language.PROMPT, btn: [language.SURE],closeBtn:0},function(index)
        {
            layer.close(index);

            layer.closeAll('loading');
        });
    }
}


function hideRightLoading()
{
    $('.global-loading').remove();
}

//自动播放消息提醒音
function audioplayer(id, file, loop)
{
    var audioplayer = document.getElementById(id);

    if (audioplayer != null)
    {
        document.body.removeChild(audioplayer);
    }

    if (typeof(file) != 'undefined')
    {
        var player;

        if (navigator.userAgent.indexOf("MSIE") > 0)
        { // IE
            player = document.createElement('bgsound');

            player.id = id;

            player.src = file['mp3'];

            player.setAttribute('autostart', 'true');

            if (loop)
            {
                player.setAttribute('loop', 'infinite');
            }

            document.body.appendChild(player);
        }
        else // Other FF Chome Safari Opera
        {
            player = document.createElement('audio');

            player.id = id;

            player.setAttribute('autoplay', 'autoplay');

            if (loop)
            {
                player.setAttribute('loop', 'loop');
            }

            document.body.appendChild(player);

            var mp3 = document.createElement('source');

            mp3.src = file['mp3'];

            mp3.type = 'audio/mpeg';

            player.appendChild(mp3);
        }

        toggleSound();
    }
}

//触发自动播放消息提醒音
function toggleSound()
{
    var audio = document.getElementById("audioplane");//获取ID  

    if(audio.paused)
    {
        audio.paused = false;

        audio.play(); //没有就播放 
    }
}

// 更新消息數量和列表内容
function changeMsg(isAll,isRead,id)
{
    var that = $("#msgNum");

    var messageNumber = parseInt(that.text());

    if(messageNumber > 0)
    {
        if(isAll === true)
        {
            messageNumber = 0
        }
        else
        {
            messageNumber = messageNumber - 1;
        }

        that.text(messageNumber);

        if(messageNumber === 0)
        {
            that.addClass('visibility');
        }
    }

    if(isRead)
    {
        $.post("/"+moduleName+"/Message/updateMessageStatus",{ids:id,type:'read'},function(data) {});
    }
}

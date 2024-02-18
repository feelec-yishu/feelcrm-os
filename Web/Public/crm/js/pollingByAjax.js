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

var get_msg,get_ticket_num;

$(function()
{
    /* 头部菜单显示与隐藏 */
    var itme;

    $(".nav").hover(function()
    {
        itme = $(this);

        itme.find('.menu-panel').show();
    },
    function()
    {
        itme.find('.menu-panel').hide();
    });

    $(".lang-menu").hover(function()
    {
        itme = $(this);

        itme.find('.lang-panel').show();
    },
    function()
    {
        itme.find('.lang-panel').hide();
    });

    $('#updateLoginStatus').find('a').unbind('click').on('click',function(e)
    {
        var status = $(this).data('value');

        $.post("/"+moduleName+'/AjaxRequest/updateLoginStatus',{status:status},function(data)
        {
            if(data.errcode == 0)
            {
                $('.login-status i').removeClass('gray2 green4').addClass(data.color);

                $('.login-status span').html(data.state);
            }
        },'JSON');
    });

    /* 循环任务 */
	// get_msg = setInterval("getSystemMessage()",1000*10);

	// get_ticket_num = setInterval('getTicketNumber()',1000*10);

	// /* 移除消息列表中选中的记录 */
    // $('.message-item').on('click','.message-list',function()//代理message-list
	// {
		// $(this).remove();

		// changeMsg();
	// });
});


// Ajax轮询获取工单数
function getTicketNumber()
{
	$.post("/"+moduleName+"/AjaxRequest/getTicketNumber?look=1",function(data)
	{
        if(data.errcode == 1)
        {
            window.location.href = data.url;
        }
        else
        {
            for (var i in data)
            {
                if(data[i] > 0)
                {
                    data[i] = data[i] > 99 ? '99+' : data[i];

                    $("#" + i + " span").remove();

                    $("#" + i + "").html("<span class='layui-badge'>" + data[i] + "</span>");
                }
                else
                {
                    $("#" + i + " span").remove();
                }

                if (i == 'waitReplyTicketNum' || i == 'ccNum')
                {
                    $(window.frames["rightMain"].document).find("#" + i + " span").html(data[i]);
                }
            }
        }
	},'JSON');
}



// Ajax轮询获取系统消息
function getSystemMessage()
{
    var msg_num = $("#msgNum");

    var topMsgBody = $('.message-item');

	$.post("/"+moduleName+"/AjaxRequest/getSystemMessage",function(data)
	{
	    if(data.errcode == 1)
	    {
	        window.location.href = data.url;
        }
        else
        {
            var str = '';

            $.each(data,function(k,v)
            {
                if(v.msg_id > 0)
                {
                    layer.open(
                    {
                        type: 1,
                        title: language.SYSTEM_MSG,
                        offset: 'rb',
                        shade:0,
                        shift:2,
                        time:15000,
                        area: ['500px', '200px'],
                        content: "<div style='padding:15px 10px'>"+v.msg_title+"<a href='/"+moduleName+"/Message/getMessage.html?msg_id="+v.msg_id+"&types=unread&from=msgbox' class='blue ml10 msg-tan' target='rightMain' >"+language.SEE+"</a></div>",
                        success: function(layero, index)
                        {
                            $('body').append('<audio autoplay="autoplay"><source src="/Public/js/msg.mp3"' + 'type="audio/wav"/><source src="/Public/js/msg.mp3" type="audio/mpeg"/></audio>');

                            layero.find('a.msg-tan').attr('onclick',"removeMsg("+v.msg_id+","+index+")");
                        }
                    });
                }

                if(parseInt(data.count) > 0)
                {
                    msg_num.removeClass('visibility');

                    //有新的未读消息时，在消息列表中增加一条新消息
                    if(v.msg_id > 0)
                    {
                        str = '<div class="message-list">'+

                            '<div class="message-title">'+

                            '<a href="/'+moduleName+'/Message/getMessage?msg_id='+v.msg_id+'&types=unread&from=msgbox" class="ellipsis fts12" target="rightMain" data-id='+v.msg_id+'>'+v.msg_title+'</a></div></div>'

                        var obj = topMsgBody.find('[data-id='+v.msg_id+']');

                        if(obj)
                        {
                            $('.no-message').remove();

                            topMsgBody.append(str);
                        }
                    }
                }
                else
                {
                    msg_num.addClass('visibility');

                    topMsgBody.html("<p class='no-message'>"+language.NO_NEW_MSG+"</p>");
                }

                msg_num.text(data.count);
            })
        }
	},'JSON');
}


// 更新消息數量和列表内容
function changeMsg()
{
    var msg_num = $("#msgNum");

    if(parseInt(msg_num.text()) > 0)
    {
        msg_num.text(msg_num.text()-1);
    }

    if(parseInt(msg_num.text()) == 0)
    {
        msg_num.addClass('visibility');
    }

    $('.message-item').each(function()
    {
        if(!$(this).find('.message-list').length)
        {
            $(this).html("<p class='no-message'>"+language.NO_NEW_MSG+"</p>");
        }
    })
}


// 点击弹窗内容时，移除消息列表中相应的消息记录
function removeMsg(id,index)
{
	layer.close(index);

	var idel = $('.message-item').find('[data-id='+id+']');

	if(idel)
	{
		idel.parents('.message-list').remove();

		changeMsg();
	}
}



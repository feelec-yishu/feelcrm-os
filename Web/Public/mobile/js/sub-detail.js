$(function()
{
//  头部菜单
    $("#detailMenu").unbind('click').on('click',function(e)
    {
        e.stopPropagation();

        $("#headerMenu").slideToggle('fast').find('div').removeClass('current').find('span').css({'border-top':'1px solid #eee'});

        $(".menu-operate:first").find('span').css({'border-top':'none'});

        $(".menu-operate").unbind('click').on('click',function()
        {
            $("#headerMenu").slideToggle('fast');

            if(!$(this).hasClass('current'))
            {
                $(this).addClass('current').siblings('.menu-operate').removeClass('current');

                $(this).siblings('.menu-operate:not(":first")').find('span').css({'border-top':'1px solid #eee'});

                $(this).find('span').css({'border-top':'none'}).parent().next().find('span').css({'border-top':'none'});
            }
        });
    });

//   提交图片
    layui.use('upload', function()
    {
        var upload = layui.upload;

//       工单回复
        upload.render(
        {
            elem: "#ticketReplyUploadImg",

            url: "/"+moduleName+"/Upload/uploadImageFile",

            multiple:true,

            before:function()
            {
                layer.msg(language.UPLOADING_IMAGE,{time:100000,shade: [0.3, '#393D49'],offset:['80px']});
            },
            done: function(data)
            {
                if(data.code == 0)
                {
                    var img = data.url;

                    var thumb = data.thumb;

                    var name = data.img_name;

                    for(var i in img)
                    {
                        var str = "<img src='"+img[i]+"' alt=''/>";

                        $("#ticketReplyImgContent").val(str);
                    }

                    $.post("/"+moduleName+"/subTicket/reply",$("#ticketReplyImgForm").serialize(),function(data)
                    {
                        layer.msg(data.msg,{time:1000,offset:['80px']});

                        if(data.status == 1)
                        {
                            window.location.reload();
                        }
                    });
                }
                else
                {
                    layer.msg(data.msg,{time:2000,offset:['80px']});
                }
            }
        });
    });

//   回复列表、工单属性标签页的切换
    $('.detail-tab li').on('click',function()
    {
        var value = $(this).data('value');

        var ticketMain = $('.ticket-main');

        var detailFooter = $('.detail-footer');

        $(this).addClass('current').siblings('li').removeClass('current');

        $(".detail-main").hide().siblings(".detail-main[data-value='"+value+"']").show();

        $('#'+value).show().siblings('div').hide();

        var headerHeight;var footerHeight,ticketMainHeight,detailTabHeight,detailMainHeight;

        var style;

        headerHeight = $('header')[0].getBoundingClientRect().height;

        detailTabHeight = $('.detail-tab')[0].getBoundingClientRect().height;

        if(value == 'ticket-detail')
        {
            $(".ticket-detail-main").addClass('detail-info-main');

            ticketMain.css({'height':'calc(100% - '+headerHeight+'px)','overflow-x':'hidden'});

            $('.detail-info-main').css({'height':'calc(100% - '+detailTabHeight+'px)','overflow-x':'hidden'});

            detailFooter.hide();

            scrollTop();
        }
        else
        {
            if(detailFooter.length > 0)
            {
                detailFooter.show();

                footerHeight = detailFooter[0].getBoundingClientRect().height;
            }

            ticketMainHeight = headerHeight + footerHeight;

            ticketMain.css({'height':'calc(100% - '+ticketMainHeight+'px)','overflow-x':'hidden'});

            style = {'height':'calc(100% - '+detailTabHeight+'px)','overflow-x':'hidden'};

            $(".ticket-detail-main").css(style).removeClass('detail-info-main');

            scrollBottom();
        }
    });

//   提交工单回复
    $('#submitTicketReply').on('click',function()
    {
        var loading = layer.load(2,{offset:['100px']});

        var content = $("#ticketReplyContent").html();

        $("#ticket-reply").find("input[name='reply[reply_content]']").val(content);

        $.post("/"+moduleName+"/subTicket/reply",$("#ticketReplyForm").serialize(),function(data)
        {
            layer.close(loading);

            layer.msg(data.msg,{time:1000,offset:['80px']});

            if(data.status == 1)
            {
                window.location.href = data.url;
            }
        });
    });

//   更新工单状态
    $('.select-operate').unbind('click').on('click',function()
    {
        var selectOperate = $(this);

        var param;

        var selectType = $(this).data('type');

        selectOperate.toggleClass('select-operated').next('.select-item').slideToggle('fast');

        selectOperate.next('.select-item').find('div').unbind('click').on('click',function()
        {
            var selectItem = $(this);

            var loading = layer.load(2,{offset:['40%']});

            var value = selectItem.data('value');

            var name = selectItem.data('name');

            if(selectType == 'status')
            {
                param = {value:value,source:'setStatus'};
            }

            $.post("/"+moduleName+'/subTicket/detail?id='+ticketId,param,function(data)
            {
                layer.msg(data.msg,{time:2000,offset:['40%']});

                if(data.errcode == 0)
                {
                    if(selectType == 'status')
                    {
                        layer.msg(data.msg,{time:1000,offset:['40%']},function()
                        {
                            window.location.reload();
                        });
                    }
                    else
                    {
                        layer.close(loading);

                        selectOperate.html(name+' <i class="feeldesk-edge"></i>');

                        selectItem.addClass('current').siblings().removeClass('current');
                    }
                }
                else
                {
                    layer.close(loading);

                    layer.msg(data.msg,{time:2000,offset:['40%']});
                }

                selectOperate.toggleClass('select-operated').next('.select-item').slideToggle('fast');

            },'JSON');
        });
    });

    // setTimeout('scrollBottom()', 500); //延迟1秒
});

function scrollBottom()
{
    var reply = document.getElementById("detailMain");

    reply.scrollTop = reply.scrollHeight;
}

function scrollTop()
{
    var reply = document.getElementById("detailMain");

    reply.scrollTop = 0;
}

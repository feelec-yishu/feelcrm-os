$(function()
{

    $("#publishMail,#publishTel").on('click',function()
    {

    });

//  头部菜单
    $("#detailMenu").unbind('click').on('click',function()
    {
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

            var param,textarea;

//           审核通过
            if($(this).attr('id') == 'ticket-pass')
            {
                layer.confirm(language.SURE_PASS_AUDIT,
                {
                    skin:'ticket-window',
                    title:false,
                    closeBtn:false,
                    offset:['40%'],
                    btnAlign: 'c',
                    btn:[language.SURE,language.CANCEL],
                    yes:function(index, layero)
                    {
                        layer.close(index);
                    },
                    btn2:function(index, layero)
                    {
                        var id = "{$detail.msg_id}";

                        var loading = layer.load(2,{offset:['40%']});

                        param = {value:10,source:'passAudit'};

                        $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,param,function(data)
                        {
                            if(data.errcode != 0)
                            {
                                layer.close(loading);

                                layer.msg(data.msg,{time:2000,offset:['40%']});
                            }
                            else
                            {
                                layer.msg(data.msg,{time:1000,offset:['40%']},function()
                                {
                                    window.location.reload();
                                });
                            }
                        },'JSON');

                        return false
                    }
                });
            }

//           审核驳回
            if($(this).attr('id') == 'ticket-reject')
            {
                textarea = $('#reject-form').find('textarea');

                param = {value:20,source:'rejectAudit'};

                $("#rejectWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");

                $("#rejectBack").unbind('click').on('click',function()
                {
                    $("#rejectWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
                });
            }

            $('#rejectDone').unbind('click').on('click',function()
            {
                var loading = layer.load(2,{offset:['40%']});

                param.content = textarea.val();

                $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,param,function(data)
                {
                    if(data.errcode != 0)
                    {
                        layer.close(loading);

                        layer.msg(data.msg,{time:2000,offset:['40%']});
                    }
                    else
                    {
                        layer.msg(data.msg,{time:1000,offset:['40%']},function()
                        {
                            window.location.reload();
                        });
                    }
                },'JSON');
            });
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
                layer.msg(language.UPLOADING_IMAGE,{time:100000,shade: [0.3, '#393D49'],offset:['40%']});
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
                        var str = "<img src='"+img[i]+"' />";

                        $("#ticketReplyImgContent").val(str);
                    }

                    $.post("/"+moduleName+"/Ticket/reply",$("#ticketReplyImgForm").serialize(),function(data)
                    {
                        layer.msg(data.msg,{time:1000,offset:['40%']});

                        if(data.status == 1)
                        {
                            window.location.href = data.url;
                        }
                    });
                }
                else
                {
                    layer.msg(data.msg,{time:2000,offset:['40%']});
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

        var participant = $('.ticket-participant');

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

            participant.hide();

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

            if(value == 'inside-reply')
            {
                participant.show();

                var participantHeight = participant[0].getBoundingClientRect().height;

                detailMainHeight = detailTabHeight + participantHeight;

                style = {'height':'calc(100% - '+detailMainHeight+'px)','overflow-x':'hidden'};
            }
            else
            {
                participant.hide();

                style = {'height':'calc(100% - '+detailTabHeight+'px)','overflow-x':'hidden'};
            }

            $(".ticket-detail-main").css(style).removeClass('detail-info-main');

            scrollBottom();
        }

    });

//   提交工单回复
    $('#submitTicketReply').on('click',function()
    {
        var loading = layer.load(2,{offset:['40%']});

        var content = $("#ticketReplyContent").html();

        $("#ticket-reply").find("input[name='reply[reply_content]']").val(content);

        $.post("/"+moduleName+"/Ticket/reply",$("#ticketReplyForm").serialize(),function(data)
        {
            layer.close(loading);

            layer.msg(data.msg,{time:1000,offset:['40%']});

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

        selectOperate.toggleClass('select-operated').next('.select-item').slideToggle('fast');

        selectOperate.next('.select-item').find('div').unbind('click').on('click',function()
        {
            var selectItem = $(this);

            var loading = layer.load(2,{offset:['40%']});

            var value = selectItem.data('value');

            param = {value:value,source:'setStatus'};

            $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,param,function(data)
            {
                if(data.errcode == 0)
                {
                    if(data.is_end == 'yes')
                    {
                        layer.close(loading);

                        $('.mobileSelect').removeClass('mobileSelect-show');

                        satisfyFun(true);
                    }
                    else
                    {
                        layer.msg(data.msg,{time:1000,offset:['40%']},function()
                        {
                            window.location.href = data.url;
                        });
                    }
                }
                else
                {
                    if(data.errcode == 3)//结束工单如果需要审核时启用
                    {
                        layer.msg(data.msg,{time:3000,offset:['40%']},function()
                        {
                            window.location.reload();
                        });
                    }
                    else
                    {
                        layer.close(loading);

                        layer.msg(data.msg,{time:2000,offset:['40%']});
                    }
                }
            });
        });
    });
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


function LoadMobileSelect(obj,title,data,defaultSelect)
{
    new MobileSelect(
    {
        trigger: '#'+obj,

        title: title,

        cancelBtnText:cancelBtn,

        ensureBtnText:ensureBtn,

        triggerDisplayData:false,

        wheels: [{data: data}],

        textColor:'#48507d',

        position:[defaultSelect], //初始化定位 打开时默认选中的哪个 如果不填默认为0

        callback:function(indexArr, data)
        {
            var id = data[0].id;

            //var status_name = data[0].value;

            var loading = layer.load(2,{offset:['40%']});

            if(obj == 'update-status')
            {
                $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{value:id,source:'setStatus'},function(data)
                {
                    if(data.errcode == 0)
                    {
                        if(data.is_end == 'yes')
                        {
                            layer.close(loading);

                            $('.mobileSelect').removeClass('mobileSelect-show');

                            $("#satisfyWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");

                            layui.use('rate',function()
                            {
                                var rate = layui.rate;

                                rate.render(
                                {
                                    elem: '#satisfy-star',
                                    text: true,
                                    value:1,
                                    setText: function(value)
                                    {
                                        var satisfy = satisfyData.satisfy;

                                        $('#satisfy').val(satisfy[value-1]['satisfy_id']);

                                        $('#satisfy-score').val(satisfy[value-1]['score']);

                                        this.span.text(satisfy[value-1]['lang_name']);

                                        var labels = satisfyData['labels'][satisfy[value-1]['satisfy_id']];

                                        if(labels.length > 0)
                                        {
                                            var li = '';

                                            $.each(labels,function(k,v)
                                            {
                                                li += "<li class='label-item' onclick='chooseSatisfyLabel(this,"+ v.label_id +")'>"+ v.label_name +"</li>";
                                            });

                                            $('#satisfy-label').html(li);

                                        }

                                        $('#satisfy-form').find("input[name='satisfy[labelId][]']").remove();
                                    }
                                });
                            });

                            $("#satisfyBack").unbind('click').on('click',function()
                            {
                                $("#satisfyWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
                            });

                            $("#satisfyDone").unbind('click').on("click",function()
                            {
                                var loading = layer.load(2,{offset:['40%']});

                                $.post("/"+moduleName+'/Ticket/satisfy?id='+ticketId,$('#satisfy-form').serialize(),function(data)
                                {
                                    if(data.status == 1)
                                    {
                                        layer.msg(data.msg,{offset:['40%']});

                                        window.location.reload();
                                    }
                                    else
                                    {
                                        layer.close(loading);

                                        layer.msg(data.msg,{time:1500,offset:['40%']});
                                    }
                                },'JSON');
                            })
                        }
                        else
                        {
                            layer.msg(data.msg,{time:1000,offset:['40%']},function()
                            {
                                window.location.href = data.url;
                            });
                        }
                    }
                    else
                    {
                        layer.close(loading);

                        layer.msg(data.msg,{time:1000,offset:['40%']});
                    }
                });
            }
        }
    });
}

var satisfyFun = function (reload)
{
    $("#satisfyWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");

    layui.use('rate',function()
    {
        var rate = layui.rate;

        rate.render(
        {
            elem: '#satisfy-star',
            text: true,
            value:5,
            setText: function(value)
            {
                var satisfy = satisfyData.satisfy;

                $('#satisfy').val(satisfy[value-1]['satisfy_id']);

                $('#satisfy-score').val(satisfy[value-1]['score']);

                this.span.text(satisfy[value-1]['lang_name']);

                var labels = satisfyData['labels'][satisfy[value-1]['satisfy_id']];

                if(labels.length > 0)
                {
                    var li = '';

                    $.each(labels,function(k,v)
                    {
                        li += "<li class='label-item' onclick='chooseSatisfyLabel(this,"+ v.label_id +")'>"+ v.label_name +"</li>";
                    });

                    $('#satisfy-label').html(li);

                }

                $('#satisfy-form').find("input[name='satisfy[labelId][]']").remove();
            }
        });
    });

    $("#satisfyBack").unbind('click').on('click',function()
    {
        if(reload == true)
        {
            window.location.reload();
        }
        else
        {
            $("#satisfyWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper')
                .animate({'z-index': '0',right:'0'}, "700");
        }
    });

    $("#satisfyDone").unbind('click').on("click",function()
    {
        var loading = layer.load(2,{offset:['40%']});

        $.post("/"+moduleName+'/Ticket/satisfy?id='+ticketId,$('#satisfy-form').serialize(),function(data)
        {
            if(data.status == 1)
            {
                layer.msg(data.msg,{offset:['40%']});

                window.location.reload();
            }
            else
            {
                layer.close(loading);

                layer.msg(data.msg,{time:1500,offset:['40%']});
            }
        },'JSON');
    })
};


//选择满意度标签
var chooseSatisfyLabel = function (t,id)
{
    $(t).toggleClass('current');

    var satisfyForm = $('#satisfy-form');

    var labelInput = "<input type='hidden' name='satisfy[labelId][]' value='"+id+"' v='"+id+"'>";

    if(satisfyForm.find("input[v='"+id+"']").length == 0)
    {
        satisfyForm.append(labelInput);
    }
    else
    {
        satisfyForm.find("input[v='"+id+"']").remove()
    }
};

var resubmitAudit = function(audit_status)
{
    var loading = layer.load(2,{offset:['40%']});

    $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{source:'resubmit_audit',audit_status:audit_status},function(data)
    {
        if(data.errcode != 0)
        {
            layer.close(loading);

            layer.msg(data.msg,{time:2000,offset:['40%']});
        }
        else
        {
            layer.msg(data.msg,{time:1000,offset:['40%']},function()
            {
                window.location.reload();
            });
        }
    })
};

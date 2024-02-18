$(function()
{
    $("#publishMail,#publishTel").on('click',function()
    {

    });

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

//           关联工单
            if($(this).attr('id') == 'associate-ticket')
            {
                $('#associate').toggleClass('associate-show');

                if($('.associate-show').length > 0)
                {
                    $('#associateSearch').unbind('click').on('click',function()
                    {
                        $('#associateItem').html("<div class='no-match'><div class='layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop'></div></div>");

                        var keyword = $(this).prev('input').val();

                        $.post("/"+moduleName+"/Ticket/detail?id="+ticketId+"&request=associate",{keyword:keyword},function(result)
                        {
                            var item = '';

                            $.each(result.data,function(k,v)
                            {
                                item +="<div class='associate-item' data-value='"+v.ticket_id+"'>" +
                                    "<span class='ticket-title ellipsis'>"+ v.title+"</span>" +
                                    "<span class='iconfont icon-check'></span></div>"
                            });

                            if(item)
                            {
                                $('#associateItem').html(item);

                                $('.associate-item').unbind('click').on('click',function()
                                {
                                    var value = $(this).data('value');

                                    $(this).find('span.iconfont').addClass('icon-radio-checked').parent().siblings().find('span').removeClass('icon-radio-checked');

                                    $('#associate-id').val(value);
                                })
                            }
                            else
                            {
                                $('#associateItem').html("<div class='no-match'>"+language.NO_DATA+"</div>");
                            }

                        },'JSON');
                    });

                    $('#associateDone').unbind('click').on('click',function()
                    {
                        var loading = layer.load(2,{offset:['40%']});

                        $.post("/"+moduleName+"/Ticket/reply",$('#associateForm').serialize(),function(data)
                        {
                            if(data.errcode == 0)
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
                        },'JSON');
                    })
                }

                $('#associateCancel').unbind('click').on('click',function()
                {
                    $('#associate').toggleClass('associate-show');
                });
            }

//           催促工单
            if($(this).attr('id') == 'urge-ticket')
            {
                var urgeChooseAll = $("#urgeChooseAll");

                var urgeMember = $('.urge-member');

                $("#urgeWrapper").css("display", 'block').animate({'z-index': '1', left: 0}, "700").siblings('#formWrapper').animate({'z-index': '0', right: '100%'}, "700");

                $("#urgeBack").on('click', function ()
                {
                    $("#urgeWrapper").animate({'z-index': '1', left: '100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0', right: '0'}, "700");
                });

//                单选
                urgeMember.on('click', function ()
                {
                    var spanIcon = $(this).find('span');

                    var value = $(this).data('value');

                    var inputDom = "<input type='hidden' name='urge[members][]' value='"+value+"'/>";

                    if (!spanIcon.hasClass('icon-checkbox-checked'))
                    {
                        spanIcon.addClass('icon-checkbox-checked').after(inputDom);
                    }
                    else
                    {
                        spanIcon.removeClass('icon-checkbox-checked').next('input').remove();
                    }
                });

//                全选
                urgeChooseAll.on('click', function ()
                {
                    var spanIcon = $(this).find('span');

                    spanIcon.toggleClass('icon-checkbox-checked');

                    $('.urge-main').find('.urge-member').each(function ()
                    {
                        var value = $(this).data('value');

                        var inputDom = "<input type='hidden' name='urge[members][]' value='"+value+"'/>";

                        if (spanIcon.hasClass('icon-checkbox-checked'))
                        {
                            if(!$(this).find('span').hasClass('icon-checkbox-checked'))
                            {
                                $(this).find('span').addClass('icon-checkbox-checked').after(inputDom);
                            }
                        }
                        else
                        {
                            $(this).find('span').removeClass('icon-checkbox-checked').next('input').remove();
                        }
                    });
                });

                $('#urgeDone').unbind('click').on('click',function()
                {
                    var loading = layer.load(2,{offset:['40%']});

                    $.post("/"+moduleName+"/Ticket/urgeTicket",$('#urgeForm').serialize(),function(data)
                    {
                        if(data.errcode == 0)
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
                    },'JSON');
                })
            }
        });

        $(document).unbind('click').bind('click',function(e)
        {
            $("#headerMenu").slideUp('fast');
        });
    });

//   工单回复与评论切换
    $('.ticket-reply-comment').unbind('click').on('click',function()
    {
        var value = $(this).data('value');

        var type = $(this).data('type');

        $('#commentReplyId').val(value).next('input').val(type);

        $('#ticketReplyInput,#submitTicketReply').addClass('hidden');

        $('#replyCommentInput,#submitReplyComment').removeClass('hidden');

        $('.reply-comment-content').focus().on('blur',function(e)
        {
            var id = '';

            if(e.relatedTarget) id = e.relatedTarget.id;

            if(id !== 'submitReplyComment')
            {
                $('#ticketReplyInput,#submitTicketReply').removeClass('hidden');

                $('#replyCommentInput,#submitReplyComment').addClass('hidden');

                $('#commentReplyId').val('').next('input').val('');

                $(this).val('').attr('placeholder',language.COMMENT_REPLY);
            }
        });

    });

//   内部协作回复与评论切换
    $('.inside-reply-comment').unbind('click').on('click',function()
    {
        var value = $(this).data('value');

        var type = $(this).data('type');

        $('#commentReplyId').val(value).next('input').val(type);

        $('#insideReplyInput,#submitInsideReply').addClass('hidden');

        $('#insideCommentInput,#submitInsideComment').removeClass('hidden');

        $('.inside-comment-content').focus().on('blur',function(e)
        {
            var id = '';

            if(e.relatedTarget) id = e.relatedTarget.id;

            if(id !== 'submitInsideComment')
            {
                $('#insideReplyInput,#submitInsideReply').removeClass('hidden');

                $('#insideCommentInput,#submitInsideComment').addClass('hidden');

                $('#commentReplyId').val('').next('input').val('');

                $(this).val('').attr('placeholder',language.COMMENT_REPLY);
            }
        });

    });

//   提交评论
    $('#submitReplyComment,#submitInsideComment').unbind('click').on('click',function(e)
    {
        var loading = layer.load(2,{offset:['40%']});

        var content = '';

        if(e.target.id == 'submitReplyComment')
        {
            content = $('.reply-comment-content').val();
        }

        if(e.target.id == 'submitInsideComment')
        {
            content = $('.inside-comment-content').val();
        }

        $('#commentContent').val(content);

        $.post("/"+moduleName+"/Ticket/commentReply",$("#commentForm").serialize(),function(data)
        {
            layer.close(loading);

            layer.msg(data.msg,{time:1000,offset:['40%']});

            if(data.errcode == 0)
            {
                window.location.reload();
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

//       内部协作回复
        upload.render(
        {
            elem: "#insideReplyUploadImg",

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

                        $("#insideReplyImgContent").val(str);
                    }

                    $.post("/"+moduleName+"/Ticket/teamReply",$("#insideReplyImgForm").serialize(),function(data)
                    {
                        layer.msg(data.msg,{time:1000,offset:['40%']},function()
                        {
                            if(data.status == 1)
                            {
                                window.location.href = data.url;
                            }
                        });
                    });
                }
                else
                {
                    layer.msg(res.msg,{time:3000,offset:['40%']});
                }
            }
        });
    });

//   回复列表、团队协作、工单属性标签页的切换
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


//   提交团协作回复
    $('#submitInsideReply').on('click',function()
    {
        var loading = layer.load(2,{offset:['40%']});

        var content = $("#insideReplyContent").html();

        $("#inside-reply").find("input[name='team_reply[reply_content]']").val(content);

        $.post("/"+moduleName+"/Ticket/teamReply",$("#insideReplyForm").serialize(),function(data)
        {
            layer.close(loading);

            layer.msg(data.msg,{time:1000,offset:['40%']});

            if(data.status == 1)
            {
                window.location.href = data.url;
            }
        });
    });

//   更新工单状态和优先级
    $('.select-operate').unbind('click').on('click',function()
    {
        var selectOperate = $(this);

        var param;

        var selectType = $(this).data('type');

        selectOperate.toggleClass('select-operated').next('.select-item').slideToggle('fast');

        if(selectType == 'audit') return;

        selectOperate.next('.select-item').find('div').unbind('click').on('click',function()
        {
            var selectItem = $(this);

            var loading = layer.load(2,{offset:['40%']});

            var value = selectItem.data('value');

            var name = selectItem.data('name');

            if(selectType == 'priority')
            {
                param = {priority:value,source:'setPriority'};
            }

            if(selectType == 'status')
            {
                param = {value:value,source:'setStatus'};
            }

            $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,param,function(data)
            {
                if(data.errcode == 0)
                {
                    if(selectType == 'status')
                    {
                        if(data.is_end == 'yes')
                        {
                            layer.msg(data.msg,{time:1000,offset:['40%']});

                            layer.close(loading);

                            $('.mobileSelect').removeClass('mobileSelect-show');

                            satisfyFun(true);
                        }
                        else
                        {
                            layer.msg(data.msg,{time:1500,offset:['40%']},function()
                            {
                                window.location.reload();
                            });
                        }
                    }
                    else
                    {
                        layer.close(loading);

                        layer.msg(data.msg,{time:1500,offset:['40%']});

                        selectOperate.html(name+' <i class="feeldesk-edge"></i>');

                        selectItem.addClass('current').siblings().removeClass('current');
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

                selectOperate.toggleClass('select-operated').next('.select-item').slideToggle('fast');

            },'JSON');
        });
    });

//    重启工单
    if($("#restart-ticket").length > 0)
    {
        var restartStatusData = [];

        for (var j in restartStatus)
        {
            restartStatusData.push({id: restartStatus[j].status_id, value: restartStatus[j].lang_name});

            if (restartStatus[j].status_id == endStatusId)
            {
                restartStatusData.splice(j,1);
            }
        }

        LoadMobileSelect('restart-ticket', restartStatusTitle, restartStatusData, 0);
    }
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

            if(obj == 'restart-ticket')
            {
                layer.load(2,{offset:['40%']});

                $("#restart-status").val(id);

                $.post("/"+moduleName+"/Ticket/Reply",$('#restartForm').serialize(),function(data)
                {
                    if(data.errcode == 0)
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
                },'JSON')
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

var remindReviewer = function(auditor_id)
{
    var loading = layer.load(2,{offset:['40%']});

    $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{source:'remind_audit',auditor_id:auditor_id},function(data)
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

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
var satisfyFun;

/**
* @param data {Object}
* @param data.errcode {int}
* @param data.ticket_no {string}
* @param data.is_end {string}
* @param data.priorityColor {string}
* @param data.label_name {string}
*/
$(function()
{
    var index,teamReplyTextarea;

    var detailMain = $(".detail-main");

    layui.config({
        base: '/Public/js/layui/extends/',
        version: '1.0.0'
    }).extend({
        layUploader:'uploader/layUploader'
    }).use(['layedit','form','upload','rate','layUploader'], function()
    {
        layui.form;

        var layUploader = layui.layUploader;

        /* 附件上传 */
        $("#uploadReplyFile,#uploadInsideReplyFile").unbind('click').click(function ()
        {
            var replyType = $(this).data('value');

            layUploader.render(
            {
                url:'/Upload/UploadTicketFile',//上传文件服务器地址，必填
                reload:false,
                source:'reply',
                replyType:replyType //回复类型 reply 工单回复 team 内部协作回复
            });
        });

//       删除上传文件
        $(document).on('click','.delete-attach',function()
        {
            var attach = $(this);

            layer.confirm(language.DELETE_FILE_TIP, {icon: 3, title:language.PROMPT,offset:['150px']}, function(index)
            {
                var name = attach.data('name');

                var loading = layer.load(2,{offset:['150px']});

                $.post("/Upload/deleteUploadFile",{'file_name':name},function(data)
                {
                    if(data.status === 1)
                    {
                        attach.parent().remove();

                        detailMain.css('padding-bottom',parseInt(detailMain.css('padding-bottom'))-31+'px');

                        feelDeskAlert(data.msg);
                    }
                    else
                    {
                        feelDeskAlert(data.msg);
                    }

                    layer.close(loading);

                },'JSON');

                layer.close(index);
            })
        });

//        下拉菜单
        $(".select").on('click',function (e)
        {
            $(this).siblings().find('.select-item').slideUp('fast');

            if($(e.target).attr('id') !== undefined || $(e.target).parent().attr('id') !== undefined)
            {
                $(this).find('.select-item').slideToggle('fast');
            }

            $(document).one('click',function()
            {
                $(".select").find('.select-item').slideUp('fast');
            });

            e.stopPropagation();
        });

//        选择工单主题
        $('.select-subject').find('li.select-subject-item').unbind('click').on('click',function()
        {
            var t = $(this);

            var typeId =  t.data('value');

            var subject_name = $.trim(t.text().replace('|--',''));

            var subjectLength = $('#subjects').find("li.subject-label[data-value='"+typeId+"']").length;

            if(typeId && subjectLength === 0)
            {
                $.post('/Ticket/detail?id='+ticketId,{typeId:typeId,source:'setSubject'},function(data)
                {
                    if(data.status === 2)
                    {
                        var label = "<li class='subject-label' data-value='"+typeId+"'><div class='subject-name'>"+subject_name+" <i class='iconfont icon-cha1 subject-del' data-value="+typeId+"></i></div></li>";

                        $('#subjects').append(label).parents('div.ticket-subject').removeClass('hidden');

                        t.addClass('active').append("<i class='iconfont icon-selected'></i>");
                    }
                    else
                    {
                        feelDeskAlert(data.msg);
                    }
                },'JSON');

                layer.close(index);
            }
        });

        $(document).on('click','.subject-del',function()
        {
            var t = $(this);

            var value = $(this).data('value');

            $.post('/Ticket/detail?id='+ticketId,{typeId:value,source:'deleteSubject'},function(data)
            {
                if(data.status === 2)
                {
                    t.parents('li').remove();

                    $('.select-subject').find("li[data-value='"+value+"']").removeClass('active').find('i').remove();

                    if($('#subjects').find("li").length === 1)
                    {
                        $('div.ticket-subject').addClass('hidden');
                    }
                }
                else
                {
                    feelDeskAlert(data.msg);
                }
            },'JSON');

        });

        $('.search-subject').keyup(function ()
        {
            var value = $(this).val();

            if(value)
            {
                $('.select-subject').find('li').hide().filter(":contains('" + ($(this).val()) + "')").show();
            }
            else
            {
                $('.select-subject').find('li').show();
            }
        });

//        选择工单状态
        $('.update-status dd').unbind('click').on('click',function ()
        {
            var value = $(this).data('value');

            var status_color =$(this).find('span').css('color');

            var status_name =$(this).find('span').html();

            var loading = layer.load(2,{offset:['150px']});

            $.post('/Ticket/detail?id='+ticketId,{value:value,source:'setStatus'},function(data)
            {
                if(data.errcode !== 0)
                {
                    if(data.errcode === 3)//结束工单如果需要审核时启用
                    {
                        feelDeskAlert(data.msg,data);
                    }
                    else
                    {
                        feelDeskAlert(data.msg);
                    }

                    layer.close(loading);
                }
                else
                {
					if(window.formCustomer !== 1)
					{
						var minimalistStatus = parent.$("div.status[data-value='status"+data.ticket_no+"']");

						var listStatus = parent.$("span.list-status[data-value='status"+data.ticket_no+"']");

						minimalistStatus.css("background-color",status_color).html(status_name);

						listStatus.css("background-color",status_color).html(status_name);

						$('#currentStatus').css('color',status_color).html(status_name);

                        data.isReload = 1;

                        feelDeskAlert(data.msg,data);
					}

                    if(data.is_end === 'yes')
                    {
                        layer.close(loading);

                        satisfyFun(true);
                    }
                }

                $(".status-item").slideUp('fast');

            },'JSON');
        });

//        选择优先级
        $('.update-priority dd').unbind('click').on('click',function ()
        {
            var priority = $(this).data('value');

            var loading = layer.load(2,{offset:['150px']});

            $.post('/Ticket/detail?id='+ticketId,{priority:priority,source:'setPriority'},function(data)
            {
                if(data.errcode === 1)
                {
                    feelDeskAlert(data.msg);
                }
                else
                {
					if(window.formCustomer !== 1)
					{
						var minimalistPriority = window.parent.$("span[data-value='priority"+data.ticket_no+"']");

						var listPriority = window.parent.$("td[data-value='priority"+data.ticket_no+"']").find('span');

						listPriority.removeClass().addClass("ticket-priority "+data.priorityColor).html(data.priority);

						minimalistPriority.removeClass().addClass("ticket-priority "+data.priorityColor).html(data.priority);

                        $('#currentPriority').html(data.priority).prev('#currentPriorityBg').removeClass().addClass("priority "+data.priorityColor);
					}

                    feelDeskAlert(data.msg);
                }

                $(".priority-item").slideUp('fast');

                layer.close(loading);

            },'JSON');
        });

//        审核工单
        $('.update-audit dd').unbind('click').on('click',function()
        {
            var value = $(this).data('value');

            var status = $(this).data('status');

            layer.open(
            {
                type: 1,
                title: '工单审核',
                offset: '150px',
                area: ['500px'],
                content: $(".ticket-audit"),
                shade: 0.5,
                closeBtn: 1,
                scrollbar: true,
                cancel:function(index)
                {
                    layer.close(index);
                },
                success:function()
                {
                    $('#submit-audit').unbind('click').on('click',function()
                    {
                        layer.load(2,{offset:['150px']});

                        var content = $("#audit-form").find('textarea').val();

                        var progress_id = $("input[name='audit_progress_id']").val();//审核进度ID

                        var data = {value:value,source:'user_audit',content:content,progress_id:progress_id,audit_status:status};

                        $.post('/Ticket/detail?id='+ticketId,data,function(data)
                        {
                            if(data.errcode !== 0)
                            {
                                feelDeskAlert(data.msg);
                            }
                            else
                            {
                                feelDeskAlert(data.msg,data);
                            }
                        },'JSON');
                    })
                }
            });

        });

        $('.opinion-title').next('.progress-item').css('margin-top',0).nextAll('.progress-item:last').css('margin-bottom','20px');

        var layedit = layui.layedit;

//        建立编辑器
        index = layedit.build('ticketReply',{uploadImage: {url: "/Upload/uploadImageFile?type=editor", type: 'post'}, height:120});

        teamReplyTextarea = layedit.build('teamReply',{uploadImage: {url: "/Upload/uploadImageFile?type=editor", type: 'post'}, height:120});

        var atBody = $("#LAY_layedit_"+teamReplyTextarea).contents().find('body');

//       @抄送人
        $.fn.atwho.debug = true;

        var ccMembers = [];

        $.each(members,function(k,v)
        {
            if(v.type === '1')
            {
                ccMembers.push(v);
            }
        });

        var member = $.map(ccMembers,function(value)
        {
            return {'id':value.member_id,'name':value.name};
        });

        var at_config = {
            at: "@",
            data: member,
            headerTpl: '<div class="atwho-header">'+language.SELECT_CC+'</div>',
            insertTpl: "<span class='cc-member'>@<span style='vertical-align: middle'>${name}</span></span>" +
            "<input type='hidden' name='ticket[cc_id][]' value='${id}'>",
            displayTpl: "<li data-id='${id}'>${name}</li>",
            limit: 200,
            callbacks:{
                beforeInsert: function(value)
                {
                    return value;
                }
            }
        };

        atBody.atwho(at_config);

//        快捷回复
        $(".quick-reply,.quick-close").on('click',function()
        {
            $("#quickBox,.quick-shade").fadeToggle('fast');

            $("#quickBox").find('dd').on('click',function()
            {
                var quick_content =  $(this).find('.quick_content').html();

                var oldText = layedit.getContent(index);

                var content;

                if(oldText)
                {
                    content = oldText + '<br />' + quick_content;
                }
                else
                {
                    content = quick_content;
                }

                $("#ticketReply").text(content);/*给文本域赋值。成功*/

//                重构编辑器
                index = layedit.build('ticketReply',{
                    uploadImage: {url: "/Upload/uploadImageFile?type=editor", type: 'post'},
                    height:120
                });
            });
        });

//        提交工单回复
        $('#statusMenu li,.submit-btn').on("click",function()
        {
            layedit.sync(index);

            layer.load(2,{offset:['150px']});

            $.post("/Ticket/reply",$('#replyForm').serialize(),function(data)
            {
                if(data.status === 0)
                {
                    feelDeskAlert(data.msg);
                }
                else
                {
                    feelDeskAlert(data.msg,data);

                    //当父级页面为待回复的工单时，回复成功后移除工单
                    if(parent.action === 'waitReplyTicket')
                    {
                        parent.$("tr[data-value='"+data.ticket_no+"'],div.ticket-list[data-value='"+data.ticket_no+"']").remove();
                    }
                }

            },'JSON');

            layer.close(index);
        });

//        提交团队沟通
        $('#progressNodeMenu li,.team-submit-btn').on("click",function()
        {
            layedit.sync(teamReplyTextarea);

            var teamReplyContent = layedit.getContent(teamReplyTextarea).trim();

            var obj = $("<p>"+teamReplyContent+"</p>");

            if(obj.find('span.atwho-inserted').length > 0)
            {
                var ccItem = '';

                $.each(obj.find('span.atwho-inserted'),function(k,v)
                {
                    if($(v).length > 0)
                    {
                        var value = $(this).find('input').val();

                        var cc_member_item = "<input type='hidden' name='team_reply[cc_member_id][]' value='"+value+"'>";

                        if($("#teamReplyForm").find('.cc-member-item').find("input[value='"+value+"']").length === 0)
                        {
                            $(".cc-member-item").append(cc_member_item);
                        }

                        if($('#cc-item').find("input[value='"+value+"']").length === 0)
                        {
                            ccItem += $(v).prop('outerHTML');
                        }
                    }
                });

                $("#cc-item").append(ccItem);
            }

            var loading = layer.load(2,{offset:'150px'});

            $.post("/Ticket/teamReply",$('#teamReplyForm').serialize(),function(data)
            {
                if(data.status === 0)
                {
                    feelDeskAlert(data.msg);
                }
                else
                {
                    feelDeskAlert(data.msg,data);
                }

                layer.close(loading);

            },'JSON');
        });
    });

//   工单回复框伸缩
	$("#reply-input").on('click',function(e)
    {
        $(this).parent('.reply-input').hide();

        $("#reply-textarea").slideToggle('fast',function ()
        {
            var textAreaHeight = $(this).height();

            if(parseInt(textAreaHeight) <= 300)
            {
                $(".ticket-detail-content").css('height','calc(100% - 300px)');
            }
            else
            {
                $(".ticket-detail-content").css('height','calc(100% - '+parseInt(textAreaHeight)+'px)');
            }
        });

        e.stopPropagation();

        $(".textarea-hide").on('click',function()
        {
            $("#reply-textarea").slideUp('fast',function()
            {
                $(".reply-input").show();

                $(".ticket-detail-content").css('height','calc(100% - 60px)');
            });
        })
    });

//   团队沟通框伸缩
    $("#team-reply-input").on('click',function(e)
    {
        $(this).parent('.team-reply-input').hide();

        $("#team-reply-textarea").slideToggle('fast',function ()
        {
            var textAreaHeight = $(this).height();

            if(parseInt(textAreaHeight) <= 300)
            {
                $(".ticket-detail-content").css('height','calc(100% - 300px)');
            }
            else
            {
                $(".ticket-detail-content").css('height','calc(100% - '+parseInt(textAreaHeight)+'px)');
            }
        });

        e.stopPropagation();

        $(".team-textarea-hide").on('click',function()
        {
            $("#team-reply-textarea").slideUp('fast',function()
            {
                $(".team-reply-input").show();

                $(".ticket-detail-content").css('height','calc(100% - 60px)');
            });
        })
    });

//   Tab切换
    $("#replyTab").find('a').on('click',function()
    {
        var itemNumber = $(this).data('value');

        $(this).addClass('active').siblings('a').removeClass('active');

        $("#item-"+itemNumber).removeClass('hidden').siblings('.response').addClass('hidden');

        if(itemNumber === 1)
        {
            if($('#reply-textarea').css('display') === 'none')
            {
                $("#reply-box-1,.reply-input").attr('style','display:block');

                $(".ticket-detail-content").css('height','calc(100% - 60px)');
            }

            if($('#team-reply-textarea').css('display') === 'block')
            {
                $(".ticket-detail-content").css('height','calc(100% - 60px)');
            }

            $("#reply-box-2,.team-reply-input,#team-reply-textarea").attr('style','display:none');
        }
        else if(itemNumber === 2)
        {
            if($('#team-reply-textarea').css('display') === 'none')
            {
                $("#reply-box-2,.team-reply-input").attr('style','display:block');

                $(".ticket-detail-content").css('height','calc(100% - 60px)');
            }

            if($('#reply-textarea').css('display') === 'block')
            {
                $(".ticket-detail-content").css('height','calc(100% - 60px)');
            }

            $("#reply-box-1,.reply-input,#reply-textarea").attr('style','display:none');
        }
        else
        {
            $(".ticket-detail-content").css('height','100%');

            $("#reply-box-1,#reply-box-2,.reply-input,.team-reply-input,#reply-textarea,#team-reply-textarea").attr('style','display:none');
        }
    });

//    大图
    var image = $('#images,.replyImage,#form-images,#email-images');

    image.find("img:not('.face')").css('cursor','pointer');

    image.viewer(
    {
        url: 'data-original',
        movable:true,
        scalable:false,
        zoomRatio:0.3
    });

    satisfyFun = function (reload)
    {
        var that = $(".satisfy");

        layer.open(
        {
            type: 1,
            title: that.data('title'),
            offset: '150px',
            area: ['500px','400px'],
            content: that,
            shade: 0.5,
            closeBtn: 1,
            scrollbar: true,
            success:function()
            {
                var rate = layui.rate;

                rate.render(
                {
                    elem: '#satisfy-star',
                    text: false,
                    value:5,
                    setText: function(value)
                    {
                        var satisfy = satisfyData.satisfy;

                        $('#satisfy').val(satisfy[value-1]['satisfy_id']);

                        $('#satisfy-score').val(satisfy[value-1]['score']);

                        $('#satisfy-name').text(satisfy[value-1]['lang_name']);

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
            },
            cancel:function(index)
            {
                layer.close(index);

                if(reload === true)
                {
                    window.location.reload();
                }
            }
        });

        $("#submit-satisfy").on("click",function()
        {
            var loading = layer.load(2,{offset:['15vw']});

            $.post('/Ticket/satisfy?id='+ticketId,$('#satisfy-form').serialize(),function(data)
            {
                if(data.status === 0)
                {
                    feelDeskAlert(data.msg);
                }
                else
                {
                    feelDeskAlert(data.msg,data);
                }

                layer.close(loading);

            },'JSON')
        })
    };
});


//选择满意度标签
var chooseSatisfyLabel = function (t,id)
{
    $(t).toggleClass('current');

    var satisfyForm = $('#satisfy-form');

    var labelInput = "<input type='hidden' name='satisfy[labelId][]' value='"+id+"' v='"+id+"'>";

    if(satisfyForm.find("input[v='"+id+"']").length === 0)
    {
        satisfyForm.append(labelInput);

    }
    else
    {
        satisfyForm.find("input[v='"+id+"']").remove()
    }
};

var showAuditOpinion = function ()
{
    $('#audit-progress').slideDown('fast',function ()
    {
        if($('.progress-footer').length === 0)
        {
            $('.progress-content').css('height','315px');
        }

        $('#audit-close').on('click',function()
        {
            $(this).parents('.audit-progress').fadeOut('fast');
        });
    });

    $(document).on('click',function()
    {
        $('.audit-progress').fadeOut('fast');
    })
};

var resubmitAudit = function(audit_status)
{
    var loading = layer.load(2,{offset:['150px']});

    $.post('/Ticket/detail?id='+ticketId,{source:'resubmit_audit',audit_status:audit_status},function(data)
    {
        if(data.errcode !== 0)
        {
            layer.close(loading);

            layer.msg(data.msg,{icon:2,time:2000,offset:['100px']});
        }
        else
        {
            layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
            {
                window.location.reload();
            });
        }
    })
};


var remindReviewer = function(auditor_id)
{
    var loading = layer.load(2,{offset:['150px']});

    $.post('/Ticket/detail?id='+ticketId,{source:'remind_audit',auditor_id:auditor_id},function(data)
    {
        if(data.errcode !== 0)
        {
            layer.close(loading);

            layer.msg(data.msg,{icon:2,time:2000,offset:['100px']});
        }
        else
        {
            layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
            {
                window.location.reload();
            });
        }
    })
};
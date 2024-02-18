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
$(function()
{
    var index,teamReplyTextarea;

    var detailMain = $(".detail-main");

    layui.use(['layedit','form','upload'], function()
    {
        var form = layui.form;

        var upload = layui.upload;

        upload.render(
        {
            elem: ".uploadFile",

            url:"/"+moduleName+"/Upload/UploadTicketFile",

            exts: 'zip|rar|txt|doc|docx|xlsx|xls|pptx|pdf|chf',

            type:'file',

            before: function()
            {
                layer.msg(language.UPLOADING_FILE,{time:100000,shade: [0.3, '#393D49'],offset:['150px']});
            },

            done: function(res)
            {
                var item = this.item;

                var uploadSource = item.data('value');

                if(res.status == 1)
                {
                    layer.msg(res.msg,{icon:1,time:1000,shift:0,offset:['150px']});

                    var attachItem = "<div>" +
                        "<i class='iconfont icon-fujian'></i>" +
                        "<span>"+res.name+"</span>" +
                        "<i class='iconfont icon-guanbi delete-attach' data-name='"+res.cname+"'></i>" +
                        "<input type='hidden' name='file[links][]' value='"+res.link+"' />" +
                        "<input type='hidden' name='file[names][]' value='"+res.name+"' />" +
                        "<input type='hidden' name='file[sizes][]' value='"+res.size+"' />" +
                        "<input type='hidden' name='file[types][]' value='"+res.type+"' />" +
                        "</div>";

                    $('#'+uploadSource+'-attach-item').append(attachItem);

                    detailMain.css('padding-bottom',parseInt(detailMain.css('padding-bottom'))+31+'px');
                }
                else
                {

                    layer.msg(res.msg,{icon:2,time:2000,offset:['150px']});
                }
            }
        });

//       删除上传文件
        $(document).on('click','.delete-attach',function()
        {
            var attach = $(this);

            layer.confirm(language.DELETE_FILE_TIP, {icon: 3, title:language.PROMPT,offset:['150px']}, function()
            {
                var name = attach.data('name');

                var loading = layer.load(2,{offset:['150px']});

                $.post("/"+moduleName+"/Upload/deleteUploadFile",{'file_name':name},function(data)
                {
                    layer.close(loading);

                    if(data.status == 1)
                    {
                        layer.msg(data.msg,{icon:1,time:1000,shift:0,offset:['150px']},function()
                        {
                            attach.parent().remove();

                            detailMain.css('padding-bottom',parseInt(detailMain.css('padding-bottom'))-31+'px');
                        });
                    }
                    else
                    {
                        layer.msg(data.msg,{icon:2,time:2000,offset:['150px']});
                    }
                },'JSON')
            })
        });

//        下拉菜单
        $(".select").on('click',function (e)
        {
            e.stopPropagation();

            $(this).siblings().find('.select-item').slideUp('fast');

            $(this).find('.select-item').slideToggle('fast');
        });

//        更新主题
        form.on('select(selectType)',function(data)
        {
            var ticketId = $(data.elem).data('id');

            var typeId =  data.value;

            var reg = /l---/g;

            var typeName = data.othis.find(".layui-this").text();

            typeName = typeName.replace(reg,'');

            if(typeId)
            {
                layer.confirm(
                    language.SET_TICKET_TYPE+" <span style='color:red'>"+typeName+"</span>",
                    {icon: 3, title:language.PROMPT,offset:['60px']},
                    function(index)
                    {
                        $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{typeId:typeId,source:'setType'},function(res)
                        {
                            if(res.status == 2)
                            {
                                $("#typeName").text(typeName);

                                $(".type-item").slideUp('fast');
                            }
                            else
                            {
                                layer.msg(res.msg,{icon:2,time:2000,offset:['100px']});
                            }
                        },'JSON');

                        layer.close(index);
                    }
                );
            }
        });

//        选择工单状态
        $('.update-status dd').unbind('click').on('click',function ()
        {
            var ticketId = $(this).parent('dl').data('value');

            var value = $(this).data('value');

            var status_color =$(this).find('span').css('color');

            var status_name =$(this).find('span').html();

            var loading = layer.load(2,{offset:['150px']});

            $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{value:value,source:'setStatus'},function(data)
            {
                layer.close(loading);

                if(data.errcode != 0)
                {
                    layer.msg(data.msg,{icon:2,time:2000,offset:['100px']});
                }
                else
                {
                    var minimalistStatus = window.parent.$("div[data-value='status"+data.ticket_no+"']");

                    var listStatus = window.parent.$("span[data-value='status"+data.ticket_no+"']");

                    minimalistStatus.css("background-color",status_color).html(status_name);

                    listStatus.css("background-color",status_color).html(status_name);

                    $('#currentStatus').css('color',status_color).html(status_name);

                    if(data.is_end == 'yes')
                    {
//                        满意度评价
                        layer.open(
                        {
                            type: 1,
                            title: false,
                            offset: '150px',
                            area: ['500px'],
                            content: $(".satisfy"),
                            shade: 0.5,
                            closeBtn: 1,
                            scrollbar: true,
                            cancel:function(index, layero)
                            {
                                layer.close(index);

                                window.location.href = data.url;
                            }
                        });

                        $(".sa-img").on('click',function()
                        {
                            var v = $(this).attr('v');

                            $("#satisfyConfig").attr('class','sa-sel-'+v);

                            $("#satisfy").val(v);
                        });

                        $("#sa_submit").on("click",function()
                        {
                            var satisfy = $("#satisfy").val();

                            var advise = $("#sa_advise").val();

                            $.post("/"+moduleName+'/Ticket/satisfy?id='+ticketId,{satisfy:satisfy,advise:advise},function(data)
                            {
                                if(data.status == 0)
                                {
                                    layer.msg(data.msg,{icon:2,time:1000,offset:['150px']});
                                }
                                else
                                {
                                    layer.msg(data.msg,{icon:1,time:1000,shift:0,offset:['150px']},function()
                                    {
                                        window.location.href = data.url;
                                    });
                                }
                            },'JSON')
                        })
                    }
                    else if(data.refresh == 1)
                    {
                        window.location.href = data.url;
                    }
                    else
                    {
                        return false;
                    }
                }

                $(".status-item").slideUp('fast');

            },'JSON');
        });

//        选择进度节点
        $('.update-progress-node dd').unbind('click').on('click',function ()
        {
            var ticketId = $(this).parent('dl').data('value');

            var value = $(this).data('value');

            var node_color =$(this).find('span').css('color');

            var node_name =$(this).find('span').html();

            var loading = layer.load(2,{offset:['150px']});

            $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{value:value,source:'setProgressNode'},function(data)
            {
                layer.close(loading);

                if(data.errcode != 0)
                {
                    layer.msg(data.msg,{icon:2,time:2000,offset:['100px']});
                }
                else
                {
                    var listStatus = window.parent.$("span[data-value='progress-node"+data.ticket_no+"']");

                    listStatus.css("color",node_color).html(node_name).addClass('list-progress-node');

                    $('#currentProgressNode').css('color',node_color).html(node_name);
                }

                $(".progress-node-item").slideUp('fast');

            },'JSON');
        });

//        选择优先级
        $('.update-priority dd').unbind('click').on('click',function ()
        {
            var ticketId = $(this).parent('dl').data('value');

            var priority = $(this).data('value');

            var loading = layer.load(2,{offset:['150px']});

            $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{priority:priority,source:'setPriority'},function(data)
            {
                layer.close(loading);

                if(data.errcode == 1)
                {
                    layer.msg(data.msg,{icon:2,time:2000,offset:['100px']});
                }
                else
                {
                    var minimalistPriority = window.parent.$("span[data-value='priority"+data.ticket_no+"']");

                    var listPriority = window.parent.$("td[data-value='priority"+data.ticket_no+"']").find('span');

                    listPriority.removeClass().addClass("ticket-priority "+data.priorityColor).html(data.priority);

                    minimalistPriority.removeClass().addClass("ticket-priority "+data.priorityColor).html(data.priority);

                    layer.msg(data.msg,{icon:1,time:2000,offset:['100px']});

                    window.location.href = data.url;
                }

                $(".priority-item").slideUp('fast');

            },'JSON');
        });

//       选择处理人
        form.on('select(selectUser)',function(data)
        {
            var ticketId = $(data.elem).data('id');

            var userId =  data.value;

            var name = data.othis.find(".layui-this").text();

            var face = $(data.elem).find("option[value='"+userId+"']").data('face');

            face = face ? face : '/Attachs/face/face.png';

            if(userId)
            {
                layer.confirm
                (
                    language.SURE_ASSIGN_TICKET+" <span style='color:red'>"+name+"</span>",
                    {icon: 3, title:language.PROMPT,offset:['60px']},
                    function(index)
                    {
                        var loading = layer.load(2,{offset:['150px']});

                        $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{userId:userId,source:'assignUser'},function(res)
                        {
                            layer.close(loading);

                            if(res.status == 2)
                            {
                                if($("#receive").data('value') == 0)
                                {
                                    $("#receiveFace").attr('src',face);

                                    $("#receiveUser").text(name)
                                }

                                $("#assignUser").remove();

                                $("#disposeFace").attr('src',face);

                                $("#disposeUser").css('display','inline-block').find('span').text(name);
                            }
                            else
                            {
                                layer.msg(res.msg,{icon:2,time:2000,offset:['100px']});
                            }

                            $(".user-item").slideUp('fast');

                        },'JSON');

                        layer.close(index);
                    }
                );
            }
        });

//       转交工单
        $('.transfer-group dd').hover(function()
        {
            var ticketId = $(this).parent('dl').data('value');

            $(this).find('span').addClass('active');

            $(this).find('ul').show();

            $(this).find('li').unbind('click').on('click',function()
            {
                var group_id = $(this).parents('dd').data('value');

                var user_id = $(this).data('id');

                var loading = layer.load(2,{offset:['150px']});

                $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{groupId:group_id,userId:user_id,source:'transferUser'},function(data)
                {
                    layer.close(loading);

                    if(data.errcode == 1)
                    {
                        layer.msg(data.msg,{icon:2,time:2000,offset:['100px']});
                    }
                    else
                    {
                        layer.msg(data.msg,{icon:1,time:1000,offset:['100px']});

                        window.location.href = data.url;
                    }

                    $(".group-item").slideUp('fast');

                },'JSON');
            })
        },
        function()
        {
            $(this).find('span').removeClass('active');

            $(this).find('ul').hide();
        });

        $("#info").unbind('click').stop(true).on('click',function ()
        {
            $(this).toggleClass('icon-shouqi');

            $("#detailInfo").slideToggle('fast');
        });

        var layedit = layui.layedit;

//        建立编辑器
        index = layedit.build('ticketReply',{uploadImage: {url:"/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'}, height:120});

        teamReplyTextarea = layedit.build('teamReply',{uploadImage: {url:"/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'}, height:120});

        var atBody = $("#LAY_layedit_2").contents().find('body');

//       @抄送人
        $.fn.atwho.debug = true;

        var member = $.map(members,function(value,i)
        {
            return {'id':value.member_id,'name':value.name};
        });

        var at_config = {
            at: "@",
            data: member,
            headerTpl: '<div class="atwho-header">'+language.SELECT_CC+'</div>',
            insertTpl: "<span class='cc-member'>@<span style='vertical-align: middle'>${name}</span></span>" +
            "<input type='checkbox' name='ticket[cc_id][]' value='${id}' style='display:none' checked='checked'>",
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
            $("#quickBox,.quick-shade").fadeToggle();

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
                    uploadImage: {url:"/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'},
                    height:120
                });
            });
        });

//        提交工单回复
        $('#statusMenu li,.submit-btn').on("click",function()
        {
            layedit.sync(index);

            var loading = layer.load(2);

            $.post("/"+moduleName+"/Ticket/reply",$('#replyForm').serialize(),function(ret)
            {
                layer.close(loading);

                if(ret.status == 0)
                {
                    layer.msg(ret.msg,{icon:2,time:1000,offset:['150px']});
                }
                else
                {
                    layer.msg(ret.msg,{icon:1,time:1000,shift:0,offset:['150px']},function()
                    {
                        window.location.href = ret.url;
                    });
                }
            },'JSON');

            layer.close(index);
        });

//        提交团队沟通
        $('#progressNodeMenu li,.team-submit-btn').on("click",function()
        {
            layedit.sync(teamReplyTextarea);

            var teamReplyContent = layedit.getContent(teamReplyTextarea);

            $("#cc-item").append(teamReplyContent);

            var loading = layer.load(2);

            $.post("/"+moduleName+"/Ticket/teamReply",$('#teamReplyForm').serialize(),function(ret)
            {
                layer.close(loading);

                if(ret.status == 0)
                {
                    layer.msg(ret.msg,{icon:2,time:1000,offset:['150px']});
                }
                else
                {
                    layer.msg(ret.msg,{icon:1,time:1000,shift:0,offset:['150px']},function()
                    {
                        window.location.href = ret.url;
                    });
                }
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
                detailMain.css('padding-bottom','300px');
            }
            else
            {
                detailMain.css('padding-bottom',parseInt(textAreaHeight)+'px');
            }
        });

        e.stopPropagation();

        $(".textarea-hide").click(function()
        {
            $("#reply-textarea").slideUp('fast',function()
            {
                $(".reply-input").show();

                detailMain.css('padding-bottom','60px');
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
                detailMain.css('padding-bottom','300px');
            }
            else
            {
                detailMain.css('padding-bottom',parseInt(textAreaHeight)+'px');
            }
        });

        e.stopPropagation();

        $(".team-textarea-hide").click(function()
        {
            $("#team-reply-textarea").slideUp('fast',function()
            {
                $(".team-reply-input").show();

                detailMain.css('padding-bottom','60px');
            });
        })
    });

//   Tab切换
    $("#replyTab").find('a').on('click',function()
    {
        var itemNumber = $(this).data('value');

        $(this).addClass('active').siblings('a').removeClass('active');

        $("#item-"+itemNumber).removeClass('hidden').siblings('.response').addClass('hidden');

        if(itemNumber == 1)
        {
            if($('#reply-textarea').css('display') == 'none')
            {
                $("#reply-box-1,.reply-input").attr('style','display:block');
            }

            if($('#team-reply-textarea').css('display') == 'block')
            {
                detailMain.css('padding-bottom','60px');
            }

            $("#reply-box-2,.team-reply-input,#team-reply-textarea").attr('style','display:none');
        }
        else if(itemNumber == 2)
        {
            if($('#team-reply-textarea').css('display') == 'none')
            {
                $("#reply-box-2,.team-reply-input").attr('style','display:block');
            }

            if($('#reply-textarea').css('display') == 'block')
            {
                detailMain.css('padding-bottom','60px');
            }

            $("#reply-box-1,.reply-input,#reply-textarea").attr('style','display:none');
        }
        else
        {
            detailMain.css('padding-bottom','0');

            $("#reply-box-1,#reply-box-2,.reply-input,.team-reply-input,#reply-textarea,#team-reply-textarea").attr('style','display:none');
        }
    });

//    选择回复状态
	$("#status-arrow").click(function()
	{
        $(".submit-reply,#status-arrow").toggleClass('ui-border');

		$("#statusMenu").slideToggle('fast');
	});

//    选择沟通状态
    $("#progress-node-arrow").click(function()
    {
        $(".team-submit-reply,#progress-node-arrow").toggleClass('ui-border');

        $("#progressNodeMenu").slideToggle('fast');
    });

//    选择抄送人
	$("#cc").click(function()
    {
        $("#cc-group").toggleClass('ui-block');

        $(".cc-user").removeClass('ui-block');

        $(".cc-reply").toggleClass('ui-border');

        $(".group-btn,#group-arrow").unbind('click').on('click',function(e)
        {
            $("#cc-group,#group-arrow").toggleClass('ui-border');

            $("#CcGroupMenu").slideToggle('fast',function()
            {
                $(this).find('li').hover(function()
                {
                    $(this).find('ul').stop(true,true).slideDown('500',function()
                    {
                        $(this).find('li').unbind('click').click(function ()
                        {
                            var value = $(this).data('value');

                            if (value > 0)
                            {
                                var exist = 0;

                                var name = $(this).text();

                                $.each($("#cc-item").find('div'), function ()
                                {
                                    var user_id = $(this).data('value');

                                    if (user_id == value) exist = 1;
                                });

                                if (exist == 1)
                                {
                                    layer.msg(language.CC_DUPLICATION, {offset: ['150px']});

                                    return false;
                                }
                                else
                                {
                                    layer.confirm
                                    (
                                        language.CC_MEMBER + name,

                                        {btn: [language.SURE, language.CANCEL], icon: 3, shade: false, offset: ['150px']},

                                        function (index)
                                        {
                                            $("#cc-item").slideDown('fast');

                                            $(".cc-user").find('.user-btn').text(name);

                                            var ccItem = "<div class='ui' data-value=" + value + ">" + name + "" +
                                                "<i class='iconfont icon-close1'></i>" +
                                                "<input type='checkbox' name='ticket[cc_id][]' value=" + value + " style='display:none' checked='checked'>" +
                                                "</div>";

                                            $("#cc-item").append(ccItem);

                                            layer.close(index);
                                        }
                                    );
                                }
                            }
                        });
                    })
                },
                function()
                {
                    $(this).find('ul').stop(true,true).slideUp('fast');
                })
            });
        })
    });

//    移除抄送人
    $(document).on("click", ".icon-close1", function ()
    {
        var removeCc = $(this).parent();

        var name = removeCc.text();

        removeCc.toggleClass('ui-border');

        layer.confirm
        (
            language.MOVE_CC+name+'?',

            {btn: [language.SURE,language.CANCEL],icon:3,shade: false,offset:['150px']},

            function(index)
            {
                removeCc.remove();

                if($("#cc-item").find('div').length === 0)
                {
                    $('#cc-item').slideUp('fast');
                }

                layer.close(index);
            },
            function()
            {
                removeCc.toggleClass('ui-border');
            }
        );
    });

//    大图
    var image = $('#images,.replyImage');

    image.find("img:not('.face')").css('cursor','pointer');

    image.viewer(
    {
        url: 'data-original',
        movable:true,
        scalable:false,
        zoomRatio:0.3
    });
});

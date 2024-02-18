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

$(function()
{
    var index = '';

    layui.config({
        base: '/Public/js/layui/extends/',
        version: '1.0.0'
    }).extend({
        layUploader:'uploader/layUploader'
    }).use(['layedit','form','upload','rate','layUploader'], function()
    {
        var form = layui.form;

        var layUploader = layui.layUploader;

        /* 附件上传 */
        $("#uploadReplyFile,#uploadInsideReplyFile").unbind('click').click(function ()
        {
            layUploader.render(
            {
                url:"/"+moduleName+'/Upload/UploadTicketFile',//上传文件服务器地址，必填
                reload:false,
                source:'reply',
                replyType:'reply' //回复类型 reply 工单回复
            });
        });

//      删除上传文件
        $(document).on('click','.delete-attach',function()
        {
            var attach = $(this);

            layer.confirm(language.DELETE_FILE_TIP, {icon: 3, title:language.PROMPT,offset:['150px']}, function()
            {
                var name = attach.data('name');

                var loading = layer.load(2,{offset:['150px']});

                $.post("/"+moduleName+"/Upload/deleteUploadFile",{'file_name':name},function(data)
                {
                    if(data.status == 1)
                    {
                        layer.msg(data.msg,{icon:1,time:1000,shift:0,offset:['150px']},function()
                        {
                            attach.parent().remove();
                        });
                    }
                    else
                    {
                        layer.msg(data.msg,{icon:2,time:2000,offset:['150px']});
                    }

                    layer.close(loading);

                },'JSON')
            })
        });

        var layedit = layui.layedit;

//        建立编辑器
        index = layedit.build('ticketReply',{uploadImage: {url: "/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'}, height:120});

//        提交回复
        $('.submit-btn').on("click",function()
        {
            layedit.sync(index);

            layer.load(2,{offset:'15vw'});

            $.post("/"+moduleName+"/Ticket/reply",$('#replyForm').serialize(),function(data)
            {
                if(data.status == 0)
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

//    选择工单状态
    $('.update-status dd').unbind('click').on('click',function ()
    {
        var ticketId = $(this).parent('dl').data('value');

        var value = $(this).data('value');

        var status_color =$(this).find('span').css('color');

        var status_name =$(this).find('span').html();

        var loading = layer.load(2,{offset:['150px']});

        $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{value:value,source:'setStatus'},function(data)
        {
            if(data.errcode != 0)
            {
                if(data.errcode == 3)//结束工单如果需要审核时启用
                {
                    feelDeskAlert(data.msg,data);
                }
                else
                {
                    feelDeskAlert(data.msg);
                }
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
                    layer.close(loading);

                    satisfyFun(true);
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

            layer.close(loading);

        },'JSON');
    });

//    审核工单 - 通过
    $('#pass-audit').unbind('click').on('click',function ()
    {
        var loading = layer.load(2,{offset:['150px']});

        $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{value:10,source:'passAudit'},function(data)
        {
            if(data.errcode != 0)
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
        },'JSON');
    });

//    审核工单 - 驳回
    $('#reject-audit').unbind('click').on('click',function()
    {
        layer.open(
        {
            type: 1,
            title: false,
            offset: '150px',
            area: ['500px'],
            content: $(".ticket-reject"),
            shade: 0.5,
            closeBtn: 1,
            scrollbar: true,
            cancel:function(index)
            {
                layer.close(index);
            },
            success:function()
            {
                $('#submit-reject').unbind('click').on('click',function()
                {
                    var loading = layer.load(2,{offset:['150px']});

                    var content = $("#reject-form").find('textarea').val();

                    $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{value:20,source:'rejectAudit',content:content},function(data)
                    {
                        if(data.errcode != 0)
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
                    },'JSON');
                });
            }
        });
    });

    $('.opinion-title').next('.progress-item').css('margin-top',0).nextAll('.progress-item:last').css('margin-bottom','20px');

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

        $(".textarea-hide").click(function()
        {
            $("#reply-textarea").slideUp('fast',function()
            {
                $(".reply-input").show();

                $(".ticket-detail-content").css('height','calc(100% - 60px)');
            });
        });
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

    $(".select").on('click',function (e)
    {
        e.stopPropagation();

        $(this).siblings().find('.select-item').slideUp('fast');

        $(this).find('.select-item').slideToggle('fast');
    });

    satisfyFun = function (reload)
    {
//       满意度评价
        layer.open(
        {
            type: 1,
            title: "满意度评价",
            offset: '150px',
            area: ['500px','400px'],
            content: $(".satisfy"),
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
            cancel:function(index, layero)
            {
                layer.close(index);

                if(reload == true)
                {
                    window.location.reload();
                }
            }
        });

        $("#submit-satisfy").on("click",function()
        {
            var loading = layer.load(2,{offset:['15vw']});

            $.post("/"+moduleName+'/Ticket/satisfy?id='+ticketId,$('#satisfy-form').serialize(),function(data)
            {
                if(data.status == 0)
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
    }
});

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

var showAuditOpinion = function ()
{
    $('#audit-progress').slideDown('fast',function ()
    {
        if($('.progress-footer').length == 0)
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

    $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{source:'resubmit_audit',audit_status:audit_status},function(data)
    {
        if(data.errcode != 0)
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

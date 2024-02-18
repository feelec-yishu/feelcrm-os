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
    var index;

    var detailMain = $(".detail-main");

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
        $("#uploadReplyFile").unbind('click').click(function ()
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
                    if(data.status == 1)
                    {
                        feelDeskAlert(data.msg);

                        attach.parent().remove();

                        detailMain.css('padding-bottom',parseInt(detailMain.css('padding-bottom'))-31+'px');
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
            e.stopPropagation();

            $(this).siblings().find('.select-item').slideUp('fast');

            if($(e.target).attr('id') != undefined || $(e.target).parent().attr('id') != undefined)
            {
                $(this).find('.select-item').slideToggle('fast');
            }

            $(document).bind('click',function(e)
            {
                $(".select").find('.select-item').hide();
            });
        });

//        选择工单状态
        $('.update-status dd').unbind('click').on('click',function ()
        {
            var o = $(this);

            var ticketId = $(this).parent('dl').data('value');

            var value = $(this).data('value');

            var status_class =$(this).find('span').attr('class');

            var status_name =$(this).find('span').html();

            var loading = layer.load(2,{offset:['150px']});

            $.post('/subTicket/detail?id='+ticketId,{value:value,source:'setStatus'},function(data)
            {
                if(data.errcode != 0)
                {
                    feelDeskAlert(data.msg);
                }
                else
                {
                    var minimalistStatus = window.parent.$("div[data-value='status"+data.ticket_no+"']");

                    var listStatus = window.parent.$("span[data-value='status"+data.ticket_no+"']");

                    $('#currentStatus').attr('class','').addClass('status-color '+status_class).html(status_name);

                    if(value == 20)
                    {
                        minimalistStatus.addClass('bg-85D654').html(status_name);

                        listStatus.addClass('bg-85D654').html(status_name);

                        $('#currentStatus').nextAll().remove();

                        $('.title-right,.reply-box').remove();
                    }
                    else
                    {
                        minimalistStatus.addClass('bg-ff2e4b').html(status_name);

                        listStatus.addClass('bg-ff2e4b').html(status_name);
                    }

                    feelDeskAlert(data.msg);
                }

                $(".status-item").slideUp('fast');

                layer.close(loading);

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

                        $.post('/Ticket/detail?id='+ticketId,{userId:userId,source:'assignUser'},function(data)
                        {
                            if(data.status == 2)
                            {
                                if($("#receive").data('value') == 0)
                                {
                                    $("#receiveFace").attr('src',face);

                                    $("#receiveUser").text(name)
                                }

                                $("#disposeFace").attr('src',face);

                                $("#disposeName").text(name);
                            }
                            else
                            {
                                feelDeskAlert(data.msg);
                            }

                            $(".user-item").slideUp('fast');

                            layer.close(loading);

                        },'JSON');

                        layer.close(index);
                    }
                );
            }
        });

        var layedit = layui.layedit;

//        建立编辑器
        index = layedit.build('ticketReply',{uploadImage: {url: "/Upload/uploadImageFile?type=editor", type: 'post'}, height:120});

//        提交工单回复
        $('#statusMenu li,.submit-btn').on("click",function()
        {
            layedit.sync(index);

            var loading = layer.load(2,{offset:['150px']});

            $.post("/SubTicket/reply",$('#replyForm').serialize(),function(data)
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

            layer.close(index);
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

            $("#reply-box-2").attr('style','display:none');
        }
        else
        {
            detailMain.css('padding-bottom','0');

            $("#reply-box-1,#reply-box-2,.reply-input,#reply-textarea").attr('style','display:none');
        }
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
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

var j = 0;

layui.use('upload', function()
{
    var upload = layui.upload;

    var uploading,fileLoad;

    var drag = $(".layui-upload-drag");

    var dragWidth = drag.width();

    drag.css({'height':dragWidth+'px','line-height':dragWidth+'px'});

    /* 图片上传 */
    upload.render(
    {
        elem: ".uploadImg",

        url: "/"+moduleName+"/Upload/uploadImageFile.html?type=ticket",

        title:' ',

        multiple: true,

        drag: true,

        progress: function(n, elem, e)
        {
            // 获取进度百分比
            var percent = n + '%';

            var file = $('#'+e.fileId).find('.upload-percent');

            if(n < 100)
            {
                file.css('display','inline-block').text(percent);
            }
            else
            {
                file.css('display','none');
            }
        },
        before:function(obj)
        {
            this.isSuccess = 0;

            var item = this.item;

            obj.preview(function(index, file, result)
            {
                var imgDom = '<div class="layui-upload-drag" id="'+index+'" style="height:'+dragWidth+'px;line-height: '+dragWidth+'px">' +
                    '<img src="" alt="" onclick=openVisitorPhoto('+parseInt(j)+',"images") />' +
                    '<span class="upload-percent">0%</span>'+
                    '</div>';

                item.before(imgDom);

                j++;
            });

            uploading = layer.msg(language.UPLOADING_IMAGE, {time: 1000000,offset: ['40%']});
        },
        done: function (data,index)
        {
            if (data.code === 0)
            {
                var fileForm = '<a href="javascript:;" class="cancel iconfont icon-cancel" data-name="'+data.img_name[0]+'"></a>' +
                    '<input type="hidden" name="photo[]" value="' + data.url[0] + '" />';

                $('#'+index).find('img').attr('src',data.thumb[0]).after(fileForm);

                this.isSuccess++;
            }
            else
            {
                layer.msg(data.msg, {icon: 2, time: 3000, shift: 0, offset: ['40%']});
            }
        },
        allDone:function(obj)
        {
            if(this.isSuccess === obj.successful)
            {
                layer.msg('ok!',{time:1000,offset:['40%']});
            }
            else
            {
                layer.msg('Failed - '+obj.aborted,{time:2000,offset:['40%']});
            }

            layer.close(uploading);
        }
    });

    /* ocr图片上传 */
    upload.render(
    {
        elem: ".ocruploadImg",

        url: "/"+moduleName+"/Upload/uploadOcrImageFile.html",

        title:' ',

        multiple: true,

        drag: true,

        acceptMime: 'image/*',

        before: function ()
        {
            uploading = layer.msg(language.UPLOADING_IMAGE, {time: 100000,offset: ['40%']});
        },
        done: function (res)
        {
            layer.close(uploading);

            if (res.code === 0)
            {
                var thumbImg = res.thumbImg;

                var ocrloading = layer.msg('识别中',{time:100000,offset:['40%']});

                $.post("/"+moduleName+"/AjaxRequest/ocrCardDiscern",{'thumbImg':thumbImg},function(result)
                {
                    layer.close(ocrloading);

                    if(result.errcode === 2)
                    {
                        layer.msg(result.msg,{icon:2,time:2000,offset:['40%']});
                    }
                    else
                    {
                        window.location.href = result.url;
                    }
                },'JSON');

            }
            else
            {
                layer.msg(res.msg, {icon: 2, time: 3000, shift: 0, offset: ['150px']});
            }
        }
    });

    /* 删除上传图片 */
    $(document).on('click','.cancel',function(e)
    {
        e.stopPropagation();

        var obj = $(this);

        var name = obj.data('name');

        var loading = layer.load(2,{offset:['40%']});

        $.post("/"+moduleName+"/Upload/deleteUploadFile",{'file_name':name},function(res)
        {
            layer.close(loading);

            if(res.status === 1)
            {
                obj.parent().remove();

                j--;
            }
            else
            {
                layer.msg(res.msg,{icon:2,time:2000,offset:['40%']});
            }
        },'JSON');
    });

    /* 附件上传 */
    upload.render(
    {
        elem: ".uploadFile",

        url: "/"+moduleName+"/Upload/UploadTicketFile",

        accept:'file',

        exts: 'zip|rar|7z|txt|doc|docx|xls|xlsx|pdf|ppt|pptx|gif|jpg|jpeg|bmp|png|xmind|mp4|avi|mov',

        progress: function(n, elem, e)
        {
            // 获取进度百分比
            var percent = n + '%';

            $('#file-percent').text(percent);
        },
        before:function()
        {
            fileLoad = layer.msg(language.UPLOADING_FILE+" ( <span id='file-percent'>0%</span> )",{time:100000,shade: [0.3, '#393D49'],offset:['40%']});
        },
        done: function(data)
        {
            layer.close(fileLoad);

            if(data.status === 1)
            {
                layer.msg(data.msg);

                var str = '<li>';

                str += '<i class="iconfont icon-fujian"></i>';

                str += '<span>'+data.name+'</span>';

                str += '<span class="iconfont icon-close2 closed" style="cursor: pointer" data-name="'+data.cname+'" id="closed"></span>';

                str += '<input type="hidden" name="file[saves][]" value="'+data.save+'" />';

                str += '<input type="hidden" name="file[names][]" value="'+data.name+'"/>';

                str += '<input type="hidden" name="file[sizes][]" value="'+data.size+'"/>';

                str += '<input type="hidden" name="file[types][]" value="'+data.type+'"/>';

                str += '<input type="hidden" name="file[links][]" value="'+data.link+'" /></li>';

                $('#attachments').append(str);
            }
            else
            {
                layer.msg(data.msg);
            }
        }
    });

    /* 删除上传文件 */
    $(document).on('click','span[id="closed"]',function()
    {
        var t = $(this);

        layer.confirm(language.DELETE_FILE_TIP,
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
            btn2:function(index)
            {
                var name = t.data('name');

                var loading = layer.load(2,{offset:['150px']});

                $.post("/"+moduleName+"/Upload/deleteUploadFile",{'file_name':name},function(data)
                {
                    layer.close(loading);

                    if(data.status === 1)
                    {
                        t.parent().remove();
                    }
                    else
                    {
                        layer.msg(data.msg);
                    }
                },'JSON');

                layer.close(index);
            }
        });
    });

//    上传头像
    upload.render(
    {
        elem:"#uploadLogoAndFace",

        url: "/"+moduleName+"/Upload/uploadHeadFile.html?type="+$('#uploadLogoAndFace').data('value'),

        before:function()
        {
            layer.msg(language.UPLOADING_IMAGE,{time:100000,offset:['40%']});
        },
        done: function(data)
        {
            if(data.status === 0)
            {
                layer.msg(data.msg,{time:2000,offset:['40%']});
            }
            else
            {
                layer.msg(data.msg,{time:1000,offset:['40%']});

                var formObj = $('#imageForm');

                $('#image-url').val(data.face_name);

                $('#image').val(data.url);

                $('#image-name').val(data.face_name);

                $.post(formObj.attr('action'),formObj.serialize(),function(data){});

                this.item.attr('src',data.url);
            }
        }
    });
});

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
    layui.config({
        base: '/Public/js/layui/extends/',
        version: '1.0.0'
    }).extend({
        layUploader:'uploader/layUploader'
    }).use(['form','upload','element','layedit','layUploader'], function()
    {
		var form = layui.form,
            element = layui.element,
            layuiEditor,layuiEditorEn,layuiEditorJp,fastEditor,tempEditor,followEditor,layedit = layui.layedit,
            layUploader = layui.layUploader,
            upload = layui.upload;

        var j = 1;

        var fileLoad;

        /* 图片上传 */
        upload.render(
        {
            elem: ".uploadImg",

            url: "/"+moduleName+"/Upload/uploadImageFile",

            multiple:true,

            drag:true,

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

                fileLoad = layer.msg(language.UPLOADING_IMAGE+'...',{time:100000,shade: [0.3, '#393D49'],offset:['20vw']});

                obj.preview(function(index, file, result)
                {
                    var imgDom = '<div class="layui-upload-drag" id="'+index+'">' +
                        '<img src="" alt=""/>' +
                        '<span class="upload-percent">0%</span>'+
                        '</div>';

                    item.before(imgDom);

                    return true;
                });
            },
            done: function(data,index)
            {
                if(data.code === 0)
                {
                    var fileForm = '<a href="javascript:void(0);" class="cancel" data-name="'+data.img_name[0]+'" title='+language.DELETE_IMAGE+'>'+language.DELETE+'</a>' +
                        '<input type="hidden" name="photo[]" value="' + data.url[0] + '" />';

                    $('#'+index).addClass('border-none').find('img').attr('src',data.thumb[0]).after(fileForm);

                    this.isSuccess++;
                }
            },
            allDone:function(obj)
            {
                if(this.isSuccess === obj.successful)
                {
                    feelDeskAlert('ok!',[]);
                }
                else
                {
                    feelDeskAlert('Failed - '+obj.aborted,[]);
                }

                layer.close(fileLoad);
            }
        });

		/* 产品图片上传 */
		upload.render(
		{
			elem: ".prouploadImg",

			url: "/"+moduleName+"/Upload/uploadImageFile",

            multiple:true,

            drag:true,

			before:function()
			{
                fileLoad = layer.msg(language.UPLOADING_IMAGE,{time:100000,shade: [0.3, '#393D49'],offset:['2vh']});
			},
            done: function(data)
            {
                layer.close(fileLoad);

                if(data.code === 0)
				{
					var br = '';var mr = '';

					var img = data.url;

					for(var i in img)
					{
						if(parseInt(j+3)%3 === 0) mr = 'mr0';

					    var imgDom = br+'<div class="layui-upload-drag '+mr+'">' +
                            '<img src="'+data.thumb[0]+'" alt=""/>' +
                            '<a href="javascript:void(0);" class="cancel" data-name="'+data.img_name[0]+'" title='+language.DELETE_IMAGE+'>X</a>' +
                            '<input type="hidden" name="proPhoto" value="' + data.url[0] + '" />' +
                            '</div>';

                        $('.productImgList').html(imgDom);

                        j++;
					}

				}

                feelDeskAlert(data.msg);
			}
		});

        /* 附件上传 */
        $("#uploadFile").unbind('click').click(function ()
        {
            var visitorToken = $(this).data('value');

            layUploader.render(
            {
                url:"/"+moduleName+'/Upload/UploadTicketFile',
                reload:false,
                source:'ticket',
                visitorToken:visitorToken === undefined ? '' : visitorToken //游客发工单时的公司login_token
            });
        });

		/* 删除上传图片 */
		$(document).on('click','.cancel',function()
		{
			var obj = $(this);

			layer.confirm(language.DELETE_IMAGE_TIP, {icon: 3, title:language.PROMPT,offset:['150px']}, function(index)
			{
				var name = obj.data('name');

                var loading = layer.load(2,{offset:['150px']});

				$.post("/"+moduleName+"/Upload/deleteUploadFile",{'file_name':name},function(data)
				{
					if(data.status === 1)
					{
                        obj.parent().remove();
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

		/* 删除上传文件 */
		$(document).on('click','span[id="closed"]',function()
		{
			var t = $(this);

			layer.confirm(language.DELETE_FILE_TIP, {icon: 3, title:language.PROMPT,offset:['150px']}, function(index)
			{
				var name = t.data('name');

				var loading = layer.load(2,{offset:['150px']});

				$.post("/"+moduleName+"/Upload/deleteUploadFile",{'file_name':name},function(data)
				{
					if(data.status === 1)
					{
                        t.parent().remove();
					}
					else
					{
                        feelDeskAlert(data.msg);
					}

                    layer.close(loading);

                },'JSON');

                layer.close(index);
			});
		});

		var ticketEditor = [],layEditBody = [];

		for(var i = 1;i <= $(".ticket-textarea").length;i++)
		{
            /* 编辑器 */
            ticketEditor[i] = layedit.build('ticketEditor'+i,{
                uploadImage: {url:"/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'},
                hideTool:['face','|','underline','del','italic'],
                height:200
            }); //建立编辑器

            layEditBody[i] = $("#LAY_layedit_"+i).contents().find('body');

            if(!layEditBody[i].length)
            {
                layEditBody[i] = $("iframe[textarea='ticketEditor"+i+"']").contents().find('body');
            }

            layEditBody[i].unbind('paste').bind('paste',function(e)
            {
                //剪切板
                var clipboardData = event.clipboardData || window.clipboardData || event.originalEvent.clipboardData;

                var items, item, types;

                if(clipboardData)
                {
                    items = clipboardData.items;

                    if( !items ) return;

                    item = items[0];

                    types = clipboardData.types || [];

                    for(var m = 0; m < types.length; m++ )
                    {
                        if( types[m] === 'Files' )
                        {
                            item = items[m];
                            break;
                        }
                    }

                    if( item && item.kind === 'file' && item.type.match(/^image\//i) )
                    {
                        feeldesk.imgReader(item,$(this));

                        //阻止默认事件, 避免重复添加
                        e.originalEvent.preventDefault();
                    }
                }
            });
		}

        layuiEditor = layedit.build('layuiEditor',{uploadImage: {url:"/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'},height:300}); //建立编辑器

		layuiEditorEn = layedit.build('layuiEditorEn',{uploadImage: {url:"/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'},height:300}); //建立编辑器

		layuiEditorJp = layedit.build('layuiEditorJp',{uploadImage: {url:"/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'},height:300}); //建立编辑器

        fastEditor = layedit.build('fastEditor',{tool: ['strong','italic' ,'underline' ,'|' ,'left' ,'center','right','link' ,'unlink','face'], height:190});

        tempEditor = layedit.build('tempEditor',{tool: [ 'strong', 'italic', 'underline', 'del', '|', 'left', 'center', 'right']});

		followEditor = layedit.build('follow_content',{uploadImage: {url:"/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'},tool: ['strong','italic' ,'underline' ,'del' ,'|' ,'left' ,'center','right','image'], height:200}); //建立编辑器

        var layEditBodyFollow = $("iframe[textarea='follow_content']").contents().find('body');

        layEditBodyFollow.unbind('paste').bind('paste',function(e)
        {
            //剪切板
            var clipboardData = event.clipboardData || window.clipboardData || event.originalEvent.clipboardData;

            var items, item, types;

            if(clipboardData)
            {
                items = clipboardData.items;

                if( !items ) return;

                item = items[0];

                types = clipboardData.types || [];

                for(var m = 0; m < types.length; m++ )
                {
                    if( types[m] === 'Files' )
                    {
                        item = items[m];
                        break;
                    }
                }

                if( item && item.kind === 'file' && item.type.match(/^image\//i) )
                {
                    feeldesk.imgReader(item,$(this));

                    //阻止默认事件, 避免重复添加
                    e.originalEvent.preventDefault();
                }
            }
        });

		/* 提交 */
		$("#submitForm,#submitForm2,.submitForm").on('click',function()
		{
			var loading = layer.load(2,{offset:'15vw'});

			for(var i = 1;i <= $(".ticket-textarea").length;i++)
            {
                layedit.sync(ticketEditor[i]);
            }

			layedit.sync(layuiEditor);

			layedit.sync(layuiEditorEn);

			layedit.sync(layuiEditorJp);

			layedit.sync(fastEditor);

			layedit.sync(tempEditor);

			layedit.sync(followEditor);

			var formObj = $(this).parents('form');

			var action = formObj.attr('action');

            if(formObj.attr('id') === 'visitor-form')
            {
                var verify = visitorSlideVerify.slideFinishState;

                if(false === verify)
                {
                    feelDeskAlert(language.SLIDE_VERIFY);

                    layer.close(loading);

                    return false;
                }
            }

			var jump = $(this).data('jump');

			if(jump)
			{
				action += '/jump/'+jump;
			}

            var dataNo = $(this).data('no');

			$.post(action,formObj.serialize(),function(data)
			{
				if(data.status === 0 || data.status === 1)
				{
                    feelDeskAlert(data.msg);
				}
				else
				{
                    if(data.type === 'jump')
                    {
                        location.href = data.url;
                    }
                    else
                    {
                        if(data.html && dataNo)
                        {
                            window.parent.$("tr[data-no='"+dataNo+"']").html(data.html);

                            window.parent.layui.form.render();
                        }

                        feelDeskAlert(data.msg,data);
                    }
				}

                layer.close(loading);

            },'JSON');
		});
	});
});

var feeldesk =
{
    imgReader:function(item,layEditBody)
    {
        var blob = item.getAsFile(), reader = new FileReader(), imageSrc;

        reader.onload = function( e )
        {
            imageSrc = e.target.result;

            layer.msg('正在上传截图',{time:1000,end:function() {
                var imageData = {
                    imageContent:imageSrc,
                    imageName:blob.name,
                    imageSize:blob.size,
                    imageType:blob.type
                };

                $.post("/"+moduleName+'/Upload/uploadImageFile?type=editor&name=paste',{imageData:imageData},function(result)
                {
                    if(result.code === 0)
                    {
                        feeldesk.focusInsert(result.data.src,layEditBody);
                    }
                    else
                    {
                        layer.msg(result.msg);
                    }
                },'json');

            }});
        };

        reader.readAsDataURL( blob );
    },
    focusInsert : function(src,layEditBody)
    {
        var img = "<img src='"+src+"' width='80%' alt=''>";

        layEditBody.append(img);
    }
};

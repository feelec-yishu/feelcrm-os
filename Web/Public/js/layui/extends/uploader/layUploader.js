layui.extend({
    //你的webuploader.js路径
    webuploader: 'uploader/uploader/webuploader'
}).define(['layer','laytpl','table','element','webuploader'],function(exports)
{
    var $ = layui.$
        ,webUploader= layui.webuploader
        ,element = layui.element
        ,layer=layui.layer
        ,table=layui.table
        ,rowData=[]//保存上传文件属性集合,添加table用
        ,fileSize=1024*1024*1024//默认上传文件大小
        ,fileType='zip,rar,7z,txt,doc,docx,xls,xlsx,pdf,ppt,pptx,gif,jpg,jpeg,bmp,xmind,png,mp4,avi,mov'
        ,upload;
    //加载样式
    layui.link('/Public/js/layui/extends/uploader/uploader/webuploader.css');

    var Class = function (options) {
        var that = this;
        that.options=options;
        that.getStorage(options.visitorToken);//获取存储信息
        that.register();
        that.init();
        that.events();
    };

    Class.prototype.init = function()
    {
        var that = this,
            options=that.options;

        if(!that.strIsNull(options.size))
        {
            fileSize = options.size
        }

        if(!that.strIsNull(that.options.fileType))
        {
            fileType=that.options.fileType;
        }

        layer.open(
        {
            type: 1,
            title:language.UPLOAD_ATTACHMENT,
            area: ['80%', '500px'], //宽高
            resize:false,
            skin:'uploader-content',
            content:
            '<div class="extend-header">' +
            '<div  id="extend-upload-chooseFile">'+language.SELECT_FILE+'</div>'+
            '<button id="extent-button-uploader" class="layui-btn">'+language.START_UPLOAD+'</button>' +
            '</div>'+
            '<table style="margin-top:-10px;" class="layui-table" id="extend-uploader-form" lay-filter="extend-uploader-form">' +
                '  <thead>' +
            '    <tr>' +
            '      <th lay-data="{type:\'numbers\', fixed:\'left\'}">'+language.NUMBER+'</th>' +
            '      <th lay-data="{field:\'fileName\'}">'+language.NAME+'</th>' +
            '      <th lay-data="{field:\'fileSize\'}">'+language.SIZE+'</th>' +
            //'      <th lay-data="{field:\'validateMd5\', width:120}">文件验证</th>' +
            '      <th lay-data="{field:\'progress\',templet:\'#button-form-optProcess\'}">'+language.PROGRESS+'</th>' +
            '      <th lay-data="{field:\'oper\',templet: \'#button-form-uploadTalbe\'}">'+language.OPERATE+'</th>' +
            '    </tr>' +
            '  </thead>'+
            '</table>'+
            '<script type="text/html" id="button-form-uploadTalbe">'+
                '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">'+language.DELETE+'</a>'+
            '</script>'+
            '<script type="text/html" id="button-form-optProcess">' +
                '<div style="margin-top: 5px;" class="layui-progress layui-progress-big" lay-filter="{{d.fileId}}"  lay-showPercent="true">'+
                  '<div class="layui-progress-bar layui-bg-blue" lay-percent="0%"></div>'+
                '</div>'+
            '</script>',
            success: function(layero, index)
            {
                table.init('extend-uploader-form',
                {
                    height: 380,
                    unresize:true
                });
                upload = webUploader.create(
                {
                    // 不压缩image
                    resize: false,
                    // swf文件路径
                    swf:  'src/lib/extend/uploader/Uploader.swf',
                    // 默认文件接收服务端。
                    server: options.url,
                    pick: '#extend-upload-chooseFile',
                    chunked: options.storage === '10',//开启分片上传
                    chunkSize:5*1024*1024,
                    threads: 1,//上传并发数
                    fileSingleSizeLimit:fileSize,//单个文件大小
                    //接收文件类型--自行添加options
                    accept:[{
                        title: 'file',
                        extensions: fileType,
                        mimeTypes: that.buildFileType(fileType)
                    }],
                    formData:options.token ? {'token':options.token} : {}//传递参数
                });
            },//可以自行添加按钮关闭,关闭请清空rowData
            end:function ()
            {
                rowData=[];
                if(options.success)
                {
                    if(typeof options.success==='function')
                    {
                        options.success();
                    }
                }
            },
            cancel: function(index)
            {
                layer.close(index);

                if(options.reload == true)
                {
                    location.reload();
                }
            }
        });
    };

    //文件大小换算
    Class.prototype.formatFileSize = function(size)
    {
        var fileSize = 0,length = 0;

        if(size/1024>1024)
        {
            length = size/1024/1024;

            fileSize = length.toFixed(2) +"MB";
        }
        else if(size/1024/1024>1024)
        {
            length = size/1024/1024;

            fileSize = length.toFixed(2)+"GB";
        }
        else
        {
            length = size/1024;

            fileSize = length.toFixed(2)+"KB";
        }

        return fileSize;
    };

    Class.prototype.buildFileType=function (type)
    {
        var ts = type.split(',');

        var ty = '';

        for(var i=0;i<ts.length;i++)
        {
            ty=ty+ "."+ts[i]+",";
        }

        return  ty.substring(0, ty.length - 1)
    };

    Class.prototype.strIsNull=function (str)
    {
        if(typeof str == "undefined" || str == null || str == "")
            return true;
        else
            return false;
    };

    //获取当前的存储类型，20 代表七牛云并返回七牛云相关信息
    Class.prototype.getStorage=function(visitorToken)
    {
        var ajax = this.createAjax();

        ajax.open('POST', '/AjaxRequest/getStorageType', false);

        // ajax.setRequestHeader("If-Modified-Since", "0");

        ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded");

        // 开启跨域带cookie
        ajax.withCredentials = true;

        ajax.send('visitorToken='+visitorToken);

        if (ajax.status === 200)
        {
            var res = JSON.parse(ajax.responseText);

            this.options.storage = res.storage;

            if(res.storage === '10')
            {
                return false;
            }
            else
            {
                this.options.url = res.url;

                this.options.token = res.token;

                this.options.domain = res.domain;
            }
        }
        else
        {
            console.log("get upload token error: ", ajax.responseText);
        }
    };

    Class.prototype.events=function ()
    {
        var that = this;

        //当文件添加进去
        upload.on('fileQueued', function( file )
        {
            var fileSize = that.formatFileSize(file.size);

            var row = {fileId:file.id,fileName:file.name,fileSize:fileSize,validateMd5:'0%',progress:file.id,state:'ok'};

            rowData.push(row);

            that.reloadData(rowData);

            element.render('progress');
        });

        //监听进度条,更新进度条信息
        upload.on( 'uploadProgress', function( file, percentage )
        {
            element.progress(file.id, (percentage * 100).toFixed(0)+'%');
        });

        //错误信息监听
        upload.on('error', function(handler)
        {
            if(handler === 'F_EXCEED_SIZE')
            {
                layer.msg('上传的单个太大!', {icon: 5});
            }
            else if(handler === 'Q_TYPE_DENIED')
            {
                layer.msg(language.FILE_TYPE_ERROR, {icon: 5});
            }
        });

        //移除上传的文件
        table.on('tool(extend-uploader-form)', function(obj)
        {
            var data = obj.data;

            if(obj.event === 'del')
            {
                that.removeArray(rowData,data.fileId);

                upload.removeFile(data.fileId,true);

                obj.del();
            }
        });

        //开始上传
        $("#extent-button-uploader").click(function ()
        {
            that.uploadToServer();

            that.setTableBtn(0,language.UPLOADING);
        });

        //上传前，添加参数，适用于七牛云
        upload.on('uploadBeforeSend', function (obj, data, headers)
        {
            data.key = data.name;
        });

        //单个文件上传成功
        upload.on( 'uploadSuccess', function(file,response)
        {
            that.setTableBtn(file.id,language.COMPLETE);

            //七牛云
            if(that.options.storage === '20')
            {
                response.save_name = response.attach_link = that.options.domain + response.key;

                response.attach_name = response.key;

                response.attach_size = file.size;

                response.attach_type = file.ext;
            }

            if(that.options.source === 'ticket')
            {
                var str = '<li>';

                str += '<i class="iconfont icon-fujian"></i>';

                str += '<span>'+response.attach_name+'</span>';

                str += '<span class="pointer iconfont icon-close2 closed" data-name="'+response.save_name+'" id="closed"></span>';

                str += '<input type="hidden" name="file[saves][]" value="'+response.save_name+'" />';

                str += '<input type="hidden" name="file[names][]" value="'+response.attach_name+'"/>';

                str += '<input type="hidden" name="file[sizes][]" value="'+response.attach_size+'"/>';

                str += '<input type="hidden" name="file[types][]" value="'+response.attach_type+'"/>';

                str += '<input type="hidden" name="file[links][]" value="'+response.attach_link+'" /></li>';

                $('#attachments').append(str);
            }

            if(that.options.source === 'reply')
            {
                var attachItem = "<div>";

                    attachItem += "<i class='iconfont icon-fujian'></i>";

                    attachItem += "<span>"+response.attach_name+"</span>";

                    attachItem += "<i class='iconfont icon-guanbi delete-attach' data-name='"+response.save_name+"'></i>";

                    attachItem += '<input type="hidden" name="file[saves][]" value="'+response.save_name+'" />';

                    attachItem += '<input type="hidden" name="file[names][]" value="'+response.attach_name+'"/>';

                    attachItem += '<input type="hidden" name="file[sizes][]" value="'+response.attach_size+'"/>';

                    attachItem += '<input type="hidden" name="file[types][]" value="'+response.attach_type+'"/>';

                    attachItem += '<input type="hidden" name="file[links][]" value="'+response.attach_link+'" />';

                    attachItem += "</div>";

                $('#'+that.options.replyType+'-attach-item').append(attachItem);

                $(".detail-main").css('padding-bottom',parseInt($(".detail-main").css('padding-bottom'))+31+'px');
            }
        });

        //所有文件上传成功后
        upload.on('uploadFinished',function()
        {
            //成功后
            that.setTableBtn('finished',language.COMPLETE);

            $("#extent-button-uploader").text(language.START_UPLOAD).removeClass('layui-btn-disabled');
        });
    };

    Class.prototype.reloadData=function(data)
    {
        layui.table.reload('extend-uploader-form',
        {
            data : data
        });
    };

    Class.prototype.register=function ()
    {
        var that = this,
            options = that.options;

        if(that.strIsNull(options.md5))
        {
            return;
        }

        // 在文件开始发送前做些异步操作。做md5验证
        // WebUploader会等待此异步操作完成后，开始发送文件。
        webUploader.Uploader.register(
        {
            "before-send-file":"beforeSendFile"
        },
        {
            beforeSendFile: function(file)
            {
                var task = new $.Deferred();
                (new webUploader.Uploader()).md5File(file, 0, 10*1024*1024).progress(function(percentage)
                {
                    var v = that.getTableHead('validateMd5');
                    var table = $("#extend-uploader-form").next().find('div[class="layui-table-body layui-table-main"]').find('table');
                    var pro = table.find('td[data-field="progress"]');
                    for(var i=0;i<pro.length;i++){
                        var d = $(pro[i]).attr('data-content');
                        if(d==file.id ){
                            var t = $(pro[i]).prev();
                            t.empty();
                            t.append('<div class="'+v+'">'+(percentage * 100).toFixed(0)+'%</div>');
                        }
                    }
                }).then(function(val){
                    $.ajax({
                        type: "POST",
                        url: options.md5,
                        data: {
                            type: "md5Check",md5: val //后台接收 String md5
                        },
                        cache: false,
                         timeout: 3000,
                         dataType: "json"
                    }).then(function(data, textStatus, jqXHR){
                        if(data.data==0){   //若存在，这返回失败给WebUploader，表明该文件不需要上传
                            task.reject(); //
                            upload.skipFile(file);
                            that.setTableBtn(file.id,'秒传');
                            element.progress(file.id,'100%');
                        }else{
                            task.resolve();
                        }
                    }, function(jqXHR, textStatus, errorThrown){    //任何形式的验证失败，都触发重新上传
                        task.resolve();
                    });
                });
                return $.when(task);
            }
        });
    };

    Class.prototype.createAjax = function(argument)
    {
        var xmlhttp = {};

        if (window.XMLHttpRequest)
        {
            xmlhttp = new XMLHttpRequest();
        }
        else
        {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        return xmlhttp;
    };

    /*
    * 注意更改了table列的位置,或自行新增了表格,请自行在这修改
    */
    Class.prototype.getTableHead=function (field)
    {
        //获取table头的单元格class,保证动态设置table内容后单元格不变形
        var div = $("#extend-uploader-form").next().find('div[class="layui-table-header"]');
        var div2 = div[0];
        var table = $(div2).find('table');

        var td = table.find('th[data-field="'+field+'"]').find('div').attr('class');

        return td;
    };

    Class.prototype.setTableBtn=function (fileId,val)
    {
        var td = this.getTableHead('oper');

        //获取操作栏,修改其状态
        var table = $("#extend-uploader-form").next().find('div[class="layui-table-body layui-table-main"]').find('table');

        var pro = table.find('td[data-field="progress"]');

        for(var i=0;i<pro.length;i++)
        {
            var d = $(pro[i]).attr('data-content');

            if(d == fileId )
            {
                $(pro[i]).next().empty().append('<div class="'+td+'"><a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="ok">'+val+'</a></div>')
            }

            if(fileId == 0)
            {
                $(pro[i]).next().empty().append('<div class="'+td+'"><a class="layui-btn layui-btn-normal layui-btn-ing layui-btn-xs" lay-event="ok">'+val+'</a></div>')
            }

            if(fileId == 'finished')
            {
                $(pro[i]).next().empty().append('<div class="'+td+'"><a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="ok">'+val+'</a></div>')
            }
        }
    };


    Class.prototype.uploadToServer=function ()
    {
        if(rowData.length<=0)
        {
            layer.msg(language.NO_UPLOADED_FILES, {icon: 5});
            return;
        }

        $("#extent-button-uploader").text(language.UPLOADING_FILE).addClass('layui-btn-disabled');

        upload.upload();
    };

    Class.prototype.removeArray=function (array,fileId)
    {
        for(var i=0;i<array.length;i++)
        {
            if(array[i].fileId==fileId)
            {
                array.splice(i,1);
            }
        }

        return array;
    };

    var layUploader =
    {
        render:function (options)
        {
            var inst = new Class(options);

            return inst;
        }
    };

    exports('layUploader', layUploader);
});

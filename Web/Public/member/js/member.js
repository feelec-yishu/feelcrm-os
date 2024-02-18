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

var list,detail;

$(function()
{
    layui.use(['form','element','laydate'],function()
    {
        var element = layui.element;

        var form = layui.form;

        var laydate = layui.laydate;

        var fliter_option = {
            elem:'#fliter-time',
			lang:language.LANG,
            type:'datetime',
            range: '~',
            format: 'yyyy-MM-dd HH:mm',
            max: $('#fliter-time').data('max'),
            trigger: 'click',
            btns: ['clear', 'confirm']
        };

        laydate.render(fliter_option);

        var option2 =
        {
            elem:'#summary',
			lang:language.LANG,
            type:'month',
            done: function(value)
            {
                var summaryForm = $("#summaryForm");

                summaryForm.find("input[type='hidden']").val(value);

                summaryForm.submit();

                var financeForm = $("#financeForm");

                financeForm.find("input[type='hidden']").val(value);
            }
        };

        laydate.render(option2);

        //筛选 - 展示
        $('#filter').on('click',function()
        {
            $(this).addClass('active');

            $('#filter-panel').slideDown('fast');
        });

        //筛选 - 自定义时间
        form.on('radio(create-time)',function(data)
        {
            if(data.value === 'n')
            {
                $('#fliter-time').show();
            }
            else
            {
                $('#fliter-time').hide().val('');
            }
        });

        //筛选 - 取消
        $('.filter-cancel').on('click',function()
        {
            $('#filter-panel').slideUp('fast');

            $('#filter').removeClass('active');
        });

        //筛选 - 提交
        $('.filter-sure').on('click',function()
        {
            $('#filter-form').submit();
        });

//       列表 - 全选
        var ListCheckboxLength = 0;

        var checkbox = $("#list td,#message-list .message-item").find('input[type="checkbox"]');

//       列表 - 初始化 - 默认已选中的长度
        $.each(checkbox,function (index,item)
        {
            if(item.checked)
            {
                ListCheckboxLength++;
            }
        });

//       列表 - 初始化 - 全选框状态
        if(checkbox.length === ListCheckboxLength && checkbox.length !== 0)
        {
            $("input[lay-filter='ListAllChoose']").prop('checked',true);

            form.render('checkbox');
        }

        form.on('checkbox(ListAllChoose)', function(data)
        {
            var batch = $('#batchDisable,#batchRecover');

            checkbox.each(function(index, item)
            {
                item.checked = data.elem.checked;
            });

            //消息
            if($("a[action='markRead']").length > 0)
            {
                if(data.elem.checked)
                {
                    $("a[action='markRead'],a[action='deleteAll']").removeClass('msg-disabled');
                }
                else
                {
                    $("a[action='markRead'],a[action='deleteAll']").addClass('msg-disabled');
                }
            }

            if(batch.length > 0)
            {
                if(data.elem.checked)
                {
                    batch.removeClass('disabled');
                }
                else
                {
                    ListCheckboxLength = 0;

                    batch.addClass('disabled');
                }
            }

            if(!data.elem.checked)
            {
                ListCheckboxLength = 0;
            }

            form.render('checkbox');
        });

//        列表 - 单选控制全选及批量删除按钮
        form.on('checkbox(ListChoose)',function(data)
        {
            var isChecked = false;

            if(data.elem.checked)
            {
                ListCheckboxLength++;
            }
            else
            {
                ListCheckboxLength--;

                if(ListCheckboxLength < 0) ListCheckboxLength = 0;
            }

            if(ListCheckboxLength === checkbox.length)
            {
                $("input[lay-filter='ListAllChoose']").prop('checked',true);
            }
            else
            {
                $("input[lay-filter='ListAllChoose']").prop('checked',false);
            }

            form.render();

            checkbox.each(function(index, item)
            {
                if(item.checked) isChecked = true;

                if($(item).data('init') == 1)
                {
                    if(!item.checked)
                    {
                        $(item).next("input[type='checkbox']").prop('checked',true);
                    }
                    else
                    {
                        $(item).next("input[type='checkbox']").prop('checked',false);
                    }
                }
            });

            //消息
            if($("a[action='markRead']").length > 0)
            {
                if(data.elem.checked)
                {
                    $("a[action='markRead'],a[action='deleteAll']").removeClass('msg-disabled');
                }
                else
                {
                    $("a[action='markRead'],a[action='deleteAll']").addClass('msg-disabled');
                }
            }

            var batch = $('#batchDisable,#batchRecover');

            if(batch.length > 0)
            {
                if(isChecked)
                {
                    batch.removeClass('disabled');
                }
                else
                {
                    batch.addClass('disabled');
                }
            }
        });

//        批量恢复
        $("#batchRecover").unbind('click').on('click',function()
        {
            if(!$(this).hasClass('disabled'))
            {
                var action = $(this).attr('action');

                var remindContent = language.SURE_RECOVER;

                layer.confirm(remindContent,{offset:['15vw']},function(index)
                {
                    layer.close(index);

                    var loading = layer.load(2,{offset:['15vw']});

                    $.post(action,$("#itemForm").serialize(),function(data)
                    {
                        if(data.status === 2)
                        {
                            data.isReload = 1;

                            feelDeskAlert(data.msg,data);
                        }
                        else
                        {
                            feelDeskAlert(data.msg);
                        }

                        layer.close(loading);

                    },'JSON')
                });
            }
        });

//       批量禁用
        $("#batchDisable").unbind('click').on('click',function ()
        {
            if(!$(this).hasClass('disabled'))
            {
                var action = $(this).attr('action');

                var remindContent = language.SURE_DISABLE;

                layer.confirm(remindContent,{offset:['15vw']},function(index)
                {
                    $.post(action, $("#itemForm").serialize(), function (data)
                    {
                        if(data.status === 2)
                        {
                            data.isReload = 1;

                            feelDeskAlert(data.msg,data);
                        }
                        else
                        {
                            feelDeskAlert(data.msg);
                        }

                    }, 'JSON');
                })
            }
        });

        $('#window-submit').on('click',function()
        {
            layer.load(2,{offset:'15vw'});

            var form = $(this).parents('form');

            $.post(form.attr('action'),form.serialize(),function (data)
            {
                if(data.status == 0)
                {
                    feelDeskAlert(data.msg);
                }
                else
                {
                    feelDeskAlert(data.msg,data);
                }
            })
        });
    });

//   列表 -- 工单详情层
    $(document).on("click", "a[mini='ticket-detail']", function (e)
    {
        e.preventDefault();

        var ticket_id = $(this).data('id');

        openTicketDetail(ticket_id);
    });

//    消息 -- 工单详情层
    $(document).on("click", "a[mini='msgTicketDetail']", function (e)
    {
        e.preventDefault();

        var actionAttr =  $(this).attr('action').split('/');

        var ticket_id = actionAttr[actionAttr.length-1];

        $(this).find("i").remove();

        var source = $(this).data('source');

        openTicketDetail(ticket_id,source);
    });

//    列表操作面板
    $(".listOperate").hover(function()
    {
        $(this).parent('tr').siblings().find('td div').slideUp('fast');

        $(this).find('div').stop(true, true).slideDown('500');
    },function ()
    {
        $(this).find('div').stop(true, true).slideUp('fast');
    });

//   搜索 - 提交
    $("#search").keydown(function(e)
    {
        if(e.keyCode == 13)
        {
            $(this).parents('form').submit();
        }
    });
});


// 工单详情层
function openTicketDetail(id,source)
{
    var area = ['100%','100%'];

    var offset = 'l';

    if(source == 'right-window')
    {
        area = ['83%','100%'];

        offset = 'r';
    }

    layer.open(
    {
        type: 2,
        title: false,
        offset: offset,
        area: area,
        content:"/"+moduleName+"/Ticket/detail?id="+id,
        shade: 0,
        skin: 'bounceInRight',
        closeBtn:0,
        scrollbar: true,
        success: function(layero, index)
        {
            var close = layer.getChildFrame('body', index).find('.close-window');

            close.on('click',function()
            {
                layer.close(index);
            });

            if(index)
            {
                layer.close(index-1);
                layer.setTop(layero);
            }
        }
    });
}


//显示全局加载图标
function showRightLoading()
{
    $('.member-right').after("<div class='global-loading'><i class='layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop'></i></div>");
}


// 带表单的弹窗
function openFormWindow(that,url)
{
    event.stopPropagation();

    layer.open(
    {
        type: 2,
        title: $(that).attr('title'),
        area: ['60%','550px'],
        content:url,
        skin: 'form-window',
        closeBtn:1,
        scrollbar: true,
        success: function(layero, index)
        {
            layer.getChildFrame('body', index).find('#cancel-form').on('click',function()
            {
                layer.close(index);
            });
        }
    });
}


// 带搜索框的弹窗
function openSearchWindow(that,url)
{
    event.stopPropagation();

    layer.open(
    {
        type: 2,
        title: false,
        area: ['60%','550px'],
        content:url,
        skin: 'form-window',
        closeBtn:0,
        scrollbar: true,
        success: function(layero, index)
        {
            var frame = layer.getChildFrame('body', index);

            frame.find('#cancel-form,#cancel-window').on('click',function()
            {
                layer.close(index);
            });

            //搜索
            frame.find('#search-keyword').keyup(function ()
            {
                var value = $(this).val();

                if(value)
                {
                    frame.find('tbody tr').hide().filter(":contains('" + (value) + "')").show();
                }
                else
                {
                    frame.find('tbody tr').show();
                }
            });
        }
    });
}


function confirmWindow(that,url)
{
    event.stopPropagation();

    var title = $(that).attr('title');

    layer.confirm(language.SURE+title+'？',{title:language.PROMPT,offset:['15vw']},function(index)
    {
        layer.close(index);

        $.get(url,function(data)
        {
            if(data.status === 0)
            {
                feelDeskAlert(data.msg);
            }
            else
            {
                feelDeskAlert(data.msg,data);
            }

        },'JSON');
    });
}

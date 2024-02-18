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

var list,detail,searchFilter;

var offset = '20vw';

$(function()
{
//    导航菜单显示与隐藏
    $(".personal-menu").hover(function()
    {
        $(this).find('.personal-panel').stop(true,true).slideToggle('fast');
    });

    $(".lang-menu").hover(function()
    {
        $(this).find('.lang-panel').stop(true,true).slideToggle('fast');
    });

    $(".fast-menu").hover(function()
    {
        $(this).find('.fast-panel').stop(true,true).slideToggle('fast');
    });

    $('.ticket-fast').on('click',function()
    {
        $('.feeldesk-left').find('li').removeClass('current');

        $('li.ticket-menu').addClass('current');
    });

    $('.crm-fast').on('click',function()
    {
        $('.feeldesk-left').find('li').removeClass('current');

        $('li.crm-menu').addClass('current');
    });

    $(".feeldesk-left-menu li:not('.menu-logo')").on('click',function()
    {
        var index = $(this).index();

        $('.menu-item').find("li:eq('"+index+"')").addClass('current').siblings('li').removeClass('current');
    });

    $('#menu-fold').on('click',function ()
    {
        var that = $('#menu-fold');

        var icon = $('#icon-menu');

        var text = $('#text-menu');

        var body = $('#menu-body');

        if(!that.hasClass('icon-unfold'))
        {
            that.addClass('icon-unfold');

            icon.addClass('hidden');

            text.removeClass('hidden');

            body.addClass('body-unfold');
        }
        else
        {
            that.removeClass('icon-unfold');

            icon.removeClass('hidden');

            text.addClass('hidden');

            body.removeClass('body-unfold');
        }
    });

    $('input:not([autocomplete]),textarea:not([autocomplete]),select:not([autocomplete])').attr('autocomplete', 'off');

    searchFilter = $('#searchFilter');

    var listContent = $('.list-content');

    if(listContent.length > 0)
    {
        listContent.css({height:'calc(100% - '+$(".list-header")[0].getBoundingClientRect().height+'px)'});
    }

//    工单列表 - 排序
    $(".sort").on('click',function()
    {
        $(this).toggleClass('active').next(".sort-ul").slideToggle('fast');
    });

    $(".sort-ul").find('li').on('click',function()
    {
        var sortInput =  $("input[name='sort']");

        var sort = sortInput.val();

        if(sort === 'desc')
        {
            sortInput.val('asc');
        }
        else
        {
            sortInput.val('desc');
        }

        $("input[name='sort_field']").val($(this).data('value'));

        layer.load(2,{offset:offset});

        searchFilter.submit();
    });

//    处理人筛选
    $(".process").on('click',function()
    {
        $(this).addClass('active');
    });

    $(document).on('click','.notice-link,.notice-prev,.notice-next',function(e)
    {
        e.preventDefault();

        openNoticeDetail($(this));
    });

//    视图切换
    $(".ticket-nav li").on('click',function()
    {
        changeTicketView($(this).attr('v'));

        $(this).addClass('active').siblings().removeClass('active');
    });

    layui.use(['form','element','laydate'],function()
    {
        var form = layui.form,laydate = layui.laydate;

        var option = {
            elem:'#fliter-time',
			lang:language.LANG,
            type:'datetime',
            range: '~',
            format: 'yyyy-MM-dd HH:mm',
            max: getNowFormatDate()+' 23:59',
            trigger: 'click',
            btns: ['clear', 'confirm'],
            done: function(value)
            {
                var datetime = value.split('~');

                /*var diff = DateDiff($.trim(datetime[0]),$.trim(datetime[1]));

                if(diff > 93)
                {
                    feelDeskAlert(language.TIME_PERIOD);

                    return;
                }*/

                if(searchFilter.length > 0 && value)
                {
                    $("input[name='datetime']").val(value);
                }
            }
        };

        laydate.render(option);

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

        var option3 = {
            elem:'#filter-time-range',
			lang:language.LANG,
            type:'date',
            range: '~',
            format: 'yyyy-MM-dd',
            max: getNowFormatDate()+' 23:59:59',
            trigger: 'click',
            btns: ['clear', 'confirm'],
            done: function(value)
            {
                var datetime = value.split('~');

               /* var diff = DateDiff($.trim(datetime[0]),$.trim(datetime[1]));

                if(diff > 31)
                {
                    feelDeskAlert(language.TIME_PERIOD);

                    return;
                }*/

                if(searchFilter.length > 0 && value)
                {
                    $("input[name='datetime']").val(value);
                }
            }
        };

        laydate.render(option3);

        form.on('select(ticketProject)',function()
        {
            layer.load(2,{offset:offset});

            searchFilter.submit();
        });

        //筛选 - 展示
        $('#filter').on('click',function()
        {
            $(this).toggleClass('active');

            $('#filter-panel').slideToggle('fast');
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
            layer.load(2,{offset:offset});

            $('#searchFilter').submit();
        });

        var selected = $('#selected-number');

        /* 极简 - 全选 */
        var MiniCheckboxLength = 0;

        form.on('checkbox(MiniAllChoose)', function(data)
        {
            var checkbox = $("#minimalist").find('input[type="checkbox"]');

            var batchDelete = $('#batchDeleteTicket,#batchFollowTicket,#batchCancelFollowTicket');

            checkbox.each(function(index, item)
            {
                item.checked = data.elem.checked;
            });

            if(data.elem.checked)
            {
                MiniCheckboxLength = checkbox.length - 1;

                selected.html(MiniCheckboxLength);
            }
            else
            {
                selected.html(0);
            }

            if(batchDelete.length > 0)
            {
                if(data.elem.checked)
                {
                    batchDelete.removeClass('disabled');
                }
                else
                {
                    MiniCheckboxLength = 0;

                    batchDelete.addClass('disabled');
                }
            }

            form.render('checkbox');
        });

//        极简 - 单选控制全选及批量删除按钮
        form.on('checkbox(MiniListChoose)',function(data)
        {
            var checkbox = $("#minimalist").find('input[type="checkbox"]');

            var isChecked = false;

            if(data.elem.checked)
            {
                MiniCheckboxLength++;
            }
            else
            {
                MiniCheckboxLength--;

                if(MiniCheckboxLength < 0) MiniCheckboxLength = 0;
            }

            if(MiniCheckboxLength === checkbox.length - 1)
            {
                $("input[lay-filter='MiniAllChoose']").prop('checked',true);
            }
            else
            {
                $("input[lay-filter='MiniAllChoose']").prop('checked',false);
            }

            form.render();

            checkbox.each(function(index, item)
            {
                if(item.checked) isChecked = true;
            });

            var batchDelete = $('#batchDelete,#batchDeleteTicket,#batchRecoverTicket,#batchFollowTicket');

            if(batchDelete.length > 0)
            {
                if(isChecked)
                {
                    batchDelete.removeClass('disabled');
                }
                else
                {
                    batchDelete.addClass('disabled');
                }
            }

            selected.html(MiniCheckboxLength);
        });

        /* 列表 - 全选 */
        var ListCheckboxLength = 0;

        form.on('checkbox(ListAllChoose)', function(data)
        {
            var checkbox = $("#list td,#messageList td,#problemList .problem-item").find('input[type="checkbox"]');

            var batchDelete = $('#batchDelete,#batchDeleteTicket,#batchRecover,#batchFollowTicket,#batchCancelFollowTicket');

            checkbox.each(function(index, item)
            {
                item.checked = data.elem.checked;
            });

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

            if(data.elem.checked)
            {
                ListCheckboxLength = checkbox.length;

                selected.html(checkbox.length);
            }
            else
            {
                selected.html(0);
            }

            if(batchDelete.length > 0)
            {
                if(data.elem.checked)
                {
                    batchDelete.removeClass('disabled');
                }
                else
                {
                    ListCheckboxLength = 0;

                    batchDelete.addClass('disabled');
                }
            }

            form.render('checkbox');
        });

//        列表 - 单选控制全选及批量删除按钮
        form.on('checkbox(ListChoose)',function(data)
        {
            var checkbox = $("#list td,#messageList td,#problemList .problem-item").find('input[type="checkbox"]');

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

            selected.html(ListCheckboxLength);

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
            });

            var batchDelete = $('#batchDelete,#batchDeleteTicket,#batchRecover,#batchFollowTicket,#batchCancelFollowTicket');

            if(batchDelete.length > 0)
            {
                if(isChecked)
                {
                    batchDelete.removeClass('disabled');
                }
                else
                {
                    batchDelete.addClass('disabled');
                }
            }
        });

//        已完成的工单控制
        form.on('checkbox(showEndTicket)',function(data)
        {
            var type = $(data.elem).data('type');

            var isChecked = data.elem.checked ? 'show' : 'hide' ;

            var loading = layer.load(2,{offset:offset});

            /**
            * @param data {Object}
            * @param data.errmsg {String}
            */
            $.post('/AjaxRequest/updateShowEndTicket',{'is_show':isChecked,'type':type},function (data)
            {
                if(data.errcode === 0)
                {
                    searchFilter.submit();
                }
                else
                {
                    feelDeskAlert(data.errmsg);
                }

                layer.close(loading);
            })
        });

//        批量关注工单
        $("#batchFollowTicket").unbind('click').on('click',function()
        {
            if(!$(this).hasClass('disabled'))
            {
                var action = $(this).attr('action');

                var ids = [];

                var checkBox = $(".ticket-list,.list-item").find('input[type="checkbox"]');

                checkBox.each(function(index, item)
                {
                    if(item.checked && $(item).val() !== 'on') ids.push(item.value);
                });

                if(ids.length > 0)
                {
                    layer.confirm(language.SURE_FOLLOW_TICKET,{icon: 3, offset:['15vw']},function(index)
                    {
                        layer.close(index);

                        var loading = layer.load(2,{offset:[offset]});

                        $.post(action,{ticket_ids:ids,request:'follow'},function(data)
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
                else
                {
                    feelDeskAlert(language.SELECT_FOLLOW_TICKET);
                }
            }
        });

//        批量取消关注工单
        $("#batchCancelFollowTicket").unbind('click').on('click',function()
        {
            if(!$(this).hasClass('disabled'))
            {
                var action = $(this).attr('action');

                var ids = [];

                var checkBox = $(".ticket-list,.list-item").find('input[type="checkbox"]');

                checkBox.each(function(index, item)
                {
                    if(item.checked && $(item).val() !== 'on') ids.push(item.value);
                });

                if(ids.length > 0)
                {
                    layer.confirm(language.SURE_UNFOLLOW_TICKET,{icon: 3, offset:['15vw']},function(index)
                    {
                        layer.close(index);

                        var loading = layer.load(2,{offset:[offset]});

                        $.post(action,{ticket_ids:ids,request:'cancel_follow'},function(data)
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
                else
                {
                    feelDeskAlert(language.SELECT_UNFOLLOW_TICKET);
                }
            }
        });

//        批量删除工单
        $("#batchDeleteTicket").unbind('click').on('click',function()
        {
            if(!$(this).hasClass('disabled'))
            {
                var action = $(this).attr('action');

                var ids = [];

                var checkBox = $(".ticket-list,.list-item").find('input[type="checkbox"]');

                checkBox.each(function(index, item)
                {
                    if(item.checked && $(item).val() !== 'on') ids.push(item.value);
                });

                if(ids.length > 0)
                {
                    layer.confirm(language.SURE_DELETE_TICKET,{icon: 3, offset:['15vw']},function(index)
                    {
                        layer.close(index);

                        var loading = layer.load(2,{offset:[offset]});

                        $.post(action,{ticket_ids:ids},function(data)
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
                else
                {
                    feelDeskAlert(language.SELECT_DELETE_TICKET);
                }
            }
        });

//        批量恢复工单
        $("#batchRecover").unbind('click').on('click',function()
        {
            if(!$(this).hasClass('disabled'))
            {
                var ids = [];

                var action = $(this).attr('action');

                var checkBox = $(".ticket-list,.list-item,.item-list").find('input[type="checkbox"]');

                checkBox.each(function(index, item)
                {
                    if(item.checked && $(item).val() !== 'on') ids.push(item.value);
                });

                var remindContent,param;

                if(action === '/Ticket/recover' || action === '/SubTicket/recover')
                {
                    remindContent = language.SURE_RECOVER_TICKET;

                    param = {ticket_ids:ids};
                }

                if(action === '/Customer/recovery')
                {
                    remindContent = language.SURE_RECOVER_CUSTOMER;

                    param = {batch:ids};
                }

                if(ids.length > 0)
                {
                    layer.confirm(remindContent,{icon: 3, offset:offset},function(index)
                    {
                        layer.close(index);

                        var loading = layer.load(2,{offset:offset});

                        $.post(action,param,function(data)
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
                else
                {
                    feelDeskAlert(language.SELECT_RECOVER_TICKET);
                }
            }
        });

//       批量刪除
        $("#batchDelete").unbind('click').on('click',function ()
        {
            if(!$(this).hasClass('disabled'))
            {
                var action = $(this).data('value');

                var remindContent = language.SURE_DELETE;

                if(action === '/Member/delete')
                {
                    remindContent = $(this).data('remind');
                }

                layer.confirm(remindContent,{icon: 3, offset:['15vw']},function()
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

//       用户列表筛选，部门与角色
        form.on('select(member-groups)',function() {$("#memberForm").submit();});

        form.on('select(member-roles)',function() {$("#memberForm").submit();});

//       CRM工单客户名称
        form.on('select(crm_customer)',function(data)
        {
            var value = data.value;

            var crm_customer_name = $("option[value='"+value+"']").text();

            $('#crm_customer_name').val(crm_customer_name);
        });

//       文章列表筛选 —— 分类
        form.on('select(article-category)',function() {$("#articleForm").submit();});

//       项目列表筛选 —— 负责人
        form.on('select(project)',function()
        {
            layer.load();

            searchFilter.submit();
        });
    });

//    极简 -- 工单详情层
    $(document).on("click", "div[mini='ticket']", function (e)
    {
        e.preventDefault();

        var ticket_id = $(this).data('id');

        $('.ticket-list').removeClass('current');

        $(this).addClass('current').find('.icon-new').remove();

        openTicketDetail(ticket_id,['69%','100%']);
    });

//    极简 -- 子工单详情层
    $(document).on("click", "div[mini='subTicket']", function (e)
    {
        e.preventDefault();

        var ticket_id = $(this).data('id');

        $("div[mini='subTicket']").each(function()
        {
            $(this).parents('.ticket-list').css('background','none');
        });

        $(this).parents('.ticket-list').css('background-color','#fafafa');

        openTicketDetail(ticket_id,['69%','100%'],'/subTicket/detail?id='+ticket_id);
    });

    $(document).on('click',"div[lay-skin='primary']",function(e)
    {
        e.stopPropagation();
    });

//   列表 -- 子工单详情层
    $(document).on("click", "td[mini='subTicket']", function (e)
    {
        e.preventDefault();

        var ticket_id = $(this).parent('tr').data('id');

        $('tr').removeClass('current');

        $(this).parent('tr').addClass('current').find('.icon-new').remove();

        openTicketDetail(ticket_id,['69%','100%'],'/subTicket/detail?id='+ticket_id);
    });

//   列表 -- 工单详情层
    $(document).on("click", "td[mini='ticket']", function (e)
    {
        e.preventDefault();

        var that = $(this).parent('tr');

        var ticket_id = that.data('id');

        //当前登录用户是否为工单处理人
        if(that.data('v1') === that.data('v2'))
        {
            that.find('.ticket-unread').addClass('ticket-read').removeClass('ticket-unread');
        }

        $('tr').removeClass('current');

        that.addClass('current').find('.icon-new').remove();

        openTicketDetail(ticket_id,['75%','100%']);
    });

//    首页 -- 工单详情层
    $(document).on("click", "a[mini='ticketDetail']", function (e)
    {
        e.preventDefault();

        var ticket_id = $(this).data('id');

        var area = ['69%','100%'];

        if($(this).data('type') === 'associate')
        {
            area = ['100%','100%'];
        }

        openTicketDetail(ticket_id,area);
    });

//    首页 -- 子工单详情层
    $(document).on("click", "a[mini='subTicket']", function (e)
    {
        e.preventDefault();

        var ticket_id = $(this).data('id');

        openTicketDetail(ticket_id,['69%','100%'],'/subTicket/detail?id='+ticket_id);
    });

//    消息 -- 工单详情层
    $(document).on("click", "a[mini='msgTicketDetail']", function()
    {
        //e.preventDefault();

        var action =  $(this).attr('action');

        var actionAttr = action.split('/');

        var ticket_id = actionAttr[actionAttr.length-1];

        $(this).find("i").remove();

        openTicketDetail(ticket_id,['69%','100%'],action);
    });

//    指定成员 -- 工单列表
    $('.view-ticket').on('click',function()
    {
        var url = $(this).data('url');

        layer.open(
        {
            type: 2,
            title: language.TICKET_LIST,
            offset: ['50px'],
            area: ['98%','90%'],
            content:url,
            skin:"ticket-view",
            isOutAnim : false,
            closeBtn:1,
            scrollbar: false,
            success: function(layero, index)
            {
                var sidebar = layer.getChildFrame('body', index).find('.ticket-view-close');

                sidebar.on('click',function()
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
    });

//    客户信息 -- 弹层
    $(document).on("click", "a[mini='userInfo'],a[mini='echatRecord']", function (e)
    {
        e.preventDefault();

        layer.open(
        {
            type: 2,
            title: false,
            offset: 'rt',
            area: ['100%','100%'],
            content:$(this).attr('href'),
            shade: 0,
            skin: 'bounceInRight1',
            closeBtn:0,
            scrollbar: false,
            success: function(layero, index)
            {
                var id_layer_min = layero.attr("id");

                var ids =id_layer_min.split("r");

                var idtabindex= "#layui-layer"+"-iframe"+ids[1];

                var ifr = document.querySelector(idtabindex);

                ifr.contentWindow.postMessage({a: idtabindex}, '*');

                window.addEventListener('message', function(e)
                {
                    if(e.data)
                    {
                        layer.close(index);
                    }

                }, false);
            }
        })
    });

//    修改工单 -- 弹层
    $(document).on("click", "a[mini='editTicket']", function (e)
    {
        e.preventDefault();

        layer.open(
        {
            type: 2,
            title: false,
            offset: 'rt',
            area: ['100%','100%'],
            content:$(this).attr('href'),
            shade: 0,
            skin: 'bounceInRight1',
            closeBtn:0,
            scrollbar: false,
            success: function(layero, index)
            {
                var sidebar = layer.getChildFrame('body', index).find('.sidebar,#window-feeldesk-form-cancel');

                sidebar.on('click',function()
                {
                    layer.close(index);
                });

                if(index)
                {
                    layer.close(index-1);
                    layer.setTop(layero);
                }
            }
        })
    });

//    文章详情 -- 弹层
    $(document).on("click", "tr[mini='articleDetail']", function (e)
    {
        e.stopPropagation();

        var article_id = $(this).data('value');

        if($(e.target).parents('td.listOperate').length === 0)
        {
            layer.open(
                {
                    type: 2,
                    title: false,
                    offset: 'r',
                    area: ['63%','100%'],
                    content:"/Article/detail.html?id="+article_id,
                    shade: 0,
                    skin: 'bounceInRight',
                    closeBtn:0,
                    scrollbar: true,
                    success: function(layero, index)
                    {
                        var sidebar = layer.getChildFrame('body', index).find('.sidebar');

                        sidebar.on('click',function()
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
    });

//    公告详情弹层
    $(document).on("click", "a[mini='noticeDetail']", function (e)
    {
        e.preventDefault();

        var addActionUrl =  $(this).attr('href');

        var tit = $(this).data('tit');

        $("a[mini='noticeDetail']").parents('ul').each(function()
        {
            $(this).find('li').css('background-color','#FFF');
        });

        $(this).parents('li').css('background-color','#FFF8DC');

        layer.open({
            type: 2,
            title: tit,
            offset: ['0px','54%'],
            area: ['46%','100%'],
            content:addActionUrl,
            shade: 0,
            skin: 'bounceInRight',
            closeBtn:2,
            scrollbar: false,
            success: function(layero, index){if(index){layer.close(index-1);layer.setTop(layero);}}
        });
    });

//   系统消息页crm弹层
    $(document).on("click", "a[mini='msgCustomerDetail'],a[mini='msgOrderDetail'],a[mini='msgContractDetail'],a[mini='msgCrmDetail']", function (e)
    {
        e.stopPropagation();

        layer.open(
        {
            type: 2,
            title: false,
            offset: 'r',
            area: ['69%','100%'],
            content:$(this).attr('action'),
            shade: 0,
            skin: 'bounceInRight',
            closeBtn:0,
            scrollbar: true,
            success: function(layero, index)
            {
                var id_layer_min = layero.attr("id");

                var ids =id_layer_min.split("r");

                var idtabindex= "#layui-layer"+"-iframe"+ids[1];

                var ifr = document.querySelector(idtabindex);

                ifr.contentWindow.postMessage({a: idtabindex}, '*');

                window.addEventListener('message', function(e)
                {
                    if(e.data)
                    {
                        layer.close(index);
                    }

                }, false);
            }
        });
    });




//    列表操作面板
    $(".feeldesk-list-operate").hover(function()
    {
        $(this).parent('tr').siblings().find('td div.operate-panel').slideUp('fast');

        $(this).find('div.operate-panel').stop(true, true).slideDown('500');
    },
    function ()
    {
        $(this).find('div').stop(true, true).slideUp('fast');
    });

//    消息列表操作面板
    $(".messageOperate").hover(function()
    {
        $(this).parent('tr').siblings().find("td div[mini='operate']").slideUp('fast');

        $(this).find('div').stop(true, true).slideDown('500');
    },function ()
    {
        $(this).find('div').stop(true, true).slideUp('fast');
    });

    //转为客户
    $("#switchToCustomer").on('click',function()
    {
        var ids = [];

        var postUrl = $(this).attr('data-href');

        var operationName = $(this).attr('data-name');

        var checkBox = $(".item-list").find('input[type="checkbox"]');

        checkBox.each(function(index, item)
        {
            if(item.checked && item.value !== 'on') ids.push(item.value);
        });

        if(ids.length > 0)
        {
            layer.confirm(language.SURE+operationName+'?',{icon: 3, offset:['15vw']},function()
            {
                var loading = layer.load(2,{offset:offset});

                $.post(postUrl,{member_ids:ids},function(data)
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
        else
        {
            feelDeskAlert(language.NO_DATA);
        }
    });
});

function openDetailCommon(action)
{
    layer.open(
    {
        type: 2,
        title: false,
        offset: 'r',
        area: ['69%','100%'],
        content:action,
        shade: 0,
        skin: 'bounceInRight',
        closeBtn:0,
        scrollbar: true,
        success: function(layero, index)
        {
            var id_layer_min = layero.attr("id");

            var ids =id_layer_min.split("r");

            var idtabindex= "#layui-layer"+"-iframe"+ids[1];

            var ifr = document.querySelector(idtabindex);

            ifr.contentWindow.postMessage({a: idtabindex}, '*');

            window.addEventListener('message', function(e)
            {
                if(e.data)
                {
                    layer.close(index);
                }

            }, false);
        }
    });
}

function getNowFormatDate()
{
    var date = new Date();

    var slide = "-";

    var year = date.getFullYear();

    var month = date.getMonth() + 1;

    var strDate = date.getDate();

    if (month >= 1 && month <= 9)
    {
        month = "0" + month;
    }

    if (strDate >= 0 && strDate <= 9)
    {
        strDate = "0" + strDate;
    }

    return year + slide + month + slide + strDate;
}

/**
* 获取年月日时分
*/
function getMinFormatDate()
{
    var date = new Date();

    var slide = "-";

    var year = date.getFullYear();

    var month = date.getMonth();

    var strDate = date.getDate();

    if (month >= 1 && month <= 9)
    {
        month = "0" + month;
    }

    if (strDate >= 0 && strDate <= 9)
    {
        strDate = "0" + strDate;
    }

    return year + slide + month + slide + strDate;
}


function openNoticeDetail(id)
{
    layer.open(
        {
            type: 2,
            title: false,
            offset: 'r',
            area: ['70%','100%'],
            content:"/notice/detail?id="+id,
            shade: 0,
            skin: 'bounceInRight',
            closeBtn:0,
            scrollbar: true,
            success: function(layero, index)
            {
                var sidebar = layer.getChildFrame('body', index).find('.sidebar');

                sidebar.on('click',function()
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

// 工单详情层
function openTicketDetail(id,area,url)
{
    /*
        $(".ticket-main-left",parent.document).animate({'left':'-300px'},700);

        $(".ticket-main-right",parent.document).animate({'left':'0','width':'100%'},700);
    */
    if(!url)
    {
        url = "/Ticket/detail.html?id="+id;
    }

    layer.open(
{
        type: 2,
        title: '工单详情',
        offset: 'r',
        area: area,
        content:url,
        shade:0,
        // shade: [0.01, '#fff'],
        // shadeClose:true,
        skin: 'bounceInRight',
        closeBtn:1,
        // move:false,
        scrollbar: true,
        success: function(layero, index)
        {
            var sidebar = layer.getChildFrame('body', index).find('.sidebar');

            sidebar.on('click',function()
            {
                layer.close(index);

                $('.ticket-list,tr.list-item-tr').removeClass('current');

                setTimeout(function() {},300);
            });

            if(index)
            {
                layer.close(index-1);
                layer.close(index-2);
                layer.setTop(layero);
            }

            // clickOtherCloseOwn('layui-layer'+index,index);
        }
    });
}

function clickOtherCloseOwn(id,index)
{
    $(document).on('click',function(e)
    {
        var e = e || window.event; //浏览器兼容性

        var elem = e.target || e.srcElement;

        while (elem)
        {
            //循环判断至跟节点，防止点击的是div子元素
            if (elem.id && elem.id === id)
            {
                return;
            }

            elem = elem.parentNode;
        }

        if(index) layer.close(index);
    });
}

function changeTicketView(v)
{
    $("#"+v).show();

    if(v === 'minimalist')
    {
        $("#list").hide();
    }

    if(v === 'list')
    {
        $("#minimalist").hide();
    }

    $("#viewSource").val(v);

    $.post('/AjaxRequest/updateViewSource',{'view_source':v},function (data)
    {
        if(data.errcode === 1)
        {
            window.location.href = data.url;
        }
    });
}


//sDate1和sDate2是yyyy-MM-dd格式
function DateDiff(sDate1, sDate2)
{
     var aDate, oDate1, oDate2, iDays;

    aDate1 = sDate1.split("-");

    oDate1 = new Date(aDate1[0] + '-' + aDate1[1].split(' ')[0] + '-' + aDate1[2]);  //转换为yyyy-MM-dd格式

    aDate2 = sDate2.split("-");

    oDate2 = new Date(aDate2[0] + '-' + aDate2[1].split(' ')[0] + '-' + aDate2[2]);

    iDays = parseInt(Math.abs(oDate1 - oDate2) / (1000 * 60 * 60 * 24)); //把相差的毫秒数转换为天数

    return iDays;  //返回相差天数
}


function copy(msg)
{
    var clipboard = new Clipboard('.copyBtn,.copy-btn');

    clipboard.on('success', function(e)
    {
        layer.msg(msg,{time:1000,offset:['15vw']});

        e.clearSelection();

        clipboard.destroy();
    });

    clipboard.on('error', function(e)
    {
        console.error('Action:', e.action);

        console.error('Trigger:', e.trigger);
    });
}


function importExcel(source)
{
    layui.use('upload', function()
    {
        var upload = layui.upload;

        var fileLoad;

        upload.render(
        {
            elem:'#uploadFile',

            url: "/Upload/importExcel?source="+source,

            field:'excel',

            exts: 'xlsx|xls',

            accept:'file',

            before:function()
            {
                fileLoad = layer.msg(language.IMPORT_LOADING,{time:1000000,shift:0,offset:['15vw']})
            },
            done: function(data)
            {
                layer.close(fileLoad);

                if(data.error === 1)
                {
                    feelDeskAlert(data.msg,data);
                }
                else
                {
                    feelDeskAlert(data.msg);
                }
            }
        })
    });
}


/*
* 获取工单修改记录
* @param ticket_id {String}
* @param modify_id {String}
*/
function getTicketModifyRecord(ticket_id,modify_id)
{
    var loading = layer.load(2);

    $.post('/Ticket/detail?id='+ticket_id+'&request=modify_record',{modify_id:modify_id},function(data)
    {
        var record = "<div class='modify-record'>";

        if(data.length > 0)
        {
            $.each(data,function(k,v)
            {
                record += "<div class='modify-item'>" +
                    "<div class='modify-item-tab'><span class='middle'>"+v.user_name+" "+language.MODIFY_TICKET+"</span> <i class='iconfont icon-downs'></i><span class='fr'>"+v.modify_time+"</span></div>" +
                    "<div class='modify-content'>";
                $.each(v.modify_data,function(k1,v1)
                {
                    record += "<div class='content-item'>" +
                        "<div class='content-tab'>"+language.MODIFY+"：<span class='blue1'>"+v1.field_name+"</span></div>"+
                        "<div class='content-data'>" +
                        "<div class='modify-before'><div>"+language.MODIFY_BEFORE+"</div><div>"+v1.modify_before+"</div></div>" +
                        "<div class='modify-after'><div>"+language.MODIFY_AFTER+"</div><div>"+v1.modify_after+"</div></div>" +
                        "</div></div>";
                });

                record += "</div></div>";
            });
        }
        else
        {
            record += "<div class='no-data'>"+language.NO_DATA+"</div>"
        }

        record += "</div>";

        layer.open(
    {
            type:1,
            title:language.MODIFY_RECORD,
            offset:['10vw'],
            area:['85%','500px'],
            shade:false,
            skin:'modify-window',
            content: record,
            success:function()
            {
                layer.close(loading);

                $('.modify-item-tab').unbind('click').on('click',function()
                {
                    $(this).next('.modify-content').slideToggle('fast');

                    $(this).find('i').toggleClass('icon-up');
                });
            }
        });

    },'JSON')
}


function selectProvince(j,url)
{
    var value = $("#country"+j).val();

    $.post(url,{country_id:value,type:'province'},function(data)
    {
        if(data.code === 0)
        {
            var option = '<option value="">'+language.SELECT_PROVINCE+'</option>';

            if(data.data.length > 0)
            {
                $.each(data.data,function(k,v)
                {
                    option += "<option c-value='"+ v.country_code+"' value='"+v.code+"'>"+v.name+"</option>";
                });

                $('#province'+j).html(option).parents().slideDown('fast');
            }
            else
            {
                $('#province'+j).html(option).parents().slideUp('fast');
            }

            $("#province"+j).select2();
        }
    });

    $('#city'+j).html('<option value="">'+language.SELECT_CITY+'</option>').parents('.region-item').slideUp('fast');

    $('#area'+j).html('<option value="">'+language.SELECT_REGION+'</option>').parents('.region-item').slideUp('fast');
}


function selectCity(j,url)
{
    var value = $("#province"+j).val();

    var cValue = $("#country"+j).val();

    $.post(url,{country_id:cValue,province_id:value,type:'city'},function(data)
    {
        if(data.code === 0)
        {
            var option = '<option value="">'+language.SELECT_CITY+'</option>';

            if(data.data.length > 0)
            {
                $.each(data.data,function(k,v)
                {
                    option += "<option c-value='"+ v.country_code+"' p-value='"+ v.province_code+"' value='"+v.code+"'>"+v.name+"</option>";
                });

                $('#city'+j).html(option).parents().slideDown('fast');
            }
            else
            {
                $('#city'+j).html(option).parents().slideUp('fast');
            }

            $("#city"+j).select2();
        }
    });

    $('#area'+j).html('<option value="">'+language.SELECT_REGION+'</option>').parents('.region-item').slideUp('fast');
}


function selectArea(j,url)
{
    var value = $("#city"+j).val();

    var pValue = $("#province"+j).val();

    var cValue = $("#country"+j).val();

    $.post(url,{country_id:cValue,province_id:pValue,city_id:value,type:'area'},function(data)
    {
        if(data.code === 0)
        {
            var option = '<option value="">'+language.SELECT_REGION+'</option>';

            if(data.data.length > 0)
            {
                $.each(data.data,function(k,v)
                {
                    option += "<option value='"+v.code+"'>"+v.name+"</option>";
                });

                $('#area'+j).html(option).parents().slideDown('fast');
            }
            else
            {
                $('#area'+j).html(option).parents().slideUp('fast');
            }

            $("#area"+j).select2();
        }
    });
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

function OnclickCall(phone)
{
    var loading = layer.load(2);

    $.ajax({
        url:'/AjaxRequest/OnclickCall',
        type:'POST',
        async: false,
        data:{'phone':phone},
        datatype:'json',
        success:function(data)
        {
            layer.close(loading);
            if(data.code == 0)
            {
                console.log(data.msg);
            }else
            {
                layer.msg(data.msg,{icon:2,time:1500,offset:['200px']});
            }
        },
        error:function()
        {
            //layer.msg('保存排序异常');
        }
    });
}

//修改分机状态
function setExtenStatus()
{
    var status = $('#busyoridle').data('status');

    var type = 0;

    if(status == 'idle')
    {
        type = 1;
    }

    var loading = layer.load(2);

    $.ajax({
        url:'/AjaxRequest/setExtenStatus',
        type:'POST',
        async: false,
        data:{'type':type},
        datatype:'json',
        success:function(data)
        {
            if(data.code == 0)
            {
                layer.close(loading);
                if (type == 0) {
                    $('#busyoridle').data('status','idle');
                    $('#busyoridle').find('i').attr('class','iconfont icon-yilianjie-');
                    layer.msg('示闲成功',{icon:1,time:1500});
                } else {
                    $('#busyoridle').data('status','busy');
                    $('#busyoridle').find('i').attr('class','iconfont icon-shimang-');
                    layer.msg('示忙成功',{icon:1,time:1500});
                }
            }
            else
            {
                layer.close(loading);
                layer.msg(data.msg,{icon:2,time:1500});
            }
        },
        error:function()
        {
            layer.close(loading);
            layer.msg('操作失败');
        }
    });
}

function setHold()
{
    var status = $('#keeptalking').data('status');
    var loading = layer.load(2);

    if(status == 'T_ico22') //保持通话
    {
        $.ajax({
            url:'/AjaxRequest/keepCalling',
            type:'POST',
            async: false,
            datatype:'json',
            success:function(data)
            {
                if(data.code == 0)
                {
                    layer.close(loading);
                    $('#keeptalking').data('status','hold');
                    $('#keeptalking').attr('title','恢复通话');
                    sessionStorage.setItem("keepHold", data.data.hold);
                    layer.msg('保持成功!',{icon:1,time:1500});
                }
                else
                {
                    layer.close(loading);
                    layer.msg(data.msg,{icon:2,time:1500});
                }
            },
            error:function()
            {
                layer.close(loading);
                layer.msg('操作失败');
            }
        });
    }
    else  //恢复通话
    {
        var hold =  sessionStorage.getItem("keepHold");
        $.ajax({
            url:'/AjaxRequest/restoreCalling',
            type:'POST',
            async: false,
            data:{'hold':hold},
            datatype:'json',
            success:function(data)
            {
                if(data.code == 0)
                {
                    layer.close(loading);
                    $('#keeptalking').data('status','T_ico22');
                    $('#keeptalking').attr('title','保持通话');
                    sessionStorage.removeItem("keepHold");
                    layer.msg('恢复成功!',{icon:1,time:1500});
                }
                else
                {
                    layer.close(loading);
                    layer.msg(data.msg,{icon:2,time:1500});
                }
            },
            error:function()
            {
                layer.close(loading);
                layer.msg('操作失败');
            }
        });
    }
}

//通话质检
function callQuality()
{
    var loading = layer.load(2);

    $.ajax({
        url:'/AjaxRequest/callQuality',
        type:'POST',
        async: false,
        datatype:'json',
        success:function(data)
        {
            if(data.code == 0)
            {
                layer.close(loading);
                layer.msg('质检成功!',{icon:1,time:1500});
            }
            else
            {
                layer.close(loading);
                layer.msg(data.msg,{icon:2,time:1500});
            }
        },
        error:function()
        {
            layer.close(loading);
            layer.msg('操作失败');
        }
    });
}

//通话转移
function callTransfer()
{
    layer.prompt({
        formType: 2,
        value: '',
        title: '请输入转移号码',
        area: ['300px', '35px'] //自定义文本域宽高
    }, function (value, index, elem)
    {
        if (value) {
            var reg1 = new RegExp("^1[3,4,5,7,8,9]{1}[0-9]{9}$");
            var reg2 = new RegExp("^01[3,4,5,7,8,9]{1}[0-9]{9}$");
            if (!reg1.test(value) && !reg2.test(value))
            {
                layer.msg('请输入有效的转移号码！', {time: 1500, icon: 2});
                return false;
            }
            else
            {
                var loading = layer.load(2);

                $.ajax({
                    url:'/AjaxRequest/callTransfer',
                    type:'POST',
                    async: false,
                    data:{'phone':value},
                    datatype:'json',
                    success:function(data)
                    {
                        if(data.code == 0)
                        {
                            layer.close(loading);
                            layer.msg('转移成功!',{icon:1,time:1500});
                        }
                        else
                        {
                            layer.close(loading);
                            layer.msg(data.msg,{icon:2,time:1500});
                        }
                        layer.close(index);
                    },
                    error:function()
                    {
                        layer.close(loading);
                        layer.msg('操作失败');
                    }
                });
            }
        }
        else
        {
            layer.close(index);
            layer.msg('转移号码不能为空!', {time: 1500, icon: 2});
        }
    });
}

//三方通话
function callConference()
{
    layer.prompt({
        formType: 2,
        value: '',
        title: '请输入有效电话号码',
        area: ['300px', '35px'] //自定义文本域宽高
    }, function (value, index, elem)
    {
        if (value)
        {
            var reg = new RegExp("^[0-9]*$");
            if (!reg.test(value))
            {
                layer.msg('电话号码只能是数字！', {time: 1500, icon: 2});
                return false;
            }
            else
            {
                var loading = layer.load(2);

                $.ajax({
                    url:'/AjaxRequest/callConference',
                    type:'POST',
                    async: false,
                    data:{'exten':value},
                    datatype:'json',
                    success:function(data)
                    {
                        if(data.code == 0)
                        {
                            layer.close(loading);
                            layer.msg('呼叫成功!',{icon:1,time:1500});
                        }
                        else
                        {
                            layer.close(loading);
                            layer.msg(data.msg,{icon:2,time:1500});
                        }
                        layer.close(index);
                    },
                    error:function()
                    {
                        layer.close(loading);
                        layer.msg('操作失败');
                    }
                });
            }
        }
        else
        {
            layer.close(index);
            layer.msg('有效电话号码不能为空!', {time: 1500, icon: 2});

        }
    });
}

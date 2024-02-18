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

var list,detail,searchFilter,nowUrl;

var wHeight = $(window).height();

wHeight = wHeight - 180 + 'px';

$(function()
{
	$('#advanced-search-btn').click(function(){

		var searchIndex = layer.open({
		  type: 1,
		  title: false,
		  scrollbar:false,
		  area: ['80%',wHeight],
		  closeBtn:0,
		  content: $('#advanced-search')
		});

	})

    nowUrl = window.location.href;

    searchFilter = $('#searchFilter');

//    筛选按钮
    $(".filter").on('click',function()
    {
        var itemFilter = $(".item-filter");

        if(itemFilter.is(':hidden'))
        {
            itemFilter.slideDown('fast');

            $(this).addClass('active');
        }
        else
        {
            itemFilter.slideUp('fast');

            $(this).removeClass('active');
        }
    });

//    排序
    $(".sort").on('click',function()
    {
        $(this).toggleClass('active').next(".sort-ul").slideToggle('fast');
    });

    $(".sort-ul").find('li').on('click',function()
    {
        var sortInput =  $("input[name='sort']");

        var sort = sortInput.val();

        if(sort == 'desc')
        {
            sortInput.val('asc');
        }
        else
        {
            sortInput.val('desc');
        }

        $("input[name='sort_field']").val($(this).data('value'));

        searchFilter.submit();
    });

//    筛选
    $(".item-filter .item").unbind('click').on('click',function()
    {
        var item = $(this);

        if(!item.hasClass('active'))
        {
            item.find('.filter-menu').slideDown('fast');

            item.siblings().find('.filter-menu').slideUp('fast');

            item.addClass('active').siblings().removeClass('active');
        }
        else
        {
            item.removeClass('active').find('.filter-menu').slideUp('fast');
        }
    });

//      自定义时间
    $(".custom").on("click", function (e)
    {
        e.stopPropagation();
    });

//    筛选 -- 状态
    $(".status-item li").on('click',function()
    {
        var value = $(this).data('value');

        $("input[name='status']").val(value);

        searchFilter.submit();
    });

//    筛选
    $(".filter-menu .menu-item").on('click',function()
    {
        if($(this).hasClass('channel-item'))
        {
            var check = $(this).find("input[type='checkbox']");

            if(check.is(':checked'))
            {
                check.prop('checked',false);
            }
            else
            {
                check.prop('checked',true);
            }
        }
        else
        {
            var value = $(this).data('value');

            $(this).parent().find(".filter-input").val(value);
        }

        if($(this).hasClass('create_time'))
        {
            $("#datetimeValue,#datetime").val('');
        }

        if(!$(this).hasClass('custom')) searchFilter.submit();
    });

	//详情页切换修改表单
	/*$('.detail-update-form').click(function()
	{
		$('.detail-update-form').find('.layui-detail-edit-default').removeClass('hidden');

		$('.detail-update-form').find('.layui-detail-edit-form').addClass('hidden');

		var editForm = $(this).find('.layui-detail-edit-form');

		editForm.removeClass('hidden');

		if(!editForm.find('input').hasClass('layui-form-time'))
		{
			var value = editForm.find('input').val();

			editForm.find('input[type="text"],input[type="number"]').val('').focus().val(value);
		}

		$(this).find('.layui-detail-edit-default').addClass('hidden');
	})	*/

    layui.use(['form','element','laydate'],function()
    {
        var element = layui.element;

        var form = layui.form;

        var laydate = layui.laydate;

        var option = {
            elem:'#datetime',
            type:'datetime',
            range: '~',
            format: 'yyyy-MM-dd HH:mm',
            trigger: 'click',
            btns: ['clear', 'confirm'],
            done: function(value)
            {
                if(searchFilter.length > 0 && value)
                {
                    $("input[name='datetime']").val(value);

                    searchFilter.submit();
                }
            }
        };

        laydate.render(option);

        var option2 =
        {
            elem:'#summary',
            type:'month',
			trigger: 'click',
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
            elem:'#teamDate',
            type:'date',
            range: '~',
            format: 'yyyy-MM-dd',
            trigger: 'click',
            btns: ['clear', 'confirm'],
            done: function(value, date, endDate)
            {
                var datetime = value.split('~');

                var diff = DateDiff($.trim(datetime[0]),$.trim(datetime[1]));

                if(diff > 31)
                {
                    layer.msg(language.TIME_PERIOD,{icon:2,time:1500,shift:0,offset:['150px']});

                    return;
                }

                if(searchFilter.length > 0 && value)
                {
                    $("input[name='datetime']").val(value);

                    searchFilter.submit();
                }
            }
        };

        laydate.render(option3);

		for(var i = 1;i <= $(".layui-form-time").length;i++)
		{
            laydate.render({
				elem: '#form_time'+i,
				trigger: 'click',
				type: 'datetime'
			});
		}

        laydate.render({
            elem: '#form_contacttime',
            trigger: 'click',
            type: 'datetime',
            done: function (value,date)
            {
                var hours = date.hours;
                var minutes = date.minutes;
                var seconds = date.seconds;
                if (hours == "0" && minutes == "0" && seconds == "0")
                {
                    $(".layui-laydate-footer [lay-type='datetime'].laydate-btns-time").click();
                    // 如果是datetime的范围选择，改变开始时间默认值
                    $(".laydate-main-list-0 .layui-laydate-content li:first-child ol li:nth-child(10)").click();

                    $(".layui-laydate-footer [lay-type='date'].laydate-btns-time").click();
                }
            }
        });

        /* 全选 */
        form.on('checkbox(MiniAllChoose)', function(data)
        {
            var checkbox = $("#minimalist").find('input[type="checkbox"]');

            checkbox.each(function(index, item)
            {
                item.checked = data.elem.checked;
            });

            form.render('checkbox');
        });

        form.on('checkbox(ListAllChoose)', function(data)
        {
            var checkbox = $("#list td,#messageList td").find('input[type="checkbox"]');

            checkbox.each(function(index, item)
            {
                item.checked = data.elem.checked;
            });

            form.render('checkbox');
        });

        //线索操作
        $("#drawOperate").on('click',function()
        {
            var ids = [];

            var postUrl = $(this).attr('data-href');

            var operationName = $(this).attr('data-name');

            var data_id = $(this).attr('data-id');

            if(data_id)
            {
                ids.push(data_id);
            }
            else
            {
                var checkBox = $(".item-list").find('input[type="checkbox"]');

                checkBox.each(function(index, item)
                {
                    if(item.checked && item.value!='on') ids.push(item.value);
                });
            }

            if(ids.length > 0)
            {
                layer.confirm(language.SURE+operationName+'?',{icon: 3, offset:['100px']},function()
                {
                    var loading = layer.load(2,{offset:['150px']});

                    $.post(postUrl,{ids:ids},function(data)
                    {
                        layer.close(loading);

                        if(data.status == 2)
                        {
                            layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
                            {
                                window.location.reload();
                            });
                        }
                        else
                        {
                            layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
                        }

                    },'JSON')
                });
            }
            else
            {
                layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
            }
        });

		//客户操作
		$("#delCustomer,#drawCustomer,#examineCustomer").on('click',function()
        {
            var ids = [];

			var postUrl = $(this).attr('data-href');

			var operationName = $(this).attr('data-name');

			var customer_id = $(this).attr('data-id');

			if(customer_id)
			{
				ids.push(customer_id);
			}
			else
			{
				var checkBox = $(".item-list").find('input[type="checkbox"]');

				checkBox.each(function(index, item)
				{
					if(item.checked && item.value!='on') ids.push(item.value);
				});
			}

            if(ids.length > 0)
            {
                layer.confirm(language.SURE+operationName+'?',{icon: 3, offset:['100px']},function()
                {
                    var loading = layer.load(2,{offset:['150px']});

                    $.post(postUrl,{customer_ids:ids},function(data)
                    {
                        layer.close(loading);

                        if(data.status == 2)
                        {
                            layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
                            {
                                window.location.reload();
                            });
                        }
                        else
                        {
                            layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
                        }

                    },'JSON')
                });
            }
            else
            {
                layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
            }
        });

		//放弃客户
		$('#toPoolCustomer').click(function(){

			var ids = [];

			var customer_id = $(this).attr('data-id');

			if(customer_id)
			{
				ids.push(customer_id);
			}
			else
			{
				var checkBox = $(".item-list").find('input[type="checkbox"]');

				checkBox.each(function(index, item)
				{
					if(item.checked && item.value!='on') ids.push(item.value);
				});
			}

			if(ids.length > 0)
			{
				var index = layer.open({
				  type: 1,
				  title: false,
				  scrollbar:false,
				  area: ['60%','400px'],
				  closeBtn:0,
				  content: $('#abandonCust')
				});
			}
			else
			{
				layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
			}

		});

		//客户失单
		$('#loseCustomer').click(function(){

			var ids = [];

			var customer_id = $(this).attr('data-id');

			if(customer_id)
			{
				ids.push(customer_id);
			}
			else
			{
				var checkBox = $(".item-list").find('input[type="checkbox"]');

				checkBox.each(function(index, item)
				{
					if(item.checked && item.value!='on') ids.push(item.value);
				});
			}

			if(ids.length > 0)
			{
				var index = layer.open({
				  type: 1,
				  title: false,
				  scrollbar:false,
				  area: ['60%','400px'],
				  closeBtn:0,
				  content: $('#LoseCust')
				});
			}
			else
			{
				layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
			}

		});

		//window.parent.location.href = data.href;
		//生成工单账号
		$("#createFeeldesk").on('click',function()
        {
            var ids = [];

			var postUrl = $(this).attr('data-href');

			var operationName = $(this).attr('data-name');

            var checkBox = $(".item-list").find('input[type="checkbox"]');

            checkBox.each(function(index, item)
            {
                if(item.checked && item.value!='on') ids.push(item.value);
            });

            if(ids.length > 0)
            {
                layer.confirm(language.SURE+operationName+'?',{icon: 3, offset:['100px']},function()
                {
                    var loading = layer.load(2,{offset:['150px']});

                    $.post(postUrl,{customer_ids:ids,surepwd:'123456'},function(data)
                    {
                        layer.close(loading);

                        if(data.status == 2)
                        {
                            layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
                            {
                                window.location.reload();
                            });
                        }
                        else
                        {
                            layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
                        }

                    },'JSON')
                });
            }
            else
            {
                layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
            }
        });

		$('.chooseMember').click(function(){

			var wHeight = $(window).height();

			wHeight = wHeight - 180 + 'px';

			var ids = [];

			var customer_id = $(this).attr('data-id');

			if(customer_id)
			{
				ids.push(customer_id);
			}
			else
			{
				var checkBox = $(".item-list").find('input[type="checkbox"]');

				checkBox.each(function(index, item)
				{
					if(item.checked && item.value!='on') ids.push(item.value);
				});
			}

			if(ids.length > 0)
			{
				var index = layer.open({
				  type: 1,
				  title: false,
				  scrollbar:false,
				  area: ['80%',wHeight],
				  closeBtn:0,
				  content: $('#Memberlist')
				});
			}
			else
			{
				layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
			}

		});

		$('.chooseMemberDetail').click(function(){

			var wHeight = $(window).height();

			wHeight = wHeight - 180 + 'px';

			var ids = [];

			var postUrl = $(this).attr('data-href');

			var operationName = $(this).attr('data-name');

			var data_id = $(this).attr('data-id');

			if(data_id)
			{
                ids.push(data_id);
            }
			else
            {
                var checkBox = $(".item-list").find('input[type="checkbox"]');

                checkBox.each(function(index, item)
                {
                    if(item.checked && item.value!='on') ids.push(item.value);
                });
            }

			if(ids.length > 0)
			{
				$('#memberDetailLay').attr('href',"javascript:chooseMember('"+postUrl+"','"+operationName+"','"+data_id+"')");

				var index = layer.open({
				  type: 1,
				  title: false,
				  scrollbar:false,
				  area: ['80%',wHeight],
				  closeBtn:0,
				  content: $('#Memberlist')
				});
			}
			else
			{
				layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
			}

		})

        $(".edit-customer-detail").click(function ()
        {
            $(this).parent().find(".layui-detail-edit-default").hide();

            $(this).parent().find(".layui-detail-edit-form").show();

            $(this).hide();

            $(this).parent().find(".finish-customer-detail").show();
        });

		//订单操作

		$("#delOrder").on('click',function()
        {
            var ids = [];

			var postUrl = $(this).attr('data-href');

			var operationName = $(this).attr('data-name');

            var checkBox = $(".item-list").find('input[type="checkbox"]');

            checkBox.each(function(index, item)
            {
                if(item.checked && item.value!='on') ids.push(item.value);
            });

            if(ids.length > 0)
            {
                layer.confirm(language.SURE+operationName+'?',{icon: 3, offset:['100px']},function()
                {
                    var loading = layer.load(2,{offset:['150px']});

                    $.post(postUrl,{order_ids:ids},function(data)
                    {
                        layer.close(loading);

                        if(data.status == 2)
                        {
                            layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
                            {
                                window.location.reload();
                            });
                        }
                        else
                        {
                            layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
                        }

                    },'JSON')
                });
            }
            else
            {
                layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
            }
        });

		//合同操作

		$("#delContract").on('click',function()
        {
            var ids = [];

			var postUrl = $(this).attr('data-href');

			var operationName = $(this).attr('data-name');

            var checkBox = $(".item-list").find('input[type="checkbox"]');

            checkBox.each(function(index, item)
            {
                if(item.checked && item.value!='on') ids.push(item.value);
            });

            if(ids.length > 0)
            {
                layer.confirm(language.SURE+operationName+'?',{icon: 3, offset:['100px']},function()
                {
                    var loading = layer.load(2,{offset:['150px']});

                    $.post(postUrl,{contract_ids:ids},function(data)
                    {
                        layer.close(loading);

                        if(data.status == 2)
                        {
                            layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
                            {
                                window.location.reload();
                            });
                        }
                        else
                        {
                            layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
                        }

                    },'JSON')
                });
            }
            else
            {
                layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
            }
        });

		//crm 批量操作
		$("#CrmListDelete,.CrmBatchOperate").on('click',function()
        {
            var ids = [];

			var postUrl = $(this).attr('data-href');

			var operationName = $(this).attr('data-name');

            var checkBox = $(".item-list").find('input[type="checkbox"]');

            checkBox.each(function(index, item)
            {
                if(item.checked && item.value!='on') ids.push(item.value);
            });

            if(ids.length > 0)
            {
                layer.confirm(language.SURE+operationName+'?',{icon: 3, offset:['100px']},function()
                {
                    var loading = layer.load(2,{offset:['150px']});

                    $.post(postUrl,{ids:ids},function(data)
                    {
                        layer.close(loading);

                        if(data.status == 2)
                        {
                            layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
                            {
                                window.location.reload();
                            });
                        }
                        else
                        {
                            layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
                        }

                    },'JSON')
                });
            }
            else
            {
                layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
            }
        });


		//客户回收周期选择
		form.on('radio(custRecover)', function(data)
		{
			if(data.value == 1){$('.RecoverCondition').show()}else{$('.RecoverCondition').hide()};
		});

		//是否启用OCR
		form.on('radio(ocrEnable)', function(data)
		{
			if(data.value == 1){$('.ocrAppcodeInfo').show()}else{$('.ocrAppcodeInfo').hide()};
		});

		if(typeof areaJson!="undefined"){

			for(var i = 1;i <= $(".regionCountry").length;i++)
			{
				loadCountry(form,i);
			}
		}

//       用户列表筛选，部门与角色
        form.on('select(member-groups)',function() {$("#memberForm").submit();});

        form.on('select(member-roles)',function() {$("#memberForm").submit();});

//       文章列表筛选 —— 分类
        form.on('select(article-category)',function() {$("#articleForm").submit();});

//		获取沟通类型的自定义回复列表
		form.on('select(getCmncateReply)',function(data) {

			var cmncate_id = data.value;

			 $.ajax({
					url:"/"+moduleName+'/AjaxRequest/getCmncateReply',
					type:'POST',
					async: false,
					data:{'cmncate_id':cmncate_id},
					datatype:'json',
					success:function(data)
					{
						$('#cmncate_reply').html(data.html);

						form.render('select');

						$('#cmncate_reply').parent().removeClass('hidden');
					},
					error:function()
					{
					   //layer.msg('保存排序异常');
					}
			 });

		});

		form.on('select(ReplyContent)',function(data) {

			var reply = data.elem;

			var value=data.value;

            if(value)
            {
                $('#follow_content').val($(reply).find("option[value="+value+"]").html());
            }
            else
            {
                $('#follow_content').val('');
            }
		});

//		获取客户所属联系人
		form.on('select(getContacter)',function(data) {

			var customer_id = data.value;

			 $.ajax({
					url:"/"+moduleName+'/AjaxRequest/getContacter',
					type:'POST',
					async: false,
					data:{'customer_id':customer_id},
					datatype:'json',
					success:function(data)
					{
						$('#ContacterList').html(data.html);

						form.render('select');

						$('#ContacterList').parent().removeClass('hidden');
					},
					error:function()
					{
					   //layer.msg('保存排序异常');
					}
			 });

		});

    });

    layui.use(['layedit'],function() {

        var layedit = layui.layedit;

        var crmEditor = [];

        for(var i = 1;i <= $(".ticket-textarea").length;i++) {
            /* 编辑器 */
            crmEditor[i] = layedit.build('crmEditor'+i, {
                uploadImage: {url:"/"+moduleName+"/Upload/uploadImageFile?type=editor", type: 'post'},
                hideTool: ['face', '|', 'underline', 'del', 'italic'],
                height: 200
            }); //建立编辑器
        }

        $(".finish-customer-detail").on('click', function () {

            var loading = layer.load(2, {offset: '15vw'});

            for (var i = 1; i <= $(".ticket-textarea").length; i++) {
                layedit.sync(crmEditor[i]);
            }

            var formObj = $(this).parent('form');

            var action = formObj.attr('action');

            var dataNo = $(this).data('no');

            $.post(action, formObj.serialize(), function (data)
            {
                if (data.status == 0 || data.status == 1)
                {
                    feelDeskAlert(data.msg);
                }
                else
                {
                    if(data.html && dataNo)
                    {
                        window.parent.$("tr[data-no='"+dataNo+"']").html(data.html);

                        window.parent.layui.form.render();
                    }

                    feelDeskAlert(data.msg, data);
                }
                layer.close(loading);

            }, 'JSON');
        });
    })

//    动态 -- 客户详情层
    $(document).on("click", "a[mini='customer']", function (e)
    {
        e.preventDefault();

        var customer_id = $(this).data('id');

		var customer_type = $(this).data('type');

        openCustomerDetail(customer_id,customer_type);
    });

//	动态 -- 订单详情层
	$(document).on("click", "a[mini='order']", function (e)
	{
		e.preventDefault();

		var order_id = $(this).data('id');

		var order_type = $(this).data('type');

		openOrderDetail(order_id,order_type);
	});

//	动态 -- 合同详情层
	$(document).on("click", "a[mini='contract']", function (e)
	{
		e.preventDefault();

		var contract_id = $(this).data('id');

		var contract_type = $(this).data('type');

		openContractDetail(contract_id,contract_type);
	});


//   列表 -- 客户详情层
	$(document).on("click", "td[mini='customer']", function (e)
	{
		e.preventDefault();

		var customer_id = $(this).parent('tr').data('id');

		$(this).parent('tr').addClass('bgfa');

		openCustomerDetail(customer_id);
	});

	$(document).on("click", "td[mini='elseCustomer']", function (e)
	{
		e.preventDefault();

		var customer_id = $(this).parent('tr').data('customer');

		$(this).parent('tr').addClass('bgfa');

		openCustomerDetail(customer_id);
	});

	$(document).on("click", "li[mini='customer']", function (e)
	{
		e.preventDefault();

		var customer_id = $(this).parent('ul').data('id');

		openCustomerDetail(customer_id);
	});

//   客户 -- 工单详情层
	$(document).on("click", "td[mini='ticket']", function (e)
	{
		e.preventDefault();

		var ticket_id = $(this).parent('tr').data('id');

		var postUrl = $(this).parent().parent('tbody').data('href');

		$(this).parent('tr').addClass('bgfa');

		openTicketDetail(ticket_id,postUrl);
	});

//	列表 -- 订单详情层

	$(document).on("click", "td[mini='order']", function (e)
	{
		e.preventDefault();

		var order_id = $(this).parent('tr').data('id');

		var order_type = $(this).parent('tr').data('type');

		$(this).parent('tr').addClass('bgfa');

		openOrderDetail(order_id,order_type);
	});

//	列表 -- 合同详情层

	$(document).on("click", "td[mini='contract']", function (e)
	{
		e.preventDefault();

		var contract_id = $(this).parent('tr').data('id');

		var contract_type = $(this).parent('tr').data('type');

		$(this).parent('tr').addClass('bgfa');

		openContractDetail(contract_id,contract_type);
	});

	$(document).on("click", "td[mini='elseContract']", function (e)
	{
		e.preventDefault();

		var contract_id = $(this).parent('tr').data('contract');

		var contract_type = $(this).data('type');

		$(this).parent('tr').addClass('bgfa');

		openContractDetail(contract_id,contract_type);
	});

//    列表操作面板
    $(".listOperate").hover(function()
    {
        $(this).parent('tr').siblings().find('td div').slideUp('fast');

        $(this).find('div').stop(true, true).slideDown('500');
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

    var tipsBox;

    $(".intro-icon").hover(function()
    {
        var content = $(this).data('note');

        tipsBox = layer.tips(content,$(this), {tips:2, time:1000000, skin:'form-tips'});

    },function ()
    {
        layer.close(tipsBox);
    });

	$(document).on("click", "a[load='add'],a[load='edt']", function (e)
	{
		e.preventDefault();

		var action =  $(this).attr('href');

		layer.open({type: 2,title: false,scrollbar:false,offset: '60px',area: ['80%',wHeight],closeBtn:0,content: action});
	});

	$(document).on("click", "a[load='trash']", function (e)
	{
		e.preventDefault();

		var action = $(this).attr('href');

		layer.confirm(language.SURE_DELETE,{icon: 3, title:language.PROMPT,offset:['100px']},function()
		{
			$.get(action,function(ret)
			{
				if(ret.status == 0)
				{
					layer.msg(ret.msg,{icon:2,time:3000,offset:['100px']});
				}
				else
				{
					layer.msg(ret.msg,{icon:1,time:1500,shift:0,offset:['100px']},function()
					{
						if(ret.status == 3)
						{
							if(ret.reloadType == 'parent')
							{
								window.parent.location.reload();
							}
							else
							{
								window.parent.location.href = ret.url;
							}
						}
						else
						{
							window.location.href = ret.url;
						}
					});
				}

			},'JSON');

			layer.close();
		});
	});

    $(document).on("click", "a[load='batch']", function (e)
    {
        e.preventDefault();

        var action = $(this).attr('href');

        layer.confirm(language.SURE_RESTORE,{icon: 3, title:language.PROMPT,offset:['100px']},function()
        {
            $.get(action,function(ret)
            {
                if(ret.status == 0)
                {
                    layer.msg(ret.msg,{icon:2,time:3000,offset:['100px']});
                }
                else
                {
                    layer.msg(ret.msg,{icon:1,time:1500,shift:0,offset:['100px']},function()
                    {
                        if(ret.status == 3)
                        {
                            if(ret.reloadType == 'parent')
                            {
                                window.parent.location.reload();
                            }
                            else
                            {
                                window.parent.location.href = ret.url;
                            }
                        }
                        else
                        {
                            window.location.href = ret.url;
                        }
                    });
                }

            },'JSON');

            layer.close();
        });
    });

	$(document).on("click", "a[load='createFeeldesk']", function (e)
	{
		e.preventDefault();

		var title=$(this).html().replace(/<\/?.+?>/g,"").replace(/ /g,"");

		var tit;

		tit = language.SURE+title+"？";

		var action = $(this).attr('href');

		layer.confirm(tit,{icon: 3, title:language.PROMPT,offset:['100px']},function()
		{
			$.post(action,{surepwd:'123456'},function(ret)
			{
				if(ret.status == 0)
				{
					layer.msg(ret.msg,{icon:2,time:3000,offset:['100px']});
				}
				else
				{
					layer.msg(ret.msg,{icon:1,time:1500,shift:0,offset:['100px']},function()
					{
						window.location.href = ret.url;
					});
				}

			},'JSON');

			layer.close();
		});
	});

	//导出数据--start
	$("#startExport").on('click',function()
	{
		layer.open(
		{
			type: 1,
			title: language.SELECT_DATA_EXPORT,
			scrollbar:false,
			area: ['60%','400px'],
			content: $('#export-content')
		});
	});

	$(".export-content ul a").on('click',function()
	{
		var obj = $(this);

		if(obj.hasClass('select-this'))
		{
			obj.removeClass('select-this').next('input').prop('checked',false);
		}
		else
		{
			obj.addClass('select-this').next('input').prop('checked',true);
		}
	});

	$('.export-type a').unbind('click').on('click',function()
	{
		var obj = $(this);

		$('.export-type a').removeClass('select-this');

		$('.export-type input').prop('checked',false);

		obj.addClass('select-this').next('input').prop('checked',true);

	})

	$('#toExport').on('click',function()
	{
		var exporttype = $('input[name="exporttype"]:checked').val();

		var startpage = parseInt($('input[name="startpage"]').val());

		var endpage = parseInt($('input[name="endpage"]').val());

		if(exporttype === 'pagedata')
		{
			if(!startpage)
			{
				layer.msg(language.ENTER_EXPORT_START_PAGE,{icon:2,time:1000,offset:'100px'});

				return false;
			}
			else if(!endpage)
			{
				layer.msg(language.ENTER_EXPORT_END_PAGE,{icon:2,time:1000,offset:'100px'});

				return false;
			}

			if(startpage >= endpage)
			{
				layer.msg(language.END_PAGE_MUST_GT_START,{icon:2,time:1000,offset:'100px'});

				return false;
			}
		}

		var loading = layer.load(2);

		$.get("/"+moduleName+export_url+"?action="+export_action,$("#exportForm").serialize(),function(obj)
		{
			layer.close(loading);

			if(obj.msg === 'success')
			{
				layer.msg(language.EXPORT_SUCCESSFULLY,{icon:1,time:1000,shift:0,offset:'100px'},function()
				{
					window.location.href = "/"+moduleName+export_url;
				});
			}
			else
			{
				layer.msg(obj.msg,{icon:2,time:1000,offset:'100px'});
			}
		});

	})
	//导出数据--end

    var listHeight = $('.feelcrm-list').height();

    var filternum = $('.list-filter').length;

    if(filternum > 0)
    {
        var filterHeight = 45 * filternum;

        var itemListHight = $('.item-list').height();

        var itemListH = itemListHight - filterHeight + 20;

        $('.item-list').css('height',itemListH+'px');

        var listH = listHeight - filterHeight + 20;

        if($('.list-header').is(':hidden'))
        {
            listH = listH + 60;
        }

        $('.feelcrm-list').css('height',listH+'px');

        $('#selectCustomerCon .feelcrm-list').css('height',listH - 165 +'px');
    }

    $('.select-fiter').find('.list-filter-option').on('click',function ()
    {
        var type = $(this).parent().data('type');

        var value = $(this).data('value');

        $("input[name='"+type+"']").val(value);

        $('#filterForm').submit();
    })

    $('.select-fiter .list-filter-checkbox span').on('click',function ()
    {
        var type = $(this).parent().parent().data('type');

        //var value = $(this).parent().data('value');

        if($(this).hasClass('icon-square-selected'))
        {
            $(this).removeClass('icon-square-selected').addClass('icon-weixuanzhong');
            $(this).parent().removeClass('active');
        }
        else
        {
            $(this).removeClass('icon-weixuanzhong').addClass('icon-square-selected');
            $(this).parent().addClass('active');
        }

        var value = [];

        var check = $(this).parent().parent().find('.list-filter-checkbox.active');

        check.each(function()
        {
            value.push($(this).attr('data-value'));
        });

        value = value.toString();

        if(!value) value = '';

        $("input[name='"+type+"']").val(value);

        $('#filterForm').submit();
    })

    $('.list-filter-sort-by .iconfont').on('click',function ()
    {
        window.event.stopPropagation();

        var type = $(this).parent().parent().data('type');

        var value = $(this).data('value');

        $("input[name='"+type+"']").val(value);

        $('#filterForm').submit();
    })

    $('.filter-time-type i').on('click',function ()
    {
        window.event.stopPropagation();

        $(this).removeClass('icon-xuanze').addClass('icon-xuanzhong').parent().addClass('layui-form-radioed').siblings('.filter-time-type').removeClass('layui-form-radioed').find('i.icon-xuanzhong').removeClass('icon-xuanzhong').addClass('icon-xuanze');

        var type = $(this).parent().parent().data('type');
        var value = $(this).parent().data('value');

        $("input[name='"+type+"']").val(value);

        var starttime = $("input[name='highKeyword[start_time]']").val();

        if(starttime)
        {
            $('#filterForm').submit();
        }
    })

    if ( navigator.userAgent.toLowerCase().indexOf('electron/') > -1)
    {
        top.setIframe(document);
    }
});

//关闭弹出层
function closeLayerOpen()
{
	var index=parent.layer.getFrameIndex(window.name);

	parent.layer.close(index);
}


//sDate1和sDate2是yyyy-MM-dd格式
function DateDiff(sDate1, sDate2)
{
    var aDate, oDate1, oDate2, iDays;

    aDate = sDate1.split("-");

    oDate1 = new Date(aDate[1] + '-' + aDate[2] + '-' + aDate[0]);  //转换为yyyy-MM-dd格式

    aDate = sDate2.split("-");

    oDate2 = new Date(aDate[1] + '-' + aDate[2] + '-' + aDate[0]);

    iDays = parseInt(Math.abs(oDate1 - oDate2) / 1000 / 60 / 60 / 24); //把相差的毫秒数转换为天数

    return iDays;  //返回相差天数
}

function switchCustomerContent(obj,i)
{
	$('#customer-detail-nav li a').removeClass('active');

	$('.customerDetailContent').hide();

	$(obj).addClass('active');

	$('#customer-detail-content'+i).show();
}

// 客户详情层
function openCustomerDetail(id,type)
{
	var width = '75%';

	if(type == 'detailPop'){width = "100%";}

    layer.open(
    {
        type: 2,
        title: false,
        offset: 'r',
        area: [width,'100%'],
        content:"/"+moduleName+"/Customer/detail.html?id="+id,
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

//工单详情层
function openTicketDetail(id,postUrl)
{
	layer.open(
    {
        type: 2,
        title: false,
        offset: 'r',
        area: ['100%','100%'],
        content:postUrl+"/ticket/detail.html?id="+id,
        shade: 0,
        skin: 'bounceInRight',
        closeBtn:0,
        scrollbar: true,
        success: function(layero, index)
        {
			var id_layer_min = layero.attr("id");
			var ids =id_layer_min.split("r");
			idtabindex= "#layui-layer"+"-iframe"+ids[1];
			var ifr = document.querySelector(idtabindex);
			ifr.contentWindow.postMessage({a: idtabindex}, '*');

			window.addEventListener('message', function(e) {

				if(e.data)
				{
					layer.close(index);
				}

			}, false);

        }
    });
}

// 订单详情层
function openOrderDetail(id,type)
{
	var width = '75%';

	if(type == 'detailPop'){width = "100%";}

    layer.open(
    {
        type: 2,
        title: false,
        offset: 'r',
        area: [width,'100%'],
        content:"/"+moduleName+"/Order/detail.html?id="+id,
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

// 合同详情层
function openContractDetail(id,type)
{
	var width = '75%';

	if(type == 'detailPop'){width = "100%";}

    layer.open(
    {
        type: 2,
        title: false,
        offset: 'r',
        area: [width,'100%'],
        content:"/"+moduleName+"/Contract/detail.html?id="+id,
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

//点击打开详情公共事件
function clickOpenDetail(obj,controller)
{
	event.preventDefault();

	var id = $(obj).parent('tr').data('id');

	var type = $(obj).parent('tr').data('type');

	$(obj).parent('tr').addClass('bgfa');

	openCommonDetail(id,controller,type);
}

//a标签点击打开详情公共事件
function clickOpenDetailByA(obj,controller)
{
    event.preventDefault();

    var id = $(obj).data('id');

    var type = $(obj).data('type');

    openCommonDetail(id,controller,type);
}

// 公共详情层
function openCommonDetail(id,controller,type)
{
	var width = '75%';

	if(type == 'detailPop'){width = "100%";}

    layer.open(
    {
        type: 2,
        title: false,
        offset: 'r',
        area: [width,'100%'],
        content:"/"+moduleName+"/"+controller+"/detail.html?id="+id,
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

function switchtab(obj,i)
{
	if(!$(obj).hasClass('active'))
	{
		$('.form-tab').removeClass('active');

		$(obj).addClass('active');

		$('.form-content').addClass('hidden');

		$(".form-content"+i).removeClass('hidden');
	}
}

function closeLayerAll()
{
	layer.closeAll('page');
}

function pageclick(obj,replacehref)
{
	var href = $(obj).attr('href');

	var ajaxhref = href.replace(replacehref, '/AjaxRequest/getMemberList');

	$.ajax({
			url:ajaxhref,
			type:'POST',
			async: false,
			datatype:'json',
			success:function(data)
			{
				//console.log(11);return false;
				$('#member-detail').html(data.html);

				$('#Memberlist .feeldesk-page').html(data.page);

				layui.use('form', function() {

					var form = layui.form; //只有执行了这一步，部分表单元素才会自动修饰成功

					form.render();

				});

				$('#Memberlist .feeldesk-page a').click(function(){

					pageclick(this);

					return false;
				})

			},
			error:function()
			{
			   layer.msg(language.FAILED_TO_ACQUIRE_USER);
			}
	 });
}

function chooseMember(action,operName,data_id)
{
	var member = $('input[name="member"]:checked').val();

	if(member)
	{
		var ids = [];

		if(data_id && data_id != 'undefined')
		{
			ids.push(data_id);
		}
		else
		{
			var checkBox = $(".item-list").find('input[type="checkbox"]');

			checkBox.each(function(index, item)
			{
				if(item.checked && item.value!='on') ids.push(item.value);
			});
		}

		if(ids.length > 0)
		{
			layer.confirm(language.SURE+operName+'?',{icon: 3, offset:['100px']},function()
			{
				var loading = layer.load(2,{offset:['150px']});

				$.post(action,{ids:ids,member_id:member},function(data)
				{
					layer.close(loading);

					if(data.status == 2)
					{
						layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
						{
							window.location.reload();
						});
					}
					else
					{
						layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
					}

				},'JSON')
			});
		}
		else
		{
			layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
		}
	}
	else
	{
		layer.msg(language.PLEASE_SELECT+operName+language.USER,{icon:2,time:1500,offset:['100px']});
	}
}

//放弃客户
function abandonOperate(action,operName,data_id)
{
	var abandon_id = $('#abandon_id').val();

	var ids = [];

	if(data_id)
	{
		ids.push(data_id);
	}
	else
	{
		var checkBox = $(".item-list").find('input[type="checkbox"]');

		checkBox.each(function(index, item)
		{
			if(item.checked && item.value!='on') ids.push(item.value);
		});
	}

	if(ids.length > 0)
	{
		layer.confirm(language.SURE+operName+'?',{icon: 3, offset:['100px']},function()
		{
			var loading = layer.load(2,{offset:['150px']});

			$.post(action,{ids:ids,abandon_id:abandon_id},function(data)
			{
				layer.close(loading);

				if(data.status == 2)
				{
					layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
					{
						window.location.reload();
					});
				}
				else
				{
					layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
				}

			},'JSON')
		});
	}
	else
	{
		layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
	}
}

//客户失单
function loseCustomer(action,operName,id)
{
	var lose_id = $('#lose_id').val();

	var competitor_id = $('#competitor_id').val();

	var lose_closed = $('input[name="lose_closed"]:checked').val();

	var ids = [];

	if(id)
	{
		ids.push(id);
	}
	else
	{
		var checkBox = $(".item-list").find('input[type="checkbox"]');

		checkBox.each(function(index, item)
		{
			if(item.checked && item.value!='on') ids.push(item.value);
		});
	}

	if(ids.length > 0)
	{
		layer.confirm(language.SURE+operName+'?',{icon: 3, offset:['100px']},function()
		{
			var loading = layer.load(2,{offset:['150px']});

			$.post(action,{ids:ids,lose_id:lose_id,competitor_id:competitor_id,lose_closed:lose_closed},function(data)
			{
				layer.close(loading);

				if(data.status == 2)
				{
					layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
					{
						window.location.reload();
					});
				}
				else
				{
					layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
				}

			},'JSON')
		});
	}
	else
	{
		layer.msg(language.NO_DATA,{icon:2,time:1500,offset:['100px']});
	}
}

function highSearch()
{
	$('#highSearchForm').submit();
}

function selectProvince(j,url)
{
	var value = $("#country"+j).val();

	$.post(url,{country_id:value,type:'province'},function(data)
	{
		if(data.code == 0)
		{
			var option = '<option value="">'+language.SELECT_PROVINCE+'</option>';

			if(data.data.length > 0)
			{
				$.each(data.data,function(k,v)
				{
					option += "<option c-value='"+ v.country_code+"' value='"+v.code+"'>"+v.name+"</option>";
				});

				$('#province'+j).html(option).parents('.region-item').slideDown('fast');
			}
			else
			{
				$('#province'+j).html(option).parents('.region-item').slideUp('fast');
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
		if(data.code == 0)
		{
			var option = '<option value="">'+language.SELECT_CITY+'</option>';

			if(data.data.length > 0)
			{
				$.each(data.data,function(k,v)
				{
					option += "<option c-value='"+ v.country_code+"' p-value='"+ v.province_code+"' value='"+v.code+"'>"+v.name+"</option>";
				});

				$('#city'+j).html(option).parents('.region-item').slideDown('fast');
			}
			else
			{
				$('#city'+j).html(option).parents('.region-item').slideUp('fast');
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
		if(data.code == 0)
		{
			var option = '<option value="">'+language.SELECT_REGION+'</option>';

			if(data.data.length > 0)
			{
				$.each(data.data,function(k,v)
				{
					option += "<option value='"+v.code+"'>"+v.name+"</option>";
				});

				$('#area'+j).html(option).parents('.region-item').slideDown('fast');
			}
			else
			{
				$('#area'+j).html(option).parents('.region-item').slideUp('fast');
			}

			$("#area"+j).select2();
		}
	});
}

function addContent(oldContent,newContent)
{
	var content = '';

	if(oldContent.indexOf('/') == -1)
	{
		if(oldContent == '')
		{
			content = newContent;
		}
		else
		{
			content = oldContent+' / '+newContent;
		}
	}
	else
	{
		content = oldContent+' / '+newContent;
	}

	return content;
}

function removeContent(oldContent,newContent)
{
	var content = '';

	if(oldContent.indexOf('/') == -1)
	{
		content = oldContent.replace(newContent,'');
	}
	else
	{
		content = oldContent.replace(' / '+newContent,'');

		content = content.replace(newContent+' / ','');
	}

	return content;
}

//详情页面修改内容
function updateFormContentAjax(postUrl,infoId,formType,content,form_name,obj,typeName)
{
	var loading = layer.load(2,{offset:['150px']});

	$.post(postUrl,{id:infoId,type:formType,content:content,form_name:form_name},function(data)
	{
		layer.close(loading);

		if(data.status == 2)
		{
			if(typeName == 'region')
			{
				obj.parent().prev('.layui-detail-edit-default').html(data.data);
			}
			else if(typeName == 'textarea')
			{
				layer.msg(data.msg,{icon:1,time:1000,offset:['100px']},function()
				{
					window.location.reload();
				});
			}
			else
			{
				obj.parent().addClass('hidden').prev('.layui-detail-edit-default').html(data.data).removeClass('hidden');
			}

			obj.parent().next('.i-update-success').fadeIn();

			setTimeout(function(){obj.parent().next('.i-update-success').fadeOut();},1000);
		}
		else
		{
			layer.msg(data.msg,{icon:2,time:1500,offset:['100px']});
		}

	},'JSON')
}

//检查待办事项数量
function scheduleNum(href)
{
    $.post(href+"?request=schedule", function (data)
    {
        if(data.count > 0)
        {
            $('a[href="'+href+'"] .schedule-num').html(data.count).show();
        }
        else
        {
            $('a[href="'+href+'"] .schedule-num').html('0').hide();
        }
    });
}

function OnclickCall(phone)
{
    $.ajax({
        url:"/"+moduleName+'/AjaxRequest/OnclickCall',
        type:'POST',
        async: false,
        data:{'phone':phone},
        datatype:'json',
        success:function(data)
        {
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

//对接呼叫中心打电话
function CallUp(phone,iframeLevel = 1,e)
{
    e = e || window.event;

    if(e.stopPropagation)
    { //W3C阻止冒泡方法
        e.stopPropagation();
    } else {
        e.cancelBubble = true; //IE阻止冒泡方法
    }

    if(phone)
    {
        layer.confirm('确定拨打电话 '+phone+' ?',{icon: 3, offset:['100px']},function()
        {
            var obj = window;

            for(i=1;i<=iframeLevel;i++)
            {
                obj = obj.parent;
            }

            obj.postMessage({
                type: 'dial',   // 类型 必传 dial
                params: {
                    mark:'makeCall',              // 消息来源标识 必传
                    message:phone,   // 电话号码
                }
            }, '*')
        });
    }
    else
    {
        return false;
    }
}

function editDeleteFile(obj)
{
    var fileName =$(obj).data('name');

    $('#attachments').append("<input type='hidden' name='delFile[]' value="+fileName+">");

    $(obj).parent().remove();
}

function openFile(link,type)
{
   /* if(type == 'pdf' || type =='txt' || type == 'jpeg' || type =='png' || type == 'gif' || type =='jpg')
    {
        window.open(link);
    }
    else if(type == 'doc' || type =='docx' || type =='xlsx' || type =='xls' || type =='ppt' || type =='pptx')
    {
        link = 'https://view.officeapps.live.com/op/view.aspx?src='+link;

        window.open(link);
    }*/

    if(type == 'doc' || type =='docx' || type =='xlsx' || type =='xls' || type =='ppt' || type =='pptx')
    {
        link = 'https://view.officeapps.live.com/op/view.aspx?src='+link;
    }

    var a = $("<a href='"+link+"' target='_blank'>"+type+"</a>").get(0);
    var e = document.createEvent('MouseEvents');
    e.initEvent('click', true, true);
    a.dispatchEvent(e);
}
$(function()
{
	layui.use('form',function()
	{
		var form = layui.form;

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

						$('#cmncate_reply').parents('.feeldesk-form-item').removeClass('hidden');
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

						$('#ContacterList').parents('.feeldesk-form-item').removeClass('hidden');
					},
					error:function()
					{
					   //layer.msg('保存排序异常');
					}
			 });

		});

	})
    /*--------------------- 选择产品类型 ---------------------*/

    var handlerGroup = $('.product-parent-item');

    var handlerMember = $('.product-child-item');

    var selectProductType = $('#select-product-type');

    selectProductType.unbind('click').on('click',function()
    {
        $("#productTypeWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#productTypeBack").unbind('click').on('click',function()
    {
        $("#productTypeWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

    /*handlerMember.unbind('click').on('click',function()
    {
        var groupId = $(this).parents('.group-item').data('value');

        var groupName = $(this).parents('.group-item').data('name');

        var value = $(this).data('value');

        var name = $(this).data('name');

        handlerGroup.find('span.iconfont').removeClass('icon-radio-checked');

       $(this).find('span.iconfont').toggleClass('icon-radio-checked');

        selectProductType.html("<span>"+groupName+" — "+name+"</span>").next('input').val(groupId).next('input').val(value);

        if(handlerGroup.find('span.icon-radio-checked').length == 0)
        {
            selectProductType.html("<span>"+language.SELECT_HANDEL+"</span>").nextAll("input").val('');
        }

    });*/


    /*--------------------- 选择产品 ---------------------*/

    var productChooseAll = $("#productChooseAll");

    var product = $('.product-info');

    var toProduct = $('.to-product');

    var selectProduct = $('#select-product');

    toProduct.unbind('click').click(function()
    {
        $("#productWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#productTypeWrapper').animate({'z-index': '0',left:'-100%'}, "700");

		var href = $(this).attr('data-href');

		var orderPro = [];

		$("input[name='orderPro[]']").each(function(index, item)
		{
			orderPro.push(item.value);
		});

		$.ajax({
				url:href,
				type:'POST',
				async: false,
				data: {orderPro:orderPro},
				datatype:'json',
				success:function(data)
				{
					//console.log(11);return false;
					$('#product-list .product-item').html(data.html);

					$('#product-list .product-page').html(data.page);

					//  单选产品
					$('.product-info').unbind('click').click(function()
					{
						var spanIcon = $(this).find('span');

						var value = $(this).data('value');

						var name = $(this).data('name');

						var productItem = $('#product-item');

						var inputDom = "<input type='hidden' name='orderPro[]' value='"+value+"' />";

						var productLength = productItem.find("input[value="+value+"]").length;

						if(!spanIcon.hasClass('icon-checkbox-checked'))
						{
							if(productLength > 0)
							{
								layer.msg(language.PRODUCT_REPEAT,{time:1000,shift:0,offset:['100px']});

								return;
							}

							$("li.product-info[data-value='"+value+"']").find('span').addClass('icon-checkbox-checked');

							spanIcon.addClass('icon-checkbox-checked');

							$('#select-product').after(inputDom).append("<span data-id='"+value+"'>"+name+" </span>");

							if($('.product-info .icon-checkbox-checked').length == $('.product-info').length)
							{
								$("#productChooseAll").find('span').addClass('icon-checkbox-checked');
							}
						}
						else
						{
							$("li.product-info[data-value='"+value+"']").find('span').removeClass('icon-checkbox-checked');

							spanIcon.removeClass('icon-checkbox-checked');

							productItem.find("input[value="+value+"]").remove();

							$('#select-product').find("span[data-id='"+value+"']").remove();

							if($("#productChooseAll").find('span').hasClass('icon-checkbox-checked'))
							{
								$("#productChooseAll").find('span').removeClass('icon-checkbox-checked');
							}
						}
					});


					$('.product-page .layui-flow-more').unbind('click').click(function()
					{
						ajaxproduct(this);
					});
				},
				error:function()
				{
				   layer.msg(language.FAILED_TO_GET_PRODUCT);
				}
		 });

    });



    $("#productBack").unbind('click').click(function()
    {
        $("#productWrapper").animate({'z-index': '1',left:'200%'}, "700").fadeOut("fast").siblings('#productTypeWrapper').animate({'z-index': '1',left:'0'}, "700");
    });

//  单选产品
    product.unbind('click').click(function()
    {
        var spanIcon = $(this).find('span');

        var value = $(this).data('value');

        var name = $(this).data('name');

        var productItem = $('#product-item');

        var inputDom = "<input type='hidden' name='product[]' value='"+value+"' />";

        var productLength = productItem.find("input[value="+value+"]").length;

        if(!spanIcon.hasClass('icon-checkbox-checked'))
        {
            if(productLength > 0)
            {
                layer.msg(language.CC_DUPLICATION,{time:1000,shift:0,offset:['100px']});

                return;
            }

            $("li.product-info[data-value='"+value+"']").find('span').addClass('icon-checkbox-checked');

            spanIcon.addClass('icon-checkbox-checked');

            selectProduct.after(inputDom).append("<span data-id='"+value+"'>"+name+" </span>");

            if($('.product-info .icon-checkbox-checked').length == $('.product-info').length)
            {
                $("#productChooseAll").find('span').addClass('icon-checkbox-checked');
            }
        }
        else
        {
            $("li.product-info[data-value='"+value+"']").find('span').removeClass('icon-checkbox-checked');

            spanIcon.removeClass('icon-checkbox-checked');

            productItem.find("input[value="+value+"]").remove();

            selectProduct.find("span[data-id='"+value+"']").remove();

            if(productChooseAll.find('span').hasClass('icon-checkbox-checked'))
            {
                productChooseAll.find('span').removeClass('icon-checkbox-checked');
            }
        }
    });

//  搜索产品
    $('#productSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            $('#product-list').find('.product-info').hide().filter(":contains('"+(keyword)+"')").show();

            var productCheckedFilterLength = product.filter(":contains('"+(keyword)+"')").find('.icon-checkbox-checked').length;

            var productFilterLength = product.filter(":contains('"+(keyword)+"')").length;

            if(productCheckedFilterLength == productFilterLength && productFilterLength > 0)
            {
                $("#productChooseAll").find('span').addClass('icon-checkbox-checked');
            }
        }
        else
        {
            $('#product-list').find('.product-info').show();
        }
    });

//  全选抄送人
    productChooseAll.on('click',function()
    {
        var spanIcon = $(this).find('span');

        var productItem = $('#product-item');

        spanIcon.toggleClass('icon-checkbox-checked');

        $('.product-main').find('.product-info:visible').each(function()
        {
            var value = $(this).data('value');

            var name = $(this).data('name');

            var productLength = productItem.find("input[value="+value+"]").length;

            if(spanIcon.hasClass('icon-checkbox-checked'))
            {
                $(this).find('span').addClass('icon-checkbox-checked');

                if(productLength == 0)
                {
                    selectProduct.after("<input type='hidden' name='product[]' value='"+value+"' />").append("<span data-id='"+value+"'>"+name+" </span>");
                }
            }
            else
            {
                $(this).find('span').removeClass('icon-checkbox-checked');

                productItem.find("input[value="+value+"]").remove();

                selectProduct.find("span[data-id="+value+"]").remove();
            }
        });
    });

	//自定义筛选

	var selectScreen = $('#select-screen');

	selectScreen.on('click',function()
    {
        $("#screenWrapper").css("display",'block').animate({'z-index': '3',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

	$("#screenBack").on('click',function()
    {
        $("#screenWrapper").animate({'z-index': '3',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

	$('#finish-custom-screen').click(function()
	{
		CustomerScreen('customer');
	});

	$('#finish-clue-screen').click(function()
	{
		CustomerScreen('clue');
	});

	$('.customer-label').find('div').on('click',function()
	{
		var value= $(this).data('value');

		$(this).addClass('current').siblings().removeClass('current');

		$('#customer-label'+value).show().siblings('.feeldesk-form').hide();
	});
});

function CustomerScreen(type)
{
	var screen = [];

	var screenFixed = [];

	var Selectedscreen = [];

	var SelectedscreenFixed = [];

	$('input[name="Selectedscreen[]"]').each(function(index,item)
	{
		Selectedscreen.push(item.value);
	})

	$('input[name="SelectedscreenFixed[]"]').each(function(index,item)
	{
		SelectedscreenFixed.push(item.value);
	})

	$('input[name="screenFixed[]"]').each(function(index, item)
	{
		if($.inArray(item.value,SelectedscreenFixed) < 0 )
		{
			screenFixed.push(item.value);
		}
	});

	$('input[name="screen[]"]').each(function(index, item)
	{
		if($.inArray(item.value,Selectedscreen) < 0 )
		{
			screen.push(item.value);
		}
	});

	var url = '';

	if(type === "customer")
	{
		url  = '/AjaxRequest/customerScreen';
	}
	else if(type === "clue")
	{
		url  = '/AjaxRequest/clueScreen';
	}

	$.ajax({
		url:"/"+moduleName+url,
		type:'POST',
		async: false,
		data:{'Selectedscreen':Selectedscreen,'SelectedscreenFixed':SelectedscreenFixed,'screen':screen,'screenFixed':screenFixed},
		datatype:'json',
		success:function(data)
		{
			$('.feelcrm-screen-list-L').append(data.htmlL);

			$('.feelcrm-screen-list').find('.clear').remove();

			$('.feelcrm-screen-list').append(data.htmlR);

			$('.feelcrm-screen-list').append('<div class="clear"></div>');

			$("#screenWrapper").animate({'z-index': '3',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");

			$('.feelcrm-screen-info').unbind('click').click(function(){

				$(this).parent().find('.feelcrm-screen-info').removeClass('feelcrm-screen-infoactive');

				$(this).addClass('feelcrm-screen-infoactive');

				var value = $(this).attr('data-value');

				if(value)
				{
					$(this).parent().find('input[type="hidden"]').val(value);
				}
				else
				{
					$(this).parent().find('input[type="hidden"]').val('');

					$(this).parent().find('input[type="text"]').val('');
				}

			})

			$('.feelcrm-screen-checkbox').unbind('click').click(function(){

				var name = $(this).parent().attr('data-name');

				var value = $(this).attr('data-value');

				$(this).parent().find('.feelcrm-screen-checkbox-first').removeClass('feelcrm-screen-infoactive');

				if(!$(this).hasClass('feelcrm-screen-infoactive'))
				{
					$(this).addClass('feelcrm-screen-infoactive');

					var inputDom = "<input type='hidden' data-type='checkbox' name='highKeyword[define_form]["+name+"][]' value='"+value+"' />";

					$(this).parent().append(inputDom);
				}
				else
				{
					$(this).removeClass('feelcrm-screen-infoactive');

					$(this).parent().find("input[name='highKeyword[define_form]["+name+"][]'][value='"+value+"']").remove();
				}
			})

			$('.feelcrm-screen-checkbox-first').unbind('click').click(function(){

				$(this).parent().find('.feelcrm-screen-info').removeClass('feelcrm-screen-infoactive');

				$(this).addClass('feelcrm-screen-infoactive');

				var name = $(this).parent().attr('data-name');

				$(this).parent().find("input[name='highKeyword[define_form]["+name+"][]']").remove();

			})
		}
	});
}

function cancelSelectFixed(obj,k)
{
	$(obj).addClass('hidden');

	$('#screen-list-fixed-'+k).removeClass('hidden');

	$("input[name='screenFixed[]'][value='"+k+"']").remove();

	$('.SelectedscreenFixed'+k).remove();

	$("input[name='SelectedscreenFixed[]'][value='"+k+"']").remove();
}

function cancelSelect(obj,k)
{
	$(obj).addClass('hidden');

	$('#screen-list-'+k).removeClass('hidden');

	$("input[name='screen[]'][value='"+k+"']").remove();

	$('.Selectedscreen'+k).remove();

	$("input[name='Selectedscreen[]'][value='"+k+"']").remove();
}

function toSelectFixed(obj,k)
{
	$(obj).addClass('hidden');

	$('#screen-selected-fixed-'+k).removeClass('hidden');

	var inputDom = "<input type='hidden' name='screenFixed[]' value='"+k+"' />";

	$('#screenWrapper').append(inputDom);
}

function toSelect(obj,k)
{
	$(obj).addClass('hidden');

	$('#screen-selected-'+k).removeClass('hidden');

	var inputDom = "<input type='hidden' name='screen[]' value='"+k+"' />";

	$('#screenWrapper').append(inputDom);
}

function ajaxproduct(obj)
{
	var href = $(obj).attr('data-href');

	var orderPro = [];

	$("input[name='orderPro[]']").each(function(index, item)
	{
		orderPro.push(item.value);
	});

	$.ajax({
			url:href,
			type:'POST',
			async: false,
			data: {orderPro:orderPro},
			datatype:'json',
			success:function(data)
			{
				//console.log(11);return false;
				$('#product-list .product-item').append(data.html);

				$('#product-list .product-page').html(data.page);

				//  单选产品
				$('.product-info').unbind('click').click(function()
				{
					var spanIcon = $(this).find('span');

					var value = $(this).data('value');

					var name = $(this).data('name');

					var productItem = $('#product-item');

					var inputDom = "<input type='hidden' name='orderPro[]' value='"+value+"' />";

					var productLength = productItem.find("input[value="+value+"]").length;

					if(!spanIcon.hasClass('icon-checkbox-checked'))
					{
						if(productLength > 0)
						{
							layer.msg(language.PRODUCT_REPEAT,{time:1000,shift:0,offset:['100px']});

							return;
						}

						$("li.product-info[data-value='"+value+"']").find('span').addClass('icon-checkbox-checked');

						spanIcon.addClass('icon-checkbox-checked');

						$('#select-product').after(inputDom).append("<span data-id='"+value+"'>"+name+" </span>");

						if($('.product-info .icon-checkbox-checked').length == $('.product-info').length)
						{
							$("#productChooseAll").find('span').addClass('icon-checkbox-checked');
						}
					}
					else
					{
						$("li.product-info[data-value='"+value+"']").find('span').removeClass('icon-checkbox-checked');

						spanIcon.removeClass('icon-checkbox-checked');

						productItem.find("input[value="+value+"]").remove();

						$('#select-product').find("span[data-id='"+value+"']").remove();

						if($("#productChooseAll").find('span').hasClass('icon-checkbox-checked'))
						{
							$("#productChooseAll").find('span').removeClass('icon-checkbox-checked');
						}
					}
				});

				$('.product-page .layui-flow-more').unbind('click').click(function()
				{
					ajaxproduct(this);
				});
			},
			error:function()
			{
			   layer.msg(language.FAILED_TO_GET_PRODUCT);
			}
	 });
}

function openFile(link,type)
{
	if(type == 'pdf' || type =='txt' || type == 'jpeg' || type =='png' || type == 'gif' || type =='jpg')
	{
		window.open(link);
	}
	else if(type == 'doc' || type =='docx' || type =='xlsx' || type =='xls' || type =='ppt' || type =='pptx')
	{
		link = 'https://view.officeapps.live.com/op/view.aspx?src='+link;

		window.open(link);
	}
}

function tocrmdetail(url)
{
	event.preventDefault();

	window.location.href = url;
}

//审核提交
function examinePost(action)
{
	var reason = $("textarea[name='examineReason']").val();

	$('#examineReason').val(reason);

	var loading = layer.load(2,{offset:['40%']});

	$.post(action,$('#examineForm').serialize(),function(data)
	{
		if(data.status === 2)
		{
			layer.msg(data.msg,{time:1000,offset:['40%']},function()
			{
				window.location.reload();
			});
		}
		else
		{
			layer.close(loading);

			layer.msg(data.msg,{time:1500,offset:['40%']});
		}
	},'JSON');
}
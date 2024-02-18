$(function()
{
    var ccChooseAll = $("#ccChooseAll");

    var ccMember = $('.cc-member-info');

    var selectCc = $('#select-cc');

    selectCc.on('click',function()
    {
        $("#ccWrapper").css("display",'block').animate({'z-index': '3',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#ccBack").on('click',function()
    {
        $("#ccWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

//  单选抄送人
    ccMember.on('click',function()
    {
        var spanIcon = $(this).find('span');

        var value = $(this).data('value');

        var name = $(this).data('name');

        var ccItem = $('#cc-item');

        var inputDom = "<input type='hidden' name='cc[]' value='"+value+"' />";

        var ccLength = ccItem.find("input[value="+value+"]").length;

        if(!spanIcon.hasClass('icon-checkbox-checked'))
        {
            if(ccLength > 0)
            {
                layer.msg(language.CC_DUPLICATION,{time:1000,shift:0,offset:['100px']});

                return;
            }

            $("li.cc-member-info[data-value='"+value+"']").find('span').addClass('icon-checkbox-checked');

            spanIcon.addClass('icon-checkbox-checked');

            selectCc.after(inputDom).append("<span data-id='"+value+"'>@"+name+" </span>");

            if($('.cc-member-info .icon-checkbox-checked').length == $('.cc-member-info').length)
            {
                $("#ccChooseAll").find('span').addClass('icon-checkbox-checked');
            }
        }
        else
        {
            $("li.cc-member-info[data-value='"+value+"']").find('span').removeClass('icon-checkbox-checked');

            spanIcon.removeClass('icon-checkbox-checked');

            ccItem.find("input[value="+value+"]").remove();

            selectCc.find("span[data-id='"+value+"']").remove();

            if(ccChooseAll.find('span').hasClass('icon-checkbox-checked'))
            {
                ccChooseAll.find('span').removeClass('icon-checkbox-checked');
            }
        }
    });

//  搜索抄送人
    $('#ccSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            //.hide()隐藏全部部门 —— .hide()隐藏全部抄送人 —— .show()显示包含关键字的抄送人 —— .show()显示包含关键字的抄送人所在部门
            ccMember.parents('.group-item').siblings('.group-item').hide()
                .find('.cc-member-info').hide().filter(":contains('"+(keyword)+"')").show().parents('.group-item').show();

            var ccMemberCheckedFilterLength = ccMember.filter(":contains('"+(keyword)+"')").find('.icon-checkbox-checked').length;

            var ccMemberFilterLength = ccMember.filter(":contains('"+(keyword)+"')").length;

            if(ccMemberCheckedFilterLength == ccMemberFilterLength && ccMemberFilterLength > 0)
            {
                $("#ccChooseAll").find('span').addClass('icon-checkbox-checked');
            }
        }
        else
        {
            ccMember.show().parents('.group-item').show();

            if(ccMember.find('.icon-checkbox-checked').length != ccMember.length)
            {
                $("#ccChooseAll").find('span').removeClass('icon-checkbox-checked');
            }
        }
    });

//  全选抄送人
    ccChooseAll.on('click',function()
    {
        var spanIcon = $(this).find('span');

        var ccItem = $('#cc-item');

        spanIcon.toggleClass('icon-checkbox-checked');

        $('.cc-main').find('.cc-member-info:visible').each(function()
        {
            var value = $(this).data('value');

            var name = $(this).data('name');

            var ccLength = ccItem.find("input[value="+value+"]").length;

            if(spanIcon.hasClass('icon-checkbox-checked'))
            {
                $(this).find('span').addClass('icon-checkbox-checked');

                if(ccLength == 0)
                {
                    selectCc.after("<input type='hidden' name='cc[]' value='"+value+"' />").append("<span data-id='"+value+"'>@"+name+" </span>");
                }
            }
            else
            {
                $(this).find('span').removeClass('icon-checkbox-checked');

                ccItem.find("input[value="+value+"]").remove();

                selectCc.find("span[data-id="+value+"]").remove();
            }
        });
    });


    var createrChooseAll = $("#createrChooseAll");

    var createrMember = $('.creater-member-info');

    var selectCreater = $('#select-creater');

    selectCreater.on('click',function()
    {
        $("#createrWrapper").css("display",'block').animate({'z-index': '3',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#createrBack").on('click',function()
    {
        $("#createrWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

//  单选抄送人
    createrMember.on('click',function()
    {
        var spanIcon = $(this).find('span');

        var value = $(this).data('value');

        var name = $(this).data('name');

        var createrItem = $('#creater-item');

        var inputDom = "<input type='hidden' name='creater[]' value='"+value+"' />";

        var createrLength = createrItem.find("input[value="+value+"]").length;

        if(!spanIcon.hasClass('icon-checkbox-checked'))
        {
            if(createrLength > 0)
            {
                layer.msg(language.CC_DUPLICATION,{time:1000,shift:0,offset:['100px']});

                return;
            }

            $("li.creater-member-info[data-value='"+value+"']").find('span').addClass('icon-checkbox-checked');

            spanIcon.addClass('icon-checkbox-checked');

            selectCreater.after(inputDom).append("<span data-id='"+value+"'>@"+name+" </span>");

            if($('.creater-member-info .icon-checkbox-checked').length == $('.creater-member-info').length)
            {
                $("#createrChooseAll").find('span').addClass('icon-checkbox-checked');
            }
        }
        else
        {
            $("li.creater-member-info[data-value='"+value+"']").find('span').removeClass('icon-checkbox-checked');

            spanIcon.removeClass('icon-checkbox-checked');

            createrItem.find("input[value="+value+"]").remove();

            selectCreater.find("span[data-id='"+value+"']").remove();

            if(createrChooseAll.find('span').hasClass('icon-checkbox-checked'))
            {
                createrChooseAll.find('span').removeClass('icon-checkbox-checked');
            }
        }
    });

//  搜索创建人
    $('#createrSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            //.hide()隐藏全部部门 —— .hide()隐藏全部抄送人 —— .show()显示包含关键字的抄送人 —— .show()显示包含关键字的抄送人所在部门
            createrMember.parents('.group-item').siblings('.group-item').hide()
                .find('.creater-member-info').hide().filter(":contains('"+(keyword)+"')").show().parents('.group-item').show();

            var createrMemberCheckedFilterLength = createrMember.filter(":contains('"+(keyword)+"')").find('.icon-checkbox-checked').length;

            var createrMemberFilterLength = createrMember.filter(":contains('"+(keyword)+"')").length;

            if(createrMemberCheckedFilterLength == createrMemberFilterLength && createrMemberFilterLength > 0)
            {
                $("#createrChooseAll").find('span').addClass('icon-checkbox-checked');
            }
        }
        else
        {
            createrMember.show().parents('.group-item').show();

            if(createrMember.find('.icon-checkbox-checked').length != createrMember.length)
            {
                $("#createrChooseAll").find('span').removeClass('icon-checkbox-checked');
            }
        }
    });

//  全选创建人
    createrChooseAll.on('click',function()
    {
        var spanIcon = $(this).find('span');

        var createrItem = $('#creater-item');

        spanIcon.toggleClass('icon-checkbox-checked');

        $('.creater-main').find('.creater-member-info:visible').each(function()
        {
            var value = $(this).data('value');

            var name = $(this).data('name');

            var createrLength = createrItem.find("input[value="+value+"]").length;

            if(spanIcon.hasClass('icon-checkbox-checked'))
            {
                $(this).find('span').addClass('icon-checkbox-checked');

                if(createrLength == 0)
                {
                    selectCreater.after("<input type='hidden' name='creater[]' value='"+value+"' />").append("<span data-id='"+value+"'>@"+name+" </span>");
                }
            }
            else
            {
                $(this).find('span').removeClass('icon-checkbox-checked');

                createrItem.find("input[value="+value+"]").remove();

                selectCreater.find("span[data-id="+value+"]").remove();
            }
        });
    });


    /*--------------------- 选择处理人 ---------------------*/

    var processGroup = $('.group-item');

    var processMember = $('.process-member');

    var selectProcess = $('#select-process');

    selectProcess.unbind('click').on('click',function()
    {
        $("#processWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#processBack").unbind('click').on('click',function()
    {
        $("#processWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

    processMember.unbind('click').on('click',function()
    {
        var groupId = $(this).parents('.group-item').data('value');

        var groupName = $(this).parents('.group-item').data('name');

        var value = $(this).data('value');

        var name = $(this).data('name');

        processGroup.find('span.iconfont').removeClass('icon-radio-checked');

       $(this).find('span.iconfont').toggleClass('icon-radio-checked');

        selectProcess.html("<span>"+groupName+" — "+name+"</span>").next('input').val(groupId).next('input').val(value);

        if(processGroup.find('span.icon-radio-checked').length == 0)
        {
            selectProcess.html("<span>"+language.SELECT_HANDEL+"</span>").nextAll("input").val('');
        }
    });

//  搜索处理人
    $('#processSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            //.hide()隐藏全部部门 —— .show()显示包含关键字的部门 —— .hide()隐藏所有处理人 —— .show()显示包含关键字的处理人
            processGroup.hide().filter(":contains('"+(keyword)+"')").show().find('.process-member').hide().filter(":contains('"+(keyword)+"')").show();
        }
        else
        {
            processGroup.show().find('.process-member').show();
        }
    });



    /*--------------------- 选择审核对象 ---------------------*/

    var auditGroup = $('.audit-group-item');

    var auditMember = $('.audit-member');

    var selectAudit = $('#select-audit');

    selectAudit.unbind('click').on('click',function()
    {
        $("#auditWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#auditBack").unbind('click').on('click',function()
    {
        $("#auditWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

    var audit_object = 10;

    $('#select-audit-object a').on('click',function ()
    {
        $(this).addClass('current').siblings('a').removeClass('current');

        audit_object = $(this).data('value');

        $('#audit-'+audit_object).removeClass('hidden').siblings('.audit-object-item').addClass('hidden');
    });

//  选择审核人员
    auditMember.unbind('click').on('click',function()
    {
        var audit_discrete = $(this).data('value');

        var auditor_name = $(this).data('name');

        auditGroup.find('span.iconfont').removeClass('icon-radio-checked');

        $('#audit-20').find('span.iconfont').removeClass('icon-radio-checked');

        $(this).find('span.iconfont').toggleClass('icon-radio-checked');

        selectAudit.html("<span>"+language.AUDITOR+' - '+auditor_name+"</span>").next('input').val(audit_object).next('input').val(audit_discrete);

        if(auditGroup.find('span.icon-radio-checked').length == 0)
        {
            selectAudit.html("<span>"+language.AUDIT+"</span>").nextAll("input").val('');
        }
    });

//  搜索审核人员
    $('#auditSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            //.hide()隐藏全部部门 —— .show()显示包含关键字的部门 —— .hide()隐藏所有处理人 —— .show()显示包含关键字的处理人
            auditGroup.hide().filter(":contains('"+(keyword)+"')").show().find('.process-member').hide().filter(":contains('"+(keyword)+"')").show();
        }
        else
        {
            auditGroup.show().find('.process-member').show();
        }
    });

//  选择审核流程
    var auditProcess = $('.audit-process');

    auditProcess.unbind('click').on('click',function()
    {
        var audit_discrete = $(this).data('value');

        var process_name = $(this).data('name');

        auditProcess.find('span.iconfont').removeClass('icon-radio-checked');

        $('#audit-10').find('span.iconfont').removeClass('icon-radio-checked');

        $(this).find('span.iconfont').toggleClass('icon-radio-checked');

        selectAudit.html("<span>"+language.AUDIT_PROCESS+' - '+process_name+"</span>").next('input').val(audit_object).next('input').val(audit_discrete);

        if($('.audit-process-item').find('span.icon-radio-checked').length == 0)
        {
            selectAudit.html("<span>"+language.AUDIT+"</span>").nextAll("input").val('');
        }
    });

    /*--------------------- 选择会员 ---------------------*/

    var member = $('.member');

    var selectMember = $('#select-member');

    selectMember.on('click',function()
    {
        $("#memberWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#memberBack").on('click',function()
    {
        $("#memberWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

    member.on('click',function()
    {
        var value = $(this).data('value');

        var name = $(this).data('name');

        $(this).find('span.iconfont').toggleClass('icon-radio-checked').parent('.member').siblings('.member').find('span.iconfont').removeClass('icon-radio-checked');

        selectMember.html("<span>"+name+"</span>").next("input").val(value);

        if(member.find('span.icon-radio-checked').length == 0)
        {
            selectMember.html("<span>"+language.SELECT_MEMBER+"</span>").next("input").val('');
        }
        else
        {
            $('#select-customer').html("<span>"+language.SELECT_CUSTOMER+"</span>").nextAll("input").val('');//清空客户

            $('#select-visitor').html("<span>"+language.SELECT_VISITOR+"</span>").next("input").val('');//清空游客

            $('.visitor,.customer').find('span.iconfont').removeClass('icon-radio-checked');//去除游客与客户选中
        }
    });

//  搜索会员
    $('#memberSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            member.hide().filter(":contains('"+(keyword)+"')").show();

            if(member.filter(":contains('"+(keyword)+"')").length == 0)
            {
                $(".member-item .search-no-data").fadeIn('fast');
            }
            else
            {
                $(".member-item .search-no-data").hide();
            }
        }
        else
        {
            member.show();

            $(".member-item .search-no-data").fadeOut('fast');
        }
    });


    /*--------------------- 选择游客 ---------------------*/

    var visitor = $('.visitor');

    var selectVisitor = $('#select-visitor');

    selectVisitor.on('click',function()
    {
        $("#visitorWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#visitorBack").on('click',function()
    {
        $("#visitorWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

    visitor.on('click',function()
    {
        var value = $(this).data('value');

        var name = $(this).data('name');

        $(this).find('span.iconfont').toggleClass('icon-radio-checked').parent('.member').siblings('.member').find('span.iconfont').removeClass('icon-radio-checked');

        selectVisitor.html("<span>"+name+"</span>").next("input").val(value);

        if(visitor.find('span.icon-radio-checked').length == 0)
        {
            selectVisitor.html("<span>"+language.SELECT_VISITOR+"</span>").next("input").val('');
        }
        else
        {
            $('#select-customer').html("<span>"+language.SELECT_CUSTOMER+"</span>").nextAll("input").val('');//清空客户

            $('#select-member').html("<span>"+language.SELECT_MEMBER+"</span>").next("input").val('');//清空会员

            $('.member,.customer').find('span.iconfont').removeClass('icon-radio-checked');//去除会员与客户选中
        }
    });

//  搜索游客
    $('#visitorSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            visitor.hide().filter(":contains('"+(keyword)+"')").show();

            if(visitor.filter(":contains('"+(keyword)+"')").length == 0)
            {
                $(".visitor-item .search-no-data").fadeIn('fast');
            }
            else
            {
                $(".visitor-item .search-no-data").hide();
            }
        }
        else
        {
            visitor.show();

            $(".visitor-item .search-no-data").fadeOut('fast');
        }
    });


    /*--------------------- 选择客户 ---------------------*/

    var customer = $('.customer');

    var selectCustomer = $('#select-customer');

    selectCustomer.on('click',function()
    {
        $("#customerWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#customerBack").on('click',function()
    {
        $("#customerWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

    customer.on('click',function()
    {
        var value = $(this).data('value');

        var name = $(this).data('name');

        $(this).find('span.iconfont').toggleClass('icon-radio-checked').parent('.customer').siblings('.customer').find('span.iconfont').removeClass('icon-radio-checked');

        selectCustomer.html("<span>"+name+"</span>").next("input").val(value).next('input').val(name);

        if(customer.find('span.icon-radio-checked').length == 0)
        {
            selectCustomer.html("<span>"+language.SELECT_CUSTOMER+"</span>").nextAll("input").val('');

            $('.order').show();
        }
        else
        {
            $('#select-order').html("<span>"+language.SELECT_ORDER+"</span>").nextAll("input").val('');

            $('.order').find('span.iconfont').removeClass('icon-radio-checked');

            $(".order[data-id='"+value+"']").show().siblings(".order:not([data-id='"+value+"'])").hide();//显示该客户的订单并隐藏不是该客户的订单

            $('#select-visitor').html("<span>"+language.SELECT_VISITOR+"</span>").next("input").val('');//清空游客

            $('#select-member').html("<span>"+language.SELECT_MEMBER+"</span>").next("input").val('');//清空会员

            $('.member,.visitor').find('span.iconfont').removeClass('icon-radio-checked');//去除会员与游客选中
        }
    });

//  搜索客户
    $('#customerSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            customer.hide().filter(":contains('"+(keyword)+"')").show();

            if(customer.filter(":contains('"+(keyword)+"')").length == 0)
            {
                $(".customer-item .search-no-data").fadeIn('fast');
            }
            else
            {
                $(".customer-item .search-no-data").hide();
            }
        }
        else
        {
            customer.show();

            $(".customer-item .search-no-data").fadeOut('fast');
        }
    });


/*--------------------- 选择订单 ---------------------*/

    var order = $('.order');

    var selectOrder = $('#select-order');

    selectOrder.on('click',function()
    {
        $("#orderWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#orderBack").on('click',function()
    {
        $("#orderWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

    order.on('click',function()
    {
        var value = $(this).data('value');

        var no = $(this).data('no');

        var id = $(this).data('id');

        var name = $(this).data('name');

        $(this).find('span.iconfont').toggleClass('icon-radio-checked').parent('.order').siblings('.order').find('span.iconfont').removeClass('icon-radio-checked');

        selectOrder.html("<span>"+no+"</span>").next("input").val(value).next('input').val(no).next('input').val(id).next('input').val(name);

        if(order.find('span.icon-radio-checked').length == 0)
        {
            selectOrder.html("<span>"+language.SELECT_ORDER+"</span>").nextAll("input").val('');
        }
        else
        {
            $("#crm_customer_id").val(id).next('input').val(name);

            $("#select-customer").html("<span>"+name+"</span>");

            $(".customer[data-value='"+id+"']").find('span.iconfont').addClass('icon-radio-checked').parent('.customer')
                .siblings('.customer').find('span.iconfont').removeClass('icon-radio-checked');
        }
    });

//  搜索订单
    $('#orderSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            order.hide().filter(":contains('"+(keyword)+"')").show();

            if(order.filter(":contains('"+(keyword)+"')").length == 0)
            {
                $(".order-item .search-no-data").fadeIn('fast');
            }
            else
            {
                $(".order-item .search-no-data").hide();
            }
        }
        else
        {
            order.show();

            $(".order-item .search-no-data").fadeOut('fast');
        }
    });


    /*--------------------- 选择产品 ---------------------*/

    var product = $('.product');

    var selectProduct = $('#select-product');

    selectProduct.on('click',function()
    {
        $("#productWrapper").css("display",'block').animate({'z-index': '1',left:0}, "700").siblings('#formWrapper').animate({'z-index': '0',right:'100%'}, "700");
    });

    $("#productBack").on('click',function()
    {
        $("#productWrapper").animate({'z-index': '1',left:'100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right:'0'}, "700");
    });

    product.on('click',function()
    {
        var value = $(this).data('value');

        var name = $(this).data('name');

        var no = $(this).data('no');

        $(this).find('span.iconfont').toggleClass('icon-radio-checked').parent('.product').siblings('.product').find('span.iconfont').removeClass('icon-radio-checked');

        selectProduct.html("<span>"+name+' — '+no+"</span>").next("input").val(value).next('input').val(name);

        if(product.find('span.icon-radio-checked').length == 0)
        {
            selectProduct.html("<span>"+language.SELECT_PRODUCT+"</span>").nextAll("input").val('');
        }
    });

//  搜索产品
    $('#productSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            product.hide().filter(":contains('"+(keyword)+"')").show();

            if(product.filter(":contains('"+(keyword)+"')").length == 0)
            {
                $(".product-item .search-no-data").fadeIn('fast');
            }
            else
            {
                $(".product-item .search-no-data").hide();
            }
        }
        else
        {
            product.show();

            $(".product-item .search-no-data").fadeOut('fast');
        }
    });

});

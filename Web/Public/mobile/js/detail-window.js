$(function()
{
    var ccChooseAll = $("#ccChooseAll");

    var ccMember = $('.cc-member-info');

    var selectCc = $('#inside-cc');

    var insideReplyContent = $("#insideReplyContent");

    selectCc.on('click', function () {
        $("#ccWrapper").css("display", 'block').animate({'z-index': '1', left: 0}, "700").siblings('#formWrapper').animate({
            'z-index': '0',
            right: '100%'
        }, "700");
    });

    $("#ccBack").on('click', function () {
        $("#ccWrapper").animate({'z-index': '1', left: '100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({
            'z-index': '0',
            right: '0'
        }, "700");
    });

    insideReplyContent.keyup(function () {
        if (event.which == 8) {
            $('.cc-member-item').find('span').each(function () {
                var ccMember = $(this);

                var value = ccMember.data('id');

                if (insideReplyContent.find("span[data-id='" + value + "']").find('input').length == 0) {
                    insideReplyContent.find("span[data-id='" + value + "']").remove();

                    selectCc.find("input[value=" + value + "]").remove();

                    ccMember.remove();

                    $("li.cc-member-info[data-value='" + value + "']").find("span.iconfont").removeClass('icon-checkbox-checked');
                }
            })
        }
    });

//  单选抄送人
    ccMember.on('click', function () {
        var spanIcon = $(this).find('span');

        var value = $(this).data('value');

        var name = $(this).data('name');

        var ccInputDom = "<input type='hidden' name='team_reply[cc_member_id][]' value='" + value + "' />";

        var ccLength = selectCc.find("input[value=" + value + "]").length;

        if (!spanIcon.hasClass('icon-checkbox-checked')) {
            if (ccLength > 0) {
                layer.msg(language.CC_DUPLICATION, {time: 1000, shift: 0, offset: ['100px']});

                return;
            }

            $("li.cc-member-info[data-value='" + value + "']").find('span').addClass('icon-checkbox-checked');

            spanIcon.addClass('icon-checkbox-checked');

            selectCc.append(ccInputDom);

            var str = "<span class='atwho-inserted' data-atwho-at-query='@' data-id=" + value + ">" +
                "<span class='cc-member'>@<span>" + name + "</span>" +
                "</span><input type='hidden' name='ticket[cc_id][]' value='" + value + "'></span> &zwj;";

            insideReplyContent.append(str).next('span.cc-member-item').append("<span data-id='" + value + "'>@" + name + " </span>");

            if ($('.cc-member-info .icon-checkbox-checked').length == $('.cc-member-info').length) {
                $("#ccChooseAll").find('span').addClass('icon-checkbox-checked');
            }
        }
        else {
            $("li.cc-member-info[data-value='" + value + "']").find('span').removeClass('icon-checkbox-checked');

            spanIcon.removeClass('icon-checkbox-checked');

            selectCc.find("input[value=" + value + "]").remove();

            insideReplyContent.find("span[data-id='" + value + "']").remove();

            insideReplyContent.next("span.cc-member-item").find("span[data-id='" + value + "']").remove();

            if (ccChooseAll.find('span').hasClass('icon-checkbox-checked')) {
                ccChooseAll.find('span').removeClass('icon-checkbox-checked');
            }
        }
    });

//  搜索抄送人
    $('#ccSearch').keyup(function () {
        var keyword = $(this).val();

        if (keyword) {
            //.hide()隐藏全部部门 —— .hide()隐藏全部抄送人 —— .show()显示包含关键字的抄送人 —— .show()显示包含关键字的抄送人所在部门
            ccMember.parents('.group-item').siblings('.group-item').hide()
                .find('.cc-member-info').hide().filter(":contains('" + (keyword) + "')").show().parents('.group-item').show();

            var ccMemberCheckedFilterLength = ccMember.filter(":contains('" + (keyword) + "')").find('.icon-checkbox-checked').length;

            var ccMemberFilterLength = ccMember.filter(":contains('" + (keyword) + "')").length;

            if (ccMemberCheckedFilterLength == ccMemberFilterLength && ccMemberFilterLength > 0) {
                $("#ccChooseAll").find('span').addClass('icon-checkbox-checked');
            }
        }
        else {
            ccMember.show().parents('.group-item').show();

            if (ccMember.find('.icon-checkbox-checked').length != ccMember.length) {
                $("#ccChooseAll").find('span').removeClass('icon-checkbox-checked');
            }
        }
    });

//  全选抄送人
    ccChooseAll.on('click', function () {
        var spanIcon = $(this).find('span');

        spanIcon.toggleClass('icon-checkbox-checked');

        $('.cc-main').find('.cc-member-info:visible').each(function () {
            var value = $(this).data('value');

            var name = $(this).data('name');

            var ccLength = selectCc.find("input[value=" + value + "]").length;

            if (spanIcon.hasClass('icon-checkbox-checked')) {
                $(this).find('span').addClass('icon-checkbox-checked');

                if (ccLength == 0) {
                    var ccInputDom = "<input type='hidden' name='team_reply[cc_member_id][]' value='" + value + "' />";

                    selectCc.append(ccInputDom);

                    var str = "<span class='atwho-inserted' data-atwho-at-query='@' data-id=" + value + ">" +
                        "<span class='cc-member'>@<span>" + name + "</span>" +
                        "</span><input type='hidden' name='ticket[cc_id][]' value='" + value + "'></span> &zwj;";

                    insideReplyContent.append(str).next('span.cc-member-item').append("<span data-id='" + value + "'>@" + name + " </span>");
                }
            }
            else {
                $(this).find('span').removeClass('icon-checkbox-checked');

                selectCc.find("input[value=" + value + "]").remove();

                insideReplyContent.find("span[data-id='" + value + "']").remove();

                insideReplyContent.next("span.cc-member-item").find("span").remove();
            }
        });
    });


    /*--------------------- 分配处理人 ---------------------*/

    var processGroup = $('.group-item');

    var processMember = $('.process-member');

    var selectProcess = $('#select-process');

    selectProcess.unbind('click').on('click', function ()
    {
        $("#processWrapper").css("display", 'block').animate({'z-index': '1', left: 0}, "700").siblings('#formWrapper').animate({'z-index': '0',right: '100%'}, "700");
    });

    $("#processBack").unbind('click').on('click', function ()
    {
        $("#processWrapper").animate({'z-index': '1', left: '100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({'z-index': '0',right: '0'}, "700");
    });

    processMember.unbind('click').on('click', function ()
    {
        var groupId = $(this).parents('.group-item').data('value');

        var groupName = $(this).parents('.group-item').data('name');

        var value = $(this).data('value');

        var name = $(this).data('name');

        processGroup.find('span.iconfont').removeClass('icon-radio-checked');

        $(this).find('span.iconfont').toggleClass('icon-radio-checked');

        selectProcess.html("<span>" + groupName + " — " + name + "</span>");

        if(processGroup.find('span.icon-radio-checked').length == 0)
        {
            selectProcess.html("<span>" + language.SELECT_HANDEL + "</span>");
        }
        else
        {
            $('#processDone').unbind('click').on('click',function()
            {
                var loading = layer.load(2,{offset:['40%']});

                $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,{groupId:groupId,userId:value,source:'assignProcessor'},function(data)
                {
                    if(data.errcode == 1)
                    {
                        layer.close(loading);

                        layer.msg(data.msg,{time:2000,offset:['40%']});
                    }
                    else
                    {
                        layer.msg(data.msg,{time:1000,offset:['40%']});

                        window.location.reload();
                    }
                },'JSON');
            })
        }
    });

//  搜索分配对象
    $('#processSearch').keyup(function ()
    {
        var keyword = $(this).val();

        if (keyword)
        {
            //.hide()隐藏全部部门 —— .show()显示包含关键字的部门 —— .hide()隐藏所有处理人 —— .show()显示包含关键字的处理人
            processGroup.hide().filter(":contains('" + (keyword) + "')").show().find('.process-member').hide().filter(":contains('" + (keyword) + "')").show();
        }
        else
        {
            processGroup.show().find('.process-member').show();
        }
    });

    /*--------------------- 审核流程 ---------------------*/

    var toProcess = $('#showAuditProcess');

    toProcess.on('click', function () {
        $("#auditProcessWrapper").css("display", 'block').animate({'z-index': '1', left: 0}, "700").siblings('#formWrapper').animate({
            'z-index': '0',
            right: '100%'
        }, "700");

        if($('.ticket-auditor').length > 0)
        {
            $('.progress-content').css('height','calc(100% - 12.3vw)');
        }
    });

    $("#auditProcessBack").on('click', function () {
        $("#auditProcessWrapper").animate({'z-index': '1', left: '100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({
            'z-index': '0',
            right: '0'
        }, "700");
    });

    $('.opinion-title').prev('.progress-item').css('margin-bottom',0);

    /*--------------------- 审批意见 ---------------------*/

    var approvalTicket = $('#approval-ticket');

    approvalTicket.on('click', function () {
        $("#auditOpinionWrapper").css("display", 'block').animate({'z-index': '1', left: 0}, "700").siblings('#formWrapper').animate({
            'z-index': '0',
            right: '100%'
        }, "700");

        // var value = $(this).data('value');

        // $('#audit-value').val(value);

        $('#auditOpinionDone').unbind('click').on('click',function ()
        {
            var loading = layer.load(2,{offset:['40%']});

            $.post("/"+moduleName+'/Ticket/detail?id='+ticketId,$('#audit-form').serialize(),function(data)
            {
                if(data.errcode != 0)
                {
                    layer.close(loading);

                    layer.msg(data.msg,{time:3000,offset:['40%']});
                }
                else
                {
                    layer.msg(data.msg,{time:1000,offset:['40%']},function()
                    {
                        window.location.reload();
                    });
                }
            },'JSON');
        })
    });

    $("#auditOpinionBack").on('click', function () {
        $("#auditOpinionWrapper").animate({'z-index': '1', left: '100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({
            'z-index': '0',
            right: '0'
        }, "700");
    });

});

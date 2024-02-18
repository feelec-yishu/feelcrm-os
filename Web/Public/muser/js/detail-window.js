$(function()
{
    /*--------------------- 审核流程 ---------------------*/

    var toProcess = $('#showAuditProcess');

    toProcess.on('click', function () {
        $("#auditProcessWrapper").css("display", 'block').animate({'z-index': '1', left: 0}, "700").siblings('#formWrapper').animate({
            'z-index': '0',
            right: '100%'
        }, "700");
    });

    $("#auditProcessBack").on('click', function () {
        $("#auditProcessWrapper").animate({'z-index': '1', left: '100%'}, "700").fadeOut("fast").siblings('#formWrapper').animate({
            'z-index': '0',
            right: '0'
        }, "700");
    });

    $('.opinion-title').prev('.progress-item').css('margin-bottom',0);
});

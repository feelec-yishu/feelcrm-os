$(function()
{
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
        var value = $(this).data('value');

        var name = $(this).data('name');

        processMember.find('span.iconfont').removeClass('icon-radio-checked');

        $(this).find('span.iconfont').toggleClass('icon-radio-checked');

        selectProcess.html("<span>"+name+"</span>").next('input').val(value);

        if(processMember.find('span.icon-radio-checked').length == 0)
        {
            selectProcess.html("<span>"+language.SELECT_HANDEL+"</span>").next("input").val('');
        }
    });

//  搜索处理人
    $('#processSearch').keyup(function()
    {
        var keyword = $(this).val();

        if(keyword)
        {
            //.hide()隐藏全部部门 —— .show()显示包含关键字的部门 —— .hide()隐藏所有处理人 —— .show()显示包含关键字的处理人
            $('.process-member').hide().filter(":contains('"+(keyword)+"')").show();
        }
        else
        {
            $('.process-member').show();
        }
    });
});

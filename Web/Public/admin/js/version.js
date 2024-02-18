layui.config({base: '/Public/js/layui/extends/'}).extend({authtree: 'authtree/authtree'}).use(['form','authtree'],function()
{
    var loading;

    var form = layui.form;

    var tree = layui.authtree;

    var vn = $(".version-name");

    vn.hover
    (
        function()
        {
            if(!$(this).find('input').is(':focus'))
            {
                $(this).find('.del').animate({opacity:1},300);
            }
        },
        function()
        {
            $(this).find('.del').animate({opacity:0},300);
        }
    );

    vn.children('input').on('focus',function()
    {
        $(this).next('.del').animate({opacity:0},100);
    });

    $(".version-add").on('click',function()
    {
        $(":text,:hidden").removeClass('active');

        var html = "<div class='version-name'><input type='text' name='version[version_name]' value='' class='active' placeholder='请输入版本名称'/></div>";

        $(".version-add").before(html);

        $(".version-name").find('input').focus();

        getFeelMenus('-1');
    });

    $(document).on('click','.version-name',function()
    {
        var vni = $(this).children('input');

        if(!vni.hasClass('active'))
        {
            $(":text,:hidden").removeClass('active');

            vni.addClass('active');

            var vid = vni.data('id');

            if(vid == undefined) vid = '-1';

            getFeelMenus(vid);
        }
    });

    $(".del").on('click',function()
    {
        var vid = $(this).attr('data-id');

        layer.confirm('确认删除？', {icon: 3, title:'提示',offset:['100px']}, function(index)
        {
            $.post("/"+moduleName+"/Version/delete",{version_id:vid},function(res)
            {
                if(res.code != 2)
                {
                    layer.msg(res.msg,{icon:2,time:2000,offset:['100px']});
                }
                else
                {
                    layer.msg(res.msg,{icon:1,time:1000,offset:['100px']},function()
                    {
                        window.location.href=res.url;
                    });
                }
            });

            layer.close(index);
        });
    });

    getFeelMenus(version_id);

    function renderAuthTree(id,menus,name)
    {
        tree.render('#'+id, menus,
        {
            inputname: name,
            layfilter: 'lay-check-auth',
            childKey:'children',
            nameKey: 'menu_name',
            valueKey: 'menu_id',
            openall:true,
            autowidth: true
        });

        layer.close(loading);
    }

    function getFeelMenus(vid)
    {
        loading = layer.load(2);

        $.get("/"+moduleName+"/Version/index",{version_id:vid,source:'change'},function(data)
        {
            renderAuthTree('ticketAuth',data.ticketMenus,'auth[ticket][]');

            renderAuthTree('crmAuth',data.crmMenus,'auth[crm][]');

        },'JSON');
    }

    form.on('submit(version)',function()
    {
        var data= $("form,input[class='active']").serialize();

        $.post("/"+moduleName+"/Version/create",data,function(res)
        {
            if(res.code != 2)
            {
                layer.msg(res.msg,{icon:2,time:2000,offset:['100px']});
            }
            else
            {
                layer.msg(res.msg,{icon:1,time:1000,offset:['100px']},function()
                {
                    window.location.href=res.url;
                });
            }
        },'JSON')
    })
});
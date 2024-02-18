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
$(document).ready(function(e) 
{
    /* 头部菜单显示与隐藏 */
    var itme;

    $(".nav").hover(function()
    {
        itme = $(this);

        itme.find('.menu-panel').show();
    },
    function()
    {
        itme.find('.menu-panel').hide();
    });

    $(".lang-menu").hover(function()
    {
        itme = $(this);

        itme.find('.lang-panel').show();
    },
    function()
    {
        itme.find('.lang-panel').hide();
    });

    /* 左侧导航菜单伸展 */
    $(".show").on('click',function()
    {
        var t = $(this);

        var a = $(this).find('.arrow');

        var e = $(this).data('id');

        var childMenu = $("#child-menu"+e);

        if(childMenu.css("display") == 'none')
        {
            childMenu.slideDown(50);

            a.html('&#xe625;');

            t.parent('.nav-li').css('background','#2b2f3e');

            t.css('border-left','3px solid #3dfdff');

            childMenu.find('.child-menu-li').css('border-left','3px solid #3dfdff');
        }
        else
        {
            childMenu.slideUp(50);

            a.html('&#xe623;');

            t.parent('.nav-li').css('background','');

            t.css('border-left','none');

            childMenu.find('child-menu-li').css('border-left','none');
        }
    });

    $(".menu-fold").on('click',function()
    {
        var fold = $(this);

        if(fold.hasClass('icon-unfold'))
        {
            $(".feeldesk-left").addClass('feeldesk-left-icon');

            fold.removeClass('icon-unfold').addClass('icon-fold');

            $(".nav-ul").hide();

            $(".nav-ul-icon").show();

            $(".feeldesk-right-wrap").css('padding-left','55px');
        }
        else
        {
            $(".feeldesk-left").removeClass('feeldesk-left-icon');

            fold.removeClass('icon-fold').addClass('icon-unfold');

            $(".nav-ul").show();

            $(".nav-ul-icon,.nav-icon-menu").hide();

            $(".feeldesk-right-wrap").css('padding-left','250px');
        }
    });

    var iconMenu;

    $(".nav-ul-icon li").stop(true, true).hover(function ()
    {
        var iconMenuLi = $(this);

        var menuId = $(this).data('value');

        $('.nav-ul-icon li').removeClass('active');

        $(this).addClass('active');

        $(".nav-icon-menu").hide();

        iconMenu = $(".nav-icon-menu[data-value="+menuId+"]");

        iconMenu.show();

        iconMenu.find('li').unbind('click').on('click',function()
        {
            iconMenu.hide();

            $('.nav-icon-menu').find('a').removeClass('icon-menu-active');

            $('.nav-ul-icon li').removeClass('current');

            $(this).find('a').addClass('icon-menu-active');

            $('.nav-ul-icon').find("li[data-value='"+menuId+"']").addClass('current');
        });

        iconMenu.mouseleave(function(e)
        {
            iconMenuLi.removeClass('active');

            $(this).hide();
        });
    });

    /* 左侧导航子菜单选中 */
    $(".child-menu-a").click(function()
    {
        var a = $(this);

        if(!(a.hasClass('child-menu-current')))
        {
            $(".child-menu-ul").each(function()
            {
                $(this).find('li a').removeClass('child-menu-current');

                $(this).find('i').removeClass('feeldesk-icon-baidian');

                $(this).find('i').addClass('feeldesk-icon-heidian');
            });

            a.addClass('child-menu-current');
        }
    });

//    列表操作面板
    $(".listOperate").hover(function()
    {
        $(this).parent('tr').siblings().find('td div').slideUp('fast');

        $(this).find('.operate').stop(true, true).slideDown('fast');
    },
    function ()
    {
        $(this).find('.operate').stop(true, true).slideUp('fast');
    });


    $(document).on("click", "a[mini='adm'],a[mini='adc'],a[mini='edt']", function (e)
	{
		e.preventDefault();

		var action =  $(this).attr('href');

		layer.open({type: 2,title: false,scrollbar:false,offset: '60px',area: ['80%','500px'],content: action});
	});


	$(document).on("click", "a[mini='del']", function (e) 
	{
		e.preventDefault();

		var id =  $(this).attr('id');

		var classAttr = $(this).attr('class');

		layer.confirm("确定删除此菜单项？",{icon: 3, title:'提示',offset:'60px'},function()
		{
			$.post("/"+moduleName+"/menu/delete",{'menu_id':id},function(data)
			{
				if(data.status == 0)
				{
					layer.msg(data.msg,{icon:2,time:1000,offset:'60px'});
				}
				else
				{
					layer.msg(data.msg,{icon:1,time:1000,shift:0,offset:'60px'},function()
					{
						if(classAttr == 'feelBtn')
						{
							window.location.href = data.url;
						}
						else
						{
							window.parent.location.href = data.url;
						}
					});
				}

			},'JSON');

			layer.close();
		});
	});
	
	$(document).on("click", "a[mini='delCrm']", function (e) 
	{
		e.preventDefault();

		var id =  $(this).attr('id');

		var classAttr = $(this).attr('class');

		layer.confirm("确定删除此菜单项？",{icon: 3, title:'提示',offset:'60px'},function()
		{
			$.post("/"+moduleName+"/menu/deleteCrm",{'menu_id':id},function(data)
			{
				if(data.status == 0)
				{
					layer.msg(data.msg,{icon:2,time:1000,offset:'60px'});
				}
				else
				{
					layer.msg(data.msg,{icon:1,time:1000,shift:0,offset:'60px'},function()
					{
						if(classAttr == 'feelBtn')
						{
							window.location.href = data.url;
						}
						else
						{
							window.parent.location.href = data.url;
						}
					});
				}

			},'JSON');

			layer.close();
		});
	});

	/* 新增一行 */
    var action_num = 0;

    $("#addMenu").click(function ()
    {
        action_num++;

        var html = '';

        html += '<tr id="menu_action_' + action_num + '">';

        html += '<td><input type="text" name="new[' + action_num + '][menu_name]" value="" placeholder="菜单名称"/></td>';

        html += '<td><input type="text" name="new[' + action_num + '][menu_action]" value="" placeholder="链接参数"/></td>';

        html += '<td><input type="text" name="new[' + action_num + '][orderby]" value="100" placeholder="排序值"/></td>';

        html += '<td> <select name="new[' + action_num + '][is_show]"> <option value="0">隐藏</option>  <option value="1">显示</option></select></td>';

        html += '<td> ' +
            '<select name="new[' + action_num + '][menu_level]"> ' +
            '<option value="2">二级菜单</option>' +
            '<option value="3">三级菜单</option>' +
            '<option value="4">四级菜单</option>' +
            '</select>' +
            '</td>';

        html += '<td><a href="javascript:" onclick="$(\'#menu_action_' + action_num + '\').remove();">移除</a></td></tr>';

        $("#noData").remove();

        $("#menuItem").append(html);
    });

    /* 时间控件 */
    layui.use(['laydate','element'], function()
    {
        var laydate = layui.laydate;

        var element = layui.element;

        var option = {elem:'#datetime', type:'date', range: '~', format: 'yyyy-MM-dd', trigger: 'click',
            btns: ['clear', 'confirm']};

        laydate.render(option);
    });
});
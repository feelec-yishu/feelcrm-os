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

var htmlText,len,form;

layui.use(['form'], function()
{
    form = layui.form;

	form.on('select(source)', function(data)
	{
		var source = data.value;

		len = $(data.elem).attr('id').replace(/[^0-9]/ig,"");

		var que = $(data.elem).attr('id').match(/(and|or|action)/g).toString();

		if(source)
		{
            var information = '';

			$.post("/Sla/index",{source:source},function(res)
			{
				var operator = "<div class='layui-input-inline'><select name='sla[conditions]["+que+"]["+len+"][operator]' lay-filter='operator' id='conditions_"+que+"_"+len+"_operator'>";

				var values = "<div class='layui-input-inline mr0'><select name='sla[conditions]["+que+"]["+len+"][value]' lay-filter='value' id='conditions_"+que+"_"+len+"_value'>";

				for(var i in res[0])
				{
					operator += "<option value="+res[0][i]['operator']+">"+res[0][i]['name']+"</option>";
				}

				for(var j in res[1])
				{
					values += "<option value="+res[1][j]['value']+">"+res[1][j]['name']+"</option>";
				}

				operator += "</select></div>";

				values += "</select></div>"+information;

				if(source === 'group_id')
				{
					values = "<div class='layui-input-inline mr0' id='conditions_"+que+"_"+len+"_value'></div>";
				}

				if(que === 'action')
				{
					htmlText = values;
				}
				else
				{
                   htmlText = operator + values;
				}

				$("#conditions_"+que+"_"+len+"_source_target").html(htmlText);

				if(source === 'group_id')
				{
					loadXmSelect('conditions_'+que+'_'+len+'_value',"sla[conditions]["+que+"]["+len+"][value]",res[1],true);
				}

				form.render();

			},'JSON')
		}
	});

	form.on('select(operator)', function(data)
	{
		if(data.value == 'changed' || data.value == 'not_changed')
		{
			$(data.elem).parent().next().hide();
		}
		else
		{
			$(data.elem).parent().next().show();
		}
	});
	
	$(document).on('click',".remove",function()
	{
		$(this).parents(".select-group").remove();
	});

	var loadXmSelect = function(id,name,data,isRadio,values)
	{
		xmSelect.render({
			el: '#'+id,
			name:name,
			tips:language.PLEASE_SELECT,
			model: { label: { type: 'text' } },
			radio: isRadio,
			clickClose: isRadio,
			filterable:true,
			theme: {
				color: '#2c6ee5'
			},
			searchTips:language.SEARCH_KEYWORD,
			tree: {
				show: true,
				strict: false,
				indent: 20,
				expandedKeys: true
			},
			height: '200px',
			initValue:values,
			prop: {
				name: 'name',
				value: 'value',
				children:'child'
			},
			data:data
		})
	};

	for(var a in and_multiple_data)
	{
		var and_data = and_multiple_data[a].data;

		var and_values = and_multiple_data[a].values;

		if(and_multiple_data[a].source === 'cc')
		{
			loadSelectM(and_multiple_data[a].id,and_multiple_data[a].name,and_data,and_values);
		}
		else if(and_multiple_data[a].source === 'group_id')
		{
			loadXmSelect(and_multiple_data[a].id,and_multiple_data[a].name,and_data,true,and_values);
		}
		else
		{
			loadXmSelect(and_multiple_data[a].id,and_multiple_data[a].name,and_data,false,and_values);
		}
	}

	for(var o in or_multiple_data)
	{
		var or_data = or_multiple_data[o].data;

		var or_values = or_multiple_data[o].values;

		if(or_multiple_data[o].source === 'cc')
		{
			loadSelectM(or_multiple_data[o].id,or_multiple_data[o].name,or_data,or_values);
		}
		else if(or_multiple_data[o].source === 'group_id')
		{
			loadXmSelect(or_multiple_data[o].id,or_multiple_data[o].name,or_data,true,or_values);
		}
		else
		{
			loadXmSelect(or_multiple_data[o].id,or_multiple_data[o].name,or_data,false,or_values);
		}
	}
});

function conditions_and_add()
{
	var type = "and";

	and_id++;

	$("#conditions_and_add").before(createConditionHtml(type,and_id));

	form.render();
}

function conditions_or_add()
{
	var type = "or";

	or_id++;

	$("#conditions_or_add").before(createConditionHtml(type,or_id));

	form.render();
}

function createConditionHtml(type,id)
{
    var first,option;

	if(type === 'action')
	{
		first = language.SELECT_ACTION;

		option = action_option;
	}
	else
	{
		first = language.SELECT_FIELDER;

		option = condition_option;
	}

    htmlText = "<div class='select-group clearfix' id='conditions_"+type+"_"+id+"'><div class='layui-input-inline clearfix'>"+

				"<select name='sla[conditions]["+type+"]["+id+"][source]' lay-filter='source' id='conditions_"+type+"_"+id+"_source'>"+

				"<option value=''>"+first+"</option>"+option+

				"</select></div><span id='conditions_"+type+"_"+id+"_source_target'></span>"+

				"<a class='remove iconfont icon-jianqu2' href='javascript:;'></a></div>";

	return htmlText;
}

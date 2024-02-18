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

layui.config({
	base : '/Public/js/layui/extends/'
}).extend({
	selectM: 'selectM'
}).use(['form','selectM'], function()
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

            if(source === 'email_user' || source === 'email_customer')
			{
				information = "<div class='clear'></div><br/><div class='action-notice action_email_"+len+"'><div class='layui-form-item'>"+

					"<label class='layui-form-label'><span class='red'>* </span>"+language.MAIL_TITLE+"</label>"+

					"<div class='layui-input-block'><input type='text' name='triggers[conditions][action]["+len+"][subject]' placeholder='"+language.MAIL_TITLE+"' class='layui-input'></div></div>"+

					"<div class='layui-form-item layui-form-text'><label class='layui-form-label'><span class='red'>* </span>"+language.MAIL_CONTENT+"</label>"+

					"<div class='layui-input-block'><textarea name='triggers[conditions][action]["+len+"][body]' placeholder='"+language.ENTER_CONTENT+"' class='layui-textarea'></textarea></div></div>"+

					"<a href='javascript:' onclick='show_trigger_tag()' class='email-tag'>"+language.VIEW_TAGS+"</a></div></div>";
			}

			if(source === 'message_user' || source === 'message_customer')
			{
				information = "<div class='clear'></div><br/><div class='action-notice action_message_"+len+"'><div class='layui-form-item'>"+

					"<label class='layui-form-label'><span class='red'>* </span>"+language.MSG_TITLE+"</label>"+

					"<div class='layui-input-block'><input type='text' name='triggers[conditions][action]["+len+"][msg_title]' placeholder='"+language.MSG_TITLE+"' class='layui-input'></div></div>"+

					"<div class='layui-form-item layui-form-text'><label class='layui-form-label'><span class='red'>* </span>"+language.MSG_CONTENT+"</label>"+

					"<div class='layui-input-block'><textarea name='triggers[conditions][action]["+len+"][body]' placeholder='"+language.ENTER_CONTENT+"' class='layui-textarea'></textarea></div></div></div>";
			}

			$.post("/Triggers/index",{source:source},function(res)
			{
				var operator = "<div class='layui-input-inline'><select name='triggers[conditions]["+que+"]["+len+"][operator]' lay-filter='operator' id='conditions_"+que+"_"+len+"_operator'>";

				var values = '';

				if(source === 'cc')
				{
					values = "<div class='layui-input-inline mr0' id='multiple_"+que+"_"+len+"'><select name='triggers[conditions]["+que+"]["+len+"][value]' lay-filter='value' id='conditions_"+que+"_"+len+"_value'>";
				}
				//部分select加搜索
				else if($.inArray(source,['ticket_model_id','member_id','recipient_id','dispose_id']) >= 0)
				{
					values = "<div class='layui-input-inline mr0'>" +
						"<select name='triggers[conditions]["+que+"]["+len+"][value]' lay-filter='value' id='conditions_"+que+"_"+len+"_value' lay-search>" +
						"<option value=''>"+language.PLEASE_SELECT+" ("+language.SEARCH_KEYWORD+")</option>";
				}
				else
				{
					values = "<div class='layui-input-inline mr0'><select name='triggers[conditions]["+que+"]["+len+"][value]' lay-filter='value' id='conditions_"+que+"_"+len+"_value'>";
				}

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

				if(source === 'group_id' || source === 'publisher_group_id' || source === 'handler_group_id' || source === 'cc_group_id')
				{
					values = "<div class='layui-input-inline mr0' id='multiple_"+que+"_"+len+"'></div>";
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

				if(source === 'cc')
				{
					loadSelectM('multiple_'+que+'_'+len,"triggers[conditions]["+que+"]["+len+"][value]",res[1]);
				}

				if(source === 'group_id')
				{
					loadXmSelect('multiple_'+que+'_'+len,"triggers[conditions]["+que+"]["+len+"][value]",res[1],true);
				}

				if(source === 'publisher_group_id' || source === 'handler_group_id' || source === 'cc_group_id')
				{
					loadXmSelect('multiple_'+que+'_'+len,"triggers[conditions]["+que+"]["+len+"][value]",res[1],false);
				}

				form.render();

			},'JSON')
		}
	});

	form.on('select(operator)', function(data)
	{
		if(data.value === 'changed' || data.value === 'not_changed')
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

	var loadSelectM = function (id,name,data,values)
	{
		layui.selectM(
		{
			elem: '#'+id,
			data: data,
			selected: values,
			tips:language.PLEASE_SELECT,
			width:'60%',
			name:name,
			max:100,
			search:true,
			searchTips:language.SEARCH_KEYWORD,
			field: {idName:'value',titleName:'name',statusName:'status'}
		});
	};

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

	for(var ac in action_multiple_data)
	{
		var ac_data = action_multiple_data[ac].data;

		var ac_values = action_multiple_data[ac].values;

		if(action_multiple_data[ac].source === 'group_id')
		{
			loadXmSelect(action_multiple_data[ac].id,action_multiple_data[ac].name,ac_data,true,ac_values);
		}
		else
		{
			loadXmSelect(action_multiple_data[ac].id,action_multiple_data[ac].name,ac_data,false,ac_values);
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

function conditions_action_add()
{
	var type = "action";

	action_id++;

	$("#conditions_action_add").before(createConditionHtml(type,action_id));

	form.render();
}

function createConditionHtml(type,id)
{
    var first,option;

	if(type == 'action')
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

				"<select name='triggers[conditions]["+type+"]["+id+"][source]' lay-filter='source' id='conditions_"+type+"_"+id+"_source'>"+

				"<option value=''>"+first+"</option>"+option+

				"</select></div><span id='conditions_"+type+"_"+id+"_source_target'></span>"+

				"<a class='remove iconfont icon-jianqu2' href='javascript:;'></a></div>";

	return htmlText;
}

function show_trigger_tag()
{
	var triggerLabelBox = "<div class='trigger-label'>"+
		"<div class='label-header'>"+language.TRIGGER_NOTE+"<span class='iconfont icon-close2 close'></span></div>" +
		"<div class='label-item'>" +
		"<div>"+language.TRIGGER_NOTE1+"</div>" +
		"<ul>" +
		"<li><i class='iconfont icon-iddenglufanbai'></i><span>"+language.TRIGGER_NOTE2+"：</span>{{ticket.id}}</li>" +
		"<li><i class='iconfont icon-biaoti'></i><span>"+language.TRIGGER_NOTE3+"：</span>{{ticket.title}}</li>" +
		"<li><i class='iconfont icon-yuanzhuangtai'></i><span>"+language.TRIGGER_NOTE4+"：</span>{{ticket.oldStatus}}</li>" +
		"<li><i class='iconfont icon-xinzhuangtai'></i><span>"+language.TRIGGER_NOTE5+"：</span>{{ticket.newStatus}}</li>" +
		"</ul>" +
		"</div>" +
		"</div>";

	var index = layer.open(
	{
	    title:false,
        skin: 'trigger-layer',
		type: 1,
		content: triggerLabelBox,
        area: ['600px'],
		offset: ['25%', '35%'],
		shade: [0.3, '#393D49'],
        closeBtn:false,
		shadeClose:true,
        success: function(layero, index)
        {
            $('.close').on('click',function()
            {
                layer.close(index);
            });
        }
	});
}

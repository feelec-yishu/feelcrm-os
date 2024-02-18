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

    // 增加审核场景
	var scenesHtml =
		"<div class='select-group clearfix'>" +
			"<div class='layui-input-inline clearfix'>" +
			"<select name='rule[scenes][]'>" +
			"<option value=''>"+language.SELECT_AUDIT_SCENARIO+"</option>" + scenesOption +
			"</select>" +
			"</div>" +
		"<a class='remove iconfont icon-jianqu2' href='javascript:'></a>" +
		"</div>";

	$('#add-scenes').on('click',function ()
	{
		$(this).before(scenesHtml);

		form.render();
	});

	// 增加审核对象
	var objectHtml =
		"<div class='select-group clearfix'>" +
		"<div class='layui-input-inline clearfix'>" +
		"<select name='rule[object][]'>" +
		"<option value=''>"+language.SELECT_AUDIT_OBJECT+"</option>" + objectOption +
		"</select>" +
		"</div>" +
		"<a class='remove-level iconfont icon-jianqu2' href='javascript:'></a>" +
		"</div>";

	$('#add-object').on('click',function ()
	{
		$(this).before(objectHtml);

		form.render();
	});

	// 增加审核层级
	$('#add-level').on('click',function ()
	{
		level_id++;

		var level = language.AUDIT_LEVEL.replace('level',level_id);

		var levelHtml =
			"<div class='select-group clearfix' id='level_"+level_id+"'>" +
				"<input type='hidden' name='process["+level_id+"][audit_level]' value='"+level_id+"' class='level'/>" +
				// "<span class='audit-level'>第"+convertToChinese(level_id)+"级审核</span>" +
				"<span class='audit-level'>"+level+"</span>" +
				"<div class='layui-input-inline clearfix'>" +
					"<select name='process["+level_id+"][audit_type]' lay-filter='audit_type'>" + levelOption + "</select>" +
				"</div>" +
				"<a href='javascript:' class='add-reviewer hide'>+ "+language.ADD_REVIEWER+"</a>" +
				// "<div class='clear'></div>" +
				"<a class='remove-level iconfont icon-jianqu2' href='javascript:'></a>" +
				"<ul class='reviewers hide'></ul>" +
			"</div>";

		$(this).prev('.select-group').find('.remove-level').remove();

		$(this).before(levelHtml);

		form.render('select');
	});

	form.on('select(type)', function(data)
	{
		var type = data.value;

		len = $(data.elem).attr('id').replace(/[^0-9]/ig,"");

		if(type)
		{
			$.post("/Audit/rule",{type:type},function(result)
			{
				var values = "<div class='layui-input-inline'><select name='rule[condition][value][]' lay-filter='operator'>";

				for(var i in result)
				{
					values += "<option value="+result[i]['value']+">"+result[i]['name']+"</option>";
				}

				values += "</select></div>";

				$("#conditions_value_"+len).html(values);

				form.render();

			},'JSON')
		}
	});

	$(document).on('click',".remove",function()
	{
		$(this).parents(".select-group").remove();
	});

	//审核流程 - 层级类型
	form.on('select(audit_type)',function (data)
	{
		var select = $(data.elem).parents('.select-group');

		data.value = parseInt(data.value);

		if(data.value === 20 || data.value === 30)
		{
			select.find('.add-reviewer').removeClass('hide');
		}
		else
		{
			select.find('.add-reviewer').addClass('hide').nextAll('ul.reviewers').html('').addClass('hide');
		}
	});

	$(document).on('click',".remove-level",function()
	{
		var id = $(this).parents('.select-group').data('id');

		if(id !== undefined)
		{
			var delInput = "<input type='checkbox' name='del[]' value='"+id+"' class='hidden' checked='' lay-ignore=''>";

			$('#audit-level').append(delInput);
		}

		$(this).parents(".select-group").remove();

		if($('.select-group').length > 1)
		{
            $('.select-group:last').append("<a class='remove-level iconfont icon-jianqu2' href='javascript:'></a>");
        }

		level_id--;
	});

//	 选择审核人
	$(document).on('click','.add-reviewer',function ()
	{
		var o = $(this);

		layer.open(
		{
			type: 1,
			title:"选择审核人",
			content: $('#processPanel'),
			skin: 'process-window',
			offset: ['150px'],
			area: ['50%', '500px'],
			success: function (layero, index)
			{
				var reviewers = o.nextAll('ul.reviewers');

				var reviewerIds = [];

				$.each(reviewers.find('li'),function()
				{
					var id = $(this).find('input').val();

					reviewerIds.push(id);
				});

				var processUserItem = $('#processUserItem');

//				 初始化已选择的审核人
				processUserItem.find('li').each(function()
				{
					var reviewer_id = $(this).data('id');

					if($.inArray(reviewer_id.toString(),reviewerIds) >= 0)
					{
						$(this).addClass('user-selected');
					}
					else
					{
						$(this).removeClass('user-selected');
					}
				});

				var reviewerHtml = reviewers.html();

				processUserItem.find('li').unbind('click').on('click',function()
				{
					$(this).toggleClass('user-selected');

					var name = $(this).find('span').html();

					var reviewer_id = $(this).data('id');

					var level = o.prevAll('.level').val();

					var reviewerLi = '<li>' +
						'<span>'+name+'</span>' +
						'<input type="checkbox" name="process['+level+'][reviewer][]" value="'+reviewer_id+'" class="hide" checked="" lay-ignore="">' +
						'</li>';

					if($(this).hasClass('user-selected'))
					{
						reviewerHtml += reviewerLi;
					}
					else
					{
						reviewers.find('li').each(function (k,v)
						{
							var id = $(v).find('input').val();

							if(parseInt(id) === reviewer_id)
							{
								reviewerLi = '<li>'+$(v).find("input[value='"+reviewer_id+"']").parent('li').html()+'</li>';
							}
						});

						reviewerHtml = reviewerHtml.replace(reviewerLi,'');
					}

					console.log(reviewerHtml);
				});

//				 筛选
				$('#processUserSearch').keyup(function ()
				{
					var value = $(this).val();

					if(value)
					{
						$('#processUserItem').find('li').addClass('user-hidden').filter(":contains('" + ($(this).val()) + "')").removeClass('user-hidden');
					}
					else
					{
						$('#processUserItem').find('li').removeClass('user-hidden');
					}
				});

				$('#sureSelectProcess').unbind('click').on('click',function()
				{
					if(reviewerHtml)
					{
						reviewers.removeClass('hide').html(reviewerHtml);
					}
					else
					{
						reviewers.addClass('hide').html('');
					}

					layer.close(index);
				});
			},
			cancel: function(index)
			{
				layer.close(index);

				return false;
			}
		});
	})
});

function addAuditCondition(o)
{
	condition_id++;

	o.before(createConditionHtml(condition_id));

	form.render();
}

function createConditionHtml(id)
{
    var first,option;

	first = language.SELECT_AUDIT_CONDITION;

	option = condition;

    htmlText = "<div class='select-group clearfix' id='conditions_type_"+id+"'><div class='layui-input-inline clearfix'>"+

				"<select name='rule[condition][type][]' lay-filter='type' id='conditions_type_"+id+"'>"+

				"<option value=''>"+first+"</option>"+option+

				"</select></div><span id='conditions_value_"+id+"'></span>"+

				"<a class='remove iconfont icon-jianqu2' href='javascript:;'></a></div>";

	return htmlText;
}


/* 数字转中文 */

var chnNumChar = ["零","一","二","三","四","五","六","七","八","九"];

var chnUnitSection = ["","万","亿","万亿","亿亿"];

var chnUnitChar = ["","十","百","千"];

function convertToChinese(num)
{
	var unitPos = 0;
	var strIns = '', chnStr = '';
	var needZero = false;

	if(num === 0){
		return chnNumChar[0];
	}

	while(num > 0)
	{
		var section = num % 10000;
		if(needZero){
			chnStr = chnNumChar[0] + chnStr;
		}
		strIns = SectionToChinese(section);
		strIns += (section !== 0) ? chnUnitSection[unitPos] : chnUnitSection[0];
		chnStr = strIns + chnStr;
		needZero = (section < 1000) && (section > 0);
		num = Math.floor(num / 10000);
		unitPos++;
	}

	return chnStr === '一十' ? '十' : chnStr;
}


/**
* @return {string}
*/
function SectionToChinese(section)
{
	var strIns = '', chnStr = '';
	var unitPos = 0;
	var zero = true;
	while(section > 0){
		var v = section % 10;
		if(v === 0){
			if(!zero){
				zero = true;
				chnStr = chnNumChar[v] + chnStr;
			}
		}else{
			zero = false;
			strIns = chnNumChar[v];
			strIns += chnUnitChar[unitPos];
			chnStr = strIns + chnStr;
		}
		unitPos++;
		section = Math.floor(section / 10);
	}

	return chnStr;
}

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

$(function()
{
//  新工单推送 - 我处理的工单，仅支持处理人推送
	if(action === 'disposeTicket')
	{
		top.socket.on('new_ticket', function (data)
		{
			updateTicketHtml(data,'new_ticket');
		});
	}

//    新工单推送 - 待回复的工单
	if(action === 'waitReplyTicket')
	{
		top.socket.on('new_wait_reply', function (data)
		{
			updateTicketHtml(data,'new_wait_reply');
		});
	}
	else
	{
		// 更新最新回复数 - 待回复工单页面不监听最新回复数
		top.socket.on('new_reply', function (data)
		{
			data = JSON.parse(data);

			updateNewReplyNumber(data);
		});
	}
});

//在列表中增加新工单
function updateTicketHtml(data,type)
{
	data = JSON.parse(data);

	//当前页面是待回复工单时，如果工单已存在于页面中则更新工单最新回复数
	if(type === 'new_wait_reply' && $("tr[data-value='"+data.ticket_no+"']").length > 0)
	{
		updateNewReplyNumber(data);
	}
	//在列表中增加一行新工单
	else
	{
		var minimalistDiv = $("#minimalist");

		minimalistDiv.find('.minimalist-main').children().first().before(data.minimalist);

		minimalistDiv.find('.nodata').remove();

		$('tbody>:first').before(data.list);

		$('tr.nodata').remove();

		layui.use('form',function(){layui.form.render();});
	}
}

//更新列表中的最新回复数
function updateNewReplyNumber(data)
{
	var newReplyNum = $("#newReplyNum"+data.ticket_no);

	newReplyNum.css({'font-size':'14px','color':'red',"font-weight":"600"});

	var new_reply_num = parseInt(newReplyNum.text());

	if(data.new_reply_num)
	{
		new_reply_num = new_reply_num + data.new_reply_num;
	}
	else
	{
		new_reply_num++;
	}

	newReplyNum.html(new_reply_num);
}


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
var slideVerify;

$(function ()
{
	layui.use('form',function ()
	{
		var form = layui.form;
	});

	$("#login-form").find('input').keydown(function(e)
	{
		if(e.keyCode === 13) login();
	});

	$(".language").hover(function()
	{
		$('.lang-panel').show();
	},
	function()
	{
		$('.lang-panel').hide();
	});

	slideVerify = new window.slideVerifyPlug('#slide-verify',
	{
		wrapWidth:'100%',
		initText:language.SLIDE_VERIFY,
		sucessText:language.VERIFY_PASS
	});

	$('#reg-submit').on('click',function ()
	{
		var loading = layer.load(2,{offset:'15vw'});

		var verify = slideVerify.slideFinishState;

		if(false === verify)
		{
			feelDeskAlert(language.SLIDE_VERIFY);

			layer.close(loading);

			return false;
		}

		$.post('/u-reg-submit',$('#reg-form').serialize(),function (result)
		{
			if(result.status !== 2)
			{
				feelDeskAlert(result.msg);
			}
			else
			{
				result.url = '/u-login';

				feelDeskAlert(result.msg,result);
			}

			layer.close(loading);
		});
	});

	$('#reset-submit').on('click',function ()
	{
		var loading = layer.load(2,{offset:'15vw'});

		var verify = slideVerify.slideFinishState;

		if(false === verify)
		{
			feelDeskAlert(language.SLIDE_VERIFY);

			layer.close(loading);

			return false;
		}

		$.post('/u-reset-submit',$('#reset-form').serialize(),function (result)
		{
			if(result.status !== 2)
			{
				feelDeskAlert(result.msg);

				layer.close(loading);
			}
			else
			{
				window.location.href = result.url;
			}
		});
	});
});

var login = function ()
{
	var loading;

	if(window.isclient !== 'true')
	{
		var verify = slideVerify.slideFinishState;

		if(false === verify)
		{
			layer.tips(language.SLIDE_VERIFY,'#slide-verify',{tips:3,time: 5000, skin:'login-tips'});

			return false;
		}

		loading = layer.load(2,{offset:'15vw'});

		$.post("/u-log-in",$('#login-form').serialize(),function(data)
		{
			if(data.status !== 2)
			{
				layer.close(loading);

				layer.tips(data.msg,'#'+data.id,{tips:3,time: 2000, skin:'login-tips'});

				slideVerify.resetVerify();
			}
			else
			{
				window.location.reload();
			}
		},'JSON');
	}
	else
	{
		loading = layer.load(2,{offset:'60vw'});

		$.post("/u-log-in",$('#client-login-form').serialize(),function(data)
		{
			if(data.status !== 2)
			{
				layer.close(loading);

				layer.tips(data.msg,'#'+data.id,{tips:3,time: 2000000, skin:'client-login-tips'});
			}
			else
			{
				if($('#client-remember').is(':checked') === true)
				{
					var name = $('#client-username').val();

					var pass = $('#client-password').val();

					chatcloud.saveAccount('Feelec-'+name,'name',name);

					chatcloud.saveAccount('Feelec-'+name,'pass',pass);
				}

				//
				// chatcloud.getAccountList(['name'])
				//
				//
				// chatcloud.getAccountInfo('Feelec-',['name','pass'])

				window.location.href='/u-home?pc=ide';
			}
		},'JSON');
	}


};

var getVerifyCode = function (source)
{
	var loading = layer.load(2,{offset:'15vw'});

	var verify = slideVerify.slideFinishState;

	if(false === verify)
	{
		feelDeskAlert(language.SLIDE_VERIFY);

		layer.close(loading);

		return false;
	}

	var param = {username:$("#username").find('input').val()};

	var url = '/u-reg-code';

	if(source === 'reset')
	{
		param.way = $("input[name='way").val();

		url = '/u-reset-code';
	}

	$.post(url,param,function(result)
	{
		if(result.status !== 2)
		{
			feelDeskAlert(result.msg);
		}
		else
		{
			setCookie(countdown_name,60,60);

			countDownTime('code',countdown_name);

			feelDeskAlert(result.msg);

			slideVerify.resetVerify();
		}

		layer.close(loading);

	},'JSON')
};


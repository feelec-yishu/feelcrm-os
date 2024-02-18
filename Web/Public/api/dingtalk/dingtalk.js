// config信息验证后会执行ready方法
dd.ready(function(res)
{
    dd.runtime.permission.requestAuthCode(
        {
            corpId: _config.corpId,

            onSuccess: function(result)
            {
//              获取用户信息并登录
                $.post(_config.login,{'code':result.code},function (data)
                {
                    if(data.errcode == 0)
                    {
                        window.location.href = data.url;
                    }
                    else
                    {
                        alert(JSON.stringify(data));
                    }
                },'JSON')
            },
            onFail : function(err)
            {
                alert(JSON.stringify(err));
            }
        })
});

// config信息验证失败会执行error函数
dd.error(function(error)
{
    alert(JSON.stringify(error));
});
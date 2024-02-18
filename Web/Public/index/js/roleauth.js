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
layui.config({base: '/Public/js/layui/extends/'}).extend({authtree: 'authtree/authtree'}).use(['form','authtree'],function()
{
    var loading;

    var form = layui.form;

    var tree = layui.authtree;

    function getFeelMenus(id,menu,name)
    {
        loading = layer.load(2,{offset:'15vw'});

        tree.render('#'+id, menu,
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

    form.on('submit',function(data)
    {
        loading = layer.load(2,{offset:'15vw'});

        var role_id = data.elem.getAttribute('data-id');

        $.post("/Role/auth.html?role_id="+role_id,$('form').serialize(),function(data)
        {
            if(data.status == 0)
            {
                feelDeskAlert(data.msg);
            }
            else
            {
                feelDeskAlert(data.msg,data);
            }

            layer.close(loading);

        },'JSON');
    })
});


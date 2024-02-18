<?php

/**
 * 菜单权限的URL校验
 * @param string $action URL表达式，格式：'[分组/模块/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param array  $params 参数 数组形式
 * @param string $title  标题
 * @param string $style  A标签样式
 * @param string $load   是否异步加载
 * @return string
 */
 
//返回状态数量
function getStatusNum($status_id)
{
    return M('ticket')->where(['status_id'=>$status_id,'company_id'=>session('company_id')])->count();
}


//检测是否为整数
function isInteger($value)
{
    return  is_numeric($value) && is_int($value+0);
}


/**
 * 方法二: 获取指定日期段内每一天的日期
 * @date 2017-02-23 14:50:29
 *
 * @param $start
 * @param $end
 *
 * @return array
 */
function getDateRange($start, $end)
{
    $range = [];

    for ($i = 0; strtotime($start . '+' . $i . ' days') <= strtotime($end); $i++)
    {
        $time = strtotime($start . '+' . $i . ' days');
        $range[] = date('Y-m-d', $time);
    }

    return $range;
}

//将二位数组中某个键值组装为一位数组
function getOneArray($arr,$field)
{
	foreach($arr as $v)
	{	
		$data_arr[] .= $v[$field];
	}
	
	if(count($data_arr) > 0)
	{
		return $data_arr;
	}
	else
	{
		return false;
	}
}

function getCrmDetailList($type,$get_id,$company_id,$formName=[])
{
    $data = [];

    $form = getCrmDbModel('define_form')->where(['company_id'=>$company_id,'closed'=>0,'type'=>$type,'form_name'=>['in',$formName]])->field('form_id,form_name')->select();

    foreach($form as $k1=>&$v1)
    {
        $form_content= getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,$type.'_id'=>$get_id,'form_id'=>$v1['form_id']])->getField('form_content');

        $data[$v1['form_name']] = $form_content;
    }

    return $data;
}
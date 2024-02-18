<?php
/**
 * 无限级分类菜单
 * @param array  $list	要转换的数据集
 * @param string $pk	主键
 * @param string $pid	父标记字段
 * @param string $child	子标记字段
 * @param int    $star	star标记字段
 * @return array
 */
function getCategoryTree($list =[], $pk='category_id',$pid = 'parent_id',$child = 'children',$star=0)
{
    // 创建Tree
    $tree = array();

    if(is_array($list))
    {
        // 创建基于主键的数组引用
        $menu = array();

        foreach ($list as $k => $v)
        {
            $menu[$v[$pk]] = &$list[$k];
        }

        foreach ($list as $k => $v)
        {
            // 判断是否存在parent
            $parentId = $v[$pid];

            if ($star == $parentId)
            {
                $tree[] = &$list[$k];
            }
            else
            {
                if (isset($menu[$parentId]))
                {
                    $parent = &$menu[$parentId];

                    $parent[$child][] = &$list[$k];
                }
            }
        }
    }

    return $tree;
}


function getHoursAndMinutes($time)
{
    if($time['hours'] > 0 && $time['minutes'] > 0)
    {
        $str = $time['hours'].'h '.$time['minutes'].'m';
    }
    else if($time['hours'] > 0 && !$time['minutes'])
    {
        $str = $time['hours'].'h';
    }
    else if($time['minutes'] > 0 && !$time['hours'])
    {
        $str = $time['minutes'].'m';
    }
    else
    {
        $str = 0;
    }

    return $str;
}


/*
* 通过相差的秒数获取工单处理时长，最大单位小时，最小单位分钟，不足一分钟按一分钟算
*/
function getTicketProcessTimeBySeconds($time)
{
    if(is_numeric($time))
    {
        $value = ["hours" => 0, "minutes" => 0];

        if($time >= 3600)
        {
            $value["hours"] = floor($time/3600);
            $time = ($time%3600);
        }

        if($time >= 60)
        {
            $value["minutes"] = floor($time/60);

            $time = ($time%60);
        }

        if(floor($time) > 0)
        {
            $value["minutes"] += 1;
        }

        return $value;
    }
    else
    {
        return 0;
    }
}


/**
 * 获取CRM客户信息
 * @param string $type
 * @param string $get_id
 * @param int $company_id
 * @param array $formName
 * @return array
 */

/*CRM自定义字段查重*/
function isUniqueData($type,$company_id,$form_id,$form_content)
{
	$data = getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,'form_id'=>$form_id,'form_content'=>$form_content])->find();
	
	$info = getCrmDbModel($type)->where(['company_id'=>$company_id,$type.'_id'=>$data[$type.'_id'],'isvalid'=>1])->find();
	
	if($info)
	{
		return $data;
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



function isLoginTimeout($login_time,$setTime)
{
	session('[start]');

	$loginTimes = intval((NOW_TIME - $login_time)/60);

	if($loginTimes > (int) $setTime)
	{
		session('index',null);

		header('Location: ' . U('Index/login/index'));

		exit();
	}
}


function getPercentage($total = 0,$num = 0)
{
    if($num == 0)
    {
        return '0%';
    }
    else
    {
        return round(($num/$total)*100).'%';
    }
}


function getViewName()
{
    $url = CONTROLLER_NAME.'/'.ACTION_NAME;

    $viewName = D('Menu')->getNameByLang('menu_name');

    $view_name = M('menu')->where(['menu_action'=>$url,'apply'=>10])->getField("{$viewName}");

    return $view_name;
}


function getRecordContent($str = '')
{
    $contentArr = explode('<br/>',$str);

    $content = '';

    foreach($contentArr as $k=>$v)
    {
        if(!$v)
        {
            unset($contentArr[$k]);

            continue;
        }
        else
        {
            $style = 'color:#333;';

            if(mb_substr($v, 0, 4,'utf-8') == '对话开始')
            {
                $style = 'color:#999;font-size:14px;';

                $v = str_replace($v,"<div style='margin-bottom: 10px;'>$v</div>",$v);
            }
            else if(mb_substr($v, 0, 4,'utf-8') == '对话结束')
            {
                $style = 'color:#999;font-size:14px;';

                $v = str_replace($v,"<div style='margin-bottom: 10px;'>$v</div>",$v);
            }
            else if($k+1 == count($contentArr))
            {
                $style = 'color:red;font-size:14px;';

                $v = str_replace($v,"<div style='margin-top: 10px;'>对话结束原因：{$v}</div>",$v);
            }
            else
            {
                $role = mb_substr($v, 2, 1,'utf-8');

                if($role == '>') $v = str_replace('>',"<span style='margin-right: 10px;'></span>",$v);

                $role = mb_substr($v, 0, 2,'utf-8');

                if($role == '系统')
                {
                    $style = 'color:#999;';

                    $v = str_replace($role,"<span style='font-weight: bold'>{$role}</span>",$v);
                }
                else if($role == '客服')
                {
                    $style = 'color:#3b8ed3;';

                    $v = str_replace($role,"<span style='font-weight: bold'>{$role}</span>",$v);
                }
                else if($role == '访客')
                {
                    $style = 'color:#42b475;';

                    $v = str_replace($role,"<span style='font-weight: bold'>{$role}</span>",$v);
                }
                else
                {
                    $v = str_replace($v,"<div style='margin-bottom: 10px;'>$v</div>",$v);
                }
            }

            $content .= "<div style='padding:0 15px 0 30px;font-size:14px;".$style."'>{$v}</div>";
        }
    }

    return $content;
}


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


//获取group名称组成的字符串
function getMemberGroupName($groups,$memberGroupIds)
{
	$groupName = [];

	foreach($groups as $v)
	{
		if(in_array($v['group_id'],explode(',',$memberGroupIds)))
		{
			$groupName[] = $v['group_name'];
		}
	}

	return implode('、',$groupName);
}
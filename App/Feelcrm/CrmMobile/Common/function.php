<?php
/**
 * Created by PhpStorm.
 * User: navyy
 * Date: 2018.11.20
 * Time: 17:52
 */
 
function getFirstStr($str)
{
	return strtoupper(mb_substr($str,0,1,'utf-8'));
}


/**
 * 获取CRM客户信息
 * @param string $type
 * @param string $get_id
 * @param int $company_id
 * @param array $formName
 * @return array
 */
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
            $style = 'color:#666;';

            if(mb_substr($v, 0, 4,'utf-8') == '对话开始')
            {
                $style = 'color:#999;font-size:14px;';

                $v = str_replace('对话开始 >>','',$v);

                $v = str_replace($v,"<div style='padding-top:2vh;text-align:center;color:#9ba3b6;font-size: 4vw'>—— $v ——</div>",$v);
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
                    $style = 'color:#666;font-size:3.5vw;margin:3vh 0 1.5vh 0';

                    $v = str_replace($role,"<span style='font-weight: bold'>{$role}</span>",$v);
                }
                else if($role == '客服')
                {
                    $style = 'color:#3b8ed3;font-size:3.5vw;margin:3vh 0 1.5vh 0';

                    $v = str_replace($role,"<span style='font-weight: bold'>{$role}</span>",$v);
                }
                else if($role == '访客')
                {
                    $style = 'color:#42b475;font-size:3.5vw;margin:3vh 0 1.5vh 0';

                    $v = str_replace($role,"<span style='font-weight: bold'>{$role}</span>",$v);
                }
                else
                {
                    $v = str_replace($v,"<div style='margin-bottom: 10px;'>$v</div>",$v);
                }
            }

            $content .= "<div style='padding:0 4vw;font-size:3.8vw;".$style."'>{$v}</div>";
        }
    }

    return $content;
}
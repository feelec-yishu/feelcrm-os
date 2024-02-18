<?php

use Crypto\CryptMessage;

header("content-type:text/html;charset=utf-8");

//读取本地文件
function get_file($filename)
{
	if(file_exists($filename))
	{
		return trim(substr(file_get_contents($filename), 15));
	}
	else
	{
		return '{"expire_time":0}';
	}
}


//写入本地文件
function set_file($filename, $content)
{
	$fp = fopen($filename, "w");

	fwrite($fp, "<?php exit();?>" . $content);

	fclose($fp);
}


function is_odd($num)
{
	return (is_numeric($num)&($num&1));
}

//判断偶数，是返回TRUE，否返回FALSE
function is_even($num)
{
	return (is_numeric($num)&(!($num&1)));
}

function _get($name)
{
	$val = !empty($_GET[$name]) ? $_GET[$name] : null;

	return $val;
}


function getParentIds($data,$pk,$id,$level = 1)
{
	$function = __FUNCTION__;

	static $parentIds = [];

	foreach($data as $k => $v)
	{
		if ($v[$pk] == $id)
		{
			$v['level'] = $level;

			if($v['parent_id'] > 0)
			{
				$parentIds[] = $v['parent_id'];
			}

			unset($data[$k]);

			$function($data,$pk,$v['parent_id'],$level+1);
		}
	}

	sort($parentIds);

	return $parentIds;
}


function signature($param,$secret)
{
	ksort($param);

	$sign = '';

	foreach($param as $k=>$p)
	{
		$sign .= $k.'='.$p.'&';
	}

	$sign = rtrim($sign,'&');

	$str = mb_convert_encoding($sign,'UTF-8');

	if (function_exists('hash_hmac'))
	{
		$sign = base64_encode(hash_hmac("sha1", $str, $secret, true));
	}

	return $sign;
}


function convertToChinese($num)
{
	if($num < 100)
	{
		$char = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
		$unit = ['', '十', '百', '千', '万'];
		$return = '';
		if ($num < 10)
		{
			$return = $char[$num];
		}
		elseif ($num%10 == 0)
		{
			$firstNum = substr($num, 0, 1);
			if ($num != 10) $return .= $char[$firstNum];
			$return .= $unit[strlen($num) - 1];
		}
		elseif ($num < 20)
		{
			$return = $unit[substr($num, 0, -1)]. $char[substr($num, -1)];
		}
		else
		{
			$numData = str_split($num);

			$numLength = count($numData) - 1;

			foreach ($numData as $k => $v)
			{
				if ($k == $numLength) continue;

				$return .= $char[$v];

				if ($v != 0) $return .= $unit[$numLength - $k];
			}

			$return .= $char[substr($num, -1)];
		}

		return $return;
	}
}


//创建目录
function mkdirs($dir, $mode = 0777)
{
	if (is_dir($dir) || @mkdir($dir,$mode)) return true;

	if (!mkdirs(dirname($dir),$mode)) return false;

	return @mkdir($dir,$mode);
}


function getSubjectTree($list =[], $pk='menu_id',$pid = 'parent_id',$child = 'childMenu',$star=0)
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


function fetchAll($data,$field)
{
    $datas = [];

    foreach($data as $v)
    {
        $datas[$v[$field]] = $v;
    }

    return $datas;
}


function getFeelChatSignature()
{
    [$msec, $sec] = explode(' ', microtime());

    $timestamp = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

    $data = ['appId'=>'b054014693241bcd9c20','timestamp'=>$timestamp];

    ksort($data);

    $sign = '';

    foreach($data as $k=>$p)
    {
        $sign .= $k.'='.$p.'&';
    }

    $sign = rtrim($sign,'&');

    $str = mb_convert_encoding($sign,'UTF-8');

    if (function_exists('hash_hmac'))
    {
        $appSecret = '44166c9e7acafe44a320';

        $data['signature'] = base64_encode(hash_hmac("sha1", $str, $appSecret, true));
    }

    return $data;
}


//获取工作时间和非工作时间的处理时长
function check_work_time($stime,$etime)
{
    $work = 0;//工作时间

    $nwork = 0;//非工作时间

    $day = floor((($etime - $stime)/3600)/24);//整天数

    if($day > 0)
    {
        $work = 8 * 3600 * $day;

        $nwork = 16 * 3600 * $day;
    }

    $second = ($etime - $stime) - ($day * 24 * 3600);//计算非整天数秒数

    if(date("H",$stime) < 9){//开始时间0-9

        $t_9 = (strtotime(date("Y-m-d",$stime)." 9:00:00") - $stime);

        $t_18 = (strtotime(date("Y-m-d",$stime)." 18:00:00") - $stime);

        if($second<=$t_9)
        {
            $work = 0 + $work;

            $nwork = $second + $nwork;
        }
        else if($second>$t_9 && $second<=$t_18)
        {
            $work = $second - $t_9 + $work;

            $nwork = $t_9 + $nwork;
        }
        else
        {
            $work = 8*3600 + $work;

            $nwork = $second - 8*3600 + $nwork;
        }

    }elseif(date("H",$stime)>=9 && date("H",$stime)<18){//开始时间9-18

        $t_18 = (strtotime(date("Y-m-d",$stime)." 18:00:00") - $stime);

        if($second<=$t_18)
        {
            $work = $second + $work;

            $nwork = 0 + $nwork;
        }
        else
        {
            $work = $t_18 + $work;

            $nwork = $second - $t_18 + $nwork;
        }
    }
    else
    {//开始时间18-24

        $work = 0 + $work;

        $nwork = $second + $nwork;
    }

    return ['work'=>check_date($work),"nwork"=>check_date($nwork)];
}


function check_date($time)
{
	$days = floor($time/86400);

	$days = $days ? sprintf("%02d", $days) : 0;

	//计算小时数
	$time = $time%86400;

	$hours = floor($time/3600);

	$hours = $hours ? sprintf("%02d", $hours) : 0;

	//计算分钟数
	$time = $time%3600;

	$mins = floor($time/60);

	$mins = $mins ? sprintf("%02d", $mins) : 0;

	//计算秒数
	$secs = $time%60;

	$secs = sprintf("%02d", $secs);

    return $days."天".$hours."小时".$mins.'分'.$secs."秒";
}


function changeKeyToId($data,$field)
{
    $array = [];

    foreach($data as $v)
    {
        $array[$v[$field]] = $v;
    }

    return $array;
}

// 储存UUID
function saveFeelDeskEncodeId($company_id = 0,$id = 0,$source = '')
{
	$chars = 'F'.sha1(md5(uniqid(md5(microtime(true)).$source.$id.$company_id,true)).rand(0,9));

	$uuid  = substr($chars,0,8);

	$uuid .= substr($chars,8,4);

	$uuid .= substr($chars,12,4);

	$uuid .= substr($chars,16,4);

	$uuid .= substr($chars,20,12);

	$table = [
		'Member'         => ['member','member_id','employee_id'],
		'Group'          => ['group','group_id','department_id'],
		'Role'           => ['role','role_id','part_id'],
		'Firm'           => ['firm','firm_id','enterprise_id'],
		'FirmDepartment' => ['firm_department','id','uuid'],
		'FirmRole'       => ['firm_role','role_id','uuid'],
	];

	M($table[$source][0])->where([$table[$source][1]=>$id])->setField([$table[$source][2]=>$uuid]);

	return $uuid;
}

// 储存加密客户ID
function saveCustomerEmployeeId($customer_id=0,$company_id=0)
{
    $employeeId = sha1(md5(microtime().'FeelCrm'.$customer_id.$company_id).rand(0,9));

    getCrmDbModel('Customer')->where(['customer_id'=>$customer_id])->setField(['employeeId'=>$employeeId]);

    return $employeeId;
}

function saveFeelCRMEncodeId($id = 0,$company_id = 0,$source = 'Customer')
{
	$chars = 'F'.sha1(md5(uniqid(md5(microtime(true)).$source.$id.$company_id,true)).rand(0,9));

	$uuid  = substr($chars,0,8);

	$uuid .= substr($chars,8,4);

	$uuid .= substr($chars,12,4);

	$uuid .= substr($chars,16,4);

	$uuid .= substr($chars,20,12);

	$table = [
		'Customer'         => ['customer','customer_id','employeeId'],
		'Clue'          => ['clue','clue_id','employee_id'],
	];

	getCrmDbModel($table[$source][0])->where([$table[$source][1]=>$id])->setField([$table[$source][2]=>$uuid]);

	return $uuid;
}

function FeelDeskCurl($url,$method = 'GET',$params = '',$is_json = false,$header = [])
{
    $curl = curl_init(); // 启动一个CURL会话

    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在

    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转

    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer

    curl_setopt($curl, CURLOPT_REFERER, $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']); // 设置Referer

	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

	if($header)
	{
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	}

	if($is_json)
	{
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($params)));
	}

    if($method == 'GET')
    {
        curl_setopt($curl, CURLOPT_HTTPGET, 1); // 发送一个常规的GET请求
    }
    else
    {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");//HTTP 请求时，使用自定义的 Method 来代替"GET"或"HEAD"。对 "DELETE" 或者其他更隐蔽的 HTTP 请求有用。 有效值如 "GET"，"POST"，"CONNECT"等等；

        //设置提交的信息
        curl_setopt($curl, CURLOPT_POSTFIELDS,$params);//全部数据使用HTTP协议中的 "POST" 操作来发送。
    }

    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环

    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

    $result = curl_exec($curl); // 执行操作

	$result = json_decode($result, true);

	$result['http_code'] = curl_getinfo($curl,CURLINFO_HTTP_CODE);//最后一个收到的HTTP代码

	$result['last_url'] = curl_getinfo($curl,CURLINFO_EFFECTIVE_URL);//最后一个有效的URL地址

	$error = curl_error($curl);

	if ($error != '')
	{
		$result['curl']['code'] = curl_errno($curl);

		$result['curl']['msg'] = $error;
	}
	else
	{
		$result['errcode'] = 0;

		$result['msg'] = 'ok';
	}

	curl_close($curl); // 关闭CURL会话

    return $result;
}


//获取毫秒级时间戳
function getMicrotime()
{
    [$msec, $sec] = explode(' ', microtime());

    $microtime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

    return $microtime;
}


//毫秒时间戳转时间戳
function getMsecToMescdate($timestamp)
{
    $timestamp = $timestamp * 0.001;

    if(strstr($timestamp,'.'))
    {
        sprintf("%01.3f",$timestamp);
        [$usec, $sec] = explode(".",$timestamp);
        $sec = str_pad($sec,3,"0",STR_PAD_RIGHT);
    }
    else
    {
        $usec = $timestamp;

        $sec = "000";
    }

    return $usec;
}


function getTreeHtml($data,$parent_id,$pk,$level = 1,$value=0)
{
    $html = $style = '';

    foreach($data as $k => $v)
    {
	    if(getTreeChild($data,$v['directory_id']))
	    {
		    $i = "<i class='layui-icon layui-icon-triangle-r'></i> <i class='iconfont icon-dir-close'></i>";
	    }
	    else
	    {
		    $i = "<i class='iconfont icon-dir-close color-ffe792'></i>";
	    }

        if($value == $v[$pk])
        {
            $current = 'current';
        }
        else
        {
            $current = '';
        }

        if($v['parent_id'] == $parent_id)
        {
            $html .= "<li class='category-level{$level}'>";

            $html .= "<div class='category-name ".$current."' data-value=".encrypt($v[$pk],'directoryId').">{$i}{$v['lang_name']}</div>";

            $html .= getTreeHtml($data,$v[$pk],$pk,$level+1);

            $html = $html."</li>";
        }
    }

    return $html ? '<ul>'.$html.'</ul>' : $html ;
}


function getTreeChild($data,$id)
{
    foreach($data as $v)
    {
        if($v['parent_id'] == $id)
        {
            return true;
        }
    }
}


function getTreeParentData($data,$parent_id,$pk)
{
    $parentIds = [];

    foreach($data as $k => $v)
    {
        if($v['parent_id'] == $parent_id)
        {
            $parentIds[] = $v[$pk];

            $parentIds = array_merge($parentIds,getTreeParentData($data,$v[$pk],$pk));
        }
    }

    return $parentIds;
}



/*
 * tree数据
 * @param array     $data       数据源
 * @param int       $parent_id  父级ID
 * $param int       $pk         主键
 * $param string    $icon       图标
 * @param bool      $open       是否展开节点
 * @return array    $tree
 */
function getTreeData($data,$parent_id,$pk,$open = false)
{
    $tree = [];

    foreach($data as $k => $v)
    {
        if($v['parent_id'] == $parent_id)
        {
            $v['children'] = getTreeData($data,$v[$pk],$pk,$open);

            $v['open'] = $open;

            $tree[] = $v;
        }
    }

    return $tree;
}

/* 检查角色菜单权限 */
function checkRoleMenuAuth($where = [],$pk='',$system = '')
{
    if($system == 'crm')
    {
        $Menu = getCrmDbModel('Menu');
    }
    else
    {
        $Menu = D('Menu');
    }

    $result = $Menu->where($where)->getField($pk);

    return $result;
}


// 判断奇偶数
function judgeParity($num)
{
    return ($num%2) ? 1 : 2;
}


function curl($url)
{
    $curl = curl_init();

    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);

    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 1);

    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    //执行命令
    $data = curl_exec($curl);

    //关闭URL请求
    curl_close($curl);

    return $data;
}


function getCrmDbModel($table)
{
	return M($table,'feel_','CRM_DB_CONFIG');
}

function getCrmLanguageData($field)
{
	$lang = cookie('think_language');

	if($lang == 'en-us') $field = 'name_en as '.$field;

	if($lang == 'ja-jp') $field = 'name_jp as '.$field;

	return $field;
}


function getCrmLastSql($table)
{
    $data = M($table,'feel_','CRM_DB_CONFIG')->getlastsql();

    return $data;
}


/**
 * 判断用户是否在线
 * @param int $last_active_time 最后活动时间
 * @return string
 */
function checkUserLoginStatus($last_active_time)
{
//    有效时间
    $effective = time() - 5*60;

    if($last_active_time > $effective)
    {
        return true;
    }

    return false;
}


function encrypt($tex, $key = 'yzpencode', $expire = 0)
{
    $chrArr = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z','A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z','0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

    $tex.="~#~" . sprintf('%010d', $expire ? $expire + time() : 0) . "~#~";

    $key_b = $chrArr[rand() % 62] . $chrArr[rand() % 62] . $chrArr[rand() % 62] . $chrArr[rand() % 62] . $chrArr[rand() % 62] . $chrArr[rand() % 62];

    $rand_key = $key_b . $key;

    $rand_key = md5($rand_key);

    $texlen = strlen($tex);

    $reslutstr = "";

    for ($i = 0; $i < $texlen; $i++)
    {
        $reslutstr.=$tex{$i} ^ $rand_key{$i % 32};
    }

    $reslutstr = trim($key_b . base64_encode($reslutstr), "==");

    $reslutstr = substr(md5($reslutstr), 0, 8) . $reslutstr;

    return $reslutstr;
}


function decrypt($tex, $key = 'yzpencode') {
    if (strlen($tex) < 14)
        return false;
    $verity_str = substr($tex, 0, 8);
    $tex = substr($tex, 8);
    if ($verity_str != substr(md5($tex), 0, 8)) {
        //完整性验证失败
        return false;
    }
    $key_b = substr($tex, 0, 6);
    $rand_key = $key_b . $key;
    $rand_key = md5($rand_key);
    $tex = base64_decode(substr($tex, 6));
    $texlen = strlen($tex);
    $reslutstr = "";
    for ($i = 0; $i < $texlen; $i++) {
        $reslutstr.=$tex{$i} ^ $rand_key{$i % 32};
    }
    $expiry_arr = array();
    preg_match('/^(.*)~#~(\d{10})~#~$/', $reslutstr, $expiry_arr);
    if (count($expiry_arr) != 3) {
        //过期时间完整性验证失败
        return false;
    } else {
        $tex_time = $expiry_arr[2];
        if ($tex_time > 0 && $tex_time - time() <= 0) {
            //验证码过期
            return false;
        } else {
            $reslutstr = $expiry_arr[1];
        }
    }
    return $reslutstr;
}


function getHostDomain()
{
    $url  = $_SERVER['HTTP_HOST'];

    $data = explode('.', $url);

    $co_ta = count($data);

    //判断是否是双后缀
    $zi_tow = true;

    $host_cn = 'com.cn,net.cn,org.cn,gov.cn';

    $host_cn = explode(',', $host_cn);

    foreach($host_cn as $host)
    {
        if(strpos($url,$host))
        {
            $zi_tow = false;
        }
    }

    //如果是返回FALSE ，如果不是返回true
    if($zi_tow == true)
    {
        $host = $data[$co_ta-2].'.'.$data[$co_ta-1];
    }
    else
    {
        $host = $data[$co_ta-3].'.'.$data[$co_ta-2].'.'.$data[$co_ta-1];
    }

    return $host;
}


/**
 * 判断是否是通过手机访问
 */
function isMobileClient()
{
//    如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    }

//    如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA']))
    {
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }

//    判断手机发送的会员端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords =['nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp',
            'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu',
            'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi',
            'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'];

//        从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        }
    }

//    协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT']))
    {
//        如果只支持wml并且不支持html那一定是移动设备
//        如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }

    return false;
}



function getStrToLower($str = '')
{
    return strtolower($str);
}


/*
* 根据组件时间检测工单是否超时,计算超时时间
* @param int   $dead_time      工单完成期限
* @param int   $end_time       工单完成时间
* @param int   $count          是否计算超时时间
* @return string
*/
function checkTicketIsTimeout($dead_time = 0,$end_time = 0,$count = false)
{
//    如果当前时间 > 完成期限并且工单未完成那么工单已超时
    if($dead_time > 0 && time() > $dead_time && $end_time == 0)
    {
        if($count)
        {
            $timeout = getTakingTime(time(),$dead_time);

            $timeStr = '';

            if($timeout['day'] > 0) $timeStr .= $timeout['day'].L('DAYS');

            if($timeout['hour'] > 0) $timeStr .= $timeout['hour'].L('HOURS');

            if($timeout['min'] > 0) $timeStr .= $timeout['min'].L('MINUTES');

            return "<span class='orange4'>{$timeStr}</span>";
        }

        return "<span class='red'>".L('YES')."</span>";
    }
    else if($dead_time > 0 && $end_time > $dead_time)//完成时间 > 完成期限 那么 工单已超时
    {
        if($count)
        {
            $timeout = getTakingTime($end_time,$dead_time);

            $timeStr = '';

            if($timeout['day'] > 0) $timeStr .= $timeout['day'].L('DAYS');

            if($timeout['hour'] > 0) $timeStr .= $timeout['hour'].L('HOURS');

            if($timeout['min'] > 0) $timeStr .= $timeout['min'].L('MINUTES');

            return "<span class='orange4'>{$timeStr}</span>";
        }

        return "<span class='red'>".L('YES')."</span>";
    }
    else
    {
        if($count == 1) return 0;

        return L('NO');
    }
}


function checkTicketIsTimeoutByCron($dead_time = 0,$end_time = 0)
{
//    如果完成期限已设置 并且 当前时间 > 完成期限并且工单未完成 或者 完成时间 > 完成期限 那么工单已超时
    if($dead_time > 0 && ((time() > $dead_time && $end_time == 0) || $end_time > $dead_time))
    {
        return true;
    }
    else
    {
        return false;
    }
}


/*
* 检测工单是否超时,计算超时时间
* @param int   $ticket_id      工单ID
* @param int   $create_time    工单创建时间
* @param int   $end_time       工单完成时间
* @param int   $count          是否计算超时时间
* @param int   $company_id     公司ID，适用于API
* @return string
*/
function checkIsTimeout($ticket_id,$create_time = 0,$end_time = 0,$count = 0,$company_id = 0)
{
//	获取工单停止时长
	$stopTiming = M('stop_timing')->where(['ticket_id'=>$ticket_id])->select();

	$stop_timing_sec = $start_time = 0;

	foreach($stopTiming as $st)
	{
		$close_time = $st['close_time'] ? $st['close_time'] : NOW_TIME;

		$stop_timing_sec += $close_time - $st['start_time'];
	}

	$company_id = $company_id ? $company_id : session('company_id');

    $overtime = M('ticket_config')->where(['company_id'=>$company_id])->getField('overtime');

//    超时上限
	$timeUpper = intval($create_time + $overtime * 3600);

	$time = $end_time > 0 ? $end_time : NOW_TIME;

//    计算超时秒数
	$timeout_sec = $time - $timeUpper - $stop_timing_sec;

	if($timeout_sec <= 0)
	{
		if($count == 1)
		{
			return 0;
		}
		else
		{
			return L('NO');
		}
	}
	else
	{
		if($count)
		{
			$timeout = getTakingTimeBySec($timeout_sec);

			$timeStr = '';

			if($timeout['day'] > 0) $timeStr .= $timeout['day'].L('DAYS').' ';

			if($timeout['hour'] > 0) $timeStr .= $timeout['hour'].L('HOURS').' ';

			if($timeout['min'] > 0) $timeStr .= $timeout['min'].L('MINUTES');

			return "<span class='orange4'>{$timeStr}</span>";
		}
		else
		{

			return "<span class='red'>".L('YES')."</span>";
		}
	}
}


/*
* 检测工单是否超时,计算超时时间
* @param int   $ticket_id      工单ID
* @param int   $create_time    工单创建时间
* @param int   $end_time       工单完成时间
* @param int   $source         是否计算超时时间
* @param int   $company_id     公司ID，适用于超时通知
* @return string
*/
function checkTicketIsTimeoutByGlobal($ticket_id = 0,$create_time = 0,$end_time = 0,$company_id = 0)
{
	//	获取工单停止时长
	$stopTiming = M('stop_timing')->where(['ticket_id'=>$ticket_id])->select();

	$stop_timing_sec = 0;

	foreach($stopTiming as $st)
	{
		$close_time = $st['close_time'] ? $st['close_time'] : NOW_TIME;

		$stop_timing_sec += $close_time - $st['start_time'];
	}

    $company_id = $company_id > 0 ? $company_id : session('company_id');

    $overtime = M('ticket_config')->where(['company_id'=>$company_id])->getField('overtime');

//    超时上限
    $timeUpper = intval($create_time + $overtime * 3600);

    $time = $end_time > 0 ? $end_time : NOW_TIME;

//    计算超时秒数
	$timeout_sec = $time - $timeUpper - $stop_timing_sec;

//    如果已完成用完成时间计算，未完成用当前时间计算
    if($timeout_sec > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}


/*
* 判断是否是微信端
* @return bool
*/
function isWeChatTerminal()
{
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false )
    {
        return true;
    }

    return false;
}

//转换文件单位
function getFileSize($bit)
{
    $type = ['B','KB','M','G','T'];

    for($i = 0; $bit >= 1024; $i++)//单位每增大1024，则单位数组向后移动一位表示相应的单位
    {
        $bit/=1024;
    }

    return (floor($bit*100)/100).$type[$i];//floor是取整函数，为了防止出现一串的小数，这里取了两位小数
}


//检查语言版本权限
function checkLangAuth()
{
    $lang = cookie('think_language');

    $langAuth = M('company')->where(["company_id"=>session('company_id')])->field('en_auth,jp_auth')->find();

    if(($lang == 'en-us' && $langAuth['en_auth'] != 10) || ($lang== 'ja-jp' && $langAuth['jp_auth'] != 10))
    {
        cookie('think_language','zh-cn',3600*24*365);

        return false;
    }
    else
    {
        return strtolower($lang);
    }
}



//获取毫秒级时间戳
function msecTime()
{
    [$msec, $sec] = explode(' ', microtime());

    return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
}



function getNonce()
{
    $str = '123456789';

    $nonce = "";

    for($i=0;$i<10;$i++)
    {
        $nonce .= $str{mt_rand(0,8)};    //生成php随机数
    }

    if(strlen($nonce) != 10)
    {
        return getNonce();
    }
    else
    {
        return $nonce;
    }
}


/*
* 通模块名称获取对应域名
* @param $modul 模块名称
* @return string
*/

function getHostUrl($module = '')
{
    $hosts = C('APP_SUB_DOMAIN_RULES');

    $url = C('HTTP_LINK');

    $arr = parse_url($url);

    $file = $arr['host'];

    $ext = substr($file,strpos($file,".")+1);

    $host = array_search($module,$hosts).'.'.$ext;

	if(isset($arr['port']) && $arr['port'])
	{
		$host = $host.':'.$arr['port'];
	}

    return $host;
}


/*
* 检查菜单是否存在
* @return bool
*/
function checkMenuExist()
{
    $menuAction = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);

    $mid = D('Menu')->where(['menu_action'=>$menuAction])->getField('menu_id');

    return $mid;
}

/*
* 检查账号是否可用
* @param id 账号所属的公司ID
*/
function checkAccountAuth($id)
{
    $count = M('company_audit')->where(['company_id'=>$id, 'open_time'=>['lt',time()],'due_time'=>['gt',time()], 'activity'=>2])->count();

    if($count > 0)
    {
        cookie('acp',\Think\Crypt\Driver\Crypt::encrypt('pass','ACCOUNT_AUTH'),'expire=3600*1');
    }

    return $count;
}



/**
* 获取真实IP
* @return string 访客IP
*/
function Getip()
{
	if(!empty($_SERVER["HTTP_CLIENT_IP"]))
	{
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	}

	// 获取代理ip
	if(! empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	}

	if($ip)
	{
		$ips = array_unshift($ips, $ip);
	}

	$count = count($ips);

	for($i = 0; $i < $count; $i ++)
	{
		// 排除局域网ip
		if(! preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i]))
		{
			$ip = $ips[$i];

			break;
		}
	}

	$tip = empty($_SERVER['REMOTE_ADDR']) ? $ip : $_SERVER['REMOTE_ADDR'];

	return $tip;
}



/**
* 七牛图片处理
* @param string		原图路径
* @param string		宽x高
* @return string	处理完毕的图片路径
*/
function getQiniuImage($image, $size)
{
	$imageMogr2 = "?imageMogr2/auto-orient/thumbnail/".$size."!/format/png/blur/1x0/quality/100|imageslim";

	return $image.$imageMogr2;
}



/**
* 获取七牛图片名称
* @param string		原图路径
* @return string	图片名称
*/
function getQiniuImageName($url,$image)
{
//	针对本地空间
	if(strpos($image,'Attach'))
	{
		$url = C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN');
	}

	$imageName = str_replace($url,'',$image);

	return $imageName;
}



function getBannerName($image)
{
	$domain = M('qiniu')->where(['company_id'=>session('company_id')])->getField('domain');

	if($domain)
	{
		$imageName = str_replace("http://".$domain."/",'',$image);
	}
	else
	{
		$domain = C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN');

		$imageName = str_replace($domain,'',$image);
	}

	return $imageName;
}



/**
* 获取七牛文件名称
* @param    string  $url    	原图路径
* @return   string  $file   	图片名称
*/
function getQiniuFileName($url,$file)
{
    if(strpos($file,$url) !== false)
    {
        $fileName = str_replace($url,'',$file);
    }
    else
    {
        $fileName = str_replace(C('HTTP_PROTOCOL')."://".C('HOST_DOMAIN').'/','./',$file);
    }

	return $fileName;
}


/**
* 生成公司token
* @param string	$cid	公司ID
* @param string	$key	私有密匙(用于解密和加密)
* @return string		处理后的token
*/
function getLoginToken($cid,$key)
{
	$token = encrypt($cid.$key,$key);

	$company_id = M('company')->where(['login_token'=>$token])->getField('company_id');

	if($company_id > 0)
	{
		getLoginToken($cid,$key);
	}
	else
	{
		$token = str_replace(['+','/','='],['f','e','e'],$token);

		return  $token;
	}
}



/**
 * 获取高亮
 * @param string $str	需要高亮的字符串
 * @param string $text	原字符串
 * @return string
 */
function getHighLight($str,$text)
{
	return str_replace($str,"<span style='color: #138df5'>".$str."</span>",$text);
}



/**
 * 获取数组长度
 * @param string $array 需要计算的数组
 * @return string
 */
function getCount($array = array())
{
	return count($array);
}



/**
 * 转换实体
 * @param string $html 需要转换的html字符串
 * @return string
 */
function getHtml($html)
{
	return html_entity_decode($html);
}



function getHtmlDecode($html)
{
    return html_entity_decode($html);
}


function clearHtmlTag($html)
{
    return html_entity_decode($html);
}


/**
 * 格式化输出数组
 * @param array $array 需要格式化的数组
 * @return array
 */
function getPrint($array)
{
	echo '<pre>';print_r($array);echo '</pre>';
}



/**
 * 判断输入的字符串是否是一个合法的字段名（以小写字母开头，只能包含字母或字母加下划线）
 * @param string $string 需要验证的字段名
 * @return boolean
 */
function isFormField($string)
{
    if(preg_match('/^[a-z]([a-z]|_[a-z])*$/', $string))
	{
		return true;
	}

    return false;
}



/**
 * 判断输入的字符串是否是一个合法的QQ
 * @param string $string
 * @return boolean
 */
function isQQ($string)
{
    if(ctype_digit($string))
	{
        $len = strlen($string);

        if($len < 5 || $len > 14)
		{
			return false;
		}

        return true;
    }
	else
	{
		return false;
	}
}



/**
 * 判断输入的字符串是否是一个合法的电话号码（仅限中国大陆）
 * @param string $string
 * @return boolean
 */
function isPhone($string)
{
    if (preg_match('/^[0,4]\d{2,3}-\d{7,8}$/', $string))
	{
		return true;
	}

    return false;
}

/**
 * 判断输入的字符串是否是一个合法的座机号(仅限中国大陆)
 * @param string $string
 * @return boolean
 */
function isTel($string)
{
	if (preg_match('/^([0-9]{3,4}-)?[0-9]{7,8}$/', $string))
	{
		return true;
	}

	return false;
}

/**
* 判断输入的字符串是否是一个合法的手机号(仅限中国大陆)
* @param string $string
* @return boolean
*/
function isMobile($string)
{
    if (preg_match('/^[1]+[3,4,5,6,7,8,9]+\d{9}$/', $string))
	{
		 return true;
	}

    return false;
}

/**
 * 判断输入的字符串是否是一个合法的手机号(世界)
 * @param string $string
 * @return boolean
 */
function isWorldMobile($string)
{
	//菲律宾
	if (preg_match('/^(\\+?0?63\\-?)?\\d{10}$/', $string))
	{
		return true;
	}
	//泰国
	if (preg_match('/^(\\+?0?66\\-?)?\\d{10}$/', $string))
	{
		return true;
	}
	//新加坡
	if (preg_match('/^(\\+?0?65\\-?)?\\d{10}$/', $string))
	{
		return true;
	}
	//阿尔及利亚
	if (preg_match('/^(\+?213|0)(5|6|7)\d{8}$/', $string))
	{
		return true;
	}
	//叙利亚
	if (preg_match('/^(!?(\+?963)|0)?9\d{8}$/', $string))
	{
		return true;
	}
	//沙特阿拉伯
	if (preg_match('/^(!?(\+?966)|0)?5\d{8}$/', $string))
	{
		return true;
	}
	//美国
	if (preg_match('/^(\+?1)?[2-9]\d{2}[2-9](?!11)\d{6}$/', $string))
	{
		return true;
	}
	//捷克共和国
	if (preg_match('/^(\+?420)? ?[1-9][0-9]{2} ?[0-9]{3} ?[0-9]{3}$/', $string))
	{
		return true;
	}
	//德国
	if (preg_match('/^(\+?49[ \.\-])?([\(]{1}[0-9]{1,6}[\)])?([0-9 \.\-\/]{3,20})((x|ext|extension)[ ]?[0-9]{1,4})?$/', $string))
	{
		return true;
	}
	//丹麦
	if (preg_match('/^(\+?45)?(\d{8})$/', $string))
	{
		return true;
	}
	//希腊
	if (preg_match('/^(\+?30)?(69\d{8})$/', $string))
	{
		return true;
	}
	//澳大利亚
	if (preg_match('/^(\+?61|0)4\d{8}$/', $string))
	{
		return true;
	}
	//英国
	if (preg_match('/^(\+?44|0)7\d{9}$/', $string))
	{
		return true;
	}
	//香港
	if (preg_match('/^(\+?852\-?)?[569]\d{3}\-?\d{4}$/', $string))
	{
		return true;
	}
	//印度
	if (preg_match('/^(\+?91|0)?[789]\d{9}$/', $string))
	{
		return true;
	}
	//新西兰
	if (preg_match('/^(\+?64|0)2\d{7,9}$/', $string))
	{
		return true;
	}
	//南非
	if (preg_match('/^(\+?27|0)\d{9}$/', $string))
	{
		return true;
	}
	//赞比亚
	if (preg_match('/^(\+?26)?09[567]\d{7}$/', $string))
	{
		return true;
	}
	//西班牙
	if (preg_match('/^(\+?34)?(6\d{1}|7[1234])\d{7}$/', $string))
	{
		return true;
	}
	//芬兰
	if (preg_match('/^(\+?358|0)\s?(4(0|1|2|4|5)?|50)\s?(\d\s?){4,8}\d$/', $string))
	{
		return true;
	}
	//法国
	if (preg_match('/^(\+?33|0)[67]\d{8}$/', $string))
	{
		return true;
	}
	//以色列
	if (preg_match('/^(\+972|0)([23489]|5[0248]|77)[1-9]\d{6}/', $string))
	{
		return true;
	}
	//匈牙利
	if (preg_match('/^(\+?36)(20|30|70)\d{7}$/', $string))
	{
		return true;
	}
	//意大利
	if (preg_match('/^(\+?39)?\s?3\d{2} ?\d{6,7}$/', $string))
	{
		return true;
	}
	//日本
	if (preg_match('/^(\+?81|0)\d{1,4}[ \-]?\d{1,4}[ \-]?\d{4}$/', $string))
	{
		return true;
	}
	//马来西亚
	if (preg_match('/^(\+?6?01){1}(([145]{1}(\-|\s)?\d{7,8})|([236789]{1}(\s|\-)?\d{7}))$/', $string))
	{
		return true;
	}
	//挪威
	if (preg_match('/^(\+?47)?[49]\d{7}$/', $string))
	{
		return true;
	}
	//比利时
	if (preg_match('/^(\+?32|0)4?\d{8}$/', $string))
	{
		return true;
	}
	//挪威
	if (preg_match('/^(\+?47)?[49]\d{7}$/', $string))
	{
		return true;
	}
	//波兰
	if (preg_match('/^(\+?48)? ?[5-8]\d ?\d{3} ?\d{2} ?\d{2}$/', $string))
	{
		return true;
	}
	//巴西
	if (preg_match('/^(\+?55|0)\-?[1-9]{2}\-?[2-9]{1}\d{3,4}\-?\d{4}$/', $string))
	{
		return true;
	}
	//葡萄牙
	if (preg_match('/^(\+?351)?9[1236]\d{7}$/', $string))
	{
		return true;
	}
	//俄罗斯
	if (preg_match('/^(\+?7|8)?9\d{9}$/', $string))
	{
		return true;
	}
	//塞尔维亚
	if (preg_match('/^(\+3816|06)[- \d]{5,9}$/', $string))
	{
		return true;
	}
	//土耳其
	if (preg_match('/^(\+?90|0)?5\d{9}$/', $string))
	{
		return true;
	}
	//越南
	if (preg_match('/^(\+?84|0)?((1(2([0-9])|6([2-9])|88|99))|(9((?!5)[0-9])))([0-9]{7})$/', $string))
	{
		return true;
	}
	//台湾
	if (preg_match('/^(\+?886\-?|0)?9\d{8}$/', $string))
	{
		return true;
	}

	return false;
}

function isInternationalMobile($string)
{
    if(preg_match('/^(\\+\\d{2})(\d{9})$/', $string) || preg_match('/^(\d{7,11})$/', $string))
    {
        return true;
    }

    return false;
}



/**
 * 判断一个字符串是否是一个Email地址
 * @param string $string
 * @return boolean
 */
function isEmail($string)
{
    return (boolean) preg_match('/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i', $string);
}



/**
 * 判断一个用户名是否合法
 * @param string $string
 * @return boolean
 */
function isUname($string)
{
    return (boolean) preg_match('/^[\x{4e00}-\x{9fa5}\w\-]{2,16}$/u', $string);
}



/**
 * 判断密码格式是否合法（6-16位，仅允许数字、下划线、字母的组合）
 * @param string $string
 * @return boolean
 */
function isPassword($string)
{
    return (boolean) preg_match('/^[_0-9a-z]{6,16}$/i',$string);
}



/**
 * 检查是否为一个合法的时间格式
 * @access public
 * @param string  $time
 * @return void
 */
function isTime($time)
{
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';

    return preg_match($pattern, $time);
}



/**
 * 判断一个字符串是否是一个合法时间
 * @param string $string
 * @return boolean
 */
function isDate($string)
{
    if (preg_match('/^\d{4}-[0-9][0-9]-[0-9][0-9]$/', $string))
	{
        $date_info = explode('-', $string);

        return checkdate(ltrim($date_info[1], '0'), ltrim($date_info[2], '0'), $date_info[0]);
    }

    if (preg_match('/^\d{8}$/', $string))
	{
        return checkdate(ltrim(substr($string, 4, 2), '0'), ltrim(substr($string, 6, 2), '0'), substr($string, 0, 4));
    }

    return false;
}



/**
 * 判断一个字符串是否是一张合法的图片
 * @param string $fileName
 * @return boolean
 */
function isImage($fileName)
{
    $ext = explode('.', $fileName);

    $ext_seg_num = count($ext);

    if ($ext_seg_num <= 1)
	{
		return false;
	}

    $ext = strtolower($ext[$ext_seg_num - 1]);

    return in_array($ext, array('jpeg', 'jpg', 'png', 'gif'));
}

/**
 * 判断输入的字符串是否是一个合法的营业执照号
 * @param string $string
 * @return boolean
 */
function isBusinessLicense($string)
{
	if (preg_match('/(^(?:(?![IOZSV])[\dA-Z]){2}\d{6}(?:(?![IOZSV])[\dA-Z]){10}$)|(^\d{15}$)/', $string))
	{
		return true;
	}

	return false;
}

/**
 * 判断输入的字符串是否是一个合法的纳税号
 * @param string $string
 * @return boolean
 */
function isTaxNumber($string)
{
	if (preg_match('/^[\da-z]{10,15}$/i', $string))
	{
		return true;
	}
	if (preg_match('/^\d{6}[\da-z]{10,12}$/i', $string))
	{
		return true;
	}
	if (preg_match('/^[a-z]\d{6}[\da-z]{9,11}$/i', $string))
	{
		return true;
	}
	if (preg_match('/^[a-z]{2}\d{6}[\da-z]{8,10}$/i', $string))
	{
		return true;
	}
	if (preg_match('/^\d{14}[\dx][\da-z]{4,5}$/i', $string))
	{
		return true;
	}
	if (preg_match('/^\d{17}[\dx][\da-z]{1,2}$/i', $string))
	{
		return true;
	}
	if (preg_match('/^[a-z]\d{14}[\dx][\da-z]{3,4}$/i', $string))
	{
		return true;
	}
	if (preg_match('/^[a-z]\d{17}[\dx][\da-z]{0,1}$/i', $string))
	{
		return true;
	}
	if (preg_match('/^[\d]{6}[\da-z]{13,14}$/i', $string))
	{
		return true;
	}

	return false;
}

/**
 * 判断输入的字符串是否是一个合法的银行卡号
 * @param string $string
 * @return boolean
 */
function isBankCard($card_number){
	$arr_no = str_split($card_number);
	$last_n = $arr_no[count($arr_no)-1];
	krsort($arr_no);
	$i = 1;
	$total = 0;
	foreach ($arr_no as $n){
		if($i%2==0){
			$ix = $n*2;
			if($ix>=10){
				$nx = 1 + ($ix % 10);
				$total += $nx;
			}else{
				$total += $ix;
			}
		}else{
			$total += $n;
		}
		$i++;
	}
	$total -= $last_n;
	$x = 10 - ($total % 10);
	if($x == $last_n){
		return true;
	}else{
		return false;
	}
}

/**
 * 判断输入的字符串是否是一个合法的网址
 * @param string $string
 * @return boolean
 */
function isUrl($string)
{
	if (preg_match('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $string))
	{
		return true;
	}

	return false;
}

//专门给含有HTML的字段
function feel_msubstr($str, $start, $length, $suffix)
{
    $str = preg_replace("@<(.*?)>@is", "", $str);

    return msubstr($str, $start, $length, 'utf-8', $suffix);
}



/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = false)
{
    if (function_exists("mb_substr"))
	{
		$slice = mb_substr($str, $start, $length, $charset);
	}
    else if (function_exists('iconv_substr'))
	{
        $slice = iconv_substr($str, $start, $length, $charset);

        if (false === $slice)
		{
            $slice = '';
        }
    }
	else
	{
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";

        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";

        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";

        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

        preg_match_all($re[$charset], $str, $match);

        $slice = join("", array_slice($match[0], $start, $length));
    }

    return $suffix ? $slice . '...' : $slice;
}



//时间格式化
function formatTime($time)
{
    $rtime = date("m-d H:i", $time);

    $htime = date("H:i", $time);

    $time = time() - $time;

    if ($time < 60)
    {
        $str = '刚刚';
    }
    elseif ($time < 60 * 60)
    {
        $min = floor($time / 60);

        $str = $min . '分钟前';
    }
    elseif ($time < 60 * 60 * 24)
    {
        $h = floor($time / (60 * 60));

        $str = $h . '小时前 ';
    }
    elseif ($time < 60 * 60 * 24 * 3)
    {
        $d = floor($time / (60 * 60 * 24));

        if ($d == 1)
        {
            $str = '昨天 ' . date("H:i", $time);
        }
        else
        {
            $str = '前天 ' . date("H:i", $time);
        }
    }
    else
    {
        $str = $rtime;
    }

    return $str;
}


//时间格式化
function format($time)
{
    $t = NOW_TIME - $time;

    $mon = (int) ($t / (86400 * 30));

    if ($mon >= 1) {return '一个月前';}

    $day = (int) ($t / 86400);

    if ($day >= 1) {return $day . '天前';}

    $h = (int) ($t / 3600);

    if ($h >= 1) {return $h . '小时前';}

    $min = (int) ($t / 60);

    if ($min >= 1){return $min . '分前';}

    return '刚刚';
}



function getDates($time,$type = 1)
{
	if($time > 0)
	{
		if($type == 0)
		{
			return date('Y-m-d H:i:s',$time);
		}
		else if($type == 1)
		{
            return date('Y-m-d H:i',$time);
        }
        else if($type == 2)
        {
            return date('Y-m-d',$time);
        }
        else if($type == 3)
        {
            return date('Y-m-d H:00',$time);
        }
        else if($type == 4)
        {
            return date('H:i',$time);
        }
        else if($type == 5)
        {
            return date('Y-m-d 00:00',$time);
        }
        else if($type == 6)
        {
	        return date('Y-m-d H:i',$time);
        }
	}
	else
	{
		return "<span class='iconfont icon-nothing'></span>";
	}
}



/**
 * 无限级分类菜单
 * @param array     $list	要转换的数据集
 * @param string    $pk	主键
 * @param string    $pid	父标记字段
 * @param string    $child	子标记字段
 * @param int       $star	star标记字段
 * @param array     $checkedMenuIds 已有菜单ID，用于默认选中
 * @return array
 */
function getMenuTree($list =[], $pk='menu_id',$pid = 'parent_id',$child = 'childMenu',$star=0,$checkedMenuIds=[])
{
    // 创建Tree
    $tree = array();

    if(is_array($list))
	{
        // 创建基于主键的数组引用
        $menu = array();

        foreach ($list as $k => &$v)
		{
			if(in_array($v[$pk],$checkedMenuIds))
			{
				$v['checked'] = 'checked';
			}

            $menu[$v[$pk]] = &$list[$k];
        }

        foreach ($list as $k => &$v)
		{
            // 判断是否存在parent
            $parentId = $v[$pid];

            if ($star == $parentId)
			{
				if($checkedMenuIds)
				{
					$tree[] = &$list[$k];
				}
				else
				{
					$tree[$list[$k][$pk]] = &$list[$k];
				}
            }
			else
			{
                if (isset($menu[$parentId]))
				{
                    $parent = &$menu[$parentId];

					if($checkedMenuIds)
					{
						$parent[$child][] = &$list[$k];
					}
					else
					{
						$parent[$child][$list[$k][$pk]] = &$list[$k];
					}
                }
            }
        }
    }

    return $tree;
}



function delFileByDir($dir)
{
    $dh = opendir($dir);

    while ($file = readdir($dh))
	{
        if ($file != "." && $file != "..")
		{
            $fullpath = $dir . "/" . $file;

            if (is_dir($fullpath))
			{
                delFileByDir($fullpath);
            }
			else
			{
                unlink($fullpath);
			}
        }
    }

    closedir($dh);
}



/**

 * 大鱼短信发送函数

 * @param string $name    接收短信者称呼

 * @param string $code	  短信模板编码

 * @param string $mobile  短信接收人

 * @param string $title	  工单标题

 * @return boolean

 */
function sendSMS($code = '',$name = '',$mobile = '',$title = '')
{
	import('Org.Alidayu.top.TopClient');
	import('Org.Alidayu.top.ResultSet');
	import('Org.Alidayu.top.RequestCheckUtil');
	import('Org.Alidayu.top.TopLogger');
	import('Org.Alidayu.top.request.AlibabaAliqinFcSmsNumSendRequest');

	$sms = new \TopClient;

	$sms->appkey = '23513210';

	$sms->secretKey = '43c1745719702f06c849effb4dfeadc6';

	$sms->format = "json";//返回格式 xml,json

	$send = new \AlibabaAliqinFcSmsNumSendRequest;

	$send ->setExtend( "123456" );

	$send ->setSmsType( "normal" );//短信类型，传入值请填写normal

	$send ->setSmsFreeSignName( "FeelDesk" );//来源于配置短信签名 下面列表中有签名名称

	if($name)
	{
		$send ->setSmsParam( "{name:'{$name}'}" );
	}
	if($title)
	{
		$send ->setSmsParam( "{title:'{$title}'}" );
	}

	$send ->setRecNum($mobile); //手机号

	$send ->setSmsTemplateCode($code); //配置短信模板 列表中有模板id

	$resp = $sms ->execute($send);

	return json_decode(json_encode($resp),true);
}



/**

 * 大鱼短信发送记录查询函数

 * @param string $code	  短信发送流水 可选

 * @param string $mobile  短信接收人

 * @param string $title	  工单标题

 * @return boolean

 */
function getSmsRecord($mobile = '',$date = '',$p = 1)
{
	import('Org.Alidayu.top.TopClient');
	import('Org.Alidayu.top.ResultSet');
	import('Org.Alidayu.top.RequestCheckUtil');
	import('Org.Alidayu.top.TopLogger');
	import('Org.Alidayu.top.request.AlibabaAliqinFcSmsNumQueryRequest');

	$sms = new \TopClient;

	$sms->appkey = '23513210';

	$sms->secretKey = '43c1745719702f06c849effb4dfeadc6';

	$req = new AlibabaAliqinFcSmsNumQueryRequest;

	$req->setRecNum($mobile);//短信接收号码

	$req->setQueryDate($date);//短信发送日期，支持近30天记录查询

	$req->setCurrentPage("$p");//分页参数,页码

	$req->setPageSize("5");//分页参数，每页数量。最大值50

	$resp = $sms->execute($req);

	return json_decode(json_encode($resp),true);
}


/**
* 系统邮件发送函数
* @param string $address 接收邮件者邮箱
* @param string $name 接收邮件者名称
* @param string $subject 邮件主题
* @param string $body 邮件内容
* @param int $id 工单ID
* @param string $attachment 附件列表
* @param array $config
* @return boolean
* @throws phpmailerException
*/
function sendMail($address, $name, $subject = '', $body = '',$attachment = null, $config = array())
{
	if(!$config)
	{
		$config = M('Mail')->where(['company_id' => 0])->find();
	}

	vendor('PHPMailer.class#phpmailer');			// 从PHPMailer目录导class.phpmailer.php类文件

    $mail   = new PHPMailer();			// PHPMailer对象

    $mail->ClearAddresses();						// 清除发送队列

	$mail->IsSMTP();								// 设定使用SMTP服务

    $mail->CharSet    = 'UTF-8';					// 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码

	$mail->SMTPAuth   = true;						// 启用 SMTP 验证功能

    $mail->SMTPDebug  = false;							// 关闭SMTP调试功能1 = errors and messages2 = messages only

    // 使用安全协议,如果不填写，则下面的端口须为25
    if($config['send_port'] != 587)
    {
        $mail->SMTPSecure = 'ssl';
    }
    else
    {
        $mail->SMTPSecure = 'tls';
    }

    $mail->Host       = $config['smtp'];			// SMTP 服务器

    $mail->Port       = $config['send_port'];		// SMTP服务器的端口号

    $mail->Username   = $config['account'];	    	// SMTP服务器用户名

    $mail->Password   = htmlspecialchars_decode($config['password']); // SMTP服务器密码,htmlspecialchars_decode 放在&等字符串被转义

    $mail->SetFrom($config['from_email'], $config['from_name']);

    $replyEmail		= $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];

    $replyName		= $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];

    $mail->AddReplyTo($replyEmail, $replyName);

    $mail->Subject		= $subject;

    $mail->AltBody		= L('SEND_MAIL_ALT_BODY');

    $mail->MsgHTML($body);

    $mail->AddAddress($address,$name);

	// 添加附件
    if(is_array($attachment))
	{
        foreach ($attachment as $file)
		{
            is_file($file) && $mail->AddAttachment($file);
        }
    }

	return $mail->Send() ? 1 : $mail->ErrorInfo;
}



 //创建TOKEN
function createToken()
{
    $code = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));

    session('TOKEN', authcode($code));
}



//判断TOKEN
function checkToken($token)
{
    if ($token == session('TOKEN'))
	{
        session('TOKEN', NULL);

        return TRUE;
    }
	else
	{
        return FALSE;
    }
}



/* 加密TOKEN */
function authcode($str,$key = "FeelDesk")
{
    $str = substr(md5($str), 8, 10);

    return md5($key . $str);
}



function getAccessToken($cid,$key)
{
	$code = chr(mt_rand(0xB0, 0xF7)) . $cid . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));

	return  authcode($code,$key);
}



/**
 * 菜单权限的URL校验
 * @param string $action URL表达式，格式：'[分组/模块/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param array  $params 参数 数组形式
 * @param string $title  标题
 * @param string $style  A标签样式
 * @param string $load   是否异步加载
 * @return string
 */
function FEELDESK($action='',$params=[],$title='',$style='',$load='',$iconstyle='')
{
    $user = session('index');

    $class = $icon = '';

    $hasAuth = D('RoleAuth')->checkRoleAuthByMenu($user['company_id'],$action,$user['role_id']);

    if($user['is_first'] == 1)
    {
        if(!$hasAuth)
        {
            $action = 'javascript:';

            $title = L('UNAUTHORIZED');

            $class = ' style=display:none';
        }
        else if(!$action)
        {
            $action = 'javascript:';
        }
        else
        {
            if($style) $class = ' class="'.$style.'"';

            $action = U($action, $params);
        }
    }
    else
    {
        if($style) $class = ' class="'.$style.'"';

        if(!$action)
        {
            $action = 'javascript:;';
        }
        else
        {
            $action = U($action, $params);
        }
    }

    if($iconstyle) $icon = "class='{$iconstyle}'";

    if($load)
    {
	    $load = ' load="'.$load.'"';
    }
    else
    {
	    $load = ' load=loading';
    }

    $a_tag = '<a href="'.$action.'"'.$class.$load.' ><i '.$icon.'></i>'.$title.'</a>';

    return $a_tag;
}



/**
 * 时间差计算
 * @param string $begin_time 开始时间
 * @param string $end_time  结束时间
 * @return array
 */
function getTakingTime($begin_time,$end_time)
{
	if($begin_time < $end_time)
	{
		$starttime = $begin_time;

		$endtime = $end_time;
	}
	else
	{
		$starttime = $end_time;

		$endtime = $begin_time;
	}

	//计算天数
	$timediff = $endtime-$starttime;

	$days = intval($timediff/86400);

	$days = $days ? sprintf("%02d", $days) : 0;

	//计算小时数
	$remain = $timediff%86400;

	$hours = intval($remain/3600);

	$hours = $hours ? sprintf("%02d", $hours) : 0;

	//计算分钟数
	$remain = $remain%3600;

	$mins = intval($remain/60);

	$mins = $mins ? sprintf("%02d", $mins) : 0;

	//计算秒数
	$secs = $remain%60;

	$secs = sprintf("%02d", $secs);

	$res = ["day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs];

	return $res;
}


function getTakingTimeBySec($time)
{
	if(is_numeric($time))
	{
		$value = ['day'=>0,"hour" => 0, "min" => 0];

		if($time >= 86400)
		{
			$value["day"] = floor($time/86400);

			$time = ($time%86400);
		}

		if($time >= 3600)
		{
			$value["hour"] = floor($time/3600);

			$time = ($time%3600);
		}

		if($time >= 60)
		{
			$value["min"] = floor($time/60);

			$time = ($time%60);
		}

		if(floor($time) > 0)
		{
			$value["min"] += 1;
		}

		return $value;
	}
	else
	{
		return 0;
	}
}


/**
 * 去除文件后缀
 * @param string $filename 完整文件名
 * @return string
 */
function getFileName($filename)
{
    // $name = explode('.',$filename);

    return substr($filename,0,strrpos($filename, '.'));
}


/*七牛*/
function Qiniu_Encode($str) // URLSafeBase64Encode
{
    $find = array('+', '/');

    $replace = array('-', '_');

    return str_replace($find, $replace, base64_encode($str));
}



//$info里面的url
function Qiniu_Sign($url)
{
    $setting = M('qiniu')->where(['company_id'=>0])->find();

    $duetime = NOW_TIME + 86400;//下载凭证有效时间

    $DownloadUrl = $url . '?e=' . $duetime;

    $Sign = hash_hmac ( 'sha1', $DownloadUrl, $setting['secrect_key'], true );

    $EncodedSign = Qiniu_Encode ( $Sign );

    $Token = $setting['access_key'] . ':' . $EncodedSign;

    $RealDownloadUrl = $DownloadUrl . '&token=' . $Token;

    return $RealDownloadUrl;
}



/**
 * 自动闭合不完整的HTML标签
 * @param string $body
 * @return string
 */
function HtmlClose($body)
{
    $strlen_var = strlen($body);

    // 不包含 html 标签
    if (strpos($body, '<') === false)
	{
        return $body;
    }

    //html 代码标记
    $html_tag = $size = 0;

    // 摘要字符串
    $summary_string = '';

    $html_array_str = '';

    /**
     * 数组用作记录摘要范围内出现的 html 标签
     * 开始和结束分别保存在 left 和 right 键名下
     * 如字符串为：<h3><p><b>a</b></h3>，假设 p 未闭合
     * 数组则为：array('left' => array('h3', 'p', 'b'), 'right' => 'b', 'h3');
     * 仅补全 html 标签，<? <% 等其它语言标记，会产生不可预知结果
     */
    $html_array = array('left' => array(), 'right' => array());

    for($i = 0; $i < $strlen_var; ++$i)
	{
        $current_var = substr($body, $i, 1);

        if($current_var == '<')
		{
            // html 代码开始
            $html_tag = 1;
            $html_array_str = '';
        }
		else if($html_tag == 1)
		{
            // 一段 html 代码结束
            if($current_var == '>')
			{
                /**
                 * 去除首尾空格，如 <br /  > < img src="" / > 等可能出现首尾空格
                 */
                $html_array_str = trim($html_array_str);

                /**
                 * 判断最后一个字符是否为 /，若是，则标签已闭合，不记录
                 */
                if(substr($html_array_str, -1) != '/')
				{
                    // 判断第一个字符是否 /，若是，则放在 right 单元
                    $f = substr($html_array_str, 0, 1);
                    if($f == '/')
					{
                        // 去掉 /
                        $html_array['right'][] = str_replace('/', '', $html_array_str);
                    }
					else if ($f != '?')
					{
                        // 判断是否为 ?，若是，则为 PHP 代码，跳过

                        /**
                         * 判断是否有半角空格，若有，以空格分割，第一个单元为 html 标签
                         * 如 <h2 class="a"> <p class="a">
                         */
                        if(strpos($html_array_str, ' ') !== false)
						{
                            // 分割成2个单元，可能有多个空格，如：<h2 class="" id="">
                            $html_array['left'][] = strtolower(current(explode(' ', $html_array_str, 2)));
                        }
						else
						{
                            /**
                             * * 若没有空格，整个字符串为 html 标签，如：<b> <p> 等
                             * 统一转换为小写
                             */
                            $html_array['left'][] = strtolower($html_array_str);
                        }
                    }
                }

                // 字符串重置
                $html_array_str = '';
                $html_tag = 0;
            }
			else
			{
                /**
                 * 将< >之间的字符组成一个字符串
                 * 用于提取 html 标签
                 */
                $html_array_str .= $current_var;
            }
        }
		else
		{
            // 非 html 代码才记数
            --$size;
        }

        $ord_var_c = ord($body{$i});

        switch (true)
		{
            case (($ord_var_c & 0xE0) == 0xC0):
                // 2 字节
                $summary_string .= substr($body, $i, 2);
                $i += 1;
                break;
            case (($ord_var_c & 0xF0) == 0xE0):

                // 3 字节
                $summary_string .= substr($body, $i, 3);
                $i += 2;
                break;
            case (($ord_var_c & 0xF8) == 0xF0):
                // 4 字节
                $summary_string .= substr($body, $i, 4);
                $i += 3;
                break;
            case (($ord_var_c & 0xFC) == 0xF8):
                // 5 字节
                $summary_string .= substr($body, $i, 5);
                $i += 4;
                break;
            case (($ord_var_c & 0xFE) == 0xFC):
                // 6 字节
                $summary_string .= substr($body, $i, 6);
                $i += 5;
                break;
            default:
                // 1 字节
                $summary_string .= $current_var;
        }
    }

    if ($html_array['left'])
	{
        /**
         * 比对左右 html 标签，不足则补全
         */
        /**
         * 交换 left 顺序，补充的顺序应与 html 出现的顺序相反
         * 如待补全的字符串为：<h2>abc<b>abc<p>abc
         * 补充顺序应为：</p></b></h2>
         */
        $html_array['left'] = array_reverse($html_array['left']);

        foreach ($html_array['left'] as $index => $tag)
		{
            // 判断该标签是否出现在 right 中
            $key = array_search($tag, $html_array['right']);

            if($key !== false)
			{
                // 出现，从 right 中删除该单元
                unset($html_array['right'][$key]);
            }
			else
			{
                // 没有出现，需要补全
                $summary_string .= '</' . $tag . '>';
            }
        }
    }

    return $summary_string;
}


function getEncode($array)
{
    $a = json_encode($array);

    return $a;
}


function remove_trim($str)
{
    return rtrim($str,',');
}


//移除非法字段
function checkFields($data = array(), $fields = array())
{
    foreach ($data as $k => $val )
    {
        if (!in_array($k, $fields))
        {
            unset($data[$k]);
        }
    }

    return $data;
}



/*自定义字段查重*/

function CrmisUniqueData($type,$company_id,$form_id,$form_content)
{
	$data = getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,'form_id'=>$form_id,'form_content'=>$form_content])->select();

	$i = 0;

	foreach($data as $v)
	{
		$info = getCrmDbModel($type)->where(['company_id'=>$company_id,$type.'_id'=>$v[$type.'_id']])->find();

		if($info)
		{
			$i++;
		}
	}

	if($i > 0)
	{
		return $info;
	}
	else
	{
		return false;
	}
}

function FEELCRM($action='',$params=[],$title='',$style='',$load='',$iconstyle='',$showtitle='')
{
	$user = session('index');

    $class = $icon = '';

	$hasAuth = D('RoleAuth')->checkRoleAuthByMenu($user['company_id'],$action,$user['role_id'],'crm');

    if($user['is_first'] == 1)
    {
        if(!$hasAuth)
        {
            $action = 'javascript:';

            $title = L('UNAUTHORIZED');

            $class = ' style=display:none';
        }
        else if(!$action)
        {
            $action = 'javascript:';
        }
        else
        {
            if($style) $class = ' class="'.$style.'"';

            $action = U($action, $params);
        }
    }
    else
    {
        if($style) $class = ' class="'.$style.'"';

        if(!$action)
        {
            $action = 'javascript:';
        }
        else
        {
            $action = U($action, $params);
        }
    }

    if($iconstyle) $icon = "class='{$iconstyle}'";

    if($load)  $load = ' load="'.$load.'"';

    $a_tag = '<a href="'.$action.'"'.$class.$load.' title="'.$showtitle.'" ><i '.$icon.'></i>'.$title.'</a>';

    return $a_tag;
}


/**
 * 登录超时处理
 * @param string $login_time 登录时间
 * @param string $setTime  时限
 * @return string
 */
function CrmfetchAll($data,$field)
{
	$datas = [];

	foreach($data as $v)
	{
		$datas[$v[$field]] = $v;
	}

	return $datas;
}



function CrmisLoginTimeout($login_time,$setTime)
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


function CrmgetPercentage($totle,$num)
{
	if($totle == 0 || !$totle)
	{
		return '0%';
	}
	else
	{
		return number_format(($num/$totle)*100,2).'%';
	}
}


function CrmgetViewName()
{
    $url = CONTROLLER_NAME.'/'.ACTION_NAME;

    $viewName = D('Menu')->getNameByLang('menu_name');

    $view_name = M('menu')->where(['menu_action'=>$url,'apply'=>10])->getField("{$viewName}");

    return $view_name;
}


function CrmgetRecordContent($str = '')
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



//获取group名称组成的字符串
function CrmgetMemberGroupName($groups,$memberGroupIds)
{
    $groupNameStr = '';

    foreach($groups as $v)
    {
        if(in_array($v['group_id'],explode(',',$memberGroupIds)))
        {
            $groupNameStr .= $v['group_name'].'、';
        }
    }

    if(!$groupNameStr)
    {
    	$groupNameStr = '<span class="iconfont icon-nothing gray1"></span>';
    }

    return rtrim($groupNameStr,'、');
}

//查询自定义字段detail信息
function CrmgetCrmDetailList($type,$get_id,$company_id,$formName='')
{
	$data = [];

	$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$company_id])->find();

	$crmsite = unserialize($crmsite['value']);

	if($formName)
	{
		$form = getCrmDbModel('define_form')->where(['company_id'=>$company_id,'closed'=>0,'type'=>$type,'form_name'=>['in',$formName]])->field('form_id,form_name,form_type')->select();
	}
	else
	{
		$form = getCrmDbModel('define_form')->where(['company_id'=>$company_id,'closed'=>0,'type'=>$type])->field('form_id,form_name,form_type')->select();
	}

    foreach($form as $k1=>&$v1)
    {
        $form_content= getCrmDbModel($type.'_detail')
	        ->where(['company_id'=>$company_id,$type.'_id'=>$get_id,'form_id'=>$v1['form_id']])
	        ->getField('form_content');

		if($v1['form_type'] == 'textarea')
		{
			$data[$v1['form_name']] = htmlspecialchars_decode($form_content);
		}
		elseif($v1['form_type'] == 'region')
		{
			if($form_content)
			{
				$region_detail = explode(',',$form_content);

				$form_content = '';

				if($region_detail[0])
				{
					$name = getCrmLanguageData('name');

					$country = getCrmDbModel('country')->where(['code'=>$region_detail[0]])->getField($name);

					if(!$crmsite['regionType'] || $crmsite['regionType'] == 'world')
					{
						$form_content .= $country;
					}
				}
				if($region_detail[0] && $region_detail[1])
				{
					$province_name = getCrmLanguageData('name');

					$province = getCrmDbModel('province')
						->where(['code'=>$region_detail[1],'country_code'=>$region_detail[0]])
						->getField($province_name);

					if(!$crmsite['regionType'] || $crmsite['regionType'] == 'world')
					{
						$form_content .= '--'.$province;
					}
					else
					{
						$form_content .= $province;
					}
				}
				if($region_detail[0] && $region_detail[1] && $region_detail[2])
				{
					$city_name = getCrmLanguageData('name');

					$city = getCrmDbModel('city')
						->where(['code'=>$region_detail[2],'country_code'=>$region_detail[0],'province_code'=>$region_detail[1]])
						->getField($city_name);

					$form_content .= '--'.$city;
				}
				if($region_detail[0] && $region_detail[1] && $region_detail[2] && $region_detail[3])
				{
					$area_name = getCrmLanguageData('name');

					$area = getCrmDbModel('area')
						->where(['code'=>$region_detail[3],'country_code'=>$region_detail[0],'province_code'=>$region_detail[1],'city_code'=>$region_detail[2]])
						->getField($area_name);

					$form_content .= '--'.$area;
				}
				if(!$country && !$province && !$city)
				{
					$form_content = '';

					for($i=0;$i<count($region_detail);$i++)
					{
						$region_content = explode('_',$region_detail[$i]);

						if($i >0) $form_content .= '--';

						$form_content .= $region_content[0];
					}
				}
			}

			$data[$v1['form_name']] = $form_content;
		}
		else
		{
			$data[$v1['form_name']] = $form_content;
		}
    }

    return $data;
}

//跟据用户id查询用户所属部门下的所有成员
function getGroupMemberList($company_id,$member_id)
{
	//            查询当前用户所属部门
	$groupIds = M('member')->where(['member_id'=>$member_id])->getField('group_id');

	//            查询部门下的用户
	foreach(explode(',',$groupIds) as $k=>$v)
	{
		$members[$k] = M('member')
			->where(["find_in_set('{$v}',group_id)",'type'=>1,'company_id'=>$company_id])
			->field('member_id,name,group_id')->select();
	}

	$membersId = [];

	$j = 0;

	foreach($members as $k1=>$v1)
	{
		foreach($v1 as $k2=>$v2)
		{
			if(!in_array($v2['member_id'],$membersId))
			{
				$membersId[$j] = $v2['member_id'];

				$users[$j] = ['member_id'=>$v2['member_id'],'name'=>$v2['name']];

				$j ++;
			}
		}
	}

	return ['member_id'=>$membersId,'user'=>$users];
}

//根据部门id查询部门下的所有成员
function getGroupMemberByGroups($company_id,$groupIds)
{
	//            查询部门下的用户

	if(!is_array($groupIds))
	{
		$groupIds = explode(',',$groupIds);
	}

	foreach($groupIds as $k=>$v)
	{
		$members[$k] = M('member')
			->where(["find_in_set('{$v}',group_id)",'type'=>1,'closed'=>0,'feelec_opened'=>10,'company_id'=>$company_id])
			->field('member_id,name,group_id')->select();
	}

	$membersId = [];

	$j = 0;

	foreach($members as $k1=>$v1)
	{
		foreach($v1 as $k2=>$v2)
		{
			if(!in_array($v2['member_id'],$membersId))
			{
				$membersId[$j] = $v2['member_id'];

				$users[$j] = ['member_id'=>$v2['member_id'],'name'=>$v2['name'],'id'=>$v2['member_id']];

				$j ++;
			}
		}
	}

	return ['member_id'=>$membersId,'user'=>$users];
}

//客户查看权限
function CrmmemberRoleCrmauth($thisMember,$company_id,$member_id,$viewType='',$showtype='index')
{
	if($showtype == 'pool')
	{
		$all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'poolAll',$thisMember['role_id'],'crm');

		$group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'poolGroup',$thisMember['role_id'],'crm');

		$own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'poolOwn',$thisMember['role_id'],'crm');
	}
	elseif($showtype == 'clue')
	{
		$all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'clueAll',$thisMember['role_id'],'crm');

		$group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'clueGroup',$thisMember['role_id'],'crm');

		$own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'clueOwn',$thisMember['role_id'],'crm');
	}
	elseif($showtype == 'cluePool')
	{
		$all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'cluePoolAll',$thisMember['role_id'],'crm');

		$group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'cluePoolGroup',$thisMember['role_id'],'crm');

		$own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'cluePoolOwn',$thisMember['role_id'],'crm');
	}
	elseif($showtype == 'contract')
	{
		$all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'contractsAll',$thisMember['role_id'],'crm');

		$group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'contractsGroup',$thisMember['role_id'],'crm');

		$own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'contractsOwn',$thisMember['role_id'],'crm');
	}
	elseif($showtype == 'account')
	{
		$all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'accountAll',$thisMember['role_id'],'crm');

		$group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'accountGroup',$thisMember['role_id'],'crm');

		$own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'accountOwn',$thisMember['role_id'],'crm');
	}
	elseif($showtype == 'receipt')
	{
		$all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'receiptAll',$thisMember['role_id'],'crm');

		$group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'receiptGroup',$thisMember['role_id'],'crm');

		$own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'receiptOwn',$thisMember['role_id'],'crm');
	}
	elseif($showtype == 'invoice')
	{
		$all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'invoiceAll',$thisMember['role_id'],'crm');

		$group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'invoiceGroup',$thisMember['role_id'],'crm');

		$own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'invoiceOwn',$thisMember['role_id'],'crm');
	}
	else
	{
		$all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'all',$thisMember['role_id'],'crm');

		$group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'group',$thisMember['role_id'],'crm');

		$own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($company_id,'own',$thisMember['role_id'],'crm');
	}

	$users = $members = [];

	if(!$viewType)
	{
		if($all_view_auth)
		{
			$members = M('member')->where(['type'=>1,'company_id'=>$company_id/*,'closed'=>0*/])->field('member_id,name')->select();

			$users = M('member')->where(['type'=>1,'company_id'=>$company_id,'closed'=>0])->field('member_id,name')->select();

			$memberIds = [];

			foreach($members as $k=>$m)
			{
				$memberIds[$k] = $m['member_id'];
			}

			if($showtype == 'pool' || $showtype == 'cluePool')
			{
				$field = ['exp','is not null'];
			}
			else
			{
				$field = ['in',implode(',',$memberIds)];
			}
		}
		else if($group_view_auth)
		{

	//            查询当前用户所属部门
			$groupIds = M('member')->where(['member_id'=>$member_id])->getField('group_id');

	//            查询部门下的用户
			foreach(explode(',',$groupIds) as $k=>$v)
			{
				$members[$k] = M('member')
					->where(["find_in_set('{$v}',group_id)",'type'=>1,'company_id'=>$company_id/*,'closed'=>0*/])
					->field('member_id,name,group_id')->select();
			}

			$membersId = [];

			$j = 0;

			foreach($members as $k1=>$v1)
			{
				foreach($v1 as $k2=>$v2)
				{
					if(!in_array($v2['member_id'],$membersId))
					{
						$membersId[$j] = $v2['member_id'];

						if($v2['closed'] == 0)
						{
							$users[$j] = ['member_id'=>$v2['member_id'],'name'=>$v2['name']];
						}

						$j ++;
					}
				}
			}

			$field = ['in',implode(',',$membersId)];
		}
		else
		{
			if(!$own_view_auth)
			{
				$field = 0;
			}
			else
			{
				$users[0] = ['member_id'=>$member_id,'name'=>$thisMember['name']];

				$field = $member_id;
			}
		}

		return $field;
	}
	else
	{
		if($viewType == 'all')
		{
			$members = M('member')->where(['type'=>1,'company_id'=>$company_id/*,'closed'=>0*/])->field('member_id,name')->select();

			$users = M('member')->where(['type'=>1,'company_id'=>$company_id,'closed'=>0])->field('member_id,name')->select();

			$memberIds = [];

			foreach($members as $k=>$m)
			{
				$memberIds[$k] = $m['member_id'];
			}

			if($showtype == 'pool' || $showtype == 'cluePool')
			{
				$field = ['exp','is not null'];
			}
			else
			{
				$field = ['in',implode(',',$memberIds)];
			}
		}
		else if($viewType == 'group')
		{

	//            查询当前用户所属部门
			$groupIds = M('member')->where(['member_id'=>$member_id])->getField('group_id');

	//            查询部门下的用户
			foreach(explode(',',$groupIds) as $k=>$v)
			{
				$members[$k] = M('member')
					->where(["find_in_set('{$v}',group_id)",'type'=>1,'company_id'=>$company_id/*,'closed'=>0*/])
					->field('member_id,name,group_id,closed')->select();
			}

			$membersId = [];

			$j = 0;

			foreach($members as $k1=>$v1)
			{
				foreach($v1 as $k2=>$v2)
				{
					if(!in_array($v2['member_id'],$membersId))
					{
						$membersId[$j] = $v2['member_id'];

						if($v2['closed'] == 0)
						{
							$users[$j] = ['member_id'=>$v2['member_id'],'name'=>$v2['name']];
						}

						$j++;
					}
				}
			}

			$field = ['in',implode(',',$membersId)];
		}
		else
		{
			if(!$own_view_auth)
			{
				$field = 0;

				$users = '';
			}
			else
			{
				$users[0] = ['member_id'=>$member_id,'name'=>$thisMember['name']];

				$field = $member_id;
			}
		}

		return ['field'=>$field,'users'=>$users,'members'=>$members];
	}

}

//自定义字段查询条件
function CrmgetDefineFormField($company_id,$type,$form_name,$keyword)
{
	$name_form_id = getCrmDbModel('define_form')
	->where(['company_id'=>$company_id,'type'=>$type,'form_name'=>['in',$form_name]])
	->field('form_id,form_type')->select();

	$data_arr = [];

	foreach($name_form_id as $namev)
	{
		if(in_array($name_form_id['form_type'],['radio','select']))
		{
			$form_content_field = $keyword;
		}
		else
		{
			$form_content_field = ["like","%".$keyword."%"];
		}

		$data_name = getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,'form_id'=>$namev['form_id'],'form_content'=>$form_content_field])->field($type.'_id,form_content')->select();

		foreach($data_name as $cv)
		{
			$data_arr[] .= $cv[$type.'_id'];
		}
	}

	$data_arr = array_unique($data_arr);

	if(count($data_arr) > 0)
	{
		$field = implode(',',$data_arr);
	}
	else
	{
		$field = 0;
	}

	return $field;
}

//自定义字段高级查询条件
function CrmgetDefineFormHighField($company_id,$type,$form_name,$keyword)
{
	$name_form_id = getCrmDbModel('define_form')
	->where(['company_id'=>$company_id,'type'=>$type,'form_name'=>['in',$form_name]])
	->field('form_id,form_type')->find();

	if($keyword)
	{
		if(in_array($name_form_id['form_type'],['radio','select']))
		{
			$form_content_field = ['in',$keyword];
		}
		else
		{
			$form_content_field = ["like","%".$keyword."%"];
		}

		$data_name = getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,'form_id'=>$name_form_id['form_id'],'form_content'=>$form_content_field])->field($type.'_id,form_content')->select();
	}
	else
	{
		$data_name = getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,'form_id'=>$name_form_id['form_id'],'form_content'=>''])->field($type.'_id,form_content')->select();
	}

	foreach($data_name as $cv)
	{
		$data_arr[] .= $cv[$type.'_id'];
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

//自定义字段高级查询条件-时间范围
function CrmgetDefineFormHighFieldTimeRange($company_id,$type,$form_name,$starttime,$endtime='')
{
	$name_form_id = getCrmDbModel('define_form')
		->where(['company_id'=>$company_id,'type'=>$type,'form_name'=>['in',$form_name]])
		->field('form_id,form_type')->find();

	if($starttime)
	{
		$starttime = date('Y-m-d H:i:s',$starttime);

		if($endtime)
		{
			$endtime = date('Y-m-d H:i:s',$endtime);

			$form_content_field = [['egt',$starttime],['elt',$endtime]];
		}
		else
		{
			$form_content_field = [['egt',$starttime],['elt',date('Y-m-d H:i:s',time())]];
		}
//var_dump($form_content_field);die;
		$data_name = getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,'form_id'=>$name_form_id['form_id'],'form_content'=>$form_content_field])->field($type.'_id,form_content')->select();
	}
	else
	{
		$data_name = getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,'form_id'=>$name_form_id['form_id'],'form_content'=>''])->field($type.'_id,form_content')->select();
	}

	foreach($data_name as $cv)
	{
		$data_arr[] .= $cv[$type.'_id'];
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

//获取列表显示的自定义字段
function CrmgetShowListField($type,$company_id)
{
	$lang = cookie('think_language');

	$name = 'form_description';

	if($lang == 'en-us') $name = 'name_en as form_description';

	if($lang == 'ja-jp') $name = 'name_jp as form_description';

	$field = getCrmDbModel('define_form')->where(['company_id'=>$company_id,'closed'=>0,'type'=>$type,'show_list'=>1])
		->field('form_name,'.$name.',form_type,role_id,member_id')->order('orderby asc')->select();

	$form_name = [];

	foreach($field as $val)
	{
		$form_name[] = $val['form_name'];
	}

	$form_name = implode(',',$form_name);

	return ['form_name'=>$form_name,'form_list'=>$field];

}

function CrmgetFieldName($id,$type)
{
	$company_id = getCrmDbModel($type)->where([$type.'_id'=>$id,'isvalid'=>1])->getField('company_id');

	$detail = CrmgetCrmDetailList($type,$id,$company_id,'name');

	return $detail['name'];
}

function get_weeks($daynum = 7,$time = '', $format='Y-m-d')
{
  $time = $time != '' ? $time : time();
  //组合数据
  $date = [];

  for ($i=1; $i<=$daynum; $i++){
	$date[$i] = date($format ,strtotime( '+' . $i-$daynum .' days', $time));
  }

  return $date;
}

//获取产品分类列表
function resultCategory($data,$class='',$type_id = 0,$j=0)
{

	$i = 1;
	$j ++;
	$count = count($data);
	//var_dump($data);die;
	$arr = '';

	foreach($data as $key=>$val)
	{
		$padding = $j * 3;

		$paddingstr = "padding: 0 ".$padding."%";

		$prolink = U('AjaxRequest/getProductList',['id'=>encrypt($val['type_id'],'PRODUCT')]);


		if(count($val['subClass']) > 0 && $class=='parent')
		{
			$arr .= '<div class="product-parent-item" data-value="'.$val['type_id'].'" data-name="'.$val['type_name'].'"><div class="product-parent-name product-type"><span>'.$val['type_name'].'</span><div class="operate"><i class="iconfont icon-tianjia to-child"></i><i class="iconfont icon-more"></i><ul class="operate-menu"><li class="to-product" data-href="'.$prolink.'" data-value="'.$val['type_id'].'">'.L('PRODUCT').'</li></ul></div></div><ul class="product-child-item">';
		}
		else
		{
			if(count($val['subClass']) > 0 && $class == 'child')
			{
				$arr .='<li class="product-child-type" data-value="'.$val['type_id'].'" data-name="'.$val['type_name'].'"><div class="product-type" style="'.$paddingstr.'"><i class="iconfont icon-wenjian"></i><div class="product-child-name">'.$val['type_name'].'</div><div class="operate"><i class="iconfont icon-tianjia to-child"></i><i class="iconfont icon-more"></i><ul class="operate-menu"><li class="to-product" data-href="'.$prolink.'" data-value="'.$val['type_id'].'">'.L('PRODUCT').'</li></ul></div></div><ul class="product-child-item">';
			}
			elseif($class == 'parent')
			{
				$arr .= '<div class="product-parent-item" data-value="'.$val['type_id'].'" data-name="'.$val['type_name'].'"><div class="product-parent-name product-type"><span>'.$val['type_name'].'</span><div class="operate"><i class="iconfont icon-more"></i><ul class="operate-menu"><li class="to-product" data-href="'.$prolink.'" data-value="'.$val['type_id'].'">'.L('PRODUCT').'</li></ul></div></div>';
			}
			else
			{
				$arr .='<li class="product-child-type" data-value="'.$val['type_id'].'" data-name="'.$val['type_name'].'"><div class="product-type" style="'.$paddingstr.'"><i class="iconfont icon-wenjian"></i><div class="product-child-name">'.$val['type_name'].'</div><div class="operate"><i class="iconfont icon-more"></i><ul class="operate-menu"><li class="to-product" data-href="'.$prolink.'" data-value="'.$val['type_id'].'">'.L('PRODUCT').'</li></ul></div></div>';
			}

		}

		if($val['subClass'])
		{

			$arr .= resultCategory($val['subClass'],'child',$type_id,$j);
		}

		if(count($val['subClass']) > 0 && $class=='parent')
		{
			$arr .= '</ul></div>';
		}
		else
		{
			if(count($val['subClass']) > 0 && $class == 'child')
			{
				$arr .= '</ul></li>';
			}
			elseif($class == 'parent')
			{
				$arr .= '</div>';
			}
			else
			{
				$arr .= '</li>';
			}
		}

		$i++;
	}

	return $arr;
}

function tocurl($url, $header,$content='',$method='POST'){
	 //初始化CURL句柄
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $url);//设置请求的URL

	curl_setopt($curl, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出

	//curl_setopt($curl,CURLOPT_PROXY,'127.0.0.1:8888');

	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	//请求时间
	$timeout = 30;

	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);//设置连接等待时间

	curl_setopt($curl,CURLOPT_POST,1);

	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

	curl_setopt($curl, CURLOPT_HEADER, false);

	if($method == 'GET')
	{
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");//HTTP 请求时，使用自定义的 Method 来代替"GET"或"HEAD"。对 "DELETE" 或者其他更隐蔽的 HTTP 请求有用。 有效值如 "GET"，"POST"，"CONNECT"等等；

		curl_setopt($curl, CURLOPT_HTTPGET, 1); // 发送一个常规的GET请求
	}
	else
	{
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");//HTTP 请求时，使用自定义的 Method 来代替"GET"或"HEAD"。对 "DELETE" 或者其他更隐蔽的 HTTP 请求有用。 有效值如 "GET"，"POST"，"CONNECT"等等；

		//设置提交的信息
		curl_setopt($curl, CURLOPT_POSTFIELDS,$content);//全部数据使用HTTP协议中的 "POST" 操作来发送。
	}

	$data = curl_exec($curl);//执行预定义的CURL
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);//获取http返回值,最后一个收到的HTTP代码
	$lasturl = curl_getinfo($curl,CURLINFO_EFFECTIVE_URL);//最后一个有效的URL地址
	curl_close($curl);//关闭cURL会话

	$res = json_decode($data,true);

	return $res;
}

function imgToBase64($img_file) {

	$img_base64 = '';
	if (file_exists($img_file)) {
		$app_img_file = $img_file; // 图片路径
		$img_info = getimagesize($app_img_file); // 取得图片的大小，类型等

		//echo '<pre>' . print_r($img_info, true) . '</pre><br>';
		$fp = fopen($app_img_file, "r"); // 图片是否可读权限

		if ($fp) {
			$filesize = filesize($app_img_file);
			$content = fread($fp, $filesize);
			$file_content = chunk_split(base64_encode($content)); // base64编码
			switch ($img_info[2]) {           //判读图片类型
				case 1: $img_type = "gif";
					break;
				case 2: $img_type = "jpg";
					break;
				case 3: $img_type = "png";
					break;
			}

			$img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content;//合成图片的base64编码

		}
		fclose($fp);
	}

	return $img_base64; //返回图片的base64
}

//去除二维数组的重复值
function a_array_unique($array){

  $out = array();

  foreach ($array as $key=>$value) {

   if (!in_array($value, $out)){

    $out[$key] = $value;

   }

  }

  $out = array_values($out);

  return $out;
}

function getReceiptTypeName($type)
{
	switch($type)
	{
		case 'CASH':

			$name = L('CASH');

		break;

		case 'BANK':

			$name = L('BANK_TRANSFER');

		break;

		case 'CORPORATE':

			$name = L('CORPORATE_TRANSFER');

		break;

		case 'CHEQUE':

			$name = L('CHEQUE');

		break;

		case 'REMIT':

			$name = L('REMITTANCE');

		break;

		case 'WECHAT':

			$name = L('WECHAT_TRANSFER');

		break;

		case 'ALIPAY':

			$name = L('ALIPAY_TRANSFER');

		break;

		case 'ESCROW':

			$name = L('THIRD_PARTY_PAYMENT');

		break;
	}

	return $name;
}

function getFinanceExamineName($status,$type='')
{
	if($type == 'html')
	{
		switch($status)
		{
			case '1':

				$name = '<span class="orange">'.L('WAIT_AUDIT').'</span>';

			break;

			case '-1':

				$name = '<span class="red1">'.L('REJECTED').'</span>';

			break;

			case '2':

				$name = '<span class="green1">'.L('AUDITED').'</span>';

			break;
		}
	}
	elseif($type == 'operate')
	{
		switch($status)
		{
			case 'examine':

				$name = L('AUDIT');

			break;

			case 'reject':

				$name = L('REJECT');

			break;

			case 'revoke':

				$name = L('REVOKE');

			break;
		}
	}
	elseif($type == 'operate_result')
	{
		switch($status)
		{
			case 'examine':

				$name = L('WAIT_AUDIT');

				break;

			case 'reject':

				$name = L('REJECTED');

				break;

			case 'revoke':

				$name = L('REVOKED');

				break;
		}
	}
	else
	{
		switch($status)
		{
			case '1':

				$name = L('WAIT_AUDIT');

			break;

			case '-1':

				$name = L('REJECTED');

			break;

			case '2':

				$name = L('AUDITED');

			break;
		}
	}

	return $name;
}

function getInvoiceTypeName($type)
{
	switch($type)
	{
		case 'GENERAL':

			$name = L('VAT_GENERAL_INVOICE');

		break;

		case 'SPECIAL':

			$name = L('VALUE_ADDED_TAX_INVOICE');

		break;

		case 'RECEIPT':

			$name = L('RECEIPT');

		break;
	}

	return $name;
}

function getLanguage($company = [])
{
	$language = M('language')->select();

	if($company)
	{
		foreach($language as $k=>$v)
		{
			if($v['lang'] == 'en-us' && $company['en_auth'] != 10) unset($language[$k]);

			if($v['lang'] == 'ja-jp' && $company['jp_auth'] != 10) unset($language[$k]);
		}
	}

	return $language;
}

function getCustomerCreateName($creater_id,$create_name='',$company_id=0,$type='')
{
	if($type == 'html')
	{
		if (!$creater_id) {
			$html = '<span class="red1">'.L('SYSTEM_').'</span>';
		}elseif ($creater_id == -1) {
			$html = '<span class="red1">'.L('THIRD_PARTY_SYSTEM').'</span>'; //第三方系统
		} elseif ($creater_id == -2) {
			$html = '<span class="red1">'.L('WEBSITE_REGIST').'</span>'; //网站注册
		} elseif ($creater_id == -3) {
			$html = '<span class="red1">'.L('WEBSITE_REGIST').'('.L('CUSTOMER_SERVICE').')</span>'; //网站注册（客服）
		} elseif ($creater_id == -4) {
			$html = '<span class="red1">'.L('WEBSITE_REGIST').'('.L('DISPATCH').')</span>'; //网站注册（派单）
		} elseif ($creater_id == -5) {
			$html = '<span class="red1">'.L('WEBSITE_REGIST').'( CRM )</span>'; //网站注册（crm）
		} elseif ($creater_id == -6) {
			$html = '<span class="red1">'.L('WEBSITE_REGIST').'('.L('TICKET').'5.0)</span>'; //网站注册（工单5.0）
		} elseif ($creater_id == -7) {
			$html = '<span class="red1">'.L('WEBSITE_REGIST').'('.L('BUSINESS_CARD').')</span>'; //网站注册（名片）
		}
		else {
			if (!$create_name) {
				$create_name = M('Member')->where(['company_id' => $company_id, 'closed' => 0, 'type' => 1, 'member_id' => $creater_id])->getField('name');
			}

			$html = '<span class="blue8">'.$create_name.' </span>';
		}
	}
	else
	{
		if (!$creater_id) {
			$html = L('SYSTEM_');
		}elseif ($creater_id == -1) {
			$html = L('THIRD_PARTY_SYSTEM');
		} elseif ($creater_id == -2) {
			$html = L('WEBSITE_REGIST');
		} elseif ($creater_id == -3) {
			$html = L('WEBSITE_REGIST').'('.L('CUSTOMER_SERVICE').')';
		} elseif ($creater_id == -4) {
			$html = L('WEBSITE_REGIST').'('.L('DISPATCH').')';
		} elseif ($creater_id == -5) {
			$html = L('WEBSITE_REGIST').'( CRM )';
		} elseif ($creater_id == -6) {
			$html = L('WEBSITE_REGIST').'( '.L('TICKET').'5.0 )';
		} elseif ($creater_id == -7) {
			$html = L('WEBSITE_REGIST').'( '.L('BUSINESS_CARD').' )';
		}else {
			if (!$create_name) {
				$create_name = M('Member')->where(['company_id' => $company_id, 'closed' => 0, 'type' => 1, 'member_id' => $creater_id])->getField('name');
			}

			$html = $create_name;
		}
	}

	return $html;
}

function getGroupCategory($data,$company_id,$type = 0,$parent_id = 0,$lev = 0)
{
	$i = 1;

	$count = count($data);

	$members = D('Member')->where(['company_id'=>$company_id,'type'=>1,'closed'=>0])->field('member_id,name')->fetchAll();

	$roles = D('Role')->where(['company_id'=>$company_id,'closed'=>0])->field('role_id,role_name')->fetchAll();

	foreach($data as $key=>$val)
	{
		if($lev > 0)
		{
			$pre = '';
			for($j = 1;$j<=$lev;$j++)
			{
				$pre .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		}

		if($type == 1)
		{
			$html = '';

			$html = '<tr><td class="left">';

			$html .=  '<span>'.$pre.$val['group_name'].'</span>';

			if($val['is_default'] == '20')
			{
				$html .= '<i class="iconfont icon-moren orange5 fts25"></i>';
			}

			$html .=  '</td>';

			if($members[$val['manager_id']]['name'])
			{
				$html .=  '<td><span>'.$members[$val['manager_id']]['name'].'</span></td>';
			}
			else
			{
				$html .=  '<td><span><i class="iconfont icon-nothing"></i></span></td>';
			}

			if($roles[$val['role_id']]['role_name'])
			{
				$html .=  '<td><span>'.$roles[$val['role_id']]['role_name'].'</span></td>';
			}
			else
			{
				$html .=  '<td><span><i class="iconfont icon-nothing"></i></span></td>';
			}

			$html .=  '<td>';

			if($val['closed'] == 0)
			{
				$html .=  '<span class="open-status enable">'.L('ENABLE').'</span>';
			}
			else
			{
				$html .= '<span class="open-status disable">'.L('DISABLE').'</span>';
			}

			$html .=  '</td>';

			$html .=  '<td class="listOperate">';

			$html .=  '<i class="iconfont icon-dian"></i>';

			$html .=  '<div class="operate hidden">';

			$html .=  FEELDESK('Group/edit',['group_id'=>encrypt($val['group_id'],'GROUP')],L('EDITOR'),'editBtn');

			if($val['closed'] == 1)
			{
				$html .= FEELDESK('Group/delete',['id'=>$val['group_id']],L('DELETE'),'','del-group');
			}

			$html .=  FEELDESK('Member/index',['group_id'=>$val['group_id']],L('VIEW').L('USER'),'editBtn');

			$html .=  '<a href="javascript:" data-value="'.$val['group_id'].'" data-name="'.$val['group_name'].'" class="assign-user">'.L('ASSIGN_USER').'</a>';

			$html .=  '</div>';

			$html .=  '</td>';

			$html .=  '</tr>';

			$arr[] = $html;
		}
		else
		{
			if($parent_id == $val['group_id'])
			{
				$arr[] = '<option value="'.$val['group_id'].'" selected >'.$pre.$val['group_name'].'</option>';
			}
			else
			{
				$arr[] = '<option value="'.$val['group_id'].'" >'.$pre.$val['group_name'].'</option>';
			}
		}

		if($val['subClass'])
		{
			$arr = array_merge($arr,getGroupCategory($val['subClass'],$company_id,$type,$parent_id,$lev+1));
		}

		$i++;
	}

	return $arr;
}

//获取下个月的日期
function getNextMonth($timestamp)
{
	$arr=getdate($timestamp);

	if($arr['mon'] == 12)
	{
		$year=$arr['year'] +1;

		$month=$arr['mon'] -11;

		$next_month = $year.'-'.$month;
	}
	else
	{
		$next_month = date('Y',$timestamp).'-'.(date('m',$timestamp)+1);
	}

	$next_month = date('Y-m',strtotime($next_month));

	return $next_month;
}

//获取上个月的日期
function getLastMonth($timestamp)
{
	$arr=getdate($timestamp);

	if($arr['mon'] == 1)
	{
		$year=$arr['year'] -1;

		$month=$arr['mon'] +11;

		$last_month = $year.'-'.$month;
	}
	else
	{
		$last_month = date('Y',$timestamp).'-'.(date('m',$timestamp)-1);
	}

	$last_month = date('Y-m',strtotime($last_month));

	return $last_month;
}

function getKpiTypeName($type)
{
	if($type == 'customer')
	{
		$name = L('FOLLOW_UP_WITH_CUSTOMERS');
	}
	elseif($type == 'customer_trade')
	{
		$name = L('RANSACTION_CUSTOMER');
	}
	elseif($type == 'contract')
	{
		$name = L('SIGNING_THE_CONTRACT');
	}
	elseif($type == 'contract_money')
	{
		$name = L('CONTRACT_AMOUNT');
	}
	elseif($type == 'receipt')
	{
		$name = L('AMOUNT_RECEIVED');
	}

	return $name;
}

function curlJson($url,$data)
{
	$data = json_encode($data);

	//初始化CURL句柄
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $url);//设置请求的URL

	curl_setopt($curl, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出

	curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-type: application/json"]);

	//curl_setopt($curl,CURLOPT_PROXY,'127.0.0.1:8888');

	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	//请求时间
	$timeout = 30;

	curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);//设置连接等待时间

	curl_setopt($curl,CURLOPT_POST,1);

	curl_setopt($curl, CURLOPT_HEADER, false);

	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");//HTTP 请求时，使用自定义的 Method 来代替"GET"或"HEAD"。对 "DELETE" 或者其他更隐蔽的 HTTP 请求有用。 有效值如 "GET"，"POST"，"CONNECT"等等；

	//设置提交的信息
	curl_setopt($curl, CURLOPT_POSTFIELDS,$data);//全部数据使用HTTP协议中的 "POST" 操作来发送。

	$curlData = curl_exec($curl);//执行预定义的CURL

	//$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);//获取http返回值,最后一个收到的HTTP代码

	curl_close($curl);//关闭cURL会话

	$res = json_decode($curlData,true);

	return $res;
}

function getClueStatusName($status,$type = '')
{
	if($type == 'html')
	{
		if($status == 2)
		{
			$name = "<span class='blue8'>".L('CONVERTED')."</span>";
		}
		elseif($status == 1)
		{
			$name = "<span class='green1'>".L('FOLLOWING_UP')."</span>";
		}
		else
		{
			$name = "<span class='yellow2'>".L('NOT_FOLLOWED_UP')."</span>";
		}
	}
	else
	{
		if($status == 2)
		{
			$name = L('CONVERTED');
		}
		elseif($status == 1)
		{
			$name = L('FOLLOWING_UP');
		}
		else
		{
			$name = L('NOT_FOLLOWED_UP');
		}
	}

	return $name;
}

function getCrmEntryMethod($type)
{
	switch ($type)
	{
		case 'CREATE':
			$name = L('MANUAL_ENTRY');
			break;
		case 'API':
			$name = L('API_INPUT');
			break;
		case 'IMPORT':
			$name = L('BATCH_IMPORT');
			break;
	}

	return $name;
}

function openFile($link,$type)
{
	if($type == 'doc' || $type =='docx' || $type =='xlsx' || $type =='xls' || $type =='ppt' || $type =='pptx')
	{
		$link = 'https://view.officeapps.live.com/op/view.aspx?src='.$link;
	}

	return $link;
}

function getCreaterViewSql($member_id)
{
	if(count($member_id) > 1)
	{
		if(is_array($member_id[1]))
		{
			$member_id[1] = implode(',',$member_id[1]);
		}

		$create_view_sql = "((member_id in (".$member_id[1].") or creater_id in (".$member_id[1].")) and member_id > 0)";
	}
	else
	{
		$create_view_sql = "((member_id = ".$member_id." or creater_id = ".$member_id.") and member_id > 0)";
	}

	return $create_view_sql;
}

//检查联系人查询字段该联系人是否存在
function checkContacterSqlField($company_id,$contacterField)
{
	if($contacterField)
	{
		$first_contacts = getCrmDbModel('contacter')->where(['company_id'=>$company_id,'isvalid'=>1,'contacter_id'=>['in',$contacterField]])->field('contacter_id')->select();

		if($first_contacts)
		{
			$first_contact_ids = array_column($first_contacts,'contacter_id');

			$contacterField = implode(',',$first_contact_ids);
		}
		else
		{
			$contacterField = 0;
		}
	}

	return $contacterField;
}

//根据导入的产品分类数组，取得对应树状数组中的type_id
function getTypeIdByImportValue($company_id,$type_arr,$product_type,$lev = 0)
{
	if(!$type_arr)
	{
		return false;
	}
	else
	{
		$type_id = $product_type[$type_arr[$lev]]['type_id'];

		if(!$type_id)
		{
			return false;
		}
		else
		{
			if($lev == (count($type_arr) - 1))
			{
				return $type_id;
			}
			else
			{
				$type_id = getTypeIdByImportValue($company_id,$type_arr,$product_type[$type_arr[$lev]]['subClass'],$lev + 1);

				return $type_id;
			}
		}
	}
}

//将数组所有子级按字段进行树状排列
function CrmfetchAllChild($data,$field)
{
	$datas = [];

	foreach($data as $v)
	{
		$datas[$v[$field]] = $v;

		if($v['subClass'])
		{
			$datas[$v[$field]]['subClass'] = CrmfetchAllChild($v['subClass'],$field);
		}
	}

	return $datas;
}

//修改主商户
function updateFeelecPrimary($feelec_primary,$company_id)
{
	if($feelec_primary == 10)
	{
		M('company')->where(['feelec_primary'=>10,'company_id'=>['neq',$company_id]])->save(['feelec_primary'=>20]);
	}
}
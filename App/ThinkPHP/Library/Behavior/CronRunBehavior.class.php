<?php
namespace Behavior;

class CronRunBehavior 
{
    public function run(&$params) 
	{
        if (C('CRON_CONFIG_SWITCH')) 
		{
            $this->checkTime();
        }
    }

    private function checkTime() 
	{
        if (F('CRON_CONFIG')) 
		{
            $crons = F('CRON_CONFIG');
        } 
		else if (C('CRON_CONFIG')) 
		{
            $crons = C('CRON_CONFIG');
        }

        if (!empty($crons) && is_array($crons)) 
		{
            $update = false;

            $log = array();

            foreach ($crons as $key => $cron) 
			{
				//判断指定的开始时间是否有效
                if (empty($cron[2]) || $_SERVER['REQUEST_TIME'] > $cron[2]) 
				{
                    G('cronStart');
                    R($cron[0]);// 到达指定时间 执行cron文件
                    G('cronEnd');

                    $_useTime = G('cronStart', 'cronEnd', 6);//计算CRON任务执行时间

                    $cron[2] = $_SERVER['REQUEST_TIME'] + $cron[1];//更新下一次任务开始执行时间

                    $crons[$key] = $cron;

                    $log[] = 'Cron:' . $key . ' Runat ' . date('Y-m-d H:i:s') . ' Use ' . $_useTime . ' s ' . "\r\n";

                    $update = true;
                }
            }

            if ($update) 
			{
                \Think\Log::write(implode('', $log));

                F('CRON_CONFIG', $crons);
            }
        }
    }
}
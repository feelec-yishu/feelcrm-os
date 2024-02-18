<?php
$config = array
(
	"SLA"=>[
		"condition" =>[
			[
				"condition_id"	=>1,
				"source"		=> 'status_id',
				"name"			=> '工单：状态',
				"name_en"		=> 'Ticket: Status',
				"name_jp"		=> 'タスク: 状態'
			],

            [
                "condition_id"	=>2,
                "source"		=> 'customer_level_id',
                "name"			=> '会员：会员级别',
                "name_en"		=> 'Member: Member level',
                "name_jp"		=> 'メンバー：メンバーレベル'
            ],

            [
                "condition_id"	=>3,
                "source"		=> 'ticket_model_id',
                "name"			=> '工单：模板',
                "name_en"		=> 'Ticket: Template',
                "name_jp"		=> 'タスク：テンプレート'
            ],
			[
				"condition_id"	=>4,
				"source"		=> 'type_id',
				"name"			=> '模板：类型',
                "name_en"		=> 'Ticket: Type',
                "name_jp"		=> 'テンプレート: タイプ'
			],
			[
				"condition_id"	=>5,
				"source"		=> 'group_id',
				"name"			=> '受理客服组',
                "name_en"		=> 'Assigned department',
                "name_jp"		=> '顧客サービスグループを承認する'
			],
			[
				"condition_id"	=>6,
				"source"		=> 'member_id',
				"name"			=> '工单：发起人',
                "name_en"		=> 'Ticket: Creator',
                "name_jp"		=> 'タスク：プロモーター'
			],
            [
                "condition_id"	=>7,
                "source"		=> 'recipient_id',
                "name"			=> '工单：受理客服',
                "name_en"		=> 'Ticket: Recipient',
                "name_jp"		=> 'タスク：顧客サービスを受け入れる'
            ],
			[
				"condition_id"	=>8,
				"source"		=> 'dispose_id',
				"name"			=> '工单：处理客服',
                "name_en"		=> 'Ticket: Assignee',
                "name_jp"		=> 'タスク：カスタマーサービス'
			],
			[
				"condition_id"	=>9,
				"source"		=> 'ticket_from',
				"name"			=> '工单：来源',
                "name_en"		=> 'Ticket: Source',
                "name_jp"		=> 'タスク：ソース'
			]
		],
		"operator"  =>[
			[
				"operat_id"		=>1,
				"cids"			=>[1,2,3,4,5,6,7,8,9],
				"operator"		=>"is",
				"name"			=>"是",
                "name_en"		=>'Yes',
                "name_jp"		=> 'はい'
			],
			[
				"operat_id"		=>2,
				"cids"			=>[1,2,3,4,5,6,7,8,9],
				"operator"		=>"is_not",
				"name"			=>"不是",
                "name_en"		=>'Not',
                "name_jp"		=> 'ない'
			]
		],
		"period"    =>[
			[
				"name"		=>"0分钟",
				"name_en"	=>'0 minutes',
				"name_jp"	=>'0分',
				"value"		=> 0
			],
			[
				"name"		=>"1分钟",
				"name_en"	=>'1 minutes',
				"name_jp"	=>'1分',
				"value"		=> 1
			],
			[
				"name"		=>"2分钟",
				"name_en"	=>'2 minutes',
				"name_jp"	=>'2分',
				"value"		=> 2
			],
			[
				"name"		=>"3分钟",
				"name_en"	=>'3 minutes',
				"name_jp"	=>'3分',
				"value"		=> 3
			],
			[
				"name"		=>"4分钟",
				"name_en"	=>'4 minutes',
				"name_jp"	=>'4分',
				"value"		=> 4
			],
			[
				"name"		=>"5分钟",
				"name_en"	=>'5 minutes',
				"name_jp"	=>'5分',
				"value"		=> 5
			],
			[
				"name"		=>"6分钟",
				"name_en"	=>'6 minutes',
				"name_jp"	=>'6分',
				"value"		=> 6
			],
			[
				"name"		=>"7分钟",
				"name_en"	=>'7 minutes',
				"name_jp"	=>'7分',
				"value"		=> 7
			],
			[
				"name"		=>"8分钟",
				"name_en"	=>'8 minutes',
				"name_jp"	=>'8分',
				"value"		=> 8
			],
			[
				"name"		=>"9分钟",
				"name_en"	=>'9 minutes',
				"name_jp"	=>'9分',
				"value"		=> 9
			],
            [
                "name"		=>"10分钟",
                "name_en"	=>'10 minutes',
                "name_jp"	=>'10分',
                "value"		=> 10
            ],
            [
                "name"		=>"15分钟",
                "name_en"	=>'15 minutes',
                "name_jp"	=>'15分',
                "value"		=> 15
            ],
            [
                "name"		=>"20分钟",
                "name_en"	=>'20 minutes',
                "name_jp"	=>'20分',
                "value"		=> 20
            ],
            [
                "name"		=>"30分钟",
                "name_en"	=>'30 minutes',
                "name_jp"	=>'30分',
                "value"		=> 30
            ],
            [
                "name"		=>"40分钟",
                "name_en"	=>'40 minutes',
                "name_jp"	=>'40分',
                "value"		=> 40
            ],
            [
                "name"		=>"50分钟",
                "name_en"	=>'50 minutes',
                "name_jp"	=>'50分',
                "value"		=> 50
            ],
            [
                "name"		=>"1小时",
                "name_en"	=>'1 hour',
                "name_jp"	=>'1時間',
                "value"		=> 60
            ],
            [
                "name"		=>"2小时",
                "name_en"	=>'2 hour',
                "name_jp"	=>'1時間',
                "value"		=> 120
            ],
            [
                "name"		=>"5小时",
                "name_en"	=>'5 hour',
                "name_jp"	=>'5時間',
                "value"		=> 300
            ],
            [
                "name"		=>"10小时",
                "name_en"	=>'10 hour',
                "name_jp"	=>'10時間',
                "value"		=> 600
            ],
            [
                "name"		=>"1天",
                "name_en"	=>'1 day',
                "name_jp"	=>'1日',
                "value"		=> 24*60
            ],
            [
                "name"		=>"10天",
                "name_en"	=>'10 day',
                "name_jp"	=>'10日',
                "value"		=> 10*24*60
            ],
		],
		"hour"    	=>[
			[
				"name"		=>"00",
				"value"		=> 0
			],
			[
				"name"		=>"01",
				"value"		=> 1
			],
			[
				"name"		=>"02",
				"value"		=> 2
			],
			[
				"name"		=>"03",
				"value"		=> 3
			],
			[
				"name"		=>"04",
				"value"		=> 4
			],
			[
				"name"		=>"05",
				"value"		=> 5
			],
			[
				"name"		=>"06",
				"value"		=> 6
			],
			[
				"name"		=>"07",
				"value"		=> 7
			],
			[
				"name"		=>"08",
				"value"		=> 8
			],
			[
				"name"		=>"09",
				"value"		=> 9
			],
			[
				"name"		=>"10",
				"value"		=> 10
			],
			[
				"name"		=>"11",
				"value"		=> 11
			],
			[
				"name"		=>"12",
				"value"		=> 12
			],
			[
				"name"		=>"13",
				"value"		=> 13
			],
			[
				"name"		=>"14",
				"value"		=> 14
			],
			[
				"name"		=>"15",
				"value"		=> 15
			],
			[
				"name"		=>"16",
				"value"		=> 16
			],
			[
				"name"		=>"17",
				"value"		=> 17
			],
			[
				"name"		=>"18",
				"value"		=> 18
			],
			[
				"name"		=>"19",
				"value"		=> 19
			],
			[
				"name"		=>"20",
				"value"		=> 20
			],
			[
				"name"		=>"21",
				"value"		=> 21
			],
			[
				"name"		=>"22",
				"value"		=> 22
			],
			[
				"name"		=>"23",
				"value"		=> 23
			],
			[
				"name"		=>"24",
				"value"		=> 24
			],
			[
				"name"		=>"48",
				"value"		=> 48
			],
			[
				"name"		=>"72",
				"value"		=> 72
			],
			[
				"name"		=>"5*24",
				"value"		=> 120
			],
            [
                "name"		=>"7*24",
                "value"		=> 168
            ],
            [
                "name"		=>"15*24",
                "value"		=> 360
            ]
		],
		"minute"    =>[
			[
				"name"		=>"00",
				"value"		=> 0
			],
			[
				"name"		=>"05",
				"value"		=> 05
			],
			[
				"name"		=>"10",
				"value"		=> 10
			],
			[
				"name"		=>"15",
				"value"		=> 15
			],
			[
				"name"		=>"20",
				"value"		=> 20
			],
			[
				"name"		=>"25",
				"value"		=> 25
			],
			[
				"name"		=>"30",
				"value"		=> 30
			],
			[
				"name"		=>"35",
				"value"		=> 35
			],
			[
				"name"		=>"40",
				"value"		=> 40
			],
			[
				"name"		=>"45",
				"value"		=> 45
			],
			[
				"name"		=>"50",
				"value"		=> 50
			],
			[
				"name"		=>"55",
				"value"		=> 55
			]
		],
	]
);

$lang = cookie('think_language');

foreach($config as $k=>&$v)
{
    foreach($v['condition'] as &$c)
    {
        if($lang == 'en-us') $c['name'] = $c['name_en'];

        if($lang == 'ja-jp') $c['name'] = $c['name_jp'];
    }

    foreach($v['operator'] as &$o)
    {
        if($lang == 'en-us') $o['name'] = $o['name_en'];

        if($lang == 'ja-jp') $o['name'] = $o['name_jp'];
    }

    foreach($v['period'] as &$o)
    {
        if($lang == 'en-us') $o['name'] = $o['name_en'];

        if($lang == 'ja-jp') $o['name'] = $o['name_jp'];
    }
}

return $config;

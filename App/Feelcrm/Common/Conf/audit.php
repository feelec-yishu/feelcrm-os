<?php
$config = [
	"RULE"=>[
		"scenes"   =>[
			[
				'name'      => '发布工单',
				'name_en'   => 'Create ticket',
				'name_jp'   => '发布工单',
				'value'     => 'create_ticket',
			],
			[
				'name'      => '结束工单',
				'name_en'   => 'End ticket',
				'name_jp'   => '结束工单',
				'value'     => 'end_ticket',
			]
		],
		"condition"=>[
			[
				"name"			=> '工单：全部',
				"name_en"		=> 'Ticket: All',
				"name_jp"		=> 'テンプレート: すべて',
				"value"		    => 'all',
			],
			[
				"name"			=> '工单：类型',
				"name_en"		=> 'Ticket: Type',
				"name_jp"		=> 'テンプレート: タイプ',
				"value"		    => 'type_id',
			],
			[
				"name"			=> '工单：模板',
				"name_en"		=> 'Ticket: Template',
				"name_jp"		=> 'タスク：テンプレート',
				"value"		    => 'ticket_model_id',
			],
			[
				"name"			=> '用户：角色',
				"name_en"		=> 'User: Role',
				"name_jp"		=> 'ユーザー：役割',
				"value"		    => 'role_id',
			]
		],
		"object"   =>[
			[
				'name'      => '内部员工',
				'name_en'   => 'Internal staff',
				'name_jp'   => '内部员工',
				'value'     => 'user',
			],
			[
				'name'      => '会员',
				'name_en'   => 'Member',
				'name_jp'   => '会员',
				'value'     => 'member',
			]
		]
	],
	'PROCESS'=>[
		"level"   =>[
			[
				'name'      => '部门主管审核',
				'name_en'   => 'Department review',
				'name_jp'   => '部門長レビュー',
				'value'     => '10',
			],
			[
				'name'      => '指定审核人员（任意）',
				'name_en'   => 'Designated auditor (optional)',
				'name_jp'   => '指定監査人（オプション）',
				'value'     => '20',
			],
			[
				'name'      => '指定审核人员（会审）',
				'name_en'   => 'Designated auditor (review)',
				'name_jp'   => '指定審査員（レビュー）',
				'value'     => '30',
			]
		]
	]
];

$lang = cookie('think_language');

foreach($config as $k=>&$v)
{
	if($k == 'RULE')
	{
		foreach($v['scenes'] as &$s)
		{
			if($lang == 'en-us') $s['name'] = $s['name_en'];

			if($lang == 'ja-jp') $s['name'] = $s['name_jp'];

		}

		foreach($v['condition'] as &$c)
		{
			if($lang == 'en-us') $c['name'] = $c['name_en'];

			if($lang == 'ja-jp') $c['name'] = $c['name_jp'];
		}

		foreach($v['object'] as &$o)
		{
			if($lang == 'en-us') $o['name'] = $o['name_en'];

			if($lang == 'ja-jp') $o['name'] = $o['name_jp'];
		}
	}

	if($k == 'PROCESS')
	{
		foreach($v['level'] as &$l)
		{
			if($lang == 'en-us') $l['name'] = $l['name_en'];

			if($lang == 'ja-jp') $l['name'] = $l['name_jp'];
		}
	}
}

return $config;

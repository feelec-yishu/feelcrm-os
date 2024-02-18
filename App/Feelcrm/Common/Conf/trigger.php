<?php
$config = array
(
	"TRIGGER"=>[
		"condition"=>[
			[
				"condition_id"	=>3,
				"source"		=> 'been',
				"name"			=> '工单',
				"name_en"		=> 'Ticket',
				"name_jp"		=> 'タスク'
			],
			[
				"condition_id"	=>1,
				"source"		=> 'status_id',
				"name"			=> '工单：状态',
				"name_en"		=> 'Ticket: Status',
				"name_jp"		=> 'タスク: 状態'
			],
			[
				"condition_id"	=>10,
				"source"		=> 'ticket_model_id',
				"name"			=> '工单：模板',
				"name_en"		=> 'Ticket: Template',
				"name_jp"		=> 'タスク：テンプレート'
			],
			[
				"condition_id"	=>2,
				"source"		=> 'type_id',
				"name"			=> '模板：类型',
				"name_en"		=> 'Template: Type',
				"name_jp"		=> 'テンプレート: タイプ'
			],
			[
				"condition_id"	=>4,
				"source"		=> 'priority',
				"name"			=> '工单：优先级',
				"name_en"		=> 'Ticket: Priority',
				"name_jp"		=> 'タスク：優先度'
			],
			[
				"condition_id"	=>5,
				"source"		=> 'group_id',
				"name"			=> '工单：处理客服组',//工单直属的部门，只有一个值
				"name_en"		=> 'Ticket: Assigned Department',
				"name_jp"		=> 'タスク：顧客サービスグループを承認する'
			],
			[
				"condition_id"	=>12,
				"source"		=> 'publisher_group_id',
				"name"			=> '工单：发起人部门',
				"name_en"		=> 'Ticket: Sponsor Department',
				"name_jp"		=> 'タスク：スポンサー部門'
			],
			[
				"condition_id"	=>13,
				"source"		=> 'handler_group_id',
				"name"			=> '工单：处理人部门',//工单处理人所属的部门，多个值
				"name_en"		=> 'Ticket: Processing Department',
				"name_jp"		=> 'タスク：加工部門'
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
				"name"			=> '工单：接收人',
				"name_en"		=> 'Ticket: Recipient',
				"name_jp"		=> 'タスク：顧客サービスを受け入れる'
			],
			[
				"condition_id"	=>8,
				"source"		=> 'dispose_id',
				"name"			=> '工单：处理人',
				"name_en"		=> 'Ticket: Assignee',
				"name_jp"		=> 'タスク：カスタマーサービス'
			],
			[
				"condition_id"	=>11,
				"source"		=> 'cc',
				"name"			=> '工单：抄送人',
				"name_en"		=> 'Ticket: Cc',
				"name_jp"		=> 'タスク：Cc'
			],
			[
				"condition_id"	=>9,
				"source"		=> 'ticket_from',
				"name"			=> '工单：来源',
				"name_en"		=> 'Ticket: Source',
				"name_jp"		=> 'タスク：ソース'
			]
		],
		"operator"=>[
			[
				"operat_id"		=>1,
				"cids"			=>[1,2,3,4,5,6,7,8,9,10],
				"operator"		=>"is",
				"name"			=>"是",
				"name_en"		=>'Yes',
				"name_jp"		=> 'はい'
			],
			[
				"operat_id"		=>2,
				"cids"			=>[1,2,4,5,6,7,8,9,10],
				"operator"		=>"is_not",
				"name"			=>"不是",
				"name_en"		=>'Not',
				"name_jp"		=> 'ない'
			],
			[
				"operat_id"		=>3,
				"cids"			=>[1,4],
				"operator"		=>"less_than",
				"name"			=>"小于",
				"name_en"		=>'Less than',
				"name_jp"		=> 'より小さい'
			],
			[
				"operat_id"		=>4,
				"cids"			=>[1,4],
				"operator"		=>"greater_than",
				"name"			=>"大于",
				"name_en"		=>'Greater than',
				"name_jp"		=> 'より大きい'
			],
			[
				"operat_id"		=>5,
				"cids"			=>[1,5,7],
				"operator"		=>"changed",
				"name"			=>"已改变",
				"name_en"		=>'Changed',
				"name_jp"		=> '変更されました'
			],
			[
				"operat_id"		=>6,
				"cids"			=>[1,5,7],
				"operator"		=>"changed_to",
				"name"			=>"改变为...",
				"name_en"		=>'Change to...',
				"name_jp"		=> 'に変更...'
			],
			[
				"operat_id"		=>7,
				"cids"			=>[1,5,7],
				"operator"		=>"changed_from",
				"name"			=>"从...改变",
				"name_en"		=>'Change from...',
				"name_jp"		=> 'からの変更...'
			],
			[
				"operat_id"		=>8,
				"cids"			=>[1,5,7],
				"operator"		=>"not_changed",
				"name"			=>"未改变",
				"name_en"		=>'Unchanged',
				"name_jp"		=> '変わらない'
			],
			[
				"operat_id"		=>9,
				"cids"			=>[1,5,7],
				"operator"		=>"not_changed_to",
				"name"			=>"未改变为...",
				"name_en"		=>'Unchanged to...',
				"name_jp"		=> 'に変更されていない...'
			],
			[
				"operat_id"		=>10,
				"cids"			=>[1,5,7],
				"operator"		=>"not_changed_from",
				"name"			=>"不是从...改变",
				"name_en"		=>'Not change from...',
				"name_jp"		=> 'からの変更...変更'
			],
			[
				"operat_id"		=>11,
				"cids"			=>[11,12,13],
				"operator"		=>"contain",
				"name"			=>"包含",
				"name_en"		=>'contain',
				"name_jp"		=> '含む'
			]
		],
		"action"=>[
			[
				"condition_id"	=>1,
				"source"		=> 'status_id',
				"name"			=> '工单：状态',
				"name_en"		=> 'Ticket: Status',
				"name_jp"		=> 'タスク: 状態'
			],
			[
				"condition_id"	=>2,
				"source"		=> 'priority',
				"name"			=> '工单：优先级',
				"name_en"		=> 'Ticket: Priority',
				"name_jp"		=> 'タスク：優先度'
			],
			[
				"condition_id"	=>3,
				"source"		=> 'group_id',
				"name"			=> '工单：处理部门',
				"name_en"		=> 'Ticket: Processing department',
				"name_jp"		=> 'タスク：顧客サービスグループを承認する'
			],
			[
				"condition_id"	=>4,
				"source"		=> 'recipient_id',
				"name"			=> '工单：接收人',
				"name_en"		=> 'Ticket: Recipient',
				"name_jp"		=> 'タスク：顧客サービスを受け入れる'
			],
			[
				"condition_id"	=>5,
				"source"		=> 'dispose_id',
				"name"			=> '工单：处理人',
				"name_en"		=> 'Ticket: Assignee',
				"name_jp"		=> 'タスク：カスタマーサービス'
			],
			[
				"condition_id"	=>10,
				"source"		=> 'cc_group_id',
				"name"			=> '抄送：抄送部门',
				"name_en"		=> 'Cc:department',
				"name_jp"		=> 'Cc:抄送部门'
			],
			[
				"condition_id"	=>6,
				"source"		=> 'email_customer',
				"name"			=> '通知：发邮件给会员',
				"name_en"		=> 'Notice: Send an email to customer',
				"name_jp"		=> 'お知らせ：顧客にEメールする'
			],
			[
				"condition_id"	=>7,
				"source"		=> 'message_customer',
				"name"			=> '通知：发消息给会员',
				"name_en"		=> 'Notice: Send a message to customer',
				"name_jp"		=> 'お知らせ：顧客にメッセージを送信する'
			],
			[
				"condition_id"	=>8,
				"source"		=> 'email_user',
				"name"			=> '通知：发邮件给用户',
				"name_en"		=> 'Notice: Send an email to user',
				"name_jp"		=> 'お知らせ：ユーザーへの電子メール'
			],
			[
				"condition_id"	=>9,
				"source"		=> 'message_user',
				"name"			=> '通知：发消息给用户',
				"name_en"		=> 'Notice: Send a message to user',
				"name_jp"		=> 'お知らせ：ユーザーにメッセージを送信する'
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

	foreach($v['action'] as &$a)
	{
		if($lang == 'en-us') $a['name'] = $a['name_en'];

		if($lang == 'ja-jp') $a['name'] = $a['name_jp'];
	}
}

return $config;

<?php

$config = [

	"ORDERBY"=>[
		"1"	=>	'ticket_id',

		"2"	=>	'status_id',

		"3"	=>	'title',

		"4"	=>	'priority',

		"5"	=>	'type_id',

		"6"	=>	'ticket_model_id',

		"7"	=>	'member_id',

		"8"	=>	'create_time',

		"11"=>	'dispose_id',

		"12"=>	'update_time',

		"13"=>	'dispose_time',

		"14"=>	'ticket_no'
	],
    'EXPORT'=>[
        [
            'identity'  => 'ticket_from',
            'name'      => '工单来源',
            'name_en'   => 'Source of ticket',
            'name_jp'   => 'タスク参照元'
        ],
        [
            'identity'  => 'ticket_no',
            'name'      => '工单编号',
            'name_en'   => 'Ticket Number',
            'name_jp'   => 'タスク番号'
        ],
        [
            'identity'  => 'title',
            'name'      => '工单标题',
            'name_en'   => 'Ticket title',
            'name_jp'   => 'タスクタイトル'
        ],
        [
            'identity'  => 'status_id',
            'name'      => '工单状态',
            'name_en'   => 'Ticket status',
            'name_jp'   => 'ステータス'
        ],
        [
            'identity'  => 'ticket_model_id',
            'name'      => '工单模板',
            'name_en'   => 'Ticket template',
            'name_jp'   => 'タスクテンプレート'
        ],
        [
            'identity'  => 'priority',
            'name'      => '优先级',
            'name_en'   => 'Priority',
            'name_jp'   => '優先度'
        ],
        [
            'identity'  => 'member_id',
            'name'      => '发布人',
            'name_en'   => 'Publish',
            'name_jp'   => '登録者'
        ],
	    [
		    'identity'  => 'mobile_discrete',
		    'name'      => '手机号码',
		    'name_en'   => 'Cell phone',
		    'name_jp'   => '携帯の番号'
	    ],
	    [
		    'identity'  => 'mail_discrete',
		    'name'      => '电子邮箱',
		    'name_en'   => 'E-mail',
		    'name_jp'   => '電子メールアドレス'
	    ],
        [
            'identity'  => 'dispose_id',
            'name'      => '处理人',
            'name_en'   => 'Acceptance',
            'name_jp'   => '処理者'
        ],
        [
            'identity'  => 'create_time',
            'name'      => '发布时间',
            'name_en'   => 'Release Time',
            'name_jp'   => '登録時間'
        ],
	    [
		    'identity'  => 'end_time',
		    'name'      => '结束时间',
		    'name_en'   => 'End Time',
		    'name_jp'   => '終了時間'
	    ],
	    [
		    'identity'  => 'ticket_subject_id',
		    'name'      => '工单主题',
		    'name_en'   => 'Ticket subject',
		    'name_jp'   => 'テーマ'
	    ],
	    [
		    'identity'  => 'timeout',
		    'name'      => '是否超时',
		    'name_en'   => 'Whether timeout',
		    'name_jp'   => 'タイムアウト'
	    ],
	    [
		    'identity'  => 'timeout_period',
		    'name'      => '超时时长',
		    'name_en'   => 'Timeout period',
		    'name_jp'   => 'タイムアウト期間'
	    ],
	    [
		    'identity'  => 'reply_content',
		    'name'      => '回复内容',
		    'name_en'   => 'Reply content',
		    'name_jp'   => '返信内容'
	    ]
    ],
//	未购买增值服务，请勿修改
	'VALUE_ADDED_SERVICES'  => [
		'project'   => true,//项目管理
		'feelchat'  => true,//内部即时通信
	],
//	服务报告模板
	'PDF_TEMP'=>[
		'template'  => "<div class='feeldesk-pdf-body'>
							<div class='feeldesk-pdf-wrapper'>
								<div class='feeldesk-pdf-header'>{{header}}</div>
								{{detail_content}}
								{{reply_content}}
							</div>
						</div>",
		'detail'    =>"
					<div class='feeldesk-detail-content'>
                        <ul class='detail-item'>
                            <li class='clearfix'><div class='item-div1 blue'>{{title}}</div><div class='item-div2 blue'>{{ticket.title}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{status}}</div><div class='item-div2'>{{ticket.status}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{priority}}</div><div class='item-div2'>{{ticket.priority}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{publish}}</div><div class='item-div2'>{{ticket.publish}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{handler}}</div><div class='item-div2'>{{ticket.handler}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{create_time}}</div><div class='item-div2'>{{ticket.create_time}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{dispose_time}}</div><div class='item-div2'>{{ticket.dispose_time}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{end_time}}</div><div class='item-div2'>{{ticket.end_time}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{take_time}}</div><div class='item-div2'>{{ticket.take_time}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{is_timeout}}</div><div class='item-div2'>{{ticket.is_timeout}}</div></li>
                            <li class='clearfix'><div class='item-div1'>{{timeout}}</div><div class='item-div2'>{{ticket.timeout}}</div></li>
                            {{ticket.ticket_form}}
                        </ul>
					</div>",

		"reply"     =>"
					<div class='feeldesk-reply-item'>
						<div class='item'>
							<div class='content-header'>
							<img src='{{face}}' alt='' width='30' style='margin-right:10px'><span>{name}</span><span>· {{reply_time}}</span></div>
							<div class='reply-content'><div class='content replyImage'>{{reply_content}}</div></div>
							<div class='reply-attach'>{{reply_attach}}</div>
						</div>
					</div>"
	]
];

$lang = cookie('think_language');

foreach($config['EXPORT'] as &$v)
{
    if($lang == 'en-us') $v['name'] = $v['name_en'];

    if($lang == 'ja-jp') $v['name'] = $v['name_jp'];
}

return $config;
<?php
return array
(
    'CRM_TEMP_CN' =>
    [
//        邮件通知模板
        "ALLOT_CUSTOMER"		=> [
            'title'		=> '客户分配通知',
            'content'	=> "客户 <span style='color:#FF5722'>{{customer.name}}</span>  已分配给您，请及时关注！<br/>联系电话：{{customer.phone}}<br/>邮箱：{{customer.email}}"
        ],
		"DRAW_CUSTOMER"		=> [
            'title'		=> '客户领取通知',
            'content'	=> "您已成功领取客户 <span style='color:#FF5722'>{{customer.name}}</span>  ，请及时关注！<br/>联系电话：{{customer.phone}}<br/>邮箱：{{customer.email}}"
        ],
		"TRANSFER_CUSTOMER"		=> [
            'title'		=> '客户转移通知',
            'content'	=> "客户 <span style='color:#FF5722'>{{customer.name}}</span>  已转移给您，请及时关注！<br/>联系电话：{{customer.phone}}<br/>邮箱：{{customer.email}}"
        ],
		"FOLLOW_CUSTOMER"		=> [
            'title'		=> '客户待跟进通知',
            'content'	=> "您有客户 <span style='color:#FF5722'>{{customer.name}}</span>  需要跟进，请及时处理！<br/>联系电话：{{customer.phone}}<br/>邮箱：{{customer.email}}"
        ],
		"NEW_CUSTOMER"		=> [
            'title'		=> '新客户通知',
            'content'	=> "有新客户 <span style='color:#FF5722'>{{customer.name}}</span>  录入客户池，请及时处理！<br/>联系电话：{{customer.phone}}<br/>邮箱：{{customer.email}}"
        ],
		"NEW_ORDER"		=> [
            'title'		=> '新订单通知',
            'content'	=> "有新订单 <span style='color:#FF5722'>{{order.order_no}}</span> 已被添加，请及时关注！<br/>订单名称：{{order.name}}<br/>所属客户：{{order.customer_name}}"
        ],
		"NOFOLLOW_CUSTOMER"		=> [
            'title'		=> '客户未跟进通知',
            'content'	=> "有客户 <span style='color:#FF5722'>{{customer.name}}</span>  跟进超时，请查看处理！<br/>联系电话：{{customer.phone}}<br/>邮箱：{{customer.email}}"
        ],
		"NORETURNVISIT_CUSTOMER"		=> [
            'title'		=> '客户未回访通知',
            'content'	=> "有客户 <span style='color:#FF5722'>{{customer.name}}</span>  回访超时，请查看处理！<br/>联系电话：{{customer.phone}}<br/>邮箱：{{customer.email}}"
        ],
		"NEW_CONTRACT"		=> [
            'title'		=> '合同审核提醒',
            'content'	=> "您有新的合同需要审核，请查看处理！ <br/>合同编号：<span style='color:#FF5722'>{{contract.contract_no}}</span> <br/>合同名称：{{contract.name}}<br/>所属客户：{{contract.customer_name}}"
        ],
		"CONTRACT_EXPIRE"		=> [
            'title'		=> '合同到期提醒',
            'content'	=> "合同 <span style='color:#FF5722'>{{contract.contract_no}}</span> 即将到期，请及时关注！<br/>合同名称：{{contract.name}}<br/>所属客户：{{contract.customer_name}}"
        ],
		"RECEIPT_EXAMINE"		=> [
            'title'		=> '收款审核提醒',
            'content'	=> "您有一笔新的收款需要审核 ，请查看审核！ <br/>收款编号：<span style='color:#FF5722'>{{receipt.receipt_no}}</span><br/>收款金额：{{receipt.receipt_money}}<br/>关联合同：{{contract.name}}<br/>所属客户：{{contract.customer_name}}"
        ],
		"INVOICE_EXAMINE"		=> [
            'title'		=> '发票审核提醒',
            'content'	=> "您有一张新的发票需要审核，请查看审核！<br/>发票编号：<span style='color:#FF5722'>{{invoice.invoice_no}}</span><br/>开票金额：{{invoice.invoice_money}}<br/>关联合同：{{contract.name}}<br/>所属客户：{{contract.customer_name}}"
        ],
		"EXAMINE_OPERATE"		=> [
            'title'		=> '审核结果通知',
            'content'	=> "您提交的{{examine.type}} <span style='color:#FF5722'>{{examine.examine_no}}</span> 已被{{examine.operate_type}}，请注意查看！<br/>{{examine.operate_type}}原因：{{examine.reason}}<br/>关联合同：{{contract.name}}<br/>所属客户：{{contract.customer_name}}"
        ],
        "ALLOT_CLUE"		=> [
	        'title'		=> '线索分配通知',
	        'content'	=> "线索 <span style='color:#FF5722'>{{clue.name}}</span>  已分配给您，请及时关注！<br/>联系电话：{{clue.phone}}<br/>邮箱：{{clue.email}}"
        ],
        "DRAW_CLUE"		=> [
	        'title'		=> '线索领取通知',
	        'content'	=> "您已成功领取线索 <span style='color:#FF5722'>{{clue.name}}</span>  ，请及时关注！<br/>联系电话：{{clue.phone}}<br/>邮箱：{{clue.email}}"
        ],
        "TRANSFER_CLUE"		=> [
	        'title'		=> '线索转移通知',
	        'content'	=> "线索 <span style='color:#FF5722'>{{clue.name}}</span>  已转移给您，请及时关注！<br/>联系电话：{{clue.phone}}<br/>邮箱：{{clue.email}}"
        ],
        "FOLLOW_CLUE"		=> [
	        'title'		=> '线索待跟进通知',
	        'content'	=> "您有线索 <span style='color:#FF5722'>{{clue.name}}</span>  需要跟进，请及时处理！<br/>联系电话：{{clue.phone}}<br/>邮箱：{{clue.email}}"
        ],
        "NEW_CLUE"		=> [
	        'title'		=> '新线索通知',
	        'content'	=> "有新线索 <span style='color:#FF5722'>{{clue.name}}</span>  录入线索池，请及时处理！<br/>联系电话：{{clue.phone}}<br/>邮箱：{{clue.email}}"
        ],
        "FOLLOW_COMMENT"		=> [
	        'title'		=> '联系记录评论通知',
	        'content'	=> "您添加的联系记录有新的评论，请及时关注！<br/>联系对象：{{follow.type}}<br/>{{follow.type}}名称：{{follow.name}}<br/>联系记录：{{follow.content}}<br/>评论内容：{{follow.comment_content}}"
        ],

//        微信通知模板

        'ALLOT_CUSTOMER_WECHAT' => [
            'first'   => '您分配到一个新的客户',
            'remark'  => '请及时关注！'
        ],

        'DRAW_CUSTOMER_WECHAT' => [
            'first'   => '您已成功领取客户',
            'remark'  => '请及时关注！'
        ],

        'TRANSFER_CUSTOMER_WECHAT' => [
            'first'   => '有新的客户转移给您',
            'remark'  => '请及时关注！'
        ],

        'FOLLOW_CUSTOMER_WECHAT' => [
            'first'   => '您有一个客户需要跟进',
            'remark'  => '请及时处理！'
        ],

        'NEW_CUSTOMER_WECHAT' => [
            'first'   => '有新客户录入客户池',
            'remark'  => '请及时处理！'
        ],

        'NEW_ORDER_WECHAT' => [
            'first'   => '有新订单已被添加',
            'remark'  => '请及时关注！'
        ],
		'NOFOLLOW_CUSTOMER_WECHAT' => [
            'first'   => '有客户已跟进超时',
            'remark'  => '请查看处理！'
        ],
		'NORETURNVISIT_CUSTOMER_WECHAT' => [
            'first'   => '有客户已回访超时',
            'remark'  => '请查看处理！'
        ],
		'NEW_CONTRACT_WECHAT' => [
            'first'   => '有新合同需要审核',
            'remark'  => '请查看处理！'
        ],
		'CONTRACT_EXPIRE_WECHAT' => [
            'first'   => '有合同即将到期',
            'remark'  => '请及时关注！'
        ],
		'RECEIPT_EXAMINE_WECHAT' => [
			'first'   => '有新的收款需要审核',
            'remark'  => '请查看处理！'
		],
		'INVOICE_EXAMINE_WECHAT' => [
			'first'   => '有新的发票需要审核',
            'remark'  => '请查看处理！'
		],
		'EXAMINE_OPERATE_WECHAT' => [
			'first'   => '您提交的信息已被处理',
            'remark'  => '请注意查看！'
		],

        'ALLOT_CLUE_WECHAT' => [
	        'first'   => '您分配到一个新的线索',
	        'remark'  => '请及时关注！'
        ],

        'DRAW_CLUE_WECHAT' => [
	        'first'   => '您已成功领取线索',
	        'remark'  => '请及时关注！'
        ],

        'TRANSFER_CLUE_WECHAT' => [
	        'first'   => '有新的线索转移给您',
	        'remark'  => '请及时关注！'
        ],

        'FOLLOW_CLUE_WECHAT' => [
	        'first'   => '您有一个线索需要跟进',
	        'remark'  => '请及时处理！'
        ],

        'NEW_CLUE_WECHAT' => [
	        'first'   => '有新线索录入线索池',
	        'remark'  => '请及时处理！'
        ],
        'FOLLOW_COMMENT_WECHAT' => [
	        'first'   => '您添加的联系记录有新的评论',
	        'remark'  => '请及时关注！'
        ],

//        短信通知模板

        'ALLOT_CUSTOMER_SMS' =>"客户分配通知：您好，客户 {{customer.name}}  已分配给您，请及时关注！ 联系电话：{{customer.phone}} 邮箱：{{customer.email}}",

        'DRAW_CUSTOMER_SMS' =>"客户领取通知：您好，您已成功领取客户 {{customer.name}}  ，请及时关注！ 联系电话：{{customer.phone}} 邮箱：{{customer.email}}",

        'TRANSFER_CUSTOMER_SMS'  =>"客户转移通知：您好，客户 {{customer.name}}  已转移给您，请及时关注！ 联系电话：{{customer.phone}} 邮箱：{{customer.email}}",

        'FOLLOW_CUSTOMER_SMS' =>"客户待跟进通知：您好，客户 {{customer.name}}  需要跟进，请及时处理！ 联系电话：{{customer.phone}} 邮箱：{{customer.email}}",

        'NEW_CUSTOMER_SMS'     =>"新客户通知：您好，有新客户 {{customer.name}}  录入客户池，请及时处理! 联系电话：{{customer.phone}} 邮箱：{{customer.email}}",

        'NEW_ORDER_SMS' =>"新订单通知：您好，有新订单 {{order.order_no}} 添加成功，请及时关注! 订单名称：{{order.name}} 所属客户：{{order.customer_name}}",
		
		'NOFOLLOW_CUSTOMER_SMS' =>"客户未跟进通知：客户 {{customer.name}} 跟进超时，请查看处理! 联系电话：{{customer.phone}} 邮箱：{{customer.email}}",
		
		'NORETURNVISIT_CUSTOMER_SMS' =>"客户未回访通知：客户 {{customer.name}} 回访超时，请查看处理! 联系电话：{{customer.phone}} 邮箱：{{customer.email}}",
		
		'NEW_CONTRACT_SMS' =>"审核提醒：您有新的合同需要审核，请查看处理！ 合同名称：{{contract.name}} 所属客户：{{contract.customer_name}}",
		
		'CONTRACT_EXPIRE_SMS' =>"合同到期通知：您好，合同 {{contract.contract_no}} 即将到期，请及时关注！ 合同名称：{{contract.name}} 所属客户：{{contract.customer_name}}",
		
		'RECEIPT_EXAMINE_SMS' =>"审核提醒：您有新的收款需要审核 ，请查看处理！  收款金额：{{receipt.receipt_money}} 关联合同：{{contract.name}} 所属客户：{{contract.customer_name}}",
		
		'INVOICE_EXAMINE_SMS' =>"审核提醒：您有新的发票需要审核 ，请查看处理！ 开票金额：{{invoice.invoice_money}} 关联合同：{{contract.name}} 所属客户：{{contract.customer_name}}",
		
		'EXAMINE_OPERATE_SMS' =>"审核通知：您提交的{{examine.type}} 已被{{examine.operate_type}}，请注意查看！ {{examine.operate_type}}原因：{{examine.reason}} ",

        'ALLOT_CLUE_SMS' =>"线索分配通知：您好，线索 {{clue.name}}  已分配给您，请及时关注！ 联系电话：{{clue.phone}} 邮箱：{{clue.email}}",

        'DRAW_CLUE_SMS' =>"线索领取通知：您好，您已成功领取线索 {{clue.name}}  ，请及时关注！ 联系电话：{{clue.phone}} 邮箱：{{clue.email}}",

        'TRANSFER_CLUE_SMS'  =>"线索转移通知：您好，线索 {{clue.name}}  已转移给您，请及时关注！ 联系电话：{{clue.phone}} 邮箱：{{clue.email}}",

        'FOLLOW_CLUE_SMS' =>"线索待跟进通知：您好，线索 {{clue.name}}  需要跟进，请及时处理！ 联系电话：{{clue.phone}} 邮箱：{{clue.email}}",

        'NEW_CLUE_SMS'     =>"新线索通知：您好，有新线索 {{clue.name}}  录入线索池，请及时处理! 联系电话：{{clue.phone}} 邮箱：{{clue.email}}",

        'FOLLOW_COMMENT_SMS' =>"联系记录评论通知：您添加的联系记录有新的评论，请及时关注！ 联系对象：{{follow.type}} {{follow.type}}名称：{{follow.name}} 联系记录：{{follow.content}} 评论内容：{{follow.comment_content}}",

		

//        系统消息模板

        'ALLOT_CUSTOMER_MSG' => [
            'title'     => '客户分配通知：客户 {{customer.name}} 已分配给您，请及时关注',
            'content'   => '您好，您分配到一个新的客户 {{customer.name}}，请及时关注!',
        ],

        'DRAW_CUSTOMER_MSG'  => [
            'title'     => '客户领取通知：客户 {{customer.name}} 领取成功，请及时关注',
            'content'   => '您好，您已成功领取客户 {{customer.name}}，请及时关注!',
        ],

        'TRANSFER_CUSTOMER_MSG' => [
            'title'     => '客户转移通知：客户 {{customer.name}} 已转移给您，请及时关注',
            'content'   => '您好，客户 {{customer.name}} 已转移给您，请及时关注!',
        ],

        'FOLLOW_CUSTOMER_MSG'   => [
            'title'     => '客户待跟进通知：客户 {{customer.name}} 需要跟进，请及时处理',
            'content'   => '您好，您有客户 {{customer.name}} 需要跟进，请及时处理!',
        ],

        'NEW_CUSTOMER_MSG'   => [
            'title'     => '新客户通知：新客户 {{customer.name}} 录入客户池成功，请及时处理',
            'content'   => '您好，有新客户 {{customer.name}} 录入客户池，请及时处理!',
        ],

        'NEW_ORDER_MSG'   => [
            'title'     => '新订单通知：新订单 {{order.name}} 添加成功，请及时关注',
            'content'   => '您好，有新订单 {{order.name}} 添加成功，请及时关注！',
        ],
		
		'NOFOLLOW_CUSTOMER_MSG'   => [
            'title'     => '客户未跟进通知：{{customer.name}} 跟进超时，请及时处理',
            'content'   => '客户 {{customer.name}} 跟进超时，请查看处理!',
        ],
		
		'NORETURNVISIT_CUSTOMER_MSG'   => [
            'title'     => '客户未回访通知：{{customer.name}} 回访超时，请及时处理',
            'content'   => '客户 {{customer.name}} 回访超时，请查看处理!',
        ],
		
		'NEW_CONTRACT_MSG'   => [
            'title'     => '合同审核提醒：合同 {{contract.name}} 需要审核，请查看处理',
            'content'   => '您好，有新合同 {{contract.name}} 需要审核，请查看处理！',
        ],
		
		'CONTRACT_EXPIRE_MSG'   => [
            'title'     => '合同到期通知：合同 {{contract.name}} 即将到期，请及时关注',
            'content'   => '您好，合同 {{contract.name}} 即将到期，请及时关注！',
        ],
		
		'RECEIPT_EXAMINE_MSG' => [
			'title'     => '收款审核提醒：收款编号 {{receipt.receipt_no}} 需要审核，请查看处理',
			'content'   => '您好，有新收款 {{receipt.receipt_no}} 需要审核 ，请查看处理！',	
		],
		'INVOICE_EXAMINE_MSG' => [
			'title'     => '发票审核提醒：发票编号 {{invoice.invoice_no}} 需要审核，请查看处理',
			'content'   => '您好，有新发票 {{invoice.invoice_no}} 需要审核 ，请查看处理！',	
		],
		'EXAMINE_OPERATE_MSG' => [
			'title'     => '审核结果通知：您提交的{{examine.type}} {{examine.examine_no}} 已被{{examine.operate_type}}，请注意查看！',
			'content'   => '您好，您提交的{{examine.type}} {{examine.examine_no}} 已被{{examine.operate_type}}，请查看处理！',	
		],

        'ALLOT_CLUE_MSG' => [
	        'title'     => '线索分配通知：线索 {{clue.name}} 已分配给您，请及时关注',
	        'content'   => '您好，您分配到一个新的线索 {{clue.name}}，请及时关注!',
        ],

        'DRAW_CLUE_MSG'  => [
	        'title'     => '线索领取通知：线索 {{clue.name}} 领取成功，请及时关注',
	        'content'   => '您好，您已成功领取线索 {{clue.name}}，请及时关注!',
        ],

        'TRANSFER_CLUE_MSG' => [
	        'title'     => '线索转移通知：线索 {{clue.name}} 已转移给您，请及时关注',
	        'content'   => '您好，线索 {{clue.name}} 已转移给您，请及时关注!',
        ],

        'FOLLOW_CLUE_MSG'   => [
	        'title'     => '线索待跟进通知：线索 {{clue.name}} 需要跟进，请及时处理',
	        'content'   => '您好，您有线索 {{clue.name}} 需要跟进，请及时处理!',
        ],

        'NEW_CLUE_MSG'   => [
	        'title'     => '新线索通知：新线索 {{clue.name}} 录入线索池成功，请及时处理',
	        'content'   => '您好，有新线索 {{clue.name}} 录入线索池，请及时处理!',
        ],

        'FOLLOW_COMMENT_MSG'   => [
	        'title'     => '联系记录评论通知：{{follow.type}} {{follow.name}} 添加的联系记录有新的评论，请及时关注',
	        'content'   => '您好，您为{{follow.type}} {{follow.name}} 添加的联系记录有新的评论，请及时关注!',
        ],
		
//        钉钉通知模板

        'ALLOT_CUSTOMER_DINGTALK' => [
            'title'   => '客户分配通知',
            'content'  => '您分配到一个新的客户，请及时关注！'
        ],

        'DRAW_CUSTOMER_DINGTALK' => [
            'title'   => '客户领取通知',
            'content'  => '您已成功领取客户，请及时关注！'
        ],

        'TRANSFER_CUSTOMER_DINGTALK' => [
            'title'   => '客户转移通知',
            'content'  => '有新的客户转移给您，请及时关注！'
        ],

        'FOLLOW_CUSTOMER_DINGTALK' => [
            'title'   => '客户待跟进通知',
            'content'  => '您有一个客户需要跟进，请及时处理！'
        ],

        'NEW_CUSTOMER_DINGTALK' => [
            'title'   => '新客户通知',
            'content'  => '有新客户录入客户池，请及时处理！'
        ],

        'NEW_ORDER_DINGTALK' => [
            'title'   => '新订单通知',
            'content'  => '有新订单已被添加，请及时关注！'
        ],
		'NOFOLLOW_CUSTOMER_DINGTALK' => [
            'title'   => '客户未跟进通知',
            'content'  => '有客户已跟进超时，请查看处理！'
        ],
		'NORETURNVISIT_CUSTOMER_DINGTALK' => [
            'title'   => '客户未回访通知',
            'content'  => '有客户已回访超时，请查看处理！'
        ],
		'NEW_CONTRACT_DINGTALK' => [
            'title'   => '合同审核提醒',
            'content'  => '有新的合同需要审核，请查看处理！'
        ],
		'CONTRACT_EXPIRE_DINGTALK' => [
            'title'   => '合同到期通知',
            'content'  => '有合同即将到期，请及时关注！'
        ],
		'RECEIPT_EXAMINE_DINGTALK' => [
            'title'   => '收款审核提醒',
            'content'  => '有新的收款需要审核 ，请查看处理！'
        ],
		'INVOICE_EXAMINE_DINGTALK' => [
            'title'   => '发票审核提醒',
            'content'  => '有新的发票需要审核 ，请查看处理！'
        ],
		'EXAMINE_OPERATE_DINGTALK' => [
            'title'   => '审核结果通知',
            'content'  => '您提交的信息已被处理 ，请注意查看！'
        ],

        'ALLOT_CLUE_DINGTALK' => [
	        'title'   => '线索分配通知',
	        'content'  => '您分配到一个新的线索，请及时关注！'
        ],

        'DRAW_CLUE_DINGTALK' => [
	        'title'   => '线索领取通知',
	        'content'  => '您已成功领取线索，请及时关注！'
        ],

        'TRANSFER_CLUE_DINGTALK' => [
	        'title'   => '线索转移通知',
	        'content'  => '有新的线索转移给您，请及时关注！'
        ],

        'FOLLOW_CLUE_DINGTALK' => [
	        'title'   => '线索待跟进通知',
	        'content'  => '您有一个线索需要跟进，请及时处理！'
        ],

        'NEW_CLUE_DINGTALK' => [
		    'title'   => '新线索通知',
		    'content'  => '有新线索录入线索池，请及时处理！'
	    ],

        'FOLLOW_COMMENT_DINGTALK' => [
	        'title'   => '联系记录评论通知',
	        'content'  => '您添加的联系记录有新的评论，请及时关注！'
        ],

//        企业微信通知模板

        'ALLOT_CUSTOMER_WORKWX' => [
	        'title'   => '您分配到一个新的客户',
	        'content'  => '请及时关注！'
        ],

        'DRAW_CUSTOMER_WORKWX' => [
	        'title'   => '您已成功领取客户',
	        'content'  => '请及时关注！'
        ],

        'TRANSFER_CUSTOMER_WORKWX' => [
	        'title'   => '有新的客户转移给您',
	        'content'  => '请及时关注！'
        ],

        'FOLLOW_CUSTOMER_WORKWX' => [
	        'title'   => '您有一个客户需要跟进',
	        'content'  => '请及时处理！'
        ],

        'NEW_CUSTOMER_WORKWX' => [
	        'title'   => '有新客户录入客户池',
	        'content'  => '请及时处理！'
        ],

        'NEW_ORDER_WORKWX' => [
	        'title'   => '有新订单已被添加',
	        'content'  => '请及时关注！'
        ],
        'NOFOLLOW_CUSTOMER_WORKWX' => [
	        'title'   => '有客户已跟进超时',
	        'content'  => '请查看处理！'
        ],
        'NORETURNVISIT_CUSTOMER_WORKWX' => [
	        'title'   => '有客户已回访超时',
	        'content'  => '请查看处理！'
        ],
        'NEW_CONTRACT_WORKWX' => [
	        'title'   => '有新合同需要审核',
	        'content'  => '请查看处理！'
        ],
        'CONTRACT_EXPIRE_WORKWX' => [
	        'title'   => '有合同即将到期',
	        'content'  => '请及时关注！'
        ],
        'RECEIPT_EXAMINE_WORKWX' => [
	        'title'   => '有新的收款需要审核',
	        'content'  => '请查看处理！'
        ],
        'INVOICE_EXAMINE_WORKWX' => [
	        'title'   => '有新的发票需要审核',
	        'content'  => '请查看处理！'
        ],
        'EXAMINE_OPERATE_WORKWX' => [
	        'title'   => '您提交的信息已被处理',
	        'content'  => '请注意查看！'
        ],
        'ALLOT_CLUE_WORKWX' => [
	        'title'   => '您分配到一个新的线索',
	        'content'  => '请及时关注！'
        ],

        'DRAW_CLUE_WORKWX' => [
	        'title'   => '您已成功领取线索',
	        'content'  => '请及时关注！'
        ],

        'TRANSFER_CLUE_WORKWX' => [
	        'title'   => '有新的线索转移给您',
	        'content'  => '请及时关注！'
        ],

        'FOLLOW_CLUE_WORKWX' => [
	        'title'   => '您有一个线索需要跟进',
	        'content'  => '请及时处理！'
        ],

        'NEW_CLUE_WORKWX' => [
	        'title'   => '有新线索录入线索池',
	        'content'  => '请及时处理！'
        ],

        'FOLLOW_COMMENT_WORKWX' => [
	        'title'   => '您添加的联系记录有新的评论',
	        'content'  => '请及时关注！'
        ],
    ]
);

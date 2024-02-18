<?php

$config = [

    'EXPORT_CUSTOMER_INDEX_FIELD'=>'customer_no,percent{DEFINE},contacter_name,contacter_phone,lastfollowtime,nextcontacttime,is_losed,createtime,member_name,create_name',

    'EXPORT_CUSTOMER_INDEX_ARR'=>[

		'customer_no' => L('CUSTOMER_NUMBER'),

		'percent' => L('INFORMATION_COMPLETENESS'),

		//'contacter_name' => L('CUSTOMER_CONTACT'),

		//'contacter_phone' => L('TEL'),

		'lastfollowtime' => L('LAST_FOLLOW-UP_TIME'),

		'nextcontacttime' => L('NEXT_CONTACT_TIME'),

		//'is_losed' => L('IS_LOSE_ORDER'),

		'createtime' => L('CREATE_TIME'),

		'member_name' => L('CUSTOMER_RESPONSIBLE'),

		'create_name' => L('FOUNDER'),
	],

	'EXPORT_CUSTOMER_POOL_FIELD'=>'customer_no,percent{DEFINE},contacter_name,contacter_phone,lastfollowtime,nextcontacttime,is_losed,is_examine,createtime,create_name',

	'EXPORT_CUSTOMER_POOL_ARR'=>[

		'customer_no' => L('CUSTOMER_NUMBER'),

		'percent' => L('INFORMATION_COMPLETENESS'),

		//'contacter_name' => L('CUSTOMER_CONTACT'),

		//'contacter_phone' => L('TEL'),

		'lastfollowtime' => L('LAST_FOLLOW-UP_TIME'),

		'nextcontacttime' => L('NEXT_CONTACT_TIME'),

		//'is_losed' => L('IS_LOSE_ORDER'),

		'is_examine' => L('WHETHER_TO_REVIEW'),

		'createtime' => L('CREATE_TIME'),

		'create_name' => L('FOUNDER'),
	],

	'EXPORT_CONTACTER_FIELD'=>'{DEFINE},customer_name',

	'EXPORT_CONTACTER_ARR'=>[

		'customer_name' => L('OWNED_CUSTOMER'),
	],

	'EXPORT_FOLLOW_FIELD'=>'content,comment,createtime,member_name,customer_contacter,cmncate_name,follow_type,belong_name',

	'EXPORT_FOLLOW_ARR'=>[

		'content' => L('CONTACT_CONTENT'),

		'comment' => L('COMMENT'),

		'createtime' => L('CONTACT_TIME'),

		'member_name' => L('CONTACT'),

		'customer_contacter' => L('CUSTOMER_CONTACT'),

		'cmncate_name' => L('COMMUNICATION_TYPE'),

		'follow_type' => L('CONTACT_PERSON'),

		'belong_name' => L('BELONGING_TO'),
	],

	'EXPORT_ORDER_FIELD'=>'order_no,contract_no{DEFINE},customer_name,member_name,createtime,create_name',

	'EXPORT_ORDER_ARR'=>[

		'order_no' => L('ORDER_NUM'),

		'contract_no' => L('ASSOCIATED_CONTRACT'),

		'customer_name' => L('OWNED_CUSTOMER'),

		'member_name' => L('LEADER'),

		'createtime' => L('CREATE_TIME'),

		'create_name' => L('FOUNDER'),

	],

	'EXPORT_PRODUCT_FIELD'=>'type_name{DEFINE},closed',

	'EXPORT_PRODUCT_ARR'=>[

		'type_name' => L('PRODUCT_CATEGORY'),

		'closed' => L('STATUS'),

	],

    'EXPORT_CONTRACT_FIELD'=>'contract_no,status,receipt_money,uncollected_money,contract_speed,order_no{DEFINE},customer_name,createtime,member_name,create_name',

	'EXPORT_CONTRACT_ARR'=>[

		'contract_no' => L('CONTRACT_NO'),

		//'order_no' => '关联订单',

		'customer_name' => L('OWNED_CUSTOMER'),

		'createtime' => L('CREATE_TIME'),

		'member_name' => L('LEADER'),

		'create_name' => L('FOUNDER'),

	],

	'EXPORT_ACCOUNT_FIELD'=>'account_no,account_money,receipt_money,uncollected_money,account_speed,account_time,customer_name,contract_name,member_name,create_name',

	'EXPORT_ACCOUNT_ARR'=>[

		'account_no' => L('ACCOUNT_RECEIVABLE_NUMBER'),

		'account_money' => L('RECEIVABLES'),

		'receipt_money' => L('PAID_FOR'),

		'uncollected_money' => L('REMAINING_RECEIVABLE'),

		'account_speed' => L('PAYMENT_PROGRESS'),

		'account_time' => L('RECEIVABLE_TIME'),

		'customer_name' => L('OWNED_CUSTOMER'),

		'contract_name' => L('ASSOCIATED_CONTRACT'),

		'member_name' => L('LEADER'),

		'create_name' => L('FOUNDER'),
	],

	'EXPORT_RECEIPT_FIELD'=>'receipt_no,status,account_money,receipt_money,receipt_type,receipt_time,customer_name,contract_name,member_name,examine_name,create_name',

	'EXPORT_RECEIPT_ARR'=>[

		'receipt_no' => L('COLLECTION_NUMBER'),

		'status' => L('STATUS'),

		'account_money' => L('RECEIVABLES'),

		'receipt_money' => L('ACTUAL_PAYMENT_AMOUNT'),

		'receipt_type' => L('PAYMENT_METHOD'),

		'receipt_time' => L('COLLECTION_TIME'),

		'customer_name' => L('OWNED_CUSTOMER'),

		'contract_name' => L('ASSOCIATED_CONTRACT'),

		'member_name' => L('LEADER'),

		'examine_name' => L('REVIEWER'),

		'create_name' => L('FOUNDER'),
	],

	'EXPORT_INVOICE_FIELD'=>'invoice_no,status,invoice_money,invoice_time,invoice_type,customer_name,contract_name,member_name,examine_name,create_name',

	'EXPORT_INVOICE_ARR'=>[

		'invoice_no' => L('INVOICE_NUMBER'),

		'status' => L('STATUS'),

		'invoice_money' => L('INVOICE_AMOUNT'),

		'invoice_time' => L('BILLING_TIME'),

		'invoice_type' => L('INVOICING_TYPE'),

		'customer_name' => L('OWNED_CUSTOMER'),

		'contract_name' => L('ASSOCIATED_CONTRACT'),

		'member_name' => L('LEADER'),

		'examine_name' => L('REVIEWER'),

		'create_name' => L('FOUNDER'),
	],

    'EXPORT_CLUE_INDEX_FIELD'=>'clue_no,status{DEFINE},lastfollowtime,nextcontacttime,createtime,member_name,create_name',

    'EXPORT_CLUE_INDEX_ARR'=>[

	    'clue_no' => L('CLUE_NO'),

	    'status' => L('STATUS'),

	    'lastfollowtime' => L('LAST_FOLLOW-UP_TIME'),

	    'nextcontacttime' => L('NEXT_CONTACT_TIME'),

	    'createtime' => L('CREATE_TIME'),

	    'member_name' => L('CLUE_LEADER'),

	    'create_name' => L('FOUNDER'),
    ],

    'EXPORT_CLUE_POOL_FIELD'=>'clue_no{DEFINE},lastfollowtime,nextcontacttime,createtime,create_name',

    'EXPORT_CLUE_POOL_ARR'=>[

	    'customer_no' => L('CUSTOMER_NUMBER'),

	    'lastfollowtime' => L('LAST_FOLLOW-UP_TIME'),

	    'nextcontacttime' => L('NEXT_CONTACT_TIME'),

	    'createtime' => L('CREATE_TIME'),

	    'create_name' => L('FOUNDER')
    ],

    'EXPORT_OPPORTUNITY_FIELD'=>'opportunity_no,customer_name{DEFINE},lastfollowtime,nextcontacttime,createtime,member_name,create_name',

    'EXPORT_OPPORTUNITY_ARR'=>[

	    'opportunity_no' => L('OPPORTUNITY_NO'),

	    'customer_name' => L('OWNED_CUSTOMER'),

	    'lastfollowtime' => L('LAST_FOLLOW-UP_TIME'),

	    'nextcontacttime' => L('NEXT_CONTACT_TIME'),

	    'member_name' => L('LEADER'),

	    'createtime' => L('CREATE_TIME'),

	    'create_name' => L('FOUNDER')
    ],
];

return $config;

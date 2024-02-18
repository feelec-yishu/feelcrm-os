<?php
return array
(
    'CRM_TEMP_JP' =>
    [
//        邮件通知模板
        "ALLOT_CUSTOMER"		=> [
            'title'		=> '客户分配通知',
            'content'	=> "客户 <span style='color:#FF5722'>{{customer.name}}</span>  已分配给您，请尽快跟进，谢谢！<br/>联系人：{{customer.contacter_name}}<br/>联系电话：{{customer.contacter_phone}}<br/>邮箱：{{customer.contacter_email}}"
        ],
		"DRAW_CUSTOMER"		=> [
            'title'		=> '客户领取通知',
            'content'	=> "您已成功领取客户 <span style='color:#FF5722'>{{customer.name}}</span>  ，请尽快跟进，谢谢！<br/>联系人：{{customer.contacter_name}}<br/>联系电话：{{customer.contacter_phone}}<br/>邮箱：{{customer.contacter_email}}"
        ],
		"TRANSFER_CUSTOMER"		=> [
            'title'		=> '客户转移通知',
            'content'	=> "客户 <span style='color:#FF5722'>{{customer.name}}</span>  已转移给您，请尽快跟进，谢谢！<br/>联系人：{{customer.contacter_name}}<br/>联系电话：{{customer.contacter_phone}}<br/>邮箱：{{customer.contacter_email}}"
        ],
		"FOLLOW_CUSTOMER"		=> [
            'title'		=> '客户待跟进通知',
            'content'	=> "您有客户 <span style='color:#FF5722'>{{customer.name}}</span>  待跟进，请尽快跟进，谢谢！<br/>联系人：{{customer.contacter_name}}<br/>联系电话：{{customer.contacter_phone}}<br/>邮箱：{{customer.contacter_email}}"
        ],
		"NEW_CUSTOMER"		=> [
            'title'		=> '新客户通知',
            'content'	=> "有新客户 <span style='color:#FF5722'>{{customer.name}}</span>  进入客户池，请尽快分配。<br/>联系人：{{customer.contacter_name}}<br/>联系电话：{{customer.contacter_phone}}<br/>邮箱：{{customer.contacter_email}}"
        ],
		"NEW_ORDER"		=> [
            'title'		=> '新订单通知',
            'content'	=> "有新订单 <span style='color:#FF5722'>{{order.order_no}}</span> 已被添加，请尽快查看。<br/>订单名称：{{order.name}}<br/>所属客户：{{order.customer_name}}"
        ],
		"NOFOLLOW_CUSTOMER"		=> [
            'title'		=> '客户未跟进通知',
            'content'	=> "有客户 <span style='color:#FF5722'>{{customer.name}}</span>  未及时跟进，请查看处理，谢谢！<br/>联系人：{{customer.contacter_name}}<br/>联系电话：{{customer.contacter_phone}}<br/>邮箱：{{customer.contacter_email}}"
        ],

//        微信通知模板

        'ALLOT_CUSTOMER_WECHAT' => [
            'first'   => '您有一个新的分配客户',
            'remark'  => '请尽快跟进，谢谢！'
        ],

        'DRAW_CUSTOMER_WECHAT' => [
            'first'   => '您已成功领取客户',
            'remark'  => '请尽快跟进，谢谢！'
        ],

        'TRANSFER_CUSTOMER_WECHAT' => [
            'first'   => '您有一个新的转移客户',
            'remark'  => '请尽快跟进，谢谢！'
        ],

        'FOLLOW_CUSTOMER_WECHAT' => [
            'first'   => '您有一个客户待跟进',
            'remark'  => '请尽快跟进，谢谢！'
        ],

        'NEW_CUSTOMER_WECHAT' => [
            'first'   => '有新客户进入客户池',
            'remark'  => '请尽快分配，谢谢！'
        ],

        'NEW_ORDER_WECHAT' => [
            'first'   => '有新订单已被添加',
            'remark'  => '请尽快查看，谢谢！'
        ],
		'NOFOLLOW_CUSTOMER_WECHAT' => [
            'first'   => '有客户未及时跟进',
            'remark'  => '请查看处理，谢谢！'
        ],

//        短信通知模板

        'ALLOT_CUSTOMER_SMS' =>"您好，客户 {{customer.name}}  已分配给您，请尽快跟进，谢谢！联系人：{{customer.contacter_name}} 联系电话：{{customer.contacter_phone}} 邮箱：{{customer.contacter_email}}",

        'DRAW_CUSTOMER_SMS' =>"您好，您已成功领取客户 {{customer.name}}  ，请尽快跟进，谢谢！ 联系人：{{customer.contacter_name}} 联系电话：{{customer.contacter_phone}} 邮箱：{{customer.contacter_email}}",

        'TRANSFER_CUSTOMER_SMS'  =>"您好，您有客户 {{customer.name}}  待跟进，请尽快跟进，谢谢！ 联系人：{{customer.contacter_name}} 联系电话：{{customer.contacter_phone}} 邮箱：{{customer.contacter_email}}",

        'FOLLOW_CUSTOMER_SMS' =>"您好，您有客户 {{customer.name}}  待跟进，请尽快跟进，谢谢！ 联系人：{{customer.contacter_name}} 联系电话：{{customer.contacter_phone}} 邮箱：{{customer.contacter_email}}",

        'NEW_CUSTOMER_SMS'     =>"您好，有新客户 {{customer.name}}  进入客户池，请尽快分配。 联系人：{{customer.contacter_name}} 联系电话：{{customer.contacter_phone}} 邮箱：{{customer.contacter_email}}",

        'NEW_ORDER_SMS' =>"您好，有新订单 {{order.order_no}} 已被添加，请尽快查看。 订单名称：{{order.name}} 所属客户：{{order.customer_name}}",
		
		'NOFOLLOW_CUSTOMER_SMS' =>"有客户 {{customer.name}} 未及时跟进，请查看处理，谢谢！ 联系人：{{customer.contacter_name}} 联系电话：{{customer.contacter_phone}} 邮箱：{{customer.contacter_email}}",

//        系统消息模板

        'ALLOT_CUSTOMER_MSG' => [
            'title'     => '客户#{{customer.customer_no}}分配通知',
            'content'   => '您好，您分配到一个新的客户 {{customer.name}}，请及时跟进，谢谢!',
        ],

        'DRAW_CUSTOMER_MSG'  => [
            'title'     => '客户#{{customer.customer_no}}领取通知',
            'content'   => '您好，您已成功领取客户 {{customer.name}}，请及时跟进，谢谢!',
        ],

        'TRANSFER_CUSTOMER_MSG' => [
            'title'     => '客户#{{customer.customer_no}}转移通知',
            'content'   => '您好，您收到一个新的转移客户 {{customer.name}}，请及时跟进，谢谢!',
        ],

        'FOLLOW_CUSTOMER_MSG'   => [
            'title'     => '客户#{{customer.customer_no}}待跟进通知',
            'content'   => '您好，您有客户 {{customer.name}} 待跟进，请及时跟进，谢谢!',
        ],

        'NEW_CUSTOMER_MSG'   => [
            'title'     => '新客户#{{customer.customer_no}}通知',
            'content'   => '您好，有新客户 {{customer.name}} 进入客户池，请尽快分配，谢谢!',
        ],

        'NEW_ORDER_MSG'   => [
            'title'     => '新订单#{{order.order_no}}通知',
            'content'   => '您好，有新订单 {{order.order_no}} 已被添加，请及时查看，谢谢！',
        ],
		
		'NOFOLLOW_CUSTOMER_MSG'   => [
            'title'     => '客户#{{customer.customer_no}}未跟进通知',
            'content'   => '客户 {{customer.name}} 未及时跟进，请查看处理，谢谢!',
        ],
    ]
);

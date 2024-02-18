<?php
return [
	"CRMFIELDS" => [
		"CUSTOMER"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'name',
				'form_description' => '客户名称',
				'name_en' => 'Customer name',
				'name_jp' => '顧客名',
				'form_explain' => '请输入客户名称',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '1',
				'show_list' => '1',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'phone',
				'form_description' => '联系电话',
				'name_en' => 'Phone number',
				'name_jp' => '連絡電話',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '1',
				'show_list' => '1',
				'orderby' => '2',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'email',
				'form_description' => '邮箱',
				'name_en' => 'Email',
				'name_jp' => 'メールボックス',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '1',
				'show_list' => '0',
				'orderby' => '3',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'importance',
				'form_description' => '重要程度',
				'name_en' => 'Importance',
				'name_jp' => '重要性',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '重要|一般|不重要|淘汰',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '4',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'customer_status',
				'form_description' => '客户类别',
				'name_en' => 'Customer category',
				'name_jp' => '顧客カテゴリ',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '终端客户|经销商客户',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '5',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'industry',
				'form_description' => '客户行业',
				'name_en' => 'Customer Industry',
				'name_jp' => '顧客業',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => 'IT/教育|电子/商务|对外贸易|酒店、旅游|金融、保险|房产行业|医疗/保健|政府、机关',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '6',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'origin',
				'form_description' => '客户来源',
				'name_en' => 'Source of customer',
				'name_jp' => '顧客ソース',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '电话营销|网络营销|上门推销',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '7',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'stage',
				'form_description' => '销售进度',
				'name_en' => 'Sales progress',
				'name_jp' => '販売の進度',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '初步接触|需求确认|方案报价|协商议价|合同签约',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '8',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'region',
				'form_description' => '客户地区',
				'name_en' => 'Customer area',
				'name_jp' => '顧客エリア',
				'form_explain' => '',
				'form_type' => 'region',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '9',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'address',
				'form_description' => '客户地址',
				'name_en' => 'Customer address',
				'name_jp' => '顧客の住所',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '10',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'website',
				'form_description' => '网址',
				'name_en' => 'Website',
				'name_jp' => 'ウェブサイト',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '11',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'customer_grade',
				'form_description' => '客户等级',
				'name_en' => 'Customer level',
				'name_jp' => '顧客レベル',
				'form_explain' => '',
				'form_type' => 'radio',
				'form_option' => '普通客户|主要客户|关键客户',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '12',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'customer',
				'form_name' => 'remark',
				'form_description' => '备注',
				'name_en' => 'Remarks',
				'name_jp' => 'コメント',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '13',
				'is_default' => '1',
			],
		],
		"CONTACTER"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contacter',
				'form_name' => 'name',
				'form_description' => '联系人姓名',
				'name_en' => 'Contact name',
				'name_jp' => '連絡先の名前',
				'form_explain' => '请输入联系人姓名',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '0',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contacter',
				'form_name' => 'role',
				'form_description' => '角色',
				'name_en' => 'Role',
				'name_jp' => 'キャラクター',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '普通人|决策人|分项决策人|商务决策|技术决策|财务决策|使用人|意见影响人',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contacter',
				'form_name' => 'saltname',
				'form_description' => '尊称',
				'name_en' => 'Honorific title',
				'name_jp' => '敬称',
				'form_explain' => '',
				'form_type' => 'radio',
				'form_option' => '先生|女士',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '2',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contacter',
				'form_name' => 'position',
				'form_description' => '职位',
				'name_en' => 'Position',
				'name_jp' => '地位',
				'form_explain' => '请输入职位名称',
				'form_type' => 'text',
				'form_option' => '先生|女士',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '3',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contacter',
				'form_name' => 'phone',
				'form_description' => '手机',
				'name_en' => 'Mobile',
				'name_jp' => '携帯電話',
				'form_explain' => '请输入手机号',
				'form_type' => 'phone',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '1',
				'show_list' => '1',
				'orderby' => '4',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contacter',
				'form_name' => 'qq',
				'form_description' => 'QQ',
				'name_en' => 'QQ',
				'name_jp' => 'QQ',
				'form_explain' => '请输入QQ号码',
				'form_type' => 'number',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '5',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contacter',
				'form_name' => 'email',
				'form_description' => '邮箱',
				'name_en' => 'Email',
				'name_jp' => 'メールボックス',
				'form_explain' => '请输入邮箱',
				'form_type' => 'email',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '1',
				'show_list' => '1',
				'orderby' => '6',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contacter',
				'form_name' => 'wechat',
				'form_description' => '微信号',
				'name_en' => 'Wechat',
				'name_jp' => 'チャット番号',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '1',
				'show_list' => '1',
				'orderby' => '7',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contacter',
				'form_name' => 'remark',
				'form_description' => '备注',
				'name_en' => 'Remarks',
				'name_jp' => 'コメント',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '8',
				'is_default' => '1',
			],
		],
		"PRODUCT"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'product',
				'form_name' => 'name',
				'form_description' => '产品名称',
				'name_en' => 'product name',
				'name_jp' => '商品コード',
				'form_explain' => '请输入产品名称',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'product',
				'form_name' => 'standard',
				'form_description' => '规格',
				'name_en' => 'Specifications',
				'name_jp' => '規格',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '个|箱|套|盒|瓶|块|只|把|枚|条',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '2',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'product',
				'form_name' => 'product_num',
				'form_description' => '产品编号',
				'name_en' => 'Product number',
				'name_jp' => '製品番号',
				'form_explain' => '请输入产品编号',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '3',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'product',
				'form_name' => 'describe',
				'form_description' => '产品描述',
				'name_en' => 'Product description',
				'name_jp' => '製品の説明',
				'form_explain' => '请输入产品描述',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '4',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'product',
				'form_name' => 'cost_price',
				'form_description' => '成本价',
				'name_en' => 'Cost price',
				'name_jp' => '原価',
				'form_explain' => '请输入成本价',
				'form_type' => 'number',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '5',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'product',
				'form_name' => 'list_price',
				'form_description' => '建议售价',
				'name_en' => 'Suggested price',
				'name_jp' => '価格を提案します',
				'form_explain' => '请输入建议售价',
				'form_type' => 'number',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '6',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'product',
				'form_name' => 'remark',
				'form_description' => '备注',
				'name_en' => 'Remarks',
				'name_jp' => 'コメント',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '7',
				'is_default' => '1',
			],
		],
		"ORDER"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'order',
				'form_name' => 'name',
				'form_description' => '订单名称',
				'name_en' => 'Order name',
				'name_jp' => 'オーダー名',
				'form_explain' => '请输入订单名称',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'order',
				'form_name' => 'price',
				'form_description' => '订单金额',
				'name_en' => 'Order amount',
				'name_jp' => '注文金額',
				'form_explain' => '0.00',
				'form_type' => 'number',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '2',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'order',
				'form_name' => 'other_price',
				'form_description' => '其他费用',
				'name_en' => 'Other expenses',
				'name_jp' => 'その他の費用',
				'form_explain' => '0.00',
				'form_type' => 'number',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '3',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'order',
				'form_name' => 'currency',
				'form_description' => '币种',
				'name_en' => 'Currency',
				'name_jp' => '貨幣種類',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => 'RMB|美元|日元|欧元|英镑',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '4',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'order',
				'form_name' => 'pay_style',
				'form_description' => '支付方式',
				'name_en' => 'Payment method',
				'name_jp' => '支払い方法',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '现金|银行转账|支票|汇款|第三方支付',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '5',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'order',
				'form_name' => 'delivery_time',
				'form_description' => '交货日期',
				'name_en' => 'Delivery date',
				'name_jp' => '納品日',
				'form_explain' => '',
				'form_type' => 'date',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '6',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'order',
				'form_name' => 'describe',
				'form_description' => '订单描述',
				'name_en' => 'Order description',
				'name_jp' => '注文書の説明',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '7',
				'is_default' => '1',
			],
		],
		"ANALYSIS"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'analysis',
				'form_name' => 'project_approval',
				'form_description' => '是否立项',
				'name_en' => 'Project approval or not',
				'name_jp' => '審査時かどうか',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '是|否',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'analysis',
				'form_name' => 'cycle',
				'form_description' => '决策周期',
				'name_en' => 'Decision cycle',
				'name_jp' => '政策決定の周期',
				'form_explain' => '天',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '2',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'analysis',
				'form_name' => 'budget',
				'form_description' => '预算',
				'name_en' => 'Budget',
				'name_jp' => '予算',
				'form_explain' => '请填写预算金额',
				'form_type' => 'number',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '3',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'analysis',
				'form_name' => 'concern',
				'form_description' => '客户关注点',
				'name_en' => 'Customer focus',
				'name_jp' => 'お客様の懸念点',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '4',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'analysis',
				'form_name' => 'demand',
				'form_description' => '需求描述',
				'name_en' => 'Requirement description',
				'name_jp' => '要件の説明',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '5',
				'is_default' => '1',
			],

		],
		"COMPETITOR"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'competitor',
				'form_name' => 'name',
				'form_description' => '竞争对手名称',
				'name_en' => 'Competitor name',
				'name_jp' => 'ライバル名',
				'form_explain' => '请输入竞争对手名称',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '1',
				'show_list' => '0',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'competitor',
				'form_name' => 'stage',
				'form_description' => '对方进行阶段',
				'name_en' => 'The other party\'s stage',
				'name_jp' => '相手の進行段階',
				'form_explain' => '请填写对方进行阶段',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '2',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'competitor',
				'form_name' => 'advantage',
				'form_description' => '优势',
				'name_en' => 'Advantage',
				'name_jp' => '優勢',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '3',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'competitor',
				'form_name' => 'inferiority',
				'form_description' => '劣势',
				'name_en' => 'Inferiority',
				'name_jp' => '劣勢',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '4',
				'is_default' => '1',
			],
		],
		"CONTRACT"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contract',
				'form_name' => 'name',
				'form_description' => '合同名称',
				'name_en' => 'Contract name',
				'name_jp' => '契約の名称',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contract',
				'form_name' => 'money',
				'form_description' => '合同金额',
				'name_en' => 'Contract amount',
				'name_jp' => '契約金額',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '2',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contract',
				'form_name' => 'sign_time',
				'form_description' => '签订日期',
				'name_en' => 'Date of signing',
				'name_jp' => '調印日',
				'form_explain' => '',
				'form_type' => 'date',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '3',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contract',
				'form_name' => 'start_time',
				'form_description' => '生效日期',
				'name_en' => 'Effective date',
				'name_jp' => '発効日',
				'form_explain' => '',
				'form_type' => 'date',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '4',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contract',
				'form_name' => 'end_time',
				'form_description' => '截止日期',
				'name_en' => 'Closing date',
				'name_jp' => '締切日',
				'form_explain' => '',
				'form_type' => 'date',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '5',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'contract',
				'form_name' => 'item',
				'form_description' => '合同条款',
				'name_en' => 'Contract terms',
				'name_jp' => '契約条項',
				'form_explain' => '请填写合同条款',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '6',
				'is_default' => '1',
			],

		],
		"SHIPMENT"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'shipment',
				'form_name' => 'trade',
				'form_description' => '行业',
				'name_en' => 'Industry',
				'name_jp' => '業種',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '户外|渔业|专卖店',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'shipment',
				'form_name' => 'promotion',
				'form_description' => '推广活动',
				'name_en' => 'Promotion activities',
				'name_jp' => '普及活動',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '展会|活动|广告|媒体|网红评测',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '2',
				'is_default' => '1',
			],
		],
		"CLUE"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'name',
				'form_description' => '姓名',
				'name_en' => 'name',
				'name_jp' => '名前',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'source',
				'form_description' => '线索来源',
				'name_en' => 'Source of clues',
				'name_jp' => '手がかりの源',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '电话营销|网络营销|上门推销',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '2',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'phone',
				'form_description' => '电话',
				'name_en' => 'Phone',
				'name_jp' => '電話',
				'form_explain' => '',
				'form_type' => 'phone',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '1',
				'show_list' => '1',
				'orderby' => '3',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'email',
				'form_description' => '邮箱',
				'name_en' => 'Email',
				'name_jp' => 'メールボックス',
				'form_explain' => '',
				'form_type' => 'email',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '4',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'wechat',
				'form_description' => '微信号',
				'name_en' => 'Wechat',
				'name_jp' => '番号をチャットします',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '5',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'qq',
				'form_description' => 'QQ',
				'name_en' => 'QQ',
				'name_jp' => 'QQ',
				'form_explain' => '',
				'form_type' => 'number',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '6',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'company',
				'form_description' => '公司名称',
				'name_en' => 'Company name',
				'name_jp' => '会社名',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '7',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'industry',
				'form_description' => '行业',
				'name_en' => 'Industry',
				'name_jp' => '業界',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => 'IT/教育|电子/商务|对外贸易|酒店、旅游|金融、保险|房产行业|医疗/保健|政府、机关',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '8',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'address',
				'form_description' => '地址',
				'name_en' => 'Address',
				'name_jp' => '住所',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '9',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'website',
				'form_description' => '网址',
				'name_en' => 'Website',
				'name_jp' => 'ウェブサイト',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '10',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'clue',
				'form_name' => 'remark',
				'form_description' => '备注',
				'name_en' => 'Remarks',
				'name_jp' => 'コメント',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '11',
				'is_default' => '1',
			],
		],
		"OPPORTUNITY"=>[
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'opportunity',
				'form_name' => 'name',
				'form_description' => '商机名称',
				'name_en' => 'Opportunity name',
				'name_jp' => '商談名',
				'form_explain' => '',
				'form_type' => 'text',
				'form_option' => '',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '1',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'opportunity',
				'form_name' => 'stage',
				'form_description' => '商机阶段',
				'name_en' => 'Opportunity stage',
				'name_jp' => '機会段階',
				'form_explain' => '',
				'form_type' => 'select',
				'form_option' => '初步接触|需求确认|方案报价|协商议价|合同签约|赢单|输单',
				'is_required' => '0',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '1',
				'orderby' => '2',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'opportunity',
				'form_name' => 'budget',
				'form_description' => '预算金额',
				'name_en' => 'Budget amount',
				'name_jp' => '予算額',
				'form_explain' => '',
				'form_type' => 'number',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '3',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'opportunity',
				'form_name' => 'predict_time',
				'form_description' => '预计成交日期',
				'name_en' => 'Estimated closing date',
				'name_jp' => '締切予定日',
				'form_explain' => '',
				'form_type' => 'date',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '4',
				'is_default' => '1',
			],
			[
				'company_id' => '{{COMPANY_ID}}',
				'type' => 'opportunity',
				'form_name' => 'remark',
				'form_description' => '备注',
				'name_en' => 'Remarks',
				'name_jp' => 'コメント',
				'form_explain' => '',
				'form_type' => 'textarea',
				'form_option' => '',
				'is_required' => '1',
				'closed' => '0',
				'is_unique' => '0',
				'show_list' => '0',
				'orderby' => '5',
				'is_default' => '1',
			],
		],
	]
];
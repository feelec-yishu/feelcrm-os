/*
* @version: 2.0
* @Author:  tomato
* @Date:    2018-5-5 11:29:57
* @Last Modified by:   tomato
* @Last Modified time: 2018-5-26 18:08:43
*/
//多选下拉框
layui.define(['jquery', 'layer'], function(exports){
	var MOD_NAME = 'selectM';
	var $ = layui.jquery,layer=layui.layer;
	var obj = function(config)
	{
		this.disabledIndex =[];
		//当前选中的值名数据
		this.selected = [];
		//当前选中的值
		this.values =[];
		//当前选中的名称
		this.names =[];

		//初始化设置参数
		this.config = {
			//选择器id或class
			elem: '',

			//候选项数据[{id:"1",name:"名称1",status:0},{id:"2",name:"名称2",status:1}]
			data: [],

			//默认选中值
			selected: [],

			//空值项提示，支持将{max}替换为max
			tips: '',

			//最多选中个数，默认5
			max : 5,

			//选择框宽度
			width:null,

			//值验证，与lay-verify一致
			verify: '',

			//input的name 不设置与选择器相同(去#.)
			name: '',

			//值的分隔符
			delimiter: ',',

			//搜索
			search:false,

			searchTips:'',

			//候选项数据的键名 status=0为禁用状态
			field: {idName:'id',titleName:'name',statusName:'status',levelName:'level',childName:'child'},
			
			//全选
			selectAll:false
		};

		this.config = $.extend(this.config,config);
		//创建选项元素
		this.createOption = function()
		{
			var o=this,c=o.config,f=c.field,d = c.data;
			var s = c.selected;
			$E = $(c.elem);
			var tips = c.tips.replace('{max}',c.max);
			var inputName = c.name=='' ? c.elem.replace('#','').replace('.','') : c.name;
			var verify = c.verify=='' ? '' : 'lay-verify="'+c.verify+'" ';
			var html = '';
			html +=	'<div class="layui-unselect layui-form-select">';
			html +=			'<div class="layui-select-title">';
			html +=				'<input '+verify+'name="'+inputName+'" type="text" readonly="" class="layui-input layui-unselect">';
			html +=			'</div>';
			html +=			'<div class="layui-input multiple">';
			html +=			'</div>';
			html +=			'<dl class="layui-anim layui-anim-upbit">';
			if(c.search) html += '<dd lay-value="" class="selectM-search"><input type="text" placeholder="'+c.searchTips+'" class="selectM-search-input"></dd>';
			html +=				'<dd lay-value="" class="layui-select-tips">'+tips+'</dd>';
			if(c.selectAll)
			{
				html +='<dd lay-value="" class="selectM-all">';
				html +=		'<div class="layui-unselect layui-form-checkbox" lay-skin="primary">';
				html +=			'<span>全选</span><i class="layui-icon">&#xe605;</i>';
				html +=		'</div>';
				html +='</dd>';
			}
			html += o.getChildHtml(o,f,d,s);
			html +=			'</dl>';
			html +=		'</div>';

			$E.html(html);
		};

		//设置选中值
		this.set = function(selected){
			var o=this,c=o.config;
			var s = typeof selected=='undefined' ? c.selected : selected;
			$E = $(c.elem);
			$E.find('.layui-form-checkbox').removeClass('layui-form-checked');
			$E.find('dd').removeClass('layui-this');
			//为默认选中值添加类名
			var max = s.length>c.max ? c.max : s.length;
			for(var i=0;i<max;i++){
				if(s[i] && !o.disabledIndex.hasOwnProperty(s[i])){
					$E.find('dd[lay-value='+s[i]+']').addClass('layui-this');
				}
			}
			$E.find('dd.layui-this').each(function(){
				$(this).find('div').addClass('layui-form-checked');
			});
			//修改时的全选选中控制
			if(c.selectAll){
				var selectAll = $E.find('.selectM-all');
				if($E.find('dd.layui-this').length === selectAll.nextAll('dd').length){
					selectAll.addClass('layui-this').find('div').addClass('layui-form-checked');
				}
			}
			o.setSelected(selected);
		};

		//设置选中值 每次点击操作后执行
		this.setSelected = function(first){
			var o=this,c=o.config,f=c.field;
			$E = $(c.elem);
			var values=[],names=[],selected = [],spans = [];
			var items = $E.find('dd.layui-this');
			if(items.length==0){
				var tips = c.tips.replace('{max}',c.max);
				spans.push('<span class="tips">'+tips+'</span>');
			}
			else{
				items.each(function()
				{
					$this = $(this);
					var item ={};
					var v = $this.attr('lay-value');
					var n = $this.find('span').text();
					if(typeof(v) || v > 0){
						item[f.idName] = v;
						item[f.titleName] = n;
						values.push(v);
						names.push(n);
						spans.push('<a href="javascript:;"><span lay-value="'+v+'">'+n+'</span><i class="layui-icon">&#x1006;</i></a>');
						selected.push(item);
					}
				});
			}
			spans.push('<i class="layui-edge" style="pointer-events: none;"></i>');
			$E.find('.multiple').html(spans.join(''));
			$E.find('.layui-select-title').find('input').each(function(){
				if(typeof first=='undefined'){
					this.defaultValue = values.join(c.delimiter);
				}
				this.value = values.join(c.delimiter);
			});
			
			if($E.find('.multiple').height() > 0)
			{
				var h = $E.find('.multiple').height()+14;
				$E.find('.layui-form-select dl').css('top',h+'px');
			}
			o.values=values,o.names=names,o.selected = selected;
			if(typeof this.config.callback=='function'){this.config.callback(values)};
		}
		//ajax方式获取候选数据
		this.getData = function(url){
			var d;
			$.ajax({
				url:url,
				dataType:'json',
				async:false,
				success:function(json){
					d=json;
				},
				error: function(){
					console.error(MOD_NAME+' hint：候选数据ajax请求错误 ');
					d = false;
				}
			});
			return d;
		};
		//无限极
		this.getChildHtml = function(o,f,data)
		{
			var html = '';

			var s = typeof this.config.selected != 'undefined' ? this.config.selected : [];

			for(var i=0;i<data.length;i++)
			{
				var disabled1='',disabled2='',pl = '';

				if(data[i][f.statusName]==0)
				{
					o.disabledIndex[data[i][f.idName]] = data[i][f.titleName];
					disabled1 = data[i][f.statusName]==0 ? 'layui-disabled' : '';
					disabled2 = data[i][f.statusName]==0 ? ' layui-checkbox-disbaled layui-disabled' : '';
				}

				padding = 10+(data[i][f.levelName] - 1)*30 + 'px';

				pl = 'padding-left:'+padding;

				//默认选中
				var ddChecked = divChecked = '';

				if($.inArray(data[i][f.idName],s))
				{
					ddChecked = ' layui-this';
					divChecked = ' layui-form-checked';
				}

				html +='<dd lay-value="'+data[i][f.idName]+'" class="'+disabled1+ddChecked+'" style="'+pl+'">';
				html +=		'<div class="layui-unselect layui-form-checkbox'+disabled2+divChecked+'" lay-skin="primary">';
				html +=			'<span>'+data[i][f.titleName]+'</span><i class="layui-icon">&#xe605;</i>';
				html +=		'</div>';
				html +='</dd>';

				var child = data[i][f.childName];

				if('undefined' != typeof child && child.length > 0 )
				{
					html += o.getChildHtml(o,f,child);
				}
			}

			return html;
		}
	};
	//渲染一个实例
	obj.prototype.render = function()
	{
		var o=this,c=o.config,f=c.field;

		$E = $(c.elem);

		if($E.length==0)
		{
			console.error(MOD_NAME+' hint：找不到容器 ' +c.elem);
			return false;
		}

		if(Object.prototype.toString.call(c.data)!='[object Array]')
		{
			var data = o.getData(c.data);

			if(data===false)
			{
				console.error(MOD_NAME+' hint：缺少分类数据');
				return false;
			}

			o.config.data =  data;
		}

		//给容器添加一个类名
		$E.addClass('lay-ext-mulitsel');

		if(/^\d+$/.test(c.width))
		{
			$E.css('width',c.width+'px');
		}

		var name = c.name=='' ? c.elem.replace('#','').replace('.','') : c.name;

		//添加专属的style
		if($('#lay-ext-'+name+'-style').length == 0)
		{
			var style = 'input[name="'+name+'"]{border:none}.lay-ext-mulitsel .layui-form-select dl dd div{margin-top:0px!important;}.lay-ext-mulitsel .layui-form-select dl dd.layui-this{background-color:#fff}.lay-ext-mulitsel .layui-input.multiple{border-radius:3px;line-height:auto;height:auto;padding:2px 10px;border-color:#dce3e8;overflow:hidden;min-height:35px;margin-top:-35px;left:0;z-index:99;position:relative;background:#fff;}.lay-ext-mulitsel .layui-input.multiple a{display: inline-block;font-weight: normal;margin: 3px 4px 3px 0;padding: 4px;line-height: 14px;border-radius: 2px;background-color: #2c6ee5;color: #fff;font-size: 12px;}.lay-ext-mulitsel .layui-input.multiple a span{vertical-align: middle;font-size:12px}.lay-ext-mulitsel .layui-input.multiple a i{margin-left:4px;font-size:13px;vertical-align: middle;} .lay-ext-mulitsel .layui-input.multiple a i:hover{color:#e3e9ed;}.lay-ext-mulitsel .danger{border-color:#FF5722!important}.lay-ext-mulitsel .tips{pointer-events: none;position: absolute;left: 10px;top: 10px;color:#757575;}.layui-form-selectup dl{top:auto !important;bottom:42px} .layui-form-select dl dd.layui-this{background:#fff !important}';

			$('<style id="lay-ext-'+name+'-style"></style>').text(style).appendTo($('head'));
		}

		//创建选项
		o.createOption();
		//设置选中值
		o.set();

		//展开/收起选项
		$E.on('click','.layui-select-title,.multiple,.multiple.layui-edge',function(e)
		{
			var h = $(this).offset().top + $(this).outerHeight() + 5 - $(this).scrollTop();

			var sc = 'layui-form-selected';

			if(h > 600) sc += ' layui-form-selectup';

			//隐藏其他实例显示的弹层
			$('.lay-ext-mulitsel').not(c.elem).removeClass(sc);

			if($(c.elem).is('.layui-form-selected'))
			{
				$(c.elem).removeClass(sc);

				$(document).off('click',mEvent);
			}
			else
			{
				$(c.elem).addClass(sc);

				$(document).on('click',mEvent=function(e)
				{
					if(e.target.id!==c.elem && e.target.className!=='layui-input multiple')
					{
						$(c.elem).removeClass(sc);

						$(document).off('click',mEvent);
					}
				});
			}
		});

		//点击选项
		$E.on('click','dd',function(e){
			var _dd = $(this);
			
			var _dds,selectAll;

			if(_dd.hasClass('layui-disabled')){
				return false;
			}

			//搜索
			if(c.search && $(this).attr('class') == 'selectM-search')
			{
				$('.selectM-search-input').keyup(function ()
				{
					var value = $(this).val();

					var y = $('.selectM-search');

					if(value)
					{
						y.siblings("dd[class!='layui-select-tips']").hide();

						y.siblings('dd').filter(":contains('" + (value) + "')").show();
					}
					else
					{
						y.siblings('dd').show();
					}
				});

				return false;
			}

			//点 请选择
			if(_dd.is('.layui-select-tips')){
				_dd.siblings().removeClass('layui-this');
				$(c.elem).find('.layui-form-checkbox').removeClass('layui-form-checked');
			}
			//取消选中
			else if(_dd.is('.layui-this')){
				_dd.removeClass('layui-this');
				_dd.find('.layui-form-checkbox').removeClass('layui-form-checked');
				e.stopPropagation();
				if(c.selectAll)
				{
					//取消全选
					if(_dd.hasClass('selectM-all'))
					{
						_dds = _dd.nextAll('dd');

						$.each(_dds,function(k,v)
						{
							var that = $(this);

							that.removeClass('layui-this');

							that.find('.layui-form-checkbox').removeClass('layui-form-checked');

							e.stopPropagation();
						})
					}
					else
					{
						//单项取消控制全选取消
						selectAll = _dd.prevAll('.selectM-all');

						if(selectAll.hasClass('layui-this'))
						{
							selectAll.removeClass('layui-this').find('.layui-form-checkbox').removeClass('layui-form-checked');

							e.stopPropagation();
						}
					}
				}
			}
			//选中
			else
			{
				if(o.selected.length >= c.max)
				{
					$(c.elem+' .multiple').addClass('danger');
					layer.tips('最多只能选择 '+c.max+' 个', c.elem+' .multiple', {
						tips: 3,
						time: 1000,
						end:function(){
							$(c.elem+' .multiple').removeClass('danger');
						}
					});
					return false;
				}
				else{
					_dd.addClass('layui-this');
					_dd.find('.layui-form-checkbox').addClass('layui-form-checked');
					e.stopPropagation();
					//全选
					if(c.selectAll)
					{
						if(_dd.hasClass('selectM-all'))
						{
							_dds = _dd.nextAll('dd');

							$.each(_dds,function(k,v)
							{
								var that = $(this);

								if(!that.is('.layui-this'))
								{
									that.addClass('layui-this');
									that.find('.layui-form-checkbox').addClass('layui-form-checked');
									e.stopPropagation();
								}
							})
						}
						else
						{
							//单项选中控制全选选中
							selectAll = _dd.prevAll('.selectM-all');

							_dds = selectAll.nextAll('dd');

							var selectedLength = 0;

							$.each(_dds,function(k,v)
							{
								var that = $(this);

								if(that.is('.layui-this'))
								{
									selectedLength++;
								}
							});

							if(selectedLength === _dds.length)
							{
								selectAll.addClass('layui-this');
								selectAll.find('.layui-form-checkbox').addClass('layui-form-checked');
								e.stopPropagation();
							}
						}
					}
				}
			}

			o.setSelected();
		});

		//删除选项
		$E.on('click','a i',function(e)
		{
			var _this = $(this).prev('span');
			var v = _this.attr('lay-value');
			if(v){
				var _dd = $(c.elem).find('dd[lay-value='+v+']');
				_dd.removeClass('layui-this');
				_dd.find('.layui-form-checkbox').removeClass('layui-form-checked');
				$(c.elem).find('.selectM-all').removeClass('layui-this').find('.layui-form-checkbox').removeClass('layui-form-checked')
			}
			o.setSelected();
			_this.parent().remove();
			e.stopPropagation();
		});

		//验证失败样式
		$E.find('input').focus(function()
		{
			$(c.elem+' .multiple').addClass('danger');

			setTimeout(function()
			{
				$(c.elem+' .multiple').removeClass('danger');
			},3000);
		});
	};

	//输出模块
	exports(MOD_NAME, function (config) {
		var _this = new obj(config);
		_this.render();
		return _this;
  });
});

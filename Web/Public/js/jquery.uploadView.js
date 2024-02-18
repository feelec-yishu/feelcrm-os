// 图片上传前预览插件
//Power By 勾国印
//QQ:245629560
//Blog:http://www.gouguoyin.cn
(function($){  
	$.fn.uploadView = function(options){
		var defaults = {
			uploadBox: '.js_uploadBox', //设置上传框容器
			showBox : '.js_showBox', //设置显示预览图片的容器
			width : 200, //设置预览图片的宽度
			height: 200, //设置预览图片的高度
			allowType: ["gif", "jpeg", "jpg", "bmp", "png"], 
			maxSize: 1, //设置允许上传图片的最大尺寸，单位M
			success:$.noop //上传成功时的回调函数
		};
		var config = $.extend(defaults, options);
		
		var showBox = config.showBox;
		var uploadBox = config.uploadBox;
		var width = config.width;
		var height = config.height;
		var allowType = config.allowType;
		var maxSize = config.maxSize;
		
		var success = config.success;
		
		$(this).each(function(i){
		    $(this).change(function(e){
		      handleFileSelect($(this), width, height, allowType, maxSize, success);
		    });
		});
		
		var handleFileSelect = function(obj, _w, _h, _type, _size, _callback){
		
			  if (typeof FileReader == "undefined") {
			    return false;
			  }
			  var thisClosest = obj.closest(uploadBox);
			  if (typeof thisClosest.length == "undefined") {
			    return;
			  }
			  
			  var files = obj[0].files;
			  var f = files[0];
			  if (!isAllowFile(f.name, _type)) {
				alert("图片类型必须是" + _type.join("，") + "中的一种"); 
			    return false;
			  }
			  
			  var fileSize = f.size;
			  
			  var maxSize = _size*1024*1024;
			  
			  if(fileSize > maxSize){
			  	alert('上传图片超出允许上传大小');
			  	return false;
			  }
			  
			  var reader = new FileReader();
			  reader.onload = (function(theFile){
				      return function (e) {
				        var tmpSrc = e.target.result;
				        if (tmpSrc.lastIndexOf('data:base64') != -1) {
						  tmpSrc = tmpSrc.replace('data:base64', 'data:image/jpeg;base64');
						} else if (tmpSrc.lastIndexOf('data:,') != -1) {
						  tmpSrc = tmpSrc.replace('data:,', 'data:image/jpeg;base64,');
						}
						
						var img = '<img src="' + tmpSrc + '"/>';
						//consoleLog(reader, img);
						thisClosest.find(showBox).show().html(img);
						if (_w && _h) {
						  cssObj = { 'width':_w+'px', 'height':_h+'px' };
						} else if (_w) {
						  cssObj = { 'width':_w+'px' };
						} else if (_h) {
						  cssObj = { 'height':_h+'px' };
						} else {
						  cssObj = { 'max-width':'360px', 'max-height':'200px' };
						}
						thisClosest.find(showBox+" img").css( cssObj );
						_callback();
				     };
			  })(f)
			  reader.readAsDataURL(f);
	
		}
		//获取上传文件的后缀名
		var getFileExt = function(fileName){
			  if (!fileName) {
				    return '';
				  }
				  
				  var _index = fileName.lastIndexOf('.');
				  if (_index < 1) {
				    return '';
				  }
				  
				  return fileName.substr(_index+1);
			};
		//是否是允许上传文件格式	
	    var isAllowFile = function(fileName, allowType){

			  var fileExt = getFileExt(fileName).toLowerCase();
			  if (!allowType) {
			    allowType = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
			  }
			  
			  if ($.inArray(fileExt, allowType) != -1) {
			    return true;
			  }
			  return false;

		}		

	};
	

})(jQuery);

jQuery.extend({ 
unselectContents: function(){ 
	if(window.getSelection) 
		window.getSelection().removeAllRanges(); 
	else if(document.selection) 
		document.selection.empty(); 
	} 
}); 
jQuery.fn.extend({ 
	selectContents: function(){ 
		$(this).each(function(i){ 
			var node = this; 
			var selection, range, doc, win; 
			if ((doc = node.ownerDocument) && (win = doc.defaultView) && typeof win.getSelection != 'undefined' && typeof doc.createRange != 'undefined' && (selection = window.getSelection()) && typeof selection.removeAllRanges != 'undefined'){ 
				range = doc.createRange(); 
				range.selectNode(node); 
				if(i == 0){ 
					selection.removeAllRanges(); 
				} 
				selection.addRange(range); 
			} else if (document.body && typeof document.body.createTextRange != 'undefined' && (range = document.body.createTextRange())){ 
				range.moveToElementText(node); 
				range.select(); 
			} 
		}); 
	}, 

	setCaret: function(){ 
		if(!$.browser.msie) return; 
		var initSetCaret = function(){ 
			var textObj = $(this).get(0); 
			textObj.caretPos = document.selection.createRange().duplicate(); 
		}; 
		$(this).click(initSetCaret).select(initSetCaret).keyup(initSetCaret); 
	}, 

	insertAtCaret: function(textFeildValue){ 
		var textObj = $(this).get(0); 
		if(document.all && textObj.createTextRange && textObj.caretPos){ 
			var caretPos=textObj.caretPos; 
			caretPos.text = caretPos.text.charAt(caretPos.text.length-1) == '' ? 
			textFeildValue+'' : textFeildValue; 
		} else if(textObj.setSelectionRange){ 
			var rangeStart=textObj.selectionStart; 
			var rangeEnd=textObj.selectionEnd; 
			var tempStr1=textObj.value.substring(0,rangeStart); 
			var tempStr2=textObj.value.substring(rangeEnd); 
			textObj.value=tempStr1+textFeildValue+tempStr2; 
			textObj.focus(); 
			var len=textFeildValue.length; 
			textObj.setSelectionRange(rangeStart+len,rangeStart+len); 
			textObj.blur(); 
		}else{ 
			textObj.value+=textFeildValue; 
		} 
	} 
});
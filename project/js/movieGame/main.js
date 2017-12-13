window.onload=function(){
	if($('.index-head span').length){
		var _oldCity=$('.index-head span').eq(0).html()
		var _tmpCity=''
		if(_oldCity.length>3){
			_tmpCity=_oldCity.substring(0,3)+"..."
			$('.index-head span').eq(0).html(_tmpCity)
		}
	}
	if($('.cinema-head span').length){
		var _oldCity=$('.cinema-head span').eq(0).html()
		var _tmpCity=''
		if(_oldCity.length>3){
			_tmpCity=_oldCity.substring(0,3)+"..."
			$('.cinema-head span').eq(0).html(_tmpCity)
			$('.cinema-head .search').eq(0).css('width','65%')
		}else if(_oldCity.length==3){
			$('.cinema-head .search').eq(0).css('width','67%')
		}else{
			$('.cinema-head .search').eq(0).css('width','69%')
		}
	}
	if($('.detail-content').length){
		var _content=$('.detail-content p').eq(0).html()
		var _shotContent=''
		if(_content.length>30){
			_shotContent=_content.substring(0,30)+'...'
			$('.detail-content .button').eq(0).data("_buttonContent",_content);
			$('.detail-content p').eq(0).html(_shotContent)
			$('.detail-content .button').eq(0).addClass('active')
		}
		$('.detail-content .button').eq(0)[0].onclick=function(){
			var _tmpContent=$('.detail-content p').eq(0).html()
			var _buttonContent=$('.detail-content .button').eq(0).data("_buttonContent");
			$('.detail-content p').eq(0).html(_buttonContent)
			$('.detail-content .button').eq(0).data("_buttonContent",_tmpContent);
			if($('.detail-content .button').eq(0).html()=='展开'){
				$('.detail-content .button').eq(0).html('收起')
			}else{
				$('.detail-content .button').eq(0).html('展开')
			}
		}
	}
}
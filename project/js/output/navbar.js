$(function(){
	navActive()
	for(var i=0;i<$('.index-foot .index-foot-item').length;i++){
		$('.index-foot .index-foot-item')[i].i=i
		$('.index-foot .index-foot-item')[i].onclick=function(){
			$('.index-foot .index-foot-item').each(function(i){
				$(this).removeClass('active')
			})
			$('.index-foot .index-foot-item').eq(this.i).addClass('active')
			navActive()
		}
	}
})
function navActive(){
	$('.index-foot .index-foot-item img').each(function(i){
		var _oldSrc=$(this)[0].src
		var _TmpSrc=_oldSrc.substring(0,_oldSrc.lastIndexOf('_'))
		var _newSrc=_TmpSrc+'_0.png'
		$(this)[0].src=_newSrc
	})
	var _activeOldSrc=$('.index-foot .active img')[0].src
	var _activeSrcTmp=_activeOldSrc.substring(0,_activeOldSrc.lastIndexOf('_0'))
	var _activeNewSrc=_activeSrcTmp+'_1.png'
	$('.index-foot .active img')[0].src=_activeNewSrc
}
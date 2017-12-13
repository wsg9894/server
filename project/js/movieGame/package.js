$(function(){
	for(var i=0;i<$('.package-game-right .package-game-head .button').length;i++){
		$('.package-game-right .package-game-head .button')[i].i=i
		$('.package-game-right .package-game-head .button')[i].onclick=function(){
			if($('.package-game-right').eq(this.i)[0].className.indexOf('active')>=0){
				$('.package-game-right').each(function(i){
					$(this).removeClass('active')
				})
			}else{
				$('.package-game-right').each(function(i){
					$(this).removeClass('active')
				})
				$('.package-game-right').eq(this.i).addClass('active')
			}
			toupdown(this.i)
		}
	}
})
function toupdown(i){

	var _oldSrc=$('.package-game-right .package-game-head .button').eq(i)[0].src
	var _tmpSrc=_oldSrc.substring(0,_oldSrc.indexOf('_'))
	$('.package-game-right .package-game-head .button').each(function(i){
		$(this)[0].src=_tmpSrc+'_0.png'
	})
	if($('.package-game-right').eq(i)[0].className.indexOf('active')>=0){
		$('.package-game-right .package-game-head .button').eq(i)[0].src=_tmpSrc+'_1.png'
	}else{
		$('.package-game-right .package-game-head .button').eq(i)[0].src=_tmpSrc+'_0.png'
	}
}
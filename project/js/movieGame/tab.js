$(function(){
	if($('.ranking-head').length>0){
		for(var i=0;i<$('.ranking-head .ranking-head-item').length;i++){
			$('.ranking-head .ranking-head-item')[i].i=i
			$('.ranking-head .ranking-head-item')[i].onclick=function(){
				$('.ranking-head .ranking-head-item').each(function(i){
					$(this).removeClass('active')
				})
				$('.ranking-head .ranking-head-item').eq(this.i).addClass('active')
				$('title').text($('.ranking-head .active p').text())
				$('.ranking-tabContent .ranking-tabContent-item').each(function(i){
					$(this).removeClass('active')
				})
				$('.ranking-tabContent .ranking-tabContent-item').eq(this.i).addClass('active')
			}
		}
	}
	if($('.my-head').length>0){
		for(var i=0;i<$('.my-head .my-head-item').length;i++){
			$('.my-head .my-head-item')[i].i=i
			$('.my-head .my-head-item')[i].onclick=function(){
				$('.my-head .my-head-item').each(function(i){
					$(this).removeClass('active')
				})
				$('.my-head .my-head-item').eq(this.i).addClass('active')
				$('.my-content .my-content-item').each(function(i){
					$(this).removeClass('active')
				})
				$('.my-content .my-content-item').eq(this.i).addClass('active')
			}
		}
	}
})
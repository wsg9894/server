$(function(){
	// var windowW=$(window).width()
	// var windowH=$(window).height()
	// $('.hide-poster .swiper-slide img').each(function(i){
	// 	var img = new Image();
	// 	img.src =$(this).attr("src") ;
	// 	var w = img.width;
	// 	var h = img.height;
	// 	if(w/h>=1){
	// 		$(this).css('top',(windowH-h)/2)
	// 	}else{
	// 		$(this).css('top',(windowH-h)/2)
	// 	}
	// 	console.log(w+" "+h)
	// })

	for(var i=0;i<$('.gamedetail-poster-group .swiper-slide').length;i++){
		$('.gamedetail-poster-group .swiper-slide')[i].i=i
		$('.gamedetail-poster-group .swiper-slide')[i].onclick=function(){
			$('.hide-poster').addClass('active')
			var hideSwiper = new Swiper('.hide-poster .swiper-container', {
		        initialSlide: this.i,
		        loop:true,
		    });
		}
	}
	$('.hide-poster').eq(0)[0].onclick=function(){
		$('.hide-poster').removeClass('active')
	}
})
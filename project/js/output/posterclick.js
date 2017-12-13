function posterclick(){
	for(var i=0;i<$('.detail-poster-group .swiper-container .swiper-wrapper .swiper-slide').length;i++){
		$('.detail-poster-group .swiper-container .swiper-wrapper .swiper-slide')[i].i=i
		$('.detail-poster-group .swiper-container .swiper-wrapper .swiper-slide')[i].onclick=function(){
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
}
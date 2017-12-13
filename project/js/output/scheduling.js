function dateOnclick(){
	// 为所有date添加i和点击事件
	var datelink = $(".scheduling-timetable .active .scheduling-daytable .swiper-wrapper .swiper-slide");
	$(datelink).each(function (ids,item) {
		if($(this).children().hasClass('active')){
			$('.scheduling-timetable .active .scheduling-timeinfo').each(function(index,items){
				$(this).removeClass('active')
				if(ids == index){
					$(this).addClass('active')
				}
			})
		}
	});
	if($('.cinema-table').length){
		for(var i=0;i<$('.cinema-table-daytable-item').length;i++){
			$('.cinema-table-daytable-item')[i].i=i
			$('.cinema-table-daytable-item')[i].onclick=function(){
				$('.cinema-table-daytable-item').each(function(i){
					$(this).removeClass('active')
				})
				$('.cinema-table-daytable-item').eq(this.i).addClass('active')
				date = $('.swiper-wrapper .active').text();
				$('.cinema-table-info').each(function(i){
					$(this).removeClass('active')
				})
				$('.cinema-table-info').eq(this.i).addClass('active')
				getRegionList(cityId);
			}
		}
	}
}
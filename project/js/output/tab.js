$(function(){
	var item_0_top=0;
	var item_1_top=0;
	for(var i=0;i<$('.ticket-head .tab-group .tab-group-item').length;i++){
		$('.ticket-head .tab-group .tab-group-item')[i].i=i
		$('.ticket-head .tab-group .tab-group-item')[i].onclick=function(){
			if(this.i==0){
				item_1_top=$(window).scrollTop()
				$(window).scrollTop(item_0_top)
			}else if(this.i==1){
				item_0_top=$(window).scrollTop()
				$(window).scrollTop(item_1_top)
			}
			$('.ticket-head .tab-group .tab-group-item').each(function(i){
				$(this).removeClass('active')
			})
			$('.ticket-head .tab-group .tab-group-item').eq(this.i).addClass('active')
			$('.ticket-table .ticket-table-item').each(function(i){
				$(this).removeClass('active')
			})
			$('.ticket-table .ticket-table-item').eq(this.i).addClass('active')
			if($('.ticket-head .tab-group .tab-group-item').eq(1)[0].className.indexOf('active')>=0){
				$('.ticket-head .search').eq(0).addClass('active')
				$('.ticket-head .city').eq(0).addClass('active')
			}else{
				$('.ticket-head .search').eq(0).removeClass('active')
				$('.ticket-head .city').eq(0).removeClass('active')
			}
		}
	}
})
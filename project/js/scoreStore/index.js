function timeCounting(obj,startTime,endTime,goodsLimit){
        var object=document.getElementsByName(obj)[0];
        var inter=setInterval(function(){
            var nowTime=parseInt(new Date().getTime()/1000);
            var resultTime="";

            var container=object.parentNode;
            var span=container.getElementsByTagName("span")[0];
            if(nowTime<startTime){
                //判断是否开始
                resultTime="即将开始";
                span.style.backgroundColor="#5dc280";
                span.style.display="block";
            }else if(nowTime>endTime){
                //时间结束
				if(goodsLimit>0)
				{
					resultTime="抢兑结束";
					span.style.display="block";
				}
            }else{
				

                var leftTime=endTime-nowTime;
                var leftDay=parseInt(leftTime/(60*60*24));
                var leftHour=parseInt(leftTime/(60*60)-leftDay*24);
                var leftMinute=parseInt(leftTime/(60)-leftDay*24*60-leftHour*60);
                var leftSecond=parseInt(leftTime-leftDay*24*60*60-leftHour*60*60-leftMinute*60);
                if(leftDay>0){
                    resultTime="";
                    span.style.backgroundColor="#5dc280";
                    // span.style.display="block";
                }else{
                    if(leftHour<10){
                        leftHour="0"+leftHour;
                    }
                    if(leftMinute<10){
                        leftMinute="0"+leftMinute;
                    }
                    if(leftSecond<10){
                        leftSecond="0"+leftSecond;
                    }
                    span.style.backgroundColor="#5dc280";
                    span.style.display="block";
                    resultTime=leftHour+":"+leftMinute+":"+leftSecond;
                }
            }
            object.innerHTML=resultTime;
        },1000);
    }

function tabs(selection){
    var tabsArr=document.getElementsByName("tab");
    var tabsContentArr=document.getElementsByName("tabsContent");
    if(selection!=null){
        tabsArr[selection].style.color="#5dc280";
        tabsContentArr[selection].style.display="block";
    }else{
        tabsArr[0].style.color="#5dc280";
        tabsContentArr[0].style.display="block";
    }
}

window.onload=function(){
    //自适应尺寸
    var width=window.innerWidth;
    if(width>=750){
    	width=750;
    }
    var html=document.getElementsByTagName("html")[0];
    html.style.fontSize=width/3.2+"px";
    var body=document.getElementsByTagName("body")[0];
    html.style.width=width+"px";

    //foot位置
    var goods=document.getElementsByName("goods");
    var foot=document.getElementById("foot");
	if(goods.length<4)
	{
		foot.style.top=2*1.8+"rem";
	}else{
		foot.style.top=parseInt((goods.length+1)/2)*1.8+"rem";
	}
    //选项卡
    var tabsArr=document.getElementsByName("tab");
    var tabsContentArr=document.getElementsByName("tabsContent");
    for(var i=0;i<tabsArr.length-1;i++){
        tabsArr[i].i=i;
        tabsArr[i].onclick=function(){
            for(var j=0;j<tabsArr.length;j++){
                tabsArr[j].style.color="#3c3c3c";
                tabsContentArr[j].style.display="none";
            }
            tabsArr[this.i].style.color="#5dc280";
            tabsContentArr[this.i].style.display="block";

			
			//商品列表页图片处理
            var goodbox=document.getElementsByName("good-box");
            var good=document.getElementsByName("good");
            var _w=0.4*width;
            var _h=0.253*width;
            for(var i=0;i<good.length;i++){
                var w=good[i].clientWidth;
                var h=good[i].clientHeight;
                if((w/h)>(_w/_h)){
                    good[i].style.width=_w+"px";
                    good[i].style.left="10%";
                    good[i].style.top=0.0713*width+(0.253*width-good[i].clientHeight)/2+"px";

                }else{
                    good[i].style.height=0.33*width+"px";
                    good[i].style.width="90%";
                    good[i].style.top="5%";
                    good[i].style.left=(_w-good[i].clientWidth)/2+goodbox[i].clientWidth*0.1+"px";
                }
            }

        }
    }

	//商品列表页图片处理
	var goodbox=document.getElementsByName("good-box");
    var good=document.getElementsByName("good");
    var _w=0.4*width;
    var _h=0.253*width;
    for(var i=0;i<good.length;i++){
        var w=good[i].clientWidth;
        var h=good[i].clientHeight;
        if((w/h)>(_w/_h)){
            good[i].style.width=_w+"px";
            good[i].style.left="10%";
            good[i].style.top=0.0713*width+(0.253*width-good[i].clientHeight)/2+"px";

        }else{
            good[i].style.height=0.33*width+"px";
            good[i].style.width="90%";
            good[i].style.top="5%";
            good[i].style.left=(_w-good[i].clientWidth)/2+goodbox[i].clientWidth*0.1+"px";
        }
    }

}
window.onresize=function(){
    var width=window.innerWidth;
    if(width>=750){
    	width=750;
    }
    var html=document.getElementsByTagName("html")[0];
    html.style.fontSize=width/3.2+"px";
    var body=document.getElementsByTagName("body")[0];
    html.style.width=width+"px";

	//商品列表页图片处理
    var goodbox=document.getElementsByName("good-box");
    var good=document.getElementsByName("good");
    var _w=0.4*width;
    var _h=0.253*width;
    for(var i=0;i<good.length;i++){
        var w=good[i].clientWidth;
        var h=good[i].clientHeight;
        if((w/h)>(_w/_h)){
            good[i].style.width=_w+"px";
            good[i].style.left="10%";
            good[i].style.top=0.0713*width+(0.253*width-good[i].clientHeight)/2+"px";

        }else{
            good[i].style.height=0.33*width+"px";
            good[i].style.width="90%";
            good[i].style.top="5%";
            good[i].style.left=(_w-good[i].clientWidth)/2+goodbox[i].clientWidth*0.1+"px";
        }
    }

}
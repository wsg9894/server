//个人中心（右上角）
$(function(){
    $("#myCenter").toggle(
        function () {
            $("#myCenterConter").css({display:'block'});
        },
        function () {
            $("#myCenterConter").css({display:'none'});
        }
    );
})

 function lodeSupport(){
	
    if(navigator.geolocation){
        //support.innerHTML = '将下面的经纬度输入谷歌地图(纬度 经度)查看自己位置：';
       //showDiv.style.display = 'block';
    navigator.geolocation.getCurrentPosition(getPosition);
    }
}
function getPosition(position){

    var latitudeP = position.coords.latitude;
    var longitudeP = position.coords.longitude;
    localStorage.setItem("latitudeP",latitudeP);
    localStorage.setItem("longitudeP",longitudeP);

    var myGeo = new BMap.Geocoder();
    // 根据坐标得到地址描述
    myGeo.getLocation(new BMap.Point(longitudeP, latitudeP), function(result){
        if (result){
            localStorage.setItem("city",result.addressComponents.city);
            localStorage.setItem("province",result.addressComponents.province);
            $.ajax({
                type:'get',
                url:'ajax/cinema.php',
                data: {cmd:1, subcmd:'1',cityStr:result.addressComponents.city},
                dataType:'json',
                async: false,
                success:function(cityId){
                    //localStorage.removeItem(cityID);
                    console.log('cityId='+cityId);
                    localStorage.setItem("cityId",cityId);
                },
                error:function(){
                    localStorage.setItem("cityId","1");
                }
            });
        }
    });
}
      
function hengshuping(){
    if(window.orientation==180||window.orientation==0){
        location.reload();//alert("竖屏状态！")
    }
    if(window.orientation==90||window.orientation==-90){
        location.reload();//alert("横屏状态！")
    }
}

if(localStorage.getItem("cityId") == null){
    window.addEventListener('load', lodeSupport , true);
}

window.addEventListener("onorientationchange" in window ? "orientationchange" : "resize", hengshuping, false);


//微信浏览器中隐藏 工具栏 后退前进刷新按钮等
//在微信公众平台 开发者模式的情况下，自定义的菜单跳转到自己的Web页，通过微信内置的浏览器来解析页面
//
//但是通常情况下，浏览器的工具栏 上下占用了屏幕不少的位置
document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
    WeixinJSBridge.call('hideToolbar');
    WeixinJSBridge.call('hideOptionMenu');
});

 function GetRequest() { 
	var url = location.search; //获取url中"?"符后的字串 
	var theRequest = new Object(); 
	if (url.indexOf("?") != -1) { 
		var str = url.substr(1); 
		strs = str.split("&"); 
		for(var i = 0; i < strs.length; i ++) { 
		theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]); 
		} 
	} 
	return theRequest; 
}

    var EARTH_RADIUS = 6378137.0;    //单位M
    var PI = Math.PI;
    
    function getRad(d){
        return d*PI/180.0;
    }
    
    /**
     * caculate the great circle distance
     * @param {Object} lat1
     * @param {Object} lng1
     * @param {Object} lat2
     * @param {Object} lng2
     */
    function getGreatCircleDistance(lat1,lng1,lat2,lng2){
        var radLat1 = getRad(lat1);
        var radLat2 = getRad(lat2);
        
        var a = radLat1 - radLat2;
        var b = getRad(lng1) - getRad(lng2);
        
        var s = 2*Math.asin(Math.sqrt(Math.pow(Math.sin(a/2),2) + Math.cos(radLat1)*Math.cos(radLat2)*Math.pow(Math.sin(b/2),2)));
        s = s*EARTH_RADIUS;
        s = Math.round(s*10000)/10000.0;
                
        return s;
    }


//错误信息提示
function errorMess(text){
    var string="<div id='errorMess' style='width:100%;position: absolute;bottom:30%;z-index:10;text-align: center;display:none;'>" +
        "<a style='background: #000;color:#fff;padding:5px 5px;border-radius:12px;font-size: 18px;'>"+text+"</a></div>";
    $("body").append(string);
    $("#errorMess").fadeIn("slow");
    setTimeout(function(){
        $("#errorMess").fadeOut("slow");
        $("#errorMess").remove();
    },3000);
}
//支付宝快捷支付
function checkConfirmOrder(data){
    if(data['status']==0){
        if(typeof(data['payInfo']['payUrl'])==''){
            location='/order/'+data['order_id'];
        }else{
            if(data['pay_method']==26){
                var dataObj=eval("("+'{}'+")");
                dataObj.ChrCode=data['payInfo']['payUrl']['chrCode'];
                dataObj.TransId=data['payInfo']['payUrl']['transId'];
                dataObj.MerchantID =data['payInfo']['payUrl']['merId'];
                dataObj.MerchantOrderID =data['payInfo']['payUrl']['merOrderId'];
                dataObj.ExtraCommonParam =data['order_id'];
                dataObj.MerSign=data['payInfo']['payUrl']['merSign'];
                dataObj.token=data['channel_token'];
                dataObj.amount="1000";
                window.plugin.pay(JSON.stringify(dataObj),"orderCallback");
            }else if(data['pay_method']==31){
                payData=data['payInfo']['payUrl'];
                MBC_PAY();
            }else if(data['pay_method']==32){
                $("#payForm").attr("action",data['payInfo']['payUrl']);
                for(i in data['payInfo']['payData']){
                    $("#payForm input[name="+i+"]").val(data['payInfo']['payData'][i]);
                }
                $("#payForm").submit();
            }else{
                location=data['payInfo']['payUrl'];
            }
        }
    }else{
        showMsg(data['error']);
    }
}
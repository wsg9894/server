var request = GetRequest();
var orderId = request.outerOrderId;
var countdown = '';
var today = new Date();
var loginStatus = 0;
var tomorrow = new Date((today/1000+86400)*1000);
var third = new Date((today/1000+86400*2)*1000);
var fourth = new Date((today/1000+86400*3)*1000);
var fifth = new Date((today/1000+86400*4)*1000);
var sixth = new Date((today/1000+86400*5)*1000);
var seventh = new Date((today/1000+86400*6)*1000);
var pay_type = 2;
var userMoney = 0;
var remainMoney = 0;
var timestamp = new Date().getTime()/1000;
var jsstep = 0;
var wiusepoint = 0;
var oneTotal = request.mPrice;
var couponArr = [];
var voucherArr = [];
var iRoommovieID='';
var cinemaId=0;
var movieId=0;
var datetime='';
var alertContent='';
//获取用户信息
getUserInfo = function() {
	if(loginStatus==0){
		$(".waiting").addClass("active");
	}
	var type = "mobileNo";
	var callbackFun = "getUserInfoCallback";
	var jsonParamObj = {
		type : type,
		callback : callbackFun,
		encrypt : "RSA"
	}
	var jsonParam = JSON.stringify(jsonParamObj);
	try {
		window.SysClientJs.getUserInfo(jsonParam);
	} catch (e) {
		setWebitEvent(jsonParam, "C05");
	}
}
//获取用户信息回调
getUserInfoCallback = function(json) {//data为返回的数据，数据为json格式
	var json = JSON.parse(json);
	if(json.STATUS==1){
		var getData = {'cipher':json.MSG};
		$.ajax({
			type: 'post', // 提交方式 get/post
			url: "index.php?r=output/Site/GetLoginInfo", // 需要提交的 url
			data: getData,
			dataType : "json",
			success: function (data) {
				if(data.ok){
					$(".waiting").removeClass("active");
					if(loginStatus==0){
						getOrderInfo(orderId);
					}
					loginStatus=1;

				}else{
					$(".waiting").removeClass("active");
					alert(data.resultDesc);
				}
			},
			timeout:3000,
		})
	}else{
		getLogin();
	}
}


//返回到原生app
backToApp = function(){
	try{
		window.SysClientJs.back();
	}catch(e){
		setWebitEvent("B05","B05");
	}
}


//登录状态判断
getLoginStatus = function() {
	try {
		window.SysClientJs.getLoginStatus("getLoginStatusCallback");
	} catch (e) {
		setWebitEvent("getLoginStatusCallbackFun()", "A06");
	}
}
//iPhone客户端获取js回调方法名
getLoginStatusCallbackFun = function() {
	return "getLoginStatusCallback";
}
//客户端的登录状态从此回调方法中获取，
getLoginStatusCallback = function(isLogin) {
	//isLogin(1-已登录，0-未登录)
	if(isLogin==0){
		getLogin();
	}else{
		getUserInfo();
	}
}


//跳转登陆页面
getLogin = function() {
	try {
		window.SysClientJs.getLogin("getLoginCallbackFunback");
	} catch (e) {
		setWebitEvent("getLoginCallbackFun()", "A05");
	}
}
//iPhone客户端获取js回调方法名
getLoginCallbackFun = function() {
	return "getLoginCallbackFunback()";
}
//客户端登录成功后会回调该方法
getLoginCallbackFunback = function() {
	getUserInfo();
}

$(document).ready(function(){
	getLoginStatus();
});

function getOrderInfo(orderId)
{
	$.ajax({
		type:'get',
		url:'index.php?r=output/Ajax/GetSeatOrderInfo',
		data: {orderId:orderId},
		dataType:'json',
		success:function(msg){
			console.log(msg);
			if (msg.ok)
			{
				var splitorderinfo = msg.order['orderInfo'].split(",");//座位信息
				wiusepoint = splitorderinfo.length;
				userMoney = parseInt(msg.usermoney); //用户余额
				remainMoney = msg.order['totalPrice'];  //需支付价格
				movieId=msg.order['iMovieID'];
				cinemaId=msg.order['iCinemaID'];
				datetime=msg.order['dPlayTime'].split(' ')[0];
				$('.pay-confirm').html('取票手机号：<input type="text" id="sPhone" placeholder="接收短信的手机号" value="'+msg.order['sPhone']+'"><h4>应支付:<span>'+remainMoney+'元</span></h4> <form id="pay" name="paysumit" action="" method="post"></form><button onclick="newpay();">立即支付</button>');
				showSeatInfo(msg.order);
				updateIntervalTime();
			}
			else
			{
				if(msg.msg.indexOf("http://")==-1)
				{
					alert(msg.msg);
					if(msg.err_code == 301){
						getLogin();return;
					}else{
						location.href='index.php?r=output/Site/ArrangeSelect&cinemaId='+msg.orderInfo["iCinemaID"]+'&movieId='+msg.orderInfo["iMovieID"]+'&showDate='+msg.orderInfo["dPlayTime"].split(" ")[0];return;
					}
				}else{
					location.href=msg.msg;
				}
			}

		},
		error:function(msg){
			console.log(msg);
			return false;
		}
	});
}
//显示座位信息
function showSeatInfo(seatinfo)
{
	countdown = seatinfo["createTime"];
	var beginday = '';
	var weekday = '';
	var artime = seatinfo["dPlayTime"].split(':');
	var hourtime = artime[0]+ ":"+artime[1];
	var ardate = seatinfo["dPlayTime"].split(' ');
	var splitorderinfo = seatinfo['orderInfo'].split(",");//座位信息
	var seatInfo="";
	iRoommovieID=seatinfo['iRoomMovieID'];
	for(var i=0;i<splitorderinfo.length;i++){
		seatInfo+='<span>'+splitorderinfo[i]+'</span>';
	}
	if (dateisequal(ardate[0], today)) {
		beginday =  '今天';
		weekday = getCWeek(today.getDay());
	}

	if (dateisequal(ardate[0], tomorrow)) {
		beginday =  '明天';
		weekday = getCWeek(tomorrow.getDay());
	}

	if (dateisequal(ardate[0], third)) {
		beginday =  '后天';
		weekday = getCWeek(third.getDay());
	}
	if (dateisequal(ardate[0], fourth)) {
		weekday = getCWeek(fourth.getDay());
	}

	if (dateisequal(ardate[0], fifth)) {
		weekday = getCWeek(fifth.getDay());
	}

	if (dateisequal(ardate[0], sixth)) {
		weekday = getCWeek(sixth.getDay());
	}

	if (dateisequal(ardate[0], seventh)) {
		weekday = getCWeek(seventh.getDay());
	}
	var content ='<h3>'+seatinfo['sThMovieName']+'</h3>'
		+'<p class="tip">'+beginday+ ' '+weekday + ' '+hourtime +'  '+seatinfo['sLanguage']+'/'+seatinfo['sDimensional']+'</p>'
		+'<p>'+seatinfo['sThCinemaName']+'</p>'
		+'<p>'+seatInfo+'</p>'
		+ '<div class="pay-head-countTime">'
		+ '<h4 id="countdown"></h4>'
		+ '</div>'
	alertContent='<h3>'+seatinfo['sThMovieName']+'</h3>'
		+'<p>'+seatinfo['sThCinemaName']+'</p>'
		+'<p>'+beginday+ ' '+weekday + ' '+hourtime +'  '+seatinfo['sLanguage']+'/'+seatinfo['sDimensional']+'</p>'
		+'<div class="button-group">'
		+'<div class="button cancel" id="quxiao">'
		+'<p>取消</p>'
		+'</div>'
		+'<div class="button ok" id="queding">'
		+'<p>确认支付</p>'
		+'</div>'
		+'</div>';
	$('.pay-head').html(content);
	if(request.pay!=undefined&&request.pay=='card'){
		$('#card strong').addClass('active');
	}else{
		$('#card strong').removeClass('active');
	}

	if(request.pay!=undefined&&request.pay=='ticket'){
		$('#voucher strong').addClass('active');
	}else{
		$('#voucher strong').removeClass('active');
	}

}

function updateIntervalTime()
{
	setTimeout(function(){

	},countdown*1000);
	jsstep = countdown;
	var interval=setInterval(getRTime,1000);//显示倒计时
}


//倒计时
function getRTime(){
	var rtimestamp = (new Date()).valueOf() / 1000;
	var rlogtime = rtimestamp - timestamp;
	if(jsstep<=1){
		$("#countdown").html('您的订单已过期');
		window.clearInterval(interval);

	}else{
		jsstep = jsstep - (rlogtime - (countdown - jsstep));
		if (jsstep<=1){
			$("#countdown").html('您的订单已过期');
			window.clearInterval(interval);
		}
	}
	jsstep=jsstep-1;
	var d=Math.floor(jsstep/60/60/24);
	var h=Math.floor(jsstep/60/60%24);
	var m=Math.floor(jsstep/60%60);
	var s=Math.floor(jsstep%60);
	if(s<10){
		s="0"+s;
	}
	var string= "请在"+m + "分"+s + "秒内支付";
	$("#countdown").html(string);
}

function dateisequal(date1,date2)
{
	var ardate = date1.split('-');
	var d2 = new Date(date2);
	return (ardate[0] == d2.getFullYear()
	&& ardate[1] == d2.getMonth()+1
	&& ardate[2] == d2.getDate());
}

function getCWeek(beginday) {
	var weekarray=Array("周日","周一","周二","周三","周四","周五","周六");
	return weekarray[beginday];
}


function selectSubmit(paySel,Onthis){
	switch (paySel){
		case 'accountPay':
			pay_type=1;
			break;
		case 'alibay':
			pay_type=2;
			break;
		case 'WeChat':
			pay_type=3;
			break;
		default :
			pay_type=0;
			break;
	}
	if($(Onthis).hasClass("active")){
		pay_type = 0;
		$(Onthis).removeClass("active");
	}else{
		$('.pay-way-item').removeClass("active");
		$(Onthis).addClass("active");
	}
}


$(function(){
    if(is_weixn()){
		wxShare();
    }else{
        $("body").append("<script src='js/scoreStore/shareComon.js' type='text/javascript'></script>");
        $("head").append("<link rel='stylesheet' type='text/css' href='css/scoreStore/shareCommon.css'>");
    }
})

function is_weixn(){
    var ua = navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i)=="micromessenger") {
        return true;
    } else {
        return false;
    }
}


function wxShare()
{
	wx.config({

    debug: false,
    appId: '<{$SIGNPACKAGE.appId}>',
    timestamp: '<{$SIGNPACKAGE.timestamp}>',
    nonceStr: '<{$SIGNPACKAGE.nonceStr}>',
    signature: '<{$SIGNPACKAGE.signature}>',
    jsApiList: [
        // 所有要调用的 API 都要加到这个列表中
		'onMenuShareAppMessage',
		'onMenuShareQQ',
		'onMenuShareTimeline',
		'onMenuShareQZone',
		]
	});

	wx.ready(function(){
		//分享给朋友
		wx.onMenuShareAppMessage({
			title: '<{$WXSHARECONF.wxTitle}>', // 分享标题
			desc: '<{$WXSHARECONF.wxDesc}>', // 分享描述
			link: '<{$BASEURL}>project/index.php?r=scoreStore/Site/InviteRespond_Step1&uid=<{$UID}>', // 分享链接
			imgUrl: '<{$WXSHARECONF.wxPic}>', // 分享图标
			type: '', // 分享类型,music、video或link，不填默认为link
			dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
			success: function (e) {
				// 用户确认分享后执行的回调函数
			},
			cancel: function (e) {
				// 用户取消分享后执行的回调函数
			}
		});
		//分享到qq
		wx.onMenuShareQQ({
			title: '<{$WXSHARECONF.wxTitle}>', // 分享标题
			desc: '<{$WXSHARECONF.wxDesc}>', // 分享描述
			link: '<{$BASEURL}>project/index.php?r=scoreStore/Site/InviteRespond_Step1&uid=<{$UID}>', // 分享链接
			imgUrl:'<{$WXSHARECONF.wxPic}>', // 分享图标
		});
		
		//分享到朋友圈
		wx.onMenuShareTimeline({
			title: '<{$WXSHARECONF.wxTitle}>', // 分享标题
			link: '<{$BASEURL}>project/index.php?r=scoreStore/Site/InviteRespond_Step1&uid=<{$UID}>', // 分享链接
			imgUrl: '<{$WXSHARECONF.wxPic}>', // 分享图标
		});
		
		//分享到QQ空间
		wx.onMenuShareQZone({
			title: '<{$WXSHARECONF.wxTitle}>', // 分享标题
			desc: '<{$WXSHARECONF.wxDesc}>', // 分享描述
			link: '<{$BASEURL}>project/index.php?r=scoreStore/Site/InviteRespond_Step1&uid=<{$UID}>', // 分享链接
			imgUrl: '<{$WXSHARECONF.wxPic}>', // 分享图标
		});

	});//ready 结束
}
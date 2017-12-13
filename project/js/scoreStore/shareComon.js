$(function(){
    if(!is_weixn()){
        var share_desc = '一起来吧！';
        //var share_url = window.location.href;
		var share_url = '<{$BASEURL}>project/index.php?r=scoreStore/Site/InviteRespond_Step1&uid=<{$UID}>';
        var share_img = share_url+'/../images/weid.jpg';
        window._bd_share_config = {
            common : {
                //分享的内容
                bdText : share_desc,
                bdUrl : share_url,
                bdPic : share_img,
                bdMini:1,
                bdSign:'off',
            },
            share : [{
                "bdSize" : 32,
            }],
        }
        with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?cdnversion='+~(-new Date()/36e5)];
    }
})
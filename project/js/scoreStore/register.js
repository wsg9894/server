$(function(){
    window.onload=htmlSize;
    window.onresize =htmlSize;
    var iHuoDongID=99;
    var from = getParameterByName('from');
    if(from.length == 0) from = "1";
    var phone = $("#TxtEpwSphoneNo");
    var pass = $("#TxtEpwPassword");
    var code = $("#TxtEpwVCode");
    var picVCode= $("#picVCode");
	var inviteeUid = $("#inviteeUid");
 	var inviteePhone = $("#inviteePhone");
   //初始化页面
    if($("#AEpwVcodeToSphone").size() > 0){
        $.ajax({
            type:'get',
            url:'/ajax/actLogin.php',
            data: {cmd:1, subcmd:'5'},
            dataType:'json',
            success:function(msg){
                if (msg.ok) {
                    if(msg.time){
                        times(msg.time);
                    }
                }
            },
            error:function(msg){
                return false;
            }
        });
    }
    $("#checkpic").click(function(){
        document.getElementById('checkpic').src="/sec/securimage_show.php?"+Math.random();
    });
    
    //发送验证码
	$("#AEpwVcodeToSphone").click(function() {

		if(inviteePhone.val()==phone.val())
		{
			alert('送给好友的礼包怎么好意思再拿回来~');
			$("#AEpwVcodeToSphone").removeAttr("disabled");
			return false;
		}
		
        if(validatemobile(phone.val())){
            $('#AEpwVcodeToSphone').attr("disabled", true);
            $.ajax({
                type:'get',
                url:'/ajax/actLogin.php',
                data: {cmd:1, subcmd:'3',picCode:picVCode.val(),phone:phone.val()},
                dataType:'json',
                success:function(msg){
                    if (msg.ok) {
                        alert("验证码已下发，请注意查收");
                        times(180);
                    }else {
                        alert(msg.msg);
                        $("#AEpwVcodeToSphone").removeAttr("disabled");
                    }
                },
                error:function(msg){
                    $("#AEpwVcodeToSphone").removeAttr("disabled");
                    return false;
                }
            });
        }
		
	});
    $("#register").click(function(e) {
        e.preventDefault();
        if(!validatemobile(phone.val())) {
            return false;
        }
        if(code.val()==""){
            alert("验证码为空");
            return false;
        }
		$.ajax({
            type:'get',
            url:'/ajax/actLogin.php',
            data: {cmd:1, subcmd:'4',phone:phone.val(),code:code.val(),picCode:picVCode.val(),actName:"score_store",iHuoDongID:iHuoDongID},
            dataType:'json',
            success:function(msg){
                if (msg.ok) {
                    window.location.href="index.php?r=scoreStore/Site/InviteRespond_Step2&uid="+inviteeUid.val();
                }else {
                    alert(msg.msg);
                    document.getElementById('checkpic').src="/sec/securimage_show.php?"+Math.random();
                }
            },
            error:function(msg){
                console.log(msg);
                return false;
            }
        });
	});
});
function backUrl(){
    var url=window.location.search;
    var go = url.replace("?","");
    var backUrl=go.replace("go=","");
    return backUrl;
}

function times(timer) {
	
    var step = timer;
    $('#AEpwVcodeToSphone').val('重新发送' + step);
    var _res = setInterval(function () {
        $("#AEpwVcodeToSphone").attr("disabled", true); //设置disabled属性
        $('#AEpwVcodeToSphone').val('重新发送' + step);
        step -= 1;
        if (step <= 0) {
            $("#AEpwVcodeToSphone").removeAttr("disabled"); //移除disabled属性
            $('#AEpwVcodeToSphone').val('获取验证码');
            clearInterval(_res); //清除setInterval
        }
    }, 1000);
}



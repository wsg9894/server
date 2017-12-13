$(function(){
   
});


//支付
function pay(){
    if(iswillpay == true){
        console.log(pay_type);
        var password = $.trim($("#accountpassword").val());
        if(pay_type == 1){
            if (userMoney<remainMoney){
                alert('您的账户余额不足，请充值。');
                return false;
            }
        }
        $('#pay_alert').css('display','block');
        $('#queding').click(function () {
            $('#pay').attr('action', '/pay/SeatSubmit.php?orderId='+orderId+'&payType='+pay_type+'&password='+password);
            $('#pay').submit();
        });
        $('#quxiao').click(function () {
            $('#pay_alert').css('display','none');
        });

    }else{
        return false;
    }


}

function accountInput(th){
    var regStrs = [
        ['^0(\\d+)$', '$1'], //禁止录入整数部分两位以上，但首位为0
        ['[^\\d\\.]+$', ''], //禁止录入任何非数字和点
        ['\\.(\\d?)\\.+', '.$1'], //禁止录入两个以上的点
        ['^(\\d+\\.\\d{2}).+', '$1'] //禁止录入小数点后两位以上
    ];
    for(i=0; i<regStrs.length; i++){
        var reg = new RegExp(regStrs[i][0]);
        th.value = th.value.replace(reg, regStrs[i][1]);
    }
}
//查找兑换码
function findCoupon(){
    if($.trim($("#sCheckNo").val())==''){
        Boxy.tip("请输入正确的通兑券/电影卡卡号",2000);
        return false;
    }
    if($.trim($("#sPassWord").val())==''){
        Boxy.tip("请输入正确的通兑券/电影卡密码",2000);
        return false;
    }
    //var obj=$("#hdcodefind");
    //var onclick=$("#hdcodefind").attr("onclick");
    $.ajax({
        url:'/ajax/coupon.php',
        data:{cmd:2,subcmd:1,sCheckNo:$("#sCheckNo").val(),sPassWord:$("#sPassWord").val(),MaxPrice:oneTotal,outerOrderId:orderId},
        dataType:'json',
        //beforeSend: function(result){ obj.removeAttr("onclick");$(this).attr('id','RepeatClick');},
        success:function(data){
            $(this).attr('id','');
            //console.log(data);
            if(data['status']==0){
                $("#sCheckNo").val('');
                $("#sPassWord").val('');
               getOrderInfo(orderId);
            }else{
                Boxy.tip(data['error'],2000);
            }
        },
        
        complete: function(result){obj.attr({onclick:onclick})},
        error:function(msg){
                console.log(msg);
                   return false;
               }
    });
}

//查找兑换码
function findVoucher(){
    if($.trim($("#sVoucherPassWord").val())==''){
        Boxy.tip("请输入正确的现金券码",2000);
        return false;
    }

   var voucherPassword = $("#sVoucherPassWord").val();
   //var obj=$("#hdcodefind");
   // var onclick=$("#hdcodefind").attr("onclick");
    $.ajax({
        url:'/ajax/coupon.php',
        data:{cmd:1,subcmd:5,sPassword:voucherPassword,outerOrderId:orderId},
        dataType:'json',
        
        success:function(data){
            if(data['status']==0){
                 getOrderInfo(orderId);
            }else{
                Boxy.tip(data['error'],2000);
            }
        },
        
       complete: function(result){obj.attr({onclick:onclick})},
        error:function(msg){
            console.log(msg);
            return false;
        }
    });
}
//绑定现金券
function bgVoucher(){
    if($.trim($("#sVoucherPassWord").val())==''){
        Boxy.tip("请输入正确的现金券码",2000);
        return false;
    }

    var voucherPassword = $("#sVoucherPassWord").val();
    var obj=$("#hdcodefind");
    var onclick=$("#hdcodefind").attr("onclick");
    $.ajax({
        url:'/ajax/userCenter.php',
        data:{cmd:7,subcmd:7,voucherNo:voucherPassword,outerOrderId:orderId},
        dataType:'json',

        success:function(data){
            if(data['flag']){
                getOrderInfo(orderId);
            }else{
                Boxy.tip(data['error'],2000);
            }
        },

        complete: function(result){obj.attr({onclick:onclick})},
        error:function(msg){
            console.log(msg);
            return false;
        }
    });
}
//绑定电影卡
function bgCoupon(){
    if($.trim($("#sCheckNo").val())==''){
        Boxy.tip("您输入的卡号或密码不正确",2000);
        return false;
    }
    if($.trim($("#sPassWord").val())==''){
        Boxy.tip("您输入的卡号或密码不正确",2000);
        return false;
    }

    var obj=$("#hdcodefind");
    var onclick=$("#hdcodefind").attr("onclick");
    $.ajax({
        url:'/ajax/userCenter.php',
        data:{cmd:22,subcmd:22,checkNo:$("#sCheckNo").val(),pass:$("#sPassWord").val(),outerOrderId:orderId},
        dataType:'json',
        beforeSend: function(result){ obj.removeAttr("onclick");$(this).attr('id','RepeatClick');},
        success:function(data){
            $(this).attr('id','');
            //console.log(data);
            if(data['status']==0){
                $("#sCheckNo").val('');
                $("#sPassWord").val('');
                getOrderInfo(orderId);
            }else{
                Boxy.tip(data['msg'],2000);
            }
        },

        complete: function(result){obj.attr({onclick:onclick})},
        error:function(msg){
            console.log(msg);
            return false;
        }
    });
}


//H5改版-确认电影卡或者现金券选择(特价购票)-anqing
function selectCouSubmit(){
    checkcoupon();
    $('.seactionCard').removeClass('active');
    $('.seactionBuy').addClass('active');
}
function cencelCouSubmit(){
    couponArr=[];
    checkcoupon();
    $('.seactionCard').removeClass('active');
    $('.seactionBuy').addClass('active');
}
function selectVouSubmit(){
    checkVoucher();
    $('.seactionTicket').removeClass('active');
    $('.seactionBuy').addClass('active');
}
function cencelVouSubmit(){
    voucherArr=[];
    checkVoucher();
    $('.seactionTicket').removeClass('active');
    $('.seactionBuy').addClass('active');
}
//H5改版-支付功能-anqing
function newpay(){
    //验证手机号
    var phone = $('#sPhone').val();
    if(phone==""){
        Boxy.tip('请输入手机号',2000);
        return false;
    }
    if(!validatemobile(phone)){
        return false;
    }
    if(pay_type == 1) {
        if (userMoney < remainMoney) {
            Boxy.tip('您的账户余额不足，请充值', 2000);
            return false;
        }
    }
    if(jsstep<=1){
        alert("您的订单已超时");
        location.href = 'https://center.ingcore.com/film/hotList.do?key=06fddf3d8d5411e7ab6300163e2e452e';
        return false;
    }
    $('.alertContent').html(alertContent);
    $('.alert').addClass('active');
    $('#queding').click(function () {
        $('#pay').attr('action', 'index.php?r=output/Site/SeatSubmit&orderId='+orderId+'&payType='+pay_type+'&sPhone='+phone);
        $('#pay').submit();
    });
    $('#quxiao').click(function () {
        $('.alert').removeClass('active');
    });
}

//手机格式验证
function validatemobile(mobile){
    if(mobile.length==0)
    {
        Boxy.tip('手机号不能为空！',2000);
        return false;
    }
    if(mobile.length!=11)
    {
        Boxy.tip('请输入有效的手机号码！',2000);

        return false;
    }
    var myreg =/^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|17[0-9])\d{8}$/;
    if(!myreg.test(mobile))
    {
        Boxy.tip('请输入有效的手机号码！',2000);

        return false;
    }
    return true;
}
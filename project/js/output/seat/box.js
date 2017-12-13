jQuery.extend({
    //
    jsJia:function (){
        var result=0,point=0,max=1,tmp;
        for(var i=0;i<arguments.length;i++){
            try{tmp=arguments[i].toString().split(".")[1].length}catch(e){tmp=0}
            if(tmp>point){max=Math.pow(10,tmp);point=tmp}
        }
        for(var i=0;i<arguments.length;i++){
            if(max>1){
                result+=arguments[i]*max;
            }else{
                result+=arguments[i]
            }
        }
        return (result/max).toFixed(point);
    },
    jsJian:function(){
        var result=0,point=0,max=1,tmp;
        for(var i=0;i<arguments.length;i++){
            try{tmp=arguments[i].toString().split(".")[1].length}catch(e){tmp=0}
            if(tmp>point){max=Math.pow(10,tmp);point=tmp}
        }
        for(var i=0;i<arguments.length;i++){
            if(result==0){
                result=arguments[i]*max;
            }else{
                if(max>0){
                    result-=arguments[i]*max;
                }else{
                    result-=arguments[i];
                }
            }
        }
        if(result>=0) return (result/max).toFixed(point);
        if(result<0) return -((Math.abs(result)/max).toFixed(point));
    },
    jsCheng:function (){
        var result=1,point= 0,max= 1,tmp;
        for(var i=0;i<arguments.length;i++){
            try{tmp=arguments[i].toString().split(".")[1].length}catch(e){tmp=0}
            if(tmp>point){max=Math.pow(10,tmp);point=tmp}
        }
        for(var i=0;i<arguments.length;i++){
            result*=Number(arguments[i].toString().replace(".",""))
        }
        return result/max;
    },
    //弹出指定的内容，参数为html内容
    con: function(content) {
//        var width  =$(content).width()+"px";
//        var height  =$(content).height()+"px";
        var bodyWidth  = document.body.scrollWidth;
        var bodyHeight = document.body.scrollHeight;
        var div_zhe_zhao  = $("<div></div>"); //遮罩层
        var div_tan_chuang = $("<div></div>"); //弹出层的信息展现
        div_zhe_zhao.css({
            width:bodyWidth,height:bodyHeight,
            zIndex:9990,opacity: 0.7,background:"#666", filter:"alpha(opacity=50)",
            position:"absolute", top:0,left:0
        })
        div_zhe_zhao.attr({id:"zrf_zhezhao2015"});
        div_tan_chuang.css({
            width:"auto",height:"auto",
            backgroundColor:"#fff",margin:"0 auto",zIndex:9991,
            position:"fixed",top:0,left:0,textAlign:"center"
        })

        div_tan_chuang.attr({id:"zrf_div_tan_chuang"});
        div_zhe_zhao.appendTo("body");
        div_tan_chuang.appendTo("body");
        div_tan_chuang.html(content);
        var width  =$("#zrf_div_tan_chuang").children().eq(0).width();
        var height  =$("#zrf_div_tan_chuang").children().eq(0).height();
        var wondowWidth  = ($(window).width()-parseInt(width))/2;
        var wondowHeight = ($(window).height()-parseInt(height))/2;
        $("#zrf_div_tan_chuang").css({top:wondowHeight-50,left:wondowWidth})
    },
    //再弹出的基础上再次 弹出指定的内容，参数为html内容
    contop: function(content) {
        var bodyWidth  = document.body.scrollWidth;
        var bodyHeight = document.body.scrollHeight;
        var div_zhe_zhao  = $("<div></div>"); //遮罩层
        var div_tan_chuang = $("<div></div>"); //弹出层的信息展现
        div_zhe_zhao.css({
            width:bodyWidth,height:bodyHeight,
            zIndex:9993,opacity: 0.7,background:"#666", filter:"alpha(opacity=50)",
            position:"absolute", top:0,left:0
        })
        div_zhe_zhao.attr({id:"zrf_zhezhao2015_contop"});
        div_tan_chuang.css({
            width:"auto",height:"auto",
            backgroundColor:"#fff",margin:"0 auto",zIndex:9994,
            position:"fixed",top:0,left:0,textAlign:"center"
        })

        div_tan_chuang.attr({id:"zrf_div_tan_chuang_contop"});
        div_zhe_zhao.appendTo("body");
        div_tan_chuang.appendTo("body");
        div_tan_chuang.html(content);
        var width  =$("#zrf_div_tan_chuang_contop").children().eq(0).width();
        var height  =$("#zrf_div_tan_chuang_contop").children().eq(0).height();
        var wondowWidth  = ($(window).width()-parseInt(width))/2;
        var wondowHeight = ($(window).height()-parseInt(height))/2;
        $("#zrf_div_tan_chuang_contop").css({top:wondowHeight-50,left:wondowWidth})
    },
    //关闭再次弹出的内容
    close_contop: function (){
        $("#zrf_zhezhao2015_contop,#zrf_div_tan_chuang_contop").remove();
    },
    trueMsg: function(msg,callBack) {
        var width  ="400px";
        if(msg.length<=12){
            var div_text_height="60px"
        }else if(msg.length>12&&msg.length<24){
            var div_text_height="80px";
        }else{
            var div_text_height="100px";
        }
        var bodyWidth  = document.body.scrollWidth;
        var bodyHeight = document.body.scrollHeight;
        var wondowWidth  = ($(window).width()-parseInt(width))/2;
       // var wondowHeight = ($(window).height()-parseInt(height))/2;
        var div_zhe_zhao  = $("<div></div>"); //遮罩层
        var div_tan_chuang = $("<div></div>"); //弹出层的信息展现
        var div_close = $("<img src='/newpublic/images/tan/true_close.png'>"); //关闭按钮
        var div_text = $("<div></div>"); //提示文子
        var div_button=$("<div></div>"); //按钮盒子
        var button = $("<div>确定</div>"); //按钮
        div_zhe_zhao.css({
            width:bodyWidth,height:bodyHeight,
            zIndex:9990,opacity: 0.7,background:"#666", filter:"alpha(opacity=50)",
            position:"absolute", top:0,left:0
        })
        div_zhe_zhao.attr({id:"zrf_zhezhao2015"});
        div_tan_chuang.css({
            width:width,height:"auto",backgroundColor:"#fff",margin:"0 auto", borderTop:"3px solid #008CFF",zIndex:9991,
            position:"fixed",top:"auto",left:wondowWidth,paddingBottom:"30px",paddingTop:"30px"
        })
        div_tan_chuang.attr({id:"zrf_div_tan_chuang"});
        div_close.css({
            top:0,left:parseInt(width)-30,background:"green",cursor: "pointer",
            position:"absolute",width:"30px",height:"30px"
        })
        div_close.attr({onclick:"jQuery.close()"});
        div_text.css({
            background:"url(/newpublic/images/tan/true.png)no-repeat 1px 1px",paddingLeft:"60px",
            width:"250px",height:div_text_height,lineHeight:"40px",display:"block",
            margin:"0 auto 0",fontSize:"18px",wordWrap: "break-word",
            wordBreak: "normal"
        })
        div_text.html(msg);
        div_button.css({
            width:"100%",height:"40px",textAlign:"center",paddingTop:"20px"
        })
        button.css({
            width:"158px",height:"36px",lineHeight:"36px",textAlign:"center",background:"#008CFF",
            textAlign:"center",color:"#fff",margin:" 0 auto",cursor: "pointer",fontSize:"18px"
        })
        var string="'"+callBack+"'";
        if(string.indexOf("function") > 0 ){
            var documentClick="("+callBack+")";
            var click=documentClick+"()";
        }else{
            var click=callBack;
        }
        button.attr({onclick:click+';jQuery.close();'})
        div_zhe_zhao.appendTo("body");
        div_tan_chuang.appendTo("body");
        div_close.appendTo(div_tan_chuang);
        div_text.appendTo(div_tan_chuang);
        div_button.appendTo(div_tan_chuang);
        button.appendTo(div_button);

        var height  =$("#zrf_div_tan_chuang").height();
        var wondowHeight = ($(window).height()-parseInt(height))/2;
        $("#zrf_div_tan_chuang").css({top:wondowHeight-50});
    },

    //参数 ['提示文字',['确定','aaa(123)'],['取消','bbb(123)']];
    conButton: function(arr) {
        var width  ="400px";
        if(arr[0].length<=12){
            var div_text_height="60px"
        }else if(arr[0].length>12&&arr[0].length<24){
            var div_text_height="80px";
        }else{
            var div_text_height="100px";
        }
        var bodyWidth  = document.body.scrollWidth;
        var bodyHeight = document.body.scrollHeight;
        var wondowWidth  = ($(window).width()-parseInt(width))/2;
        // var wondowHeight = ($(window).height()-parseInt(height))/2;
        var div_zhe_zhao  = $("<div></div>"); //遮罩层
        var div_tan_chuang = $("<div></div>"); //弹出层的信息展现
        var div_close = $("<img src='/newpublic/images/tan/true_close.png'>"); //关闭按钮
        var div_text = $("<div></div>"); //提示文子
        var div_button=$("<div></div>"); //按钮盒子
        var button1 = $("<div></div>"); //按钮1
        var button2 = $("<div></div>"); //按钮2
        div_zhe_zhao.css({
            width:bodyWidth,height:bodyHeight,
            zIndex:9990,opacity: 0.7,background:"#666", filter:"alpha(opacity=50)",
            position:"absolute", top:0,left:0
        })
        div_zhe_zhao.attr({id:"zrf_zhezhao2015"});
        div_tan_chuang.css({
            width:width,height:"auto",backgroundColor:"#fff",margin:"0 auto", borderTop:"3px solid #008CFF",zIndex:9991,
            position:"fixed",top:"auto",left:wondowWidth,paddingBottom:"30px"
        })
        div_tan_chuang.attr({id:"zrf_div_tan_chuang"});
        div_close.css({
            top:0,left:parseInt(width)-30,background:"green",cursor: "pointer",
            position:"absolute",width:"30px",height:"30px"
        })
        div_close.attr({onclick:"jQuery.close()"});
        div_text.html(arr[0]);
        div_text.css({
            background:"url(/newpublic/images/loading.gif)no-repeat 1px 5px",paddingLeft:"50px",
            width:"250px",height:div_text_height,lineHeight:"40px",display:"block",
            margin:"30px 0 0 30px",fontSize:"18px",wordWrap: "break-word",textAlign:"center",
            wordBreak: "normal"
        })
        div_button.css({
            width:"100%",height:"40px",textAlign:"center",paddingTop:"20px"
        })
        button1.html(arr[1][0]);
        button1.attr({onclick:arr[1][1]+";jQuery.close();"})
        button1.css({
            width:"100px",height:"36px",lineHeight:"36px",textAlign:"center",background:"#008CFF",
            textAlign:"center",color:"#fff",margin:" 0 auto",cursor: "pointer",fontSize:"16px",float:"left",
            margin:"0 45px 0 80px",cursor: "pointer"
        })

        button2.html(arr[2][0]);
        button2.attr({onclick:arr[2][1]+";jQuery.close();"})
        button2.css({
            width:"100px",height:"36px",lineHeight:"36px",textAlign:"center",background:"#ccc",cursor: "pointer",
            textAlign:"center",color:"#fff",margin:" 0 auto",cursor: "pointer",fontSize:"16px",float:"left"
        })

        div_zhe_zhao.appendTo("body");
        div_tan_chuang.appendTo("body");
        div_close.appendTo(div_tan_chuang);
        div_text.appendTo(div_tan_chuang);
        div_button.appendTo(div_tan_chuang);
        button1.appendTo(div_button);
        button2.appendTo(div_button);

        var height  =$("#zrf_div_tan_chuang").height();
        var wondowHeight = ($(window).height()-parseInt(height))/2;
        $("#zrf_div_tan_chuang").css({top:wondowHeight-50});
    },
    falseMsg: function(msg,callBack){
        var width  ="400px";
        if(msg.length<=11){
            var div_text_height="60px"
        }else if(msg.length>11&&msg.length<24){
            var div_text_height="80px";
        }else{
            var div_text_height="100px";
        }
        var bodyWidth  = document.body.scrollWidth;
        var bodyHeight = document.body.scrollHeight;
        var wondowWidth  = ($(window).width()-parseInt(width))/2;
       // var wondowHeight = ($(window).height()-parseInt(height))/2;
        var div_zhe_zhao  = $("<div></div>"); //遮罩层
        var div_tan_chuang = $("<div></div>"); //弹出层的信息展现
        var div_close = $("<img src='/newpublic/images/tan/false_close.png'>"); //关闭按钮
        var div_text = $("<div></div>"); //提示文子
        var div_button=$("<div></div>"); //按钮盒子
        var button = $("<div>确定</div>"); //按钮
        div_zhe_zhao.css({
            width:bodyWidth,height:bodyHeight,
            zIndex:9990,opacity: 0.7,background:"#666", filter:"alpha(opacity=50)",
            position:"absolute", top:0,left:0
        })
        div_zhe_zhao.attr({id:"zrf_zhezhao2015"});
        div_tan_chuang.css({
            width:width,height:"auto",backgroundColor:"#fff",margin:"0 auto", borderTop:"3px solid #FF6900",zIndex:9991,
            position:"fixed",top:0,left:wondowWidth,paddingBottom:"30px",paddingTop:"30px"
        })
        div_tan_chuang.attr({id:"zrf_div_tan_chuang"});
        div_close.css({
            top:0,left:parseInt(width)-30,background:"green",cursor: "pointer",
            position:"absolute",width:"30px",height:"30px"
        })
        div_close.attr({onclick:"jQuery.close();"});
        div_text.css({
            background:"url(/newpublic/images/tan/false.png)no-repeat 1px 1px",paddingLeft:"60px",
            width:"250px",height:div_text_height,lineHeight:"40px",display:"block",
            margin:"0 auto 0",fontSize:"20px",wordWrap: "break-word",
            wordBreak: "normal"
        })
        div_text.html(msg);
        div_button.css({
            width:"100%",height:"40px",textAlign:"center",paddingTop:"20px"
        })
        button.css({
            width:"158px",height:"36px",lineHeight:"36px",textAlign:"center",background:"#FF6900",
            textAlign:"center",color:"#fff",margin:" 0 auto",cursor: "pointer",fontSize:"18px"
        })
        var string="'"+callBack+"'";
        if(string.indexOf("function") > 0 ){
            var documentClick="("+callBack+")";
            var click=documentClick+"()";
        }else{
            var click=callBack;
        }
        button.attr({onclick:click+';jQuery.close();'})
        div_zhe_zhao.appendTo("body");
        div_tan_chuang.appendTo("body");
        div_close.appendTo(div_tan_chuang);
        div_text.appendTo(div_tan_chuang);
        div_button.appendTo(div_tan_chuang);
        button.appendTo(div_button);

        var height  =$("#zrf_div_tan_chuang").height();
        var wondowHeight = ($(window).height()-parseInt(height))/2;
        $("#zrf_div_tan_chuang").css({top:wondowHeight-50});
    },
    //确认和取消的按钮
    confirm: function(msg,callBack){
        var width  ="400px";
        if(byteLength(msg)>20){
            var height ="250px";
            var div_text_height="150px"
        }else{
            var height ="200px";
            var div_text_height="100px"
        }
//

        var documentClick="("+callBack+")";
        var click=documentClick+"()";
//        if(typeof callBack == "function"){
//            var onclick=callBack();
//        }
        var bodyWidth  = document.body.scrollWidth;
        var bodyHeight = document.body.scrollHeight;
        var wondowWidth  = ($(window).width()-parseInt(width))/2;
        var wondowHeight = ($(window).height()-parseInt(height))/2;
        var div_zhe_zhao  = $("<div></div>"); //遮罩层
        var div_tan_chuang = $("<div></div>"); //弹出层的信息展现
        var div_text = $("<div></div>"); //提示文子
        var div_button=$("<div></div>"); //按钮盒子
        var button_queren = $("<div onclick='jQuery.close();jQuery.testfnc();'>确定</div>"); //确认按钮
        var button_quxiao = $("<div onclick='jQuery.close();'>取消</div>"); //取消按钮
        div_zhe_zhao.css({
            width:bodyWidth,height:bodyHeight,
            zIndex:9990,opacity: 0.7,background:"#666", filter:"alpha(opacity=50)",
            position:"absolute", top:0,left:0
        })
        div_zhe_zhao.attr({id:"zrf_zhezhao2015"});
        div_tan_chuang.css({
            width:width,height:height,backgroundColor:"#fff",margin:"0 auto", borderTop:"3px solid #008CFF",zIndex:9991,
            position:"fixed",top:wondowHeight,left:wondowWidth
        })
        div_tan_chuang.attr({id:"zrf_div_tan_chuang"});
        div_text.css({
            background:"url(/newpublic/images/tan/false.png)no-repeat 1px 1px",paddingLeft:"50px",
            width:"250px",height:div_text_height,lineHeight:"40px",display:"block",
            margin:"30px auto 0",fontSize:"20px",wordWrap: "break-word",
            wordBreak: "normal"
        })
        div_text.html(msg);
        div_button.css({
            width:"100%",height:"40px",textAlign:"center",fontSize:"16px"
        })
        button_queren.css({
            width:"100px",height:"40px",lineHeight:"40px",textAlign:"center",background:"#008CFF",
            textAlign:"center",color:"#fff",margin:" 0 auto",cursor: "pointer",float:"left",margin:"0 50px 0 80px"
        })
        button_queren.attr({onclick:click+';jQuery.close();'})
        button_quxiao.css({
            width:"100px",height:"40px",lineHeight:"40px",textAlign:"center",background:"#ccc",
            textAlign:"center",color:"#fff",margin:" 0 auto",cursor: "pointer",float:"left"
        })
        div_zhe_zhao.appendTo("body");
        div_tan_chuang.appendTo("body");
        div_text.appendTo(div_tan_chuang);
        div_button.appendTo(div_tan_chuang);
        button_queren.appendTo(div_button);
        button_quxiao.appendTo(div_button);
    },
    //整个网页的等待效果
    load: function(msg){
        var width  ="400px";
        if(msg.length<=12){
            var div_text_height="60px"
        }else if(msg.length>12&&msg.length<24){
            var div_text_height="80px";
        }else{
            var div_text_height="100px";
        }
        var bodyWidth  = document.body.scrollWidth;
        var bodyHeight = document.body.scrollHeight;
        var wondowWidth  = ($(window).width()-parseInt(width))/2;
        var div_zhe_zhao  = $("<div></div>"); //遮罩层
        var div_tan_chuang = $("<div></div>"); //弹出层的信息展现
        var div_text = $("<div></div>"); //提示文子
        div_zhe_zhao.css({
            width:bodyWidth,height:bodyHeight,
            zIndex:9990,opacity: 0.7,background:"#666", filter:"alpha(opacity=50)",
            position:"absolute", top:0,left:0
        })
        div_zhe_zhao.attr({id:"zrf_zhezhao2015"});
        div_tan_chuang.css({
            width:width,height:"auto",backgroundColor:"#fff",margin:"0 auto", borderTop:"3px solid #008CFF",zIndex:9991,
            position:"fixed",top:"auto",left:wondowWidth,paddingBottom:"30px"
        })
        div_tan_chuang.attr({id:"zrf_div_tan_chuang"});
        div_text.css({
            background:"url(/newpublic/images/loading.gif)no-repeat 1px 1px",paddingLeft:"60px",
            width:"250px",height:div_text_height,lineHeight:"40px",display:"block",
            margin:"30px auto 0",fontSize:"18px",wordWrap: "break-word",
            wordBreak: "normal"
        })
        div_text.html(msg);
        div_zhe_zhao.appendTo("body");
        div_tan_chuang.appendTo("body");
        div_text.appendTo(div_tan_chuang);

        var height  =$("#zrf_div_tan_chuang").height();
        var wondowHeight = ($(window).height()-parseInt(height))/2;
        $("#zrf_div_tan_chuang").css({top:wondowHeight-50})

    },
    //异步加载等待的特效 网页部分内容使用 必须传id
    loading: function(id){
        var width  = $("#"+id).width();
        var height  =$("#"+id).height();

        var div_zhe_zhao  = $("<div></div>"); //遮罩层
        var div_loading = $("<img src='/public/images/bigload.gif'>"); //等待图片
        $("#"+id).css({position:"relative"})
        div_zhe_zhao.css({
            width:width,height:height,textAlign: "center",
            zIndex:9990,opacity: 0.3,background:"#ccc", filter:"alpha(opacity=30)",
            position:"absolute", top:0,left:0
        })
        div_loading.css({
            width:"60px",height:"60px",margin:"20px auto 0"
        })
        div_loading.appendTo(div_zhe_zhao);
        div_zhe_zhao.attr({id:"zrf_zhezhao2015"});
        $("#"+id).append(div_zhe_zhao);

    },
    is_null: function(v,msg,id){
        if(v==""){
            $("#"+id).css({display:"block"})
            $("#"+id).html(msg);
            return false;
        }else{
            $("#"+id).css({display:"none"})
            return true;
        }
    },
    is_length: function(v,msg,id,min,max){
        if(v=='')return false;
        if(v.length<min||v.length>max){
            $("#"+id).css({display:"block"})
            $("#"+id).html(msg);
            return false;
        }else{
            $("#"+id).css({display:"none"})
            return true;
        }
    },
    is_mobile: function(v,msg,id){
        if(v=='')return false;
        var preg=/^\d{11}$/;
        if(!preg.test(v)){
            $("#"+id).css({display:"block"})
            $("#"+id).html(msg);
            return false;
        }else{
            $("#"+id).css({display:"none"})
            return true;
        }
    },
    is_same: function(v1,v2,msg,id){
        if(v1==''||v2=='')return false;
        if(v1!=v2){
            $("#"+id).css({display:"block"})
            $("#"+id).html(msg);
            return false;
        }else{
            $("#"+id).css({display:"none"})
            return true;
        }
    },
    is_show: function(msg,id){
        $("#"+id).css({display:"block"})
        $("#"+id).html(msg);
    },
    test: function(){
      return 1;
    },
    close:function(){
        $("#zrf_zhezhao2015,#zrf_div_tan_chuang").remove();
//        var top=$("#zrf_div_tan_chuang").offset().top-50;
//        $("#zrf_div_tan_chuang").animate({
//            'top':top
//        }, 400,function(){
//            $("#zrf_zhezhao2015,#zrf_div_tan_chuang").remove();
//        });
    }
});
//计算中文字符的长度
function byteLength(str) {
    return str.replace(/[^\x00-\xff]/g, '__').length;
}

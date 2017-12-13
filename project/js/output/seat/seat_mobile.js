(function($){
    var seat_dl_none = 0;
    var seat_dl_none_count = 0;
    var opt={
        controller:{
            type:0,
            l:0,
            t:0,
            scale:1,
            len:0,
            sc:0,
            x:0,
            y:0,
            eid:"",
            defaultL:0,
            defaultT:0,
            moveX:0,
            moveY:0,
            cx:0,
            xy:0,
            tmpX:0,
            tmpY:0,
            tmpCX:0,
            tmpCY:0,
            autoScale:0,
            limitX:{
                min:0,
                max:0
            },
            limitY:{
                min:0,
                max:0
            },
            limitScale:1
        },
        _this:'',
        planId:                "",//场次id
        url:                 '/seats/seat.php',//异步请求的服务器地址
        size:                  37,//每个座位的宽度+座位之间的宽度
        sizeH:                  29,//每个座位的宽度+座位之间的宽
        maxCount:               4,//单次下单不能超过的座位数
        chooseCount:          0,//已选的座位数
        vipPrice:              0, //会员价
        activityPrice:        0,//活动价
        activityMaxCount:     0,//活动价限购张数
        seatChooseList:       '#seat_list',//已选座位放入的id元素
        seatChooseSumMoney:   '#seatChooseSumMoney',//总金额的id元素值
        seatTextTip:           '请在上方座位图选择您满意的座位',
        css:{
            'dl': { height: "29px", display: "block", width: '100%' },
            'dd': { marginRight: "0px", display: "block", float: "left", cursor: "pointer" },
            'seat_yes':{//background: 'url(/public/images/select_icon.png) no-repeat scroll -79px  -49px rgba(0, 0, 0, 0)'
            },
            'seat_no': {//background: 'url(/public/images/select_icon.png) no-repeat scroll -239px -49px rgba(0, 0, 0, 0)'
            },
            'seat_none':{},
            'seat_sel': { //background: 'url(/public/images/select_icon.png) no-repeat scroll -158px -49px rgba(0, 0, 0, 0)'
            },//被选中是的样式
            'seat_loverl':{//情侣座左边的三种样式
                'seat_sel':{background:'url(/public/images/seat_loverl_select.png) no-repeat center center'},
                'seat_yes':{background:'url(/public/images/seat_loverl_yes.png) no-repeat center center'},
                'seat_no':{background:'url(/public/images/seat_loverl_sell.png) no-repeat center center'}
            },
            'seat_loverr':{//情侣座右边的三种样式
                'seat_sel':{background:'url(/public/images/seat_loverr_select.png) no-repeat -1px center'},
                'seat_yes':{background:'url(/public/images/seat_loverr_yes.png) no-repeat -1px center'},
                'seat_no':{background:'url(/public/images/seat_loverr_sell.png) no-repeat -1px center'}
            }
        }
    }
    jQuery.fn.seatShow = function(option) {
        var option=option||{};
        jQuery.extend(opt,option);
        var seatShow =new SeatShow();
    }
    function SeatShow(){
        opt._this=this;
        this.ajax();
    }
    SeatShow.prototype={
        ajax:function(){

            $.ajax({
                url: opt.url,timeout:80000, data: { quote_id: opt.planId, s: new Date().getTime() }, dataType:'json',
                beforeSend: function(result){

                    var string='<div style="width:100%;text-align: center;padding-top:80px">'+
                        '<img src="/public/common/loading.gif" style="width:50px;height:50px;margin:0 auto;">'+
                        '<p>座位加载中...</p></div>';
                    $('#w13').html(string);
                },
                success: function (data) {
                    data=JSON.parse(data);
                    if(data.Rows>0){
                        data=data.seatinfo;
                        var mr = 0;//座位行
                        var mc = 0;//座位列
                        for(var i=0; i<data.length; ++i) {
                            if (data[i]['graphRow']>mr) mr = parseInt(data[i]['graphRow']);//座位行
                            if (data[i]['graphCol']>mc) mc = parseInt(data[i]['graphCol']);//座位列
                        }

                        var seatsW = $("#seats").width();
                        var tmpScale = (seatsW) / ((mc + 1) * opt.size);
                        tmpScale = tmpScale > 1 ? 1 : tmpScale;
                        opt.controller.scale = tmpScale * 1;
                        opt.controller.limitScale = tmpScale * 1;
                        opt.controller.sc = tmpScale * 1;
                        var seats = $("#w13").css({ width: (mc + 1) * opt.size + "px", "transform": "scale(" + opt.controller.scale + "," + opt.controller.scale + ")" });
                        opt.controller.l = -(((mc + 1) * opt.size) - seatsW) / 2;
                        opt.controller.t = -(opt.sizeH * mr - opt.sizeH * mr * opt.controller.scale) / 2;
                        opt.controller.limitX.min = opt.controller.l;
                        opt.controller.limitY.min = opt.controller.t;
                        opt.controller.limitX.max = opt.controller.limitX.min;
                        opt.controller.limitY.max = opt.controller.limitY.min;
                        opt.controller.defaultL = opt.controller.l;
                        opt.controller.defaultT = opt.controller.t;
                        seats.css({ "left": opt.controller.l + "px", "top": opt.controller.t + "px" });

                        seats.html("");

                        for (var r = 0; r < mr; ++r) {
                            var seat_row = $('<dl>');
                            for (var c = -1; c < mc; ++c) {
                                if (c == -1) {
                                    var isSeat = false;
                                    var seatColNumber = ''; //座位列序号
                                    for (var z = 1; z <= mc; z++) {
                                        for (var k = 0; k < data.length; k++) {
                                            if (data[k]['graphRow'] == r + 1 && data[k]['graphCol'] == z) {
                                                isSeat = true;
                                                seatColNumber = data[k]['seatRow'];
                                                seat_dl_none = 1;
                                                break;
                                            }
                                            if (isSeat){
                                                seat_dl_none = 1;
                                                break;
                                            }
                                        }
                                    }
                                    if (isSeat) {
                                        $(seat_row).append("<dd r=" + (r + 1) + " c=" + (c + 1) + " mr=" + (mr) + " mc=" + (mc) + " class='seatNumberCol' style='height:29px;line-height:29px;padding:0px;'>" + seatColNumber + "</dd>");
                                    } else {
                                        if(seat_dl_none == 0)
                                        {
                                            seat_dl_none_count++;
                                            $(seat_row).append("<dd r=" + (r + 1) + " c=" + (c + 1) + " mr=" + (mr) + " mc=" + (mc) + " class='seatNumberCol seatNumberNone'>&nbsp;</dd>");
                                        }else{
                                            $(seat_row).append("<dd r=" + (r + 1) + " c=" + (c + 1) + " mr=" + (mr) + " mc=" + (mc) + " class='seatNumberCol'>&nbsp;</dd>");
                                        }
                                    }
                                } else {
                                    $(seat_row).append(opt._this.getSeat(r + 1, c + 1, data, mr, mc));
                                }

                            }
                            seats.append(seat_row);
                        }
                        opt._this.cssStyle();
                    } else if (data['status'] == 2) {
                        var string = '<div style="width:100%;text-align: center;padding-top:80px">' +
                            '<p>' + data['error'] + '</p></div>';
                        $("#w13").html(string);
                    }
                    $("#seats").css({height:opt.sizeH*mr*opt.controller.scale+100});
                    //$("#seats").css({height:opt.sizeH*mr*opt.controller.scale-seat_dl_none_count*17});
                    //缩放控制
                    $("#w13 dd").bind("click",function(e){
                        opt.controller.eid=$(this).attr("id");
                        seat_id = $(this).attr("id");
                        var count=$("#w13 .seat_sel").length;
                        if(count==0){
                            opt.controller.autoScale=1;
                        }else if(count==1){
                            opt.controller.autoScale=2;
                        }else{
                            opt.controller.autoScale=0;
                        }
                        $this=$("#"+opt.controller.eid);
                        opt.controller.tmpX=$this.offset().left-$("#w13").offset().left+5;
                        opt.controller.tmpY=$this.offset().top-$("#w13").offset().top+5;
                        //opt.controller.tmpX=$this.offset().left;
                        //opt.controller.tmpY=$this.offset().top;
                        if(opt.controller.type==0){
                            opt._this.clickSeat($this.attr("id"),parseInt($this.attr("r")),parseInt($this.attr("c")),parseInt($this.attr("mr")),parseInt($this.attr("mc")));
                        }
                        if(opt.controller.type==0){
                            var count=$("#w13 .seat_sel").length;
                            if(opt.controller.autoScale==1&&count==1){
                                var moveX=$("#seats").width()/2-(opt.controller.moveX-$("#seats").offset().left);
                                var moveY=$("#seats").height()/2-(opt.controller.moveY-$("#seats").offset().top);
                                opt.controller.sc=1;
                                opt.controller.tmpCX=(opt.size*(mc+1)*opt.controller.scale)/2;
                                opt.controller.tmpCY=(opt.sizeH*mr*opt.controller.scale)/2;
                                opt.controller.l=parseInt(opt.controller.l)+(opt.controller.tmpCX-opt.controller.tmpX)*(opt.controller.sc/opt.controller.scale-1)+moveX;
                                opt.controller.t=parseInt(opt.controller.t)+(opt.controller.tmpCY-opt.controller.tmpY)*(opt.controller.sc/opt.controller.scale-1)+moveY;
                                opt.controller.scale=1;
                                if(opt.controller.scale>opt.controller.limitScale){
                                    opt.controller.limitX.min=(((mc+1)*opt.size*opt.controller.scale)-((mc+1)*opt.size))/2;
                                    opt.controller.limitY.min=-(opt.sizeH*mr-opt.sizeH*mr*opt.controller.scale)/2;
                                    opt.controller.limitX.max=opt.controller.limitX.min+($("#seats").width()-(mc+1)*opt.size*opt.controller.scale);
                                    opt.controller.limitY.max=opt.controller.limitY.min+($("#seats").height()-mr*opt.sizeH*opt.controller.scale);
                                }
                                if(parseInt(opt.controller.l)>opt.controller.limitX.min){
                                    opt.controller.l=opt.controller.limitX.min;
                                }
                                if(parseInt(opt.controller.l)<opt.controller.limitX.max){
                                    opt.controller.l=opt.controller.limitX.max;
                                }
                                if(parseInt(opt.controller.t)>opt.controller.limitY.min){
                                    opt.controller.t=opt.controller.limitY.min;
                                }
                                if(parseInt(opt.controller.t)<opt.controller.limitY.max){
                                    opt.controller.t=opt.controller.limitY.max;
                                }
                                $("#w13").css({"transition-duration":"0.1s"});
                                $("#w13").css({
                                    "transform":"scale("+opt.controller.scale+","+opt.controller.scale+")",
                                    "left":opt.controller.l,
                                    "top":opt.controller.t
                                });
                                setTimeout(function(){
                                    $("#w13").css({"transition-duration":"0"});
                                },100);
                            }
                            if(opt.controller.autoScale==2&&count==0){
                                opt.controller.sc=opt.controller.limitScale;
                                opt.controller.scale=opt.controller.limitScale;
                                opt.controller.l=opt.controller.defaultL;
                                opt.controller.t=opt.controller.defaultT;
                                $("#w13").css({"transition-duration":"0.1s"});
                                $("#w13").css({
                                    "transform":"scale("+opt.controller.scale+","+opt.controller.scale+")",
                                    "left":opt.controller.l,
                                    "top":parseFloat(opt.controller.t)+seat_dl_none_count*5
                                });
                                setTimeout(function(){
                                    $("#w13").css({"transition-duration":"0"});
                                },100);
                            }
                        }
                    })
                    $("#seats").on("touchstart",function(e){
                        if(e.originalEvent.touches.length==2){
                            var cx=parseInt(e.originalEvent.touches[0].clientX)-parseInt(e.originalEvent.touches[1].clientX);
                            var cy=parseInt(e.originalEvent.touches[0].clientY)-parseInt(e.originalEvent.touches[1].clientY);
                            opt.controller.len=Math.sqrt(cx*cx+cy*cy);
                            //
                            var tmpX=e.originalEvent.touches[0].pageX-$("#w13").offset().left;
                            var tmpY=e.originalEvent.touches[0].pageY-$("#w13").offset().top;
                            var tmpX1=e.originalEvent.touches[1].pageX-$("#w13").offset().left;
                            var tmpY1=e.originalEvent.touches[1].pageY-$("#w13").offset().top;
                            opt.controller.tmpX=(tmpX+tmpX1)/2;
                            opt.controller.tmpY=(tmpY+tmpY1)/2;
                            opt.controller.tmpCX=(opt.size*(mc+1)*opt.controller.scale)/2;
                            opt.controller.tmpCY=(opt.sizeH*mr*opt.controller.scale)/2;
                        }
                        if(e.originalEvent.touches.length==1){
                            opt.controller.l=$("#w13").css("left");
                            opt.controller.t=$("#w13").css("top");
                            opt.controller.x=e.originalEvent.touches[0].clientX;
                            opt.controller.y=e.originalEvent.touches[0].clientY;
                            opt.controller.moveX=e.originalEvent.touches[0].pageX;
                            opt.controller.moveY=e.originalEvent.touches[0].pageY;
                        }
                    })
                    $("#seats").on("touchmove",function(e){
                        e.originalEvent.preventDefault();
                        opt.controller.type=1;
                        if(e.originalEvent.touches.length==2){
                            var cx=parseInt(e.originalEvent.touches[0].clientX)-parseInt(e.originalEvent.touches[1].clientX);
                            var cy=parseInt(e.originalEvent.touches[0].clientY)-parseInt(e.originalEvent.touches[1].clientY);
                            opt.controller.sc=Math.sqrt(cx*cx+cy*cy)/opt.controller.len*opt.controller.scale;
                            if(opt.controller.sc>=1){
                                opt.controller.sc=1;
                            }else{
                                $("#w13").css({
                                    "left":parseInt(opt.controller.l)+(opt.controller.tmpCX-opt.controller.tmpX)*(opt.controller.sc/opt.controller.scale-1),
                                    "top":parseInt(opt.controller.t)+(opt.controller.tmpCY-opt.controller.tmpY)*(opt.controller.sc/opt.controller.scale-1)
                                });
                            }
                            $("#w13").css({"transform":"scale("+opt.controller.sc+","+opt.controller.sc+")"});
                        }
                        if(e.originalEvent.touches.length==1){
                            $("#w13").css({left:(parseInt(opt.controller.l)+e.originalEvent.touches[0].clientX-opt.controller.x),top:(parseInt(opt.controller.t)+e.originalEvent.touches[0].clientY-opt.controller.y)});
                        }
                    })
                    $("#seats").bind("touchend",function(e){
                        if(e.originalEvent.touches.length==1){
                            opt.controller.x=e.originalEvent.touches[0].clientX;
                            opt.controller.y=e.originalEvent.touches[0].clientY;
                        }
                        if(opt.controller.type!=0){
                            opt.controller.l=$("#w13").css("left");
                            opt.controller.t=$("#w13").css("top");
                            opt.controller.scale=opt.controller.sc;
                        }
                        if(e.originalEvent.touches.length==0&&opt.controller.type!=0){
                            if(opt.controller.scale<=opt.controller.limitScale){
                                opt.controller.scale=opt.controller.limitScale;
                                opt.controller.l=opt.controller.defaultL;
                                opt.controller.t=opt.controller.defaultT;
                            }
                            if(opt.controller.scale>opt.controller.limitScale){
                                opt.controller.limitX.min=(((mc+1)*opt.size*opt.controller.scale)-((mc+1)*opt.size))/2;
                                opt.controller.limitY.min=-(opt.sizeH*mr-opt.sizeH*mr*opt.controller.scale)/2;
                                opt.controller.limitX.max=opt.controller.limitX.min+($("#seats").width()-(mc+1)*opt.size*opt.controller.scale);
                                opt.controller.limitY.max=opt.controller.limitY.min+($("#seats").height()-mr*opt.sizeH*opt.controller.scale);
                            }
                            if(parseInt(opt.controller.l)>opt.controller.limitX.min){
                                opt.controller.l=opt.controller.limitX.min;
                            }
                            if(parseInt(opt.controller.l)<opt.controller.limitX.max){
                                opt.controller.l=opt.controller.limitX.max;
                            }
                            if(parseInt(opt.controller.t)>opt.controller.limitY.min){
                                opt.controller.t=opt.controller.limitY.min;
                            }
                            if(parseInt(opt.controller.t)<opt.controller.limitY.max){
                                opt.controller.t=opt.controller.limitY.max;
                            }
                            $("#w13").css({"transition-duration":"0.1s"});
                            $("#w13").css({
                                "transform":"scale("+opt.controller.scale+","+opt.controller.scale+")",
                                "left":opt.controller.l,
                                "top":parseFloat(opt.controller.t)+seat_dl_none_count*5
                            });
                            setTimeout(function(){
                                $("#w13").css({"transition-duration":"0"});
                            },100);
                        }
                        if(e.originalEvent.touches.length==0){
                            opt.controller.type=0;
                        }
                    })
                    //  $("#w13 dd").bind("touchstart",function(e){
                    //     e.originalEvent.preventDefault();
                    // })
                    // $("#w13 dd").bind("touchend",function(e){
                    //     if(opt.controller.type==0){
                    //         $(this).css({"background-color":"red"});
                    //     }
                    // })
                },

                error: function(msg) {
                    console.log(msg);
                }
            });
        },
        getSeat:function(row, col, data,rowSum,colSum){
            var id = 's_' + row + '_' + col;
            var seat = $('<dd>').attr('id', id);
            var status = 'none';
            for (var i = 0; i < data.length; ++i) {
                var d = data[i];
                if (d['graphRow'] == row && d['graphCol'] == col) {
                    $(seat).attr('seatno', d['SeatNo'])
                        .attr('info', d['seatRow'] + '排' + d['seatCol']+'座')
                        .attr('seatinfo', '0'+ '_' + d['seatRow'] + '_' + d['seatCol'])
                        .attr('title', d['seatRow'] + '排' + d['seatCol']+'座')
                        .attr('r', row)
                        .attr('c', col)
                        .attr('mr', rowSum)
                        .attr('mc', colSum);
                    if (d['seatState'] <=0) {
                        status = 'yes';
                        if (d['seatType'] == 1) {
                            status += ' seat_loverl';
                        } else if (d['seatType'] == 2){
                            status += ' seat_loverr';
                        }
                        $(seat).append('<div class="seat-demo yes"><div class="seat-up"></div><div class="seat-down"></div></div>')
                    } else {
                        status = 'no';
                        if (d['seatType'] == 1) {
                            status += ' seat_loverl';
                        } else if (d['seatType'] == 2){
                            status += ' seat_loverr';
                        }
                        $(seat).append('<div class="seat-demo no"><div class="seat-up"></div><div class="seat-down"></div></div>')
                    }
                    break;
                }
            }
            $(seat).addClass('seat_' + status);
            $(seat).addClass('inline_block');
            $(seat).addClass('inline_4_ie7');
            //$(seat).click(function(){ opt._this.clickSeat(id,row,col,rowSum,colSum);});
            return seat;
        },
        cssStyle:function(){
            $("#seats dl").css(opt.css.dl);
            $("#seats dd").css(opt.css.dd);
            $("#seats .seat_yes").css(opt.css.seat_yes);
            $("#seats .seat_no").css(opt.css.seat_no);
            $("#seats .seat_none").css(opt.css.seat_none);
            $($(".seatNumberNone").closest("#seats dl")).css("display","none");
            $("#w13").css("top",((seat_dl_none_count*5)+parseFloat($("#w13").css("top").substr(0,$("#w13").css("top").length-2)))+"px");
            for(var i=0;i<$("#seats dd").length;i++){
                opt._this.changeSeatCss($("#seats dd").eq(i));
            }
        },
        clickSeat:function(seat,r,c,rnum,cnum) {
            getLoginStatus();
            var id = '#'+seat;
            if($(id).hasClass('seat_none') || $(id).hasClass('seat_no'))
                return false;
            var lover_id="";
            var loveflag = 0;
            if($(id).hasClass("seat_loverl")){
                var lover_id='#s_'+r+"_"+(c+1);
                loveflag=1;
            }else if($(id).hasClass("seat_loverr")){
                var lover_id='#s_'+r+"_"+(c-1);
                loveflag=1;
            }
            if($(id).hasClass('seat_yes')) {//可以选择
                if(lover_id!=""){
                    $(lover_id).removeClass('seat_yes').addClass('seat_sel');
                }
                $(id).removeClass('seat_yes').addClass('seat_sel');
                if(loveflag==1  && (opt.chooseCount+2)>opt.maxCount) {
                    Boxy.tip('一次最多选取'+opt.maxCount+'个座位,情侣座不支持单选',1000)

                    $(id).removeClass('seat_sel').addClass('seat_yes');
                    if(lover_id!=""){
                        $(lover_id).removeClass('seat_sel').addClass('seat_yes');
                    }
                    return false;
                }
                if(loveflag==0 && (opt.chooseCount+1)>opt.maxCount) {
                    Boxy.tip('一次最多选取'+opt.maxCount+'个座位',1000)
                    $(id).removeClass('seat_sel').addClass('seat_yes');
                    if(lover_id!=""){
                        $(lover_id).removeClass('seat_sel').addClass('seat_yes');
                    }
                    return false;
                }
                //if(opt._this.hasEmptySeat(rnum,cnum)){
                //    Boxy.tip('亲，不要留下单个座位',1000)
                //    //Boxy.falseMsg('亲，不要留下单个座位');
                //    //Boxy.alert("亲，不要留下单个座位");
                //    $(id).removeClass('seat_sel').addClass('seat_yes');
                //    if(lover_id!=""){
                //        $(lover_id).removeClass('seat_sel').addClass('seat_yes');
                //    }
                //    return false;
                //}
                $("#seats .seat_sel").css(opt.css.seat_sel);
                if(lover_id!=""){
                    opt._this.addSeat(lover_id);
                    opt._this.changeSeatCss(lover_id);
                }
                opt._this.addSeat(id);
                opt._this.changeSeatCss(id);
            } else if($(id).hasClass('seat_sel')) {//本次已经备选，可以取消选择
                $(id).removeClass('seat_sel').addClass('seat_yes');
                if(lover_id!=""){
                    $(lover_id).removeClass('seat_sel').addClass('seat_yes');
                }
                //if(opt._this.hasEmptySeat(rnum,cnum)){
                //    //Boxy.falseMsg('亲，不要留下单个座位');
                //    Boxy.tip('亲，不要留下单个座位',1000)
                //    //Boxy.alert("亲，不要留下单个座位");
                //    $(id).removeClass('seat_yes').addClass('seat_sel');
                //    if(lover_id!=""){
                //        $(lover_id).removeClass('seat_yes').addClass('seat_sel');
                //    }
                //    return false;
                //}
                $("#seats .seat_yes").css(opt.css.seat_yes);
                if(lover_id!=""){
                    opt._this.removeSeat(lover_id);
                    opt._this.changeSeatCss(lover_id);
                }
                opt._this.removeSeat(id);
                opt._this.changeSeatCss(id);
            }
        },
        addSeat:function (id) {//添加座位数
            if($(opt.seatChooseList).html()==opt.seatTextTip){
                $(opt.seatChooseList).html("");
                $(opt.seatChooseList).append(
                    // $('<div>').attr('info', id.substr(1)).html($('#seats '+id).attr('info')).addClass('inline_block').addClass('inline_4_ie7')
                    //$('<div>').attr('info', id.substr(1)).html($('#seats '+id).attr('info'))
                    opt._this.createSeat(id)
                );
            }else{
                //$(opt.seatChooseList).append($('<div>').attr('info', id.substr(1)).html($('#seats '+id).attr('info')));
                $(opt.seatChooseList).append(opt._this.createSeat(id));
            }
            opt.chooseCount++;
            opt._this.money();
        },
        createSeat:function(id){//创建座位的div
            var info=id.substr(1);
            var name=$('#seats '+id).attr('info')
            var string="<div info='"+info+"' class='seats_opt' onclick='cancelSelectSeat(this);'><span><a class='opt_close'></a>"+name+"&nbsp;<small>×</small></span></div>";
            return string;
        },
        removeSeat:function(id){//取消选中的座位
            id = id.replace('#','');
            id = '[info='+id+']';
            $('#seat_list '+id).remove();
            // if($('#seat_list').html()==''){
            //     $('#seat_list').html(opt.seatTextTip);
            // }
            opt.chooseCount--;
            opt._this.money();
        },
        changeSeatCss:function (seat){//选中和取消时切换背景样式
            if($(seat).hasClass('seat_loverl')){
                if($(seat).hasClass('seat_sel')){
                    $(seat).css({background:'url(/public/images/seat_loverl_select.png) no-repeat 1px center'});
                }else if($(seat).hasClass('seat_yes')){
                    $(seat).css({background:'url(/public/images/seat_loverl_yes.png) no-repeat 1px center'});
                }else if($(seat).hasClass('seat_no')){
                    $(seat).css({background:'url(/public/images/seat_loverl_sell.png) no-repeat 1px center'});
                }
            }else if($(seat).hasClass('seat_loverr')){
                if($(seat).hasClass('seat_sel')){
                    $(seat).css({background:'url(/public/images/seat_loverr_select.png) no-repeat -1px center'});
                }else if($(seat).hasClass('seat_yes')){
                    $(seat).css({background:'url(/public/images/seat_loverr_yes.png) no-repeat -1px center'});
                }else if($(seat).hasClass('seat_no')){
                    $(seat).css({background:'url(/public/images/seat_loverr_sell.png) no-repeat -1px center'});
                }
            }
        },
        money:function(){//计算总价
            var total=0;
            if(opt.activityPrice>0){//存在活动价
                if(opt.chooseCount<=opt.activityMaxCount){
                    total=opt._this.jsCheng(opt.activityPrice,opt.activityMaxCount);
                }else{
                    var m1=opt._this.jsCheng(opt.activityPrice,opt.activityMaxCount);
                    var m2=opt._this.jsCheng(opt.vipPrice,(opt.chooseCount-opt.activityMaxCount));
                    total=opt._this.jsAdd(m1,m2);
                }

            }else{//会员价
                total=opt._this.jsCheng(opt.vipPrice,opt.chooseCount);
            }
            $(opt.seatChooseSumMoney).html(parseFloat(total).toFixed(2));
        },
        getSeatStatus:function (row,col,rowNum,columnNum){//获取座位现在的状态
            if (col < 1 || row < 1 || col > columnNum || row > rowNum) {
                return -1;
            } else {
                var id = '#s_'+row+'_'+col;
                if($(id).hasClass("seat_none")){
                    return -1;
                }else if($(id).hasClass("seat_yes")){
                    return 0;
                }else if($(id).hasClass("seat_no")){
                    return 1;
                }else{
                    return 2;
                }
            }
        },
        hasEmptySeat: function(rowNum,columnNum){ //判断座位是否可以选择，比如选择以后是否留下空座
            for (var row = 1; row <= rowNum; row++ ) {
                for (var col = 1; col <= columnNum; col++ ) {
                    var id = '#s_'+row+'_'+col;
                    if ($(id).hasClass('seat_sel')) {
                        var originL = col;
                        var originR = col;
                        for (col; col <= columnNum; col++ ) {
                            var idd = '#s_'+row+'_'+col;
                            if ($(idd).hasClass('seat_sel')) {
                                originR = col;
                            } else break;
                        }
                        /*
                         同一排的座位
                         1 左或右挨着已选座位或者边界，ok ！
                         左或右不可能挨着自选
                         左或右加1如果挨着自选，则中间隔的已选或者没座
                         2 左右挨着空座，左右隔一个不挨着自选，已选，边界
                         */
                        var l1State = opt._this.getSeatStatus(row,originL-1,rowNum,columnNum);
                        var l2State = opt._this.getSeatStatus(row,originL-2,rowNum,columnNum);
                        var r1State = opt._this.getSeatStatus(row,originR+1,rowNum,columnNum);
                        var r2State = opt._this.getSeatStatus(row,originR+2,rowNum,columnNum);
                        var SeatViewStateNone = -1;
                        var SeatViewStateNormal = 0;//正常
                        var SeatViewStateUnavailable = 1;//售出
                        var SeatViewStateSelected = 2;//选中
                        if (l1State == SeatViewStateUnavailable || l1State == SeatViewStateNone
                            || r1State == SeatViewStateUnavailable || r1State == SeatViewStateNone) {
                            if (l2State == SeatViewStateSelected
                                && l1State != SeatViewStateNone
                                && l1State != SeatViewStateUnavailable ) {
                                return true;
                            }
                            if (r2State == SeatViewStateSelected
                                && r1State != SeatViewStateNone
                                && r1State != SeatViewStateUnavailable ) {
                                return true;
                            }
                        } else {
                            if (l2State != SeatViewStateNormal || r2State != SeatViewStateNormal) {
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
        },
        jsCheng:function(arg1,arg2){//乘法
            var m=0,s1=arg1.toString(),s2=arg2.toString();
            try{m+=s1.split(".")[1].length}catch(e){}
            try{m+=s2.split(".")[1].length}catch(e){}
            return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)
        },
        jsAdd:function(arg1,arg2){//加法
            var r1,r2,m;
            try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
            try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
            m=Math.pow(10,Math.max(r1,r2))
            return ((arg1*m+arg2*m)/m).toFixed(2);
        }
    }
})(jQuery)
//var loginStatus = 1;//--------------------------------------!!!
//判断登陆
//function loginStatusFn(){
//    if(!getLoginStatus()){
//        return false;
//    }
//    return true;
//}

//太和登录
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
    //alert(json);
    var json = JSON.parse(json);
    //alert(json.STATUS);
    if(json.STATUS==1){
        var getData = {'cipher':json.MSG};
        $.ajax({
            type: 'post', // 提交方式 get/post
            url: "index.php?r=output/Site/GetLoginInfo", // 需要提交的 url
            data: getData,
            dataType : "json",
            //beforeSend: function() {
            //    $(".waiting").addClass("active");
            //},
            success: function (data) {
                if(data.ok){
                    loginStatus=1;
                    $(".waiting").removeClass("active");
                    $('#mobile').val(data.sPhone);
                }else{
                    $(".waiting").removeClass("active");
                    alert(data.resultDesc);
                }
            },
            //complete: function () {
            //    $(".waiting").addClass("noactive");
            //},
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
    //alert(isLogin);
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


//取消订单
function cancelSeatOrder(outerOrderId) {
    Boxy.button(
        '取消订单将会释放已经锁定的座位，您确定要取消订单吗？',['确定','取消'],
        function(){
            $.ajax({
                url: '/ajax/order.php',
                data: { cmd:1,subcmd:12,outerOrderId: outerOrderId },
                dataType:'json',
                beforeSend: function() {Boxy.loading('取消订单处理中，请稍后...');},
                success: function(data) {
                    $(".boxy-wrapper,.boxy-modal-blackout").remove();
                    if(data['status']==0){
                        location.reload();
                    }else{
                        Boxy.tip(data['error'],2000);
                    }
                }
            });
        },function(){
            $(".boxy-wrapper,.boxy-modal-blackout").remove();
            location='/pay/seatpay.php?outerOrderId='+outerOrderId;
        }
    );
}

//检测是否有重复的订单
$(function(){
    if(!loginStatus) {
        return false;
    }
    var data = {
        cmd:1,
        subcmd:16,
        planId: planId
    };
    $.ajax({
        url: '/ajax/order.php',
        data: data,
        dataType:'json',
        success: function(data) {
            if(data['status']==2){
                var outerOrderId=data['order'];
                Boxy.button("您有一个本场次订单还未完成支付，请选择以下操作：",['取消重选','继续支付'],function(){
                    cancelSeatOrder(outerOrderId);
                },function(){
                    location='/pay/seatpay.php?outerOrderId='+outerOrderId;
                });
            }
        },
        error: function() { }
    });
});
function imgInit(arr){
    var width=window.innerWidth;
    if(width>=750){
        width=750;
    }
    var content=document.getElementById("content");
    var wid=width*280/750;
    sum=0;
    var num=arr.length;
    // alert(num);
    var flag=document.getElementById("flag");
    for(var i=0;i<num;i++){
        var newli=document.createElement("li");
        newli.setAttribute("class","content-tab");
        content.style.width=parseInt(content.style.width)+wid+"px";
        sum++;
        content.appendChild(newli);
        var newimg=document.createElement("img");
        newimg.setAttribute("src",arr[i]);
        newli.appendChild(newimg);
        var newflagli=document.createElement("li");
        flag.appendChild(newflagli);
    }
    flag.getElementsByTagName("li")[0].setAttribute("class","active");

    // alert(parseInt(content.style.width));
}
function btnFail(str){
    var btn1=document.getElementById("btn1");
    var direc=document.getElementById("direc");
    btn1.style.backgroundColor="#ccc";
    btn1.style.border="0.01rem solid #ccc";
    btn1.setAttribute("disabled","true");
    if(str!=null){
        direc.innerHTML=str;
        direc.style.display="block";
    }
}
function initBox(str){
    if(str!=null){
        var fail=document.getElementById("fail");
        var failP=fail.getElementsByTagName("p")[0];
        failP.innerHTML=str;
        
        hide.style.display="block";
        ask.style.display="none";
        fail.style.display="block";
        hidebtnn.onclick=function(){
            fail.style.display="none";
            hide.style.display="none";
        }
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
    var height=document.body.scrollHeight;
    var btn=document.getElementById("btn");
    var hide=document.getElementById("hide");
    hide.style.width=width+"px";
    hide.style.height=height+"px";
    

    //轮播图
    var nStartX=0,startLeft=0;
    var container=document.getElementById("container");
    var content=document.getElementById("content");
    var _l=0;
    var wid=parseInt(width*470/750);
    var startLeft=0,endLeft=0;
    content.style.width=wid*sum+"px";
    content.style.left="0px";
    
    var index=0,oldindex=0;
    var flag=document.getElementById("flag");
    var liArr=flag.getElementsByTagName("li");

    /*图片缩放*/
    var _w=container.clientWidth;
    var _h=container.clientHeight;
    var imgArr=container.getElementsByTagName("img");
    for(var i=0;i<imgArr.length;i++){
        var w=imgArr[i].clientWidth;
        var h=imgArr[i].clientHeight;
        if((w/h)>(_w/_h)){
            imgArr[i].style.width=_w+"px";
            imgArr[i].style.left="0px";
            imgArr[i].style.top=(_h-imgArr[i].clientHeight)/2+"px";
        }else{
            imgArr[i].style.height=_h+"px";
            imgArr[i].style.top="0px";
            imgArr[i].style.left=(_w-imgArr[i].clientWidth)/2+"px";
        }
    }


    function slide(dir,val,page){
        if(page!=null){
            if(dir==0){
                for(var i=0;i<val-1;i++){
                    (function(){
                        setTimeout(function(){
                            content.style.left=parseInt(content.style.left)-1+"px";
                        },2*i);
                    })(i);
                }
            }else{
                for(var i=0;i<val-1;i++){
                    (function(){
                        setTimeout(function(){
                            content.style.left=parseInt(content.style.left)+1+"px";
                        },2*i);
                    })(i);
                }
            }
        }else{
            if(dir==0){
                for(var i=0;i<val;i++){
                    (function(){
                        setTimeout(function(){
                            content.style.left=parseInt(content.style.left)-1+"px";
                        },2*i);
                    })(i);
                }
            }else{
                for(var i=0;i<val;i++){
                    (function(){
                        setTimeout(function(){
                            content.style.left=parseInt(content.style.left)+1+"px";
                        },2*i);
                    })(i);
                }
            }
        }
    }
    container.addEventListener('touchstart',function(){
        event.preventDefault();
        nStartX = event.targetTouches[0].pageX;
        startLeft=parseInt(content.style.left);
    });
    container.addEventListener('touchend',function(){
        event.preventDefault();
        endLeft=parseInt(content.style.left);
        var slideVal=0;
        if(startLeft>endLeft){
            // 向左了;
            slideVal=startLeft-endLeft;
            if(slideVal>(wid/2)&&Math.abs(startLeft)!=(sum-1)*(wid)){
                if(slideVal>wid){
                    //继续向右
                    index++;
                    slide(1,slideVal-wid);
                }else{
                    //继续向左
                    index++;
                    slide(0,wid-slideVal);
                }
            }else{
                //继续向右
                slide(1,slideVal);
            }
        }else{
            slideVal=endLeft-startLeft;
            //向右了
            if(slideVal>(wid/2)&&startLeft!=0){
                if(slideVal>wid){
                    //继续向左
                    index--;
                    slide(0,slideVal-wid);
                }else{
                    //继续向右
                    index--;
                    slide(1,wid-slideVal);
                }
            }else{
                //继续向左
                slide(0,slideVal);
            }
        }
        if(index<0){
            index=0;
        }else if(index>=liArr.length){
            index=liArr.length-1;
        }
        liArr[oldindex].removeAttribute("class");
        liArr[index].setAttribute("class","active");
        oldindex=index;
    });
    container.addEventListener('touchmove',function(){
        event.preventDefault();
        var touch=event.targetTouches[0];
        _l=parseInt(content.style.left) + touch.pageX - nStartX;
        nStartX = touch.pageX;
        content.style.left=_l+"px";
        // console.log(_l);
    });
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
    var height=document.body.scrollHeight;
    var btn=document.getElementById("btn");
    var hide=document.getElementById("hide");
    hide.style.width=width+"px";
    hide.style.height=height+"px";

    /*图片缩放*/
    var _w=container.clientWidth;
    var _h=container.clientHeight;
    var imgArr=container.getElementsByTagName("img");
    for(var i=0;i<imgArr.length;i++){
        var w=imgArr[i].clientWidth;
        var h=imgArr[i].clientHeight;
        if((w/h)>(_w/_h)){
            imgArr[i].style.width=_w+"px";
            imgArr[i].style.left="0px";
            imgArr[i].style.top=(_h-imgArr[i].clientHeight)/2+"px";
        }else{
            imgArr[i].style.height=_h+"px";
            imgArr[i].style.top="0px";
            imgArr[i].style.left=(_w-imgArr[i].clientWidth)/2+"px";
        }
    }

}
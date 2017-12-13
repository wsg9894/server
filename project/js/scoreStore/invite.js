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
    var hide=document.getElementById("hidee");
    hide.style.width=width+"px";
    hide.style.height=height+"px";
    btn.onclick=function(){
        hide.style.display="block";
    }
    hide.onclick=function(){
        this.style.display="none";
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
    var height=document.body.scrollHeight;
    var btn=document.getElementById("btn");
    var hide=document.getElementById("hidee");
    hide.style.width=width+"px";
    hide.style.height=height+"px";
}
var width=window.innerWidth;
var height=window.innerHeight;
if(width>=750){
	width=750;
}
var html=document.getElementsByTagName("html")[0];
html.style.fontSize=width/3.2+"px";
var body=document.getElementsByTagName("body")[0];
html.style.width=width+"px";
// html.style.height=height+"px";
window.onresize=function(){
    var width=window.innerWidth;
    if(width>=750){
    	width=750;
    }
    var html=document.getElementsByTagName("html")[0];
    html.style.fontSize=width/3.2+"px";
    var body=document.getElementsByTagName("body")[0];
    html.style.width=width+"px";
    // html.style.height=height+"px";
}
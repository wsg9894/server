var width=window.innerWidth;
var height=window.innerHeight;
if(width>=640){
	width=640;
}
var html=document.getElementsByTagName("html")[0];
html.style.fontSize=width/3.2*1.171875+"px";
var body=document.getElementsByTagName("body")[0];
html.style.width=width+"px";
html.style.minHeight=height+"px";
window.onresize=function(){
    var width=window.innerWidth;
    if(width>=640){
    	width=640;
    }
    var html=document.getElementsByTagName("html")[0];
    html.style.fontSize=width/3.2*1.171875+"px";
    var body=document.getElementsByTagName("body")[0];
    html.style.width=width+"px";
    html.style.minHeight=height+"px";
}
// JavaScript Document
window.onload=htmlSize;
window.onresize =htmlSize;
function htmlSize(){
	var cw=document.body.clientWidth;
	cw=cw/16;
	//计算倍数，数值可变。
	if(cw<20){cw=20} //最小宽度
	if(cw>30){cw=30} //最大宽度
	document.getElementById('html').style.fontSize=cw+'px';
	}

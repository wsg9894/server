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

    
    //选项卡
    var tabsArr=document.getElementsByName("tab");
    var tabsContentArr=document.getElementsByName("tabsContent");
    tabsArr[0].style.color="#5dc280";
    tabsContentArr[0].style.display="block";
    for(var i=0;i<tabsArr.length;i++){
        tabsArr[i].i=i;
        tabsArr[i].onclick=function(){
            for(var j=0;j<tabsArr.length;j++){
                tabsArr[j].style.color="#3c3c3c";
                tabsContentArr[j].style.display="none";
            }
            tabsArr[this.i].style.color="#5dc280";
            tabsContentArr[this.i].style.display="block";

        }
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
}
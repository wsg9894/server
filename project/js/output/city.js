// JavaScript Document
var arhtmlcontent = new Array();
var arCityList = new Array();
$(function(){
    htmlSize();
	getMovieInfo();
});
window.onresize =htmlSize;
function htmlSize(){
    var cw=document.body.clientWidth;
    cw=cw/16;
    //计算倍数，数值可变。
    if(cw<20){cw=20} //最小宽度
    if(cw>30){cw=30} //最大宽度
    document.getElementById('html').style.fontSize=cw+'px';
}

function getMovieInfo()
{

	$.ajax({
		type:'get',
		url:'index.php?r=output/Ajax/GetCityList',
		dataType:'json',
		success:function(msg){
			if (msg.ok)
			{
				showCityList(msg.data);
			}

		},
		error:function(msg){
			return false;
		}
	});

	return false;
}

function showCityList(citylist)
{
	for (var i = 65; i<91;i++)
	{
		var py = String.fromCharCode(i);
		arhtmlcontent[py] = '<div class="fz28 co333 pdd12 bgeee d-block hidden hei165 mart12" onclick="showPyCity(\''+py+'\')" id=article'+py+'><span class="fl-l">'+py+'</span><span class="fl-r"><img src="/viewlibs/default/templates/cinema/images/1212down.png" style="width:1.65rem;"/></span></div><div class="bgffffff hidden pdd16 article'+py+'" style="display: none;">';
	}
	//console.log(arhtmlcontent);return ;

	arCityList.length =0;
	$(citylist).each(function(idx,item)
	{
		var py = item['sCityPY'].toUpperCase();
		arhtmlcontent[py] +=  '<div class="fz28 co333 d-block hidden hei210 bor01 curs" style="" onclick ="selectCity('+item['iCityID']+',\''+item['sCityName']+'\')"><span class="fl-l">'+item['sCityName']+'</span></div>';
		arCityList.push(item);
	});

	for (var i = 65; i<91;i++)
	{
		var py = String.fromCharCode(i);
		arhtmlcontent[py] += '</div>';
	}

	autoComplete.value_arr = arCityList;
	showAllCity();
}


function showAllCity()
{
	var content = '';
	for (var py in arhtmlcontent)
	{
		content += arhtmlcontent[py];
	}
	document.getElementById('city').innerHTML = content;
    var cityId = localStorage.getItem("cityId");
	console.log(cityId);
    var data = {};
	var getUrl = '';
    if (cityId == null)
    {
		cityId=1;
		localStorage.setItem('cityId',cityId);
    }
    $.ajax({
        url: 'index.php?r=output/Ajax/GetCityInfo',
        data: {'cityId':cityId},
        type: "post",
        success: function (backdata) {
            var json_str = JSON.parse(backdata);
            if(json_str.ok){
                document.getElementById('aqiehuan').innerHTML = '<span id="positioncity">'+json_str.data["sCityName"]+'</span>';
            }
        },error: function (error) {
            document.getElementById('aqiehuan').innerHTML = '无定位城市';
            console.log(error);
        }
    });

}

function showPyCity(py)
{
	var imgsrc = $("#article"+py+ ' .fl-r img').attr("src");

	if(imgsrc.indexOf('1212top.png') != -1){
		$("#article"+py+ ' .fl-r img').attr("src",imgsrc.replace('1212top.png','1212down.png'));
		$(".article"+py).hide();
	}else{
		$("#article"+py+ ' .fl-r img').attr("src",imgsrc.replace('1212down.png','1212top.png'));
		$(".article"+py).show();
	}
}

function selectCityByName(cityName)
{
	var cityName =  document.getElementById('cityCode').value;

	for (var i=0;i<arCityList.length;i++)
	{
		if (arCityList[i].sCityName == cityName)
		{
			selectCity(arCityList[i].iCityID,arCityList[i].sCityName);
			break;
		}
	}
}

function selectCity(cityId,cityName)
{
	localStorage.setItem("cityId",cityId);
	localStorage.setItem("cityName",cityName);

	//var url = document.referrer;
	//window.location.href = url;
	var url = getUrlVal();
	window.location.href=url;

}
function gbcitypage(){
	var url = getUrlVal();
	window.location.href=url;
}
function qiehuancity(){
	$.ajax({
		type:'get',
		url:'/ajax/city.php',
		data: {cmd:1, subcmd:'5'},
		dataType:'json',
		success:function(msg){
			console.log('cityId='+msg.data['iCityID']);return;
			localStorage.setItem("cityId",msg.data['iCityID']);
			localStorage.setItem("city",msg.data['sCityName']);
			localStorage.setItem("cityName",msg.data['sCityName']);
			localStorage.setItem("province",msg.data['priviceID']);
		},
		error:function(){
			localStorage.setItem("cityId","1");
		}
	});
	//pdwztz();
}
function pdwztz(cityId){
	var url = getUrlVal();
	window.location.href=url;
}
function getUrlVal(){
	var url=window.location.search;
	var backUrl=url.split('&')[1];
	var backUrl=backUrl.replace("goback=","");
	return backUrl;
}

function showPYList()
{
	var content = '';
	for (var i = 65; i<91;i++)
	{
		var py = String.fromCharCode(i);
		content += '<a href="#" onclick="showCity(\''+py+'\')">'+py+'</a>';
	}
	//document.getElementById('pylist').innerHTML = content;
}


function showCity( py)
{
	if (typeof(arhtmlcontent[py])==undefined)
	{
		showAllCity();
		return;
	}
	document.getElementById('city').innerHTML = arhtmlcontent[py];
}
var tp = function (id) {
	return "string" == typeof id ? document.getElementById(id) : id;
}
var Bind = function(object, fun) {
	return function() {
		return fun.apply(object, arguments);
	}
}
function AutoComplete(obj,autoObj,arr){
	this.obj=tp(obj);        //输入框
	this.autoObj=tp(autoObj);//DIV的根节点
	this.value_arr=arr;        //不要包含重复值
	this.index=-1;          //当前选中的DIV的索引
	this.search_value="";   //保存当前搜索的字符
}
AutoComplete.prototype={
	//初始化DIV的位置
	init: function(){
		this.autoObj.style.left = this.obj.offsetLeft + "px";
		this.autoObj.style.top  = this.obj.offsetTop + this.obj.offsetHeight + "px";
		this.autoObj.style.width= this.obj.offsetWidth - 2 + "px";//减去边框的长度2px
	},
	//删除自动完成需要的所有DIV
	deleteDIV: function(){
		while(this.autoObj.hasChildNodes()){
			this.autoObj.removeChild(this.autoObj.firstChild);
		}
		this.autoObj.className="auto_hidden";
	},
	//设置值
	setValue: function(_this){
		return function(){
			_this.obj.value=this.seq;
			_this.autoObj.className="auto_hidden";
			selectCityByName();
		}
	},
	//模拟鼠标移动至DIV时，DIV高亮
	autoOnmouseover: function(_this,_div_index){
		return function(){
			_this.index=_div_index;
			var length = _this.autoObj.children.length;
			for(var j=0;j<length;j++){
				if(j!=_this.index ){
					_this.autoObj.childNodes[j].className='auto_onmouseout';
				}else{
					_this.autoObj.childNodes[j].className='auto_onmouseover';
				}
			}
		}
	},
	//更改classname
	changeClassname: function(length){
		for(var i=0;i<length;i++){
			if(i!=this.index ){
				this.autoObj.childNodes[i].className='auto_onmouseout';
			}else{
				this.autoObj.childNodes[i].className='auto_onmouseover';
				this.obj.value=this.autoObj.childNodes[i].seq;
			}
		}
	}
	,
	//响应键盘
	pressKey: function(event){
		var length = this.autoObj.children.length;
		//光标键"↓"
		if(event.keyCode==40){
			++this.index;
			if(this.index>length){
				this.index=0;
			}else if(this.index==length){
				this.obj.value=this.search_value;
			}
			this.changeClassname(length);
		}
		//光标键"↑"
		else if(event.keyCode==38){
			this.index--;
			if(this.index<-1){
				this.index=length - 1;
			}else if(this.index==-1){
				this.obj.value=this.search_value;
			}
			this.changeClassname(length);
		}
		//回车键
		else if(event.keyCode==13){
			this.autoObj.className="auto_hidden";
			this.index=-1;
			selectCityByName();
		}else{
			this.index=-1;
		}
	},
	//程序入口
	start: function(event){
		if(event.keyCode!=13&&event.keyCode!=38&&event.keyCode!=40){
			this.init();
			this.deleteDIV();
			this.search_value=this.obj.value;
			var valueArr=this.value_arr;
			if(this.obj.value.replace(/(^\s*)|(\s*$)/g,'')==""){ return; }//值为空，退出
			try{ var reg = new RegExp("(" + this.obj.value + ")","i");}
			catch (e){ return; }
			var div_index=0;//记录创建的DIV的索引
			for(var i=0;i<valueArr.length;i++){
				if(reg.test(valueArr[i].sCityName)){
					var div = document.createElement("div");
					div.className="auto_onmouseout";
					div.seq=valueArr[i].sCityName;
					div.onclick=this.setValue(this);
					div.onmouseover=this.autoOnmouseover(this,div_index);
					div.innerHTML=valueArr[i].sCityName.replace(reg,"<strong>$1</strong>");//搜索到的字符粗体显示
					this.autoObj.appendChild(div);
					this.autoObj.className="auto_show";
					div_index++;
				}
			}
		}
		this.pressKey(event);
		window.onresize=Bind(this,function(){this.init();});
	}
}
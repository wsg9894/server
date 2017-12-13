var GetLength = function (str) {
	///<summary>����ַ���ʵ�ʳ��ȣ�����2��Ӣ��1</summary>
	///<param name="str">Ҫ��ó��ȵ��ַ���</param>
	var realLength = 0, len = str.length, charCode = -1;
	for (var i = 0; i < len; i++) {
		charCode = str.charCodeAt(i);
		if (charCode >= 0 && charCode <= 128) realLength += 1;
		else realLength += 2;
	}
	return realLength;
};

/**
 * caculate the great circle distance
 * @param {Object} lat1
 * @param {Object} lng1
 * @param {Object} lat2
 * @param {Object} lng2
 */
var EARTH_RADIUS = 6378137.0;    //��λM
var PI = Math.PI;
function getGreatCircleDistance(lat1,lng1,lat2,lng2){
	var radLat1 = getRad(lat1);
	var radLat2 = getRad(lat2);

	var a = radLat1 - radLat2;
	var b = getRad(lng1) - getRad(lng2);

	var s = 2*Math.asin(Math.sqrt(Math.pow(Math.sin(a/2),2) + Math.cos(radLat1)*Math.cos(radLat2)*Math.pow(Math.sin(b/2),2)));
	s = s*EARTH_RADIUS;
	s = Math.round(s*10000)/10000.0;

	return s;
}

function getRad(d){
	return d*PI/180.0;
}

//js��ȡ�ַ�������Ӣ�Ķ�����
//����������ַ�������ָ�����ȣ���ȡָ�����ȷ��أ����߷���Դ�ַ�����
//�ַ���������

/**
 * js��ȡ�ַ�������Ӣ�Ķ�����
 * @param str����Ҫ��ȡ���ַ���
 * @param len: ��Ҫ��ȡ�ĳ���
 */
function cutstr(str, len, split) {
	var str_length = 0;
	var str_len = 0;
	var maxsindex = 0;
	var strarr=new Array();
	str_cut = new String();
	str_len = str.length;
	for (var i = 0; i < str_len; i++) {
		a = str.charAt(i);
		str_length++;
		if (escape(a).length > 4) {
			//�����ַ��ĳ��Ⱦ�����֮�����4
			str_length++;
		}
		if(typeof split != 'undefined' && a == split){
			maxsindex = i;
		}
		str_cut = str_cut.concat(a);
		if (str_length >= len) {
			if(typeof split != 'undefined'){
				strarr[0] = str.substr(0, maxsindex);
				strarr[1] = str.substr(maxsindex);
				strarr[2] = true;
				return strarr;
			}else{
				str_cut = str_cut.concat("...");
				return str_cut;
			}

		}
	}
	//��������ַ���С��ָ�����ȣ��򷵻�Դ�ַ�����
	if (str_length < len) {
		return str;
	}
}
//����ҳ��ͷ���˵�����
function load_top_menu(url,title,iscity,noback,hreurl,region){
	var cityName="����";
	if(localStorage.length!=0 && localStorage.getItem('cityName') != null){
		cityName = localStorage.getItem('cityName');
	}
	//onclick="goBack();"
	var html = '';
	if(!noback){
		html+= '<a href=""   onclick="javascript:window.history.go(-1);return false;" class="restback"><img src="/viewlibs/default/templates/cinema/images/restback.png"></a>';
	}else{
		if(noback == 'guding'){
			html+= '<a href="'+hreurl+'" class="restback"><img src="/viewlibs/default/templates/cinema/images/restback.png"></a>';
		}
	}
	if(iscity){
		html+='<a href="/cinema/city.php?goback='+url+'" class="restdown"><span>'+cityName+'</span><img src="/viewlibs/default/templates/cinema/images/1212down.png"></a>';
	}
	if(region != undefined)
	{
		html+='<img id="btn" src="/viewlibs/default/templates/cinema/images/shaixuan2.png" class="regionshaixuan" style="width: 0.8rem;top: 0.1rem;">';
	}

	if(title == 'search') {
		html += '<div style="position:relative;width: 10rem;height:2rem;line-height: 1.2rem;border-radius: 7px;text-align: center; margin: 0.3rem auto 0;">' +
			'<img src="/viewlibs/default/templates/cinema/images/ss1.png" style="position:absolute;left:8px;top: 10px;height: 0.6rem;">'+
			'<input type="text" name="search" readonly="readonly" placeholder="��ӰԺ" onclick="searchjump()" style="font-size:0.6rem;padding:0.6rem 1.5rem;box-shadow:none;border-radius:3px;margin-top:0.1rem;width: 80%;height:0;border: 1px #eee solid;background:none"/>' +
			'</div>';

	}else{
		html += '<div class="restcenter">' + title + '</div>';
	}

	return  html;
}

function GetRequest() {
	var url = location.search; //��ȡurl��"?"������ִ�
	var theRequest = new Object();
	if (url.indexOf("?") != -1) {
		var str = url.substr(1);
		strs = str.split("&");
		for(var i = 0; i < strs.length; i ++) {
			theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
		}
	}
	return theRequest;
}
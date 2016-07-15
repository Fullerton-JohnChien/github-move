//var contextPath = "/admin_pdo2";

/*
* 檢查Email格式
* email : email
* return true or false
*/
function verifyEmailAddress(email) 
{ 
	var pattern = /^([a-zA-Z0-9_.-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/; 

	flag = pattern.test(email); 

	if(flag) 
		return true; 
	else 
		return false; 
}

/*
* 檢查 checkbox 是否勾選
* objName: checkbox 物件
* checkNum: 檢查勾選數量
* return: true 通過檢查, flase 未通過檢查
*/
function checkCheckbox(objName, checkNum) {
	var count = 0;
	$("input[name='" + objName + "']:checkbox:checked").each(function(){
		count++;
		});
	if (count >= checkNum) return true;
	else return false;
}

/*
* 檢查手機門號
* mobilePhone: mobile phone 物件
* return: true 通過檢查, flase 未通過檢查
*/
function checkMobile(mobile) {
	if (!checkTextValue(mobile, 10, 10, 5) || mobile.val().substring(0,2) != "09") {
		//mobile.focus();
		return false;
	}
	return true;
}

/*
* 檢查字串長度
* obj : html物件
* maxSize : 最大長度
* return void
*/
function checkTextLength(obj, maxSize)
{
	var maxlength = new Number(maxSize);
	if(obj.value.length > maxlength)
	{
		var val = obj.value.substring(0, maxlength);
		if(val.length == maxlength)
		{
			alert("長度錯誤 最多只能 " + maxlength + " 字");
			obj.value = obj.value.substring(0, maxlength);
		}
	}
}

/*
dataType :	0: Any character
		1: Must be a numeric, include integer and float
		2: Must be a letter, not include any 0-9
		3: Include only a-z, A-Z And 0-9
		4: mail format check , a-z, A-Z , 0-9 , @ and _ , - , .
		5: only 0-9
*/
function checkTextValue(inText, minLength, maxLength, dataType){
	if((inText.val().length < minLength)||(inText.val().length > maxLength)){
		//inText.focus();
		return false;
	}

	if((dataType == 1) && isNaN(inText.val())){
		//inText.focus();
		return false;
	}

	if(dataType == 2){
		var s;
		var dataString = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"
		for(var i=0;i<inText.val().length;i++){
			s = inText.val().substring(i,i+1);
			if(dataString.indexOf(s)<0){
				//inText.focus();
				return false;
			}
		}
	}

	if(dataType == 3){
		var s;
		var dataString = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"
		for(var i=0;i<inText.val().length;i++){
			s = inText.val().substring(i,i+1);
			if(dataString.indexOf(s)<0){
				//inText.focus();
				return false;
			}
		}
	}

	if(dataType == 4){
		var s;
		var dataString = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.-_@"
		if(inText.val().indexOf("\@")<1){
			//inText.focus();
			return false;
		}else{
			for(var i=0;i<inText.val().length;i++){
				s = inText.val().substring(i,i+1);
				if(dataString.indexOf(s)<0){
					//inText.focus();
					return false;
				}
			}
		}
	}

	if(dataType == 5){
		var s;
		var dataString = "0123456789"
		for(var i=0;i<inText.val().length;i++){
			s = inText.val().substring(i,i+1);
			if(dataString.indexOf(s)<0){
				//inText.focus();
				return false;
			}
		}
	}

	return true;
}
function numberFormat(number, c, d, t) {
	var n = number, c = isNaN(c = Math.abs(c)) ? 0 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
	return s + (j ? i.substr(0, j) + d : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? t + Math.abs(n - i).toFixed(c).slice(2) : "");
};


/**
* 計算/限制最大字數
*/
function calWords(obj, wording_size, msg){
	var maxlength = new Number(wording_size);
	if(obj.value.length > maxlength){
		var val = obj.value.substring(0, maxlength);
		if(val.length == maxlength)	{
			if(msg != undefined && msg.length != '') alert(msg);
			obj.value = obj.value.substring(0, maxlength);
		}
	}
}
function isDigit(str){ 
    var reg = /^\d*$/; 
    return reg.test(str); 
} 

/**
 * 將簡訊特殊字轉為全型
 * @param val
 * @returns
 */
function escapeSmsString(val){
	var t = val;
	var src = new Array('&', '?', '=');
	var tar = new Array('＆', '？', '＝');
	for(var i=0 ; i<src.length ; i++){
		while(t.indexOf(src[i]) >= 0){
			t = t.replace(src[i], tar[i]);
		}
	}
	return t;
}
//解決IE8
function parseISO8601(dateStringInRange) {
    var isoExp = /^\s*(\d{4})-(\d\d)-(\d\d)\s*$/,
        date = new Date(NaN), month,
        parts = isoExp.exec(dateStringInRange);

    if(parts) {
      month = +parts[2];
      date.setFullYear(parts[1], month - 1, parts[3]);
      if(month != date.getMonth() + 1) {
        date.setTime(NaN);
      }
    }
    return date;
}

function isValidDate(d) {
	if ( Object.prototype.toString.call(d) !== "[object Date]" )
		return false;
	return !isNaN(d.getTime());
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

/**
 * 顯示背景遮罩浮動視窗
 * @param itemContent
 */
function showFadeDiv(itemContent, onTop){
	if($('#tag_loading').length == 0){
		$('body').append('<div id="tag_loading" style="display:none" align="center">' + itemContent + '</div>');
	}else{
		$('#tag_loading').html(itemContent );
	}
	var document_height = $(document).height();
	var window_height = $(window).height();
	var max_height = (document_height > window_height) ? document_height:window_height;
	var scroll_top = $(window).scrollTop();
	var obj_width = parseInt($('#tag_loading').width());
	var obj_height = parseInt($('#tag_loading').height());
	var left = ($(window).width() - obj_width) / 2;
	var top = 0;
	if(!onTop){
		if(obj_height < window_height){ 
			top = scroll_top + (window_height - obj_height) / 2; 
		}else{
			top = scroll_top;
		}
	}
	if($('#tag_block').length == 0){
		$('<div/>', {id: 'tag_block'}).appendTo(document.body);
		$("#tag_block").css({
			'background':'#666',
			'z-index':998, 
			'position':'absolute', 
			'display':'none',
			"width":$(document).width(), 
			'height':max_height,
			'left':'0px',
			'top':'0px'
		});
	}

	$("#tag_block").css('opacity', 0);
	$("#tag_block").show();
	$("#tag_block").fadeTo("slow", 0.5);

	$('#tag_loading').css({
		'position':'absolute', 
		'left':left + 'px', 
		'top':top + 'px', 
		'z-index':999
		});
	$('#tag_loading').show();

	// loading_circle.gif
}

/**
 * 隱藏背景遮置浮動視窗
 * @param itemContent
 */
function hideFadeDiv(){
	$("#tag_loading").hide();	
	$("#tag_block").hide(1000);
}





// add by John
function date_addDay(dateString, val) {
	var tDate = new Date(dateString);
	tDate.setDate(tDate.getDate() + val);
	
	return date_getString(tDate);
}
function date_getString(date, delimiter) {
	var t = '-';
	if (delimiter) t = delimiter;
	
	var y = date.getFullYear();
	var m = date.getMonth() + 1;
	var d = date.getDate();

	return y + t + padLeft(m, 2) + t + padLeft(d, 2);
}
function date_today() {
	return date_getString(new Date());
}
function padLeft(str, len) {
	str = '' + str;
    return str.length >= len ? str : padLeft('0' + str, len);
}
function padRight(str, len) {
	str = '' + str;
	return str.length >= len ? str : padRight(str + '0', len);
}
function printMsg(msg) {
	if ($('#sys_debugMsg').length) {
	}
	else {
		$(document.body).prepend('<div id="sys_debugMsg"></div>');
	}

	$('#sys_debugMsg').text($('#sys_debugMsg').text() + msg);
}
function select_setOptions(objId, keyValueItems, defVal, firstVal, firstText) {
	var obj = $('#' + objId);
	obj.find('option').remove().end();
	if (firstText) obj.append('<option value="' + firstVal + '">' + firstText + '</option>');
	$.each(keyValueItems, function(idx, item) {obj.append('<option value="' + item.key + '">' + item.value + '</option>');});
	if (defVal) obj.val(defVal);
}
function set_cookie ( cookie_name, cookie_value, lifespan_in_days, valid_domain )
{
  // http://www.thesitewizard.com/javascripts/cookies.shtml
  var domain_string = valid_domain ? ("; domain=" + valid_domain) : '' ;
  document.cookie = cookie_name + "=" + encodeURIComponent( cookie_value ) +
      "; max-age=" + 60 * 60 * 24 * lifespan_in_days +
      "; path=/" + domain_string ;
}

function scrollToConvas(convas) {
	if($(convas).length > 0){
		$('html, body').animate({
	        scrollTop: $(convas).offset().top
	    }, 700, 'swing');
	}
}

function trans_area(city_name){
	if (city_name=='基隆市' || city_name=='台北市' || city_name=='新北市' || city_name=='桃園市' || city_name=='新竹縣' || city_name=='新竹市'){
		area_name="north";
	}
	else if (city_name=='宜蘭縣' || city_name=='花蓮縣' || city_name=='台東縣'){
		area_name="east";
	}
	else if (city_name=='雲林縣' || city_name=='嘉義縣' || city_name=='嘉義市' || city_name=='台南市' || city_name=='高雄市' || city_name=='屏東縣'){
		area_name="south";
	}
	else if (city_name=='台中市' || city_name=='彰化縣' || city_name=='南投縣' || city_name=='澎湖縣'){
		area_name="west";
	}
	else if (city_name=='金門縣' || city_name=='連江縣' || city_name=='澎湖縣'){
		area_name="islands";
	}
	else{
		area_name="";
	}
	return area_name;
}

function search_bar(){
	var bar_class = $.trim($("#bar_class").val());
	var msg = '';
	if($.trim($("#bar_class").val()) == '') { msg += '請選擇類別\n'; }
	if(msg != ''){
		alert(msg);
		return false;
	}else{
		var bar_area_name = $('#bar_area option:selected').text();
		var sUrl='';
		var sf=trans_area(bar_area_name);
		sUrl='../location/?f='+bar_class+'&sf=&areas='+sf+'&pageno=1';
		alert(sUrl);
		location.href=sUrl;
	}
}

function search_bar(){
	var bar_class = $.trim($("#bar_class").val());
	var msg = '';
	if($.trim($("#bar_class").val()) == '') { msg += '請選擇類別\n'; }
	if(msg != ''){
		alert(msg);
		return false;
	}else{
		var bar_area_name = $('#bar_area option:selected').text();
		var sUrl='';
		var sf=trans_area(bar_area_name);
		sUrl='../location/?f='+bar_class+'&sf=&areas='+sf+'&pageno=1';
		//alert(sUrl);
		location.href=sUrl;
	}
}

function search_scroll(){
	var scroll_class = $.trim($("#scroll_class").val());
	var msg = '';
	if($.trim($("#scroll_class").val()) == '') { msg += '請選擇類別\n'; }
	if(msg != ''){
		alert(msg);
		return false;
	}else{
		var scroll_area_name = $('#scroll_area option:selected').text();
		var sUrl='';
		var sf=trans_area(scroll_area_name);
		sUrl='../location/?f='+scroll_class+'&sf=&areas='+sf+'&pageno=1';
		//alert(sUrl);
		location.href=sUrl;
	}
}

function search_top(){
	var msg = '';
	 var checkstatusFalg = true;
		$('input[name="cate"]:checked').each(function(){
			checkstatusFalg = false;
	 	});
	 	if(checkstatusFalg){
	 		msg += '請選擇類別 !\n';
	 	}else{
	 		var top_class = $('input[name="cate"]:checked').val();
	 	}
	
	if(msg != ''){
		alert(msg);
		return false;
	}else{
		//alert(top_class);
		var top_area_name = $('#top_area option:selected').text();
		var sUrl='';
		var sf=trans_area(top_area_name);
		sUrl='../location/?f='+top_class+'&sf=&areas='+sf+'&pageno=1';
		//alert(sUrl);
		location.href=sUrl;
	}
}


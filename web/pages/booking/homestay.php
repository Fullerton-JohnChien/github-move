<?
/**
 * 說明：
 * 作者：Steak
 * 日期：2015年11月11日
 * 備註：
 * test Url: http://local.tw.tripitta.com/booking/aaa/45/?beginDate=2015-11-23&endDate=2015-11-24&roomType=2&roomQuantity=1
 * 2015-12-17 John 售完btn顯示改為flex, 中間條件重設bar的人數、間數欄位加title說明
 * 2016-03-17 John, Steak 調整此頁效能
 * 2016-03-18 Steak 如此間民宿無效(解約、問題下架)，則導到首頁
 * 2016-06-06 Lewis 若入住日期小於今天，將日期調為 預設值:Date('Y-m-d', strtotime("+ 1 days"))
 * 2016-06-08 Lewis 調整 售完的button，不會再導至check_room.php，然後出現保留的房間已釋出的alertmsg
 */
header("Content-Type: text/html; charset=utf-8");
require_once __DIR__ . '/../../config.php';
$tripitta_web_service = new tripitta_web_service();
$tripitta_homestay_service = new tripitta_homestay_service();
$home_stay_promotion_service = new Home_stay_promotion_service();
$home_stay_channel_project_service = new Home_stay_channel_project_service();
$certificate_dao = Dao_loader::__get_home_stay_certificate_dao();
$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);
$track_content_view_dao = Dao_loader_tripitta::__get_track_content_view_dao();

$hs_id = $_REQUEST['hs_id'];
$areaCode = get_val('area_code');
$beginDate = get_val('beginDate');
$endDate = get_val('endDate');
$roomType = (get_val('roomType') == null) ? 2 : get_val('roomType');
$roomQuantity = (get_val('roomQuantity') == null) ? 1 : get_val('roomQuantity');
$login_user_data = $tripitta_web_service->check_login();
$serialId = 0;
if(!empty($login_user_data)) {
	$serialId = $login_user_data['serialId'];
}

// 排除1648
if($hs_id == 1648) gotourl("/");


if(date('Y-m-d', strtotime($beginDate)) !== $beginDate) $beginDate = "";
if(date('Y-m-d', strtotime($endDate)) !== $endDate) $endDate = "";
if(empty($beginDate) && empty($endDate)) {
	$beginDate = Date('Y-m-d', strtotime("+ 1 days"));
	$endDate = Date('Y-m-d', strtotime("+ 2 days"));
}
else if(!empty($beginDate) && empty($endDate)) {
	$endDate = Date('Y-m-d', strtotime($beginDate . "+ 1 days"));
}
else if(empty($beginDate) && !empty($endDate)) {
	$beginDate = Date('Y-m-d', strtotime($endDate."- 1 days"));
}
else if (!empty($beginDate) && !empty($endDate) && strtotime($beginDate) < strtotime(date('Y-m-d'))) {
    $beginDate = Date('Y-m-d', strtotime("+ 1 days"));
    $endDate = Date('Y-m-d', strtotime("+ 2 days"));
}


$dateDiff = (strtotime($endDate) - strtotime($beginDate)) / (86400);

$home_stay_row = $tripitta_homestay_service -> get_home_stay_info($hs_id);
// 判斷民宿是否有效
if(empty($home_stay_row)) {
	//header("Location: https://www.tripitta.com/web/pages/adout/404.php");
	alertmsg("很抱歉，搜尋不到 此旅宿的相關內容!","/");
	exit();
}

$certificateRow = $certificate_dao->findValidHfHomeStayCertificateListByHomeStayId($hs_id, 1);

$home_stay_photo_row = $tripitta_homestay_service -> get_home_stay_photo($hs_id);
$home_stay_rule_row = $tripitta_homestay_service -> get_home_stay_rule($hs_id);
$home_stay_facility = $tripitta_homestay_service -> get_home_stay_facility($hs_id);
$home_stay_plan = $tripitta_homestay_service -> get_home_stay_plan($hs_id);
$room_type_row = $tripitta_homestay_service -> get_room_type_by_home_stay_id($hs_id, $beginDate, $endDate, $roomType, $roomQuantity);

$home_stay_gc = $tripitta_homestay_service -> get_home_stay_geographical_coordinates($hs_id, $beginDate, $endDate);

$favorite_list = array();
if(!empty($login_user_data)) {
	$user_favorite_type_ids = $tripitta_web_service->get_user_favorite_type_ids('homestay');
	$favorite_list = $tripitta_web_service->find_user_favorite_by_user_id_and_ref_type_ids($login_user_data["serialId"], $user_favorite_type_ids);
}

$favorite_class = "fa-heart-o";
foreach($favorite_list as $favorite_row) {
	if($favorite_row["uf_type"] == 10 && $favorite_row["uf_home_stay_id"] == $hs_id) {
		$favorite_class = "fa-heart";
		break;
	}
}
// 有無房間
// $room_cnt = 0;
// foreach ($home_stay_gc['hsList'] as $hs) {
// 	if($hs_id == $hs['hs_id']) {
// 		foreach ($home_stay_gc['roomList'] as $item) {
// 			if ($item['hs_id'] == $hs['hs_id']) {
// 				$room_cnt = $item['cnt'];
// 			}
// 		}
// 	}
// }
// if($room_cnt == 0) alertmsg("目前此旅宿無可販售之房間", "/booking/");

$last_cancel_days = $home_stay_rule_row['lastCancelDays'];

$tripitta_web_service = new tripitta_web_service();
$currency_id = $tripitta_web_service->get_display_currency();
$currency_code = NULL;
$exchange_rate = 1;

// 取得匯率
if (1 == $currency_id) {
    $currency_code = 'NTD';
    $exchange_rate = 1;
}
else {
    $exchange = $tripitta_homestay_service->get_exchange_by_currency_id($currency_id);
    $currency_code = $exchange['cr_code'];
    $exchange_rate = $exchange['erd_rate'];
}
// printmsg($currency_code . ' - ' . $exchange_rate);
// 取得適用的促銷模組
$promotion_list = $home_stay_promotion_service->find_valid_by_homestay_id_and_date_range($hs_id, $beginDate, $endDate);
// printmsg($promotion_list);
// 取得各房型定價
$room_type_setting_price_list = $tripitta_homestay_service->get_home_stay_setting_price_by_home_stay_id($hs_id);

// 取得各房型售價
$room_type_sell_price_list = $tripitta_homestay_service->get_home_stay_sell_price_by_home_stay_id_and_date_range($hs_id, $beginDate, $endDate);
// printmsg($room_type_sell_price_list);

// 取得適用的銀行活動
$site = array(1, 3);
$project_list = $home_stay_channel_project_service -> find_valid_by_homestay_id_and_date_range($hs_id, $beginDate, $endDate, $site);
// printmsg($project_list);
// 紀錄view count
if (!empty($hs_id)){
	$track_count_row = $track_content_view_dao -> load(10, $hs_id);
	if(empty($track_count_row)){
		$data = array('type_id'=>10, 'ref_id'=>$hs_id);
		$tripitta_api_client_service ->add_view_count($data);
		// 紀錄log
		$item = array();
		$item["tcv_folder_id"] = 10;
		$item["tcv_ref_id"] = $hs_id;
		$item["tcv_src_ip"] = get_remote_ip();
		$item["tcv_create_time"] = date('Y-m-d H:i:s');
		$track_content_view_dao -> save($item);
	}else{
		$a = $track_count_row['tcv_create_time'];
		$b = strftime(date("Y-m-d H:i:s")); //現在時間
		$a = date("Y-m-d H:i:s", strtotime($a.'+30 min'));
		$diff =  strtotime($b) - strtotime($a); //單位秒
		if($diff > 0 || get_remote_ip() != $track_count_row['tcv_src_ip']){
			$data = array('type_id'=>10, 'ref_id'=>$hs_id);
			$tripitta_api_client_service ->add_view_count($data);
			// 紀錄log
			$item = array();
			$item["tcv_folder_id"] = 10;
			$item["tcv_ref_id"] = $hs_id;
			$item["tcv_src_ip"] = get_remote_ip();
			$item["tcv_create_time"] = date('Y-m-d H:i:s');
			$track_content_view_dao -> save($item);
		}
	}
}

?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<? include __DIR__ . "/../common/head.php"; ?>
<meta name="description" content="<?= $home_stay_row['description'] ?>" >
<title><?= $home_stay_row['name'] ?> - 旅宿訂房 - Tripitta 旅必達</title>
<link rel="stylesheet" href="/web/css/main.css?01121536">
<link rel="stylesheet" href="/web/css/home_stay.css"  type="text/css"/>
<style>
.gm-style-iw +div {
    width: 24px !important;
    height: 24px !important;
    left: 320px !important;
    top: 15px !important;
    background: url("/web/img/member-close.jpg") #ffe500;
    background-position: -1038px -257px;
    opacity: 1 !important;
    z-index: 999;
}
.gm-style-iw +div > img{
    display:none;
}
.gm-style-iw {
	width: 300px !important;
	top: 15px !important;
	left: 20px !important;
	background-color: #fff;
	box-shadow: rgba(0, 0, 0, 0.6) 0px 1px 6px;
	z-index: -1;
}
i.fa.fa-heart {
    color: #ff8c7a;
}
</style>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<script src="/web/js/jquery-scrolltofixed-min.js"></script>


<link rel="stylesheet" type="text/css" href="/web/css/flexslider.css" />
<script type="text/javascript" src="/web/js/jquery.flexslider-min.js"></script>
<script type="text/javascript">
$(function(){
	 map_init();
     $.datepicker.regional['zh-TW']={
    	dayNames:["星期日","星期一","星期二","星期三","星期四","星期五","星期六"],
    	dayNamesMin:["日","一","二","三","四","五","六"],
    	monthNames:["一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"],
    	monthNamesShort:["一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"],
    	prevText:"上月",
    	nextText:"次月",
    	weekHeader:"週"
    };

     $.datepicker.setDefaults($.datepicker.regional["zh-TW"]);
     $("#beginDate").datepicker({dateFormat:"yy-mm-dd", minDate: new Date()});
     // $('#beginDate').datepicker('option', 'numberOfMonths', 2);
     $("#endDate").datepicker({dateFormat:"yy-mm-dd", minDate: new Date()});
     // $('#endDate').datepicker('option', 'numberOfMonths', 2);
     $('#beginDate').change(function(){dateCompare2('beginDate','endDate');});
     $('#endDate').change(function(){dateCompare3('beginDate','endDate');});

    //  $('.header').scrollToFixed();
     $('.sidebar').scrollToFixed({
         marginTop: $('.header').outerHeight(true) + 10,
         //limit: $('#map').offset().top - 300,
         zIndex: 6
     });

	 $('.flexslider').eq(0).flexslider({
		animation: "slide"
	 });

     $('#btn_add_remove_favorite').click( function() { add_or_remove_favorite(); });
});
// 滑至某個div
function goDiv(type) {
	$("html,body").animate({scrollTop: $('#'+type).offset().top-30}, 300);
}

var curIndex = 0,  //当前index
imgLen = <?= (count($home_stay_photo_row['photos']) >= 5) ? 5 : count($home_stay_photo_row['photos']) ?>;  //图片总数
//左箭头点击处理
function clickPrev() {
    //根据curIndex进行上一个图片处理
    curIndex = (curIndex > 0) ? (--curIndex) : (imgLen - 1);
    changeTo(curIndex);
}

//右箭头点击处理
function clicknext(){
     curIndex = (curIndex < imgLen - 1) ? (++curIndex) : 0;
     changeTo(curIndex);
}

function changeTo(num){
    var goLeft = num *  640;
    $(".imgList").animate({left: "-" + goLeft + "px"},500);
}

var curIndex2 = 0;  //当前index
//左箭头点击处理
function clickPrev2(imgLen2) {
    //根据curIndex进行上一个图片处理
    curIndex2 = (curIndex2 > 0) ? (--curIndex2) : (imgLen2 - 1);
    changeTo2(curIndex2);
}

//右箭头点击处理
function clicknext2(imgLen2){
	curIndex2 = (curIndex2 < imgLen2 - 1) ? (++curIndex2) : 0;
     changeTo2(curIndex2);
}

function changeTo2(num){
    var goLeft = num *  600;
    $(".imgList2").animate({left: "-" + goLeft + "px"},500);
}


function showIntro(){
	$('#hsIntro2').show();
	$('#hsIntro1').hide();
	$('.introContent').css('overflow-y', 'auto');
	$('.img-more002').hide();
}
var map;
var myIcon = '/../../web/img/meicon.png';
var haveRoomIcon = '/../../web/img/bnb.png';
var noRoomIcon = '/../../web/img/bnb_off.png';
var activityIcon = '/../../web/img/activity.png';
var viewpointIon = '/../../web/img/viewpoint.png';
var foodIcon = '/../../web/img/food.png';
var gitIcon = '/../../web/img/git.png';
var homeStayAry = [];
var fullHomeStayAry = [];
var activityAry = [];
var viewpointAry = [];
var foodAry = [];
var gitAry = [];
var openInfoWindow;

// 取得旅宿的 infowindow
function get_homestay_info_window_string(areaCode, hsId, hsPhoto, hsName, location, favorite, viewCount, currencyCode, sellPrice, taImage, taId, taCount) {
	var contentString =
		'<div class="popupMapPrev" >'
		+ '<a href="/booking/' + areaCode + '/' + hsId + '/"><img src="' + hsPhoto + '" alt=""></a>'
		+ '<section class="pointInfo">'
		+ '<h3 class="pointName">' + hsName + '</h3>'
		+ '<h4 >'
		+ '<p class="location"><i class="fa fa-map-marker" ></i><span >' + location + '</span></p>'
		+ '<p class="favorite"><i class="fa fa-heart" ></i><span >' + favorite + '</span></p>'
		+ '<p class="viewCount"><i class="fa fa-eye" ></i><span >' + viewCount + '</span></p>'
		+ '</h4>'
		+ '<h5>'
		+ '<span>' + currencyCode + '</span>'
		+ '<span class="cost">' + sellPrice + '</span>'
		+ '<span>起</span>'
		+ '</h5>';

	if (taImage && taImage != '') {
		contentString += '<a href="javascript:open_trip_advisor_review(' + taId + ')" class="tripadvisorLogoMap">'
		+ taImage
		+ '<h6>'
		+ '<span class="count">' + taCount + '</span>'
		+ '<span>則評論</span>'
		+ '</h6>'
		+ '</a>	';
	}

	contentString += '</section>'
		+ '</div>';

	return contentString;
}

// 取得基礎資料的 infowindow
function get_content_info_window_string(tcPhoto, tcName, location, favorite, viewCount, ta_count, ta_img, tari_id, linkurl) {
	var contentString =
		'<div class="popupMapPrev" >'
		+ '<a href="'+linkurl+'" target="_blank"><img src="' + tcPhoto + '" alt="" onerror="javascript:this.src=\'/../../web/img/no-pic.jpg\';"></a>'
		+ '<section class="pointInfo">'
		+ '<h3 class="pointName">' + tcName + '</h3>'
		+ '<h4 >'
		+ '<p class="location"><i class="fa fa-map-marker" ></i><span >' + location + '</span></p>'
		+ '<p class="favorite"><i class="fa fa-heart" ></i><span >' + favorite + '</span></p>'
		+ '<p class="viewCount"><i class="fa fa-eye" ></i><span >' + viewCount + '</span></p>'
		+ '</h4>'
		+ '<a href="javascript:open_trip_advisor_review('+ tari_id +')" class="tripadvisorLogoMap">'
		+ ta_img
		+ '<h6>'
		+ '<span class="count">'+ta_count+'</span>'
		+ '<span>則評論</span>'
		+ '</h6>'
		+ '</a>	'
		+ '</section>'
		+ '</div>';

	return contentString;
}

function add_google_map_listener(marker, infowindow) {
	google.maps.event.addListener(marker, 'click', function() {
		infowindow.open(map, marker);
 		if (infowindow !== window.openInfoWindow) window.openInfoWindow.close();
 		window.openInfoWindow = infowindow;
 	});
	google.maps.event.addListener(infowindow, 'domready', function() {
		var iwOuter = $('.gm-style-iw');
		var iwBackground = iwOuter.prev();
		iwBackground.children(':nth-child(2)').css({'display' : 'none'});
		iwBackground.children(':nth-child(4)').css({'display' : 'none'});
	});
}

function map_init() {
	var latlng = new google.maps.LatLng(<?= $home_stay_gc['lat'] ?>+0.03, <?= $home_stay_gc['lng'] ?>);
	var myOptions = {
		zoom: 13,
    	center: latlng,
    	mapTypeId: google.maps.MapTypeId.ROADMAP,
    	scrollwheel: false
	};
    map = new google.maps.Map(document.getElementById("map"), myOptions);
	<?php
	//foreach ($home_stay_gc['hsList'] as $hs) {
		//if($hs_id == $hs['hs_id']) {
		$tCount = 0;
		$cnt = 0;
		foreach ($home_stay_gc['roomList'] as $item) {
			if ($item['hs_id'] == $hs_id) {
				$tCount++;
				$cnt = $item['cnt'];
			}
		}

		$ta_conut = 0;
		$ta_img = '';
		$tari_id = 0;
		foreach ($home_stay_gc['tripadvisor_row'] as $tr){
			if ($tr['hs_id'] == $hs_id) {
				$ta_conut = $tr['tari_review_count'];
				$ta_img = '<img src="https://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$tr['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;"/>';
				$tari_id = $tr['tari_id'];
			}
		}

		foreach ($home_stay_gc['hsList'] as $hs) {
			if($hs['hs_id'] == $hs_id) {
				$home_info_row = $hs;
			}
		}
		//$home_info_row = $tripitta_homestay_service -> get_home_stay_map_info($hs_id);

		$icon = 'haveRoomIcon';
		if ($cnt == 0) $icon = 'noRoomIcon';
		//if ($hs_id == $hs['hs_id']) $icon = 'myIcon';

		?>
	var contentString = get_homestay_info_window_string('<?= $areaCode ?>'
		, '<?= $hs_id ?>'
		, '<?php echo $home_stay_photo_row['img_url'], 'home_stay/', $hs_id, '/', $home_stay_row['main_photo'], '_middle.jpg'?>'
		, '<?php echo preg_replace("/'/", "\\'", $home_stay_row['name'])?>'
		, '<?= $home_info_row['location'] ?>'
		, '<?= $home_info_row['favorite_count']?>'
		, '<?= $home_info_row['click_count'] ?>'
		, '<?= $currency_code ?>'
		, '<?= number_format($home_info_row['min_price']/$exchange_rate) ?>'
		, '<?= $ta_img ?>'
		, '<?= $tari_id ?>'
		, '<?= $ta_conut ?>'
		);
 	var infowindow = new google.maps.InfoWindow({content: contentString, maxWidth: 300});
 	var marker = new google.maps.Marker({
 		position: new google.maps.LatLng(<?php echo $home_stay_gc['lat']?>,<?php echo $home_stay_gc['lng']?>),
 		title: '<?php echo preg_replace("/'/", "\\'", $home_stay_row['name'])?>',
 		icon: <?php echo $icon?>,
		map: map
 	});
 	infowindow.open(map,marker);
 	window.openInfoWindow = infowindow;
 	google.maps.event.addListener(marker, 'click', function() {
 		infowindow.open(map,marker);
 		if (infowindow !== window.openInfoWindow) window.openInfoWindow.close();
 		window.openInfoWindow = infowindow;
	});
 	google.maps.event.addListener(infowindow, 'domready', function() {
 	   // Reference to the DIV which receives the contents of the infowindow using jQuery
 	   var iwOuter = $('.gm-style-iw');

 	   /* The DIV we want to change is above the .gm-style-iw DIV.
 	    * So, we use jQuery and create a iwBackground variable,
 	    * and took advantage of the existing reference to .gm-style-iw for the previous DIV with .prev().
 	    */
 	   var iwBackground = iwOuter.prev();

 	   // Remove the background shadow DIV
 	   iwBackground.children(':nth-child(2)').css({'display' : 'none'});

 	   // Remove the white background DIV
 	   iwBackground.children(':nth-child(4)').css({'display' : 'none'});

 	   iwBackground.children(':nth-child(6)').css({'display' : 'none'});
 	});
<?
	    $idx = 0;
	 // 設定民宿marker
	 if (!empty($home_stay_gc['hsList'])) {
	 	foreach ($home_stay_gc['hsList'] as $hs) {
			if($hs_id == $hs['hs_id']) continue;
	 		$idxs = 0;
	 		$tCount = 0;
	 		$cnt = 0;
	 		foreach ($home_stay_gc['roomList'] as $item) {
	 			if ($item['hs_id'] == $hs['hs_id']) {
	 				if (intval($item['cnt']) == 0) {
	 					$idxs++;
	 				}
	 				$tCount++;
	 				$cnt = $item['cnt'];
	 			}
	 		}

			$ta_conut = 0;
			$ta_img = '';
			$tari_id = 0;
			foreach ($home_stay_gc['tripadvisor_row'] as $tr){
				if ($tr['hs_id'] == $hs['hs_id']) {
					$ta_conut = $tr['tari_review_count'];
					$ta_img = '<img src="https://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$tr['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;"/>';
					$tari_id = $tr['tari_id'];
				}
			}

	 		$idx++;
	 		$icon = 'haveRoomIcon';
	 		if ($cnt == 0) $icon = 'noRoomIcon';
	 		if ($hs_id == $hs['hs_id']) $icon = 'myIcon';

?>
	var contentString<?php echo $idx?> = get_homestay_info_window_string('<?= $areaCode ?>', '<?= $hs['hs_id'] ?>', '<?php echo $home_stay_photo_row['img_url'], 'home_stay/', $hs['hs_id'], '/', $hs['hs_main_photo'], '_middle.jpg'?>', '<?php echo preg_replace("/'/", "\\'", $hs['hs_name'])?>', '<?= $hs['location'] ?>', '<?= $hs['favorite_count']?>', '<?= $hs['click_count'] ?>', '<?= $currency_code ?>', '<?= number_format($hs['min_price']/$exchange_rate) ?>', '<?= $ta_img ?>', '<?= $tari_id ?>', '<?= $ta_conut ?>');
 	var infowindow<?php echo $idx?> = new google.maps.InfoWindow({content: contentString<?php echo $idx?>});
 	var marker<?php echo $idx?> = new google.maps.Marker({position: new google.maps.LatLng(<?php echo $hs['gc_latitude']?>,<?php echo $hs['gc_longitude']?>), title: '<?php echo preg_replace("/'/", "\\'", $hs['hs_name'])?>', icon: <?php echo $icon?>, map: map});
	 <?php if ($hs_id != $hs['hs_id']) echo 'homeStayAry.push(marker', $idx, ');'?>
	 <?php if ($hs_id != $hs['hs_id'] && $cnt == 0) echo 'fullHomeStayAry.push(marker', $idx, ');'?>
	 window.add_google_map_listener(marker<?php echo $idx?>, infowindow<?php echo $idx?>);
<?php }}?>

<?php
	// 設定美食marker
	if (!empty($home_stay_gc['contentList'])) {
		foreach ($home_stay_gc['contentList'] as $key => $ss) {
			$idx++;
			if ($ss['tc_type'] == 7) $icon = 'foodIcon';
			if ($ss['tc_type'] == 8) $icon = 'viewpointIon';
			if ($ss['tc_type'] == 12 || $ss['tc_type'] == 15) $icon = 'activityIcon';
			if ($ss['tc_type'] == 82) $icon = 'gitIcon';

			$ta_conut = 0;
			$ta_img = '';
			$tari_id = 0;

			if(!empty($ss['tari_average_rating'])) {
				$ta_conut = $ss['tari_review_count'];
				$ta_img = '<img src="https://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$ss['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;"/>';
				$tari_id = $ss['tari_id'];
			}

			$linkurl = '';
			$type_str = '';
			if(7 == $ss['tc_type']) {
				$type_str = 'food';
			} else if(8 == $ss['tc_type']) {
				$type_str = 'spot';
			} else if(82 == $ss['tc_type']) {
				$type_str = 'gift';
			} else if(12 == $ss['tc_type'] || 15 == $ss['tc_type']) {
				$type_str = 'event';
			}
			$linkurl = '/location/' . $type_str . '/' . $ss['tc_id'] . '/';

?>
	var contentString<?php echo $idx?> = get_content_info_window_string('<?php echo get_config_image_server() . '/photos/' . (is_production() ? 'taiwan_content' : 'taiwan_content_alpha') . '/' . $ss['tc_id']. '/'. $ss['tc_main_photo']. '.jpg'?>', '<?php echo preg_replace("/'/", "\\'", $ss['tc_name'])?>', '<?= $home_stay_gc['content_location'][$key] ?>', '<?= $ss['tc_day_cnt_click']?>', '<?= $ss['tc_collect_total']?>', '<?= $ta_conut ?>', '<?= $ta_img ?>', '<?= $tari_id ?>', '<?= $linkurl ?>');
	var infowindow<?php echo $idx?> = new google.maps.InfoWindow({content: contentString<?php echo $idx?>});
	var marker<?php echo $idx?> = new google.maps.Marker({position: new google.maps.LatLng(<?php echo $ss['gc_latitude']?>,<?php echo $ss['gc_longitude']?>), title: '<?php echo preg_replace("/'/", "\\'", $ss['tc_name'])?>', icon: <?php echo $icon?>});
<?php
	if ($ss['tc_type'] == 7) echo 'foodAry.push(marker', $idx, ');';$icon = 'foodIcon';
	if ($ss['tc_type'] == 8) echo 'viewpointAry.push(marker', $idx, ');';
	if ($ss['tc_type'] == 12 || $ss['tc_type'] == 15) echo 'activityAry.push(marker', $idx, ');';
	if ($ss['tc_type'] == 82) echo 'gitAry.push(marker', $idx, ');';
?>
	window.add_google_map_listener(marker<?php echo $idx?>, infowindow<?php echo $idx?>);
<?php }}?>
}

function showInfo(rt_id){
	$('.overlay').show();
	$('#roomInfo'+rt_id).show();
}

function closeRoomInfo(rt_id){
	$('.overlay').hide();
	$('#roomInfo'+rt_id).hide();
}

var totalRoom = <?= (count($room_type_row)) ?>;
function showMoreRoomType(type) {
	if(type == 1){
		for(i=6;i<=totalRoom;i++){
			$('#roomType_'+i).show(700);
		}
		$('#moreText').html('隱藏其他房型');
		$('#showRoom').attr('onclick', 'showMoreRoomType(2);');
		$('#showRoom').attr('class', 'fa fa-angle-up');
	}
	if(type == 2){
		for(i=6;i<=totalRoom;i++){
			$('#roomType_'+i).hide(700);
		}
		$('#moreText').html('點我看更多房型');
		$('#showRoom').attr('onclick', 'showMoreRoomType(1);');
		$('#showRoom').attr('class', 'fa fa-angle-down');
	}
}

function triMarker(obj, type) {
	// 打開
	if (obj == 0){
		addMarkers(type);
	}
	// 關閉
	else{
		clearMarkers(type);
	}
}

function addMarkers(type) {
	if (type == 'activity') {
		$('#activity').attr('onclick', 'triMarker(1,"activity")');
		for (i in activityAry) activityAry[i].setMap(map);
	}
	else if (type == 'scenic') {
		$('#scenic').attr('onclick', 'triMarker(1,"scenic")');
		for (i in viewpointAry) viewpointAry[i].setMap(map);
	}
	else if (type == 'souvenir') {
		$('#souvenir').attr('onclick', 'triMarker(1,"souvenir")');
		for (i in placeAry) placeAry[i].setMap(map);
	}
	else if (type == 'food') {
		$('#food').attr('onclick', 'triMarker(1,"food")');
		for (i in foodAry) foodAry[i].setMap(map);
	}
}

function clearMarkers(type) {
	if (type == 'activity') {
		$('#activity').attr('onclick', 'triMarker(0,"activity")');
		for (i in activityAry) activityAry[i].setMap(null);
	}
	else if (type == 'scenic') {
		$('#scenic').attr('onclick', 'triMarker(0,"scenic")');
		for (i in viewpointAry) viewpointAry[i].setMap(null);
	}
	else if (type == 'souvenir') {
		$('#souvenir').attr('onclick', 'triMarker(0,"souvenir")');
		for (i in placeAry) placeAry[i].setMap(null);
	}
	else if (type == 'food') {
		$('#food').attr('onclick', 'triMarker(0,"food")');
		for (i in foodAry) foodAry[i].setMap(null);
	}
}

function chkData(){
	//event.preventDefault();
	var dtToday = $('#endDate').val();
	var mydate = $('#beginDate').val();
	var roomType = $('#roomType').val();
	var roomQuantity = $('#roomQuantity').val();

	var Compare= Date.parse(dtToday.toString()) - Date.parse(mydate.toString()); //相差毫秒數
	var ComDay=Compare/(1000*60*24*60); //相差天數

	if (Date.parse(dtToday.toString()) < Date.parse(mydate.toString())) {
		$('#endDate').val('');
		alert("退房日期需大於入住日期!!");
		return;
	}

	if(ComDay >= 10){
		alert('入住日和退房日不能超過10天!!');
		// $('#beginDate').val('');
    	// $('#endDate').val('');
		return;
	}

	 location.href = "/booking/<?= $areaCode ?>/<?= $hs_id?>/?beginDate="+mydate+"&endDate="+dtToday+"&roomType="+roomType+"&roomQuantity="+roomQuantity;
	//$('#form1').attr('action', "/booking/<?= $areaCode ?>/<?= $hs_id?>/?beginDate="+mydate+"&endDate="+dtToday+"&roomType="+roomType+"&roomQuantity="+roomQuantity);
}

function add_or_remove_favorite() {
	<?php if(empty($login_user_data)){ ?>
	show_popup_login();
	return;
	<?php } ?>
	var ref_id = $('#btn_add_remove_favorite').attr('data-id');
	var add = $('#favorite').hasClass('fa-heart-o') ? 1 : 0;
    if(add == 1) {
    	add_favorite('#favorite', 10, ref_id);
    } else {
    	remove_favorite('#favorite', 10, ref_id);
    }

}

function add_favorite(convas, ref_type, ref_id) {
	<?php if(empty($login_user_data)){ ?>
	show_popup_login();
	return;
	<?php } ?>
	var p = {};
    p.func = 'add_favorite';
    p.user_id = $('#user_serial_id').val();
    p.ref_type = ref_type;
    p.ref_id = ref_id;
    // console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 顯示註冊完成並顯示註冊完成popup window
			alert('已加至我的收藏');
			$(convas).removeClass('fa-heart-o').addClass('fa-heart');
			//location.reload();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function remove_favorite(convas, ref_type, ref_id) {
	<?php if(empty($login_user_data)){ ?>
	show_popup_login();
	return;
	<?php } ?>
	remove_items = [];
    remove_items.push({'type_id':ref_type,'ref_id':ref_id});

	var p = {};
    p.func = 'remove_user_favorite';
    p.user_id = $('#user_serial_id').val();
    p.items = remove_items;
    console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
            // 顯示註冊完成並顯示註冊完成popup window
			alert('已從我的收藏移除');
			$(convas).removeClass('fa-heart').addClass('fa-heart-o');
			//location.reload();
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

/**
 * 說明：控制房型優惠的各項動作
 * 作者：John
 * 日期：2015年11月18日
 * 備註：
 */
var is_promotion_event = false;

function toggleRoomTypePromotions(rt_id) {
    if ('block' != $('#promotion_option_' + rt_id).css('display')) {
    	$('.optionGroup').hide();
    	is_promotion_event = true;
    	$('#promotion_option_' + rt_id).show();
    }
}

function selectPromotion(p_id, rt_id) {
	// 取得房型的剩餘空房數
	var empty_room_qty = $('#empty_room_qty_' + rt_id).val();

	// 取得選取的promotion
	var id = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="id"]').val();
	var type = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="type"]').val();
	var rule_value = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="rule_value"]').val();
	var name = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="name"]').val();
	var qty = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="qty"]').val(); // 優惠資格數量
	var amount = numberFormat($('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="amount"]').val());
	var haveBreakfast = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="haveBreakfast"]').val();
	var allowCancel = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="allowCancel"]').val();
	var promotion_type = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="promotion_type"]').val();
	var haveBreakfastText = '不含早餐';
	var allowCancelText = '免費取消';
	if ('1' == haveBreakfast) haveBreakfastText = '含早餐';
	if ('2' == allowCancel) allowCancelText = '不可取消';

	// 可售資格數不可大於房型的空房數
	var sell_qty = qty;
	if (parseInt(sell_qty) > parseInt(empty_room_qty)) sell_qty = empty_room_qty;
// alert('empty_room_qty=' + empty_room_qty + ', qty=' + qty + ', sell_qty=' + sell_qty);

	// 房型選定的優惠
    $('#selectedPromotionId_' + rt_id).val(id);
    $('#selectedPromotionName_' + rt_id).text(name);
    $('#selectedPromotionQty_' + rt_id).text(sell_qty);
    $('#selectedPromotionAmount_' + rt_id).text(amount);
    $('#selectedPromotionHaveBreakfast_' + rt_id).text(haveBreakfastText);
    $('#selectedPromotionAllowCancel_' + rt_id).text(allowCancelText);

    if ('1' != haveBreakfast) $('#selectedPromotionHaveBreakfast_' + rt_id).css('color', 'red');
    else $('#selectedPromotionHaveBreakfast_' + rt_id).css('color', 'gray');

    if ('2' == allowCancel) {
    	$('#selectedPromotionAllowCancel_' + rt_id).css('color', 'red');
    	$('#selectedPromotionAllowCancel_' + rt_id).attr('data-cancelFree', '不可取消房型，請單筆訂購');
    }
    else {
    	$('#selectedPromotionAllowCancel_' + rt_id).css('color', 'gray');
    	$('#selectedPromotionAllowCancel_' + rt_id).attr('data-cancelFree', $('#last_cancel_days').val() + '天前免費取消');
    }

    // 優惠說明 2 連住天數, 3 早鳥天數, 4 最後一分鐘前n小時  (我雞婆多做的，可以拿掉 XD  John 2015-11-18)
    $('#promotion_info_' + rt_id).hide();
    if (2 == type) {
    	$('#promotion_info_' + rt_id).text('連住' + rule_value + '天(含)以上，可享連住優惠');
    	$('#promotion_info_' + rt_id).show();
    }
    else if (3 == type) {
    	$('#promotion_info_' + rt_id).text('提早' + rule_value + '天前預訂，可享早鳥優惠');
    	$('#promotion_info_' + rt_id).show();
    }
//     else if (4 == type) {
//     	$('#promotion_info_' + rt_id).text('享當日最後一分鐘優惠');
//     	$('#promotion_info_' + rt_id).show();
//     }

    // 清除數量
    $('#addThisAmount_' + rt_id + ' option').each(function(idx){
        if (idx > 0) $(this).remove().end();
    });

    // 設定可售的資格數
    for (var i = 1; i <= sell_qty; i++) {
    	$('#addThisAmount_' + rt_id).append($('<option/>', {value:i, text:i+$('#unit_type_'+rt_id).val()}));
    }
}

function addThis(rt_id) {
	// 取得選取數量
	var select_qty = $('#addThisAmount_' + rt_id).val();

    if ('' == select_qty) {
        alert('請先選擇數量');
        return;
    }

	// 取得剩餘空房數
	var empty_room_qty = $('#empty_room_qty_' + rt_id).val();
	var p_id = $('#selectedPromotionId_' + rt_id).val();
	var rt_name = $('#rt_name_' + rt_id).val();
	var unit_type = $('#unit_type_' + rt_id).val();

    // 住宿限制
    var dateDiff = <?= $dateDiff ?>;
    var limit = $('#limit_' + rt_id).val();
    var limitDayString = $('#limitDayString_' + rt_id).val();
    if(limit != ''){
		if(limit > dateDiff){
			alert("此"+rt_name+"於 "+limitDayString+" 僅接受住宿 "+limit+"晚 以上的訂單，請重新選擇您的入住日期、更換其他房型或選擇其他旅宿！");
			return;
		}
    }

	// 取得選取的promotion
	var type = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="type"]').val();
	var rule_value = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="rule_value"]').val();
	var name = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="name"]').val();
	var qty = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="qty"]').val(); // 優惠資格數量
	var amount = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="amount"]').val();
	var haveBreakfast = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="haveBreakfast"]').val();
	var allowCancel = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="allowCancel"]').val();
	var promotion_type = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="promotion_type"]').val();
	var haveBreakfastText = '不含早餐';
	var allowCancelText = '免費取消';
	if ('1' == haveBreakfast) haveBreakfastText = '含早餐';
	if ('2' == allowCancel) allowCancelText = '不可取消';

    // 扣除可售優惠資格數及空房數
    qty -= select_qty;
    $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="qty"]').val(qty);
    empty_room_qty -= select_qty;
    $('#empty_room_qty_' + rt_id).val(empty_room_qty);
// alert($('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="qty"]').val());
// alert($('#empty_room_qty_' + rt_id).val());

    // 調整顯示可售優惠資格數
    $('[rtId="' + rt_id + '"][columnName="qty"]').each(function(){
    	// 可售資格數不可大於房型的空房數
    	var sell_qty = $(this).val();
    	if (sell_qty > empty_room_qty) sell_qty = empty_room_qty;
        $('[pId="' + $(this).attr('pId') + '"][rtId="' + rt_id + '"][name="show_qty"]').text(sell_qty);
    });

    // 更新已選擇的優惠
    selectPromotion(p_id, rt_id);

    // 目前只有一種 promotion type 就是 1:促銷模組
    var type = promotion_type;

	// 取得 template & 設定內容
	var temp = $('#selectedRoomTemplate').html();
	temp = temp.replace('#selected_room_type_id#', rt_id);
    temp = temp.replace('#selected_qty#', select_qty);
    temp = temp.replace('#selected_promotion_type#', type);
    temp = temp.replace('#selected_promotion_id#', p_id);
    temp = temp.replace('#selected_allow_cancel#', allowCancel);
    temp = temp.replace('#selected_promotion_name#', name);
    temp = temp.replace('#rt_name#', rt_name);
    temp = temp.replace('#show_name#', name);
    temp = temp.replace('#qty#', select_qty);
    temp = temp.replace('#unit_type#', unit_type);
    temp = temp.replace('#amount#', numberFormat(amount * select_qty));
    temp = temp.replace('#p_id#', p_id);
    temp = temp.replace('#rt_id#', rt_id);
    temp = temp.replace('#qty#', select_qty);

    // 加總選擇的房數
    var selectedRoomCount = $('#selectedRoomCount').val();
    if ('0' == selectedRoomCount) selectedRoomCount = select_qty;
    else selectedRoomCount = parseInt(selectedRoomCount) + parseInt(select_qty);
    $('#selectedRoomCount').val(selectedRoomCount);
    $('#roomCount').text($('#selectedRoomCount').val());

	// 加至 roomSelected 清單
    $('#roomSelected').append(temp);
    $('#sidebar').show();

    // 如果購物車長度大於房型列表，調整房型列表高度 by Steak
    if($('#sidebar').height() > $('#roomList').height()){
    	 $("#roomList").height($('#sidebar').height()+50);
    }
}

function removeThis(p_id, rt_id, remove_qty) {
	var selectedPromotionId = $('#selectedPromotionId_' + rt_id).val(); // 目前選定的優惠
	var selectedRoomCount = $('#selectedRoomCount').val();
    var empty_room_qty = $('#empty_room_qty_' + rt_id).val();
    var qty = $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="qty"]').val(); // 優惠資格數量

    // 加回房型的剩餘空房數 & 優惠資格數
	empty_room_qty = parseInt(empty_room_qty) + parseInt(remove_qty);
	$('#empty_room_qty_' + rt_id).val(empty_room_qty);
// alert($('#empty_room_qty_' + rt_id).val());
    qty = parseInt(qty) + parseInt(remove_qty);
    $('[pId="' + p_id + '"][rtId="' + rt_id + '"][columnName="qty"]').val(qty);

    // 調整顯示可售優惠資格數
    $('[rtId="' + rt_id + '"][columnName="qty"]').each(function(){
    	var sell_qty = $(this).val();
    	if (sell_qty > empty_room_qty) sell_qty = empty_room_qty;
        $('[pId="' + $(this).attr('pId') + '"][rtId="' + rt_id + '"][name="show_qty"]').text(sell_qty);
    });

    // 更新已選擇的優惠
    selectPromotion(selectedPromotionId, rt_id);


    // 扣除選擇的房數
    var selectedRoomCount = $('#selectedRoomCount').val();
    if ('0' != selectedRoomCount) selectedRoomCount = parseInt(selectedRoomCount) - parseInt(remove_qty);
    if (selectedRoomCount < 0) selectedRoomCount = 0;
    $('#selectedRoomCount').val(selectedRoomCount);
    $('#roomCount').text($('#selectedRoomCount').val());

    // 若 roomSelected 清單已無項目，則隱藏
    if (selectedRoomCount <= 0) $('#sidebar').hide();
}

function startBooking() {
	var tmp_str = '';
    $('form [columnName="selected_room_type_id"]').each(function(){
        if (tmp_str.length > 0) tmp_str += ',';
    	var room_type_id = $(this).val();
    	var qty = $(this).next().val();
    	var promotion_type = $(this).next().next().val();
    	var promotion_id = $(this).next().next().next().val();
    	var allow_cancel = $(this).next().next().next().next().val(); // 1:依旅宿規定取消, 2:不可取消
    	var promotion_name = $(this).next().next().next().next().next().val();
    	var last_cancel_days = '<?php echo $last_cancel_days?>';
        if ('' == promotion_id) {
        	promotion_id = 0;
        	promotion_type = 0;
        }
        if (2 == allow_cancel) {
        	last_cancel_days = 0;
        }
        tmp_str += room_type_id + '_' + qty + '_' + promotion_type + '_' + promotion_id + '_' + allow_cancel + '_' + last_cancel_days + '_' + promotion_name;
    });

    $('#selectRoom').val(tmp_str);
	alert('★敬請依基本入住人數入住，若有超出之人數，請務必先在付款步驟頁填寫備註，並事先與業主聯繫加人/加床事宜，現場支付現金給業主，否則業主有權不予超出的人數入住。此部分Tripitta平台不代為處理。\n★若本房型不可加人，請選其他房型，或另加訂一間房。');
    $('#form1').submit();
}

function numberFormat(number, c, d, t) {
	var n = number, c = isNaN(c = Math.abs(c)) ? 0 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
	return s + (j ? i.substr(0, j) + d : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? t + Math.abs(n - i).toFixed(c).slice(2) : "");
};

$(document).click(function(){
	if (is_promotion_event) {
		is_promotion_event = false;
	}
	else {
	    $('.optionGroup').hide();
	}
});
// end 控制房型優惠的各項動作
function dateCompare2(date1, date2) {
	if ($.trim($('#' + date1).val()) == '入住日期') $('#' + date1).val('');
	if ($.trim($('#' + date2).val()) == '退房日期') $('#' + date2).val('');
	if ($('#' + date1).val() != '') {
		d1 = parseISO8601($('#' + date1).val());
		var t = new Date();
		//var today = new Date(t.getFullYear(), t.getMonth(), t.getDate());
		var today = parseISO8601('<?= date('Y-m-d') ?>');
		if (d1 < today) {
			$('#' + date1).val('');
			alert("入住日期不能小於今日!!");
		}
	}
	//if ($('#' + date1).val() != '' && $('#' + date2).val() != '') {
		d1 = parseISO8601($('#' + date1).val() );
		var t = new Date();
		//var today = new Date(t.getFullYear(), t.getMonth(), t.getDate());
		var today = parseISO8601('<?= date('Y-m-d') ?>');
		d2 = parseISO8601($('#' + date2).val() );
		if ((d1 - d2) >= 0 || $('#' + date2).val()=='' ) {
			date_tmp=parseISO8601(($('#' + date1).val()));
			date_tmp.setDate(date_tmp.getDate()+1);
			nextDay=date_tmp.getFullYear()+'-'+((date_tmp.getMonth()+1)<10?'0'+(date_tmp.getMonth()+1):(date_tmp.getMonth()+1))+'-'+(date_tmp.getDate()<10?'0'+date_tmp.getDate():date_tmp.getDate());
			$('#' + date2).val(nextDay);
		}
	//}
		if ($('#' + date1).val() == '') $('#' + date1).val('入住日期');
		if ($('#' + date2).val() == '') $('#' + date2).val('退房日期');
}
function dateCompare3(date1, date2) {
	if ($.trim($('#' + date1).val()) == '入住日期') $('#' + date1).val('');
	if ($.trim($('#' + date2).val()) == '退房日期') $('#' + date2).val('');
	//if ($('#' + date1).val() != '' && $('#' + date2).val() != '') {
		d1 = parseISO8601($('#' + date1).val() );
		var t = new Date();
		//var today = new Date(t.getFullYear(), t.getMonth(), t.getDate());
		var today = parseISO8601('<?= date('Y-m-d') ?>');
		d2 = parseISO8601($('#' + date2).val() );
		if ((d1 - d2) >= 0 || $.trim($('#' + date1).val())=='' ) {
			date_tmp=parseISO8601(($('#' + date2).val()));
			date_tmp.setDate(date_tmp.getDate()-1);
			lastDay=date_tmp.getFullYear()+'-'+((date_tmp.getMonth()+1)<10?'0'+(date_tmp.getMonth()+1):(date_tmp.getMonth()+1))+'-'+(date_tmp.getDate()<10?'0'+date_tmp.getDate():date_tmp.getDate());
			$('#' + date1).val(lastDay);
		}
	//}
	if ($('#' + date1).val() != '') {
		d1 = parseISO8601($('#' + date1).val());
		var t = new Date();
		//var today = new Date(t.getFullYear(), t.getMonth(), t.getDate());
		var today = parseISO8601('<?= date('Y-m-d') ?>');
		if (d1 < today) {
			$('#' + date1).val('');
			alert("入住日期不能小於今日!!");
		}
	}
	if ($('#' + date1).val() == '') $('#' + date1).val('入住日期');
	if ($('#' + date2).val() == '') $('#' + date2).val('退房日期');
}
function parseISO8601(dateStringInRange) {  //解決IE8
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
function open_trip_advisor_review(ta_id) {
	window.open('http://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=' + ta_id + '&partnerId=CB56EED944AF4459B7E92BBF9B292AC6&lang=zh_TW&allowMobile&display=true', 'trip_advisor', 'width=600, location=0, menubar=0, resizable=0, scrollbars=1, status=0, titlebar=0, toolbar=0');
}
</script>
</head>
<body>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="orderIndex-container">
		<div class="h1">
			<h1 class="hotelName"><?= $home_stay_row['name'] ?></h1>
			<?php if(!empty($home_stay_row['certificate1'])) { ?><span class="img-homestay" data-homestay="合法民宿登記第<?= $certificateRow[0]['hsc_number'] ?>號"></span><?php } ?>
			<!--  <span class="img-hotel" data-homestay="合法民宿登記第720號"></span> 目前沒這個-->
			<?php if(!empty($home_stay_row['certificate3'])) { ?><span class="img-sgs" data-sgs="國際SGS 3S民宿"></span><?php } ?>
			<?php if(!empty($home_stay_row['certificate2'])) { ?> <span class="img-taiwanhost" data-taiwanhost="好客民宿"></span><?php } ?>
		</div>
		<div class="h2">
			<h5 class="address"><?= $home_stay_row['address'] ?></h5>
			<div class="seeMap" onclick="goDiv('map')">
				查看地圖
				<i class="img-viewmap"></i>
			</div>
		</div>
		<div class="tags">
		<?php foreach ($home_stay_row['tags'] as $v) {?>
			<h5><?= $v ?></h5>
        <?php } ?>
		</div>
		<!--民宿介紹_照片簡介-->
		<div class="slider" style="height: 404px;">
			<div class="flexslider">
			      <ul class="slides">
	            	<li><a href="#"><img src="<?php echo $home_stay_photo_row['img_url'], $home_stay_photo_row['main_photo']?>" alt="<?= $home_stay_row['name'] ?>" onerror="javascript:this.src='../../images/no-pic.jpg';"  /></a></li>
					<?
						$idx = 0;
						for ($i = 0; $i < count($home_stay_photo_row['photos']); $i++) {
							$p = $home_stay_photo_row['photos'][$i];
							if ($p['p_reference_id'] == $hs_id) {
								if($p["p_id"] == $home_stay_row['main_photo']){
									$idx++;
									continue;
								}
								if($idx >= 5){
									break;
								}
								$img = $home_stay_photo_row['img_url'] . 'home_stay/' . $hs_id . '/' . $p['p_id'] . "_big.jpg";
					?>
							<li><a href="#"><img src="<?= $img ?>" alt="<?= $home_stay_row['name'] ?>" onerror="javascript:this.src='../../images/no-pic.jpg';"></a></li>
					<?
								$idx++;
							}
						}

					?>
			      </ul>
			</div>
			<article class="tripWrap">
				<div id="btn_add_remove_favorite" class="collection img-collect" title="收藏" data-type="10" data-id="<?= $hs_id ?>" >
					<?php if($favorite_class == "fa-heart-o"){ ?>
						<i class="fa <?= $favorite_class ?>" id="favorite"></i>
					<?php }else{ ?>
						<i class="fa <?= $favorite_class ?>" id="favorite"></i>
					<?php } ?>
					<input type="hidden" id="user_serial_id" value="<?php echo $serialId ?>">
				</div>
				<div class="tripInfo">
					<?php if(!empty($home_stay_row['ta_row'])){ ?>
					<a href="javascript:open_trip_advisor_review(<?= $home_stay_row['ta_row']['tari_id'] ?>)" class="tripadvisorLogo">
						<span style="background-image:url('/../../web/img/tripadvisor_a.png');padding-top: 2px;padding-left: 2px;background-repeat: no-repeat; width: 124px;  height: 24px;  display: block;  "><img src="https://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $home_stay_row['ta_row']['tari_average_rating'] ?>-33123-4.gif" style="width:120px;height:20px;"/></span>
						<h4>
							<span class="count"><?= $home_stay_row['ta_row']['tari_review_count'] ?></span>
							<span>則評論</span>
						</h4>
					</a>
					<?php } ?>
					<h4 class="wifiWrap">
						<? if($home_stay_row['wifi'] == 1){ ?><span class="wifi"><i class="fa fa-wifi"></i></span><?php } ?>
						<? if($home_stay_row['breakfast'] == 1){ ?><span class="cutlery"><i class="fa fa-cutlery"></i></span><?php } ?>
						<? if($home_stay_row['pet'] == 2){ ?><span class="paw"><i class="fa fa-paw"></i></span><?php } ?>
					</h4>
					<div class="intro" style="text-align: justify;">
						<div class="introContent">
							<span id="hsIntro1" >
								<?php echo mb_substr($home_stay_row['description'], 0, 80, 'UTF-8')?>
							</span>
							<span id="hsIntro2" style="display:none;"><?= $home_stay_row['description'] ?></span>
							<a href="javascript:showIntro()" class="img-more002"></a>
						</div>
						<div class="goCheckOut" onclick="goDiv('checkInExplain')">訂房說明</div>
					</div>
				</div>
			</article>
			<div class="img-buy-expensive"></div>
			<div class="img-freecancel"></div>
		</div>
		<!--end 民宿介紹_照片簡介-->
		<!--遊記分享 -->
		<?php if(!empty($home_stay_plan)) { ?>
		<div class="shareWrap">
			<div class="blogList">
				<h5>遊記分享</h5>
				<ul>
				<?php foreach ($home_stay_plan as $k => $hsp) {
						if($k == 4) break;
				?>
					<li>
						<a href="javascript:void(0)"><?= $hsp['tp_title']?></a>
						<h4><?= $hsp['a_title']?></h4>
					</li>
				<?php } ?>
				</ul>
			</div>
			<?php if(count($home_stay_plan) > 4) {?><a href="javascript:void(0)" class="shareMore">更多</a><?php } ?>
		</div>
		<?php } ?>
		<!--END 遊記分享 -->
	</div>



	<form id="form1" method="post" action="/web/pages/booking/check_room.php">
	<div class="orderIndex-container2">
		<!-- 中間搜尋bar -->
		<article class="FilterCheckIn">
			<div class="filterWrap">
				<div class="icon"><label for="beginDate"><i class="img-day"></i></label></div><input type="text" id="beginDate" name="beginDate" placeholder="入住日期" class="checkIn" value="<?= $beginDate ?>" />
				<div class="icon"><label for="endDate"><i class="img-day"></i></label></div><input type="text" id="endDate" name="endDate" placeholder="退房日期" class="checkOut" value="<?= $endDate ?>" />
				<div class="icon">
					<i class="img-member-user"></i>
				</div>
				<div class="peopleSelect">
					<select class="people" id="roomType" name="roroomType">
						<?php for($i=1;$i<=10;$i++){
							$select = '';
							if($i == $roomType) $select = 'selected';
						?>
						<option value="<?= $i ?>" <?= $select ?>><?= $i ?>人</option>
						<?php } ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<div class="icon">
					<i class="img-room"></i>
				</div>
				<div class="roomSelect">
					<select class="room" id="roomQuantity" name="roomQuantity">
						<?php for($i=1;$i<=10;$i++){
							$select2 = '';
							if($i == $roomQuantity) $select2 = 'selected';
						?>
						<option value="<?= $i ?>" <?= $select2 ?>><?= $i ?>間</option>
						<?php } ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<button type="button" class="filterReset" onclick="chkData()" >條件重設</button>
			</div>
		</article>
		<!--end 中間搜尋bar -->
		<article class="roomList">





		    <!-- John -->
		    <input type="hidden" id="last_cancel_days" value="<?php echo $last_cancel_days?>">
		    <input type="hidden" id="selectedRoomCount" value="0">
		    <input type="hidden" id="selectRoom" name="selectRoom">
		    <input type="hidden" name="homeStayId" value="<?= $hs_id ?>">
		    <input type="hidden" name="area_code" value="<?= $areaCode ?>">

			<!--買貴退三倍  -->
			<aside class="sidebar" id="sidebar" style="display: none;">
				<div class="roomSelected" id="roomSelected">
				</div>
				<div class="nightCountWrap">
					<p>
						已加<span class="roomCount" id="roomCount">0</span>間
					</p>
					<p>
						/ 共住<span class="nightCount"><?= $dateDiff ?></span>晚
					</p>
				</div>
				<a href="javascript:startBooking()" class="orderBtn">開始訂房</a>
				<a href="/web/pages/about/price_guarantee.php" target="_blank" class="priceTriple">
					<i class="img-buy-expensive-icon"></i>
					<span>買貴退三倍差價</span>
				</a>
			</aside>

			<div id="roomList">
			<?php
			if(!empty($room_type_row)){
			foreach ($room_type_row as $idx1 => $rt){
			    $idx1 ++;

			    $rt_id = $rt['id'];
			    $empty_room_qty = $rt['sellRooms'];
			    $unit_type = $rt['unit_type'];
			//     if ('人' == $unit_type) $unit_type = '床';

			    $room_type_sell_price = 0;
			    if (!empty($room_type_sell_price)) $room_type_sell_price = $room_type_sell_price_list[$rt_id];
			    $total_sell_price = 0;
			    if (!empty($room_type_sell_price)) {
			        foreach ($room_type_sell_price as $sell_price) {
			            $total_sell_price += intval($sell_price['price']);
			        }
			    }
			// printmsg($total_sell_price);
			    $total_setting_price = 0;
			    if(!empty($room_type_setting_price_list[$rt_id])) $total_setting_price = $room_type_setting_price_list[$rt_id] * $dateDiff;
			?>
			<!-- popup room info -->
			<aside class="popupRoomInfo" id="roomInfo<?= $rt_id?>">
				<div class="closeBtn">
					<i class="img-member-close" onclick="closeRoomInfo(<?= $rt_id?>)"></i>
				</div>
				<div class="popBtn">
					<div onclick="clickPrev2(<?= (count($rt['room_type_photos']) >= 6) ? 6 : count($rt['room_type_photos']) ?>)"><i class="fa fa-angle-left"></i></div>
					<div onclick="clicknext2(<?= (count($rt['room_type_photos']) >= 6) ? 6 : count($rt['room_type_photos']) ?>)"><i class="fa fa-angle-right"></i></div>
				</div>
				<div id="banner2" class="swiper-wrapper"><!-- 轮播部分 -->
		            <ul class="imgList2"><!-- 图片部分 -->
		            	<li><img src="<?= $rt['room_type_main_photo'] ?>" alt=""></li>
						<?
							$idx = 0;
							for ($i = 0; $i < count($rt['room_type_photos']); $i++) {
								$p = $rt['room_type_photos'][$i];
								if ($p['p_reference_id'] == $rt_id) {
									if($p["p_id"] == $rt['main_photo']){
										$idx++;
										continue;
									}
									if($idx > 6){
										break;
									}
									$img = get_config_image_url() . 'room_type/' . $rt_id . '/' . $p['p_id'] . '_big.jpg';
						?>
								<li><img alt="<?= $rt['name'] ?>" src="<?= $img ?>"></li>
						<?
									$idx++;
								}
							}

						?>
		            </ul>
				</div>
				<div class="summary">
					<h5><?= $rt['name'] ?></h5>
					<div class="roomIntro">
						<h4>房間介紹</h4>
						<p><?= nl2br($rt['desc'])?></p>
					</div>
					<div class="roomFacility">
						<h4>房間設備</h4>
						<ul>
					    <?php
						// f_type 0:公共設施, 1:無障礙設施, 2:服務項目, 3:床型, 4:房間設備
						// f_zone 1:室內, 2:室外, 3:房間 , 4:浴室
						for ($i = 0; $i < count($rt['room_type_facilitys']); $i++) {
							if ($rt['room_type_facilitys'][$i]['f_type'] == 4 && $rt['room_type_facilitys'][$i]['f_zone'] == 3) {
								echo '<li><i class="fa fa-check"></i><span>'. $rt['room_type_facilitys'][$i]['f_name']. '</span></li>';
							}
						}
						?>
						</ul>
					</div>
					<div class="bathFacility">
						<h4>浴室設備</h4>
						<ul>
					    <?php
						// f_type 0:公共設施, 1:無障礙設施, 2:服務項目, 3:床型, 4:房間設備
						// f_zone 1:室內, 2:室外, 3:房間 , 4:浴室
						for ($i = 0; $i < count($rt['room_type_facilitys']); $i++) {
							if ($rt['room_type_facilitys'][$i]['f_type'] == 4 && $rt['room_type_facilitys'][$i]['f_zone'] == 4) {
								echo '<li><i class="fa fa-check"></i><span>'. $rt['room_type_facilitys'][$i]['f_name']. '</span></li>';
							}
						}
						?>
						</ul>
					</div>
					<div class="others">
						<h4>其他</h4>
						<ul>
							<li>
								<em>★</em>
								<span>餐點：<?= $rt['haveBreakfast'] ?  '附' : '不附'?>早餐。</span>
							</li>
							<li>
								<em>★</em>
								<span>床數/床型：
								<?php
									$ary = array();
									$bedList = $rt['room_type_beds'];
									for ($i = 0; $i < count($bedList); $i++) {
										$bedName = $bedList[$i]['f_name'];
										if (empty($ary[$bedName])) {
											$ary[$bedName] = 1;
										}
										else {
											$ary[$bedName] += 1;
										}
									}
									if (count($ary) > 0) {
										foreach ($ary as $key => $value) {
											echo '<font color="red">', $value, '</font>', ' 張 ', '<font color="red">', $key, '</font>';
										}
									}
								?>
								</span>
							</li>
							<li>
								<em>★</em>
								<span>基本可入住人數：<?= $rt['room_type'] ?>人。</span>
							</li>
							<li>
								<em>★</em>
								<span>敬請依基本入住人數入住，若有超出之人數，請務必先在付款步驟頁填寫備註，並事先與業主聯繫加人/加床事宜，現場支付現金給業主，否則業主有權不予超出的人數入住。此部分Tripitta平台不代為處理。</span>
							</li>
							<li>
								<em>★</em>
								<span>若本房型不可加人，請選其他房型，或另加訂一間房。</span>
							</li>
						</ul>
					</div>
				</div>
			</aside>


			<!-- room list here -->
			<section id="roomType_<?= $idx1 ?>" <?= ($idx1 > 5)? 'style=" display: none;"':'' ?>>
			    <input type="hidden" id="empty_room_qty_<?php echo $rt_id?>" value="<?php echo $empty_room_qty?>">
			    <input type="hidden" id="rt_name_<?php echo $rt_id?>" value="<?php echo $rt['name']?>">
			    <input type="hidden" id="unit_type_<?php echo $rt_id?>" value="<?php echo $unit_type?>">
			    <input type="hidden" id="limit_<?php echo $rt_id?>" value="<?= !empty($rt['limit']) ? $rt['limit']['limitDay'] : '' ?>">
				<input type="hidden" id="limitDayString_<?php echo $rt_id?>" value="<?= !empty($rt['limit']) ? $rt['limit']['limitDayString'] : '' ?>">

				<div class="imgWrap">
					<div class="people">
						<i class="img-people"></i>
						<span>x</span>
						<span class="peopleCount"><?= $rt['room_type'] ?></span> <!-- 沒有加人 -->
					</div>
					<img src="<?= $rt['room_type_main_photo'] ?>" alt="" onerror="javascript:this.src='/../../web/img/no-pic.jpg';">
					<div class="zoomIn" onclick="showInfo(<?= $rt_id?>)">
						<p class="text" style="cursor: pointer;">&nbsp;&nbsp;&nbsp;&nbsp;點選放大&nbsp;&nbsp;&nbsp;<br />查看更多資訊</p>
						<div class="thumbnail">
						<?
							$idx = 0;
							for ($i = 0; $i < count($rt['room_type_photos']); $i++) {
								$p = $rt['room_type_photos'][$i];
								if ($p['p_reference_id'] == $rt_id) {
									if($p["p_id"] == $rt['main_photo']){
										$idx++;
										continue;
									}
									if($idx > 6){
										break;
									}
									$img = get_config_image_url() . 'room_type/' . $rt_id . '/' . $p['p_id'] . '_big.jpg';
						?>
						<img alt="<?= $rt['name'] ?>" src="<?= $img ?>">
						<?
									$idx++;
								}
							}
						?>
						</div>
					</div>
				</div>



				<div class="roomInfo">
					<div class="left">
						<h5 class="subject"><?= $rt['name']?></h5>
						<div class="selectWrap">
							<div class="titleGroup">
								<h4>請選擇優惠方式</h4>
								<h4 class="origiCostGroup">
									<del class="currency"><?php echo $currency_code?></del>
									<del class="cost"><?php echo number_format($total_setting_price/$exchange_rate)?></del>
								</h4>
							</div>

							<div class="selectGroup" id="promotion_selected_<?php echo $rt_id?>" onclick="toggleRoomTypePromotions(<?php echo $rt_id?>)">
							<?php
							// $empty_room_qty = 0;
							    if ($empty_room_qty == 0) {
							?>
								<!-- display this when sold out. -->
								<div class="soldOutSelected" style="display: flex;">
									<h4>
										<strong class="itemName">
											Tripitta優惠
										</strong>
										(<p class="roomRemain">
											0
										</p>
										<p><?php echo $unit_type?></p>)
									</h4>
									<h4>
										<p class="currency">
											<?php echo $currency_code?>
										</p>
										<strong class="dollar">
											<?php echo number_format($total_sell_price)?>
										</strong>
									</h4>
								</div>
								<?php
								    }
								    else {
								        $promotions = NULL;
								        if (isset($promotion_list[$rt_id])) $promotions = $promotion_list[$rt_id];
								        $room_type_promotions = $tripitta_homestay_service->cal_promotion($promotions, $room_type_sell_price_list[$rt_id], $empty_room_qty, $exchange_rate);

								        $projects = null;
								        if (isset($project_list[$rt_id])) $projects = $project_list[$rt_id];
								        $room_type_projects = $tripitta_homestay_service->cal_project($projects, $room_type_sell_price_list[$rt_id], $hs_id, $empty_room_qty, $exchange_rate);

								        // 預設最便宜的
								        $default_promotion = null;
								        $idx = 0;
								        foreach ($room_type_promotions as $promotion) {
								            if (0 == $idx) $default_promotion = $promotion;
								            if (intval($promotion['amount']) < intval($default_promotion['amount'])) $default_promotion = $promotion;
								            $idx++;
								        }
								        foreach ($room_type_projects as $projects) {
								        	if(intval($projects['amount'] == 0)) continue;
								        	if (intval($projects['amount']) < intval($default_promotion['amount'])) $default_promotion = $projects;
								        }
								?>
								<div class="selected">
								    <input type="hidden" id="selectedPromotionId_<?php echo $rt_id?>">
									<h4>
										<strong class="itemName" id="selectedPromotionName_<?php echo $rt_id?>">

										</strong>
										(<p class="roomRemain" id="selectedPromotionQty_<?php echo $rt_id?>">

										</p>
										<p><?php echo $unit_type?></p>)
									</h4>
									<h4>
										<p class="currency">
											<?php echo $currency_code?>
										</p>
										<strong class="dollar" id="selectedPromotionAmount_<?php echo $rt_id?>">

										</strong>
										<i class="fa fa-angle-down"></i>
									</h4>
								</div>
								<script>$(function(){selectPromotion('<?php echo $default_promotion['p_id']?>',<?php echo $rt_id?>);});</script>

								<!-- option hidden -->
								<div class="optionGroup" id="promotion_option_<?php echo $rt_id?>">
								<?php
								        foreach ($room_type_promotions as $promotion) {
								            $p_id = $promotion['p_id'];
								?>
									<div class="optionSub-tripitta" onclick="selectPromotion('<?php echo $p_id?>',<?php echo $rt_id?>);">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="id" value="<?php echo $p_id?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="type" value="<?php echo $promotion['p_type']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="rule_value" value="<?php echo $promotion['p_rule_value']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="name" value="<?php echo $promotion['show_name']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="qty" value="<?php echo $promotion['sell_qty']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="amount" value="<?php echo $promotion['amount']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="allowCancel" value="<?php echo $promotion['p_allow_cancel']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="haveBreakfast" value="<?php echo $promotion['have_breakfast']?>">
										<input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="promotion_type" value="<?php echo $promotion['promotion_type']?>">

										<h4>
											<strong class="itemName">
												<?php echo $promotion['show_name']?>
											</strong>
											(<p class="roomRemain" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" name="show_qty">
												<?php echo $promotion['sell_qty']?>
											</p>
											<p><?php echo $unit_type?></p>)
										</h4>
										<h4>
											<p class="currency">
												<?php echo $currency_code?>
											</p>
											<strong class="dollar">
												<?php echo number_format($promotion['amount'])?>
											</strong>
										</h4>
									</div>
								<?php
								        }
								?>
								<?php
										// 銀行專案
								        foreach ($room_type_projects as $promotion) {
								        	// 0元不顯示
								        	if($promotion['amount'] == 0) continue;

								            $p_id = $promotion['p_id'];
								?>
									<div class="optionSub-tripitta" onclick="selectPromotion('<?php echo $p_id?>',<?php echo $rt_id?>);">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="id" value="<?php echo $p_id?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="type" value="<?php echo $promotion['p_type']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="rule_value" value="<?php echo $promotion['p_rule_value']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="name" value="<?php echo $promotion['show_name']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="qty" value="<?php echo $promotion['sell_qty']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="amount" value="<?php echo $promotion['amount']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="allowCancel" value="<?php echo $promotion['p_allow_cancel']?>">
									    <input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="haveBreakfast" value="<?php echo $promotion['have_breakfast']?>">
										<input type="hidden" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" columnName="promotion_type" value="<?php echo $promotion['promotion_type']?>">

										<h4>
											<strong class="itemName">
												<?php echo $promotion['show_name']?>
											</strong>
											(<p class="roomRemain" pId="<?php echo $p_id?>" rtId="<?php echo $rt_id?>" name="show_qty">
												<?php echo $promotion['sell_qty']?>
											</p>
											<p><?php echo $unit_type?></p>)
										</h4>
										<h4>
											<p class="currency">
												<?php echo $currency_code?>
											</p>
											<strong class="dollar">
												<?php echo number_format($promotion['amount'])?>
											</strong>
										</h4>
									</div>
								<?php
								        }
								?>
								</div><!-- <div class="optionGroup" -->
							<?php
							    }
							?>
							</div><!-- <div class="selectGroup" -->

							<h5 class="cancelNotAllow" id="promotion_info_<?php echo $rt_id?>">
								不可取消房型，請單筆訂購
							</h5>
						</div>
					</div><!-- <div class="left"> -->

					<div class="right">
						<ul>
							<li class="breakfast" id="selectedPromotionHaveBreakfast_<?php echo $rt_id?>"><?= $rt['haveBreakfast'] ?  '含' : '不含'?>早餐</li>
							<li class="cancelFree" id="selectedPromotionAllowCancel_<?php echo $rt_id?>" data-cancelFree="<?php echo $last_cancel_days?>天前免費取消">免費取消</li>
						</ul>
					</div>
				</div><!-- <div class="roomInfo"> -->



				<div class="remainWrap">
				<?php
				    if ($empty_room_qty > 0) {
				?>
					<div class="remainGroup">
						<div class="sel">
							<select class="roomAmount" id="addThisAmount_<?php echo $rt_id?>">
							<option value="" selected>選<?= $unit_type ?>數</option>
								<?php
// 									for ($j = 1; $j <= $rt['sellRooms']; $j++) {
// 										echo '<option value="', $j, '">'.$j;
// 										echo '</option>';
// 									}
								?>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
						<button class="addThis" type="button" onclick="addThis(<?php echo $rt_id?>)">加入</button>
					</div>
					<?php
					    }
					    else {
					?>
					<div class="soldOutGroup" style="display:flex;">
						<button type="button">售完</button>
					</div>
				<?php
				    }
				?>
				</div>
			</section>
	<?php
		}
	}else{
	?>
	<div align="center" style="margin-top: -26px;padding-bottom: 15px;font-size: 20px;">很抱歉，目前無符合您所設定條件的房間。</div>
	<?php } ?>
	</div>


		</article>
		<?php if (count($room_type_row) > 5){?>
		<div class="getRoomMore">
			<span id="moreText">點我看更多房型</span>
			<div class="circle">
				<i id="showRoom" class="fa fa-angle-down" onclick="showMoreRoomType(1);"></i>
			</div>
		</div>
		<?php } ?>
		<div class="map" style="z-index:6;">
			<div id="map" style="width: 100%; height: 700px;"></div>
			<div class="mapControl">
				<section class="part1">
					<p class="hasRoom">
						<i class="img-bnb-s"></i>
						<span>旅宿 (有房)</span>
					</p>
					<p class="noRoom">
						<i class="img-bnb-offs"></i>
						<span>旅宿 (無房)</span>
					</p>
				</section>
				<section class="part2">
					<label for="activity" class="activity">
						<i class="img-activity-s"></i>
						<span>活動</span>
						<input type="checkbox" id="activity" onclick="triMarker(0,'activity')">
					</label>
					<label for="scenic" class="scenic">
						<i class="img-viewpoint-s"></i>
						<span>景點</span>
						<input type="checkbox" id="scenic" onclick="triMarker(0,'scenic')">
					</label>
					<label for="food" class="food">
						<i class="img-food-s"></i>
						<span>美食</span>
						<input type="checkbox" id="food" onclick="triMarker(0,'food')">
					</label>
					<label for="souvenir" class="souvenir">
						<i class="img-gift-s"></i>
						<span>伴手禮</span>
						<input type="checkbox" id="souvenir" onclick="triMarker(0,'souvenir')">
					</label>
				</section>
			</div>
		</div>

		<div class="explain">
			<div class="checkInExplain" id="checkInExplain">
				<h4>入住、退房說明</h4>
				<ul>
					<li>
						<em>★</em>
						<span>入住時間（Check-in）：<?= $home_stay_rule_row['checkInTime']?>後，退房時間（Check-out）：<?= $home_stay_rule_row['checkOutTime']?>前</span>
					</li>
					<li>
						<em>★</em>
						<span>最晚入住時間：
						<? if(empty($home_stay_rule_row['checkKeepTime'])){ ?>
							不限定。
						<? }else{ ?>
							請務必於 <?= $home_stay_rule_row['checkKeepTime'] ?> 以前辦理入住手續，若因行程可能延誤，請務必先電話聯絡業者，並告知業者正確的入住時間，若未告知則會視同當日未入住，並不退還該日之住房費用。
						<? } ?>
						 </span>
					</li>
					<li>
						<em>★</em>
						<span>本民宿業者可提供之入住付款憑証：<?= $home_stay_rule_row['certificateStr'] ?>。</span>
					</li>
					<li>
						<em>★</em>
						<span>本網站僅協助業者訂房收款事宜，僅為代收代付系統，若您需要開立入住收據，請於入住時，可向業者要求直接開立入住憑証。</span>
					</li>
				</ul>
			</div>
			<div class="facility">
				<h4>設施</h4>
				<div class="public">
					<h5>公共設施</h5>
					<ul>
					<?php
					for ($i = 0; $i < count($home_stay_facility); $i++) {
						if ($home_stay_facility[$i]['f_type'] == 0){
							$name = $home_stay_facility[$i]['f_name'];
					?>
							<li><i class="fa fa-check"></i><?= $name ?><?php echo ($home_stay_facility[$i]['f_is_charge'] == 1) ? '<span class="t12">(付費)</span>' : ''?></li>
					<?php
						}
					}
					?>
					</ul>
				</div>
				<div class="accessible">
					<h5>無障礙設施</h5>
					<ul>
					<?php
					for ($i = 0; $i < count($home_stay_facility); $i++) {
						if ($home_stay_facility[$i]['f_type'] == 1){
							$name = $home_stay_facility[$i]['f_name'];
					?>
							<li><i class="fa fa-check"></i><?= $name ?><?php echo ($home_stay_facility[$i]['f_is_charge'] == 1) ? '<span class="t12">(付費)</span>' : ''?></li>
					<?php
						}
					}
					?>
					</ul>
				</div>
				<div class="service">
					<h5>服務項目</h5>
					<ul>
					<?php
					for ($i = 0; $i < count($home_stay_facility); $i++) {
						if ($home_stay_facility[$i]['f_type'] == 2){
							$name = $home_stay_facility[$i]['f_name'];
					?>
							<li><i class="fa fa-check"></i><h3><?= $name ?></h3><?php echo ($home_stay_facility[$i]['f_is_charge'] == 1) ? '<span class="t12">(付費)</span>' : ''?></li>
					<?php
						}
					}
					?>
					</ul>
				</div>
			</div>
			<div class="notice">
				<h4>注意事項</h4>
				<ul>
					<li>
					<p><?=  str_replace(chr(13).chr(10), '<br />', $home_stay_rule_row['notice']) ?><?= (empty($home_stay_rule_row['notice'])? '':'<br>' ) ?>
					★入住人數，務必同訂房人數，若不符合規定時，業者有權現場要求補收相關差價，或不予超過之人員入住。敬請注意，此責任自負，因此問題造成之損失，本網站恕無法賠償或退還任何款項。<br></p>
					</li>
				</ul>
			</div>
		</div>
	</div>
	</form>



	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>



    <!-- sidebar內容的template -->
	<div id="selectedRoomTemplate" style="display:none;">
        <div class="section">
            <input type="hidden" columnName="selected_room_type_id" value="#selected_room_type_id#">
    	    <input type="hidden" columnName="selected_qty" value="#selected_qty#">
    	    <input type="hidden" columnName="selected_promotion_type" value="#selected_promotion_type#">
    	    <input type="hidden" columnName="selected_promotion_id" value="#selected_promotion_id#">
    	    <input type="hidden" columnName="selected_allow_cancel" value="#selected_allow_cancel#">
    	    <input type="hidden" columnName="selected_promotion_name" value="#selected_promotion_name#">
			<i class="fa fa-times"></i>
			<h3>#rt_name#</h3>
			<h4>#show_name#</h4>
			<h5>
				<span>#qty#</span>#unit_type#
				<span><?php echo $currency_code?></span>
				<span>#amount#</span>
			</h5>
			<div class="removeWrap">
				<em>
					<i class="fa fa-times" onclick="$(this).parent().parent().parent().remove().end();removeThis('#p_id#',#rt_id#,#qty#);"></i>
				</em>
			</div>
		</div>
	</div>
	<!-- end sidebar內容的template -->

<!-- 	<script src="../../js/lib/jquery/jquery.js"></script> -->
<!-- 	<script src="../../js/embed.js"></script> -->
</body>
</html>
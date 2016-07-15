<?
/**
 *  說明：
 *  作者：Cheans <cheans.huang@fullerton.com.tw>
 *  日期：2015年12月17日
 *  備註：
 *  2015-12-17 John ken反應大banner跑太快，先暫停輪播
 */
require_once __DIR__ . '/../../config.php';
?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head.php"; ?>
	<title>旅宿預訂 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.2.5/css/swiper.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.2.5/js/swiper.js"></script>
</head>
<body>
<?
$tripitta_homestay_service = new tripitta_homestay_service();
$tripitta_web_service = new tripitta_web_service();
$image_server_url = get_config_image_server();
$travel_plan_photo_path = '/travel_plan';
$author_photo_path = '/author';
if(!is_production()) {
    $travel_plan_photo_path = '/travel_plan_alpha';
    $author_photo_path = '/author_alpha';
}

$area_list = $tripitta_homestay_service->find_valid_area_for_search_by_category_and_parent_id(get_config_current_lang(), 'homestay', 0);
// 推薦旅宿類型
$recommend_type_list = $tripitta_web_service->find_recommend_type_homestay_for_booking_home();

// 行銷banner
$sell_banner_list = $tripitta_web_service->find_valid_sell_banner_for_booking_home(null);
// 移至javascript處理

?>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="travIndex-container">
		<div class="topMenuWrap">
			<div class="sliderBtn">
				<i idx="0" class="fa fa-angle-left" style="display:none"></i>
				<i idx="0" class="fa fa-angle-right" style="display:none"></i>
			</div>
			<div class="swiper-container" style="width: 100%;height: 100%;">
				<div class="swiper-wrapper">
<?
			foreach($sell_banner_list as $idx => $sell_banner_row) {
					$img = $image_server_url . '/photos/content/' . (is_production() ? 'banner' : 'banner_alpha') . '/' . $sell_banner_row["cc_content"];
?>
						<div class="swiper-slide">
							<a href="<?= $sell_banner_row["cc_link_url"] ?>" target="_blank" style="width: 100%;height: 500px;display: block; background-image: url(<?= $img ?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;"></a>
						</div>
<?
			}

?>
				</div>
			</div>
			<menu>
				<div class="filterGroup">
					<div class="locationSelect">
						<select id="areaCode" class="location">
							<option value="" selected>您想去哪裡?</option>
							<? foreach($area_list as $area_row) { ?>
							<option value="<?= $area_row["a_code"] ?>"><?= $area_row["a_name"] ?></option>
							<? } ?>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<input type="text" id="beginDate" name="beginDate" placeholder="入住日期" class="checkIn" maxlength="20">
					<input type="text" id="endDate" name="endDate" placeholder="退房日期" class="checkOut" maxlength="20">
					<div class="peopleSelect">
						<select class="people" id="roomType" name="roroomType">
							<option value="1">1人</option>
							<option value="2" selected>2人</option>
							<option value="3">3人</option>
							<option value="4">4人</option>
							<option value="5">5人</option>
							<option value="6">6人</option>
							<option value="7">7人</option>
							<option value="8">8人</option>
							<option value="9">9人</option>
							<option value="10">10人</option>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<div class="roomSelect">
						<select class="room" id="roomQuantity" name="roomQuantity">
							<option value="1">1間</option>
							<option value="2">2間</option>
							<option value="3">3間</option>
							<option value="4">4間</option>
							<option value="5">5間</option>
							<option value="6">6間</option>
							<option value="7">7間</option>
							<option value="8">8間</option>
							<option value="9">9間</option>
							<option value="10">10間</option>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<button class="filterBtn">搜尋</button>
					<p class="slogan">買貴退三倍差價</p>
				</div>
			</menu>
		</div>
		<div class="yellowBar">
			<i class="fa fa-chevron-down"></i>
		</div>
<?
if(!empty($recommend_type_list)) {
?>
		<div class="promo-mustBe">

			<h4 class="title">非住不可</h4>
			<ul class="menuBar">
<?
foreach($recommend_type_list as $idx => $recommend_type_row) {
?>
				<li <? if($idx == 0) { echo ' class="selected" '; } ?> data-idx="<?= $idx ?>" data-content-type="<?= $recommend_type_row["content_type"] ?>" data-content-code="<?= $recommend_type_row["content_code"] ?>"><?= $recommend_type_row["content_name"] ?></li>
<?
}
?>
			</ul>
			<div class="itemWrap">
				<div class="sliderBtn">
					<i class="fa fa-angle-left"></i>
					<i class="fa fa-angle-right"></i>
				</div>

				<div class="itemGroup">
				</div>
			</div>
		</div>
<?
}
?>
		<div class="sharerWrap" style="display:none">
			<div class="sliderBtn">
				<i class="fa fa-angle-left"></i>
				<i class="fa fa-angle-right"></i>
			</div>
			<div class="banner2">
				<a href="#" id="rtp_image" style="width: 100%;height: 600px;display: block;background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;"></a>
			</div>
			<div class="infoGroup">
				<a href="" class="shareBtn">體驗分享</a>
				<h1 class="title" id="rtp_link" >

				</h1>
				<h2 class="man">
					<span id="rtp_author_avatar"></span>
					<p class="mediaName"></p>
					<p class="name"></p>
				</h2>
				<h3 class="indicator">
					<p class="favorite">
						<i class="fa fa-heart"></i>
						<span id="rtp_favorite"></span>
					</p>
					<p class="viewCount">
						<i class="fa fa-eye"></i>
						<span id="rtp_view"></span>
					</p>
					<p class="viewCount">
						<i class="fa fa-calendar"></i>
						<span id="rtp_date_range"></span>
					</p>
				</h3>
			</div>
		</div>
	</div>
	<!-- hot area -->
	<div class="hotArea-container">
		<div class="wrapper">
			<h4 class="title">熱門地區</h4>
			<div class="counties">
				<div class="left">
					<i class="img-taiwan"></i>
					<ul class="county1">
						<li data-id="16" class="selected">大台北</li>
						<li data-id="15">桃園</li>
						<li data-id="124">新竹</li>
						<li data-id="12">苗栗</li>
						<li data-id="11">台中</li>
						<li data-id="3">彰化</li>
						<li data-id="58">南投</li>
						<li data-id="14">雲林</li>
						<li data-id="5">嘉義</li>
						<li data-id="9">台南</li>
						<li data-id="13">高雄</li>
						<li data-id="1,2">屏東</li>
					</ul>
					<ul class="county2">
						<li data-id="18">宜蘭</li>
						<li data-id="7">花蓮</li>
						<li data-id="19">台東</li>
						<li data-id="4">澎湖</li>
						<li data-id="8">金門</li>
						<li data-id="138">馬祖</li>
						<li data-id="6">綠島</li>
						<li data-id="10">小琉球</li>
					</ul>
				</div>
				<div class="right">
					<a href="javascript:void(0)" class="goAnyWhere">隨意到各地方看看</a>
				</div>
			</div>
			<div class="randomHotel">
			</div>
		</div>

	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>
<script>
var image_server_url = '<?= get_config_image_server() ?>';
var travel_plan_photo_path = '<?= $travel_plan_photo_path ?>';
var author_photo_path = '<?= $author_photo_path ?>';
var travel_plan_list = null;
var area_list = <?= json_encode($area_list) ?>;
var containers = [];
var swiperIdx = 0;
var seek_pos = 0;
var recomment_type_list = <?= json_encode($recommend_type_list) ?>;
var rh_seek_pos = 0;
$(function() {

	// 搜尋
	var caneldar_option = <?= json_encode(Constants::$CALENDAR_OPTIONS) ?>;
	$('#beginDate').datepicker(caneldar_option).datepicker('option', {minDate: new Date()}).change(function(){ dateCompare2('beginDate','endDate'); });
	$('#endDate').datepicker(caneldar_option).datepicker('option', {minDate: 1}).change(function(){ dateCompare3('beginDate','endDate');});
	$('.travIndex-container .filterBtn').click(function() { query_homestay() });

	// scroll to 非住不可

	$('.travIndex-container .fa-chevron-down').click(function() { scrollToConvas('.promo-mustBe') });

	// 非住不可
	$('.travIndex-container .promo-mustBe .menuBar li').each(function() {
		var idx = parseInt($(this).attr('data-idx'));
		$(this).click(function() { show_recommend_content(idx); });
	});
	$('.travIndex-container .promo-mustBe .fa-angle-right').click(function() { seek_recommend_content(1); });
	$('.travIndex-container .promo-mustBe .fa-angle-left').click(function() { seek_recommend_content(-1); });

	// 行銷Banner
	//initSwiper();

	// 行程遊記
	$('.travIndex-container .sharerWrap .fa-angle-right').click(function() { seek_travel_plan(1); });
	$('.travIndex-container .sharerWrap .fa-angle-left').click(function() { seek_travel_plan(-1); });

	// 熱門地區
	$('.goAnyWhere').click(function() {
		location.href = '/booking/' + area_list[get_rand_int(0, area_list.length - 1)].a_code + '/';
	});
	$('.hotArea-container li').each(function() {
		var area_id = $(this).attr('data-id');
		$(this).click(function() { choose_hot_area(area_id); });
	});
	load_travel_plan_for_booking_home();
	if(recomment_type_list.length > 0) {
		show_recommend_content(0);
	}
	choose_hot_area(16);
});
function check_date() {
	var beginDate = $('#beginDate').val();
	var endDate = $('#endDate').val();
	if(beginDate != '' && endDate == '') {
		var bd = new Date(beginDate);
		bd.setDate(bd.getDate()+1);
		$('#endDate').val(bd.getFullYear() + '-' + (bd.getMonth() + 1) + '-' + bd.getDate());
	} else if(beginDate == '' && endDate != '') {
		var ed = new Date(endDate);
		ed.setDate(ed.getDate()-1);
		$('#beginDate').val(ed.getFullYear() + '-' + (ed.getMonth() + 1) + '-' + ed.getDate());
	} else if(beginDate != '' && endDate != '') {

	}
}
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
function choose_hot_area(choose_area_id) {
	$('.hotArea-container li').each(function() {
		var area_id = $(this).attr('data-id');
		$(this).css("font-weight", (area_id != choose_area_id) ? "normal":"bold");
	});

	var p = {};
    p.func = 'find_homestay_for_booking_hom_by_area_ids';
    p.area_ids = choose_area_id;
    //console.log(p);
    $.post("/web/ajax/ajax.php", p, function(data) {
        //console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
			var html = '';
			for(var i=0 ; i<data.data.length ; i++) {
				html += get_hot_area_homestay_info(data.data[i], i+1);
			}
			$('.randomHotel').html(html);
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

function get_common_homestay_info(obj) {
	var hs_img = '/web/img/no-pic.jpg';
	if(obj.hs_main_photo != 0 && obj.hs_main_photo != null) {
		hs_img = image_server_url + '/photos/travel/home_stay/' + obj.hs_id + '/' + obj.hs_main_photo + '_big.jpg';
	}
	var html = '';
	html += '	<div class="detail">';
	html += '		<h4>' + obj.hs_name + '</h4>';
	html += '		<h5>';
	html += '			<p class="location">';
	html += '				<i class="fa fa-map-marker"></i>';
	html += '				<span>' + obj.a_name + '</span>';
	html += '			</p>';
	html += '			<p class="favorite">';
	html += '				<i class="fa fa-heart"></i>';
	html += '				<span>' + numberFormat(obj.hs_favorite) + '</span>';
	html += '			</p>';
	html += '			<p class="viewCount">';
	html += '				<i class="fa fa-eye"></i>';
	html += '				<span>' + numberFormat(obj.hs_review) + '</span>';
	html += '			</p>';
	html += '		</h5>';
	html += '	</div>';
	return html;
}

function get_hot_area_homestay_info(obj, item_seek) {
	var hs_img = '/web/img/no-pic.jpg';
	if(obj.hs_main_photo != 0 && obj.hs_main_photo != null) {
		hs_img = image_server_url + '/photos/travel/home_stay/' + obj.hs_id + '/' + obj.hs_main_photo + '_big.jpg';
	}
	var html = '';
	html = '<a href="/booking/' + obj.a_code + '/' + obj.hs_id + '/" class="item' + item_seek + '" style="background-image: url(' + hs_img + ');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">';
	html += get_common_homestay_info(obj);
	html += '</a>';
	return html;
}

function get_rand_int(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

function get_recommend_homestay_info(obj, idx) {
	var hs_img = '/web/img/no-pic.jpg';
	if(obj.hs_main_photo != 0 && obj.hs_main_photo != null) {
		hs_img = image_server_url + '/photos/travel/home_stay/' + obj.hs_id + '/' + obj.hs_main_photo + '_big.jpg';
	}
	var html = '';
	var class_name = ((idx == 0) ?'imgBigGroup' : 'item' + idx);
	html += '<a href="/booking/' + obj.a_code + '/' + obj.hs_id + '/" class="' + class_name + '" style="background-image: url(' + hs_img + ');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">';
	html += get_common_homestay_info(obj);
	html += '</a>';

	return html;
}

function initSwiper(){
	var swiper = $('.travIndex-container .swiper-container').swiper(
		{
			nextButton: '.travIndex-container .fa-angle-right',
	        prevButton: '.travIndex-container .fa-angle-left',
            paginationClickable: true,
            spaceBetween: 30,
            centeredSlides: true,
//             autoplay: 2500,
//             autoplayDisableOnInteraction: true,
            loop : true
		}
	);
}
function load_travel_plan_for_booking_home() {
	var p = {};
    p.func = 'find_valid_trip_plan_for_booking_home';
    //console.log(p);
	$.post("/web/ajax/ajax.php", p, function(data) {
        //console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
        	travel_plan_list = data.data;
        	if(travel_plan_list.length > 0) {
        		seek_travel_plan(0);
            	$('.sharerWrap').show();
        	}
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}
function query_homestay() {
	var areaCode = $('#areaCode').val();
	var beginDate = $('#beginDate').val();
	var endDate = $('#endDate').val();
	var roomType = parseInt($('#roomType').val());
	var roomQuantity = parseInt($('#roomQuantity').val());
	if(areaCode == '') { alert('請選擇地區'); return; }
	if(beginDate != '' && endDate != '') {
		var bd = new Date(beginDate);
		var ed = new Date(endDate);
		//console.log(bd.getTime(), ed.getTime());
		if(bd.getTime() >= ed.getTime()) { alert('入住區間錯誤 - 入住日不可大於退房日'); return; }
	}
	if(isNaN(roomType) || roomType < 0 || roomType > 10){ roomType = 2; }
	if(isNaN(roomQuantity) || roomQuantity < 0 || roomQuantity > 10){ roomQuantity = 1; }
	var url = '/booking/' + areaCode + '/?beginDate=' + encodeURIComponent(beginDate) + '&endDate=' + encodeURIComponent(endDate)  + '&roomType=' + encodeURIComponent(roomType)  + '&roomQuantity=' + encodeURIComponent(roomQuantity);
	//console.log(url);
	location.href = url;
}
function seek_recommend_content(seek) {
	if(rh_seek_pos + seek >= recomment_type_list.length) {
		rh_seek_pos = 0;
	} else if(rh_seek_pos + seek < 0) {
		rh_seek_pos = recomment_type_list.length - 1;
	} else {
		rh_seek_pos += seek;
	}
	show_recommend_content(rh_seek_pos);
}
function seek_travel_plan(seek) {
	if(seek_pos + seek >= travel_plan_list.length) {
		seek_pos = 0;
	} else if(seek_pos + seek < 0) {
		seek_pos = travel_plan_list.length - 1;
	} else {
		seek_pos += seek;
	}
	show_travel_plan(travel_plan_list[seek_pos]);
}
function show_travel_plan(obj) {

	var rtp_img = '/web/img/no-pic.jpg';
	if(obj.tp_cover_photo != 0) {
		rtp_img = image_server_url + '/photos' + travel_plan_photo_path + '/' + obj.tp_id + '/' + obj.tp_cover_photo + '.jpg';
	}
	var avatar_img = '';
	if(obj.ap_avatar != null) {
		avatar_img = image_server_url + '/photos' + author_photo_path + '/' + obj.a_id + '/' + obj.ap_avatar + '.' + obj.p_content_type;
	}

	$('.travIndex-container .sharerWrap .shareBtn').prop('href', '/trip/' + obj.tp_id + '/');
	$('.travIndex-container .sharerWrap .title').html(obj.tp_title);
	// $('#rtp_image').prop('src', rtp_img).prop('alt', obj.tp_title);
	$('#rtp_image').css("background-image","url("+rtp_img+")").prop('href', '/trip/' + obj.tp_id + '/');
	if(avatar_img != '') {
		$('#rtp_author_avatar').html('<img src="' + avatar_img + '" alt="' + obj.a_nickname + '">');
	}else {
		$('#rtp_author_avatar').html('');
	}
	$('.travIndex-container .sharerWrap .mediaName').html(obj.a_title);
	$('.travIndex-container .sharerWrap .name').html(obj.a_nickname);
	$('#rtp_favorite').html(obj.tpe_collect_total);
	$('#rtp_view').html(obj.tpe_click_total);
	$('#rtp_date_range').html(obj.tp_begin_date + '-' + obj.tp_end_date);
}
function show_recommend_content(show_idx) {
	rh_seek_pos = show_idx;
	var content_type = '';
	var content_code = '';
	$('.travIndex-container .promo-mustBe .menuBar li').each(function() {
		var idx = $(this).attr('data-idx');
		if(idx != show_idx) {
			$(this).removeClass('selected');
			$('#recommend_homestay_' + idx).hide();
		} else {
			content_type = $(this).attr('data-content-type');
			content_code = $(this).attr('data-content-code');
			$(this).addClass('selected');
		}
	});
	// $('#recommend_homestay_' + show_idx).show();
	var p = {};
    p.func = 'find_homestay_for_booking_hom_by_recommend_type';
    p.content_code = content_code;
    p.content_type = content_type;
    console.log(p);
	$.post("/web/ajax/ajax.php", p, function(data) {
        //console.log(data);
        if(data.code == '9999'){
            alert(data.msg);
        } else {
        	var html = '';
			for(var i=0 ; i<data.data.length ; i++) {
				html += get_recommend_homestay_info(data.data[i], i);
				if(i == 0){
					html += '<div class="imgSmallGroup">';
				}

			}
			html += '</div>';
			$('.travIndex-container .promo-mustBe .itemGroup').html(html);
        }
    }, 'json').done(function() { }).fail(function() { }).always(function() { });
}

</script>
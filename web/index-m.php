<?php
require_once 'config.php';
$t1 = microtime(true);
header("Content-Type:text/html; charset=utf-8");

const KEY_VISION = 'homepage.key.vision';
const SELL_BANNER = 'homepage.sell.banner';
const EXCELLENT_PLAN = 'homepage.excellent.plan';

$travel_ez_content_service = new travel_ez_content_service();
$tripitta_service = new tripitta_service();

// 首頁資料暫存，預設為5分鐘
$cache = get_cache();
$cache_default_time = 60 * 60;

// 頁面基本參數
$adult_count = 10;
$child_count = 10;

// 頁面傳送資料
$begin_area = get_val('begin_area');
$end_area = get_val('end_area');
$car_day = get_val('car_day');
//清除OTR和TRAVELING SESSION 判斷左側選單用
clearOtrVendor();

// 作者圖片相關
$image_server_url = get_config_image_server();
$author_photo_path = '/author';
if(!is_production()) {
	$author_photo_path = '/author_alpha';
}

// 明天
$tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1, date("y")));

// 包車天數內容(預設間隔0.5)
$day_count = 3;
$day_space = 0.5;
$car_day_list = array();
for ($i = 0.5; $i <= $day_count; $i+=$day_space) {
	$car_day_list[] = $i;
}

// 區域
$north_list = $cache->get('tripitta.area.north');
$west_list = $cache->get('tripitta.area.west');
$south_list = $cache->get('tripitta.area.south');
$east_list = $cache->get('tripitta.area.east');
if (empty($north_list)) {
	$north_list = $tripitta_service -> find_area_by_region(1);
	$cache->set('tripitta.area.north', $north_list, $cache_default_time);
}
if (empty($west_list)) {
	$west_list = $tripitta_service -> find_area_by_region(2);
	$cache->set('tripitta.area.west', $west_list, $cache_default_time);
}
if (empty($south_list)) {
	$south_list = $tripitta_service -> find_area_by_region(3);
	$cache->set('tripitta.area.south', $south_list, $cache_default_time);
}
if (empty($east_list)) {
	$east_list = $tripitta_service -> find_area_by_region(4);
	$cache->set('tripitta.area.east', $east_list, $cache_default_time);
}

// 民宿區域
$area_list = $cache->get('tripitta.homestay.area');
if (empty($area_list)) {
	$tripitta_homestay_service = new tripitta_homestay_service();
	$area_list = $tripitta_homestay_service->find_valid_area_for_search_by_category_and_parent_id(get_config_current_lang(), 'homestay', 0);
	$cache->set('tripitta.homestay.area', $area_list, $cache_default_time);
}

// 包車區域
$car_area_list = $cache->get('tripitta.car.area');
if (empty($car_area_list)) {
	$area_category = 'car';
	$area_dao = Dao_loader::__get_area_dao();
	$car_area_list = $area_dao->findAreasWithLangByCategoryAndParentId(get_config_current_lang(), $area_category, 0);
	$cache->set('tripitta.car.area', $car_area_list, $cache_default_time);
}

// 機場區域
$area_deliver_list = $cache->get('tripitta.car.deliver');
if (empty($area_deliver_list)) {
	$area_category = 'car.deliver';
	$area_dao = Dao_loader::__get_area_dao();
	$area_deliver_list = $area_dao->findAreasWithLangByCategoryAndParentId(get_config_current_lang(), $area_category, 0);
	$cache->set('tripitta.car.deliver', $area_deliver_list, $cache_default_time);
}

// key vision
// $kv_list = $cache->get(KEY_VISION);
// if (empty($kv_list)) {
// 	$kv_list = $travel_ez_content_service->find_key_vision(1);
// 	$cache->set(KEY_VISION, $kv_list, $cache_default_time);
// }
// printmsg($kv_list);

// 主題企劃
$sell_banner_list = $cache->get(SELL_BANNER);
if (empty($sell_banner_list)) {
    $sell_banner_list = $travel_ez_content_service->find_homepage_banner(1);
    $cache->set(SELL_BANNER, $sell_banner_list, $cache_default_time);
}

// 行程遊記
$excellent_plan_list = $cache->get(EXCELLENT_PLAN);
if (empty($excellent_plan_list)) {
	$content_service = new content_service();
	$excellent_plan_list = $content_service->find_valid_excellent_plan();
	$cache->set(EXCELLENT_PLAN, $excellent_plan_list, $cache_default_time);
}
// intmsg($north_list);
// printmsg('time:' . (microtime(true) - $t1));
?><!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<meta charset="UTF-8">
	<?php include __DIR__ . '/pages/common/head_new.php';?>
	<?php /*?><script src="/web/js/lib/jquery/jquery.js"></script><?php */?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script src="/web/js/main-min.js"></script>
	<?php /*?><script src="/web/js/jquery-ui.min.js"></script><?php */?>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script src="/web/js/swiper.jquery.min.js"></script>
	<script src="/web/js/jquery.lazyload.min.js"></script>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.min.css">
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" href="/web/css/main2.css">
    <link rel="stylesheet" href="/web/css/swiper.min.css">
    <style>
    #swiper3 {
        width: 100%;
/*         height: 320px; */
    }
    #swiper2 {
        width: 100%;
/*         height: 320px; */
    }
/*     .swiper-slide { */
/*     	width: 280px; */
/*     } */

    #swiper4 {
        width: 420px;
/*         height: 320px; */
    }

    #swiper5 {
        width: 420px;
/*         height: 320px; */
    }

    #swiper6 {
        width: 420px;
/*         height: 320px; */
    }
    </style>
    <script>
	$(function () {
		// 民宿區域
		$("#west_area").hide();
		$("#south_area").hide();
		$("#east_area").hide();
		$('#swiper5').hide();
		$('#swiper6').hide();

		// 點了北部
        $('#north').click(function () {
        	$("#north").addClass( "selected" );
        	$("#west").removeClass( "selected" );
        	$("#south").removeClass( "selected" );
        	$("#east").removeClass( "selected" );
        	$('#north_area').show();
    		$("#west_area").hide();
    		$("#south_area").hide();
    		$("#east_area").hide();
        });

		// 點了西部
        $('#west').click(function () {
        	$("#north").removeClass( "selected" );
        	$("#west").addClass( "selected" );
        	$("#south").removeClass( "selected" );
        	$("#east").removeClass( "selected" );
        	$('#north_area').hide();
    		$("#west_area").show();
    		$("#south_area").hide();
    		$("#east_area").hide();
        });

		// 點了南部
        $('#south').click(function () {
        	$("#north").removeClass( "selected" );
        	$("#west").removeClass( "selected" );
        	$("#south").addClass( "selected" );
        	$("#east").removeClass( "selected" );
        	$('#north_area').hide();
    		$("#west_area").hide();
    		$("#south_area").show();
    		$("#east_area").hide();
        });

		// 點了東部
        $('#east').click(function () {
        	$("#north").removeClass( "selected" );
        	$("#west").removeClass( "selected" );
        	$("#south").removeClass( "selected" );
        	$("#east").addClass( "selected" );
        	$('#north_area').hide();
    		$("#west_area").hide();
    		$("#south_area").hide();
    		$("#east_area").show();
        });

		// 點了民宿
        $('#homestays').click(function () {
        	$("#homestays").addClass( "selected" );
        	$("#chartercar").removeClass( "selected" );
        	$("#pickups").removeClass( "selected" );
        	$("#tourbuss").removeClass( "selected" );
        	$('#hotel').show();
    		$("#charter").hide();
    		$("#pickUp").hide();
    		$("#tourBus").hide();
        });

		// 點了包車
        $('#chartercar').click(function () {
        	$("#homestays").removeClass( "selected" );
        	$("#chartercar").addClass( "selected" );
        	$("#pickups").removeClass( "selected" );
        	$("#tourbuss").removeClass( "selected" );
        	$('#hotel').hide();
    		$("#charter").show();
    		$("#pickUp").hide();
    		$("#tourBus").hide();
        });

		// 點了接送
        $('#pickups').click(function () {
        	$("#homestays").removeClass( "selected" );
        	$("#chartercar").removeClass( "selected" );
        	$("#pickups").addClass( "selected" );
        	$("#tourbuss").removeClass( "selected" );
        	$('#hotel').hide();
    		$("#charter").hide();
    		$("#pickUp").show();
    		$("#tourBus").hide();
        });

		// 點了觀巴
        $('#tourbuss').click(function () {
        	$("#homestays").removeClass( "selected" );
        	$("#chartercar").removeClass( "selected" );
        	$("#pickups").removeClass( "selected" );
        	$("#tourbuss").addClass( "selected" );
        	$('#hotel').hide();
    		$("#charter").hide();
    		$("#pickUp").hide();
    		$("#tourBus").show();
        });

		// 點了觀巴
        $('#tourbuss').click(function () {
        	$("#homestays").removeClass( "selected" );
        	$("#chartercar").removeClass( "selected" );
        	$("#pickups").removeClass( "selected" );
        	$("#tourbuss").addClass( "selected" );
        	$('#hotel').hide();
    		$("#charter").hide();
    		$("#pickUp").hide();
    		$("#tourBus").show();
        });

		// 點了景點
        $('#viewpoint').click(function () {
        	$("#viewpoint").addClass( "selected" );
        	$("#food").removeClass( "selected" );
        	$("#gift").removeClass( "selected" );
        	$('#swiper4').show();
    		$('#swiper5').hide();
    		$('#swiper6').hide();
        });

		// 點了美食
        $('#food').click(function () {
        	$("#viewpoint").removeClass( "selected" );
        	$("#food").addClass( "selected" );
        	$("#gift").removeClass( "selected" );
        	$('#swiper4').hide();
    		$('#swiper5').show();
    		$('#swiper6').hide();
        });

		// 點了伴手禮
        $('#gift').click(function () {
        	$("#viewpoint").removeClass( "selected" );
        	$("#food").removeClass( "selected" );
        	$("#gift").addClass( "selected" );
        	$('#swiper4').hide();
    		$('#swiper5').hide();
    		$('#swiper6').show();
        });

		$('.toggleBtn').click(function () {
        	$("#homestays").removeClass( "selected" );
        	$("#chartercar").removeClass( "selected" );
        	$("#pickups").removeClass( "selected" );
        	$("#tourbuss").removeClass( "selected" );
        	$('#hotel').hide();
    		$("#charter").hide();
    		$("#pickUp").hide();
    		$("#tourBus").hide();
        });

        alert($(window).width());

		var caneldar_option = <?= json_encode(Constants::$CALENDAR_OPTIONS) ?>;

		// 搜尋民宿
// 		$('#beginDate').datepicker(caneldar_option).datepicker('option', {minDate: new Date()}).change(function(){ dateCompare2('beginDate','endDate'); });
// 		$('#endDate').datepicker(caneldar_option).datepicker('option', {minDate: 1}).change(function(){ dateCompare3('beginDate','endDate');});
		$('#beginDate').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
		$('#beginDate').datepicker(caneldar_option).datepicker('option', {minDate: 1});
		$('#homeStayQuery').click(function() { query_homestay() });

		// 搜尋包車
		$('#begin_date').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
        $('#car_search').click(function () { query_car(); });

        // 搜尋接送
        $('#begin_date_pickup').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
        $('#pickup_search').click(function () { query_pickup(); });

        // 搜尋觀巴
        $('#begin_date_bus').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
        $('#bus_search').click(function () { query_bus(); });

        // 切換寬度
// 	    $(window).resize(function() {
// 	        wdth=$(window).width();
// 	       // alert(wdth);
// 			$('#swiper4').css({
// 				width: wdth
// 			});
// 	    });

        $('#pickup_type').change(function () {
        	var pickup_type = $('#pickup_type :selected').val();
        	if (pickup_type == 4) {
        		$('#begin_1').hide();
        		$('#end_1').hide();
		        $('#begin_2').show();
		        $('#end_2').show();
        	} else if (pickup_type == 2) {
        		$('#begin_1').show();
        		$('#end_1').show();
		        $('#begin_2').hide();
		        $('#end_2').hide();
        	}
        });
	});

	function query_homestay() {
		var areaCode = $('#areaCode').val();
		var beginDate = $('#beginDate').val();
		var endDate = $('#endDate').val();
		var roomType = parseInt($('#roomType').val());
		var roomQuantity = parseInt($('#roomQuantity').val());
		if(areaCode == null) { alert('請選擇地區'); return; }
		if(beginDate != null && endDate != null) {
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

 	// 包車交通預定 - 重新查詢 - 處理內容
    query_car = function () {
        var begin_area = $('#begin_area :selected').val();
        var end_area = $('#end_area :selected').val();
        var begin_date = $('#begin_date').val();
        var car_day = $('#car_day :selected').val();
        var car_adult = $('#car_adult :selected').val();
        var car_child = $('#car_child :selected').val();
        var url = '/bookingcar/?begin_area=' + encodeURIComponent(begin_area);
        url += '&end_area=' + encodeURIComponent(end_area);
        url += '&begin_date=' + encodeURIComponent(begin_date);
        url += '&car_day=' + encodeURIComponent(car_day);
        url += '&car_adult=' + encodeURIComponent(car_adult);
        url += '&car_child=' + encodeURIComponent(car_child);
        location.href = url;
    }

 	// 接送交通預定 - 重新查詢 - 處理內容
    query_pickup = function () {
        var pickup_type = $('#pickup_type :selected').val();
        if (pickup_type == 2) {
            var begin_area_pickup = $('#begin_area_pickup_1 :selected').val();
            var end_area_pickup = $('#end_area_pickup_1 :selected').val();
        } else if (pickup_type == 4) {
        	var begin_area_pickup = $('#begin_area_pickup_2 :selected').val();
            var end_area_pickup = $('#end_area_pickup_2 :selected').val();
        }
        var begin_date_pickup = $('#begin_date_pickup').val();
        var pickup_adult = $('#pickup_adult :selected').val();
        var pickup_child = $('#pickup_child :selected').val();
        var url = '/pickup/?pickup_type=' + encodeURIComponent(pickup_type);
        url += '&begin_area=' + encodeURIComponent(begin_area_pickup);
        url += '&end_area=' + encodeURIComponent(end_area_pickup);
        url += '&begin_date=' + encodeURIComponent(begin_date_pickup);
        url += '&car_adult=' + encodeURIComponent(pickup_adult);
        url += '&car_child=' + encodeURIComponent(pickup_child);
        location.href = url;
    }

 	// 觀光巴士交通預定 - 重新查詢 - 處理內容
    query_bus = function () {
        var begin_area_bus = $('#begin_area_bus :selected').val();
        var end_area_bus = $('#end_area_bus :selected').val();
        var begin_date_bus = $('#begin_date_bus').val();
        var url = '/tourbus/?begin_area=' + encodeURIComponent(begin_area_bus);
        url += '&end_area=' + encodeURIComponent(end_area_bus);
        url += '&begin_date=' + encodeURIComponent(begin_date_bus);
        location.href = url;
    }

	/** 推薦旅宿 **/
	var homestay_location = '<?php echo $north_list[0]['a_code']?>';
	function goRecommendHomestay() {
		window.open('/booking/' + homestay_location + '/?beginDate=&endDate=&roomType=2&roomQuantity=1');
	}
    </script>
</head>
<body>
	<header><?php include __DIR__ . '/pages/common/header_new.php';?></header>
	<main class="index-container-m">
		<div class="search">
			<!-- menu -->
			<ul class="sTitle">
				<li id="homestays">
					<div class="iWrap">
						<i class="img-house"></i>
						<i class="img-house-bk"></i>
					</div>
					<div>旅宿預定</div>
				</li>

<!-- 20160713 1700 Lily要求暫時更改畫面 -->
				<!-- <li id="chartercar">
					<div class="iWrap">
						<i class="img-chartercar"></i>
						<i class="img-chartercar-bk"></i>
					</div>
					<div>包車</div>
				</li>
				<li id="pickups">
					<div class="iWrap">
						<i class="img-airportshuttle"></i>
						<i class="img-airportshuttle-bk"></i>
					</div>
					<div>接送機</div>
				</li>
				<li id="tourbuss">
					<div class="iWrap">
						<i class="img-sightseeingbus"></i>
						<i class="img-sightseeingbus-bk"></i>
					</div>
					<div>觀光巴士</div>
				</li> -->
                                                    <li id="hsr">
                                                        <div class="iWrap">
                                                            <i class="img-highspeedrail"></i>
                                                            <i class="img-highspeedrail-bk"></i>
                                                        </div>
                                                        <div>高鐵票券</div>
                                                    </li>

			</ul>

			<!-- 旅宿 -->
			<div id="hotel" class="sBlock">
				<div class="sbWrap">
					<i class="img-icon-37"></i>
					<select id="areaCode">
						<option disabled="disabled" selected>你想去哪裡</option>
						<? foreach($area_list as $area_row) { ?>
						<option value="<?= $area_row["a_code"] ?>"><?= $area_row["a_name"] ?></option>
						<? } ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<div class="sbWrap">
					<i class="img-icon-41"></i>
					<input id="beginDate" name="beginDate" type="date" placeholder="入住日期">
				</div>
				<div class="sbWrap">
					<i class="img-icon-42"></i>
					<input id="endDate" name="endDate" type="date" placeholder="退房日期">
				</div>
				<div class="sbWrap">
					<i class="img-icon-38"></i>
					<select id="roomType">
						<option disabled="disabled" selected>房客人數</option>
						<option value="1">1人</option>
						<option value="2">2人</option>
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
				<div class="sbWrap">
					<i class="img-icon-13"></i>
					<select id="roomQuantity">
						<option disabled="disabled" selected>客房數量</option>
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
				<button id="homeStayQuery" class="submit">查詢</button>
				<div class="toggleBtn">
					<i class="fa fa-angle-up"></i>
				</div>
			</div>

			<!-- 包車 -->
			<div id="charter" class="sBlock" >
				<div class="sbWrap">
					<i class="img-icon-40"></i>
					<select id="begin_area">
						<option value='' disabled="disabled" selected>選擇出發地</option>
                            <?php
                            if (!empty($car_area_list)) {
                                foreach ($car_area_list as $a) {
                                    ?>
                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $begin_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
                                    <?php
                                }
                            }
                            ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<div class="sbWrap">
					<i class="img-icon-37"></i>
					<select id="end_area">
						<option value='' disabled="disabled" selected>選擇目的地</option>
                            <?php
                            if (!empty($car_area_list)) {
                                foreach ($car_area_list as $a) {
                                    ?>
                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $end_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
                                    <?php
                                }
                            }
                            ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<div class="sbWrap">
					<i class="img-icon-43"></i>
					<input type="text" id="begin_date" name="begin_date" value="<?php echo $tomorrow; ?>" placeholder="出發日期" class="checkIn" maxlength="20">
				</div>
				<div class="sbWrap">
					<i class="img-icon-44"></i>
					<select id="car_day">
						<option value='' disabled="disabled" selected>包車天數</option>
                            <?php
                            if (!empty($car_day_list)) {
                                foreach ($car_day_list as $k => $cdl) {
                                    $car_day_name = null;
                                    if ($k == 0) {
                                        $car_day_name = "半日";
                                    } else {
                                        $car_day_name = $cdl . "日";
                                    }
                                    ?>
                                    <option value="<?php echo $cdl; ?>" <?php echo $car_day == $cdl ? 'selected="selected"' : ''; ?>><?php echo $car_day_name; ?></option>
                                    <?php
                                }
                            }
                            ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<div class="sbWrap">
					<i class="img-icon-38"></i>
					<select id="car_adult">
						<option value='' disabled="disabled" selected>大人數</option>
                            <?php for ($i = 0; $i <= $adult_count; $i++) { ?>
                                <option value="<?php echo $i; ?>" <?//php echo $car_adult == $i ? 'selected="selected"' : ''; ?>><?php echo $i; ?>人</option>
                            <?php } ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<div class="sbWrap">
					<i class="img-icon-39"></i>
					<select id="car_child">
						<option value='' disabled="disabled" selected>5歲以下孩童</option>
                            <?php for ($i = 0; $i <= $child_count; $i++) { ?>
                                <option value="<?php echo $i; ?>" <?//php echo $car_child == $i ? 'selected="selected"' : ''; ?>><?php echo $i; ?>人</option>
                            <?php } ?>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
				<button id="car_search" class="submit">查詢</button>
				<div class="toggleBtn">
					<i class="fa fa-angle-up"></i>
				</div>
			</div>

			<!-- 接送機 -->
			<div id="pickUp" class="sBlock">
				<div class="sbWrap">
					<i class="img-icon-2"></i>
					<select id="pickup_type" name="pickup_type">
						<option value="" disabled="disabled">選擇服務項目</option>
						<option value="2" selected>接機</option>
						<option value="4">送機</option>
					</select>
					<i class="fa fa-angle-down"></i>
				</div>
						<div class="sbWrap" id="begin_1">
							<i class="img-icon-40"></i>
							<select id="begin_area_pickup_1" name="begin_area_pickup_1">
								<option value='' selected>選擇出發地</option>
	                            <?php
	                            if (!empty($area_deliver_list)) {
	                                foreach ($area_deliver_list as $a) {
	                                    ?>
	                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $begin_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
	                                    <?php
	                                }
	                            }
	                            ?>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="sbWrap" id="begin_2" style="display:none;">
							<i class="img-icon-40"></i>
							<select id="begin_area_pickup_2" name="begin_area_pickup_2">
								<option value='' selected>選擇出發地</option>
	                            <?php
	                            if (!empty($car_area_list)) {
	                                foreach ($car_area_list as $a) {
	                                    ?>
	                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $end_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
	                                    <?php
	                                }
	                            }
	                            ?>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="sbWrap" id="end_1">
							<i class="img-icon-37"></i>
							<select id="end_area_pickup_1" name="end_area_pickup_1">
								<option value='' selected>選擇目的地</option>
	                            <?php
	                            if (!empty($car_area_list)) {
	                                foreach ($car_area_list as $a) {
	                                    ?>
	                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $end_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
	                                    <?php
	                                }
	                            }
	                            ?>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="sbWrap" id="end_2" style="display:none;">
							<i class="img-icon-37"></i>
							<select id="end_area_pickup_2" name="end_area_pickup_2">
								<option value='' selected>選擇目的地</option>
	                            <?php
	                            if (!empty($area_deliver_list)) {
	                                foreach ($area_deliver_list as $a) {
	                                    ?>
	                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $begin_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
	                                    <?php
	                                }
	                            }
	                            ?>
							</select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="sbWrap">
							<i class="img-icon-43"></i>
							<input type="text" id="begin_date_pickup" name="begin_date_pickup" value="<?php echo $tomorrow; ?>" placeholder="出發日期" class="checkIn" maxlength="20">
						</div>
						<div class="sbWrap">
							<i class="img-icon-38"></i>
							<select id="pickup_adult" name="pickup_adult">
								<option value='' selected disabled>大人數</option>
	                            <?php for ($i = 0; $i <= $adult_count; $i++) { ?>
	                                <option value="<?php echo $i; ?>" <?//php echo $car_adult == $i ? 'selected="selected"' : ''; ?>><?php echo $i; ?>人</option>
	                            <?php } ?>
	                        </select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="sbWrap">
							<i class="img-icon-39"></i>
							<select id="pickup_child" name="pickup_child">
								<option value='0' selected disabled>五歲以下孩童</option>
	                            <?php for ($i = 0; $i <= $child_count; $i++) { ?>
	                                <option value="<?php echo $i; ?>" <?//php echo $car_child == $i ? 'selected="selected"' : ''; ?>><?php echo $i; ?>人</option>
	                            <?php } ?>
	                        </select>
							<i class="fa fa-angle-down"></i>
						</div>
						<button id="pickup_search" class="submit">查詢</button>
						<div class="toggleBtn" id="close_up2">
							<i class="fa fa-angle-up"></i>
						</div>
			</div>

			<!-- 觀巴 -->
			<div id="tourBus" class="sBlock">
				<div class="sbWrap">
						<i class="img-icon-40"></i>
						<select id="begin_area_bus" name="begin_area_bus">
							<option value='' selected>選擇出發地</option>
                            <?php
                            if (!empty($car_area_list)) {
                                foreach ($car_area_list as $a) {
                                    ?>
                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $begin_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
                                    <?php
                                }
                            }
                            ?>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<div class="sbWrap">
						<i class="img-icon-37"></i>
						<select id="end_area_bus" name="end_area_bus">
							<option value='' selected>選擇目的地</option>
                            <?php
                            if (!empty($car_area_list)) {
                                foreach ($car_area_list as $a) {
                                    ?>
                                    <option value="<?php echo $a["a_id"]; ?>" <?php echo $end_area == $a["a_id"] ? 'selected="selected"' : ''; ?>><?php echo $a["a_name"]; ?></option>
                                    <?php
                                }
                            }
                            ?>
						</select>
						<i class="fa fa-angle-down"></i>
					</div>
					<div class="sbWrap">
						<i class="img-icon-43"></i>
						<input type="text" id="begin_date_bus" name="begin_date_bus" value="<?php echo $tomorrow; ?>" placeholder="出發日期" class="checkIn" maxlength="20">
					</div>
					<button id="bus_search" class="submit">查詢</button>
					<div class="toggleBtn" id="close_up3">
						<i class="fa fa-angle-up"></i>
					</div>
			</div>
		</div>
		<div class="slide">
		    <div class="swiper-container" id="swiper1">
		        <div class="swiper-wrapper">
		        	<?php /*
		        	目前只有一張，抓 kv 會有 5張，先固定寫死為阿政提供的縮圖後的內容 2016-07-06 john
		        	$lazy_idx = 0;
		        	foreach($kv_list as $kl) {
		        		$kv_img = get_config_image_server() . $kl["cc_content"];
		        		$kv_img = str_replace('/photos/content/key_vision', '/photos/tmp/mobile_homepage', $kv_img);
		        	?>
		        	<?php if ($lazy_idx == 0) {?>
		        	<div class="img swiper-slide" style="background-image:url('<?php echo $kv_img?>');"></div>
		        	<?php } else {?>
		        	<div class="img swiper-slide" lazyName="kv" data-original="<?= $kv_img ?>" style="background-image:url('/web/img/grey.gif');"></div>
		        	<?php }?>
		            <?php
		            	$lazy_idx++;
		        	}*/
		            ?>
		            <!--
		            <div class="img swiper-slide" style="background-image:url('<?php echo get_config_image_server() . '/photos/tmp/mobile_homepage/5459.jpg'?>');"></div>
		            -->
		            <div class="img swiper-slide" style="background-image:url('/web/img/sec/banner-campaign.jpg');" id="main_banner"></div>
		        </div>
		        <!-- Add Pagination -->
		        <div class="swiper-pagination"></div>
		    </div>
		</div>
		<section class="sec">
			<div class="title">
				<span>主題企劃</span>
				<!--
				<a href="javascript:void(0)">
					更多<i class="fa fa-angle-right"></i>
				</a>
				-->
			</div>
			<div class="introFrame swiper-container" id="swiper2" style="display: inline-block;">
			<div class="swiper-wrapper">
			<?php
			$lazy_idx = 0;
			foreach ($sell_banner_list as $sell_banner) {
			    $img = get_config_image_server() . $sell_banner['cc_content'];
			    $img = str_replace('/photos/content/homepage_sell_banner', '/photos/tmp/mobile_homepage', $img);
			?>
				<div class="swiper-slide" style="padding:0px;">
			<?php if ($lazy_idx == 0) {?>
				<a href="<?php echo $sell_banner['cc_link_url']?>" class="project" style="background-image: url('<?= $img ?>'); margin: 0;width: 100%;">
			<?php } else {?>
			    <a href="<?php echo $sell_banner['cc_link_url']?>" class="project" lazyIdx="<?php echo $lazy_idx?>" data-original="<?= $img ?>" style="background-image: url('/web/img/grey.gif'); margin: 0;width: 100%;">
			<?php }?>
					<div class="pIntro ">
						<?= $sell_banner['cc_title'] ?>
					</div>
				</a>
				</div>
			<?php
				$lazy_idx++;
			}
			?>
			</div>
			</div>
		</section>
		<section class="sec">
			<div class="title">
				<span>行程遊記</span>
				<a href="/trip/" target="_blank">
					更多<i class="fa fa-angle-right"></i>
				</a>
			</div>
			<div class="introFrame swiper-container" id="swiper3" style="display: inline-block;">
			<div class="swiper-wrapper">
			<?php
			$lazy_idx = 0;
			foreach ($excellent_plan_list as $epl) {
				$plan_img = get_config_image_server() . '/photos/' . (is_production() ? 'travel_plan' : 'travel_plan_alpha')  . '/' . $epl['tpe_id'] . '/' . $epl['tp_cover_photo'] . '.jpg';
				$plan_img = get_config_image_server() . '/photos/tmp/mobile_homepage/' . $epl['tp_cover_photo'] . '.jpg';
				$img_author_head = null;
				if($epl["ap_avatar"] != '' && $epl["ap_avatar"] != '0' && $epl["ap_avatar"] != null) {
					$img_author_head = sprintf("%s/photos%s/%s/%s_i.jpg", $image_server_url, $author_photo_path, $epl["a_id"], $epl["ap_avatar"]);
				}
			?>
				<div class="swiper-slide itinery" style="padding:0px;">
			<?php if ($lazy_idx == 0) {?>
					<a href="/trip/<?= $epl['tpe_id']?>/" class="img" style="background: url('<?= $plan_img ?>') 50% 50%/cover no-repeat">
			<?php } else {?>
					<a href="/trip/<?= $epl['tpe_id']?>/" class="img" lazyIdx="<?php echo $lazy_idx?>" data-original="<?= $plan_img ?>" style="background: url('/web/img/grey.gif') 50% 50%/cover no-repeat">
			<?php }?>
						<div class="sign">
							<i class="fa fa-eye"></i>
							<span><?= $epl["tpe_click_total"] ?></span>
							<i class="fa fa-heart"></i>
							<span><?= $epl["tpe_collect_total"] ?></span>
						</div>
					</a>
					<a href="javascript:void(0)" class="autohor">
						<div class="aImg" style="background: url(<?php echo $img_author_head?>) 50% 50%/cover no-repeat"></div>
						<div class="aName"><?= $epl["a_title"] ?></div>
					</a>
					<a href="javascript:void(0)" class="iIntro">
						<div class="iTitle">
							<?= $epl["cm_reference_title"] ?>
						</div>
						<div class="iAbs">
							<?= $epl["tp_foreword"] ?>
						</div>
					</a>
				</div>
			<?php
				$lazy_idx++;
			}
			?>
			</div>
			</div>
		</section>
		<section class="sec">
			<div class="title">
				<span>旅宿推薦</span>
				<a href="javascript:goRecommendHomestay()">
					更多<i class="fa fa-angle-right"></i>
				</a>
			</div>

			<!-- scroll fixed block -->
			<div class="switchBtn">
				<div>
					<button class="sbBtn selected" id="north">北部</button>
				</div>
				<div>
					<button class="sbBtn" id="west">西部</button>
				</div>
				<div>
					<button class="sbBtn" id="south">南部</button>
				</div>
				<div>
					<button class="sbBtn" id="east">東部</button>
				</div>
			</div>
			<?php //北部  ?>
			<div class="hotelFrame" id="north_area">
				<?php foreach ($north_list as $nl){
					$hs_img = $image_server_url . '/photos/travel/home_stay/' . $nl["hs_id"] . '/' .$nl["hs_main_photo"]. '_big.jpg';
					$hs_url = "/booking/".$nl["a_code"]."/".$nl["hs_id"]."/";
				?>
				<a href="<?= $hs_url ?>" class="hotel">
					<div class="hImg" area="north" data-original="<?= $hs_img ?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">

					</div>
					<div class="hIntro">
						<div class="hTitle">
							<?= $nl["hs_name"] ?>
						</div>
						<div class="hInfo">
							<div class="rating">
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star-o"></i>
								<span class="rPoint">4</span>
								<span class="rRange"> / 5</span>
							</div>
							<div class="price">
								<span class="pCurrency">NTD</span>
								<span class="pNum"><?= number_format($nl["hsrp_price"]) ?></span>
							</div>
						</div>
						<div class="hBottom">
							<div class="hLocation">
								<i class="fa fa-map-marker"></i>
								<span class="LText"><?= $nl["hs_address"] ?></span>
							</div>
							<!--
							<div class="strikethrough">
								<span>NTD</span>
								<span>12,000</span>
							</div>
							-->
						</div>
					</div>
				</a>
				<?php } ?>
			</div>
			<?php //西部  ?>
			<div class="hotelFrame" id="west_area">
				<?php foreach ($west_list as $nl){
					$hs_img = $image_server_url . '/photos/travel/home_stay/' . $nl["hs_id"] . '/' .$nl["hs_main_photo"]. '_big.jpg';
					$hs_url = "/booking/".$nl["a_code"]."/".$nl["hs_id"]."/";
				?>
				<a href="<?= $hs_url ?>" class="hotel">
					<div class="hImg" area="west" data-original="<?= $hs_img ?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">

					</div>
					<div class="hIntro">
						<div class="hTitle">
							<?= $nl["hs_name"] ?>
						</div>
						<div class="hInfo">
							<div class="rating">
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star-o"></i>
								<span class="rPoint">4</span>
								<span class="rRange"> / 5</span>
							</div>
							<div class="price">
								<span class="pCurrency">NTD</span>
								<span class="pNum"><?= number_format($nl["hsrp_price"]) ?></span>
							</div>
						</div>
						<div class="hBottom">
							<div class="hLocation">
								<i class="fa fa-map-marker"></i>
								<span class="LText"><?= $nl["hs_address"] ?></span>
							</div>
							<!--
							<div class="strikethrough">
								<span>NTD</span>
								<span>12,000</span>
							</div>
							-->
						</div>
					</div>
				</a>
				<?php } ?>
			</div>
			<?php //南部  ?>
			<div class="hotelFrame" id="south_area">
				<?php foreach ($south_list as $nl){
					$hs_img = $image_server_url . '/photos/travel/home_stay/' . $nl["hs_id"] . '/' .$nl["hs_main_photo"]. '_big.jpg';
					$hs_url = "/booking/".$nl["a_code"]."/".$nl["hs_id"]."/";
				?>
				<a href="<?= $hs_url ?>" class="hotel">
					<div class="hImg" area="south" data-original="<?= $hs_img ?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">

					</div>
					<div class="hIntro">
						<div class="hTitle">
							<?= $nl["hs_name"] ?>
						</div>
						<div class="hInfo">
							<div class="rating">
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star-o"></i>
								<span class="rPoint">4</span>
								<span class="rRange"> / 5</span>
							</div>
							<div class="price">
								<span class="pCurrency">NTD</span>
								<span class="pNum"><?= number_format($nl["hsrp_price"]) ?></span>
							</div>
						</div>
						<div class="hBottom">
							<div class="hLocation">
								<i class="fa fa-map-marker"></i>
								<span class="LText"><?= $nl["hs_address"] ?></span>
							</div>
							<!--
							<div class="strikethrough">
								<span>NTD</span>
								<span>12,000</span>
							</div>
							-->
						</div>
					</div>
				</a>
				<?php } ?>
			</div>
			<?php //東部  ?>
			<div class="hotelFrame" id="east_area">
				<?php foreach ($east_list as $nl){
					$hs_img = $image_server_url . '/photos/travel/home_stay/' . $nl["hs_id"] . '/' .$nl["hs_main_photo"]. '_big.jpg';
					$hs_url = "/booking/".$nl["a_code"]."/".$nl["hs_id"]."/";
				?>
				<a href="<?= $hs_url ?>" class="hotel">
					<?php /*?>
					<div class="hImg" style="background-image: url(<?= $hs_img ?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">

					</div>
					<?php */?>
					<div class="hImg" area="east" data-original="<?= $hs_img ?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">

					</div>
					<div class="hIntro">
						<div class="hTitle">
							<?= $nl["hs_name"] ?>
						</div>
						<div class="hInfo">
							<div class="rating">
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star-o"></i>
								<span class="rPoint">4</span>
								<span class="rRange"> / 5</span>
							</div>
							<div class="price">
								<span class="pCurrency">NTD</span>
								<span class="pNum"><?= number_format($nl["hsrp_price"]) ?></span>
							</div>
						</div>
						<div class="hBottom">
							<div class="hLocation">
								<i class="fa fa-map-marker"></i>
								<span class="LText"><?= $nl["hs_address"] ?></span>
							</div>
							<!--
							<div class="strikethrough">
								<span>NTD</span>
								<span>12,000</span>
							</div>
							-->
						</div>
					</div>
				</a>
				<?php } ?>
			</div>
		</section>
		<section class="sec">
			<div class="title">
				<span>觀光指南</span>
				<!-- 台北 花蓮 墾丁 高雄 -->
				<a href="/location/" target="_blank">
					更多<i class="fa fa-angle-right"></i>
				</a>
			</div>
			<div class="switchBtn">
				<div>
					<button class="sbBtn selected" id="viewpoint">景點</button>
				</div>
				<div>
					<button class="sbBtn" id="food">美食</button>
				</div>
<?php /*?>
 				<div>
 					<button class="sbBtn">活動</button>
 				</div>
				<div>
					<button class="sbBtn" id="gift">伴手禮</button>
				</div>
<?php */?>
			</div>
			<!-- 景點 -->
			<div class="introFrame swiper-container" id="swiper4">
				<div class="swiper-wrapper">
					<?php
						$area_img = get_config_image_server() . '/photos/tmp/mobile_homepage/taipei_spot/' . rand(1,3) . '_s.jpg';
					?>
					<div class="swiper-slide">
					<a href="/location/?f=scenic&sf=&areas=north&pageno=1" class="sightseeing" type="spot" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">台北</span>
					</a>
					<?php
						$area_img = get_config_image_server() . '/photos/tmp/mobile_homepage/hualien_spot/' . rand(1,4) . '_s.jpg';
					?>
					<a href="/location/?f=scenic&sf=&areas=east&pageno=1" class="sightseeing" type="spot" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">花蓮</span>
					</a>
					<?php
						$area_img = get_config_image_server() . '/photos/tmp/mobile_homepage/kenting_spot/' . rand(1,3) . '_s.jpg';
					?>
					<a href="/location/?f=scenic&sf=&areas=south&pageno=1" class="sightseeing" type="spot" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">墾丁</span>
					</a>
					</div>
					<?php
						$area_img = get_config_image_server() . '/photos/tmp/mobile_homepage/kaohsiung_spot/' . rand(1,4) . '_s.jpg';
					?>
					<div class="swiper-slide">
					<a href="/location/?f=scenic&sf=&areas=south&pageno=1" class="sightseeing" type="spot" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">高雄</span>
					</a>
<?php /*?>
					<?php
						$area_img = get_config_image_server() . '/photos/tmp/mobile_homepage/kaohsiung_spot/' . rand(1,4) . '_s.jpg';
					?>
					<a href="/location/?f=scenic&sf=&areas=west&pageno=1" class="sightseeing" type="spot" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">台中</span>
					</a>
<?php */?>
					</div>
				</div>
			</div>
			<!-- 美食 -->
			<div class="introFrame swiper-container" id="swiper5">
				<div class="swiper-wrapper">
					<?php
						$area_img = get_config_image_server() . '/photos/tmp/mobile_homepage/taipei_food/' . rand(1,3) . '_s.jpg';
					?>
					<div class="swiper-slide">
					<a href="/location/?f=food&sf=&areas=north&pageno=1" class="sightseeing" type="food" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">台北</span>
					</a>
					<?php
						$area_img = get_config_image_server() . '/photos/tmp/mobile_homepage/hualien_food/' . rand(1,3) . '_s.jpg';
					?>
					<a href="/location/?f=food&sf=&areas=east&pageno=1" class="sightseeing" type="food" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">花蓮</span>
					</a>
					<?php
						$area_img = get_config_image_server() . '/photos/tmp/mobile_homepage/kenting_food/' . rand(1,4) . '_s.jpg';
					?>
					<a href="/location/?f=food&sf=&areas=south&pageno=1" class="sightseeing" type="food" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">墾丁</span>
					</a>
					</div>
					<?php
						$area_img = get_config_image_server() . '/photos/tmp/mobile_homepage/kaohsiung_food/' . rand(1,4) . '_s.jpg';
					?>
					<div class="swiper-slide">
					<a href="/location/?f=food&sf=&areas=south&pageno=1" class="sightseeing" type="food" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">高雄</span>
					</a>
<?php /*?>
					<?php
						$area_img = "/web/img/location/area_info_kv_5.jpg";
					?>
					<a href="/location/?f=food&sf=&areas=west&pageno=1" class="sightseeing" type="food" target="_blank" data-original="<?php echo $area_img?>" style="background-image: url('/web/img/grey.gif');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">台中</span>
					</a>
<?php */?>
					</div>
				</div>
			</div>
<?php /*?>
			<!-- 伴手禮 -->
			<div class="introFrame swiper-container" id="swiper6">
				<div class="swiper-wrapper">
					<?php
						$area_img = "/web/img/location/area_info_kv_1.jpg";
					?>
					<div class="swiper-slide">
					<a href="/location/?f=gift&sf=&areas=north&pageno=1" class="sightseeing" type="gift" target="_blank" style="background-image: url(<?= $area_img ?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">台北</span>
					</a>
					<?php
						$area_img = "/web/img/location/area_info_kv_2.jpg";
					?>
					<a href="/location/?f=gift&sf=&areas=east&pageno=1" class="sightseeing" type="gift" target="_blank" style="background-image: url(<?= $area_img ?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">花蓮</span>
					</a>
					<?php
						$area_img = "/web/img/location/area_info_kv_3.jpg";
					?>
					<a href="/location/?f=gift&sf=&areas=south&pageno=1" class="sightseeing" type="gift" target="_blank" style="background-image: url(<?= $area_img ?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">墾丁</span>
					</a>
					</div>
					<?php
						$area_img = "/web/img/location/area_info_kv_4.jpg";
					?>
					<div class="swiper-slide">
					<a href="/location/?f=gift&sf=&areas=south&pageno=1" class="sightseeing" type="gift" target="_blank" style="background-image: url(<?= $area_img ?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">高雄</span>
					</a>

					<?php
						$area_img = "/web/img/location/area_info_kv_5.jpg";
					?>
					<a href="/location/?f=gift&sf=&areas=west&pageno=1" class="sightseeing" type="gift" target="_blank" style="background-image: url(<?= $area_img ?>);background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">
						<span class="text">台中</span>
					</a>
					</div>
				</div>
			</div>
<?php */?>
		</section>
		<div class="campaignWrap">
			<a href="/traveling" class="campaign"></a>
			<div class="close"></div>
		</div>
	</main>
	<footer><?php include __DIR__ . '/pages/common/footer_new.php';?></footer>
    <script>
    var swiper = new Swiper('#swiper1', {
        pagination: '.swiper-pagination',
        paginationClickable: true
    });
    var swiper2 = new Swiper('#swiper2', {
    	loop: true,//是否循环播放
    	autoplay: 5000,
    	autoplayDisableOnInteraction: false
//     	,onSlideChangeStart: function(swiper){
//         	alert(swiper.activeIndex);
//     		$('a.project[lazyIdx=' + (swiper.activeIndex - 1) + ']').trigger('sporty');
//     	}
    });
    var swiper3 = new Swiper('#swiper3', {
    	loop: true,//是否循环播放
    	autoplay: 4000,
    	autoplayDisableOnInteraction: false
//     	,onSlideChangeStart: function(swiper){
// 			$('a.img[lazyIdx=' + (swiper.activeIndex - 1) + ']').trigger('sporty');
//     	}
    });
    var swiper4 = new Swiper('#swiper4');
    var swiper5 = new Swiper('#swiper5');
    var swiper6 = new Swiper('#swiper6');

    $(function(){
        $('div[lazyName=kv]').lazyload({event:'sporty'});
    	$('a.project').lazyload({event:'sporty'});
    	$('a.img').lazyload({event:'sporty'});
    	$('div.hImg[area=north]').lazyload();
    	$('div.hImg[area!=north]').lazyload({event:'sporty'});
    	$('a.sightseeing').lazyload({event:'sporty'});

    	$('#west').bind('click', function(){$('[area=west]').trigger('sporty');});
    	$('#south').bind('click', function(){$('[area=south]').trigger('sporty');});
    	$('#east').bind('click', function(){$('[area=east]').trigger('sporty');});
    	$('#food').bind('click', function(){$('a.sightseeing[type=food]').trigger('sporty');});

    	var timeout = setTimeout(function() { $('div[lazyName=kv]').trigger("sporty") }, 1000);
    	var timeout = setTimeout(function() { $('a.img').trigger("sporty") }, 1500);
    	var timeout = setTimeout(function() { $('a.project').trigger("sporty") }, 2000);
    	var timeout = setTimeout(function() { $('a.sightseeing[type=spot]').trigger("sporty") }, 2500);

    	//下方行程助手區塊
    	$(".campaignWrap .close").click(function(){
    		$(".campaignWrap").hide();
        });

    	$("#main_banner").click(function(){
    		url = "/web/pages/otr/campaign.php";
        	window.location.href = url;
        });
        $("#hsr").on("click", function() {
            var data = '<?php echo $header_is_login; ?>';
    	    if(data == 1){
    	    	location.href="/hsr/";
    	    }else{
    	        show_popup_login();
    	    }
        });
    });
    </script>
</body>
</html>
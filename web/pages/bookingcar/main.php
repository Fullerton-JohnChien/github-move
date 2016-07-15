<?php
/**
 * 說明：交通預定 包車拼車觀巴 大首頁
 * 作者：Bobby <bobby.luo@fullerton.com.tw>
 * 日期：2016年5月25日
 * 備註：
 */
require_once __DIR__ . '/../../config.php';
header("Content-Type:text/html; charset=utf-8");

// 預設清除 pay_step session
if(!empty($_SESSION["pay_step"])){
	unset($_SESSION["pay_step"]);
}
// 頁面基本參數
$adult_count = 10;
$child_count = 10;

// 頁面傳送資料
$begin_area = get_val('begin_area');
$end_area = get_val('end_area');
$begin_date = get_val('begin_date');
$car_day = get_val('car_day');
$car_adult = get_val('car_adult');
$car_child = get_val('car_child');
$car_adult = ($car_adult == "") ? 1 : $car_adult;
// type: 2(接機), 4(送機)
$pickup_type = get_val('pickup_type');

// 取得區域資料
$area_category = 'car';
$area_dao = Dao_loader::__get_area_dao();
$area_list = $area_dao->findAreasWithLangByCategoryAndParentId(get_config_current_lang(), $area_category, 0);

// 取得機場資料
$area_category = 'car.deliver';
$area_dao = Dao_loader::__get_area_dao();
$area_deliver_list = $area_dao->findAreasWithLangByCategoryAndParentId(get_config_current_lang(), $area_category, 0);

// 包車天數內容(預設間隔0.5)
$day_count = 3;
$day_space = 0.5;
$car_day_list = array();
for ($i = 0.5; $i <= $day_count; $i+=$day_space) {
	$car_day_list[] = $i;
}

$tripitta_web_service = new tripitta_web_service();
// 推薦旅宿類型
$recommend_type_list = $tripitta_web_service->find_recommend_type_homestay_for_booking_home();

$tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1, date("y")));

// mobile 包車資訊3筆
$limit = 3;
$cr_type = 1;
$tripitta_service = new tripitta_service();
$fleet_route_list = $tripitta_service->find_fleet_route_rand_search_list($cr_type, $limit);
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
	<head>
        <link rel="stylesheet" type="text/css" href="/web/pages/location/css/frame.css">
        <style>
            .kv{
                width: 100%;
                height: 100%;
                background: url(/web/img/location/location_banner_food.jpg) 50% 50%/cover no-repeat;
            }
            .selectWrap input{
                border: 0px;
                margin-top: -0.2em;
            }
        </style>
        <title>交通預訂 - Tripitta 旅必達</title>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
        <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
        <meta charset="UTF-8">
        <?php include __DIR__ . "/../common/head_new.php"; ?>
        <link rel="stylesheet" href="/web/css/main.css">
        <link rel="stylesheet" href="/web/css/main2.css">
        <script>
        	var image_server_url = '<?= get_config_image_server() ?>';
        	var recomment_type_list = <?= json_encode($recommend_type_list) ?>;

			$(function () {
				// 搜尋
		        var caneldar_option = <?= json_encode(Constants::$CALENDAR_OPTIONS) ?>;
				$('#begin_date').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
				$('#begin_date_pickup').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});
				$('#begin_date_bus').datepicker(caneldar_option).datepicker('option', {minDate: new Date()});

				// 偵測重新包車查詢按鈕
		        $('#car_search').click(function () {
		            query_car();
		        });

		     	// 偵測重新觀光巴士查詢按鈕
		        $('#pickup_search').click(function () {
		            query_pickup();
		        });

		     	// 偵測重新觀光巴士查詢按鈕
		        $('#bus_search').click(function () {
		            query_bus();
		        });

		     	// 切換包車按鈕
		        $('#car_button').click(function () {
		        	$("#car_button").addClass( "selected" );
		        	$("#shuttle_button").removeClass( "selected" );
		        	$("#bus_button").removeClass( "selected" );
		        	$("#rail_button").removeClass( "selected" );
			        $('#charter').hide();
			        $('#pickUp').hide();
			        $('#tourBus').hide();
			        $('#highWay').hide();
			        $('#charter').show();
		        });

				// 切換接送按鈕
		        $('#shuttle_button').click(function () {
		        	$("#car_button").removeClass( "selected" );
		        	$("#shuttle_button").addClass( "selected" );
		        	$("#bus_button").removeClass( "selected" );
		        	$("#rail_button").removeClass( "selected" );
		        	$('#charter').hide();
			        $('#pickUp').show();
			        $('#tourBus').hide();
			        $('#highWay').hide();
		        });

		     	// 切換觀巴按鈕
		        $('#bus_button').click(function () {
		        	$("#car_button").removeClass( "selected" );
		        	$("#shuttle_button").removeClass( "selected" );
		        	$("#bus_button").addClass( "selected" );
		        	$("#rail_button").removeClass( "selected" );
		        	$('#charter').hide();
			        $('#pickUp').hide();
			        $('#tourBus').show();
			        $('#highWay').hide();
		        });

		     	// 切換高鐵按鈕
		        $('#rail_button').click(function () {
		        	$("#car_button").removeClass( "selected" );
		        	$("#shuttle_button").removeClass( "selected" );
		        	$("#bus_button").removeClass( "selected" );
		        	$("#rail_button").addClass( "selected" );
		        	$('#charter').hide();
			        $('#pickUp').hide();
			        $('#tourBus').hide();
			        $('#highWay').show();
		        });

		        $('.search .toggleBtn').on("click", function(){
		        	hide_all_search_box();
		        	remove_all_search_item();
			    });

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

		     	// 高鐵交通預定 - 重新查詢 - 處理內容
		        query_highway = function () {
		            var url = "/hsr/";
		            location.href = url;
		        }

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

		     	// scroll to 非住不可
		    	$('.trafficIndex-container .fa-angle-down').click(function() {
			    	scrollToConvas('.tourbus');
			    });

		    	// 非住不可
		    	$('.trafficIndex-container .tourbus .menuBar li').each(function() {
		    		var idx = parseInt($(this).attr('data-idx'));
		    		$(this).click(function() { show_recommend_content(idx); });
		    	});
		    	$('.trafficIndex-container .charter .fa-angle-right').click(function() { seek_recommend_content(1); });
		    	$('.trafficIndex-container .charter .fa-angle-left').click(function() { seek_recommend_content(-1); });
		    	$('.trafficIndex-container .tourbus .fa-angle-right').click(function() { seek_recommend_content_bus(1); });
		    	$('.trafficIndex-container .tourbus .fa-angle-left').click(function() { seek_recommend_content_bus(-1); });

		    	if(recomment_type_list.length > 0) {
		    		show_recommend_content(0);
		    		show_recommend_content_bus(0);
		    	}
		    	check_page_open();
			});

			var rh_seek_pos = 0;
			var recomment_type_list = <?= json_encode($recommend_type_list) ?>;

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

			function show_recommend_content(show_idx) {
				rh_seek_pos = show_idx;
				var content_type = '';
				var content_code = '';
				$('.trafficIndex-container .charter .menuBar li').each(function() {
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
				var p = {};
				p.func = 'find_fleet_route_rand_search_list_car';
			    //console.log(p);
				$.post("/web/ajax/ajax.php", p, function(data) {
			        console.log(data);
			        if(data.code == '9999'){
			            alert(data.msg);
			        } else {
			        	var html = '';
						for(var i=0 ; i<data.data.length ; i++) {
							html += get_recommend_homestay_info(data.data[i], i);
							if(i == 0){
								html += '<div class="sImgBlock">';
							}

						}
						html += '</div>';
						$('.trafficIndex-container .charter .wrap').html(html);
			        }
			    }, 'json').done(function() { }).fail(function() { }).always(function() { });
			}

			function get_recommend_homestay_info(obj, idx) {
				var hs_img = '/web/img/no-pic.jpg';
				var car = '<?php echo (is_production() ? 'car' : 'car_alpha') ?>';
				if (obj.cr_main_photo != 0 && obj.cr_main_photo != null) {
					hs_img = image_server_url + '/photos/'+car+'/route/' + obj.cr_id + '/' + obj.cr_main_photo + '.jpg';
				}
				var html = '';
				var class_name = ((idx == 0) ? 'bImg' : 'sImg' );
				html += '<a href="/bookingcar/charter/' + obj.fr_id + '/" class="' + class_name + '" style="background-image: url(' + hs_img + ');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;">';
				html += get_common_homestay_info(obj);
				html += '</a>';
				return html;
			}

			function get_common_homestay_info(obj) {
				var hs_img = '/web/img/no-pic.jpg';
				var car = '<?php echo (is_production() ? 'car' : 'car_alpha') ?>';
				if(obj.cr_main_photo != 0 && obj.cr_main_photo != null) {
					hs_img = image_server_url + '/photos/'+car+'/route/' + obj.cr_id + '/' + obj.cr_main_photo + '.jpg';
				}
				var html = '';
				var i;
				var cnt;
				var str = "";
				var star_class;
				var star = 5;
				if (obj.commont == null) {
					cnt = 0;
				} else {
					cnt = obj.commont;
				}
                for (i = 1; i <= star; i++) {
                	star_class = "fa-star-o";
                    if (i <= parseInt(cnt)) {
                    	star_class = "fa-star";
                    } else {
                        if ((1 - (i - cnt)) > 0 && parseFloat(cnt - i)) {
                        	star_class = "fa-star-half-o";
                        }
                    }
                    str += '<i class="fa '+star_class+'" aria-hidden="true"></i>';
                }
				html += '	<div class="detail">';
				html += '		<div class="stars">';
				html += str;
				html += '			<p class="favorite">';
				html += '		</div>';
				html += '		<h3>'+obj.cr_name+'</h3>';
				html += '		<div class="price">NTD<span class="mark">'+formatNumber(obj.fr_price)+'</span>起</div>';
				html += '	</div>';
				return html;
			}

			function seek_recommend_content_bus(seek) {
				if(rh_seek_pos + seek >= recomment_type_list.length) {
					rh_seek_pos = 0;
				} else if(rh_seek_pos + seek < 0) {
					rh_seek_pos = recomment_type_list.length - 1;
				} else {
					rh_seek_pos += seek;
				}
				show_recommend_content_bus(rh_seek_pos);
			}

			function show_recommend_content_bus(show_idx) {
				rh_seek_pos = show_idx;
				$('.trafficIndex-container .tourbus .menuBar li').each(function() {
					var idx = $(this).attr('data-idx');
					if(idx != show_idx) {
						$(this).removeClass('selected');
						$('#recommend_homestay_' + idx).hide();
					} else {
						$(this).addClass('selected');
					}
				});
				var p = {};
				p.func = 'find_fleet_route_rand_search_list_bus';
			    //console.log(p);
				$.post("/web/ajax/ajax.php", p, function(data) {
			        console.log(data);
			        if(data.code == '9999'){
			            alert(data.msg);
			        } else {
			        	var html = '';
						for(var i=0 ; i<data.data.length ; i++) {
							html += get_recommend_homestay_info_bus(data.data[i], i);
						}
						$('.trafficIndex-container .tourbus .tBlock').html(html);
			        }
			    }, 'json').done(function() { }).fail(function() { }).always(function() { });
			}

			function get_recommend_homestay_info_bus(obj, idx) {
				var hs_img = '/web/img/no-pic.jpg';
				var car = '<?php echo (is_production() ? 'car' : 'car_alpha') ?>';
				if (obj.cr_main_photo != 0 && obj.cr_main_photo != null) {
					hs_img = image_server_url + '/photos/'+car+'/route/' + obj.cr_id + '/' + obj.cr_main_photo + '.jpg';
				}
				var html = '';
				html += '<figure>';
				html += '<a href="/bookingcar/tourbus/' + obj.fr_id + '/" class="img" style="background-image: url(' + hs_img + ');background-position: 50% 50%;background-repeat: no-repeat;background-size: cover;"></a>';
				html += get_common_homestay_info_bus(obj);
				html += '</figure>';
				return html;
			}

			function get_common_homestay_info_bus(obj) {
				var html = '';
				var i;
				var cnt;
				var str = "";
				var star = 5;
				var star_class;
				var car_route_spot = "";
				if (obj.commont == null) {
					cnt = 0;
				} else {
					cnt = obj.commont;
				}
                for (i = 1; i <= star; i++) {
                	star_class = "fa-star-o";
                    if (i <= parseInt(cnt)) {
                    	star_class = "fa-star";
                    } else {
                        if ((1 - (i - cnt)) > 0 && parseFloat(cnt - i)) {
                        	star_class = "fa-star-half-o";
                        }
                    }
                    str += '<i class="fa '+star_class+'" aria-hidden="true"></i>';
                }
                for	(var i=0 ; i<obj.car_route_spot.length ; i++) {
                    if (i < 4) {
                		car_route_spot += '<li><i class="fa fa-map-marker"></i><span>'+obj.car_route_spot[i].crs_name+'</span></li>';
                    }
                }
				html += '	<div class="aLink">';
				html += '		<div class="rank">';
				html += '			<div class="stars">';
				html += str;
				html += '			</div>';
				html += '			<a href="/bookingcar/'+obj.s_id+'/" target="_blank" class="reviews">';
				html += '				看評價';
				html += '			</a>';
				html += '		</div>';
				html += '		<div class="title">每天AM '+obj.fr_boarding_time+' / '+obj.cb_name+'發車</div>';
				html += '		<a href="/bookingcar/tourbus/' + obj.fr_id + '/">';
				html += '			<h3>'+obj.cr_name+'</h3>';
				html += '		</a>';
				html += '		<div class="wrap">';
				html += '			<ul class="location">';
				html += car_route_spot;
				html += '			</ul>';
				html += '			<div class="price">';
				html += '				<div class="subtitle">';
				html += '					優惠價 <span>NTD</span>';
				html += '				</div>';
				html += '				<div class="numWrap">';
				html += '					<div class="nTitle">每人</div>';
				html += '					<div class="num">'+formatNumber(obj.fr_child_net_price)+'</div>';
				html += '				</div>';
				html += '			</div>';
				html += '		</div>';
				html += '	</div>';
				return html;
			}

			function check_page_open(){
				if($(window).width()>=<?php echo $mobile_width; ?>){
					$("#car_button").addClass( "selected" );
				}
			}

			function hide_all_search_box(){
				$('#charter').hide();
				$('#pickUp').hide();
				$('#tourBus').hide();
				$('#highWay').hide();
			}

			function remove_all_search_item(){
				$("#car_button").removeClass( "selected" );
	        	$("#shuttle_button").removeClass( "selected" );
	        	$("#bus_button").removeClass( "selected" );
	        	$("#rail_button").removeClass( "selected" );
			}
		</script>
    </head>
    <body>
    	<header><?php include __DIR__ . "/../common/header_new.php"; ?></header>
		<main class="trafficIndex-container">
			<input type="hidden" id="go_next_page">
			<div class="banner" id="banners" style="height: 500px; background-image: url('../web/img/location/location_banner_event.jpg'); background-size: cover; background-position: center; position: relative;">
				<div class="search">
					<!-- menu -->
					<ul class="sTitle">
						<li id="car_button">
							<div class="iWrap">
								<i class="img-chartercar"></i>
								<i class="img-chartercar-bk"></i>
							</div>
							<div>包車</div>
						</li>
						<li id="shuttle_button">
							<div class="iWrap">
								<i class="img-airportshuttle"></i>
								<i class="img-airportshuttle-bk"></i>
							</div>
							<div>接送機</div>
						</li>
						<li id="bus_button">
							<div class="iWrap">
								<i class="img-sightseeingbus"></i>
								<i class="img-sightseeingbus-bk"></i>
							</div>
							<div>觀光巴士</div>
						</li>
						<li id="rail_button">
							<div class="iWrap">
								<i class="img-highspeedrail"></i>
								<i class="img-highspeedrail-bk"></i>
							</div>
							<div>高鐵票券</div>
						</li>
					</ul>

					<!-- 包車 -->
					<div id="charter" class="sBlock">
						<div class="sbWrap">
							<i class="img-icon-40"></i>
							<select id="begin_area" name="begin_area">
								<option value='' selected>選擇出發地</option>
	                            <?php
	                            if (!empty($area_list)) {
	                                foreach ($area_list as $a) {
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
							<select id="end_area" name="end_area">
								<option value='' selected>選擇目的地</option>
	                            <?php
	                            if (!empty($area_list)) {
	                                foreach ($area_list as $a) {
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
							<i class="img-icon-44"></i>
							<input type="text" id="begin_date" name="begin_date" value="<?php echo $tomorrow; ?>" placeholder="出發日期" class="checkIn" maxlength="20" style="width:150px;" />
						</div>
						<div class="sbWrap">
							<i class="img-icon-43"></i>
							<select id="car_day" name="car_day">
								<option value='' selected>包車天數</option>
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
							<i class="img-icon-38" aria-hidden="true"></i>
							<select id="car_adult" name="car_adult">
								<option value='' selected disabled>大人數</option>
	                            <?php for ($i = 0; $i <= $adult_count; $i++) { ?>
	                                <option value="<?php echo $i; ?>" <?//php echo $car_adult == $i ? 'selected="selected"' : ''; ?>><?php echo $i; ?>人</option>
	                            <?php } ?>
	                        </select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="sbWrap">
							<i class="img-icon-39" aria-hidden="true"></i>
							<select id="car_child" name="car_child">
								<option value="0" selected disabled />五歲以下孩童</option>
	                            <?php for ($i = 0; $i <= $child_count; $i++) { ?>
	                                <option value="<?php echo $i; ?>" <?//php echo $car_child == $i ? 'selected="selected"' : ''; ?>><?php echo $i; ?>人</option>
	                            <?php } ?>
	                        </select>
							<i class="fa fa-angle-down"></i>
						</div>
						<button id="car_search" class="submit">查詢</button>
						<div class="toggleBtn" id="close_up1">
							<i class="fa fa-angle-up"></i>
						</div>
					</div>

					<!-- 接送機 -->
					<div id="pickUp" class="sBlock">
						<div class="sbWrap">
							<i class="img-icon-2" aria-hidden="true"></i>
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
	                            if (!empty($area_list)) {
	                                foreach ($area_list as $a) {
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
	                            if (!empty($area_list)) {
	                                foreach ($area_list as $a) {
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
							<input type="text" id="begin_date_pickup" name="begin_date_pickup" value="<?php echo $tomorrow; ?>" placeholder="出發日期" class="checkIn" maxlength="20" style="width:150px;" />
						</div>
						<div class="sbWrap">
							<i class="img-icon-38" aria-hidden="true"></i>
							<select id="pickup_adult" name="pickup_adult">
								<option value='' selected disabled>大人數</option>
	                            <?php for ($i = 0; $i <= $adult_count; $i++) { ?>
	                                <option value="<?php echo $i; ?>" <?//php echo $car_adult == $i ? 'selected="selected"' : ''; ?>><?php echo $i; ?>人</option>
	                            <?php } ?>
	                        </select>
							<i class="fa fa-angle-down"></i>
						</div>
						<div class="sbWrap">
							<i class="img-icon-39" aria-hidden="true"></i>
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
	                            if (!empty($area_list)) {
	                                foreach ($area_list as $a) {
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
	                            if (!empty($area_list)) {
	                                foreach ($area_list as $a) {
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
							<input type="text" id="begin_date_bus" name="begin_date_bus" value="<?php echo $tomorrow; ?>" placeholder="出發日期" class="checkIn" maxlength="20" style="width:150px;" />
						</div>
						<button id="bus_search" class="submit">查詢</button>
						<div class="toggleBtn" id="close_up3">
							<i class="fa fa-angle-up"></i>
						</div>
					</div>

					<!-- 高鐵 -->
					<div id="highWay" class="sBlock">
						<div class="imgWrap-hsr">
							<img src="/web/img/sec/transport/hsrLogo.png">
						</div>
						<div class="discountBlk-hsr">
							<div class="foreigner">
								外國人
							</div>
							<div class="discountWrap">
								<div class="dNum">75</div>
								<div class="dText">折</div>
							</div>
						</div>
						<ul class="list-hsr">
							<li>價格優惠, 不限時間通通75折起</li>
							<li>快速有效, 今天購買明天使用</li>
							<li>方便彈性, 免指定車次隨時出發</li>
							<li>超長效期, 訂購完成90天有效</li>
						</ul>
						<button id="highway_search" class="submit">購買</button>
						<div class="toggleBtn" id="close_up4">
							<i class="fa fa-angle-up"></i>
						</div>
					</div>
				</div>
			</div>
			<div class="drop">
				<i class="fa fa-angle-down"></i>
			</div>

			<!-- mobile -->
			<section class="charter-m">
				<h2>熱門包車行程</h2>
				<?php
					foreach ($fleet_route_list as $value) {
						$car = (is_production()) ? "car" : "car_alpha";
						$img_url = get_config_image_server()."/photos/".$car."/route/".$value['cr_id']."/".$value['cr_main_photo'].".jpg";
				?>
				<a href="/bookingcar/charter/<?php echo $value['fr_id']; ?>/" class="charterWrap">
					<div class="img" style="background-image:url('<?php echo $img_url; ?>');"></div>
					<div class="detail">
						<h3><?php echo $value['cr_name']; ?></h3>
						<div class="cInfo">
							<div class="stars">
								<?php
                                    $star = 5;
                                    $cnt = !empty($value["commont"]) ? $value["commont"] : 0;
                                    for ($i = 1; $i <= $star; $i++) {
                                        $class = "fa-star-o";
                                        if ($i <= intval($cnt)) {
                                            $class = "fa-star";
                                        } else {
                                            if ((1 - ($i - $cnt)) > 0 && is_float($cnt - $i)) {
                                                $class = "fa-star-half-o";
                                            }
                                        }
                                        ?>
                                        <i class="fa <?php echo $class; ?>" aria-hidden="true"></i>
                                        <?php
	                                }
                                ?>
							</div>
							<div class="price">NTD<span class="mark"><?php echo number_format($value['fr_price']); ?></span>起</div>
						</div>
					</div>
				</a>
				<?php } ?>
			</section>
			<?
				if(!empty($recommend_type_list)) {
			?>
			<section class="tourbus">
				<div class="tInfo">
					<h2>最夯觀光巴士路線</h2>
					<div class="LArrow"><i class="fa fa-angle-left"></i></div>
					<div class="RArrow"><i class="fa fa-angle-right"></i></div>
					<div class="tBlock">
					</div>
				</div>
			</section>
			<section class="charter">
				<div class="cInfo">
					<h2>推薦包車路線</h2>
					<ul class="menuBar" style="display:none;">
						<?
							foreach($recommend_type_list as $idx => $recommend_type_row) {
						?>
						<li <? if($idx == 0) { echo ' class="selected" '; } ?> data-idx="<?= $idx ?>" data-content-type="<?= $recommend_type_row["content_type"] ?>" data-content-code="<?= $recommend_type_row["content_code"] ?>"><?= $recommend_type_row["content_name"] ?></li>
						<?
							}
						?>
					</ul>
					<div class="LArrow"><i class="fa fa-angle-left"></i></div>
					<div class="RArrow"><i class="fa fa-angle-right"></i></div>
					<div class="wrap">
					</div>
				</div>
			</section>
			<?
				}
			?>
		</main>
		<script>
     	// 偵測重新高鐵查詢按鈕
        $('#highway_search').click(function () {
            var data = '<?php echo $header_is_login; ?>';
            $('#go_next_page').val('');
            if(data == 1){
            	 query_highway();
            }else{
            	$('#go_next_page').val('/hsr/');
            	show_popup_login();
            }
        });
		</script>
		<footer><? include __DIR__ . "/../common/footer_new.php"; ?></footer>
		<?php include __DIR__ . '/../common/ga.php';?>
	</body>
</html>
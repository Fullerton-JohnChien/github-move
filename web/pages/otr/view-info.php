<?php
require_once __DIR__ . '/../../config.php';
header("Content-Type:text/html; charset=utf-8");

$tripitta_service = new tripitta_service();
$taiwan_content_service = new taiwan_content_service();
$tripitta_web_service = new tripitta_web_service();
$geographical_coordinates_dao = Dao_loader::__get_geographical_coordinates_dao();
$db_reader_travel = Dao_loader::__get_checked_db_reader();

$id = get_val("hs_id");
$tc_id = get_val("id");
$content_row = $taiwan_content_service -> get_scenic_content_by_id($tc_id);

$tc_type = $content_row["content"]["tc_type"];
$latitude = get_val("latitude");
$longitude = get_val("longitude");
$image_server_url = get_config_image_server();

if(!empty($_SESSION['get_geo'])){
	$get_geo = get_session_val('get_geo');
	$latitude = $get_geo["latitude"];
	$longitude = $get_geo["longitude"];
}


// 取得行程遊記內容
$travel_plan_list = $tripitta_service->find_nearby_plan_by_type_and_content_id($tc_type, $tc_id);

$travel_plan_photo_path = '/travel_plan';
$author_photo_path = '/author';
if(!is_production()) {
	$travel_plan_photo_path = '/travel_plan_alpha';
	$author_photo_path = '/author_alpha';
}

// 判斷是否有效
if($content_row["content"]["tc_status"] != 1) {
	header("Location: https://www.tripitta.com/web/pages/adout/404.php");
	exit();
}

$parent_tag_list = $taiwan_content_service->find_parent_tag_by_content_id(get_config_current_lang(), $tc_id);
$photo_row = $taiwan_content_service->get_content_image_by_id($tc_id);

$plan_count = $taiwan_content_service -> count_plan_by_content_id($tc_id);
$myGc = $geographical_coordinates_dao->getHfGeographicalCoordinatesByCategoryAndReferenceId('taiwan.content', $tc_id);
$lat = isset($myGc['gc_latitude']) ? $myGc['gc_latitude'] : "";
$lng = isset($myGc['gc_longitude']) ? $myGc['gc_longitude'] : "";

// 取得附近旅宿內容
$homestay_list = $tripitta_service->find_nearby_home_stay_by_content_geographical_coordinates($lat, $lng);

// 取得附近交通內容
$transport_list = array();
// $transport_list = $tripitta_service->find_nearby_store_by_content_area_id($lat, $lng);

// 取得地圖上目前座標距離
$distance = null;
if(!empty($latitude) && !empty($longitude) && !empty($lat) && !empty($lng)){
	$distance = $tripitta_service->get_distance($latitude, $longitude, $lat, $lng, 2);
}


// 檢查會員是否登入
$login_user_data = $tripitta_web_service->check_login();

if(!empty($photo_row['list'])) {
	$image_count = 1;
	foreach($photo_row['list'] as $pr) {
		$photoId=$pr['p_id'];
		$photoType=strtolower($pr['p_content_type']);
		if($image_count==1){
			$url = get_config_image_server() . $photo_row['path'] . $tc_id.'/'.$photoId.'.'.$photoType;
		}
		$image_count++;
	}
}
// $img_src = !empty($url) ? $url : "http://placehold.it/600x200";
$img_src = $url;

// 取得tripadvisor資訊
$sql = "SELECT * FROM hf_source_mapping ";
$sql .= "INNER JOIN hf_trip_advisor_review_info ON tari_id = sm_ref_id ";
$sql .= "WHERE sm_source_id IN (" . $tc_id . ") and sm_category = 'tripadvisor.taiwan.content' ";
$tripadvisor_row = $db_reader_travel->executeReader($sql);

$ta_conut = 0;
$ta_img = '';
$tari_id = "";
if(!empty($tripadvisor_row)){
	foreach ($tripadvisor_row as $tr){
		if ($tr['sm_source_id'] == $tc_id) {
			$tari_id = $tr["tari_id"];
			$ta_conut = $tr['tari_review_count'];
			$ta_img = '<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/'.$tr['tari_average_rating'].'-33123-4.gif" style="width:118px;height:20px;padding-top: 10px;"/>';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<?php include __DIR__ . "/../common/head_new.php"; ?>
	<script src="/web/js/lib/jquery/jquery.js"></script>
	<script src="/web/js/main-min.js"></script>
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" href="/web/css/main2.css">
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <script>
	$(function() {
		// map
		<?php if ($lat != "" && $lng != "") { ?>
		map_init();
		<?php } ?>
        $('#prexPage').show();
        $('#mobile_edit').css("display", "block");
        $('#openMenu2').hide();
        $('#menucopy').hide();
        $('#chgngeMap').hide();

        $('#mobile_edit').on("click", function(){
			window.location.href = '/vendor/otr/improve/<?php echo $tc_id; ?>/?hs_id=<?= $id ?>';
        });
	});

	<?php if(empty($get_geo)){ ?>
// 	if (navigator.geolocation) {
// 	    // HTML5 定位抓取
// 	    navigator.geolocation.getCurrentPosition(function(position) {
// 	        mapServiceProvider(position.coords.latitude, position.coords.longitude);
// 	    },
// 	    function(error) {
// 	        switch (error.code) {
// 	            case error.TIMEOUT:
// 	                alert('連線逾時');
// 	                break;

// 	            case error.POSITION_UNAVAILABLE:
// 	                alert('無法取得定位');
// 	                break;

// 	            case error.PERMISSION_DENIED://拒絕
// 	                alert('您尚未允許開啟手機的GPS定位功能!');
// 	                break;

// 	            case error.UNKNOWN_ERROR:
// 	                alert('不明的錯誤，請稍候再試');
// 	                break;
// 	        }
// 	    });
// 	} else { // 不支援 HTML5 定位
// 	    // 若支援 Google Gears
// 	    if (window.google && google.gears) {
// 	        try {
// 	              // 嘗試以 Gears 取得定位
// 	              var geo = google.gears.factory.create('beta.geolocation');
// 	              geo.getCurrentPosition(successCallback,errorCallback, { enableHighAccuracy: true,gearsRequestAddress: true });
// 	        } catch(e){
// 	              alert("定位失敗請稍候再試");
// 	        }
// 	    } else {
// 	        alert("您尚未允許開啟手機的GPS定位功能!");
// 	    }
// 	}
	<?php } ?>

	// 取得 Gears 定位發生錯誤
// 	function errorCallback(err) {
// 	    var msg = 'Error retrieving your location: ' + err.message;
// 	    alert(msg);
// 	}

	// 成功取得 Gears 定位
// 	function successCallback(p) {
// 	    mapServiceProvider(p.latitude, p.longitude);
// 	}

	var map;
	<?php if ($lat != "" && $lng != "") { ?>
	function map_init() {
		var latlng = new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lng; ?>);
		var myOptions = {
	    	zoom: 13,
	    	center: latlng,
	    	mapTypeId: google.maps.MapTypeId.ROADMAP,
	    	scrollwheel: false,
	    	zoomControl: false,
	        streetViewControlOptions: {
	            position: google.maps.ControlPosition.LEFT_TOP
	        }
		};
		var infowindow = new google.maps.InfoWindow({});
	    map = new google.maps.Map(document.getElementById("google_map"), myOptions);
	    var marker = new google.maps.Marker({
	 		position: new google.maps.LatLng(<?php echo $lat?>,<?php echo $lng?>),
	 		//icon: < ?= //$icon ?>,
	 		map: map
	 	});
	 	marker.setMap(map);
	}
	<?php } ?>


	</script>
</head>
<body>
	<header><?php include __DIR__ . "/../common/header_otr.php"; ?></header>
	<main class="otr-viewInfo-container">
		<div class="img" style="background-image: url('<?php echo $img_src; ?>'), url('/web/img/no-pic.jpg');"></div>
		<section class="art">
			<h1><?php echo $content_row["content_tw"]["tc_name"]; ?></h1>
			
			<?php if($ta_img != ''){ ?>
			<a href="https://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=<?=$tari_id?>&partnerId=<?=TRIPADVISOR_PARTNER_ID?>&lang=zh_TW&allowMobile&display=true" class="tripWrap">
				<?php echo $ta_img; ?>
				<!-- <img src="http://placehold.it/118x20" class="tripadvisorRating"> -->
				<div class="count">
					<span><?php echo $ta_conut; ?></span>
					<span>則評論</span>
				</div>
			</a>
			<?php } ?>
			
			<?php /*
			<?php if($ta_img != ''){ ?>
			<?php echo $ta_img; ?>
			<?php //<img src="<?php echo $img_src; ?>" class="tripadvisorRating"> ?>
			<span><?php echo $ta_conut; ?></span><span>則評論</span>
			<?php } ?>
			*/?>
			<div class="artDetail">
				<span>
					共有<span class="artNum"><?php echo $plan_count; ?></span>篇文章提到我
				</span>
				<span>
					<i class="fa fa-heart"></i>
					<span><?php echo number_format($content_row["content"]['tc_collect_total']); ?></span>
				</span>
				<span>
					<i class="fa fa-eye"></i>
					<span><?php echo number_format($content_row["content"]['tc_cnt_click']); ?></span>
				</span>
			</div>
			<div class="tags">
			<?php
			// 最多顯示五筆
			$max_list = 1;
			foreach ($parent_tag_list as $parent_tag_row) {
				if($max_list<=5){
				?>
				<div class="tag"><?php echo $parent_tag_row["t_tag"]; ?></div>
				<?php
				}
				$max_list++;
			}
			?>
			</div>
			<div class="artContent">
			<?php echo nl2br($content_row["content_tw"]["tc_full_desc"]); ?>
			</div>
			<div class="map">
				<div id="google_map" style="width: 100%; height:100%;"> </div>
				<?php if(!empty($distance)){ ?>
				<div class="distance">
					<span class="text">
						距離目前位置<span class="num"> <?php echo $distance; ?> km</span>
					</span>
					<a href="http://maps.google.com/maps?q=<?php echo $lat; ?>,<?php echo $lng; ?>" target="_blank" class="local">
						<i class="fa fa-location-arrow"></i>
					</a>
				</div>
				<?php } ?>
			</div>
			<div class="storesInfo">
				<div class="store">
					<div class="icon">
						<i class="fa fa-map-marker"></i>
					</div>
					<div class="info">
						<?php echo !empty($content_row["content_tw"]["tc_address"]) ? nl2br($content_row["content_tw"]["tc_address"]) : "無"; ?>
					</div>
				</div>
			</div>
			<div class="storesInfo">
				<div class="store">
					<div class="icon">
						<i class="fa fa-phone"></i>
					</div>
					<div class="info">
						<?php echo !empty($content_row["content"]["tc_tel"]) ? nl2br($content_row["content"]["tc_tel"]) : "無"; ?>
					</div>
				</div>
			</div>
			<?php if(!empty($content_row["content_tw"]["tc_open_time"])){ ?>
			<div class="storesInfo">
				<div class="store">
					<div class="icon">
						<i class="fa fa-calendar"></i>
					</div>
					<div class="info">
						<?php echo !empty($content_row["content_tw"]["tc_open_time"]) ? nl2br($content_row["content_tw"]["tc_open_time"]) : "無"; ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<?php if($content_row["content"]["tc_type"] == 8 || $content_row["content"]["tc_type"] == 12 || $content_row["content"]["tc_type"] == 15){ ?>
			<div class="storesInfo">
				<div class="store">
					<div class="icon">
						<i class="fa fa-ticket"></i>
					</div>
					<div class="info">
						<?php echo !empty($content_row["content_tw"]["tc_ticket_info"]) ? nl2br($content_row["content_tw"]["tc_ticket_info"]) : "無"; ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="storesInfo">
				<div class="store">
					<div class="icon">
						<i class="fa fa-bus"></i>
					</div>
					<div class="info">
					<?php echo !empty($content_row["content_tw"]["tc_travelling_info"]) ? nl2br($content_row["content_tw"]["tc_travelling_info"]) : "無"; ?>
					</div>
				</div>
			</div>
			<?php if (!empty($content_row["content_tw"]["tc_parking_info"])) { ?>
			<div class="storesInfo">
				<div class="store">
					<div class="icon">
						<div class="parkingSign">
							P
						</div>
					</div>
					<div class="info">
					<?php echo !empty($content_row["content_tw"]["tc_parking_info"]) ? nl2br($content_row["content_tw"]["tc_parking_info"]) : "無"; ?>
					</div>
				</div>
			</div>
			<?php } ?>
		</section>
		<?php if(!empty($travel_plan_list)){ ?>
		<section class="itinerys">
			<h2>
				<span>行程遊記</span>
				<?php if(count($travel_plan_list)>3){ ?>
				<?php /*
				<a href="javascript:void(0)">
					更多<i class="fa fa-angle-right"></i>
				</a>
				*/ ?>
				<?php } ?>
			</h2>
			<?php
				$travel_plan_count = 1;
				foreach ($travel_plan_list as $tpl){
					if($travel_plan_count <= 3 && trim($tpl["tp_title"])!=""){
						$tp_title = $tpl["tp_title"];
						if(mb_strlen($tp_title, 'utf-8') > 24) {
							$tp_title = mb_substr($tp_title, 0, 24, 'utf-8') . '...';
						}
						$img_travel_plan = '/web/img/no-pic.jpg';
						if($tpl["tp_main_photo"] != null && $tpl["tp_main_photo"] != '' && $tpl["tp_main_photo"] != 0) {
							if($tpl["tpd_type"] == 10){
								$img_travel_plan = $image_server_url . '/photos' . (is_production() ? 'travel' : 'alpha_travel') . '/home_stay/' . $tpl["tp_id"] . '/' . $tpl["tp_main_photo"] . '_middle.jpg';
							}else{
								$img_travel_plan = $image_server_url . '/photos' . $travel_plan_photo_path . '/' . $tpl["tp_id"] . '/data_image/' . $tpl["tp_main_photo"] . '.jpg';
							}
						}
						$img_author_head = null;
						if($tpl["ap_avatar"] != '' && $tpl["ap_avatar"] != '0' && $tpl["ap_avatar"] != null) {
							$img_author_head = sprintf("%s/photos%s/%s/%s.jpg", $image_server_url, $author_photo_path, $tpl["a_id"], $tpl["ap_avatar"]);
						}
						?>
			<a href="/trip/<?php echo $tpl["tpd_id"]; ?>/" class="itinery">
				<div class="img"<?php if(!empty($img_travel_plan) && $img_travel_plan!=""){ ?> style="background-image: url('<?php echo $img_travel_plan; ?>');"<?php } ?>></div>
				<div class="intro">
					<h3>
						<?php echo $tp_title; ?>
					</h3>
					<div class="autohor">
						<div class="img"<?php if(!empty($img_author_head) && $img_author_head!=""){ ?> style="background-image: url('<?php echo $img_author_head; ?>');"<?php } ?>></div>
						<div class="name"><?php echo $tpl["a_nickname"]; ?></div>
					</div>
					<div class="watch">
						<i class="fa fa-eye"></i><span><?php echo number_format($tpl["a_display_order"]); ?></span>
					</div>
				</div>
			</a>
					<?php
					}
					$travel_plan_count++;
				}
			?>
		</section>
		<?php } ?>
		<?php if(!empty($homestay_list)){ ?>
		<section class="hotels">
			<h2>
				<span>附近旅宿</span>
				<?php if(count($homestay_list)>3){ ?>
				<?php /*
				<a href="javascript:void(0)">
					更多<i class="fa fa-angle-right"></i>
				</a>
				*/ ?>
				<?php } ?>
			</h2>
			<?php
				$homestay_count = 1;
				foreach ($homestay_list as $hl){
					if($homestay_count <= 3){
						$hs_name = $hl["hs_name"];
						if(mb_strlen($hs_name, 'utf-8') > 14) {
							$hs_name = mb_substr($hs_name, 0, 14, 'utf-8') . '...';
						}
						$location = null;
						$hs_area = $tripitta_service->get_area_by_id($hl["hs_area_id"]);
						$location = !empty($hs_area) ? $hs_area["a_name"]  : "";
						// $hs_small_area = $tripitta_service->get_area_by_id($hl["hs_small_area_id"]);
						// $location .= !empty($hs_small_area) ? $hs_small_area["a_name"] : "";
						$img = $image_server_url . '/photos/' . 'travel' . '/home_stay/' . $hl["hs_id"] . '/' . $hl['hs_main_photo'] . "_big.jpg";
// 						$img = $image_server_url . '/photos/' . (is_production() ? 'travel' : 'alpha_travel') . '/home_stay/' . $hl["hs_id"] . '/' . $hl['hs_main_photo'] . "_middle.jpg";
						?>
			<a href="/booking/<?php echo $hl["a_code"]; ?>/<?php echo $hl["hs_id"]; ?>/" class="hotel">
				<div class="img"<?php if(!empty($img) && $img!=""){ ?> style="background-image: url('<?php echo $img; ?>');"<?php } ?>></div>
				<div class="info">
					<h3>
						<span class="title"><?php echo $hs_name; ?></span>
						<span class="price">
							<span class="currency">NTD</span>
							<span class="num"><?php echo number_format($hl["hsrp_price"]); ?></span>
						</span>
					</h3>
					<div class="bottom">
						<div class="location">
							<i class="fa fa-map-marker"></i>
							<span class="text"><?php echo $location; ?></span>
						</div>
						<?php /*
						<div class="strikethrough">
							<span>NTD</span>
							<span>12,000</span>
						</div>
						*/ ?>
					</div>
				</div>
			</a>
					<?php
					}
					$homestay_count++;
				}
			?>
		</section>
		<?php } ?>
		<?php if(!empty($transport_list)){ ?>
		<section class="trans">
			<h2>
				<span>附近交通</span>
				<?php if(count($transport_list)>3){ ?>
				<a href="javascript:void(0)">
					更多<i class="fa fa-angle-right"></i>
				</a>
				<?php } ?>
			</h2>
			<?php
				$transport_count = 1;
				foreach ($transport_list as $tl){
					if($transport_count <= 3){
						?>
			<a href="javascript:void(0)" class="transport">
				<div class="img"></div>
				<div class="intro">
					<div class="fleet">
						<i class="fa fa-car"></i>
						<span>台灣大車隊</span>
					</div>
					<h3>
						急行！関西&amp;中部9地10日-賞櫻美食兩不誤大阪奈良宇治京都下呂高山白川鄉神戶姬路姬…
					</h3>
					<div class="rating">
						<div class="stars">
							<i class="fa fa-star"></i>
							<i class="fa fa-star-half-o"></i>
							<i class="fa fa-star-o"></i>
							<i class="fa fa-star-o"></i>
							<i class="fa fa-star-o"></i>
						</div>
						<span class="point">3.5</span>
						<span class="range"> / 5</span>
					</div>
					<div class="price">
						<?php /*
						<div class="strikethrough">
							<span>NTD</span>
							<span>12,000</span>
						</div>
						*/ ?>
						<div class="realPrice">
							<span class="currerncy">NTD</span>
							<span class="currerncy">12,000</span>
						</div>
					</div>
				</div>
			</a>
					<?php
					}
					$transport_count++;
				}
			?>
		</section>
		<?php } ?>
	</main>
	<footer><? include __DIR__ . "/../common/footer_new.php"; ?></footer>
	

<script>
	$(document).ready(function(){
	    //tripAdvisor popup closeBtn
	    $("#tripadvisorPopupCloseBtn").click(function(){
		    $("#tripadvisorPopup").hide();
	        $('.overlay').hide();
		});
        $("a.tripWrap").click(function(e){
			e.preventDefault();
			var tripadvisorUrl = $(this).attr("href");
			open_tripadvisor_popup_by_url(tripadvisorUrl);
	    });
	    $("#prexPage").click(function(){
			history.back();
		});
	});
	
	function open_tripadvisor_popup(tripadvisorLocationId) {
		var src = "https://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=" + tripadvisorLocationId + "&partnerId=<?=TRIPADVISOR_PARTNER_ID?>&lang=zh_TW&allowMobile&display=true";
		$("#tripadvisorPopupIframe").attr("src",src);
		$("#tripadvisorPopup").show();
	}

	function open_tripadvisor_popup_by_url(src) {
        $('.overlay').show();
    	$("#tripadvisorPopupIframe").attr("src",src);
    	$("#tripadvisorPopup").show();
	}
</script>
</body>
</html>
<?php
require_once __DIR__ . '/../../config.php';
header("Content-Type:text/html; charset=utf-8");

// 設置ota session
// setOtrCode();

$tripitta_service = new tripitta_service();

$id = get_val("id");
$type = get_val("type");
$ori_type = $type;
$id = ($id == "") ? 3 : $id;
$type = ($type == "T") ? "R" : $type;
$sort = get_val_with_default('sort', 1);
// 1:廁所 2:行李寄物櫃 3:可換匯銀行 4:wifi
$bot_type = get_val("bot_type");
$latitude = get_val("latitude");
$longitude = get_val("longitude");

$image_server_url = get_config_image_server();

if ($type == "R") {
	$category = "hf_home_stay";
	$home_stay = $tripitta_service->get_home_stay($id);
	$geographical_coordinates = $tripitta_service->get_home_stay_geographical_coordinates($id);
	$gc_latitude = $geographical_coordinates['gc_latitude'];
	$gc_longitude = $geographical_coordinates['gc_longitude'];

	$hs_name = $home_stay['hs_name'];
	$map_name = $hs_name;
	$hs_main_photo = $home_stay['hs_main_photo'];
	$image = $image_server_url."/photos/travel/home_stay/".$id."/".$hs_main_photo."_big.jpg";
} else if ($type == "C") {
	alertmsg("此服務尚未開放", "");
// 	$store = $tripitta_service->get_store($id);
	//print_r($store);
	$sml_name = $store['sml_name'];
	$map_name = $sml_name;
	$store_path = (is_production()) ? "store" : "store_alpha";
	$image = $image_server_url."/photos/".$store_path."/".$id."/logo.jpg";
}

// 未傳送 user 現在座標，以 otr 座標為準
if(!empty($latitude) && !empty($longitude)){
	$view_info_list = $tripitta_service->find_otr_info($bot_type, $latitude, $longitude, $sort);
	$map_latitude = $latitude;
	$map_longitude = $longitude;
}elseif (!empty($gc_latitude) && !empty($gc_longitude)){
	$view_info_list = $tripitta_service->find_otr_info($bot_type, $gc_latitude, $gc_longitude, $sort);
	$map_latitude = $gc_latitude;
	$map_longitude = $gc_longitude;
}

function sort_by_distance($a, $b){
	if($a['distance'] == $b['distance']) return 0;
	return ($a['distance'] > $b['distance']) ? 1 : -1;
}

function sort_by_distance_reverse($a, $b){
	if($a['distance'] == $b['distance']) return 0;
	return ($a['distance'] < $b['distance']) ? 1 : -1;
}

// 重新排序
if($sort==1){
	uasort($view_info_list, 'sort_by_distance');
}else{
	uasort($view_info_list, 'sort_by_distance_reverse');
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
    <link rel="stylesheet" href="/web/css/swiper.min.css">
    <script src="/web/js/swiper.jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <style>
    .swiper-container {
        width: 100%;
    }
    .swiper-slide {
    	width: 300px;
    }
	.botMenu a:hover, .canvas a:hover {javascript:void(0)
    	cursor: pointer;
    }
    </style>
</head>
<body>
	<header><?php include __DIR__ . "/../common/header_new.php"; ?></header>
	<main class="otr-self-info-container" style=" height:100%;">
		<div id="mapPage" class="mapPage" style=" height:100%;">
			<div class="canvas" id="canvas" >
				<div id="google_map" style="width: 100%; height:100%;"> </div>
<!-- 				<a href="javascript:void(0)" class="tag">a123</a> -->
<!-- 				<a href="javascript:void(0)" class="tag selected">b123</a> -->
<!-- 				<a href="javascript:void(0)" class="centerTag">c123</a> -->

				<!-- location  -->
				<a href="http://maps.google.com/maps?q=<?php echo $gc_latitude; ?>,<?php echo $gc_longitude; ?>" target="_blank" class="local">
					<i class="fa fa-location-arrow" aria-hidden="true"></i>
				</a>

				<!-- switch button -->
				<a id="spot_location" class="btn selected">
					<img src="../../../../web/img/icoon-locate-2x.png" style="width:100%;">
					<!-- <i class="fa fa-user"></i> -->
				</a>

				<!-- information block -->
				<div class="viewinfoWrap">
					<div class="viewInfoFrame">
					<?php
					if(!empty($view_info_list)){ ?>
					<div class="swiper-container">
						<div class="swiper-wrapper">
					<?php foreach ($view_info_list as $vil){
						$location = $vil["ct_city_name"];
						if(!empty($location)){
							$location .= "-" . $vil["ct_town_name"];
						}
							?>
							<div class="swiper-slide" style="width:310px;">
						<a href="javascript:void(0)" class="view">
							<div class="info">
								<h3><?php echo $vil["omi_title"]; ?></h3>
								<div class="locationWrap">
									<span class="location">
										<i class="fa fa-map-marker"></i>
										<span><?php echo $location; ?></span>
									</span>
									<span class="distance">
										<?php echo $vil["distance"]; ?>km
									</span>
								</div>
							</div>
						</a>
						</div>
							<?php } ?>
						</div>
					</div>
					<?php } ?>
					</div>
				</div>
				<?php /*
				<div class="swiper-button-next"></div>
        		<div class="swiper-button-prev"></div>
        		*/ ?>
			</div>
		</div>

		<!-- 底部選單 -->
		<div class="botMenu">
			<a id="bot_menu_change" data-type="3" class="btn">
				<div class="iconWrap">
					<i class="img-icon-change-w"></i>
				</div>
				<div class="text">換匯</div>
			</a>
			<a id="bot_menu_wc" data-type="1" class="btn">
				<div class="iconWrap">
					<i class="img-icon-wc-w"></i>
				</div>
				<div class="text">廁所</div>
			</a>
			<a id="bot_menu_bag" data-type="2" class="btn">
				<div class="iconWrap">
					<i class="img-icon-bag-w"></i>
				</div>
				<div class="text">行李</div>
			</a>
			 <a id="bot_menu_wifi" data-type="4" class="btn">
				<div class="iconWrap">
					<i class="img-icon-wifi-w"></i>
				</div>
				<div class="text">wifi</div>
			</a>
			<!--<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-more"></i>
				</div>
				<div class="text">更多</div>
			</a> -->
		</div>

		<!-- 底部更多選單 -->
		<!-- <div class="botMoreMenu">
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-change"></i>
				</div>
				<div class="text">換匯</div>
			</a>
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-wc"></i>
				</div>
				<div class="text">廁所</div>
			</a>
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-bag"></i>
				</div>
				<div class="text">行李</div>
			</a>
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-wifi"></i>
				</div>
				<div class="text">wifi</div>
			</a>
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-post"></i>
				</div>
				<div class="text">郵局</div>
			</a>
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-hospital"></i>
				</div>
				<div class="text">醫院</div>
			</a>
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-gas"></i>
				</div>
				<div class="text">加油站</div>
			</a>
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-police"></i>
				</div>
				<div class="text">警察局</div>
			</a>
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap selected">
					<i class="img-icon-medicine"></i>
				</div>
				<div class="text">藥局</div>
			</a>
			<a href="javascript:void(0)" class="btn">
				<div class="iconWrap">
					<i class="img-icon-park"></i>
				</div>
				<div class="text">停車場</div>
			</a>
		</div> -->
	</main>
	<footer><? include __DIR__ . "/../common/footer_new.php"; ?></footer>
	<script>
		var latitude = '<?php echo $latitude; ?>';
		var longitude = '<?php echo $longitude; ?>';
		var type = '<?php echo $type; ?>';
		var id = '<?php echo $id; ?>';
		var all_location = [];
		//var myIcon = '/../../web/img/meicon.png';
		var myIcon = '/../../web/img/location-point.png';
		var haveRoomIcon = '/../../web/img/bnb.png';
		//var noRoomIcon = '/../../web/img/bnb_off.fw.png';
		var noRoomIcon = '/../../web/img/pin-inactive.png';
		//var activityIcon = '/../../web/img/activity.png';
		var activityIcon = '/../../web/img/pin-acive.png';
		//var viewpointIon = '/../../web/img/viewpoint.png';
		var viewpointIon = '/../../web/img/pin-acive.png';
		//var foodIcon = '/../../web/img/food.png';
		var foodIcon = '/../../web/img/pin-acive.png';
		var gitIcon = '/../../web/img/git.png';
		//var viewpointIon_c = '/../../web/img/food.png';
		var viewpointIon_c = '/../../web/img/pin-acive.png';
		var swiper = null;
		var curClickPoint = 0;
		var markers = [];
		var map;

    	$(function () {
	        // bot menu button
	        <?php if ($ori_type == "T") { ?>
	        $('[id^="bot_menu_"]').on('click', function(){
				var bot_type = $(this).data('type');
				url = '/traveling/info/';
				url += bot_type + '/?latitude=' + latitude + '&longitude=' + longitude;
				window.location.href = url;
		    });
	        <?php } else { ?>
	        $('[id^="bot_menu_"]').on('click', function(){
				var bot_type = $(this).data('type');
				url = '/vendor/' + type + id + '/info/';
				url += bot_type + '/?latitude=' + latitude + '&longitude=' + longitude;
				window.location.href = url;
		    });
	        <?php } ?>

			if ((/(iPhone|iPad|iPod)/i.test(navigator.userAgent)) && 
				(navigator.appVersion.indexOf('CriOS') < 0)) {
				$('.canvas').css('height', 'calc(100vh - 185px)');
			} else {
				$('.canvas').css('height', 'calc(100vh - 114px)');
			}

	     	// 地圖上切換位置
	        $("#spot_location").on("click", function(){
	        	map_all_location('<?php echo $map_latitude; ?>', '<?php echo $map_longitude; ?>', 'google_map');
		    });

	        map_all_location('<?php echo $map_latitude; ?>', '<?php echo $map_longitude; ?>', "google_map");
	        $('#chgngeMap').hide();
			// 2016/6/26 - howard add begin
	        swiper = new Swiper('.swiper-container', {
	            paginationClickable: true,
	            nextButton: '.swiper-button-next',
	            prevButton: '.swiper-button-prev',
				onSlideChangeEnd: function(swiper) {
					console.log('onSlideChangeStart init:' + curClickPoint);
					markers[curClickPoint].setIcon(noRoomIcon);
					curClickPoint = swiper.activeIndex + 1;
					markers[curClickPoint].setIcon(viewpointIon_c);
					map.setCenter(markers[curClickPoint].getPosition());
				}
	        });

	        $(window).resize(function(){
	        	$('.swiper-wrapper .swiper-slide').css("width", "620px");
	        });

			// 2016/6/26 - howard add end
			// 預設顯示 sort 狀態
	        chang_header_sort('<?php echo $sort; ?>');
	        $('#prexPage').show();
	        $('#openMenu2').hide();
			$('.footer-container').hide();
    	});

    	function map_all_location(latitude, longitude, map_id){
    		var latlng = new google.maps.LatLng(latitude, longitude);
    		var myOptions = {
    			zoom: 15,
    	    	center: latlng,
    	    	mapTypeId: google.maps.MapTypeId.ROADMAP,
    	    	scrollwheel: false,
    	    	zoomControl: false,
    	        streetViewControlOptions: {
    	            position: google.maps.ControlPosition.LEFT_TOP
    	        }
    		};
    		map = new google.maps.Map(document.getElementById(map_id), myOptions);
    	    <?php
    	    // 設定content marker
    	    $idx = 0;
    	    if (!empty($view_info_list)) {
    	    	foreach ($view_info_list as $vil) {
    	    		$idx++;
					if ($idx == 1) {
					?>
					curClickPoint = 1;
					markers[<?php echo $idx; ?>] = new google.maps.Marker({
	    	    		position: new google.maps.LatLng(<?php echo $vil['gc_latitude']; ?>, <?php echo $vil['gc_longitude']; ?>),
	    	    		title: '<?php echo preg_replace("/'/", "\\'", $vil['omi_title']); ?>',
	    	    		icon: viewpointIon_c
	    	    	});
					<?php
					} else {
    	    		?>
					markers[<?php echo $idx; ?>] = new google.maps.Marker({
	    	    		position: new google.maps.LatLng(<?php echo $vil['gc_latitude']; ?>, <?php echo $vil['gc_longitude']; ?>),
	    	    		title: '<?php echo preg_replace("/'/", "\\'", $vil['omi_title']); ?>',
	    	    		icon: noRoomIcon
	    	    	});
	    	    	<?php } ?>
					markers[<?php echo $idx; ?>].setMap(map);
					markers[<?php echo $idx; ?>].addListener('click', function() {
						swiper.slideTo('<?php echo ($idx - 1); ?>');
					});
    	    		<?php
					}
				}
			?>
			var marker = new google.maps.Marker({
    	 		position: new google.maps.LatLng(latitude, longitude),
    	 		title: '<?php echo preg_replace("/'/", "\\'", $map_name); ?>',
    	 		icon: myIcon,
    	 		map: map
    	 	});
    	    marker.setMap(map);
    	}

   	</script>
</body>
</html>
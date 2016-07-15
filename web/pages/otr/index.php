<?php
/**
 * 說明：
 * 作者：Steak
 * 日期：2016年6月19日
 * 備註：type: C:包拼車 R:民宿
 */
require_once __DIR__ . '/../../config.php';
header("Content-Type:text/html; charset=utf-8");
// $dbReader = new pdo_reader($db_dsn_ezding, $db_uid_ezding, $db_pwd_ezding);

$id = get_val("id");
$type = get_val("type");
$sort = get_val("sort");
$latitude = get_val("latitude");
$longitude = get_val("longitude");

if ($latitude != "" && $longitude != "") {
	$_SESSION['get_geo']['latitude'] = $latitude;
	$_SESSION['get_geo']['longitude'] = $longitude;
	
	$my_latitude = $latitude;
	$my_longitude = $longitude;
}
$image_server_url = get_config_image_server();
// 設置ota session
setOtrCode();

$tripitta_service = new tripitta_service();

if ($type == "R") {
	$category = "hf_home_stay";
	$home_stay = $tripitta_service->get_home_stay($id);

	// 取得溫度
	$cit_town_id = $home_stay["hs_city_town_id"];
	$city_towb_dao = Dao_loader::__get_city_down_dao();
	$cit_town_row = $city_towb_dao -> loadHfCityTown($cit_town_id);
	$parent_id = $cit_town_row["ct_parent_id"];
	$cit_town_partnet_row = $city_towb_dao -> loadHfCityTown($parent_id);

	$geographical_coordinates = $tripitta_service->get_home_stay_geographical_coordinates($id);
	$gc_latitude = $geographical_coordinates['gc_latitude'];
	$gc_longitude = $geographical_coordinates['gc_longitude'];
	$area_id = $home_stay["hs_area_id"];
	$search_latitude = !empty($latitude) ? $latitude : $gc_latitude;
	$search_longitude = !empty($longitude) ? $longitude : $gc_longitude;

	$recommend_type = 8;
	$spot_content = $tripitta_service->find_my_recommend_content_by_type($category, $id, $recommend_type, $search_latitude, $search_longitude);
	$spot_content_first = $spot_content;
	if (empty($spot_content)) {
 		$spot_content = $tripitta_service->find_my_recommend_nearby_content_by_type($area_id, $recommend_type, $search_latitude, $search_longitude);
	}
	$recommend_type = 7;
	$food_content = $tripitta_service->find_my_recommend_content_by_type($category, $id, $recommend_type, $search_latitude, $search_longitude);
	$food_content_first = $food_content;
	if (empty($food_content)) {
 		$food_content = $tripitta_service->find_my_recommend_nearby_content_by_type($area_id, $recommend_type, $search_latitude, $search_longitude);
	}
	$hs_name = $home_stay['hs_name'];
	$viewpoint_title = preg_replace("/'/", "\\'", $hs_name);
	$hs_main_photo = $home_stay['hs_main_photo'];
	$image = $image_server_url."/photos/travel/home_stay/".$id."/".$hs_main_photo."_big.jpg";
} else if ($type == "C") {
	alertmsg("此服務尚未開放", "");
	$store = $tripitta_service->get_store($id);
	$sml_name = $store['sml_name'];
	$store_path = (is_production()) ? "store" : "store_alpha";
	$image = $image_server_url."/photos/".$store_path."/".$id."/logo.jpg";
}

$temp = 30;
if(!empty($cit_town_partnet_row)) $temp = round($cit_town_partnet_row["ct_temperature"]);

function sort_by_distance($a, $b){
	if($a['distance'] == $b['distance']) return 0;
	return ($a['distance'] > $b['distance']) ? 1 : -1;
}

function sort_by_distance_reverse($a, $b){
	if($a['distance'] == $b['distance']) return 0;
	return ($a['distance'] < $b['distance']) ? 1 : -1;
}

// 重新排序
if (!empty($sort)) {
	if ($sort==1) {
		uasort($spot_content, 'sort_by_distance_reverse');
		uasort($food_content, 'sort_by_distance_reverse');
	} else {
		uasort($spot_content, 'sort_by_distance');
		uasort($food_content, 'sort_by_distance');
	}
}
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
<? include __DIR__ . "/../common/head_new.php"; ?>
	<meta charset="UTF-8">
	<script src="/web/js/lib/jquery/jquery.js"></script>
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" href="/web/css/main2.css">
    <link rel="stylesheet" href="/web/css/swiper.min.css">
    <script src="/web/js/swiper.jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <style>
    .swiper-container-spot, .swiper-container-food {
        width: 100%;
/*         height: 320px; */
    }
    .swiper-slide {
    	width: 320px;
    }
    .swtichFrame a:hover, .botMenu a:hover{
    	cursor: pointer;
    }
    </style>
</head>
<body>
	<header><?php include __DIR__ . "/../common/header_new.php"; ?></header>
	<main class="otr-index-container">
	<input type="hidden" id="go_next_page">
		<!-- 列表頁 -->
		<div id="listPage" class="listPage" style="display: block;">
			<input type="hidden" id="id" value="<?php echo $id; ?>">
			<input type="hidden" id="type" value="<?php echo $type; ?>">
			<input type="hidden" id="category" value="<?php echo $category; ?>">
			<input type="hidden" id="latitude" value="<?php echo $latitude; ?>">
			<input type="hidden" id="longitude" value="<?php echo $longitude; ?>">
			<input type="hidden" id="gc_latitude" value="<?php echo $gc_latitude; ?>">
			<input type="hidden" id="gc_longitude" value="<?php echo $gc_longitude; ?>">
			<?php if ($type == "R") { ?>
			<section class="top">
				<div class="banner" style="background-image:url('<?php echo $image; ?>');">
					<div class="mask"></div>
					<div class="temprature"><?= $temp ?>°C</div>
					<div class="hotName"><?php echo $hs_name; ?></div>
					<!-- <div class="fqbtn">FAQ</div> -->
				</div>
			</section>
			<?php } else if ($type == "C") { ?>
			<section class="top">
				<div class="banner" style="background-image:url('<?php echo $image; ?>');">
					<div class="mask"></div>
					<div class="temprature"><?= $xml->temp_c ?>°C</div>
					<div class="hotName"><?php echo $sml_name; ?></div>
					<!-- <div class="fqbtn">FAQ</div> -->
				</div>
			</section>
			<?php } ?>
			<section class="connect">
				<ul class="blk">
					<li id="gotoCharter">
						<span class="img-otr-car"></span>
						<div class="text">包車</div>
					</li>
					<li id="gotoTickup">
						<span class="img-icon-airplan"></span>
						<div class="text">接送機</div>
					</li>
					<li id="gotoBus">
						<span class="img-otr-bus"></span>
						<div class="text">觀光巴士</div>
					</li>
					<li id="gotoHsr">
						<span class="img-otr-high"></span>
						<div class="text">高鐵票券</div>
					</li>
				</ul>
			</section>

			<!-- scroll超過時候會改為置頂 -->
			<section class="categoryBlk" id="categoryBlk">
				<ul class="category">
					<a href="javascript:void(0)">
						<li id="spot" class="selected">景點</li>
					</a>
					<a href="javascript:void(0)">
						<li id="food">美食</li>
					</a>
					<!--
					<a href="javascript:void(0)">
						<li>活動</li>
					</a>
					<a href="javascript:void(0)">
						<li>伴手禮</li>
					</a>
					-->
				</ul>
			</section>
			<section id="spot_list" class="content">
				<?php
					foreach ($spot_content as $value) {
						$taiwan_content = (is_production()) ? "taiwan_content" : "taiwan_content_alpha";
						$image = $image_server_url."/photos/".$taiwan_content."/".$value['id']."/".$value['main_photo'].".jpg";
				?>
				<div>
					<a href="javascript:void(0)" class="img" style="background-image:url('<?php echo $image; ?>'), url('/web/img/no-pic.jpg');" onclick="redirect('<?php echo $value['id']; ?>');"></a>
					<div class="info">
						<h3><?php echo $value['name']; ?></h3>
						<?php if(!empty($value["tari_id"])) { ?>
						<a href="https://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=<?=$value["tari_id"]?>&partnerId=<?=TRIPADVISOR_PARTNER_ID?>&lang=zh_TW&allowMobile&display=true" class="tripWrap">
							<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $value["tari_average_rating"] ?>-33123-4.gif" class="tripadvisorRating">
							<div class="count">
								<span><?= $value["tari_review_count"] ?></span>
								<span>則評論</span>
							</div>
						</a>
						<?php } ?>
						<div class="locationWrap">
							<span class="noML">
								<i class="fa fa-map-marker"></i>
								<span><?php echo $value['a_name']; ?></span>
							</span>
							<span>
								<i class="fa fa-heart"></i>
								<span><?php echo number_format($value['cnt_collect']); ?></span>
							</span>
							<span>
								<i class="fa fa-eye"></i>
								<span><?php echo number_format($value['cnt_view']); ?></span>
							</span>
						</div>
						<?php if(!empty($value["distance"])) { ?>
						<div id="distance_<?php echo $value["id"]; ?>" class="distanceWrap"<?php if($value["distance"]==0){ echo ' style="display:none;"'; } ?>>
							<span class="dText">
								距離目前位置
							</span>
							<span id="distance_text_<?php echo $value["id"]; ?>" class="dNum"><?php echo $value["distance"]; ?> km</span>
						</div>
						<?php } ?>
					</div>
				</div>
				<!-- ------------------------------------------------------- -->
				<?php /*
				<a href="javascript:void(0)" onclick="redirect('<?php echo $value['id']; ?>');">
					<div class="img" style="background-image:url('<?php echo $image; ?>');">

					</div>
					<div class="info">
						<h3 style="line-height: 1.43;color: #222222;font-weight: normal;"><?php echo $value['name']; ?></h3>
						<?php if(!empty($value["tari_id"])) { ?>
						<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $value["tari_average_rating"] ?>-33123-4.gif" class="tripadvisorRating" />
						<div class="count">
							<span><?= $value["tari_review_count"] ?></span>
							<span>則評論</span>
						</div>
						<?php } ?>
						<div class="locationWrap" style="line-height: 1.64">
							<span class="noML">
								<i class="fa fa-map-marker"></i>
								<span><?php echo $value['a_name']; ?></span>
							</span>
							<span>
								<i class="fa fa-heart"></i>
								<span><?php echo number_format($value['cnt_collect']); ?></span>
							</span>
							<span>
								<i class="fa fa-eye"></i>
								<span><?php echo number_format($value['cnt_view']); ?></span>
							</span>
						</div>
						<?php if(!empty($value["distance"])) { ?>
						<div id="distance_<?php echo $value["id"]; ?>" class="distanceWrap"<?php if($value["distance"]==0){ echo ' style="display:none;"'; } ?>>
							<span class="dText">
								距離目前位置
							</span>
							<span id="distance_text_<?php echo $value["id"]; ?>" class="dNum"><?php echo $value["distance"]; ?> km</span>
						</div>
						<?php } ?>
					</div>
				</a>
				*/?>
				<?php } ?>
			</section>
			<section id="food_list" class="content" style="display:none;">
				<?php
					foreach ($food_content as $value) {
						$taiwan_content = (is_production()) ? "taiwan_content" : "taiwan_content_alpha";
						$image = $image_server_url."/photos/".$taiwan_content."/".$value['id']."/".$value['main_photo'].".jpg";
				?>
				<div>
					<a href="javascript:void(0)" class="img" style="background-image:url('<?php echo $image; ?>'), url('/web/img/no-pic.jpg');" onclick="redirect('<?php echo $value['id']; ?>');"></a>
					<div class="info">
						<h3><?php echo $value['name']; ?></h3>
						<?php if(!empty($value["tari_id"])) { ?>
						<a href="https://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=<?=$value["tari_id"]?>&partnerId=<?=TRIPADVISOR_PARTNER_ID?>&lang=zh_TW&allowMobile&display=true" class="tripWrap">
							<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $value["tari_average_rating"] ?>-33123-4.gif" class="tripadvisorRating">
							<div class="count">
								<span><?= $value["tari_review_count"] ?></span>
								<span>則評論</span>
							</div>
						</a>
						<?php } ?>
						<div class="locationWrap">
							<span class="noML">
								<i class="fa fa-map-marker"></i>
								<span><?php echo $value['a_name']; ?></span>
							</span>
							<span>
								<i class="fa fa-heart"></i>
								<span><?php echo number_format($value['cnt_collect']); ?></span>
							</span>
							<span>
								<i class="fa fa-eye"></i>
								<span><?php echo number_format($value['cnt_view']); ?></span>
							</span>
						</div>
						<?php if(!empty($value["distance"])) { ?>
						<div id="distance_<?php echo $value["id"]; ?>" class="distanceWrap"<?php if($value["distance"]==0){ echo ' style="display:none;"'; } ?>>
							<span class="dText">
								距離目前位置
							</span>
							<span id="distance_text_<?php echo $value["id"]; ?>" class="dNum"><?php echo $value["distance"]; ?> km</span>
						</div>
						<?php } ?>
					</div>
				</div>
				<!-- ------------------------------------------------------- -->
				<?php /*
				<a href="javascript:void(0)" onclick="redirect('<?php echo $value['id']; ?>');">
					<div class="img" style="background-image:url('<?php echo $image; ?>');">

					</div>
					<div class="info">
						<h3 style="line-height: 1.43;color: #222222;font-weight: normal;"><?php echo $value['name']; ?></h3>
						<?php if(!empty($value["tari_id"])) { ?>
						<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $value["tari_average_rating"] ?>-33123-4.gif" class="tripadvisorRating" />
						<div class="count">
							<span><?= $value["tari_review_count"] ?></span>
							<span>則評論</span>
						</div>
						<?php } ?>
						<div class="locationWrap" style="line-height: 1.64">
							<span class="noML">
								<i class="fa fa-map-marker"></i>
								<span><?php echo $value['a_name']; ?></span>
							</span>
							<span>
								<i class="fa fa-heart"></i>
								<span><?php echo number_format($value['cnt_collect']); ?></span>
							</span>
							<span>
								<i class="fa fa-eye"></i>
								<span><?php echo number_format($value['cnt_view']); ?></span>
							</span>
						</div>
						<?php if(!empty($value["distance"])) { ?>
						<div id="distance_<?php echo $value["id"]; ?>" class="distanceWrap"<?php if($value["distance"]==0){ echo ' style="display:none;"'; } ?>>
							<span class="dText">
								距離目前位置
							</span>
							<span id="distance_text_<?php echo $value["id"]; ?>" class="dNum"><?php echo $value["distance"]; ?> km</span>
						</div>
						<?php } ?>
					</div>
				</a>
				*/?>
				<?php } ?>
			</section>
		</div>

		<!-- 地圖頁 -->
		<div id="mapPage" class="mapPage">

			<section class="categoryBlk" id="categoryBlk2">
				<ul class="category">
					<a href="javascript:void(0)" >
						<li id="map_spot" class="selected">景點</li>
					</a>
					<a href="javascript:void(0)" >
						<li id="map_food">美食</li>
					</a>
					<!-- <a href="javascript:void(0)">
						<li>活動</li>
					</a>
					<a href="javascript:void(0)">
						<li>伴手禮</li>
					</a> -->
				</ul>
			</section>
			<div class="canvas">
				<div id="google_map" style="width: 100%; height:100%;"> </div>
				<!--
				<a href="javascript:void(0)" class="tag">123</a>
				<a href="javascript:void(0)" class="tag selected">123</a>
				<a href="javascript:void(0)" class="centerTag">123</a>
				-->
				<!-- location  -->
				<a href="http://maps.google.com/maps?q=<?php echo $gc_latitude; ?>,<?php echo $gc_longitude; ?>" target="_blank" class="local">
					<i class="fa fa-location-arrow" aria-hidden="true"></i>
				</a>
				<!-- switch button -->
				<div class="swtichFrame">
					<a id="now_location" class="btn">
						<i class="fa fa-user"></i>
					</a>
					<a id="spot_location" class="btn selected">
						<i class="fa fa-home"></i>
					</a>
				</div>

				<!-- information block -->
				<div id="viewinfoWrap" class="viewinfoWrap">
					<div id="map_spot_list" class="viewInfoFrame">
						<div class="swiper-container-spot">
							<div class="swiper-wrapper">
							<?php
								foreach ($spot_content as $value) {
									$taiwan_content = (is_production()) ? "taiwan_content" : "taiwan_content_alpha";
									$image = $image_server_url."/photos/".$taiwan_content."/".$value['id']."/".$value['main_photo'].".jpg";
							?>
								<div class="swiper-slide">
									<div class="view" style="width:315px;">
										<a href="javascript:void(0)" class="img" style="background-image:url('<?php echo $image; ?>'), url('/web/img/no-pic.jpg');" onclick="redirect('<?php echo $value['id']; ?>');"></a>
										<div class="info">
											<h3><?php echo $value['name']; ?></h3>
											<?php if(!empty($value["tari_id"])) { ?>
												<a href="https://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=<?=$value["tari_id"]?>&partnerId=<?=TRIPADVISOR_PARTNER_ID?>&lang=zh_TW&allowMobile&display=true" class="tripWrap">
													<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $value["tari_average_rating"] ?>-33123-4.gif" class="tripadvisorRating">
													<div class="count">
														<span><?= $value["tari_review_count"] ?></span>
														<span>則評論</span>
													</div>
												</a>
											<?php } ?>
											<div class="locationWrap">
												<span class="noML">
													<i class="fa fa-map-marker"></i>
													<span><?php echo $value['a_name']; ?></span>
												</span>
												<span>
													<i class="fa fa-heart"></i>
													<span><?php echo number_format($value['cnt_collect']); ?></span>
												</span>
												<span>
													<i class="fa fa-eye"></i>
													<span><?php echo number_format($value['cnt_view']); ?></span>
												</span>
											</div>
											<?php if(!empty($value["distance"])) { ?>
											<div id="distance_map_<?php echo $value["id"]; ?>" class="distanceWrap"<?php if($value["distance"]==0){ echo ' style="display:none;"'; } ?>>
												<span class="dText">
													距離目前位置
												</span>
												<span id="distance_map_text_<?php echo $value["id"]; ?>" class="dNum"><?php echo $value["distance"]; ?> km</span>
											</div>
											<?php }?>
										</div>
									</div>
								<!-- -------------------------------------------------------------------------------- -->
									<?php /*
									<a href="javascript:void(0)" class="view" onclick="redirect('<?php echo $value['id']; ?>');" style="width:315px;">
										<div class="img" style="background-image:url('<?php echo $image; ?>');"></div>
										<div class="info">
											<h3 style="line-height: 1.43;"><?php echo $value['name']; ?></h3>
											<?php if(!empty($value["tari_id"])) { ?>
											<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $value["tari_average_rating"] ?>-33123-4.gif" class="tripadvisorRating" />
											<div class="count">
												<span><?= $value["tari_review_count"] ?></span>
												<span>則評論</span>
											</div>
											<?php } ?>
											<div class="locationWrap" style="line-height: 1.64;">
												<span class="noML">
													<i class="fa fa-map-marker"></i>
													<span><?php echo $value['a_name']; ?></span>
												</span>
												<span>
													<i class="fa fa-heart"></i>
													<span><?php echo number_format($value['cnt_collect']); ?></span>
												</span>
												<span>
													<i class="fa fa-eye"></i>
													<span><?php echo number_format($value['cnt_view']); ?></span>
												</span>
											</div>
											<?php if(!empty($value["distance"])) { ?>
											<div id="distance_map_<?php echo $value["id"]; ?>" class="distanceWrap"<?php if($value["distance"]==0){ echo ' style="display:none;"'; } ?>>
												<span class="dText">
													距離目前位置
												</span>
												<span id="distance_map_text_<?php echo $value["id"]; ?>" class="dNum"><?php echo $value["distance"]; ?> km</span>
											</div>
											<?php }?>
										</div>
									</a>*/?>
								</div>
							<?php } ?>
							</div>
						</div>
					</div>
					<div id="map_food_list" class="viewInfoFrame" style="display:none;">
						<div class="swiper-container-food">
							<div class="swiper-wrapper">
							<?php
								foreach ($food_content as $value) {
									$taiwan_content = (is_production()) ? "taiwan_content" : "taiwan_content_alpha";
									$image = $image_server_url."/photos/".$taiwan_content."/".$value['id']."/".$value['main_photo'].".jpg";
							?>
							<div class="swiper-slide">
								<div class="view" style="width:315px;">
									<a href="javascript:void(0)" class="img" style="background-image:url('<?php echo $image; ?>'), url('/web/img/no-pic.jpg');" onclick="redirect('<?php echo $value['id']; ?>');"></a>
									<div class="info">
										<h3><?php echo $value['name']; ?></h3>
										<?php if(!empty($value["tari_id"])) { ?>
											<a href="https://www.tripadvisor.com/WidgetEmbed-cdspropertydetail?locationId=<?=$value["tari_id"]?>&partnerId=<?=TRIPADVISOR_PARTNER_ID?>&lang=zh_TW&allowMobile&display=true" class="tripWrap">
												<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $value["tari_average_rating"] ?>-33123-4.gif" class="tripadvisorRating">
												<div class="count">
													<span><?= $value["tari_review_count"] ?></span>
													<span>則評論</span>
												</div>
											</a>
										<?php } ?>
										<div class="locationWrap">
											<span class="noML">
												<i class="fa fa-map-marker"></i>
												<span><?php echo $value['a_name']; ?></span>
											</span>
											<span>
												<i class="fa fa-heart"></i>
												<span><?php echo number_format($value['cnt_collect']); ?></span>
											</span>
											<span>
												<i class="fa fa-eye"></i>
												<span><?php echo number_format($value['cnt_view']); ?></span>
											</span>
										</div>
										<?php if(!empty($value["distance"])) { ?>
										<div id="distance_map_<?php echo $value["id"]; ?>" class="distanceWrap"<?php if($value["distance"]==0){ echo ' style="display:none;"'; } ?>>
											<span class="dText">
												距離目前位置
											</span>
											<span id="distance_map_text_<?php echo $value["id"]; ?>" class="dNum"><?php echo $value["distance"]; ?> km</span>
										</div>
										<?php }?>
									</div>
								</div>
								<!-- -------------------------------------------------------------------------------- -->
								<?php /*
								<a href="javascript:void(0)" class="view" onclick="redirect('<?php echo $value['id']; ?>');" style="width:315px;">
									<div class="img" style="background-image:url('<?php echo $image; ?>');"></div>
									<div class="info">
										<h3 style="line-height: 1.43;"><?php echo $value['name']; ?></h3>
										<?php if(!empty($value["tari_id"])) { ?>
										<img src="http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/<?= $value["tari_average_rating"] ?>-33123-4.gif" class="tripadvisorRating" />
										<div class="count">
											<span><?= $value["tari_review_count"] ?></span>
											<span>則評論</span>
										</div>
										<?php } ?>
										<div class="locationWrap" style="line-height: 1.64;">
											<span class="noML">
												<i class="fa fa-map-marker"></i>
												<span><?php echo $value['a_name']; ?></span>
											</span>
											<span>
												<i class="fa fa-heart"></i>
												<span><?php echo number_format($value['cnt_collect']); ?></span>
											</span>
											<span>
												<i class="fa fa-eye"></i>
												<span><?php echo number_format($value['cnt_view']); ?></span>
											</span>
										</div>
										<?php if(!empty($value["distance"])) { ?>
										<div id="distance_map_<?php echo $value["id"]; ?>" class="distanceWrap"<?php if($value["distance"]==0){ echo ' style="display:none;"'; } ?>>
											<span class="dText">
												距離目前位置
											</span>
											<span id="distance_map_text_<?php echo $value["id"]; ?>" class="dNum"><?php echo $value["distance"]; ?> km</span>
										</div>
										<?php } ?>
									</div>
								</a>
								*/?>
							</div>
							<?php } ?>
							</div>
						</div>
					</div>
					<?php /*
					<div class="swiper-button-next"></div>
	        		<div class="swiper-button-prev"></div>
	        		*/ ?>
				</div>
				<!-- information block -->
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
			<!-- <a href="javascript:void(0)" class="btn">
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
	// 顯示地圖內容
	// var myIcon = '/../../web/img/meicon.png';
	var myIcon = '../../web/img/location-point.png';
	var haveRoomIcon = '/../../web/img/bnb.png';
	var noRoomIcon = '/../../web/img/pin-inactive.png';
	var activityIcon = '/../../web/img/pin-acive.png';
	var hotelIcon = '/../../web/img/meicon.png';
	var viewpointIon = '/../../web/img/pin-inactive.png';
	var foodIcon = '/../../web/img/pin-acive.png';
	var gitIcon = '/../../web/img/git.png';
	<?php if(!empty($gc_latitude) && !empty($gc_longitude)){ ?>
	var gc_latitude = '<?php echo $gc_latitude; ?>';
	var gc_longitude = '<?php echo $gc_longitude; ?>';
	var viewpoint_title = '<?php echo $viewpoint_title; ?>';
	<?php } else { ?>
	var gc_latitude = '<?php echo $latitude; ?>';
	var gc_longitude = '<?php echo $longitude; ?>';
	var viewpoint_title = '';
	<?php } ?>
	var map_type = 1;
	var swiper_spot = null;
	var swiper_food = null;
	var curClickPoint_spot = 0;
	var curClickPoint_food = 0;
	var markers_default = [];
	var markers_spot = [];
	var markers_food = [];
	var map;

	function map_init() {
		map_location(gc_latitude, gc_longitude, viewpoint_title, 'google_map', 1, map_type);
		swiper_init();
	}

	function map_location(latitude, longitude, title, map_id, item, type){
		var latlng = new google.maps.LatLng(latitude, longitude);
		var show_icon = myIcon;
		if(item==2){
			show_icon = activityIcon;
		}
		var myOptions = {
			zoom: 12,
	    	center: latlng,
	    	mapTypeId: google.maps.MapTypeId.ROADMAP,
	    	scrollwheel: false,
	    	zoomControl: false,
	    	mapTypeControl: false,
	        streetViewControlOptions: false
// 	        streetViewControlOptions: {
// 	            position: google.maps.ControlPosition.LEFT_TOP
// 	        }
		};
		map = new google.maps.Map(document.getElementById(map_id), myOptions);
		if(type==1){
    		<?php
    	    // 設定content marker
    	    $idx_spot = 0;
    	    if (!empty($spot_content)) {
    	    	foreach ($spot_content as $sc) {
    	    		$idx_spot++;
    	    		if ($idx_spot == 1) {
    	    			$show_icon = "foodIcon";
    	    			?>
        	    	    curClickPoint_spot = 1;
    	    			<?php 
    	    		}else{
    	    			$show_icon = "noRoomIcon";
    	    		}
    	    		echo "var map_spot_icon = ".$show_icon.";";
    	    	    ?>
    	    	    markers_spot[<?php echo $idx_spot; ?>] = new google.maps.Marker({
	    	    		position: new google.maps.LatLng(<?php echo $sc['latitude']; ?>, <?php echo $sc['longitude']; ?>),
	    	    		title: '<?php echo preg_replace("/'/", "\\'", $sc['name']); ?>',
	    	    		icon: map_spot_icon
	    	    	});
					markers_spot[<?php echo $idx_spot; ?>].setMap(map);
					markers_spot[<?php echo $idx_spot; ?>].addListener('click', function() {
						swiper_spot.slideTo('<?php echo ($idx_spot - 1); ?>');
					});
    	    	    <?php
    			}
    		}
    		?>
		}else{
    		<?php
    	   	// 設定content marker
    	   	$idx_food = 0;
    	    if (!empty($food_content)) {
    	   		foreach ($food_content as $fc) {
    	    	    $idx_food++;
    	   			if ($idx_food == 1) {
    	    			$show_icon = "foodIcon";
    	    			?>
        	    	    curClickPoint_food = 1;
    	    			<?php 
    	    		}else{
    	    			$show_icon = "noRoomIcon";
    	    		}
    	    		echo "var map_food_icon = ".$show_icon.";";
    	    	    ?>
    	    	    markers_food[<?php echo $idx_food; ?>] = new google.maps.Marker({
	    	    		position: new google.maps.LatLng(<?php echo $fc['latitude']; ?>, <?php echo $fc['longitude']; ?>),
	    	    		title: '<?php echo preg_replace("/'/", "\\'", $fc['name']); ?>',
	    	    		icon: map_food_icon
	    	    	});
					markers_food[<?php echo $idx_food; ?>].setMap(map);
					markers_food[<?php echo $idx_food; ?>].addListener('click', function() {
						swiper_food.slideTo('<?php echo ($idx_food - 1); ?>');
					});
    	    	    <?php
    	    		}
    	    	}
    	    ?>
		}
		<?php if(!empty($my_latitude) && !empty($my_longitude)): ?>
			markers_default[0] = new google.maps.Marker({
		 		position: new google.maps.LatLng(<?=$my_latitude?>, <?=$my_longitude?>),
		 		title: title,
		 		icon: myIcon,
		 		map: map
		 	});
			markers_default[0].setMap(map);
		<?php endif; ?>
		<?php if(!empty($latitude) && !empty($longitude)){ ?>
			markers_default[1] = new google.maps.Marker({
		 		position: new google.maps.LatLng(<?php echo $gc_latitude; ?>, <?php echo $gc_longitude; ?>),
		 		title: title,
		 		icon: hotelIcon,
		 		map: map
		 	});
			markers_default[1].setMap(map);
		<?php } ?>
	}

	var swiper_init = function(){
		swiper_spot = new Swiper('.swiper-container-spot', {
            paginationClickable: true,
            allowSwipeToPrev: true,
            allowSwipeToNext: true,
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev',
			onSlideChangeEnd: function(swiper_spot) {
				console.log('onSlideChangeStart init');
				markers_spot[curClickPoint_spot].setIcon(noRoomIcon);
				curClickPoint_spot = swiper_spot.activeIndex + 1;
				markers_spot[curClickPoint_spot].setIcon(foodIcon);
				map.setCenter(markers_spot[curClickPoint_spot].getPosition());
			}
        });
        swiper_food = new Swiper('.swiper-container-food', {
            paginationClickable: true,
            allowSwipeToPrev: true,
            allowSwipeToNext: true,
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev',
			onSlideChangeEnd: function(swiper_food) {
				console.log('onSlideChangeStart init');
				markers_food[curClickPoint_food].setIcon(noRoomIcon);
				curClickPoint_food = swiper_food.activeIndex + 1;
				markers_food[curClickPoint_food].setIcon(foodIcon);
				map.setCenter(markers_food[curClickPoint_food].getPosition());
			}
        });
	}
	var swiper_reinit = function (location_go){
		if(map_type==1){
			swiper_spot = new Swiper('.swiper-container-spot', {
	            paginationClickable: true,
	            allowSwipeToPrev: true,
	            allowSwipeToNext: true,
	            nextButton: '.swiper-button-next',
	            prevButton: '.swiper-button-prev',
				onSlideChangeEnd: function(swiper_spot) {
					console.log('onSlideChangeStart init');
					markers_spot[curClickPoint_spot].setIcon(noRoomIcon);
					curClickPoint_spot = swiper_spot.activeIndex + 1;
					markers_spot[curClickPoint_spot].setIcon(foodIcon);
					map.setCenter(markers_spot[curClickPoint_spot].getPosition());
				}
	        });
	        if(location_go!=0){
				swiper_spot.slideTo(curClickPoint_spot);
	        }
		}else{
	        swiper_food = new Swiper('.swiper-container-food', {
	            paginationClickable: true,
	            allowSwipeToPrev: true,
	            allowSwipeToNext: true,
	            nextButton: '.swiper-button-next',
	            prevButton: '.swiper-button-prev',
				onSlideChangeEnd: function(swiper_food) {
					console.log('onSlideChangeStart init');
					markers_food[curClickPoint_food].setIcon(noRoomIcon);
					curClickPoint_food = swiper_food.activeIndex + 1;
					markers_food[curClickPoint_food].setIcon(foodIcon);
					map.setCenter(markers_food[curClickPoint_food].getPosition());
				}
	        });
	        if(location_go!=0){
	        	swiper_food.slideTo(curClickPoint_food);
	        }
		}
	}

    	<?php if ($latitude == "" && $longitude == "") { ?>
    	if (navigator.geolocation) {
    	    // HTML5 定位抓取
    	    navigator.geolocation.getCurrentPosition(function(position) {
    	        mapServiceProvider(position.coords.latitude, position.coords.longitude);
    	    },
    	    function(error) {
    	        switch (error.code) {
    	            case error.TIMEOUT:
    	                alert('連線逾時');
    	                break;

    	            case error.POSITION_UNAVAILABLE:
    	                alert('無法取得定位');
    	                break;

    	            case error.PERMISSION_DENIED://拒絕
    	                alert('您尚未允許開啟手機的GPS定位功能!');
    	                break;

    	            case error.UNKNOWN_ERROR:
    	                alert('不明的錯誤，請稍候再試');
    	                break;
    	        }
    	    });
    	} else { // 不支援 HTML5 定位
    	    // 若支援 Google Gears
    	    if (window.google && google.gears) {
    	        try {
    	              // 嘗試以 Gears 取得定位
    	              var geo = google.gears.factory.create('beta.geolocation');
    	              geo.getCurrentPosition(successCallback,errorCallback, { enableHighAccuracy: true,gearsRequestAddress: true });
    	        } catch(e){
    	              alert("定位失敗請稍候再試");
    	        }
    	    } else {
    	        alert("您尚未允許開啟手機的GPS定位功能!");
    	    }
    	}
    	<?php } ?>

    	// 取得 Gears 定位發生錯誤
    	function errorCallback(err) {
    	    var msg = 'Error retrieving your location: ' + err.message;
    	    alert(msg);
    	}

    	// 成功取得 Gears 定位
    	function successCallback(p) {
    	    mapServiceProvider(p.latitude, p.longitude);
    	}

    	// 顯示經緯度
    	function mapServiceProvider(latitude, longitude) {
        	var id = $("#id").val();
        	var category = $("#category").val();
        	$("#latitude").val(latitude);
        	$("#longitude").val(longitude);
        	<?php if (!empty($spot_content_first) && !empty($food_content_first)) { ?>
    		$.getJSON('/web/ajax/ajax.php',
				{func: 'find_my_recommend_content_distance', 'category': category, 'id': id, 'latitude': latitude, 'longitude': longitude},
				function(data) {
					if (data.code == '9999') {
						alert(data.msg);
					} else {
						content_distance(data.data);
					}
				}
			);
    		<?php } else { ?>
    		var gc_latitude = $("#gc_latitude").val();
    		var gc_longitude = $("#gc_longitude").val();
    		$.getJSON('/web/ajax/ajax.php',
    			{func: 'find_my_recommend_nearby_content_distance', 'category': category, 'id': id, 'gc_latitude': gc_latitude, 'gc_longitude': gc_longitude, 'latitude': latitude, 'longitude': longitude},
    			function(data) {
    				if (data.code == '9999') {
    					alert(data.msg);
    				} else {
    					content_distance(data.data);
    				}
    			}
    		);
    		<?php } ?>
    		var type = $("#type").val();
        	var id = $("#id").val();
    		url = "/vendor/"+type+id+"/?latitude="+latitude+"&longitude="+longitude;
        	window.location.href = url;
    	}

    	// 顯示經緯度
    	function mapServiceProvider_show(latitude, longitude) {
        	var id = $("#id").val();
        	var category = $("#category").val();
        	$("#latitude").val(latitude);
        	$("#longitude").val(longitude);
        	<?php if (!empty($spot_content_first) && !empty($food_content_first)) { ?>
    		$.getJSON('/web/ajax/ajax.php',
				{func: 'find_my_recommend_content_distance', 'category': category, 'id': id, 'latitude': latitude, 'longitude': longitude},
				function(data) {
					if (data.code == '9999') {
						alert(data.msg);
					} else {
						content_distance(data.data);
					}
				}
			);
    		<?php } else { ?>
    		var gc_latitude = $("#gc_latitude").val();
    		var gc_longitude = $("#gc_longitude").val();
    		$.getJSON('/web/ajax/ajax.php',
    			{func: 'find_my_recommend_nearby_content_distance', 'category': category, 'id': id, 'gc_latitude': gc_latitude, 'gc_longitude': gc_longitude, 'latitude': latitude, 'longitude': longitude},
    			function(data) {
    				if (data.code == '9999') {
    					alert(data.msg);
    				} else {
    					content_distance(data.data);
    				}
    			}
    		);
    		<?php } ?>
    	}

    	function formatFloat(num, pos) {
    	    var size = Math.pow(10, pos);
    	    return Math.round(num * size) / size;
    	}

    	var content_distance = function(distance){
    		$.each( distance, function( key, value ) {
        		var tc_id = value.tc_id;
        		var distance_text;
        		$("#distance_"+tc_id).show();
        		$("#distance_map_"+tc_id).show();
        		distance_text = formatFloat(value.distance, 4) + " km";
        		$("#distance_text_"+tc_id).html(distance_text);
        		$("#distance_map_text_"+tc_id).html(distance_text);
    		});
    	}

    	function redirect(id) {
        	var latitude = $("#latitude").val();
        	var longitude = $("#longitude").val();
        	url = "/vendor/otr/"+id+"/?latitude="+latitude+"&longitude="+longitude+"&hs_id=<?= $id ?>";
        	window.location.href = url;
    	}

    	$(function () {
			// 未切換地圖按鈕 - 切換景點按鈕
			$('#spot').on("click", function (){
				$("#spot").addClass( "selected" );
	        	$("#food").removeClass( "selected" );
	        	$('#spot_list').show();
	        	$('#food_list').hide();
			});

			// 未切換地圖按鈕 - 切換景點按鈕
			$('#food').on("click", function (){
				$("#food").addClass( "selected" );
	        	$("#spot").removeClass( "selected" );
	        	$('#food_list').show();
	        	$('#spot_list').hide();
			});

	     	// 切換景點按鈕
	        $('#map_spot').click(function () {
		        map_type = 1;
	        	$("#spot").addClass( "selected" );
	        	$("#food").removeClass( "selected" );
	        	$('#spot_list').show();
	        	$('#food_list').hide();
	        	$("#map_spot").addClass( "selected" );
	        	$("#map_food").removeClass( "selected" );
	        	$('#map_spot_list').show();
	        	$('#map_food_list').hide();
	        	map_location('<?php echo $gc_latitude; ?>', '<?php echo $gc_longitude; ?>', '<?php echo $viewpoint_title; ?>', 'google_map', 1, map_type);
	        	swiper_reinit(1);
	        	$(window).scrollTop("0");
	        });

	     	// 切換美食按鈕
	        $('#map_food').click(function () {
	        	map_type = 2;
	        	$("#food").addClass( "selected" );
	        	$("#spot").removeClass( "selected" );
	        	$('#food_list').show();
	        	$('#spot_list').hide();
	        	$("#map_food").addClass( "selected" );
	        	$("#map_spot").removeClass( "selected" );
	        	$('#map_food_list').show();
	        	$('#map_spot_list').hide();
	        	map_location('<?php echo $gc_latitude; ?>', '<?php echo $gc_longitude; ?>', '<?php echo $viewpoint_title; ?>', 'google_map', 1, map_type);

	        	swiper_reinit(1);
	        	$(window).scrollTop("0");
	        });

	        $('#map_spot_list .left').on("click", function(){
				alert('left');
		    });

	        $('#map_spot_list .right').on("click", function(){
				alert('right');
		    });

	        // bot menu button
	        $('[id^="bot_menu_"]').on('click', function(){
				var type = $(this).data('type');
				url = '/vendor/<?php echo $type . $id; ?>/info/';
				url += type + '/?latitude=<?php echo $latitude; ?>&longitude=<?php echo $longitude; ?>';
				window.location.href = url;
		    });

	        <?php if ($latitude != "" && $longitude != "") { ?>
	        //mapServiceProvider_show(<?php echo $latitude; ?>, <?php echo $longitude; ?>);

	     	// 地圖上切換位置
	        $("#spot_location").on("click", function(){
	        	$("#now_location").removeClass( "selected" );
	        	$("#spot_location").removeClass( "selected" );
	        	$("#spot_location").addClass( "selected" );
	        	map.setCenter(markers_default[1].getPosition());
		    });

	        $("#now_location").on("click", function(){
	        	$("#now_location").removeClass( "selected" );
	        	$("#spot_location").removeClass( "selected" );
	        	$("#now_location").addClass( "selected" );
	        	map.setCenter(markers_default[0].getPosition());
		    });
	        <?php }else{ ?>
				$(".swtichFrame").hide();
	        <?php } ?>
	        
			// 處理 map window 視窗 resize 時，被修正的寬度
	        $(window).resize(function(){
	        	$('.swiper-wrapper .swiper-slide').css("width", "2480px");
	        });
	        
			// 預設顯示 sort 狀態
	        chang_header_sort('<?php echo $sort; ?>');
	        $('#mapPage').hide();

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

		if ((/(iPhone|iPad|iPod)/i.test(navigator.userAgent)) && 
			(navigator.appVersion.indexOf('CriOS') < 0)) {
			$('.canvas').css('height', 'calc(100vh - 185px)');
		} else {
			$('.canvas').css('height', 'calc(100vh - 114px)');
		}
    </script>
</body>
</html>
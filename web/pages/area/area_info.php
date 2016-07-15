<?
/**
 * 說明：
 * 作者：cheans <cheans.huang@fullerton.com.tw>
 * 日期：2015年12月4日
 * 備註：
 * 地區介紹主圖目前為靜態規則為 "/web/img/area_info_kv_" + 地區ID(hf_area.a_id) + ".jpg"
 * .titleWrap .title 目前尚未提供(2015-12-04)
 */
require_once __DIR__ . '/../../config.php';

$area_code = get_val('area_code');
if(empty($area_code)) {
    alertmsg('地區錯誤', '/');
}

$subtitle_list = array();
$subtitle_list[] = array('code' => 'taipei', 'subtitle' => '滿足24小時吃喝玩樂的旅遊需求');
$subtitle_list[] = array('code' => 'Hualien', 'subtitle' => '令人忘掉煩憂的好山好水');
$subtitle_list[] = array('code' => 'kenting', 'subtitle' => '陽光、沙灘、音樂的享樂天堂');
$subtitle_list[] = array('code' => 'Kaohsiung', 'subtitle' => '體驗海洋之都的濃濃人情味');
$subtitle_list[] = array('code' => 'taichung', 'subtitle' => '悠閒浪漫的人文之都');
$subtitle_list[] = array('code' => 'tainan', 'subtitle' => '緩慢出餘韻的古都風情');

$tripitta_web_service = new tripitta_web_service();

$area_row = $tripitta_web_service->get_area_by_code(get_config_current_lang(), $area_code);
if(empty($area_row)) {
    alertmsg('地區錯誤', '/');
}
$area_en_row = $tripitta_web_service->get_area_by_code('en', $area_code);

$area_article_list = $tripitta_web_service->find_area_article_by_category_and_ref_id(Constants::AREA_ARTICLE_CATEGORY_LANDMARK, $area_row["a_id"]);
$image_server_url = get_config_image_server();
$homestay_list = $tripitta_web_service->find_valid_homestay_for_booking_home_by_area_id(array($area_row["a_id"]), 4);

$subtitle = '';
foreach($subtitle_list as $subtitle_row) {
        if(strtolower($subtitle_row['code']) == strtolower($area_code)) {
        $subtitle = $subtitle_row['subtitle'];
        break;
    }
}

?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
	<title><?= $area_row["a_name"] ?> - 地區介紹 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/img-area.css">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
</head>
<body>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="areaIndex-topBanner">
		<div class="banner" style='background: url("/web/img/area/area_info_kv_<?= $area_row["a_id"] ?>.jpg") 50% 50%/cover no-repeat'></div>
		<div class="breadWrap">
			<div class="bread">
				<i class="img-member-home"></i> /
				<span class="breadPath">
					<a href="javascript:void(0)">地區介紹</a> /
					<a href="javascript:void(0)"><?= $area_row["a_name"] ?></a>
				</span>
			</div>
		</div>
	</div>
	<div class="areaIndex-container">
		<section class="map">
			<div class="mapPlugin">
				<i class="img-map-hualien" style="display: <?= ($area_row["a_id"] == 7) ? 'block' : 'none' ?>"></i>
				<i class="img-map-kaohsiung" style="display: <?= ($area_row["a_id"] == 13) ? 'block' : 'none' ?>"></i>
				<i class="img-map-pingtung" style="display: <?= ($area_row["a_id"] == 1) ? 'block' : 'none' ?>"></i>
				<i class="img-map-taichung" style="display: <?= ($area_row["a_id"] == 11) ? 'block' : 'none' ?>"></i>
				<i class="img-map-tainan" style="display: <?= ($area_row["a_id"] == 9) ? 'block' : 'none' ?>"></i>
				<i class="img-map-taipei" style="display: <?= ($area_row["a_id"] == 16) ? 'block' : 'none' ?>"></i>
			</div>
		</section>
		</section>
		<section class="head">
			<div class="taiwan">
				<i class="img-taiwanarea">
					<i class="img-hualien" style="visibility: <?= ($area_row["a_id"] == 7) ? 'visible' : 'hidden' ?>"></i>
					<i class="img-kaohsiung" style="visibility: <?= ($area_row["a_id"] == 13) ? 'visible' : 'hidden' ?>"></i>
					<i class="img-pingtung" style="visibility: <?= ($area_row["a_id"] == 1) ? 'visible' : 'hidden' ?>"></i>
					<i class="img-taichung" style="visibility: <?= ($area_row["a_id"] == 11) ? 'visible' : 'hidden' ?>"></i>
					<i class="img-tainan" style="visibility: <?= ($area_row["a_id"] == 9) ? 'visible' : 'hidden' ?>"></i>
					<i class="img-taipei" style="visibility: <?= ($area_row["a_id"] == 16) ? 'visible' : 'hidden' ?>"></i>
				</i>
			</div>
			<hgroup class="titleWrap">
				<h1 class="name">
					<div class="ch"><?= $area_row["a_name"] ?></div>
					<div class="en"><?= $area_en_row["aml_name"] ?></div>
				</h1>
				<h2 class="title"><?= $subtitle ?></h2>
				<p class="description">
					<?= $area_row["a_intro"] ?>
				</p>
			</hgroup>
		</section>
<?
foreach($area_article_list as $idx => $area_article_row) {
    $img = $image_server_url . '/photos/travel/area_article/' . $area_row["a_id"] . '/' . $area_article_row["aa_photo"] . '.jpg';
    if($idx % 2 == 0) {
?>
		<section class="location">
			<h4><?= $area_article_row["aa_title"] ?></h4>
			<div class="content">
				<p class="text"><?= nl2br($area_article_row["aa_content"]) ?></p>
				<img src="<?= $img ?>" alt="" class="localImg" style="width:470px; height:350px">
			</div>
		</section>
<?
    } else {
?>
		<section class="location">
			<h4><?= $area_article_row["aa_title"] ?></h4>
			<div class="content2">
				<img src="<?= $img ?>" alt="" class="localImg" style="width:470px; height:350px">
				<p class="text"><?= nl2br($area_article_row["aa_content"]) ?></p>
			</div>
		</section>
<?
    }
}
?>
		<h5 class="related">相關資訊</h5>
	</div>

	<div class="relatedWrap">
<?
foreach($homestay_list as $homestay_row) {
    $img = '/web/img/no-pic.jpg';
    if(!empty($homestay_row["hs_main_photo"])) {
        $img = $image_server_url . '/photos/travel/home_stay/' . $homestay_row["hs_id"] . '/' . $homestay_row["hs_main_photo"] . '_middle.jpg';
    }
    $linkurl = '/booking/' . $homestay_row["a_code"] . '/' . $homestay_row["hs_id"] . '/';
?>
		<a href="<?= $linkurl ?>" class="item">
			<img src="<?= $img ?>" alt="<?= $homestay_row["hs_name"] ?>" onerror="javascript:this.src='/web/img/no-pic.jpg';">
			<div class="description"><?= $homestay_row["hs_name"] ?></div>
		</a>
<?
}
?>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>
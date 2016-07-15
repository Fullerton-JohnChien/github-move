<!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>
<?php
/**
 *  說明：tripitta 授權失敗
 *  作者：John <john.chien@fullerton.com.tw>
 *  日期：2015年11月23日
 *  備註：
 */

include_once '../../config.php';


// 發生錯誤時導向url
$potocal = 'https://';
if (is_dev() || is_alpha()) $potocal = 'http://';


// $curLang = 'tw';
// $curDevice = 'computer';
// $order_id = NULL;
$store_id = NULL;
// if (!empty($_REQUEST['curLang'])) $curLang = $_REQUEST['curLang'];
// if (!empty($_REQUEST['curDevice'])) $curDevice = $_REQUEST['curDevice'];
// if (!empty($_REQUEST['order_id'])) $order_id = $_REQUEST['order_id'];
if (!empty($_REQUEST['store_id'])) $store_id = $_REQUEST['store_id'];
if (is_dev()) $store_id = 45;

// 取得旅宿地區
$home_stay_dao = Dao_loader::__get_home_stay_dao();
$home_stay = $home_stay_dao->loadHomeStay($store_id);
$area_dao = Dao_loader::__get_area_dao();
$area = $area_dao->loadHfArea($home_stay['hs_area_id']);

$go_homestay_url = $potocal . $_SERVER['SERVER_NAME'] . '/booking/' . $area['a_code'] . '/' . $store_id . '/';
?>
	<title>Tripitta 旅必達 授權失敗</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
</head>
<body>
	<header class="header"><?php include __DIR__ . '/../common/header.php'?></header>
	<div class="payFail-container">
		<h1 class="title">付款認證失敗</h1>
		<div class="tile">
			<div class="payFailWrap">
				<i class="img-member-cross-big"></i>
				<h1>付款認證失敗</h1>
				<h2>
					<!--
					請重新輸入( 信用卡 / 銀聯卡 / 支付寶 ) 資料，或選擇其他付款方式，如屢次失敗，請洽發卡銀行客服。
					-->
					信用卡刷卡失敗，如屢次失敗，請洽發卡銀行客服。
				</h2>
				<a href="<?php echo $go_homestay_url?>" class="goBack">返回旅宿主頁</a>
			</div>
		</div>
	</div>
	<footer class="footer"><?php include __DIR__ . '/../common/footer.php'?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>
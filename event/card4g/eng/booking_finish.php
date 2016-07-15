<?php
/**
 * 說明：4G卡 - 機場臨櫃 訂單完成頁
 * 作者：Steak
 * 日期：2016年3月23日
 * 備註：
 * test url :http://local.tw.tripitta.com/event/card4g/booking_finish.php?order_id=31&serial=A1603230011
 */
include_once('../../../web/config.php');

$order_id = $_POST["order_id"];
writeLog("如意卡訂單編號=".$order_id);
$barcode = $_POST["barcode"];
writeLog("如意卡btocode=".$barcode);
if(empty($order_id) || empty($barcode)) {
	writeLog("無訂單編號!!bb");
	alertmsg('參數錯誤', '/airport/wifi/');
	writeLog("牛排測試!!");
	exit();
}

$barcode_url = (is_production() ? 'https://www.idealcard.com.tw/airport_net/' : 'https://www.idealcard.com.tw/airport_test/');

$idealcard_service = new idealcard_service();
// 取得訂單資訊
$idelcard_order_row = $idealcard_service->get_idealcard_order_by_order_id($order_id);
writeLog("訂單資訊=".json_encode($idelcard_order_row, JSON_UNESCAPED_UNICODE));
if(empty($barcode)) {
	$barcode = $idelcard_order_row[0]["io_barcode"];
}
$pro_id = $idelcard_order_row[0]["iol_prod_id"];
writeLog("商品ID=".$pro_id);
// 取得商品資訊
$idealcard_row = $idealcard_service -> get_prod_by_id($pro_id);
writeLog("商品資訊=".json_encode($idealcard_row, JSON_UNESCAPED_UNICODE));

if(empty($idelcard_order_row) || empty($idealcard_row)) {
	// sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - 訂單完成頁錯誤(英文版) order_id=' . $order_id . json_encode($idealcard_row, JSON_UNESCAPED_UNICODE));
	// alertmsg("取得訂單資訊失敗", '/airport/wifi/');
	// exit;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<? include "../../../web/pages/common/head.php"; ?>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=1080">
	<title>【Tripitta】 - 4G Pre-Paid Card</title>
	<link rel="stylesheet" href="/event/card4g/css/main.css">
</head>
<body>
	<div class="cht-step3-container">
		<header>
			<img src="/event/card4g/img/logo.svg" alt="">
			<h4>
				<div class="telecom">Chunghwa Telecom</div>
				<div class="cardName">4G Pre-Paid Card</div>
			</h4>
		</header>
		<div class="contain">
			<h1 class="h1Eng">Completed</h1>
			<div class="orderNumEng">Order Number <div class="markEng"><?= $barcode ?></div></div>
			<div class="notedEng">
				Do not close this page, and by virtue of the page, <span class="mark">Passport</span> and <span class="mark">Taiwan's Exit and Entry Permit</span> to the counter to Redemption Sim card .
			</div>
			<div class="proDetailEng">
				<div class="imgWrap">
					<img src="<?= $barcode_url ?>barcode/<?= $barcode ?>.png">
				</div>
				<div class="pName"><span class="mark"><?= $idealcard_row["i_days"] ?> Days Pass</span> of Unlimited Data</div>
				<div class="pSubName">includes unlimited Data/Wi-Fi internet access and Airtime NTD<?= $idealcard_row["i_call_amount"] ?></div>
				<div class="pPriceWrap">
						<div class="pText">Total Charges</div>
						<div class="pCurrency">TWD</div>
						<div class="pPrice"><?= $idealcard_row["i_price"] ?></div>
				</div>
			</div>
			<!--
			<div class="brand">
				<div class="imgWrap">
					<img src="img/group.svg">
				</div>
				<div class="detail">
					<div class="title">Tripitta 旅必達</div>
					<div class="sBlock">
						<div class="stars">
							<img src="img/star.svg">
							<img src="img/star.svg">
							<img src="img/star.svg">
							<img src="img/star.svg">
							<img src="img/star_half.svg">
						</div>
						<div class="dNum">9999次下載</div>
					</div>
					<div class="text">立即下載 APP 享更多優惠</div>
				</div>
			</div>
			-->
		</div>
	</div>
</body>
</html>
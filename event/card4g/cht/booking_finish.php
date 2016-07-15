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
	writeLog("無訂單編號aa!!");
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
	// sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - 訂單完成頁錯誤(中文版) order_id=' . $order_id . json_encode($idealcard_row, JSON_UNESCAPED_UNICODE));
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
	<title>【Tripitta】 - 中華電信4G預付卡訂購</title>
	<link rel="stylesheet" href="/event/card4g/css/main.css">
</head>
<body>
	<div class="cht-step3-container">
		<header>
			<img src="/event/card4g/img/logo.svg" alt="">
			<h4>中華電信4G預付卡訂購</h4>
		</header>
		<div class="contain">
			<h1>完成訂購</h1>
			<div class="orderNum">訂單編號 <span class="mark"><?= $barcode ?></span></div>
			<div class="noted">
				請勿關閉此頁面，並憑此頁面、<span class="mark">護照</span>或<span class="mark">入台許可證</span>向櫃台領取卡片
			</div>
			<div class="proDetail">
				<img src="<?= $barcode_url ?>barcode/<?= $barcode ?>.png">
				<div class="detailBlock">
					<div class="meta">商品名稱</div>
					<div class="val">
						<div class="v1"><span class="mark"><?= $idealcard_row["i_days"] ?>日</span>無限上網</div>
						<div class="subV1">含 TWD <?= $idealcard_row["i_call_amount"] ?> 通話金</div>
					</div>
					<div class="meta">已刷卡金額</div>
					<div class="val">
						<div class="v2">
							<div class="currency">TWD</div>
							<div class="num"><?= $idealcard_row["i_price"] ?></div>
						</div>
					</div>
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
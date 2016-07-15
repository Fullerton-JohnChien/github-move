<?php
/**
 * 說明：交通 - 付款錯誤頁
 * 作者：Ricky
 * 日期：2016年6月17日
 * 備註：
 * 2016/07/13 授權失敗退還旅遊金 Steak
 */
require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");
$tripitta_service = new tripitta_service();
$marking_campaign_log_dao = Dao_loader::__get_marking_campaign_log_dao();

$type = get_val("type");
$order_id = get_val("order_id");

// 高鐵
if($type == "5") {
	$order_row = $tripitta_service -> get_ticket_order_by_order_id($order_id);
	// 退還旅遊金
	if(!empty($order_row["to_marking_campaign_discount"])) {
		for($i = 1;$i <= $order_row['to_marking_campaign_discount']/100; $i++) {
			$marking_campaign_log_dao -> update_status_by_order_id_and_user_id($order_id, $order_row["to_user_id"], 5);
		}
	}
}
// 其他 @todo
else {
	$order_row = $tripitta_service -> get_car_order_by_order_id($order_id);
	// 退還旅遊金
	if(!empty($order_row["co_marking_campaign_discount"])) {
		for($i = 1;$i <= $order_row['co_marking_campaign_discount']/100; $i++) {
			$marking_campaign_log_dao -> update_status_by_order_id_and_user_id($order_id, $order_row["co_user_id"], 2);
		}
	}
}



?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<?php include __DIR__ . "/../../common/head.php"; ?>
	<script src="/web/js/lib/jquery/jquery.js"></script>
	<script src="/web/js/main-min.js"></script>
	<script src="/web/js/lib/autogrow/autogrow.min.js"></script>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" href="/web/css/main2.css">
</head>
<body>
	<header><?php include __DIR__ . "/../../common/header_new.php"; ?></header>
	<div class="transport-payFail-container">
		<h1 class="title">付款認證失敗</h1>
		<div class="tile">
			<div class="payFailWrap">
				<i class="img-member-cross-big"></i>
				<h2>付款認證失敗</h2>
				<div class="pTitle">可能的原因：</div>
				<ul class="possible">
					<li>您使用的信用卡無法授權，可能需要詢問信用卡行。</li>
					<li>銀行授權時間過長，未接收到授權回覆。</li>
					<li>您使用的VISA金融卡/銀聯卡…餘額不足導致</li>
				</ul>
				<div class="btnWrap">
					<a href="javascript:credit()" class="btn">更換卡片重填</a>
					<a href="javascript:pay()" class="btn">更換付款方式</a>
				</div>
			</div>
		</div>
	</div>
	<footer><? include __DIR__ . "/../../common/footer_new.php"; ?></footer>
	<script type="text/javascript">
	function credit() {
		location.href = "/web/pages/bookingcar/pay/pay_step_credit.php";
	}
	function pay() {
		location.href = "/web/pages/bookingcar/pay/pay_step.php";
	}
	</script>
</body>
</html>
<?php
/**
 * 說明：處理訂購資料頁
 * 作者：Steak
 * 日期：2016年6月6日
 * 備註：路線類型 1:包車 2:接機 3:觀巴 4:送機 5:高鐵
 */
header("Content-Type: text/html; charset=utf-8");
require_once __DIR__ . '/../../config.php';
$tripitta_service = new tripitta_service();

$fr_id = get_val("fr_id");
$type = get_val("type");
$pickup_type = get_val('pickup_type');
$begin_date = get_val("begin_date");
$car_adult = get_val('car_adult');
$car_child = get_val('car_child');

// 取得高鐵 - 票券類型資料
$ticket_id = get_val("ticket_id");
$tree_type = get_val("tree_type");
$take_date = get_val("take_date");
$start_area = get_val("start_area");
$end_area = get_val("end_area");
$ticket_adult = get_val("ticket_adult");
$ticket_child = get_val("ticket_child");
$coupon = get_val("coupon");
$return_url = "/transport/";

// 包車、接機、觀巴、送機
if($type == 1 || $type == 2 || $type == 3 || $type == 4) {
	// 取得路線
	$fleet_route_row = $tripitta_service->get_fleet_route($fr_id);
	if(empty($fleet_route_row)) {
		alertmsg("無路線資訊", $return_url);
	}

	// 取得路線價格
	$fleet_price_level_id = $fleet_route_row["fr_fleet_price_level_id"];
	$fleet_price_level_row = $tripitta_service -> get_fleet_price_level($fleet_price_level_id);
	if(empty($fleet_price_level_row)) {
		alertmsg("無路線價格資訊", $return_url);
	}

	// 是否有設安全存量
	if($fleet_price_level_row["fpl_car_limit"] == "N") {
		// pass
	}else if($fleet_price_level_row["fpl_car_limit"] == "Y"){
		// 檢查當天是否還有庫存
		$ckeck_status = $tripitta_service -> check_car_stock_by_date_and_limit($begin_date, $fleet_price_level_id, $fleet_price_level_row["fpl_car_cnt"]);
		if(!$ckeck_status) {
			alertmsg("庫存量不足", $return_url);
		}
	}
}
// 高鐵
else if($type == 5) {

	// 檢查票券及票價
	$ticket_type_price_list = $tripitta_service->find_ticket_type_price_by_ticket_id($ticket_id, $start_area, $end_area);
	if(empty($ticket_type_price_list)) {
		alertmsg("查無票種票價資訊", $return_url);
	}
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset='utf-8'>
</head>
<body onload="document.form1.submit()">
	<form name="form1" method="post" action="/web/pages/bookingcar/pay/pay_step.php">
		<?php if($type == 5) { ?>
			<input type="hidden" name="ticket_id" value="<?php echo $ticket_id?>">
			<input type="hidden" name="tree_type" value="<?php echo $tree_type; ?>" />
			<input type="hidden" name="take_date" value="<?php echo $take_date; ?>" />
			<input type="hidden" name="start_area" value="<?php echo $start_area; ?>" />
			<input type="hidden" name="end_area" value="<?php echo $end_area; ?>" />
			<input type="hidden" name="ticket_adult" value="<?php echo $ticket_adult; ?>" />
			<input type="hidden" name="ticket_child" value="<?php echo $ticket_child; ?>" />
			<input type="hidden" name="coupon" value="<?php echo $coupon; ?>" />
		<?php }else { ?>
			<input type="hidden" name="fr_id" value="<?= $fr_id ?>" />
			<input type="hidden" name="pickup_type" value="<?php echo $pickup_type; ?>" />
			<input type="hidden" name="begin_date" value="<?= $begin_date ?>"/>
			<input type="hidden" name="car_adult" value="<?php echo $car_adult; ?>" />
			<input type="hidden" name="car_child" value="<?php echo $car_child; ?>" />
		<?php } ?>
		<input type="hidden" name="type" value="<?= $type ?>"/>
	</form>
</body>
</html>
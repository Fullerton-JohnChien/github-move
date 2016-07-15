<?php
require_once __DIR__ . '/../../config.php';

$tripitta_car_order_service = new tripitta_car_order_service();
$tripitta_homestay_service = new tripitta_homestay_service();
$tripitta_web_service = new tripitta_web_service();
$tripitta_service = new tripitta_service();
$payment_channel_dao = Dao_loader::__get_payment_channel_dao();
$marking_campaign_log_dao = Dao_loader::__get_marking_campaign_log_dao();

$error_url = "/transport/";

$pay_steps = get_val("pay_step");
if(empty($pay_steps)) $pay_steps = get_val("pay_step2");

$pay_step = json_decode($pay_steps, true);
$userData = !empty($_SESSION[USER_DATA]) ? $_SESSION[USER_DATA] : '';

// 取得訂購內容
$fleet_route_detail_row = $tripitta_service -> get_fleet_route_detail($pay_step["type"], $pay_step["fr_id"]);
if (empty($fleet_route_detail_row)) {
	alertmsg('取得訂購內容失敗!!', $error_url);
}

// 取得出發地、目的地
$car_boarding = $tripitta_service->get_area_by_id($fleet_route_detail_row["cr_boarding"]);
$car_get_off = $tripitta_service->get_area_by_id($fleet_route_detail_row["cr_get_off"]);

// 取得售價
if($pay_step["type"] == 3) {
	$pay_price = $fleet_route_detail_row["fr_adult_price"] * $pay_step["adult"] + $fleet_route_detail_row["fr_child_price"] * $pay_step["child"];
}else {
	$pay_price = $fleet_route_detail_row["fr_price"];
}

// 取得附加服務
$facility = array();
if(isset($pay_step["license"])) array_push($facility, $pay_step["license"]);
if(isset($pay_step["en_driver"])) array_push($facility, $pay_step["en_driver"]);
if(isset($pay_step["jp_driver"])) array_push($facility, $pay_step["jp_driver"]);
if(isset($pay_step["ct_driver"])) array_push($facility, $pay_step["ct_driver"]);
if(isset($pay_step["ko_driver"])) array_push($facility, $pay_step["ko_driver"]);
if(isset($pay_step["baby_set"])) array_push($facility, $pay_step["baby_set"]);
if(isset($pay_step["child_set"])) array_push($facility, $pay_step["child_set"]);
if(isset($pay_step["placard"])) array_push($facility, $pay_step["placard"]);
if(isset($pay_step["female"])) array_push($facility, $pay_step["female"]);
if(isset($pay_step["accessible"])) array_push($facility, $pay_step["accessible"]);
if(isset($pay_step["wifi"])) array_push($facility, $pay_step["wifi"]);

// 檢查付款方式
$cardType = $pay_step['credit']; // 信用卡ID
$paymentChannel = $payment_channel_dao->getPaymentChannelByCode($cardType);
if (empty($paymentChannel)) {
	alertmsg('付款方式未選!!', $error_url);
}

$coupon = $pay_step["coupon"];

$co_marking_campaing_id = 0;
if(empty($coupon) || $coupon == "undefined") {
	$market_list = $tripitta_service -> find_campaign_campaign_must_possessed_by_user_and_type($_SESSION['travel.ezding.user.data']["serialId"], 1);
	if(!empty($market_list)) {
		foreach ($market_list as $ml) {
			$co_marking_campaing_id = $ml["mc_id"];
			$bo_array = $tripitta_service-> cal_marking_campain_discount($pay_price, $co_marking_campaing_id, $_SESSION['travel.ezding.user.data']["serialId"]);
			$bonus = $bo_array["data"]["discount"];
			if($bonus > 0) break;
		}
	}
}

$r1 = null;
$r2 = null;
$r3 = null;
$r4 = null;
if ($paymentChannel["pc_send_redirect"] == 0) {
	// 刷卡資料
	$ccNo = $_REQUEST['ccNo'];
	$ccExpYear = $_REQUEST['ccExpYear'];
	$ccExpMonth = $_REQUEST['ccExpMonth'];
	$cvc2 = $_REQUEST['cvc2'];

	$r1 = rc4Encrypt($ccNo, $transKey . $transSum);
	$r2 = rc4Encrypt($ccExpYear, $transKey . $transSum);
	$r3 = rc4Encrypt($ccExpMonth, $transKey . $transSum);
	$r4 = rc4Encrypt($cvc2, $transKey . $transSum);
}

// 已登入
if(!empty($userData)){
	$userRow = $userData;
	// 更新會員資料
// 	$item = array();
// 	$item["userId"] = $userRow['id'];
// 	$item["nickname"] = $pay_step["name"];
// 	$item["userEmail"] = $pay_step["email"];
// 	$item["userGender"] = $pay_step["gender"];
// 	$item["userMobile"] = $pay_step["mobile"];
// 	$item["living_country_id"] = $pay_step["living_country_id"];
// 	$ret = $tripitta_web_service->update_user_data($item);
// 	$userRow = $ret['msg'];
// 	if ($ret["status"] == "0") {
// 		alertmsg($ret[0], '訂購人資料不齊全，尚未授權!!', $error_url);
// 	}
}

if (empty($userRow) || empty($userRow['id']) || empty($userRow['serialId'])) {
	writeLog('訂購人資料不齊全，尚未授權!! userMobilePhone='.$pay_step["mobile"].', username='.$pay_step["name"].', userEmail='.$pay_step["email"]);
	alertmsg('訂購人資料不齊全，尚未授權!!', $error_url);
}

// 取得購買人資訊
$order_owner = array();
$order_owner["member_id"] = $userRow['serialId'];
$order_owner["name"] = $pay_step["name"];
$order_owner["phone"] = $pay_step["mobile"];
$order_owner["country"] = $pay_step["living_country_id"];
$order_owner["email"] = $pay_step["email"];
$order_owner["wechat"] = $pay_step["wechat_id"];
$order_owner["line"] = $pay_step["line_id"];
$order_owner["whatsapp"] = $pay_step["whatsapp_id"];

// 取得聯絡人資訊
$order_contact = array();
$order_contact["name"] = $pay_step["car_name"];
$order_contact["phone"] = $pay_step["car_mobile"];
$order_contact["country"] = $pay_step["car_living_country_id"];
$order_contact["email"] = $pay_step["car_email"];
$order_contact["wechat"] = $pay_step["car_wechat_id"];
$order_contact["line"] = $pay_step["car_line_id"];
$order_contact["whatsapp"] = $pay_step["car_whatsapp_id"];

// 取得乘客資訊
$order_gusst = array();
for($i = 0; $i < $pay_step["adult"]+$pay_step["child"]; $i++) {
	$order_gusst[$i]["countty_id"] = $pay_step["passenger_country[".$i."]"];
	$order_gusst[$i]["name"] = $pay_step["passenger_name[".$i."]"];
	$order_gusst[$i]["birthday"] = $pay_step["passenger_birthday[".$i."]"];
	$order_gusst[$i]["identity_type"] = 0;
	$order_gusst[$i]["identity"] = $pay_step["passenger_number[".$i."]"];
}

// 取得匯率資訊
$currency_id = $tripitta_web_service->get_display_currency();
$exchange = $tripitta_homestay_service->get_exchange_by_currency_id($currency_id);

// otr code
$otr_code = getOtrCode();

// 建立訂單
$item = array();
$item["item_id"] = $pay_step["fr_id"];
$item["from_code"] = 'tripitta';
$item["begin"] = $pay_step["begin_date"];
if($pay_step["type"] == 1) {
	$item["departure"] = $pay_step["get_on"]; // 出發地
	$item["arrival"] = $pay_step["get_off"]; // 目的地
	$item["begin_time"] = $pay_step["get_on_time"];
	$item["final_price"] = $fleet_route_detail_row["fr_price"];
}else if($pay_step["type"] == 2 || $pay_step["type"] == 4) {
	$item["arrival_date"] = $pay_step["arrival_date"];
	$item["arrival_time"] = $pay_step["arrival_time"];
	$item["flight_number"] = $pay_step["flight_number"];
	$item["begin_time"] = $pay_step["pickup_time"];
	if($pay_step["type"] == 2) {
		$item["departure"] = $car_boarding["a_name"]; // 出發地
		$item["arrival"] = $car_get_off["a_name"].$pay_step["get_off_address"]; // 目的地
	}else if($pay_step["type"] == 4) {
		$item["departure"] = $car_boarding["a_name"].$pay_step["boarding_address"]; // 出發地
		$item["arrival"] = $car_get_off["a_name"]; // 目的地
	}
	$item["final_price"] = $fleet_route_detail_row["fr_price"];
}else if($pay_step["type"] == 3) {
	$item["begin_time"] = "00:00:00";
	$item["departure"] = $car_boarding["a_name"]; // 出發地
	$item["arrival"] = $car_get_off["a_name"]; // 目的地
	$item["final_price"] = $pay_step["adult"]*$fleet_route_detail_row["fr_adult_price"] + $pay_step["child"]*$fleet_route_detail_row["fr_child_price"];
}
$item["adult"] = $pay_step["adult"];
$item["children"] = $pay_step["child"];
$item["currency_id"] = $currency_id;
$item["exchange_rate"] = $exchange['erd_rate'];
$item["exchange_rate_id"] = $exchange["erd_exchange_rate_id"];
$item["order_guest"] = json_decode(json_encode($order_gusst));
$item["order_owner"] = json_decode(json_encode($order_owner));
$item["order_contact"] = json_decode(json_encode($order_contact));
$item["payment_channel_id"] = $paymentChannel['pc_id'];
$item["facility"] = json_decode(json_encode($facility));
$item["memo"] = $pay_step["memo"];
$item["suggest"] = isset($pay_step["suggest"]) ? $pay_step["suggest"] : 0;
$item["status"] = 10;
$item["lang"] = 'tw';
$item["device"] = 'web';
$item["otr_code"] = $otr_code;
$item["co_marking_campaing_id"] = $co_marking_campaing_id;
//$item['debug'] = 1; // 測試用
$rtnAry = $tripitta_car_order_service->app_create_car_order($item);

if ($rtnAry["code"] != '0000') {
	alertmsg('連結訂單中心失敗!!', $error_url);
}

$orderId = $rtnAry["data"]["order_id"];
// $pid = $orderId;
// $_SESSION[$pid] = $orderId;
$car_order_row = $tripitta_service->get_car_order_by_order_id($orderId);

// 新增行銷代碼log
if(!empty($car_order_row['co_marking_campaing_id'])) {
	for($i = 1;$i <= $car_order_row['co_marking_campaign_discount']/100; $i++) {
		$item = array();
		$item['mcl_marking_campaign_id'] = $car_order_row['co_marking_campaing_id'];
		$item['mcl_site'] = 3;
		$item['mcl_type'] = 2;
		$item['mcl_user_id'] = $userRow['serialId'];
		$item['mcl_order_id'] = $orderId;
		$item['mcl_status'] = 1;
		$item['mcl_create_time'] = date('Y-m-d H:i:s');
		$marking_campaign_log_dao -> save($item);
	}
}

/**
 * 不用授權走 no_charge_order.php
 * hitrust授權走 auth_hitrust.php
 * 銀聯卡授權走 auth_cathaybk_china_union.php
 */
// 先不參照這個
//$authForward = $paymentChannel['pc_auth_forward'];
if($car_order_row["co_sell_price"] - $car_order_row["co_coupon_discount"] <= 0){
	$action = 'no_charge_order.php';
}else if($cardType == "tripitta.hitrust.car"){
	$action = 'auth_hitrust.php';
}else if($cardType == "tripitta.china.union.car"){
	$action = 'auth_cathaybk_china_union.php';
}
writeLog('action=' . $action);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body onload="document.form1.submit()">
<form name="form1" method="post" action="<?php echo $action?>">
<input type="hidden" name="pid" value="<?php echo $orderId ?>"/>
<input type="hidden" name="type" value="<?php echo $pay_step["type"] ?>"/>
<input type="hidden" name="r1" value="<?php echo $r1?>"/>
<input type="hidden" name="r2" value="<?php echo $r2?>"/>
<input type="hidden" name="r3" value="<?php echo $r3?>"/>
<input type="hidden" name="r4" value="<?php echo $r4?>"/>
</form>
</body>
</html>
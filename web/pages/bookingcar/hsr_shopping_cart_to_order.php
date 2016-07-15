<?php
require_once __DIR__ . '/../../config.php';

$tripitta_ticket_order_service = new tripitta_ticket_order_service();
// $tripitta_web_service = new tripitta_web_service();
$tripitta_service = new tripitta_service();
$payment_channel_dao = Dao_loader::__get_payment_channel_dao();
$marking_campaign_log_dao = Dao_loader::__get_marking_campaign_log_dao();

$error_url = "/transport/";

$pay_steps = get_val("pay_step");
if(empty($pay_steps)) $pay_steps = get_val("pay_step2");

$pay_step = json_decode($pay_steps, true);
$userData = !empty($_SESSION[USER_DATA]) ? $_SESSION[USER_DATA] : '';

// 取得訂購內容
$t_id = $pay_step["ticket_id"];
// $tc_parent_id = $pay_step["tree_config_id"];
$take_date = $pay_step["take_date"];
// $tree_type = $pay_step["tree_type"];
$start_area = $pay_step["start_area"];
$end_area = $pay_step["end_area"];
$ticket_type_price_list = $tripitta_service->find_ticket_type_price_by_ticket_id($t_id, $start_area, $end_area);

if(empty($ticket_type_price_list)) {
	alertmsg('取得票種票價失敗!!', $error_url);
}

// 檢查付款方式
$cardType = $pay_step['credit']; // 信用卡ID
$paymentChannel = $payment_channel_dao->getPaymentChannelByCode($cardType);
if (empty($paymentChannel)) {
	alertmsg('付款方式未選!!', $error_url);
}

// 取得售價
$pay_price = 0;
$t_count = 1;
foreach ($ticket_type_price_list as $t){
	if($t_count==1){
		$pay_price += $t["ttp_sell_price"] * $pay_step["adult"];
	}elseif($t_count==2){
		$pay_price += $t["ttp_sell_price"] * $pay_step["child"];
	}
	$t_count++;
}

$coupon = $pay_step["coupon"];

$to_marking_campaign_id = 0;
if(empty($coupon) || $coupon == "undefined") {
	$market_list = $tripitta_service -> find_campaign_campaign_must_possessed_by_user_and_type($_SESSION['travel.ezding.user.data']["serialId"], 1);
	if(!empty($market_list)) {
		foreach ($market_list as $ml) {
			$to_marking_campaign_id = $ml["mc_id"];
			$bo_array = $tripitta_service-> cal_marking_campain_discount($pay_price, $to_marking_campaign_id, $_SESSION['travel.ezding.user.data']["serialId"]);
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

// 取得乘客資訊
$order_gusst = array();
for($i = 0; $i < $pay_step["adult"]+$pay_step["child"]; $i++) {
	$order_gusst[$i]["tol_user_country_id"] = $pay_step["passenger_country[".$i."]"];
	$order_gusst[$i]["tol_user_name"] = $pay_step["passenger_last_name[".$i."]"].' '.$pay_step["passenger_first_name[".$i."]"];
	$order_gusst[$i]["tol_user_passport_name"] = $pay_step["passenger_last_name[".$i."]"].' '.$pay_step["passenger_first_name[".$i."]"];
// 	$order_gusst[$i]["last_name"] = $pay_step["passenger_last_name[".$i."]"];
	$order_gusst[$i]["tol_user_birthday"] = $pay_step["passenger_birthday[".$i."]"];
// 	$order_gusst[$i]["identity_type"] = 0;
	$order_gusst[$i]["tol_user_passport_number"] = $pay_step["passenger_number[".$i."]"];
	$order_gusst[$i]["tol_user_gender"] = $pay_step["passenger_gender[".$i."]"];
}

// otr code
$otr_code = getOtrCode();

// 建立訂單
$item = array();
$item["to_from_code"] = 'tripitta';
$item["to_ticket_id"] = $t_id;
$item["to_user_id"] = $userRow['serialId'];
$item["to_user_name"] = $pay_step["name"];
$item["to_user_mobile"] = $pay_step["mobile"];
$item["to_user_email"] = $pay_step["email"];
$item["to_forecast_date"] = $take_date;

$item["ttp_from"] = $start_area; // 出發地
$item["ttp_to"] = $end_area; // 目的地

$item["adult"] = $pay_step["adult"];
$item["children"] = $pay_step["child"];
$item["order_passenger"] = $order_gusst;
$item["to_payment_channel_id"] = $paymentChannel['pc_id'];
$item["to_memo"] = $pay_step["memo"];
$item["to_status"] = 10;
$item["to_country_id"] = $pay_step["living_country_id"];
$item["to_language"] = 'tw';
$item["to_device"] = 'web';
$item["to_wechat_id"] = $pay_step["wechat_id"];
$item["to_line_id"] = $pay_step["line_id"];
$item["to_whats_app_id"] = $pay_step["whatsapp_id"];
$item["otr_code"] = $otr_code;
$item["to_marking_campaign_id"] = $to_marking_campaign_id;
//$item['debug'] = 1; // 測試用
$rtnAry = $tripitta_ticket_order_service->create_order($item);

if ($rtnAry["code"] != '0000') {
	alertmsg('連結訂單中心失敗!!', $error_url);
}

$orderId = $rtnAry["data"]["order_id"];
// $pid = $orderId;
// $_SESSION[$pid] = $orderId;
$ticket_order_row = $tripitta_service->get_ticket_order_by_order_id($orderId);

// 新增行銷代碼log
if(!empty($ticket_order_row['to_marking_campaign_id'])) {
	for($i = 1;$i <= $ticket_order_row['to_marking_campaign_discount']/100; $i++) {
		$item = array();
		$item['mcl_marking_campaign_id'] = $ticket_order_row['to_marking_campaign_id'];
		$item['mcl_site'] = 3;
		$item['mcl_type'] = 5;
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
if($ticket_order_row["to_sell_price"] - $ticket_order_row["to_coupon_discount"] <= 0){
	$action = 'hsr_no_charge_order.php';
}else if($cardType == "tripitta.hitrust.ticket"){
	$action = 'hsr_auth_hitrust.php';
}else if($cardType == "tripitta.china.union.ticket"){
	$action = 'hsr_auth_cathaybk_china_union.php';
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
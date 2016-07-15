<?php
require_once __DIR__ . '/../../config.php';
$tripitta_web_service = new tripitta_web_service();
$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);

$payment_channel_dao = Dao_loader::__get_payment_channel_dao();
$roomKeepingDao = Dao_loader::__get_room_keeping_dao();

$pid = $_REQUEST['shoppingCartId']; // 購物車ID
$cardType = $_REQUEST['cardType']; // 信用卡ID
$homeStayId = $_REQUEST['homeStayId'];
$area_code = $_REQUEST["area_code"];
$beginDate = $_REQUEST['beginDate'];
$endDate = $_REQUEST['endDate'];
$memo = $_REQUEST['memo']; // 特殊需求
$userName = $_REQUEST['userName'];
$userGender = $_REQUEST['userGender'];
$userEmail = $_REQUEST['userEmail'];
$userCountryCode = $_REQUEST['userCountryCode'];
$userMobilePhone = $_REQUEST['userMobilePhone'];
$userPassword = isset($_REQUEST['userPassword']) ? $_REQUEST['userPassword'] : '';
$roomerName = $_REQUEST['roomerName'];
$roomerMobilePhone = $_REQUEST['roomerMobilePhone'];
$roomerEmail = $_REQUEST['roomerEmail'];
$userData = !empty($_SESSION[USER_DATA]) ? $_SESSION[USER_DATA] : '';
$category = constants_user_center::USER_CATEGORY_TRIPITTA;


// 購物車主檔
$data = array('shopping_cart_id'=>$pid);
$cm_row = $tripitta_api_client_service -> query_shopping_cart($data);
if($cm_row["code"] = "0000"){
	$cart_id = $cm_row["data"]["data"][0]["cart.types"][0]["cart.details"][0]["ch_trans_id"]; // ezding 購物車ID
}else{
	alert($cm_row["msg"], '/booking/'.$area_code.'/'.$homeStayId.'/');
	exit();
}

if (empty($pid) || empty($cart_id)) {
	alertmsg('訂房資料錯誤，查無購物車ID!!', '/booking/'.$area_code.'/'.$homeStayId.'/');
	exit();
}

$paymentChannel = $payment_channel_dao->getPaymentChannelByCode($cardType);
if (empty($paymentChannel)) {
	alertmsg('付款方式未選!!', '/booking/'.$area_code.'/'.$homeStayId.'/');
	exit();
}

$r1 = null;
$r2 = null;
$r3 = null;
$r4 = null;
if ($paymentChannel["pc_send_redirect"] == 0) {
	// 刷卡資料
	$ccNo = $_REQUEST['ccNo1'].$_REQUEST['ccNo2'].$_REQUEST['ccNo3'].$_REQUEST['ccNo4'];
	$ccExpYear = $_REQUEST['ccExpYear'];
	$ccExpMonth = $_REQUEST['ccExpMonth'];
	$cvc2 = $_REQUEST['cvc2'];

	$r1 = rc4Encrypt($ccNo, $transKey . $transSum);
	$r2 = rc4Encrypt($ccExpYear, $transKey . $transSum);
	$r3 = rc4Encrypt($ccExpMonth, $transKey . $transSum);
	$r4 = rc4Encrypt($cvc2, $transKey . $transSum);
}


// log
$roomList = $roomKeepingDao->findValidRoomListByShoppingCartId($cart_id);
foreach ($roomList as $r) {
	writeLog($pid . '--->' . $r['r_id'] . ', ' . $r['rt_id'] . ', ' . $r['r_date']);
}

$rkList = $roomKeepingDao->findBillRoomKeepingListByShoppingCartId($cart_id);
foreach ($rkList as $rk) {
	writeLog($rk['rk_shopping_cart_id'] . '--->' . $rk['rk_room_id'] . ', ' . $rk['rk_price_category'] . ', ' . $rk['rk_price_id'] . ', ' . $rk['rk_status']);
}

// 檢查要結帳的 room 是否都已保留
$error = true;
foreach ($rkList as $rk) {
	$flag = false;
	foreach ($roomList as $r) {
		if ($rk['rk_room_id'] == $r['r_id']) {
			$flag = true;
			break;
		}
	}

	if ($flag) {
		$error = false;
		break;
	}
}

if ($error) {
	$msg = '親愛的客戶，很抱歉，您選擇的房型， \n'
			. '已被取消，敬請重新選擇其他房型。\n'
					. '造成困擾深表抱歉!!';
	alertmsg($msg, '/booking/'.$area_code.'/'.$homeStayId.'/');
}

// 已登入
if(!empty($userData)){
	$userRow = $userData;
	// 更新會員資料
	$item = array();
	$item["userId"] = $userRow['id'];
	$item["nickname"] = $userName;
	$item["userEmail"] = $userEmail;
	$item["userGender"] = $userGender;
	$item["userMobile"] = $userMobilePhone;
	$item["living_country_id"] = $userCountryCode;
	$ret = $tripitta_web_service->update_user_data($item);
	$userRow = $ret['msg'];
	if ($ret["status"] == "0") {
		alertmsg($ret[0], '訂購人資料不齊全，尚未授權!!', '/booking/'.$area_code.'/'.$homeStayId.'/');
		exit();
	}
}
// 未登入
else{
	// 檢查會員是否存在
	$ret = $tripitta_web_service->is_user_exists($category, $userEmail);
	// 不存在
	if ($ret["status"] == 1 && $ret["msg"] == 0) {
		// 新增會員資料
		$item = array();
		$item["category"] = $category;
		$item["userAccount"] = $userEmail;
		$item["userPassword"] = md5($userPassword);
		$item["nickname"] = $userName;
		$item["userEmail"] = $userEmail;
		$item["userGender"] = $userGender;
		$item["userMobile"] = $userMobilePhone;
		$item["living_country_id"] = $userCountryCode;
		$item["userAgreement"] = 1;
		$ret = $tripitta_web_service->add_user($item);
		if ($ret["status"] == "0") {
			alertmsg($ret["msg"],'訂購人資料不齊全，尚未授權!!', '/booking/'.$area_code.'/'.$homeStayId.'/');
			exit();
		} else if($ret["status"] == "1"){
			$result = $tripitta_web_service->get_user_data_by_category_and_account($category, $userEmail);
			$userRow = $result['msg'];
		}
	}
}

if (empty($userRow) || empty($userRow['id']) || empty($userRow['serialId'])) {
	writeLog('訂購人資料不齊全，尚未授權!! userMobilePhone='.$userMobilePhone.', username='.$userName.', userEmail='.$userEmail);
	alertmsg('訂購人資料不齊全，尚未授權!!', '/booking/'.$area_code.'/'.$homeStayId.'/');
	exit();
}

// 建立訂單
$data = array();
$data["shopping_ccart_id"] = $pid;
$data["user_id"] = $userRow['serialId'];
$data["buyer_name"] = $userName; // 訂購人
$data["buyer_email"] = $userEmail;
$data["buyer_mobile"] = $userMobilePhone;
$data["user_name"] = $roomerName; // 入住人
$data["user_email"] = $roomerEmail;
$data["user_mobile"] = $roomerMobilePhone;
$data["operator"] = "user";
$data["operator_id"] = $userRow['serialId'];
$data["request_memo"] = $memo;
$data["store_id"] = $homeStayId;
$data["cart_id"] = $pid;
$data["payment_channel_id"] = $paymentChannel['pc_id'];
$data["product_type"] = Constants::ODC_PRODUCT_TYPE_HOMESTAY;;
$data["device"] = 'web';
$data["lang"] = 'tw';

$rtnAry = $tripitta_api_client_service->create_order($data);
if ($rtnAry["code"] != '0000') {
	alertmsg('連結訂單中心失敗!!', '/booking/'.$area_code.'/'.$homeStayId.'/');
	exit();
} else {
	if ($rtnAry["data"]["code"] != '0000') {
		alertmsg($rtnAry["data"]["msg"], '/booking/'.$area_code.'/'.$homeStayId.'/');
		exit();
	}
}

$orderId = $rtnAry["data"]["data"]["detail_id"];
$_SESSION[$pid] = $orderId;

$data = array();
$data["order_id"] = $orderId;
$rets = $tripitta_api_client_service->get_order($data);

/**
 * 不用授權走 no_charge_order.php
 * hitrust授權走 auth_hitrust.php
 * 銀聯卡授權走 auth_cathaybk_china_union.php
 */
// 先不參照這個
//$authForward = $paymentChannel['pc_auth_forward'];
if($rets["data"]["data"]["od_sell_price"] - $rets["data"]["data"]["od_coupon_discount"] <= 0){
	$action = 'no_charge_order.php';
}else if($cardType == "tripitta.hitrust"){
	$action = 'auth_hitrust.php';
}else if($cardType == "tripitta.china.union"){
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
<input type="hidden" name="pid" value="<?php echo $pid?>"/>
<input type="hidden" name="homeStayId" value="<?php echo $homeStayId?>"/>
<input type="hidden" name="beginDate" value="<?php echo $beginDate?>"/>
<input type="hidden" name="endDate" value="<?php echo $endDate?>"/>
<input type="hidden" name="r1" value="<?php echo $r1?>"/>
<input type="hidden" name="r2" value="<?php echo $r2?>"/>
<input type="hidden" name="r3" value="<?php echo $r3?>"/>
<input type="hidden" name="r4" value="<?php echo $r4?>"/>
</form>
</body>
</html>
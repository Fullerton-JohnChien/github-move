<?php
include_once('../../web/config.php');
$tripitta_web_service = new tripitta_web_service();
$idealcard_service = new idealcard_service();
$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);

$proId = $_REQUEST['proId']; // 商品ID
$cardType = $_REQUEST['cardType']; // 信用卡ID
$lang = $_REQUEST["lang"]; // 語系
$language = ($lang == "cht") ? "tw" : "en";
$userEmail = $_REQUEST['email'];
$userPassword = isset($_REQUEST['userPassword']) ? $_REQUEST['userPassword'] : '';
$category = constants_user_center::USER_CATEGORY_TRIPITTA;

$r1 = null;
$r2 = null;
$r3 = null;
$r4 = null;
// 刷卡資料
if($cardType == 32) {
	$ccNo = $_REQUEST['ccNo1'].$_REQUEST['ccNo2'].$_REQUEST['ccNo3'].$_REQUEST['ccNo4'];
	$ccExpYear = $_REQUEST['ccExpYear'];
	$ccExpMonth = $_REQUEST['ccExpMonth'];
	$cvc2 = $_REQUEST['cvc2'];
	$r1 = rc4Encrypt($ccNo, $transKey . $transSum);
	$r2 = rc4Encrypt($ccExpYear, $transKey . $transSum);
	$r3 = rc4Encrypt($ccExpMonth, $transKey . $transSum);
	$r4 = rc4Encrypt($cvc2, $transKey . $transSum);
}

// 檢查會員是否存在
$ret = $tripitta_web_service->is_user_exists($category, $userEmail);

// 不存在
if ($ret["status"] == 1 && $ret["msg"] == 0) {
	// 新增會員資料
	$item = array();
	$item["category"] = $category;
	$item["userAccount"] = $userEmail;
	$item["userPassword"] = md5($userPassword);
	// $item["nickname"] = $userName;
	$item["userEmail"] = $userEmail;
	// $item["userGender"] = $userGender;
	// $item["userMobile"] = $userMobilePhone;
	// $item["living_country_id"] = $userCountryCode;
	$item["userAgreement"] = 1;
	$ret = $tripitta_web_service->add_user($item);
	if ($ret["status"] == "0") {
		alertmsg($ret["msg"],'訂購人資料不齊全，尚未授權!!', '/event/card4g/'.$lang.'/booking_confirm.php?proId='.$proId);
		exit();
	} else if($ret["status"] == "1"){
		$result = $tripitta_web_service->get_user_data_by_category_and_account($category, $userEmail);
		$userRow = $result['msg'];
	}
// 存在
}else {
	$result = $tripitta_web_service->get_user_data_by_category_and_account($category, $userEmail);
	$userRow = $result['msg'];
}

if (empty($userRow) || empty($userRow['id']) || empty($userRow['serialId'])) {
	writeLog('訂購人資料不齊全，尚未授權!! userEmail='.$userEmail);
	alertmsg('訂購人資料不齊全，尚未授權2!!', '/event/card4g/'.$lang.'/booking_confirm.php?proId='.$proId);
	exit();
}

// 建立訂單
$data = array();
$data["user_id"] = $userRow['serialId'];
// $data["passport_name"] = $passport_name;
$data["email"] = $userEmail;
$data["prod_id"] = $proId;
$data["channel_id"] = 92;
$data["from_code"] = '';
$data["payment_channel_id"] = $cardType;
//$data["trans_id"] = "TST" . generateSerno(6);
$data["client_ip"] = get_remote_ip();
$data["country_id"] = 228;
$data["device"] = "smartphone";
$data["language"] = $language;

$rtnAry = $tripitta_api_client_service->idealcard_create_order_for_airport($data);

if ($rtnAry["code"] != '0000') {
	alertmsg('連結訂單中心失敗!!', '/event/card4g/'.$lang.'/booking_confirm.php?proId='.$proId);
	exit();
} else {
	if ($rtnAry["data"]["code"] != '0000') {
		alertmsg($rtnAry["data"]["msg"], '/event/card4g/'.$lang.'/booking_confirm.php?proId='.$proId);
		exit();
	}
}

// 訂單編號
$orderId = $rtnAry["data"]["data"]["order_id"];
writeLog("order_id=".$orderId);

// 儲存卡號
$data = array();
$data["ioa_id"] = $orderId;
$data["ioa_card_number"] = $_REQUEST['ccNo4'];
$tripitta_api_client_service -> idealcard_create_order_account_for_airport($data);

// 授權前更新訂單狀態
$data = array();
$data["order_id"] = $orderId;
$data["order_status"] = 10;
$data["remark"] = 'cathaybk授權前';
$results = $tripitta_api_client_service->idealcard_update_order_status_for_airport($data);

if ('0000' != $results['code'] || '0000' != $results['data']['code']) {
	writeLog('訂單狀態更新為授權前時發生錯誤 order_id=' . $orderId . ' result:' . json_encode($results, JSON_UNESCAPED_UNICODE));
	alertmsg('訂單資料寫入失敗!!', '/event/card4g/'.$lang.'/booking_confirm.php?proId='.$proId);
}

// 取得訂單資料
$data = array();
$data["order_id"] = $orderId;
$result = $tripitta_api_client_service->idealcard_get_order_for_airport($data);

$order_data = NULL;
if ('0000' == $result['code']) {
	if ('0000' == $result['data']['code']) {
		$order_data = $result['data']['data'];
	}
	// if (is_dev()) printmsg($order_data);
}

if (empty($order_data)) {
	writeLog('auth.cathaybk order is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' order_id=' . $orderId);
	alertmsg('訂單資料錯誤!!', '/event/card4g/'.$lang.'/booking_confirm.php?proId='.$proId);
}

// 取得商品資料
$idealcard_row = $idealcard_service -> get_prod_by_id($proId);

// 授權前新增如意卡訂單
writeLog('新增如意卡api start， io_id=' . $orderId );
$item = array();
$item["campaign"] = "Tripitta";
$item["orderid"] = $order_data["io_transaction_id"];
$item["email"] = $order_data["io_buyer_email"];
$item["prod"] = $idealcard_row["i_ref_id"];
$item["money"] = $order_data["io_sell_price"];
$item["currency"] = 1;
$item["paymethod"] = get_idealcard_payment_code($order_data["io_payment_channel_id"]);
$item["enter_date"] = date("YmdHis");
$item["redate_e"] = date("Ymd", strtotime("+3 DAY"));
$item["arr_date"] = date("Ymd");
$item["last_code"] = $_REQUEST['ccNo4'];
$ret = $idealcard_service -> create_idealcard_order($item);
writeLog(json_encode($ret, JSON_UNESCAPED_UNICODE));

if($ret["code"] == "0000") {
	if($ret["data"]["data"][0]["code"] == "0000") {
		// 新增訂單更新同步狀態
		$data = array();
		$data["order_id"] = $orderId;
		$data["status"] = 10;
		$data["barcode"] = $ret["data"]["data"][0]["serial"];
		$tripitta_api_client_service->idealcard_update_order_barcode_for_airport($data);
		// $action = $potocal . $_SERVER['SERVER_NAME'] . '/event/card4g/booking_finish.php';
	}else {
		// 新增訂單失敗
		alertmsg("新增如意卡訂單失敗", '/event/card4g/'.$lang.'/booking_confirm.php?proId='.$proId);
		writeLog('如意卡api 新增訂單失敗 id_id=' . $order_data['id_id'] . ' io_transaction_id=' . $order_data['io_transaction_id'] . ' result:' . $ret["data"]["data"][0]["msg"]);
	}
} else {
	// 呼叫API失敗
	alertmsg("呼叫如意卡api失敗", '/event/card4g/'.$lang.'/booking_confirm.php?proId='.$proId);
	writeLog('如意卡api 呼叫API失敗失敗 id_id=' . $order_data['id_id'] . ' io_transaction_id=' . $order_data['io_transaction_id'] . ' result:' . $ret["error"]);
}

writeLog('新增如意卡api end， io_id=' . $orderId );
/**
 * 國泰信用卡授權走 auth_cathaybk.php
 * 支付寶授權走 auth_esunbk_alipay.php
 */
// 先不參照這個
//$authForward = $paymentChannel['pc_auth_forward'];
if($cardType == 32) {
	$action = 'auth_cathaybk.php';
}else if($cardType == 31){
	$action = 'auth_esunbk_alipay.php';
}

writeLog('action=' . $action);

function get_idealcard_payment_code($payment_id) {
	$code = 0;
	if($payment_id == 32) {
		$code = 1;
	}else if($payment_id == 31) {
		$code = 2;
	}
	return $code;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<body onload="document.form1.submit()">
<form name="form1" method="post" action="<?php echo $action?>">
<input type="hidden" name="orderId" value="<?php echo $orderId ?>">
<input type="hidden" name="proId" value="<?php echo $proId ?>">
<input type="hidden" name="lang" value="<?php echo $lang ?>">
<input type="hidden" name="userEmail" value="<?php echo $userEmail ?>">
<input type="hidden" name="r1" value="<?php echo $r1?>">
<input type="hidden" name="r2" value="<?php echo $r2?>">
<input type="hidden" name="r3" value="<?php echo $r3?>">
<input type="hidden" name="r4" value="<?php echo $r4?>">
</form>
</body>
</html>
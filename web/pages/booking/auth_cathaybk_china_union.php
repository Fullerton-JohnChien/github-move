<?php
/**
 * 說明：tripitta 銀聯卡授權
 * 作者：Steak
 * 日期：2015年12月24日
 * 備註：
 */

include_once '../../config.php';
set_time_limit(180);

// 發生錯誤時導向url
$potocal = 'https://';
if (is_dev() || is_alpha()) $potocal = 'http://';
$error_url = $potocal . $_SERVER['SERVER_NAME'];

// 訂單ID
$pid = $_REQUEST['pid'];

$orderId = $_SESSION[$pid];
if (!is_dev() && empty($orderId)) {
	writeLog('auth.china.union od_id is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' pid=' . $pid);
	alertmsg('訂單資料錯誤，尚未進行授權!!', $error_url);
}
$_SESSION[$pid] = null; // 清除 session 資料


$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);

// 取得訂單資料
$data = array();
$data["order_id"] = $orderId;
$result = $tripitta_api_client_service->get_order($data);
$order_detail = NULL;
if ('0000' == $result['code']) {
    if ('0000' == $result['data']['code']) {
        $order_detail = $result['data']['data'];
    }
}

if (empty($order_detail)) {
    writeLog('auth.hitrust order is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' od_id=' . $orderId);
    alertmsg('訂單資料錯誤!!', $error_url);
}

if ($order_detail['od_status'] != 10) {
    writeLog('訂單狀態錯誤[非授權前進入授權] od_id=' . $orderId . ' od_status=' . $order_detail['od_status']);
    alertmsg('訂單狀態錯誤，尚未進行授權!!', $error_url);
}

// 進入授權，訂單金額不能為0
if ($order_detail['od_bank_trans_money'] <= 0) {
    writeLog('tripitta銀聯卡訂單金額錯誤 od_id=' . $orderId . ' od_bank_trans_money=' . $order_detail['od_bank_trans_money']);
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - 銀聯卡 auth訂單金額錯誤 od_id=' . $orderId . ' od_bank_trans_money=' . $order_detail['od_bank_trans_money'], json_encode($order_detail, JSON_UNESCAPED_UNICODE));
    alertmsg('訂單金額錯誤，尚未進行授權!!', $error_url);
}

$curLang = 'tw';
if (!empty($order_detail['ode_language'])) $curLang = $order_detail['ode_language'];

$msg = 'Processing…';
$msg2 = 'Please do not refresh or close the window';
if ('tw' == $curLang) {
	$msg = '銀聯卡付款系統連線中';
	$msg2 = '請勿重新整理或關閉視窗';
}

/** 授權 **/
/*
 $options array(
 *		transId : 交易序號 (保証唯一)
 *		payCode : 付款方式代碼(cathaybk.china.union)
 *		amount : 金額
 *		returnUrl : 授權後導回頁面
 *		webSite : 站台代碼 (目前只有 tripitta 使用)
 * );
 */
$trans_params = get_config_trans_params();
$transId = $order_detail['od_transaction_id'];
$ez_payment = new EzdingPayment($trans_params['path'], $trans_params['account'], $trans_params['password']);

writeLog('hitrust授權 od_id=' . $orderId . ' od_transaction_id=' . $transId);
$options = array();
$options['amount'] = intval($order_detail['od_bank_trans_money']);
$options['returnUrl'] = $potocal . $_SERVER['SERVER_NAME'] . "/web/pages/booking/auth_back_cathaybk_china_union.php";
$options['webSite'] = 'tripitta';
$ret = $ez_payment -> auth($transId, "cathaybk.china.union", $options);
writeLog('cathaybk.china.union授權返回 od_id=' . $orderId . ' od_transaction_id=' . $transId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));

if ('FAIL' == $ret['status'] || '000' != $ret['code']) {
	writeLog('無法與授權中心取得連繫 od_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));
	sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - cathaybk.china.union auth授權返回狀態錯誤 od_id=' . $orderId, json_encode($ret, JSON_UNESCAPED_UNICODE));

	// 取消訂單
	$data = array();
	$data["order_id"] = $orderId;
	$data["cancel_rule"] = 1; // 1:無扣款取消
	$data["remark"] = '無法與授權中心取得連繫';
	$ret = $tripitta_api_client_service->cancel_order($data);
	writeLog('tripitta_api_client_service->cancel_order od_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));

	alertmsg('無法與授權中心取得連繫!!', $error_url);
}


$authUrl = null;
$strRqXML = null;
$authUrl = $ret['xml']['return_desc'];
$strRqXML = $ret['xml']['return_params']['param']['value'];
//writeLog('auth.cathaybk.china.union 秏時:' . (microtime(true) - $t1));


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tripitta 旅必達 銀聯卡授權</title>
<link rel="stylesheet" href="/web/css/main.css?01121536" />
</head>
<body>
<div class="authorizing-container">
	<div class="processing">
		<div id="floatingCirclesG">
			<div class="f_circleG" id="frotateG_01"></div>
			<div class="f_circleG" id="frotateG_02"></div>
			<div class="f_circleG" id="frotateG_03"></div>
			<div class="f_circleG" id="frotateG_04"></div>
			<div class="f_circleG" id="frotateG_05"></div>
			<div class="f_circleG" id="frotateG_06"></div>
			<div class="f_circleG" id="frotateG_07"></div>
			<div class="f_circleG" id="frotateG_08"></div>
		</div>
		<h1><?= $msg ?></h1>
		<h2><?= $msg2 ?></h2>
	</div>
</div>
<form name="form1" action="<?php echo $authUrl?>" method="post">
<input type="hidden" name="strRqXML" value="<?php echo $strRqXML?>"/>
</form>
<script type="text/javascript">document.form1.submit();</script>
</body>
</html>
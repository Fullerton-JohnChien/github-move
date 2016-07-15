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
$orderId = $_REQUEST['pid'];
writeLog('order_id=' . $orderId);
if (!is_dev() && empty($orderId)) {
	writeLog('auth.china.union to_id is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' pid=' . $orderId);
	alertmsg('訂單資料錯誤，尚未進行授權!!', $error_url);
}

$tripitta_service = new tripitta_service();

// 取得訂單資料
$order_detail = $tripitta_service->get_ticket_order_by_order_id($orderId);
if (empty($order_detail)) {
    writeLog('auth.china.union order is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' to_id=' . $orderId);
    alertmsg('訂單資料錯誤!!', $error_url);
}

if ($order_detail['to_status'] != 10) {
    writeLog('訂單狀態錯誤[非授權前進入授權] to_id=' . $orderId . ' to_status=' . $order_detail['to_status']);
    alertmsg('訂單狀態錯誤，尚未進行授權!!', $error_url);
}

// 進入授權，訂單金額不能為0
if ($order_detail['to_bank_trans_amount'] <= 0) {
    writeLog('tripitta銀聯卡訂單金額錯誤 to_id=' . $orderId . ' to_bank_trans_amount=' . $order_detail['to_bank_trans_amount']);
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - 銀聯卡 auth訂單金額錯誤 to_id=' . $orderId . ' to_bank_trans_amount=' . $order_detail['to_bank_trans_amount'], json_encode($order_detail, JSON_UNESCAPED_UNICODE));
    alertmsg('訂單金額錯誤，尚未進行授權!!', $error_url);
}

$curLang = 'tw';
if (!empty($order_detail['to_language'])) $curLang = $order_detail['to_language'];

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
$transId = $order_detail['to_transaction_id'];
$ez_payment = new EzdingPayment($trans_params['path'], $trans_params['account'], $trans_params['password']);

writeLog('hitrust授權 to_id=' . $orderId . ' to_transaction_id=' . $transId);
$options = array();
$options['amount'] = intval($order_detail['to_bank_trans_amount']);
$options['returnUrl'] = $potocal . $_SERVER['SERVER_NAME'] . "/web/pages/bookingcar/hsr_auth_back_cathaybk_china_union.php";
$options['webSite'] = 'tripitta';
$ret = $ez_payment -> auth($transId, "cathaybk.china.union", $options);
writeLog('cathaybk.china.union授權返回 to_id=' . $orderId . ' to_transaction_id=' . $transId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));

if ('FAIL' == $ret['status'] || '000' != $ret['code']) {
	writeLog('無法與授權中心取得連繫 to_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));
	sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - cathaybk.china.union auth授權返回狀態錯誤 to_id=' . $orderId, json_encode($ret, JSON_UNESCAPED_UNICODE));
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
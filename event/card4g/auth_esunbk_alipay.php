<?php
/**
 *  說明：tripitta 機場臨櫃 - 玉山支付寶授權
 *  作者：John <john.chien@fullerton.com.tw>
 *  日期：2016年03月23日
 *  備註：
 */

include_once '../../web/config.php';
set_time_limit(180);
if (ob_get_level() == 0) ob_start();
// $t1 = microtime(true);

// 發生錯誤時導向url
$potocal = 'https://';
if (is_dev() || is_alpha()) $potocal = 'http://';
$error_url = $potocal . $_SERVER['SERVER_NAME'] . '/airport/wifi/';


// 訂單資料
$orderId = $_REQUEST['orderId'];
if (!is_dev() && empty($orderId)) {
    writeLog('auth.esunbk.alipay order_id is empty ip:' . $_SERVER['REMOTE_ADDR']);
    alertmsg('訂單資料錯誤，尚未進行授權!!', $error_url);
}
writeLog('auth.esunbk.alipay order_id=' . $orderId . ' ip:' . $_SERVER['REMOTE_ADDR']);


// for test
// if (is_dev()) {
//     $orderId = 93;
// }


$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);


// 取得訂單資料
$data = array();
$data["order_id"] = $orderId;
$result = $tripitta_api_client_service->idealcard_get_order_for_airport($data);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
$order_data = NULL;
if ('0000' == $result['code']) {
    if ('0000' == $result['data']['code']) {
        $order_data = $result['data']['data'];
    }
// if (is_dev()) printmsg($order_data);
}

if (empty($order_data)) {
    writeLog('auth.esunbk.alipay order is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' order_id=' . $orderId);
    alertmsg('訂單資料錯誤!!', $error_url);
}

if ($order_data['io_status'] != 10) {
    writeLog('訂單狀態錯誤[非授權前進入授權] order_id=' . $orderId . ' io_status=' . $order_data['io_status']);
    alertmsg('訂單狀態錯誤，尚未進行授權!!', $error_url);
}

// 進入授權，訂單金額不能為0
if ($order_data['io_bank_trans_money'] <= 0) {
    writeLog('訂單金額錯誤 order_id=' . $orderId . ' io_bank_trans_money=' . $order_data['io_bank_trans_money']);
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - esunbk.alipay auth訂單金額錯誤 order_id=' . $orderId . ' io_bank_trans_money=' . $order_data['io_bank_trans_money'], json_encode($order_data, JSON_UNESCAPED_UNICODE));
    alertmsg('訂單金額錯誤，尚未進行授權!!', $error_url);
}


$curLang = 'tw';
// $curDevice = 'computer';
if (!empty($order_data['io_language'])) $curLang = $order_data['io_language'];
// if (!empty($order_data['io_device'])) $curDevice = $order_data['io_device'];

$msg = 'Processing…';
$msg2 = 'Please do not refresh or close the window';
if ('tw' == $curLang) {
    $msg = '授權中';
    $msg2 = '請勿重新整理或關閉視窗';
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<title>Tripitta授權</title>
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
<?php
ob_flush();
flush();


$transId = $order_data['io_transaction_id'];
$amount = intval($order_data['io_bank_trans_money']);


$trans_params = get_config_trans_params();
$ez_payment = new EzdingPayment($trans_params['path'], $trans_params['account'], $trans_params['password']);


// for test
if (is_dev() || is_alpha()) {
	$amount = 1;
}


/** esunbk.alipay 授權 **/
writeLog('esunbk.alipay授權 order_id=' . $orderId . ' io_transaction_id=' . $transId);
$options = array();
$options['amount'] = $amount;
$options['sum'] = $trans_params['sum'];
$options["orderTime"] = date("YmdHis", strtotime($order_data["io_create_time"]));
$options['returnUrl'] = $potocal . $_SERVER['SERVER_NAME'] . '/event/card4g/auth_back_esunbk_alipay.php'; // 站外授權用, 授權後導回頁面
// array('status', 'code', 'msg', 'xml'=>array('return_code', 'return_desc', 'return_trans_id'));
$ret = $ez_payment->auth($transId, 'idealcard.esun.alipay', $options);
writeLog('esunbk.alipay返回後轉址 order_id=' . $orderId . ' io_transaction_id=' . $transId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));
// if (is_dev()) printmsg($ret);


// esunbk.alipay返回後轉址
if ('000' == $ret['code']) {
	if ('00' == $ret['xml']['return_code']) {
// 		gotourl($ret["xml"]["return_desc"], true);
?>
<script>location.href = '<?php echo $ret["xml"]["return_desc"]?>';</script>
</body>
</html>
<?php
		exit(true);
	}
}
alertmsg('授權中心返回錯誤!!', $error_url);
?>
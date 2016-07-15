<?php
/**
 *  說明：tripitta hitrust授權(環匯收單)
 *  作者：John <john.chien@fullerton.com.tw>
 *  日期：2015年11月20日
 *  備註：
 *  2015-12-17 John 於授權後的任何非預期失敗加上取消訂單的動作
 *  2015-12-18 John 修正授權失敗的取消訂單判斷
 */
include_once '../../config.php';
set_time_limit(180);
if (ob_get_level() == 0) ob_start();
// $t1 = microtime(true);

// 發生錯誤時導向url
$potocal = 'https://';
if (is_dev() || is_alpha()) $potocal = 'http://';
$error_url = $potocal . $_SERVER['SERVER_NAME'];


// 刷卡資料
$r1 = $_REQUEST['r1'];
$r2 = $_REQUEST['r2'];
$r3 = $_REQUEST['r3'];
$r4 = $_REQUEST['r4'];

$type = get_val("type");
writeLog('type=' . $type);

// 檢查必要資料
if (!is_dev() && (empty($r1) || empty($r2) || empty($r3) || empty($r4))) {
    writeLog('刷卡資料錯誤 ip:' . $_SERVER['REMOTE_ADDR']);
    alertmsg('刷卡資料錯誤!!', $error_url);
}

// 訂單資料
$orderId = $_REQUEST['pid'];
writeLog('order_id=' . $orderId);
if (!is_dev() && empty($orderId)) {
    writeLog('auth.hitrust to_id is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' pid=' . $orderId);
    alertmsg('訂單資料錯誤，尚未進行授權!!', $error_url);
}
writeLog('auth.hitrust to_id=' . $orderId . ' ip:' . $_SERVER['REMOTE_ADDR']);


// for test
// if (is_dev()) {
//     $orderId = 93;
// }


$tripitta_service = new tripitta_service();


// 取得訂單資料
$order_detail = $tripitta_service->get_ticket_order_by_order_id($orderId);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));

if (empty($order_detail)) {
    writeLog('auth.hitrust order is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' to_id=' . $orderId);
    alertmsg('訂單資料錯誤!!', $error_url);
}

if ($order_detail['to_status'] != 10) {
    writeLog('訂單狀態錯誤[非授權前進入授權] to_id=' . $orderId . ' to_status=' . $order_detail['to_status']);
    alertmsg('訂單狀態錯誤，尚未進行授權!!', $error_url);
}

// 進入授權，訂單金額不能為0
if ($order_detail['to_bank_trans_amount'] <= 0) {
    writeLog('訂單金額錯誤 to_id=' . $orderId . ' to_bank_trans_amount=' . $order_detail['to_bank_trans_amount']);
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - hitrust auth訂單金額錯誤 to_id=' . $orderId . ' to_bank_trans_amount=' . $order_detail['to_bank_trans_amount'], json_encode($order_detail, JSON_UNESCAPED_UNICODE));
    alertmsg('訂單金額錯誤，尚未進行授權!!', $error_url);
}


$curLang = 'tw';
$curDevice = 'computer';
if (!empty($order_detail['to_language'])) $curLang = $order_detail['to_language'];
if (!empty($order_detail['to_device'])) $curDevice = $order_detail['to_device'];

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
<? include __DIR__ . "/../common/head_new.php"; ?>
<title>Tripitta 旅必達 授權</title>
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


$transId = $order_detail['to_transaction_id'];
$amount = intval($order_detail['to_bank_trans_amount']);
$bonus = $order_detail['to_bonus'];


$trans_params = get_config_trans_params();
$ez_payment = new EzdingPayment($trans_params['path'], $trans_params['account'], $trans_params['password']);


// for test
if (is_dev() || is_alpha()) {
    $amount = 1;
}
// if (is_dev()) {
//     $r1 = rc4Encrypt('8888880000000001', $trans_params['key'] . $trans_params['sum']);
//     $r2 = rc4Encrypt('2018', $trans_params['key'] . $trans_params['sum']);
//     $r3 = rc4Encrypt('12', $trans_params['key'] . $trans_params['sum']);
//     $r4 = rc4Encrypt('793', $trans_params['key'] . $trans_params['sum']);
// }


/** hitrust 授權 **/
writeLog('hitrust授權 to_id=' . $orderId . ' to_transaction_id=' . $transId);
$options = array();
$options['amount'] = $amount;
$options['bonus'] = $bonus;
$options['ccNo'] = $r1;
$options['ccExpYear'] = $r2;
$options['ccExpMonth'] = $r3;
$options['cvc2'] =  $r4;
$options['orderInfo'] = 'tripitta.order';
$options['sum'] = $trans_params['sum'];
$options['deposit'] = 1; // 是否自動請款 0:不請款 1:自動請款
// $options['orderTime'] = null; // 玉山銀行AliyPay使用:YmdHis
// $options['returnUrl'] = ''; // 站外授權用, 授權後導回頁面
$options['webSite'] = 'tripitta';
// array('status', 'code', 'msg', 'xml'=>array('return_code', 'return_desc', 'return_trans_id'));
$ret = $ez_payment->auth($transId, 'hitrust', $options);
writeLog('hitrust授權返回 to_id=' . $orderId . ' to_transaction_id=' . $transId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));
// if (is_dev()) printmsg($ret);


// 由 payment 授權後，將訂單狀態更新為授權返回
$item = array();
$item["to_id"] = $orderId;
$item["to_status"] = 20;
// $item["remark"] = 'hitrust授權返回';
$tripitta_service->save_or_update_ticket_order($item);

if ('FAIL' == $ret['status'] || '000' != $ret['code']) {
    writeLog('無法與授權中心取得連繫 to_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - hitrust auth授權返回狀態錯誤 to_id=' . $orderId, json_encode($ret, JSON_UNESCAPED_UNICODE));
    alertmsg('無法與授權中心取得連繫!!', $error_url);
}


$xmlAry = $ret['xml'];
$transactionId = $xmlAry['return_trans_id'];
$returnCode = $xmlAry['return_code'];
$returnDesc = $xmlAry['return_desc'];
$bankReturnCode = $xmlAry['bank_return_code'];
// $bankStatusCode = $xmlAry['bank_status_code'];
// $bankAuthCode = $xmlAry['bank_auth_code'];
$bankTransDate = $xmlAry['bank_trans_date'];
$bankRespMsg = $xmlAry['bank_resp_msg'];


// 預設為授權失敗
$is_success = false;
$action = $potocal . $_SERVER['SERVER_NAME'] . '/web/pages/bookingcar/pay/pay_fail.php';


if ($returnCode == 10) {
    $item = array();
    $item["to_id"] = $orderId;
    $item["to_banking_status"] = 20;
    $item["to_status"] = 30;
    $item["to_modify_time"] = date("Y-m-d H:i:s");
    // $item["remark"] = '授權請款成功';
    $tripitta_service->save_or_update_ticket_order($item);

    // 清掉session
    unset($_SESSION["pay_step"]);

    // 新增email job
    $job_order_notify_dao = Dao_loader::__get_travel_job_order_notify_dao();
    $item = array();
    $item["jon_type"] = 'ticket.order.email';
    $item["jon_target"] = "user";
    $item["jon_ref_id"] = $orderId;
    $item["jon_create_user"] = 2;
    $item["jon_create_time"] = date("Y-m-d H:i:s");
    $job_order_notify_dao->saveJobOrderNotifyByItem($item);
    //$tripitta_homestay_service = new tripitta_homestay_service();
    //$tripitta_homestay_service->add_order_finish_email_job('ticket.order.email', 'user', $orderId);

    // 新增 user sms job
    $item["jon_type"] = 'ticket.order.sms';
    $item["jon_create_time"] = date("Y-m-d H:i:s");
    $job_order_notify_dao->saveJobOrderNotifyByItem($item);

    $is_success = true;
    $action = $potocal . $_SERVER['SERVER_NAME'] . '/web/pages/bookingcar/pay/pay_succ.php';
}
else if ($returnCode == 19) {
    $item = array();
    $item["to_id"] = $orderId;
    $item["to_banking_status"] = 19;
    // $item["remark"] = '授權失敗';
    $tripitta_service->save_or_update_ticket_order($item);
}
else {
    writeLog('授權中心的returnCode錯誤 to_id=' . $orderId . ' returnCode=' . $returnCode . ' result:' . json_encode($xmlAry, JSON_UNESCAPED_UNICODE));
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - hitrust auth授權中心的returnCode錯誤 to_id=' . $orderId . ' returnCode=' . $returnCode, json_encode($xmlAry, JSON_UNESCAPED_UNICODE));
    alertmsg('銀行回傳的授權資料錯誤!!', $error_url);
}
writeLog('hitrust auth to_id=' . $orderId . ' to_banking_status=' . $returnCode . ' action=' . $action);


if ('N7' == $bankReturnCode) {
    $returnDesc = '卡號資料錯誤';
}
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));


// if (is_dev()) exit();
?>
<form name="form1" method="post" action="<?php echo $action?>">
<?php if ($is_success) {?>
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
<input type="hidden" name="type" value="<?php echo $type ?>"/>
<?php } else {?>
<input type="hidden" name="curLang" value="<?php echo $curLang?>"/>
<input type="hidden" name="curDevice" value="<?php echo $curDevice?>"/>
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
<input type="hidden" name="type" value="<?php echo $type ?>"/>
<input type="hidden" name="ticket_id" value="<?php echo $order_detail['to_ticket_id']?>"/>
<input type="hidden" name="return_trans_id" value="<?php echo $transactionId?>"/>
<input type="hidden" name="return_code" value="<?php echo $returnCode?>"/>
<input type="hidden" name="return_desc" value="<?php echo $returnDesc?>"/>
<?php /*?>
<input type="hidden" name="bank_return_code" value="<?php echo $bankReturnCode?>"/>
<input type="hidden" name="bank_status_code" value="<?php echo $bankStatusCode?>"/>
<input type="hidden" name="bank_auth_code" value="<?php echo $bankAuthCode?>"/>
<?php */?>
<input type="hidden" name="bank_trans_date" value="<?php echo $bankTransDate?>"/>
<input type="hidden" name="bank_resp_msg" value="<?php echo $bankRespMsg?>"/>
<?php }?>
</form>
<script type="text/javascript">document.form1.submit();</script>
</body>
</html>
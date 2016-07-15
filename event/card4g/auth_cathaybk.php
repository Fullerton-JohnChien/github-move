<?php
/**
 *  說明：tripitta 機場臨櫃 - 國泰世華信用卡授權
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


// 刷卡資料
$r1 = $_REQUEST['r1'];
$r2 = $_REQUEST['r2'];
$r3 = $_REQUEST['r3'];
$r4 = $_REQUEST['r4'];


// 檢查必要資料
if (!is_dev() && (empty($r1) || empty($r2) || empty($r3) || empty($r4))) {
    writeLog('刷卡資料錯誤 ip:' . $_SERVER['REMOTE_ADDR']);
    alertmsg('刷卡資料錯誤!!', $error_url);
}


// 訂單資料
$orderId = $_REQUEST['orderId'];
if (!is_dev() && empty($orderId)) {
    writeLog('auth.cathaybk order_id is empty ip:' . $_SERVER['REMOTE_ADDR']);
    alertmsg('訂單資料錯誤，尚未進行授權!!', $error_url);
}
writeLog('auth.cathaybk order_id=' . $orderId . ' ip:' . $_SERVER['REMOTE_ADDR']);

$proId = $_REQUEST["proId"];
$lang = $_REQUEST["lang"];
$userEmail = $_REQUEST["userEmail"];
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
    writeLog('auth.cathaybk order is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' order_id=' . $orderId);
    alertmsg('訂單資料錯誤!!', $error_url);
}

if ($order_data['io_status'] != 10) {
    writeLog('訂單狀態錯誤[非授權前進入授權] order_id=' . $orderId . ' io_status=' . $order_data['io_status']);
    alertmsg('訂單狀態錯誤，尚未進行授權!!', $error_url);
}

// 進入授權，訂單金額不能為0
if ($order_data['io_bank_trans_money'] <= 0) {
    writeLog('訂單金額錯誤 order_id=' . $orderId . ' io_bank_trans_money=' . $order_data['io_bank_trans_money']);
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - cathaybk auth訂單金額錯誤 order_id=' . $orderId . ' io_bank_trans_money=' . $order_data['io_bank_trans_money'], json_encode($order_data, JSON_UNESCAPED_UNICODE));
    alertmsg('訂單金額錯誤，尚未進行授權!!', $error_url);
}


$curLang = 'tw';
$curDevice = 'computer';
if (!empty($order_data['io_language'])) $curLang = $order_data['io_language'];
if (!empty($order_data['io_device'])) $curDevice = $order_data['io_device'];

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
<? include "../../web/pages/common/head.php"; ?>
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
// $bonus = $order_data['od_bonus'];


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


/** cathaybk 授權 **/
writeLog('cathaybk授權 order_id=' . $orderId . ' io_transaction_id=' . $transId);
$options = array();
$options['amount'] = $amount;
// $options['bonus'] = $bonus;
$options['ccNo'] = $r1;
$options['ccExpYear'] = $r2;
$options['ccExpMonth'] = $r3;
$options['cvc2'] =  $r4;
$options['orderInfo'] = 'airport.4g';
$options['sum'] = $trans_params['sum'];
$options['deposit'] = 1; // 是否自動請款 0:不請款 1:自動請款
// $options['orderTime'] = null; // 玉山銀行AliyPay使用:YmdHis
// $options['returnUrl'] = ''; // 站外授權用, 授權後導回頁面
$options['webSite'] = 'tripitta.idealcard';
// array('status', 'code', 'msg', 'xml'=>array('return_code', 'return_desc', 'return_trans_id'));
$ret = $ez_payment->auth($transId, 'cathaybk.api', $options);
writeLog('cathaybk授權返回 order_id=' . $orderId . ' io_transaction_id=' . $transId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));
// if (is_dev()) printmsg($ret);


// 由 payment 授權後，將訂單狀態更新為授權返回
$data = array();
$data["order_id"] = $orderId;
$data["order_status"] = 20;
$data["remark"] = 'cathaybk授權返回';
$result = $tripitta_api_client_service->idealcard_update_order_status_for_airport($data);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
// if (is_dev()) printmsg($result);
if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
    // 不應發生，再試一次
    $result = $tripitta_api_client_service->idealcard_update_order_status_for_airport($data);

    if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
        writeLog('訂單狀態更新為授權返回時發生錯誤 order_id=' . $orderId . ' result:' . json_encode($result, JSON_UNESCAPED_UNICODE));
        sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - cathaybk auth訂單狀態更新為授權返回錯誤(非常緊急) order_id=' . $orderId, json_encode($result, JSON_UNESCAPED_UNICODE));
        alertmsg('訂單資料寫入失敗!!', $error_url);
    }
}

if ('FAIL' == $ret['status'] || '000' != $ret['code']) {
    writeLog('無法與授權中心取得連繫 order_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - cathaybk auth授權返回狀態錯誤 order_id=' . $orderId, json_encode($ret, JSON_UNESCAPED_UNICODE));
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
$action = $potocal . $_SERVER['SERVER_NAME'] . '/event/card4g/'.$lang.'/booking_fail.php';


if ($returnCode == 10) {
    $data = array();
    $data["order_id"] = $orderId;
    $data["banking_status"] = 20;
    $data["remark"] = '授權請款成功';
    $result = $tripitta_api_client_service->idealcard_finish_order_for_airport($data);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
// if (is_dev()) printmsg($result);
    if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
        // 不應發生，再試一次
        $result = $tripitta_api_client_service->idealcard_finish_order_for_airport($data);

        if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
            writeLog('訂單授權狀態更新為授權請款成功時發生錯誤 order_id=' . $orderId . ' result:' . json_encode($result, JSON_UNESCAPED_UNICODE));
            sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - cathaybk auth訂單授權狀態更新為授權請款成功發生錯誤(非常緊急) order_id=' . $orderId, json_encode($result, JSON_UNESCAPED_UNICODE));
            alertmsg('訂單資料寫入失敗[授權成功]!!', $error_url);
        }
    }

    // 新增email job
//     $tripitta_homestay_service = new tripitta_homestay_service();
//     $tripitta_homestay_service->add_order_finish_email_job('order.email', 'user', $orderId);

    // 請款成功更新如意卡狀態
    $idealcard_service = new idealcard_service();
    writeLog('更新如意卡[請款成功]狀態api start， io_id=' . $orderId );
    $item = array();
    $item["status"] = 1;
    $item["orderid"] = $order_data['io_transaction_id'];
    $ret = $idealcard_service -> idealcard_update($item);
    writeLog(json_encode($ret, JSON_UNESCAPED_UNICODE));

    if($ret["code"] == "0000") {
    	if($ret["data"]["data"][0]["code"] == "0000") {
    		// 新增訂單更新同步狀態
    		$data = array();
    		$data["order_id"] = $orderId;
    		$data["status"] = 30;
    		$data["barcode"] = null;
    		$tripitta_api_client_service->idealcard_update_order_barcode_for_airport($data);

    		$is_success = true;
    		$action = $potocal . $_SERVER['SERVER_NAME'] . '/event/card4g/'.$lang.'/booking_finish.php';
    	}else {
    		// 新增訂單失敗
    		writeLog('如意卡api 更新如意卡[請款成功]狀態失敗 id_id=' . $order_data['id_id'] . ' io_transaction_id=' . $order_data['io_transaction_id'] . ' result:' . $ret["data"]["data"][0]["msg"]);
    	}
    } else {
    	// 呼叫API失敗
    	writeLog('如意卡api 呼叫API失敗失敗2 id_id=' . $order_data['id_id'] . ' io_transaction_id=' . $order_data['io_transaction_id'] . ' result:' . $ret["error"]);
    }
    writeLog('更新如意卡[請款成功]狀態api end， io_id=' . $orderId );
}
else if ($returnCode == 19) {
    $data2 = array();
    $data2["order_id"] = $orderId;
    $data2["order_status"] = 19;
    $data2["remark"] = '授權失敗';
    $result = $tripitta_api_client_service->idealcard_update_order_status_for_airport($data2);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
// if (is_dev()) printmsg($result);
    if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
        // 不應發生，再試一次
        $result = $tripitta_api_client_service->idealcard_update_order_status_for_airport($data);

        if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
            writeLog('訂單授權狀態更新為授權失敗時發生錯誤 order_id=' . $orderId . ' result:' . json_encode($result, JSON_UNESCAPED_UNICODE));
        }
    }

    // 授權失敗更新如意卡狀態
    $idealcard_service = new idealcard_service();
    writeLog('更新如意卡[授權失敗]狀態api start， io_id=' . $orderId );
    $item = array();
    $item["status"] = 2;
    $item["orderid"] = $order_data['io_transaction_id'];
    $ret = $idealcard_service -> idealcard_update($item);
    writeLog(json_encode($ret, JSON_UNESCAPED_UNICODE));

    if($ret["code"] == "0000") {
    	if($ret["data"]["data"][0]["code"] == "0000") {
    		// 新增訂單更新同步狀態
    		$data = array();
    		$data["order_id"] = $orderId;
    		$data["status"] = 20;
    		$data["barcode"] = null;
    		$tripitta_api_client_service->idealcard_update_order_barcode_for_airport($data);
    	}else {
    		// 新增訂單失敗
    		writeLog('如意卡api 更新如意卡[授權失敗]失敗 id_id=' . $order_data['id_id'] . ' io_transaction_id=' . $order_data['io_transaction_id'] . ' result:' . $ret["data"]["data"][0]["msg"]);
    	}
    } else {
    	// 呼叫API失敗
    	writeLog('如意卡api 呼叫API失敗失敗3 id_id=' . $order_data['id_id'] . ' io_transaction_id=' . $order_data['io_transaction_id'] . ' result:' . $ret["error"]);
    }
    writeLog('更新如意卡[授權失敗]狀態api end， io_id=' . $orderId );
}
else {
    writeLog('授權中心的returnCode錯誤 order_id=' . $orderId . ' returnCode=' . $returnCode . ' result:' . json_encode($xmlAry, JSON_UNESCAPED_UNICODE));

    // 尚未回覆更新如意卡狀態
    $idealcard_service = new idealcard_service();
    writeLog('更新如意卡[國泰未回覆]狀態api start， io_id=' . $orderId );
    $item = array();
    $item["status"] = 2;
    $item["orderid"] = $order_data['io_transaction_id'];
    $ret = $idealcard_service -> idealcard_update($item);
    writeLog(json_encode($ret, JSON_UNESCAPED_UNICODE));

    if($ret["code"] == "0000") {
    	if($ret["data"]["data"][0]["code"] == "0000") {
    		// 新增訂單更新同步狀態
    		$data = array();
    		$data["order_id"] = $orderId;
    		$data["status"] = 20;
    		$data["barcode"] = null;
    		$tripitta_api_client_service->idealcard_update_order_barcode_for_airport($data);
    	}else {
    		// 新增訂單失敗
    		writeLog('如意卡api 更新如意卡[授權失敗]失敗 id_id=' . $order_data['id_id'] . ' io_transaction_id=' . $order_data['io_transaction_id'] . ' result:' . $ret["data"]["data"][0]["msg"]);
    	}
//     	if($ret["data"]["data"][0]["code"] == "0000") {
// 			// 成功不處理
//     	}else {
//     		// 新增訂單失敗
//     		writeLog('如意卡api 更新如意卡[國泰未回覆]失敗 id_id=' . $order_data['id_id'] . ' io_transaction_id=' . $order_data['io_transaction_id'] . ' result:' . $ret["data"]["data"][0]["msg"]);
//     	}
    } else {
    	// 呼叫API失敗
    	writeLog('如意卡api 呼叫API失敗失敗3 id_id=' . $order_data['id_id'] . ' io_transaction_id=' . $order_data['io_transaction_id'] . ' result:' . $ret["error"]);
    }
    writeLog('更新如意卡[國泰未回覆]狀態api end， io_id=' . $orderId );

    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - cathaybk auth授權中心的returnCode錯誤 order_id=' . $orderId . ' returnCode=' . $returnCode, json_encode($xmlAry, JSON_UNESCAPED_UNICODE));
    alertmsg('銀行回傳的授權資料錯誤!!', $error_url);
}
writeLog('cathaybk auth order_id=' . $orderId . ' od_banking_status=' . $returnCode . ' action=' . $action);


if ('N7' == $bankReturnCode) {
    $returnDesc = '卡號資料錯誤';
}
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));


// if (is_dev()) exit();
?>
<form name="form1" method="post" action="<?php echo $action?>">
<?php if ($is_success) {?>
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
<input type="hidden" name="proId" value="<?php echo $proId ?>"/>
<input type="hidden" name="barcode" value="<?php echo $order_data['io_barcode']?>"/>
<?php } else {?>
<input type="hidden" name="lang" value="<?php echo $lang ?>">
<input type="hidden" name="curLang" value="<?php echo $curLang?>"/>
<input type="hidden" name="curDevice" value="<?php echo $curDevice?>"/>
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
<input type="hidden" name="barcode" value="<?php echo $order_data['io_barcode']?>"/>
<input type="hidden" name="store_id" value="<?php echo $order_data['io_store_id']?>"/>
<input type="hidden" name="return_trans_id" value="<?php echo $transactionId?>"/>
<input type="hidden" name="return_code" value="<?php echo $returnCode?>"/>
<input type="hidden" name="return_desc" value="<?php echo $returnDesc?>"/>
<input type="hidden" name="proId" value="<?php echo $proId ?>"/>
<?php /*?>
<input type="hidden" name="bank_return_code" value="<?php echo $bankReturnCode?>"/>
<input type="hidden" name="bank_status_code" value="<?php echo $bankStatusCode?>"/>
<input type="hidden" name="bank_auth_code" value="<?php echo $bankAuthCode?>"/>
<?php */?>
<input type="hidden" name="bank_trans_date" value="<?php echo $bankTransDate?>"/>
<input type="hidden" name="bank_resp_msg" value="<?php echo $bankRespMsg?>"/>
<input type="hidden" name="userEmail" value="<?php echo $userEmail ?>"/>
<?php }?>
</form>
<script type="text/javascript">document.form1.submit();</script>
</body>
</html>
<?php
/**
 *  說明：tripitta 機場臨櫃 - 玉山支付寶授權返回
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


$transactionId = get_val("return_trans_id");
$returnCode = get_val("return_code");
$returnDesc = get_val("return_desc");
$bankOrderId = get_val("bank_order_id");
$bankTransDate = get_val("bank_trans_date");
$proId = get_val("proId");

// 檢查必要資料
if (!is_dev() && (empty($transactionId) || empty($returnCode))) {
	writeLog('授權返回資料錯誤 ip:' . $_SERVER['REMOTE_ADDR']);
	alertmsg('授權返回資料錯誤!!', $error_url);
}
writeLog('auth.esunbk.alipay ip:' . $_SERVER['REMOTE_ADDR'] . ' ' . json_encode($_REQUEST, JSON_UNESCAPED_UNICODE));


$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);


// 取得訂單資料
$data = array();
$data["trans_id"] = $transactionId;
$result = $tripitta_api_client_service->idealcard_get_order_for_airport($data);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
$order_data = NULL;
if ('0000' == $result['code']) {
	if ('0000' == $result['data']['code']) {
		$order_data = $result['data']['data'];
	}
	// if (is_dev()) printmsg($order_data);
}
$orderId = $order_data['io_id'];
if (!is_dev() && empty($orderId)) {
	writeLog('auth.esunbk.alipay order_id is empty return_trans_id=' . $transactionId . ' return_code=' . $returnCode . ' ip:' . $_SERVER['REMOTE_ADDR']);
	alertmsg('訂單資料錯誤，尚未進行授權!!', $error_url);
}


// for test
// if (is_dev()) {
//     $orderId = 93;
// }


// 預設為授權失敗
$is_success = false;
$action = $potocal . $_SERVER['SERVER_NAME'] . '/event/card4g/booking_fail.php';


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
            sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - esunbk.alipay auth訂單授權狀態更新為授權請款成功發生錯誤(非常緊急) order_id=' . $orderId, json_encode($result, JSON_UNESCAPED_UNICODE));
            alertmsg('訂單資料寫入失敗[授權成功]!!', $error_url);
        }
    }

    // 新增email job
//     $tripitta_homestay_service = new tripitta_homestay_service();
//     $tripitta_homestay_service->add_order_finish_email_job('order.email', 'user', $orderId);

    $is_success = true;
    // 成功寫回如意卡訂單
    $action = $potocal . $_SERVER['SERVER_NAME'] . '/event/card4g/api_process.php';
}
else if ($returnCode == 19) {
    $data = array();
    $data["order_id"] = $orderId;
    $data["banking_status"] = 19;
    $data["remark"] = '授權失敗';
    $result = $tripitta_api_client_service->idealcard_update_order_status_for_airport($data);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
// if (is_dev()) printmsg($result);

    if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
        // 不應發生，再試一次
        $result = $tripitta_api_client_service->idealcard_update_order_status_for_airport($data);

        if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
            writeLog('訂單授權狀態更新為授權失敗時發生錯誤 order_id=' . $orderId . ' result:' . json_encode($result, JSON_UNESCAPED_UNICODE));
        }
    }
}
else {
    writeLog('授權中心的returnCode錯誤 order_id=' . $orderId . ' returnCode=' . $returnCode . ' result:' . json_encode($_REQUEST, JSON_UNESCAPED_UNICODE));
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . '機場臨櫃 - esunbk.alipay auth授權中心的returnCode錯誤 order_id=' . $orderId . ' returnCode=' . $returnCode, json_encode($_REQUEST, JSON_UNESCAPED_UNICODE));
    alertmsg('銀行回傳的授權資料錯誤!!', $error_url);
}
writeLog('esunbk.alipay auth order_id=' . $orderId . ' od_banking_status=' . $returnCode . ' action=' . $action);


$curLang = 'tw';
$curDevice = 'computer';
if (!empty($order_data['io_language'])) $curLang = $order_data['io_language'];
if (!empty($order_data['io_device'])) $curDevice = $order_data['io_device'];
?>
<form name="form1" method="post" action="<?php echo $action?>">
<?php if ($is_success) {?>
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
<input type="hidden" name="proId" value="<?php echo $proId ?>"/>
<?php } else {?>
<input type="hidden" name="curLang" value="<?php echo $curLang?>"/>
<input type="hidden" name="curDevice" value="<?php echo $curDevice?>"/>
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
<input type="hidden" name="store_id" value="<?php echo $order_data['io_store_id']?>"/>
<input type="hidden" name="return_trans_id" value="<?php echo $transactionId?>"/>
<input type="hidden" name="return_code" value="<?php echo $returnCode?>"/>
<input type="hidden" name="return_desc" value="<?php echo $returnDesc?>"/>
<?php /*?>
<input type="hidden" name="bank_return_code" value="<?php echo $bankReturnCode?>"/>
<input type="hidden" name="bank_status_code" value="<?php echo $bankStatusCode?>"/>
<input type="hidden" name="bank_auth_code" value="<?php echo $bankAuthCode?>"/>
<?php */?>
<input type="hidden" name="bank_trans_date" value="<?php echo $bankTransDate?>"/>
<input type="hidden" name="bank_resp_msg" value=""/>
<?php }?>
</form>
<script type="text/javascript">document.form1.submit();</script>
</body>
</html>
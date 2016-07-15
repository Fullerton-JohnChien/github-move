<?php
/**
 * 說明：Tripitta 銀聯卡授權返回
 * 作者：Steak
 * 日期：2015年12月24日
 * 備註：
 */
include_once('../../config.php');
$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);
writeLog('授權後導回, transactionId=' . $_REQUEST['return_trans_id']);

// 發生錯誤時導向url
$potocal = 'https://';
if (is_dev() || is_alpha()) $potocal = 'http://';
$error_url = $potocal . $_SERVER['SERVER_NAME'];

// return_code 1:accesskey錯誤, 2:accesskey逾時, 3:交易處理中, 4:該accesskey的要求已處理, 5:payment系統錯誤
//           , 6:金額錯誤, 7:交易資料不足, 8:, 9:交易過程發生錯誤
// 			   10:授權成功, 19:授權失敗
$returnCode = $_REQUEST['return_code'];
$returnDesc = $_REQUEST['return_desc'];
$bankReturnCode = $_REQUEST['bank_return_code'];
$bankStatusCode = $_REQUEST['bank_status_code'];
$bankAuthCode = $_REQUEST['bank_auth_code'];
$bankTransDate = $_REQUEST['bank_trans_date'];
$bankRespMsg = $_REQUEST['bank_resp_msg'];
// $bankOrderId = $_REQUEST['bank_order_id'];
// $alipayOrderId = $_REQUEST['alipay_order_id'];
$transactionId = $_REQUEST['return_trans_id'];

$transContent = 'return_trans_id=' . $transactionId;
$transContent .= ', return_code=' . $returnCode;
$transContent .= ', return_desc=' . $returnDesc;
$transContent .= ', bank_return_code=' . $bankReturnCode;
$transContent .= ', bank_status_code=' . $bankStatusCode;
$transContent .= ', bank_auth_code=' . $bankAuthCode;
$transContent .= ', bank_trans_date=' . $bankTransDate;
$transContent .= ', bank_resp_msg=' . $bankRespMsg;


writeLog('授權後導回, ' . $transContent);

if (empty($transactionId)) {
    alertmsg('查無返回的交易序號!!', $error_url);
}

if (empty($returnCode)) {
	writeLog('afterAuth returnCode 錯誤, transactionId=' . $transactionId . ', returnCode=' . $returnCode);
	sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'afterAuth returnCode 錯誤, transactionId=' . $transactionId . ', returnCode=' . $returnCode, $transContent);
	alertmsg('afterAuth returnCode 錯誤!!', $error_url);
}

// 取得訂單資料
$data = array();
$data["trans_id"] = $transactionId;
$result = $tripitta_api_client_service->get_order($data);
$order_detail = NULL;
if ('0000' == $result['code']) {
	if ('0000' == $result['data']['code']) {
		$order_detail = $result['data']['data'];
	}
}

if (empty($order_detail)) {
	writeLog('auth.hitrust order is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' trans_id=' . $transactionId);
	alertmsg('訂單資料錯誤!!', $error_url);
}

// 訂單ID
$orderId = $order_detail["od_id"];

// 由 payment 授權後，將訂單狀態更新為授權返回
$data = array();
$data["order_id"] = $orderId;
$data["order_status"] = 20;
$data["remark"] = 'hitrust授權返回';
$result = $tripitta_api_client_service->update_order_status($data);

if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
	// 不應發生，再試一次
	$result = $tripitta_api_client_service->update_order_status($data);

	if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
		writeLog('訂單狀態更新為授權返回時發生錯誤 od_id=' . $orderId . ' result:' . json_encode($result, JSON_UNESCAPED_UNICODE));
		sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - cathaybk.china.union auth訂單狀態更新為授權返回錯誤(非常緊急) od_id=' . $orderId, json_encode($result, JSON_UNESCAPED_UNICODE));

		// 取消訂單
		$data = array();
		$data["order_id"] = $orderId;
		$data["cancel_rule"] = 1; // 1:無扣款取消
		$data["remark"] = '訂單狀態更新為授權返回時發生錯誤';
		$ret = $tripitta_api_client_service->cancel_order($data);
		writeLog('tripitta_api_client_service->cancel_order od_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));

		alertmsg('訂單資料寫入失敗!!', $error_url);
	}
}


if ($returnCode == 10) {
    $data = array();
    $data["order_id"] = $orderId;
    $data["banking_status"] = 20;
    $data["remark"] = '授權請款成功';
    $result = $tripitta_api_client_service->finish_order($data);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
// if (is_dev()) printmsg($result);
    if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
        // 不應發生，再試一次
        $result = $tripitta_api_client_service->finish_order($data);

        if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
            writeLog('訂單授權狀態更新為授權請款成功時發生錯誤 od_id=' . $orderId . ' result:' . json_encode($result, JSON_UNESCAPED_UNICODE));
            sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - 銀聯卡 auth訂單授權狀態更新為授權請款成功發生錯誤(非常緊急) od_id=' . $orderId, json_encode($result, JSON_UNESCAPED_UNICODE));

            // 取消訂單
            $data = array();
            $data["order_id"] = $orderId;
            $data["cancel_rule"] = 1; // 1:無扣款取消
            $data["remark"] = '訂單授權狀態更新為授權請款成功時發生錯誤';
            $ret = $tripitta_api_client_service->cancel_order($data);
            writeLog('tripitta_api_client_service->cancel_order od_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));

            alertmsg('訂單資料寫入失敗[授權成功]!!', $error_url);
        }
    }

    // 通知 EZ訂 訂單已付款 (tripitta 沒有處理 ezding 包房的情形，如有需要需自行加上該邏輯 by John 2015-11-23)
    $order_homestay = $order_detail['order.homestay'];
    foreach ($order_homestay as $item) {
        $ezding_order_id = $item['oh_partner_trans_id'];
        $data = array();
        $data['oh_detail_id'] = $orderId;
        $data['oh_partner_trans_id'] = $ezding_order_id;
        $tripitta_api_client_service->pay_order($data);
    }

    // 新增email job
    $tripitta_homestay_service = new tripitta_homestay_service();
    $tripitta_homestay_service->add_order_finish_email_job('order.email', 'user', $orderId);

    $is_success = true;
    $action = $potocal . $_SERVER['SERVER_NAME'] . '/web/pages/booking/booking_finish.php';
}
else if ($returnCode == 19) {
    $data = array();
    $data["order_id"] = $orderId;
    $data["banking_status"] = 19;
    $data["remark"] = '授權失敗';
    $result = $tripitta_api_client_service->update_order_status($data);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
// if (is_dev()) printmsg($result);

    if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
        // 不應發生，再試一次
        $result = $tripitta_api_client_service->update_order_status($data);

        if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
            writeLog('訂單授權狀態更新為授權失敗時發生錯誤 od_id=' . $orderId . ' result:' . json_encode($result, JSON_UNESCAPED_UNICODE));
        }
    }

    // 取消訂單
    $data = array();
    $data["order_id"] = $orderId;
    $data["cancel_rule"] = 1; // 1:無扣款取消
    $data["remark"] = '訂單授權狀態更新為授權失敗時發生錯誤';
    $ret = $tripitta_api_client_service->cancel_order($data);
    writeLog('tripitta_api_client_service->cancel_order od_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));

    if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
        sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - hitrust auth訂單授權狀態更新為授權失敗發生錯誤(非常緊急) od_id=' . $orderId, json_encode($result, JSON_UNESCAPED_UNICODE));
        alertmsg('訂單資料寫入失敗[授權失敗]!!', '/web/booking/auth_fail.php?store_id='.$order_detail['od_store_id']);
    }
}
else {
    writeLog('授權中心的returnCode錯誤 od_id=' . $orderId . ' returnCode=' . $returnCode);
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - hitrust auth授權中心的returnCode錯誤 od_id=' . $orderId . ' returnCode=' . $returnCode);

    // 取消訂單
    $data = array();
    $data["order_id"] = $orderId;
    $data["cancel_rule"] = 1; // 1:無扣款取消
    $data["remark"] = '授權中心的returnCode錯誤 od_id=' . $orderId . ' returnCode=' . $returnCode;
    $ret = $tripitta_api_client_service->cancel_order($data);
    writeLog('tripitta_api_client_service->cancel_order od_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));

    alertmsg('銀行回傳的授權資料錯誤!!', '/web/booking/auth_fail.php?store_id='.$order_detail['od_store_id']);
}
writeLog('hitrust auth od_id=' . $orderId . ' od_banking_status=' . $returnCode . ' action=' . $action);


$msg = '<span class="T54_titleGreen">系統處理中</span>';
$msg2 = '請勿重新整理或關閉視窗';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tripitta 旅必達-銀聯卡授權返回</title>
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
<form name="form1" method="post" action="<?php echo $action?>">
<?php if ($is_success) {?>
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
<?php } else {?>
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
<input type="hidden" name="store_id" value="<?php echo $order_detail['od_store_id']?>"/>
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

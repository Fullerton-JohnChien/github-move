<?php
/**
 * 說明：Tripitta 銀聯卡授權返回
 * 作者：Steak
 * 日期：2015年12月24日
 * 備註：
 */
include_once('../../config.php');
$tripitta_service = new tripitta_service();
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

// 預設為授權失敗
$is_success = false;
$action = $potocal . $_SERVER['SERVER_NAME'] . '/web/pages/bookingcar/pay/pay_fail.php';

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
$order_detail = $tripitta_service->get_car_order_by_trans_id($transactionId);

if (empty($order_detail)) {
	writeLog('auth.銀聯卡  order is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' trans_id=' . $transactionId);
	alertmsg('訂單資料錯誤!!', $error_url);
}

// 訂單ID
$orderId = $order_detail["co_id"];
$type = $order_detail["co_route_type"];

// 由 payment 授權後，將訂單狀態更新為授權返回
$item = array();
$item["co_id"] = $orderId;
$item["co_status"] = 20;
// $item["remark"] = '銀聯卡授權返回';
$tripitta_service->save_or_update_car_order($item);

if ($returnCode == 10) {
    $item = array();
    $item["co_id"] = $orderId;
    $item["co_banking_status"] = 20;
    $item["co_status"] = 30;
    $item["co_modify_time"] = date("Y-m-d H:i:s");
    // $item["remark"] = '授權請款成功';
    $tripitta_service->save_or_update_car_order($item);

    // 清掉session
    unset($_SESSION["pay_step"]);

    // 新增email job
    $job_order_notify_dao = Dao_loader::__get_travel_job_order_notify_dao();
    $item = array();
    $item["jon_type"] = "car.order.email";
    $item["jon_target"] = "user";
    $item["jon_ref_id"] = $orderId;
    $item["jon_create_user"] = 2;
    $item["jon_create_time"] = date("Y-m-d H:i:s");
    $job_order_notify_dao->saveJobOrderNotifyByItem($item);
    // $tripitta_homestay_service = new tripitta_homestay_service();
    // $tripitta_homestay_service->add_order_finish_email_job('car.order.email', 'user', $orderId);

    // 新增 user sms job
    $item["jon_type"] = 'car.order.sms';
    $item["jon_create_time"] = date("Y-m-d H:i:s");
    $job_order_notify_dao->saveJobOrderNotifyByItem($item);

    $is_success = true;
    $action = $potocal . $_SERVER['SERVER_NAME'] . '/web/pages/bookingcar/pay/pay_succ.php';
}
else if ($returnCode == 19) {
    $item = array();
    $item["co_id"] = $orderId;
    $item["co_banking_status"] = 19;
    // $item["remark"] = '授權失敗';
    $tripitta_service->save_or_update_car_order($item);
}
else {
    writeLog('授權中心的returnCode錯誤 oo_id=' . $orderId . ' returnCode=' . $returnCode);
    sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - 銀聯卡 auth授權中心的returnCode錯誤 oo_id=' . $orderId . ' returnCode=' . $returnCode);
    alertmsg('銀行回傳的授權資料錯誤!!', $error_url);
}
writeLog('銀聯卡 auth oo_id=' . $orderId . ' oo_banking_status=' . $returnCode . ' action=' . $action);


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
<input type="hidden" name="type" value="<?php echo $type ?>"/>
<?php } else {?>
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
<input type="hidden" name="fr_id" value="<?php echo $order_detail['co_prod_id']?>"/>
<input type="hidden" name="type" value="<?php echo $type ?>"/>
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

<?php
/**
 *  說明：tripitta 不收費無授權訂單
 *  作者：John <john.chien@fullerton.com.tw>
 *  日期：2015年11月23日
 *  備註：
 *  2015-12-17 John 於更新訂單狀態失敗後加上取消訂單的動作
 */

include_once '../../config.php';
set_time_limit(180);
if (ob_get_level() == 0) ob_start();
// $t1 = microtime(true);

// 發生錯誤時導向url
$potocal = 'http://';
if (is_production()) $potocal = 'https://';
$error_url = $potocal . $_SERVER['SERVER_NAME'];


// 訂單資料
$pid = $_REQUEST['pid'];

// 檢查必要資料
if (!is_dev() && empty($pid)) {
    writeLog('no.charge.order pid is empty. ip:' . $_SERVER['REMOTE_ADDR']);
    alertmsg('訂單資料錯誤!!', $error_url);
}


$orderId = $_SESSION[$pid];
if (!is_dev() && empty($orderId)) {
    writeLog('no.charge.order od_id is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' pid=' . $pid);
    alertmsg('訂單資料錯誤!!', $error_url);
}
$_SESSION[$pid] = null; // 清除 session 資料
writeLog('no.charge.order od_id=' . $orderId . ' ip:' . $_SERVER['REMOTE_ADDR']);


// for test
if (is_dev()) $orderId = 93;


$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);


// 取得訂單資料
$data = array();
$data["order_id"] = $orderId;
$result = $tripitta_api_client_service->get_order($data);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
$order_detail = NULL;
if ('0000' == $result['code']) {
    if ('0000' == $result['data']['code']) {
        $order_detail = $result['data']['data'];
    }
// if (is_dev()) printmsg($order_detail);
}

if (empty($order_detail)) {
    writeLog('no.charge.order order is empty ip:' . $_SERVER['REMOTE_ADDR'] . ' od_id=' . $orderId);
    alertmsg('訂單資料錯誤!!', $error_url);
}

if ($order_detail['od_status'] != 10) {
    writeLog('訂單狀態錯誤 od_id=' . $orderId . ' od_status=' . $order_detail['od_status']);
    alertmsg('訂單狀態錯誤!!', $error_url);
}


$curLang = 'tw';
// $curDevice = 'computer';
if (!empty($order_detail['ode_language'])) $curLang = $order_detail['ode_language'];
// if (!empty($order_detail['ode_device'])) $curDevice = $order_detail['ode_device'];

$msg = 'Processing…';
$msg2 = 'Please do not refresh or close the window';
if ('tw' == $curLang) {
    $msg = '<span class="T54_titleGreen">訂單寫入中</span>';
    $msg2 = '請勿重新整理或關閉視窗';
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<? include __DIR__ . "/../common/head_new.php"; ?>
<title>Tripitta 旅必達 授權</title>
<link rel='stylesheet' type='text/css' href='/styles/style.css' />
</head>
<body>
<div class="div_introduction_allB">
<table width="150" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="40">&nbsp;</td>
  </tr>
</table>
<table width="920" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="525" class="booking_bd_gary_1px"><table width="850" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td height="170" align="center" valign="bottom" class="T45_titleGreen"><?php echo $msg?><span class="T26_titleGreen"><br />
          </span><span class="T01_titleGreen"><?php echo $msg2?></span></td>
      </tr>
      <tr>
        <td height="172" align="center" valign="top"><table width="150" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td height="10"></td>
          </tr>
        </table>
          <img src="/web/img/connection.gif" width="345" height="25" /></td>
      </tr>
    </table></td>
  </tr>
</table>
<table width="150" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="40"></td>
  </tr>
</table>
</div>
<?php
ob_flush();
flush();


$data = array();
$data["order_id"] = $orderId;
$data["banking_status"] = 20;
$data["remark"] = '無金流更新訂單狀態為完成訂單';
$result = $tripitta_api_client_service->finish_order($data);
// if (is_dev()) printmsg('spend time:' . (microtime(true) - $t1));
// if (is_dev()) printmsg($result);
if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
    // 不應發生，再試一次
    $result = $tripitta_api_client_service->finish_order($data);

    if ('0000' != $result['code'] || '0000' != $result['data']['code']) {
        writeLog('無金流更新訂單狀態為完成訂單時發生錯誤 od_id=' . $orderId . ' result:' . json_encode($result, JSON_UNESCAPED_UNICODE));
        sendmail(get_config_mail_from(), Constants::$TRIPITTA_ADMIN_EMAILS, get_config_current_site() . 'Tripitta - no charge order更新訂單狀態為完成訂單時失敗 od_id=' . $orderId, json_encode($result, JSON_UNESCAPED_UNICODE));

        // 取消訂單
        $data = array();
        $data["order_id"] = $orderId;
        $data["cancel_rule"] = 1; // 1:無扣款取消
        $data["remark"] = '無金流更新訂單狀態為完成訂單時發生錯誤';
        $ret = $tripitta_api_client_service->cancel_order($data);
        writeLog('tripitta_api_client_service->cancel_order od_id=' . $orderId . ' result:' . json_encode($ret, JSON_UNESCAPED_UNICODE));

        alertmsg('訂單資料寫入失敗!!', $error_url);
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

$action = $potocal . $_SERVER['SERVER_NAME'] . '/web/pages/booking/booking_finish.php';
?>
<form name="form1" method="post" action="<?php echo $action?>">
<input type="hidden" name="order_id" value="<?php echo $orderId?>"/>
</form>
<script type="text/javascript">document.form1.submit();</script>
</body>
</html>
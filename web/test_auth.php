<!DOCTYPE HTML>
<head>
  <meta charset="UTF-8">
<?php
include_once('config.php');

//$key = $_REQUEST["hgSessionKey"];
//$remainPoint = $_REQUEST["hgRemainPoint"];
//printmsg('hgSessionKey=' . $key);
//printmsg('remainPoint=' . $remainPoint);


//$dbReader = new pdo_reader($db_dsn_ezding, $db_uid_ezding, $db_pwd_ezding);

//$tempStorageDao = new ezding_hf_temp_storage($dbReader);
//$tempStorage = $tempStorageDao->loadHfTempStorage($key);
//$happygoLoginData = json_decode($tempStorage["ts_object"], true);
//printmsg($happygoLoginData);

$paymentUtil = new EzdingPayment($transPath, $transAccount, $transPassword);

$ret = $paymentUtil->testPayment();
// $pay_code = 'e';
// $ret = $paymentUtil->test_payment_esun_pay_auth();
printmsg($ret);

// $params = convert_pait_str_to_array($ret);
//     if (!empty($params['action'])) $action = $params['action'];
// foreach ($params as $key => $val) {
//     if ('action' == $key) continue;
//     printmsg($key . '=' . $val);
// }
// $rtnAry = $paymentUtil->queryAuth('TRCCU2014063000025', 'cathaybk.china.union');
// printmsg($rtnAry);

//$orderId = 1;
//$prefix = 'TEST';

/** 授權 **/
/*
printmsg('開始進行授權, orderId=' . $orderId);
// login payment 取得 accessKey
$t = date('YmdHi');
$pw = md5($transPassword . $t);
$loginUrl = $transPath . 'login?id=' . $transAccount . '&token=' . $pw . '&ti=' . $t;
$rtnAry = open_url($loginUrl);
printmsg($loginUrl);
printmsg('orderId=' . $orderId . ' ' . array2string($rtnAry));
printmsg($rtnAry['result']);
if ($rtnAry['status'] == 1) {
	printmsg('orderId=' . $orderId . ', login連線失敗 errorMsg=' . $rtnAry['error']);
	if($dbReader) $dbReader->closedb();
	printmsg('無法與授權中心取得連繫!');
}

$tmp = $rtnAry['result'];
$tary = preg_split('/,/', $tmp);
if (count($tary) != 2) {
	printmsg('orderId=' . $orderId . ', login回傳資料有誤, count($tary)=' . count($tary));
	if($dbReader) $dbReader->closedb();
	printmsg('無法與授權中心取得連繫!!');
}

$accessKey = $tary[1];


// 取得 transactionID
$rtnAry = open_url($transPath . 'getTransId?id=' . $transAccount . '&token=' . $pw . '&ti=' . $t . '&k=' . $accessKey . '&p=' . $prefix . '&l=16');
//writeLog('orderId=' . $orderId . ' ' . array2string($rtnAry));
if ($rtnAry['status'] == 1) {
	printmsg('orderId=' . $orderId . ', getTransId連線失敗 errorMsg=' . $rtnAry['error']);
	if($dbReader) $dbReader->closedb();
	printmsg('無法與授權中心取得連繫!!!');
}

$tmp = $rtnAry['result'];
$tary = preg_split('/,/', $tmp);
if (count($tary) != 2) {
	printmsg('orderId=' . $orderId . ', getTransId回傳資料有誤, count($tary)=' . count($tary));
	if($dbReader) $dbReader->closedb();
	alertmsg('無法與授權中心取得連繫!!!!');
}

$transactionId = $tary[1];
printmsg('transactionId=' . $transactionId);
//$rtn = $orderDao->updateTransactionIdByOrderId($orderId, $transactionId);
if ($rtn != 1) {
//	sendmail($mail_from, $TRAVEL_ADMIN_EMAILS, $siteStr . '民宿EZ訂 - 更新transactionId失敗 - orderId=' . $orderId . ', transactionId=' . $transactionId, 'rtn=' . $rtn);
//	printmsg('訂單編號更新失敗，尚未授權!!');
}

// d:1 自動請款, n:card number, y:有效年, m:有效月, c:cvc2, a:交易金額, b:bonus, i:order info, p:訂單編號 prefix
$authURL = $transPath . 'auth'
	. '?id=' . $transAccount
	. '&token=' . $pw
	. '&ti=' . $t
	. '&k=' . $accessKey
	. '&s=' . $transSum
	. '&o=' . $transactionId
	. '&a=' . '123'
	. '&b=' . '1'
//	. '&d=1'
	. '&z=happygo'
	. '&idno=' . urlencode($happygoLoginData["ENC_IDNO"])
	. '&chksum=' . urlencode($happygoLoginData["CHK_SUM"])
	. '&hgToken=' . urlencode($happygoLoginData["TOKEN"])
	;
printmsg('orderId=' . $orderId . ', authurl=' . $authURL);
$rtnAry = open_url($authURL, null, null, 180, null); // hitrust 60 秒 time out
//	writeLog('orderId=' . $orderId . ' ' . array2string($rtnAry));
if ($rtnAry['status'] == 1) {
	printmsg('orderId=' . $orderId . ', auth連線失敗 errorMsg=' . $rtnAry['error']);
	if($dbReader) $dbReader->closedb();
	printmsg('無法與授權中心取得連繫!!2');
}

// return_code 1:accesskey錯誤, 2:accesskey逾時, 3:交易處理中, 4:該accesskey的要求已處理, 5:payment系統錯誤
//           , 6:金額錯誤, 7:交易資料不足, 8:, 9:交易過程發生錯誤
// 			   10:授權成功, 19:授權失敗
$xmlAry = xml_to_array($rtnAry['result']);
printmsg('orderId=' . $orderId . ' ' . array2string($xmlAry));

$transactionId = $xmlAry['return_trans_id'];
$returnCode = $xmlAry['return_code'];
$returnDesc = $xmlAry['return_desc'];
$bankReturnCode = $xmlAry['bank_return_code'];
$bankStatusCode = $xmlAry['bank_status_code'];
$bankAuthCode = $xmlAry['bank_auth_code'];
$bankTransDate = $xmlAry['bank_trans_date'];
$bankRespMsg = $xmlAry['bank_resp_msg'];
*/
?>
</head>

<body>
</body>
</html>
<?php
function convert_pait_str_to_array($odc_ret) {
    if(empty($odc_ret)) return array();
    $ret = array();
    $t = preg_split('/,/', $odc_ret);
    foreach($t as $t1) {
        list($key, $val) = preg_split('/=/', $t1);
        $ret[$key] = urldecode($val);
    }
    return $ret;
}

function test_esun_auth($pay_code) {
    if($pay_code == 'esun.pay') {
        $auth_ret = EzdingPayment::test_payment_esun_pay_auth($pay_code, 0);
    } else {
        $auth_ret = EzdingPayment::test_payment_esun_pay_auth($pay_code, 1);
    }
    $ret = convert_pait_str_to_array($auth_ret);
    printmsg($ret);
    ?>
    <form name="form1" id="form1" action="<?= $ret["action"] ?>" method="post" target="_blank">
    <input type="text" name="MID" value="<?= $ret["MID"] ?>" /><br>
    <input type="text" name="CID" value="<?= $ret["CID"] ?>" /><br>
    <input type="text" name="TID" value="<?= $ret["TID"] ?>" /><br>
    <input type="text" name="ONO" value="<?= $ret["ONO"] ?>" /><br>
    <input type="text" name="TA" value="<?= $ret["TA"] ?>" /><br>
    <input type="text" name="U" value="<?= $ret["U"] ?>" /><br>
    <input type="text" name="M" value="<?= $ret["M"] ?>" /><br>
    <input type="text" name="BPF" value="<?= $ret["BPF"] ?>" /><br>
    </form>
    <input type="button" value="submit" onclick="dosubmit()">
    <script>
    function dosubmit() {
     document.form1.submit();
    }
    </script>
    <?
}
?>


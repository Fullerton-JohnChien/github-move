<?php
include_once ('config.php');

function ajax_send_reset_password() {
	$account = get_val('account');
	$account = 'connie_6261@hotmail.com';
	$tripitta_web_service = new tripitta_web_service();
printmsg(constants_user_center::USER_CATEGORY_TRIPITTA);
	$ret = $tripitta_web_service->get_reset_password_code(constants_user_center::USER_CATEGORY_TRIPITTA, $account);
printmsg($ret);
	if ($ret["status"] == 0) {
		return ajax_get_result('9999', $ret["msg"]);
	}
	$token_data = $ret["msg"];
printmsg('3');
	$ret = $tripitta_web_service->get_user_data_by_category_and_account(constants_user_center::USER_CATEGORY_TRIPITTA, $account);
// 	writeLog(__FUNCTION__ . ', ret:' . json_encode($ret, JSON_UNESCAPED_UNICODE));
	if ($ret["status"] == 0) {
		return ajax_get_result('9999', $ret["msg"]);
	}
	$user_data = $ret["msg"];
	$email = $user_data["email"];
printmsg($user_data);
	// expireTime: "2015-10-31 18:18:40"
	// id: "0AA806D7C158A8C067D3792C6A9615C8"
	// serialId: "1041618"
	// token: "D868DA52F91CFC3C786C98A3687536DA"
	// 組合token當重置密碼頁之參數 base64encode(id + token)
	$token = base64_encode($token_data["id"] . $token_data["token"]);
printmsg($email);
printmsg($token);
	$ret = EzdingUserUtil::get_ezding_user_util()->send_tripitta_reset_password_mail($email, $token);

	return ajax_get_result('0000', '', $ret);
}

$ret = ajax_send_reset_password();
printmsg($ret);
?>
hi john
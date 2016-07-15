<?php
/**
 * 說明：個人資料 - (取得會員基本資料編輯會員)
 * 作者：Casper <casper.lee@fullerton.com.tw>
 * 日期：2016年5月18日
 * 備註：
 */
require_once __DIR__ . '/../../config.php';

// 檢查會員是否登入
$request_uri = $_SERVER["REQUEST_URI"];
$do_check_login = false;
$popup_login = false;
if(!empty($login_user_data)){
	$do_check_login = true;
}else{
	$popup_login = true;
}
if(strpos($request_uri, '/member/') !== false) {
	if(strpos($request_uri, '/member/reset_password/') === false) {
		$do_check_login = true;
	}
}
$tripitta_web_service = new tripitta_web_service();
$display_currency_id = $tripitta_web_service->get_display_currency(0);

$login_user_data = $tripitta_web_service->check_login();
// printmsg(array2string($login_user_data, false));
$email_verify_status = 0;
// $tripitta_web_service->is_user_exists(constants_user_center::USER_CATEGORY_TRIPITTA, 'cheans.huang@fullerton.com.tw');
// $tripitta_web_service->is_user_exists(constants_user_center::USER_CATEGORY_TRIPITTA, 'cheans123@fullerton.com.tw');
// test
//$_SESSION[USER_DATA] = null;
//if(is_dev() && empty($login_user_data)) $login_user_data = $tripitta_web_service->login(user_center_user_category::TRIPITTA, 'john1@fullerton.com.tw', '123456');
// printmsg($login_user_data);
// printmsg($tripitta_web_service->is_login());
$header_is_login = 0;
if(!empty($login_user_data)) {
	$header_is_login = 1;
	$avatar = $login_user_data["avatar"];
	//     $account = $login_user_data["account"];
	$email = $login_user_data["email"];
	$email_verify_status = $login_user_data["emailVerifyStatus"];
	$name = $login_user_data["name"];
	$gender = $login_user_data["gender"];
	$nickname = $login_user_data["nickname"];
	$birthday = $login_user_data["birthday"];
	$married = $login_user_data["married"];
	$education = $login_user_data["education"];
	$family = $login_user_data["family"];
	$living_tel_country_id = $login_user_data["living_tel_country_id"];
	$living_country_id = $login_user_data["living_country_id"];
	$mobile = $login_user_data["mobile"];
	$living_area_id = $login_user_data["living_area_id"];
	$living_address = $login_user_data["living_address"];

	// 目前暫時拿來替代訂閱電子報的值，未來會修改？
	$epaper_homestay = $login_user_data["epaperHomeStay"];
} else {
	if($do_check_login) {
		$popup_login = true;
	}
}
if (empty($gender)) $gender = '';
if (empty($married)) $married = '';
// $email_verify_status = 1;

// 會員登入
//     $tripitta_web_service = new tripitta_web_service();
//     $login_user_data = $tripitta_web_service->check_login();

//     if(is_dev() && empty($login_user_data)){
//         $login_user_data = $tripitta_web_service->login(user_center_user_category::TRIPITTA, 'john1@fullerton.com.tw', '123456');
//         printmsg($login_user_data);
//     }

//     $is_login = empty($login_user_data) ? false:true;
//     if($is_login) {
//         $user_name = $login_user_data["name"];
//         $avatar = $login_user_data["avatar"];
//     }
/*
 // 新增會員
 $add_user_data = array();
 $add_user_data["category"] = user_center_user_category::TRIPITTA;
 $add_user_data["userAccount"] = "john1@fullerton.com.tw";
 $add_user_data["userPassword"] = md5('123456'); // generateSerno(6);
 $add_user_data["userName"] = "John測試";
 $add_user_ret = $tripitta_web_service->add_user($add_user_data);
 printmsg($add_user_ret);
 */
?>
<script src="/web/js/common.js"></script>
<?php
if(!preg_match('/\/member\/profile\//', $request_uri)
	&& !preg_match('/\/collection_list\//' , $request_uri)){
	include 'header_javascript.php';
}
?>
<?php
include_once ('../config.php');
if(is_readable(__DIR__ . '/../../inc/common.php')) {
    include_once ('../../inc/utils/imagick_service.php');
} else {
    include_once ('../../../../inc/utils/imagick_service.php');
}
if (!empty($_GET)) writeLog('$_GET ' . json_encode($_GET, JSON_UNESCAPED_UNICODE));
if (!empty($_POST)) writeLog('$_POST ' . json_encode($_POST, JSON_UNESCAPED_UNICODE));

$func = get_val('func');

$ret = array();
if('add_favorite' == $func) {
    $ret = ajax_add_favorite();
} else if ('login' == $func) {
    $ret = ajax_do_login();
} else if ('login_facebook' == $func) {
    $ret = ajax_do_login_facebook();
} else if ('login_ios_facebook' == $func) {
    $ret = ajax_do_login_ios_facebook();
} else if ('login_gplus' == $func) {
    $ret = ajax_do_login_gplus();
} else if ('logout' == $func) {
    $ret = ajax_do_logout();
} else if ('update_user_data' == $func) {
    $ret = ajax_update_user_data();
} else if ('cancel_order' == $func) {
    $ret = ajax_cancel_order();
} else if ('cancel_car_order' == $func) {
    $ret = ajax_cancel_car_order();
} else if ('cancel_ticket_order' == $func) {
    $ret = ajax_cancel_ticket_order();
} else if ('check_user_by_email' == $func) {
    $ret = ajax_check_user_by_email();
} else if ('find_order_by_trans_id' == $func) {
    $ret = ajax_find_order_by_trans_id();
} else if ('check_user_account_exists' == $func) {
    $ret = ajax_check_user_account_exists();
} else if ('find_homestay_for_booking_hom_by_area_ids' == $func) {
    $ret = ajax_find_homestay_for_booking_hom_by_area_ids();
} else if ('find_homestay_for_booking_hom_by_recommend_type' == $func) {
    $ret = ajax_find_homestay_for_booking_hom_by_recommend_type();
} else if ('find_fleet_route_rand_search_list_car' == $func) {
    $ret = ajax_find_fleet_route_rand_search_list_car();
} else if ('find_fleet_route_rand_search_list_bus' == $func) {
    $ret = ajax_find_fleet_route_rand_search_list_bus();
} else if ('find_ticket_price' == $func) {
    $ret = ajax_find_ticket_price();
} else if ('find_valid_trip_plan_for_booking_home' == $func) {
    $ret = ajax_find_valid_trip_plan_for_booking_home();
} else if ('find_valid_travel_plan_for_trip_home' == $func) {
    $ret = ajax_find_valid_travel_plan_for_trip_home();
} else if ('register' == $func) {
    $ret = ajax_register();
} else if ('register_facebook' == $func) {
    $ret = ajax_register_facebook();
} else if ('register_gplus' == $func) {
    $ret = ajax_register_gplus();
} else if ('send_reset_password' == $func) {
    $ret = ajax_send_reset_password();
} else if ('send_member_verify_email' == $func) {
    $ret = ajax_send_member_verify_email();
} else if ('reset_user_password' == $func) {
    $ret = ajax_reset_user_password();
} else if ('sync_avatar_image' == $func) {
    $ret = ajax_sync_user_avatar();
} else if ('update_user_avatar' == $func) {
    $ret = ajax_update_user_avatar();
} else if ('update_user_password' == $func) {
    $ret = ajax_update_user_password();
} else if ('qa' == $func) {
    $ret = ajax_qa();
} else if ($func == 'checkCaptchaCode') {
    $ret = ajax_check_aptcha_code();
} else if ('remove_user_favorite' == $func) {
    $ret = ajax_remove_user_favorite();
} else if ('resend_verify_email' == $func) {
    $ret = ajax_resend_verify_email();
} else if ('set_display_currency' == $func) {
    $ret = ajax_set_display_currency();
} else if ('find_user_by_email' == $func) {
    $ret = ajax_find_user_by_email();
} else if ('release_room' == $func) {
    $ret = ajax_release_room();
} else if ('check_coupon' == $func) {
    $ret = ajax_check_coupon();
} else if ('find_my_recommend_content_distance' == $func) {
	$ret = ajax_my_recommend_content_distance();
} else if ('find_my_recommend_nearby_content_distance' == $func) {
	$ret = ajax_my_recommend_nearby_content_distance();
} else if ('send_proof_email' == $func){
	$ret = ajax_proof_email();
} else if ('cal_ticket_order_refund_price' == $func){
	$ret = ajax_ticket_order_refund_price();	
} else if ('modify_passenger' == $func){
	$ret = ajax_modify_passenger();	
}

writeLog('ajax ret : ' . json_encode($ret, JSON_UNESCAPED_UNICODE));
echo json_encode($ret, JSON_UNESCAPED_UNICODE);


function ajax_add_favorite() {
    $user_id = get_val('user_id');
    $ref_type = get_val('ref_type');
    $ref_id = get_val('ref_id');
    $tripitta_web_service = new tripitta_web_service();

    $data = [];
    $data['user_id'] = $user_id;
    $items = [];
    $items[] = array('type_id'=>$ref_type, 'ref_id'=>$ref_id);
    $data['items'] = $items;
    $api_ret = $tripitta_web_service->add_user_favorite($data);

    if($api_ret["code"] != "0000") {
        return $api_ret;
    }
    $act_ret = $api_ret["data"];
    if($act_ret["code"] != "0000") {
        return $act_ret;
    }

    return ajax_get_result('0000', '');
}

/**
 * 取消訂單
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_cancel_order() {
    $order_id = get_val('order_id');
    $user_id = get_val('user_id');
    if (empty($order_id) || empty($user_id)) {
        return ajax_get_result('9999', '參數錯誤');
    }

    $data = array();
    $data["order_id"] = $order_id;
    $data["cancel_rule"] = 2; // 取消規則 1:不扣款取消 2:依規則扣款取消
    $data["remark"] = '使用者前台取消 IP:' . get_remote_ip();

    $tripitta_web_service = new tripitta_web_service();
    $api_ret = $tripitta_web_service->cancel_odc_order($data);
    writeLog(__FUNCTION__  . ', ' . json_encode($api_ret, JSON_UNESCAPED_UNICODE));
    if($api_ret["code"] != "0000") {
        return $api_ret;
    }
    $cancel_ret = $api_ret["data"];
    if($cancel_ret["code"] != "0000") {
        return $cancel_ret;
    }
    return ajax_get_result('0000', '');
}

/**
 * 取消包租車訂單
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_cancel_car_order() {
	$order_id = get_val('order_id');
	$user_id = get_val('user_id');
	if (empty($order_id) || empty($user_id)) {
		return ajax_get_result('9999', '參數錯誤');
	}
	$cancel_rule = 2;
	$tripitta_car_order_service = new tripitta_car_order_service();
	$api_ret = $tripitta_car_order_service->app_cancel_car_order($order_id, $user_id, $cancel_rule);
	writeLog(__FUNCTION__  . ', ' . json_encode($api_ret, JSON_UNESCAPED_UNICODE));
	if ($api_ret["code"] != "0000") {
		return ajax_get_result('9999', $api_ret["msg"]);
	} else {
		// 新增email job
	    $job_order_notify_dao = Dao_loader::__get_travel_job_order_notify_dao();
	    $item = array();
	    $item["jon_type"] = "car.cancel.order.email";
	    $item["jon_target"] = "user";
	    $item["jon_ref_id"] = $order_id;
	    $item["jon_create_user"] = 2;
	    $item["jon_create_time"] = date("Y-m-d H:i:s");
	    $job_order_notify_dao->saveJobOrderNotifyByItem($item);
	    
		return ajax_get_result('0000', '');
	}
}

/**
 * 取消高鐵訂單
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_cancel_ticket_order() {
	$order_id = get_val('order_id');
	$user_id = get_val('user_id');
	$cancel_ids_array = get_val('cancel_ids');
	if (empty($order_id) || empty($user_id) || empty($cancel_ids_array)) {
		return ajax_get_result('9999', '參數錯誤');
	}
	$cancel_ids = explode(",", $cancel_ids_array);
	$tripitta_ticket_order_service = new tripitta_ticket_order_service();
	$api_ret = $tripitta_ticket_order_service->cancel_order($order_id, $user_id, $cancel_ids);
	writeLog(__FUNCTION__  . ', ' . json_encode($api_ret, JSON_UNESCAPED_UNICODE));
	if ($api_ret["code"] != "0000") {
		return ajax_get_result('9999', $api_ret["msg"]);
	}
	return ajax_get_result('0000', '');
}

function ajax_check_user_by_email() {
	$email = get_val('email');
	$tripitta_web_service = new tripitta_web_service();
	$ret = $tripitta_web_service->is_user_exists('tripitta',$email);
	if ($ret["status"] == 0) {
		return ajax_get_result('9999', '連結會員中心失敗');
	} else {
		return ajax_get_result('0000', $ret["msg"]);
	}
}

/**
 * 查詢訂單
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_find_order_by_trans_id() {
	$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);
	$trans_id = get_val('trans_id');
	$user_id = get_val('user_id');
	if (!empty($trans_id) && !empty($user_id)) {
		$cond = array();
		$cond['trans_id'] = $trans_id;
		$cond['user_id'] = $user_id;
	}
	$api_ret_array = $tripitta_api_client_service->get_order($cond);
	if ($api_ret_array['code'] == "0000") {
		$api_ret = array();
		if (isset($api_ret_array['data'])) {
			$api_ret = $api_ret_array['data']['data'];
			if (isset($api_ret['order.homestay'])) {
				$api_ret['oh_check_in_date'] = $api_ret['order.homestay'][0]['oh_check_in_date'];
				$api_ret['oh_store_id'] = $api_ret['order.homestay'][0]['oh_store_id'];
				$home_stay_dao = Dao_loader::__get_home_stay_dao();
				$store = $home_stay_dao->findWithLang($api_ret['oh_store_id'], $api_ret['ode_language']);
				$api_ret['areaName'] = $store[0]['areaName'];
				$api_ret['hsName'] = $store[0]['hs_name'];
				unset($api_ret['order.homestay']);
			} else {
				$result = ajax_get_result('9999', '請確認訂單編號是否正確 !');
			}
		}
	}
	$result = array();
	if (empty($api_ret)) {
		$result = ajax_get_result('9999', '請確認訂單編號是否正確 !');
	} else {
		$result = ajax_get_result('0000', '', $api_ret);
	}
	return $result;
}

/**
 * 檢查會員帳號是否存在
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_check_user_account_exists() {
    $category = get_val('category');
    $account = get_val('account');
    if (empty($category)) {
        $category = constants_user_center::USER_CATEGORY_TRIPITTA;
    }
    $tripitta_web_service = new tripitta_web_service();
    $ret = $tripitta_web_service->is_user_exists($category, $account);
    if ($ret["status"] == 0) {
        return ajax_get_result('9999', 'E-mail帳號錯誤');
    } else {
        return ajax_get_result('0000', $ret["msg"]);
    }
}

/**
 * 會員登入
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_do_login() {
    $tripitta_web_service = new tripitta_web_service();
    $category = get_val('category');
    $account = get_val('account');
    $password = get_val('password');
    $auto_login = get_val('auto_login');
    $login_user_data = $tripitta_web_service->login($category, $account, $password, ($auto_login == 1) ? true : false);
    $result = array();
    if (empty($login_user_data)) {
        $result = ajax_get_result('9999', '登入失敗 - 請確認帳號密碼是否正確 !');
    } else {
        $result = ajax_get_result('0000', '', $login_user_data);
    }
    return $result;
}

/**
 * 會員登入 - facebook
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_do_login_facebook() {
	$token = get_val('token');
	$category = "fb";
// 	writeLog(json_encode("func:" . __FUNCTION__ . ",token:" . $token . ",type: " . $category, JSON_UNESCAPED_UNICODE));
	// 登入
	$login = ajax_get_social_oauth($category, $token, "login");
	if(!empty($login)){
// 	writeLog(json_encode("func:" . __FUNCTION__ . ",social oauth:" . $login["msg"], JSON_UNESCAPED_UNICODE));
		return ajax_get_result($login["code"], $login["msg"]);
	}
}

/**
 * 會員登入 - ios facebook
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_do_login_ios_facebook() {
	$code = get_val('code');
	$category = "fb";
	$token = null;
// 	writeLog(json_encode("func:" . __FUNCTION__ . ",token:" . $token . ",type: " . $category, JSON_UNESCAPED_UNICODE));
	// 登入
	$login = ajax_get_social_oauth($category, $code, "login", $code);
	if(!empty($login)){
// 	writeLog(json_encode("func:" . __FUNCTION__ . ",social oauth:" . $login["msg"], JSON_UNESCAPED_UNICODE));
		return ajax_get_result($login["code"], $login["msg"]);
	}
}

/**
 * 會員登入 - google plus
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_do_login_gplus() {
	$token = get_val('token');
	$category = "gplus";
// 	writeLog(json_encode("func:" . __FUNCTION__ . ",token:" . $token . ",type: " . $category, JSON_UNESCAPED_UNICODE));
	// 登入
	$login = ajax_get_social_oauth($category, $token, "login");
	if(!empty($login)){
// 	writeLog(json_encode("func:" . __FUNCTION__ . ",social oauth:" . $login["msg"], JSON_UNESCAPED_UNICODE));
		return ajax_get_result($login["code"], $login["msg"]);
	}
}

/**
 * 會員登出
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_do_logout() {
    $tripitta_web_service = new tripitta_web_service();
    $tripitta_web_service->logout();
    return ajax_get_result('0000', '', null);
}

/**
 * 會員註冊
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_register() {
    global $mail_from;
    global $siteStr;
    $category = get_val('category');
    $account = get_val('account');
    $password = get_val('password');
    $nickname = get_val('nickname');
    if (empty($category)) {
        $category = constants_user_center::USER_CATEGORY_TRIPITTA;
    }
    $tripitta_web_service = new tripitta_web_service();

    // 檢查會員是否存在
    $ret = $tripitta_web_service->is_user_exists($category, $account);
    if ($ret["status"] == 0) {
        return ajax_get_result('9999', '連結會員中心失敗');
    } else {
        if ($ret["msg"] == 1) {
            return ajax_get_result('9999', '帳號已存在');
        }
    }

    // 新增會員資料
    $item = array();
    $item["category"] = $category;
    $item["userAccount"] = $account;
    $item["userPassword"] = md5($password);
    $item["nickname"] = $nickname;
    $item["userEmail"] = $account;
    $item["userAgreement"] = 1;
    $ret = $tripitta_web_service->add_user($item);
    if ($ret["status"] == "0") {
        ajax_get_result('9999', $ret["msg"]);
    }

    // 發送驗證信
    $tripitta_web_service->send_verify_email(constants_user_center::USER_CATEGORY_TRIPITTA, $account, $mail_from, $siteStr);
    return ajax_get_result('0000', '');
}

/**
 * 會員註冊 - Facebook
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_register_facebook() {
	$token = get_val('token');
	$category = "fb";
// 	writeLog(json_encode("func:" . __FUNCTION__ . ",token:" . $token . ",type: " . $category, JSON_UNESCAPED_UNICODE));
	// 註冊
	$register = ajax_get_social_oauth($category, $token, "register");
	if(!empty($register)){
// 	writeLog(json_encode("func:" . __FUNCTION__ . ",social oauth:" . $register["msg"], JSON_UNESCAPED_UNICODE));
		return ajax_get_result($register["code"], $register["msg"]);
	}
}

/**
 * 會員註冊 - Google plus
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_register_gplus() {
	$token = get_val('token');
	$category = "gplus";
// 	writeLog(json_encode("func:" . __FUNCTION__ . ",token:" . $token . ",type: " . $category, JSON_UNESCAPED_UNICODE));
	// 註冊
	$register = ajax_get_social_oauth($category, $token, "register");
	if(!empty($register)){
// 		writeLog(json_encode("func:" . __FUNCTION__ . ",social oauth:" . $register["msg"], JSON_UNESCAPED_UNICODE));
		return ajax_get_result($register["code"], $register["msg"]);
	}
}

/**
 * 社群帳號驗證 及 註冊/登入 會員
 * @param string $type 社群種類(facebook:facebook/fb, google+:plus)
 * @param string $token 社群帳號已登入 token
 * @param string $item (register:註冊, login:登入)
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_get_social_oauth($type, $token, $item="register", $code=null){
	$ret = array("code" => "9999", "msg" => "參數錯誤");
	writeLog(json_encode("func:" . __FUNCTION__ . ",token:" . $token . ",type: " . $type, JSON_UNESCAPED_UNICODE));
	if(!empty($type) && !empty($token)){
		$error = null;
		$category = null;
		$name_tag = "name";
		$first_name_tag = 'first_name';
		$last_name_tag = 'last_name';
		switch ($type){
			case 'facebook':
			case 'fb':
				global $full_serverName;
				if(!empty($code)){
					$authorization_code = "https://graph.facebook.com/oauth/access_token?client_id=".FACEBOOK_APP_ID."&redirect_uri=".urlencode($full_serverName)."&client_secret=".FACEBOOK_SECRET."&code={$code}";
					$authorization_code_oauth = open_url($authorization_code);
// 					printmsg($authorization_code);
// 					printmsg($authorization_code_oauth);
// 					printmsg("item: " . $item);
					$result_parts = [];
					parse_str($authorization_code_oauth["result"],$result_parts);
					$token = $result_parts["access_token"];
// 					printmsg($result_parts);
				}
				$url = "https://graph.facebook.com/me?fields=id,name,email,first_name,last_name,gender,locale&access_token={$token}";
// 				printmsg($url);
				$error = "Facebook";
				$category = constants_user_center::USER_CATEGORY_FACEBOOK;
				break;
			case 'gplus':
				$url = "https://www.googleapis.com/oauth2/v3/tokeninfo?id_token={$token}";
				$error = "Google+";
				$category = constants_user_center::USER_CATEGORY_GPLUS;
				break;
		}
		$ret = array("code" => "9999", "msg" => $error . "資料取得失敗");
		// 依據token取得facebook,gplus會員資訊,取得後新增會員
		$oauth = open_url($url);
		writeLog(json_encode($oauth, JSON_UNESCAPED_UNICODE));
		if (!empty($oauth)) {
			$ret = array("code" => "9999", "msg" => $error . "資料驗證失敗");
			if($oauth["code"] == 200){
				$data = json_decode($oauth["result"], true);
				if (is_array($data)) {
					$tripitta_web_service = new tripitta_web_service();
					$tripitta_service = new tripitta_service();
					$account = $data["email"];
					// 檢查會員是否存在
// 					printmsg("category: " . $category . " | account: " . $account);
					$ret = $tripitta_web_service->is_user_exists($category, $account);
// 					printmsg($ret);
					switch ($item){
						// 註冊帳號
						default:
						case "register":
							if ($ret["status"] == 0) {
								return ajax_get_result('9999', '連結會員中心失敗');
							} else {
								if ($ret["msg"] == 1) {
									return ajax_get_result('9999', '帳號已存在');
								}
							}
							// 								$name = isset($data[$name_tag]) ? $data[$name_tag] : '';
							$data["category"] = $category;
							// 								$data["userName"] = $name;
							$data["nick_name"] = $data[$name_tag];

							// 新增會員資料
							$save_data = array();
							$save_data["category"] = $category;
							$save_data["userAccount"] = $account;
// 							$save_data["userPassword"] = md5($password);
							$save_data["nickname"] =  $data[$name_tag];
							if (isset($data[$first_name_tag])) $save_data["first_name"] = $data[$first_name_tag];
							if (isset($data[$last_name_tag])) $save_data["last_name"] = $data[$last_name_tag];
							$save_data["userEmail"] = $account;
							$save_data["userAgreement"] = 1;
							$ret = $tripitta_web_service->add_user($save_data);
							if ($ret["status"] == "0") {
								ajax_get_result('9999', $ret["msg"]);
							}

							// user資訊
							$user_data = $tripitta_web_service->get_user_data_by_category_and_account($category, $account);

							// 判斷是否有符合的滿千送百資格
							$marking_campaign_dao = Dao_loader::__get_marking_campaign_dao();
							$marking_campaign_list = $marking_campaign_dao -> find_by_type(1);

							$reg_date = date("Y-m-d");
							foreach ($marking_campaign_list as $mc) {
								if($mc["mc_begin_date"] <= $reg_date && $mc["mc_end_date"] >= $reg_date) {
									// 檢查是否已歸戶(不應發生)
									$must_possessed_row = $tripitta_service -> find_must_possessed_by_user_and_marking_campain_id($user_data["msg"]["serialId"], $mc["mc_id"]);

									// 歸戶
									if(empty($must_possessed_row)) {
										$item = array();
										$item["mcmp_id"] = 0;
										$item["mcmp_user_id"] = $user_data["msg"]["serialId"];
										$item["mcmp_marking_campaign_id"] = $mc["mc_id"];
										$item["mcmp_use_times"] = $mc["mc_use_times"];
										$item["mcmp_status"] = 0;
										$tripitta_service -> save_or_update_marking_campaign_must_possessed($item);
									}
								}
							}

							$ret = array("code" => "0000", "msg" => '');
							break;
						// 登入帳號
						case "login":
							$category = $category;
// 							$password = get_val('password');
							$auto_login = get_val('auto_login');
							$password = null;
							$login_user_data = $tripitta_web_service->login($category, $account, $password, ($auto_login == 1) ? true : false);
							if (empty($login_user_data)) {
								if ($ret["status"] == 0) {
									return ajax_get_result('9999', '連結會員中心失敗');
								} else {
									if ($ret["msg"] == 1) {
										return ajax_get_result('9999', '帳號已存在');
									}
								}
								// 								$name = isset($data[$name_tag]) ? $data[$name_tag] : '';
								$data["category"] = $category;
								// 								$data["userName"] = $name;
								$data["nick_name"] = $data[$name_tag];

								// 新增會員資料
								$save_data = array();
								$save_data["category"] = $category;
								$save_data["userAccount"] = $account;
								// 							$save_data["userPassword"] = md5($password);
								$save_data["nickname"] =  $data[$name_tag];
								if (isset($data[$first_name_tag])) $save_data["first_name"] = $data[$first_name_tag];
								if (isset($data[$last_name_tag])) $save_data["last_name"] = $data[$last_name_tag];
								$save_data["userEmail"] = $account;
								$save_data["userAgreement"] = 1;
								$ret = $tripitta_web_service->add_user($save_data);
								if ($ret["status"] == "0") {
									ajax_get_result('9999', $ret["msg"]);
								}
								$login_user_again_data = $tripitta_web_service->login($category, $account, $password, ($auto_login == 1) ? true : false);

								// 判斷是否有符合的滿千送百資格
								$marking_campaign_dao = Dao_loader::__get_marking_campaign_dao();
								$marking_campaign_list = $marking_campaign_dao -> find_by_type(1);								
								
								$reg_date = date("Y-m-d");
								foreach ($marking_campaign_list as $mc) {
									if($mc["mc_begin_date"] <= $reg_date && $mc["mc_end_date"] >= $reg_date) {
										// 檢查是否已歸戶(不應發生)
										$must_possessed_row = $tripitta_service -> find_must_possessed_by_user_and_marking_campain_id($login_user_again_data["msg"]["serialId"], $mc["mc_id"]);

										// 歸戶
										if(empty($must_possessed_row)) {
											$item = array();
											$item["mcmp_id"] = 0;
											$item["mcmp_user_id"] = $login_user_again_data["serialId"];
											$item["mcmp_marking_campaign_id"] = $mc["mc_id"];
											$item["mcmp_use_times"] = $mc["mc_use_times"];
											$item["mcmp_status"] = 0;
											$tripitta_service -> save_or_update_marking_campaign_must_possessed($item);
										}
									}
								}

								$ret = array("code" => "0000", "msg" => '');
// 								$ret = array("code" => "9999", "msg" => '登入失敗 - 請確認帳號是否已經完成註冊 !');
							} else {
								$ret = array("code" => "0000", "msg" => '', $login_user_data);
							}
							break;
					}
				}
			}
		}
	}
	return $ret;
}

/**
 * 取得商城首頁 - 熱門地區 - 旅宿資訊(6筆)
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_find_homestay_for_booking_hom_by_area_ids() {
    $area_id = get_val('area_ids');
    if (empty($area_id)) {
        return ajax_get_result('9999', '參數錯誤');
    }
    $tripitta_web_service = new tripitta_web_service();
    $t_ids = preg_split('/,/', $area_id);
    $area_ids = [];
    foreach($t_ids as $t_id) {
        $area_ids[] = intval($t_id);
    }
    $homestay_list = $tripitta_web_service->find_valid_homestay_for_booking_home_by_area_id($area_ids);
    return ajax_get_result('0000', null, $homestay_list);
}

/**
 * 取得商城首頁 - 推薦旅宿類別 - 旅宿資訊(5筆)
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_find_homestay_for_booking_hom_by_recommend_type() {
    $content_type = get_val('content_type');
    $content_code = get_val('content_code');
    if (empty($content_type) || empty($content_code)) {
        return ajax_get_result('9999', '參數錯誤');
    }
    $tripitta_web_service = new tripitta_web_service();
    $homestay_list = $tripitta_web_service->find_recommend_type_homestay_by_type_and_type_id($content_type, $content_code);
    return ajax_get_result('0000', null, $homestay_list);
}

/**
 * 取得交通大首頁 - 推薦包車類別 - 包車資訊(5筆)
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_find_fleet_route_rand_search_list_car() {
	$limit = 5;
	$cr_type = 1;
	$tripitta_service = new tripitta_service();
	$fleet_route_list = $tripitta_service->find_fleet_route_rand_search_list($cr_type, $limit);
	return ajax_get_result('0000', null, $fleet_route_list);
}

/**
 * 取得交通大首頁 - 推薦觀光巴士類別 - 包車資訊(5筆)
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_find_fleet_route_rand_search_list_bus() {
	$limit = 3;
	$cr_type = 3;
	$tripitta_service = new tripitta_service();
	$fleet_route_list = $tripitta_service->find_fleet_route_rand_search_list($cr_type, $limit);
	return ajax_get_result('0000', null, $fleet_route_list);
}

/**
 * 取得高鐵票價
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_find_ticket_price(){
	$ret = array("code" => "9999", "msg" => "參數錯誤", "data" => "");
	$t_id = get_val('t_id');
	$from = get_val('start_area');
	$to = get_val('end_area');
	if(!empty($t_id) && !empty($from) && !empty($to)){
		$ret = array("code" => "9999", "msg" => "查無此路線價格!", "data" => "");
    	$tripitta_service = new tripitta_service();
    	$ticket_price_list = $tripitta_service->find_ticket_type_price_by_ticket_id($t_id, $from, $to);
    	if(!empty($ticket_price_list)){
    		$ticket_price_row = array();
    		foreach ($ticket_price_list as $tpl){
    			$tt_type = 0;
    			switch ($tpl["tt_name"]){
    				default:
    				case "成人":
    					$tt_type = 1;
    					break;
    				case "小孩":
    					$tt_type = 2;
    					break;
    			}
    			if($tpl["tt_name"]=="成人" || $tpl["tt_name"]=="小孩"){
    				$ticket_price_row[] = array("tt_type"=>$tt_type, "ttp_id"=> $tpl["ttp_id"], "sell_price"=>$tpl["ttp_sell_price"]);
    			}
    		}
    		$ret = array("code" => "0000", "msg" => "", "data" => $ticket_price_row);
    	}
	}
	return ajax_get_result($ret["code"], $ret["msg"], $ret["data"]);
}

function ajax_find_valid_trip_plan_for_booking_home() {
    $tripitta_web_service = new tripitta_web_service();
    $travel_plan_list = $tripitta_web_service->find_valid_trip_plan_for_booking_home(null);
    return ajax_get_result('0000', null, $travel_plan_list);
}

function ajax_find_valid_travel_plan_for_trip_home() {
    $area_id = get_val('area_id');
    $month = get_val('q_month');
    $tags = get_val('tags');
    $days = get_val('days');
    $keyword = get_val('keyword');
    $pageno = get_val('pageno');
    $page_size = get_val('page_size');
    $order = get_val('order');

    $cond = [];
    if(!empty($area_id)) { $cond["area_id"] = $area_id; }
    if(!empty($month)) { $cond["month"] = $month; }
    if(!empty($order)) { $cond["order"] = $order; }
    if(!empty($tags)) {
        // 避免injection先將值先轉int
        $t_list = preg_split('/,/', $tags);
        $r = [];
        foreach($t_list as $t) {
            $r[] = intval($t);
        }
        $cond["tags"] = $r;
    }
    if(empty($pageno)) {
        $pageno = 1;
    }
    if(empty($page_size)) {
        $page_size = 9;
    }

    if(!empty($days)) { $cond["days"] = $days; }
    if(!empty($keyword)) { $cond["keyword"] = $keyword; }
    if(!empty($pageno)) { $cond["pageno"] = $pageno; }
    if(!empty($page_size)) { $cond["page_size"] = $page_size; }

    $tripitta_web_service = new tripitta_web_service();
    $ret = $tripitta_web_service->find_valid_travel_plan_for_trip_home($cond);
    return ajax_get_result('0000', null, $ret);
}

function ajax_get_result($return_code, $msg, $data = null) {
    return array('code' => $return_code, 'msg' => $msg, 'data' => $data);
}

function ajax_remove_user_favorite() {
    $user_id = get_val('user_id'); // 流水號
    $items = get_val('items');
    //$user_id = 1041618;
    //$items = array(array("ref_id"=>"4156", "type_id"=>"7"));
    if (empty($items) || empty($user_id)) {
        return ajax_get_result('9999', '參數錯誤');
    }
    $client_service = new tripitta_api_client_service('tripitta', '((tripitta))');

    $remove_items = [];
    foreach ($items as $item_row) {
        $remove_items[] = array('type_id' => $item_row["type_id"], 'ref_id' => $item_row["ref_id"]);
    }
    $data = array('user_id' => $user_id, 'items' => $remove_items);
    $api_ret = $client_service->remove_user_favorite($data);
    writeLog(__FUNCTION__ . ", " . array2string($data, false));
    writeLog(__FUNCTION__ . ", " . array2string($api_ret, false));
    if($api_ret["code"] != "0000") {
        $ret = $api_ret;
    }else {
        $ret = $api_ret["data"];
    }
    return $ret;
}

function ajax_send_member_verify_email() {
    writeLog(__FUNCTION__);
    global $mail_from;
    global $siteStr;
    $user_id = get_val('user_id');
    $tripitta_web_service = new tripitta_web_service();

    // 檢查會員是否存在
    $ret = $tripitta_web_service->get_user_data_by_id(null, $user_id);
    if ($ret["status"] != 1) {
        return ajax_get_result('9999', $ret["msg"]);
    }
    $user_data = $ret["msg"];

    // 發送驗證信
    $tripitta_web_service->send_verify_email($user_data["category"], $user_data["account"], $mail_from, $siteStr);
    return ajax_get_result('0000', '');
}

function ajax_reset_user_password() {
    $t = get_val('token');
    $password = get_val('password');
    $uid = substr($t, 0, 32);
    $token = substr($t, -32);
    writeLog('uid:' . $uid);
    writeLog('token:' . $token);
    writeLog('t:' . $t);

    $tripitta_web_service = new tripitta_web_service();
    $ret = $tripitta_web_service->get_user_data_by_id($uid, null);
    if ($ret["status"] != 1) {
        return ajax_get_result('9999', $ret["msg"]);
    }
    $user_data = $ret["msg"];
    writeLog(array2string($user_data));
    $ori_pssword = $user_data["password"];

    $user_data = $ret["msg"];
    $user_data = array();
    $user_data["userId"] = $uid;
    $user_data["userPassword"] = md5($password);
    $user_data["oriPassword"] = $ori_pssword;
    $ret = $tripitta_web_service->update_user_data($user_data);
    if ($ret["status"] == 0) {
        return ajax_get_result('9999', $ret["msg"]);
    }
    return ajax_get_result('0000', '');
}

/**
 * 重置密碼
 * 1. 寄發重置碼
 * 2. 連回重置頁面重置密碼
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_send_reset_password() {
    $account = get_val('account');
    $tripitta_web_service = new tripitta_web_service();
    $ret = $tripitta_web_service->get_reset_password_code(constants_user_center::USER_CATEGORY_TRIPITTA, $account);
    if ($ret["status"] == 0) {
        return ajax_get_result('9999', $ret["msg"]);
    }
    $token_data = $ret["msg"];

    $ret = $tripitta_web_service->get_user_data_by_category_and_account(constants_user_center::USER_CATEGORY_TRIPITTA, $account);
    writeLog(__FUNCTION__ . ', ret:' . json_encode($ret, JSON_UNESCAPED_UNICODE));
    if ($ret["status"] == 0) {
        return ajax_get_result('9999', $ret["msg"]);
    }
    $user_data = $ret["msg"];
    $email = $user_data["email"];

    // expireTime: "2015-10-31 18:18:40"
    // id: "0AA806D7C158A8C067D3792C6A9615C8"
    // serialId: "1041618"
    // token: "D868DA52F91CFC3C786C98A3687536DA"
    // 組合token當重置密碼頁之參數 base64encode(id + token)
    $token = base64_encode($token_data["id"] . $token_data["token"]);

    $ret = EzdingUserUtil::get_ezding_user_util()->send_tripitta_reset_password_mail($email, $token);

    return ajax_get_result('0000', '', $ret);
}

function ajax_update_user_data() {
    $userId = get_val('userId');
    $email = get_val('email');
    $real_name = get_val('real_name');
    $gender = get_val('gender');
    $nickname = get_val('nickname');
    $birthday = get_val('birthday');
    $married = get_val('married');
    $education = get_val('education');
    $family = get_val('family');
    $living_tel_country_id = get_val('living_tel_country_id');
    $living_country_id = get_val('living_country_id');
    $mobile = get_val('mobile');
    $living_area_id = get_val('living_area_id');
    $living_address = get_val('living_address');
    $epaper_homestay = get_num('epaper_homestay');

    if (empty($userId)) {
        return ajax_get_result('9999', '您尚未登入');
    }

    $user_data = array();
    if (!empty($email)) {
        $user_data["userEmail"] = $email;
    }
    if (!empty($real_name)) {
        $user_data["userName"] = $real_name;
    }
    if (!empty($nickname)) {
        $user_data["nickname"] = $nickname;
    }
    if (!empty($gender)) {
        $user_data["userGender"] = $gender;
    }
    if (!empty($birthday)) {
        $user_data["userBirthday"] = $birthday;
    }
    if (!empty($married)) {
        $user_data["married"] = $married;
    }
    if (!empty($education)) {
        $user_data["education"] = $education;
    }
    if (!empty($family)) {
        $user_data["family"] = $family;
    }
    if (!empty($living_tel_country_id)) $user_data["living_tel_country_id"] = $living_tel_country_id;
    if (!empty($living_country_id)) $user_data["living_country_id"] = $living_country_id;
    if (!empty($mobile)) $user_data["userMobile"] = $mobile;
    if (!empty($living_area_id)) $user_data["living_area_id"] = $living_area_id;
    if (!empty($living_address)) $user_data["living_address"] = $living_address;
    if (empty($epaper_homestay)) $user_data["epaperHomeStay"] = 0;
    else $user_data["epaperHomeStay"] = $epaper_homestay;

    if (!empty($user_data)) {
        $user_data["userId"] = $userId;
        $ezding_user_service = EzdingUserUtil::get_ezding_user_util();
        $ret = $ezding_user_service->updateUser($user_data);
		// 更新完update session
        $user_data = $ret['msg'];
        $_SESSION[USER_DATA] = $user_data;
        writeLog(__FUNCTION__ . ' line:' . __LINE__ . ' user_data:' . array2string($user_data, false));
        writeLog(__FUNCTION__ . ' line:' . __LINE__ . ' ret:' . array2string($ret, false));
        return ajax_get_result('0000', "更新成功");
    } else {
        return ajax_get_result('9999', "沒有資料更新");
    }
}

function ajax_sync_user_avatar() {
    global $avatar_path;

    $sync_avatar_url = 'http://api.ezding.com.tw/travel/ftc/common/sync_file.php';
    if (!is_production()) $sync_avatar_url = 'http://alpha.api.ezding.com.tw/travel/ftc/common/sync_file.php';

    $post_data = array();
    $post_data['u'] = $_REQUEST['avatar_image'];
    $post_data['p'] = $avatar_path;
    $post_data['f'] = basename($_REQUEST['avatar_image']);
    writeLog(__FUNCTION__ . ' ' . __LINE__ . ' ' . json_encode($post_data, JSON_UNESCAPED_UNICODE));
    $ret = open_url($sync_avatar_url, NULL, NULL, 30, $post_data);
    writeLog(__FUNCTION__ . ' ' . __LINE__ . ' ' . json_encode($ret, JSON_UNESCAPED_UNICODE));
    $result = json_decode($ret['result'], true);

    // avatar圖檔同步出錯則通知admin
    if ('0000' != $result['code']) {
        $subject = 'ajax_sync_user_avatar圖檔同步錯誤';
        $body = 'ajax_sync_user_avatar post_data:' . json_encode($post_data, JSON_UNESCAPED_UNICODE);
        $body .= 'result:' . json_encode($result, JSON_UNESCAPED_UNICODE);
        sendSystemMail(Constants::$TRAVEL_ADMIN_EMAILS, $subject, $body);
    }

    return ajax_get_result('0000', '', $result);
}

function ajax_update_user_avatar() {
    global $avatar_path;

    // 若是本機端則不處理 (本機端沒有裝 imagick)
    if (is_dev()) return ajax_get_result('0000', '更新成功');

    //上傳檔案處理
    if (isset($_FILES)) {
//         writeLog('ajax.php / ajax_update_user_avatar / 收到的$_FILES:'. json_encode($_FILES) );

        $type = preg_split('/\./', $_FILES['avatar_image']['name']);
        $save_path = $avatar_path . '/' . $_REQUEST['serial_id'] . '.' . $type[1];
        $tmp_path = $_FILES['avatar_image']['tmp_name'];

        $imagick_service = new imagick_service();
        $ret = $imagick_service->scaleImage($tmp_path, $save_path, 200);
        writeLog(__FUNCTION__ . ' line:' . __LINE__ . ' ' . json_encode($ret, JSON_UNESCAPED_UNICODE));

        if ('ok' == $ret['status']) {
            $user_data = array();
            $user_data["userId"] = $_REQUEST['user_id'];
            $user_data["avatar"] = $save_path;
            $ezding_user_service = EzdingUserUtil::get_ezding_user_util();
            $ret = $ezding_user_service->updateUser($user_data);
			// 更新完update session
			$user_data = $ret['msg'];
			$_SESSION[USER_DATA] = $user_data;			
            writeLog(__FUNCTION__ . ' line:' . __LINE__ . ' user_data:' . json_encode($user_data, JSON_UNESCAPED_UNICODE));
            writeLog(__FUNCTION__ . ' line:' . __LINE__ . ' ret:' . json_encode($ret, JSON_UNESCAPED_UNICODE));

            return ajax_get_result('0000', '更新成功', array('avatar_path' => $save_path));
        } else {
            return ajax_get_result('9999', $ret['msg']);
        }
    }
}

function ajax_update_user_password() {
    $user_id = get_val('user_id');
    $password = get_val('password');
    $orig_password = get_val('orig_password');
    if (empty($user_id) || empty($password) || strlen($user_id) != 32) {
        return ajax_get_result('9999', '參數錯誤');
    }
    $tripitta_web_service = new tripitta_web_service();
    $user_data = array();
    $user_data["userId"] = $user_id;
    $user_data["userPassword"] = md5($password);
    $user_data["oriPassword"] = md5($orig_password);
    writeLog(array2string($user_data, false));
    $ret = $tripitta_web_service->update_user_data($user_data);
    if ($ret["status"] == 0) {
        return ajax_get_result('9999', $ret["msg"]);
    }
    return ajax_get_result('0000', '');
}

/**
 * 產生QA列表
 */
function ajax_qa() {
    $qa_answer_dao = Dao_loader::__get_qa_answer_dao();
    $qa_question_dao = Dao_loader::__get_qa_question_dao();
    $ezding_hf_photo = Dao_loader::__get_photo_dao();

    $qa_photo_path = '/photos/travel/qa/';
    $url = 'http://img.ezding.com.tw';
    $qt_id = get_val('qt_id');
    $page = get_val('page');
    $pageSize = get_val('pageSize');

    $cond = array();
    $cond["limit"] = $pageSize;
    $cond["offset"] = ($page - 1) * $pageSize;
    $question_row = $qa_question_dao->find_qa_question_by_cond($qt_id, 'tripitta', 'tw', $cond);
    $html = '';
    foreach ($question_row as $value) {
        $qaAnswerList = $qa_answer_dao->findQaAnswerListByCondition($value["qq_id"]);
        $html .= '<section>';
        $html .= '	<h1>';
        $html .= '		<span class="q"></span>';
        $html .= '		<span class="titleQ">' . $value['qq_content'] . '</span>';
        $html .= '	</h1>';
        $html .= '	<div>';
        $html .= '		<span class="a"></span>';
        $html .= '		<span class="subContent">';

        foreach ($qaAnswerList as $data) {
            $html .= $data['qa_content'];
            $photoRow = $ezding_hf_photo->loadHfPhotoByCategoryAndReferenceId($data["qa_id"], 'home_stay_qa');

            if (!empty($photoRow)) {
            	$photoId = $photoRow['p_id'];
            	$photoType = $photoRow['p_content_type'];
                $html .= '<img src="' . $url . $qa_photo_path . $data["qa_id"] . '/' . $photoId . '.' . $photoType . '" style=" margin-top: -20px;"/>';
            }
            if (count($qaAnswerList) > 1) $html .= '<br />';
        }
        $html .= '	  </span>';
        $html .= '	</div>';
        $html .= '</section>';
    }

    return ajax_get_result('0000', '', $html);
}

/**
 * 檢查認證碼
 * @return multitype:unknown string
 */
function ajax_check_aptcha_code() {
    $data = $_REQUEST['data'];
    $authType = $data['type'];
    $captchaCode = $data['captchaCode'];

    $captchaType = null;
    if ('payment' == $authType) $captchaType = CAPTCHA_PAYMENT;
    if ('user' == $authType) $captchaType = CAPTCHA_USER;

    if ($captchaCode == $_SESSION[$captchaType]) {
        $result = ajax_get_result('0000', '');
    } else {
        $result = ajax_get_result('9999', '');
    }

    return $result;
}

/**
 * 重發認證信
 * @return array('code'=> 0000:成功 9999:失敗, 'msg', 'data')
 */
function ajax_resend_verify_email() {
    global $mail_from;
    global $siteStr;
    $t = get_val('token');
    $email = get_val('email'); // 重發時所填的email
    $uid = substr($t, 0, 32);

    $tripitta_web_service = new tripitta_web_service();
    $ret = $tripitta_web_service->get_user_data_by_id($uid, null);
    if ($ret["status"] != 1) {
        return ajax_get_result('9999', $ret["msg"]);
    }
    $user_data = $ret["msg"];
    writeLog(array2string($user_data));
    $ori_email = $user_data["email"]; // 註冊時所填的email


    if ($email != $ori_email) {
        return ajax_get_result('9999', '查無此帳號，請填寫註冊時填寫之email!!');
    }

    // 發送驗證信
    $tripitta_web_service->send_verify_email(constants_user_center::USER_CATEGORY_TRIPITTA, $email, $mail_from, $siteStr);
    return ajax_get_result('0000', '');
}
/**
 * 設定頁面顯示Currency
 */
function ajax_set_display_currency() {
    $currency_id = get_val('currency_id');
    if(empty($currency_id)) {
        $currency_id = 1;
    }
    $tripitta_web_service = new tripitta_web_service();
    if(!$tripitta_web_service->set_display_currency($currency_id)) {
        return ajax_get_result('9999', '顯示幣別設定失敗');
    }
    return ajax_get_result('0000', '');
}

function ajax_find_user_by_email(){
	$account = get_val('email');
	$category = constants_user_center::USER_CATEGORY_TRIPITTA;
	$tripitta_web_service = new tripitta_web_service();
	// 檢查會員是否存在
	$ret = $tripitta_web_service->is_user_exists($category, $account);
	if ($ret["status"] == 0) {
		return ajax_get_result('9999', '連結會員中心失敗');
	} else {
		if ($ret["msg"] == 1) {
			return ajax_get_result('9999', '帳號已存在，請登入會員後再繼續進行購買!!');
		}
	}
}

/**
 * 釋放空房(購物車)
 */
function ajax_release_room(){
	$cart_id = get_val('cart_id');
	$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);
	// 釋放空房
	$data = array('shopping_cart_id'=>$cart_id);
	$ret = $tripitta_api_client_service -> release($data);
	if ($ret["code"] != 0000) {
		return ajax_get_result('9999', '連結購物車API失敗');
	} else {
		if ($ret["data"]["code"] != 0000) {
			return ajax_get_result('9999', $ret["data"]["msg"]);
		}
	}
}

/**
 * 確認coupon
 */
function ajax_check_coupon() {
	$cart_id = get_val('cart_id');
	$number = get_val('number');
	$hs_id = get_val('hs_id');
	$userData = !empty($_SESSION[USER_DATA]) ? $_SESSION[USER_DATA] : '';
	$total_amount = get_val('total_amount');
	$exchange_rate = get_val('exchange_rate');
	$payment_code = get_val('payment_code');
	$u_id = 0;
	if(!empty($userData)) $u_id = $userData['serialId'];
	$coupon_dao = Dao_loader::__get_coupon_dao();
	$payment_channel_dao = Dao_loader::__get_payment_channel_dao();
	$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);
	$travel_coupon_service = new travel_coupon_service();
	$paymentChannel = $payment_channel_dao->getPaymentChannelByCode($payment_code);
	$coupon_row = $coupon_dao -> getCoupon($number);
	if (empty($coupon_row)) {
		return ajax_get_result('9999', "您輸入的優惠卷代碼錯誤!!");
	}
	$discount_value = 0;
	$discount = 0;
	$coupon_discount = 0;
	$twd_discount = 0;
	if ($coupon_row['cb_type'] == 1) {
		$discount_value = $coupon_row['cb_percent'];
		$amount = $total_amount * $exchange_rate; // 換算回台幣
		$discount = floor((intval($total_amount) * ($discount_value) / 100)/$exchange_rate );
		$coupon_discount = floor((intval($amount) * ($discount_value) / 100));
		$twd_discount = floor((intval($total_amount) * ($discount_value) / 100));
	} else if ($coupon_row['cb_type'] == 2) {
		$discount_value = $coupon_row['cb_price'];
		$discount = floor($discount_value/$exchange_rate);
		$coupon_discount = $discount_value;
		$twd_discount = floor($discount_value);
	}
	// 驗證 travel coupon
	$rets = $travel_coupon_service -> verify_coupon_by_travel($number, $paymentChannel['pc_id'], $u_id, ($total_amount * $exchange_rate), $exchange_rate, $hs_id, null, 3);
	if($rets['code'] == '0000'){
		$coupon = array();
		$coupon['store_id'] = $hs_id;
		// 商品類別 1:旅宿 2:租車 3:伴手禮 100:package
		$coupon['product_type'] = Constants::ODC_PRODUCT_TYPE_COUPON;
		$coupon['coupon_number'] = $number;
		$coupon['coupon_batch_id'] = $coupon_row['c_batch_id'];
		$coupon['coupon_batch_name'] = $coupon_row['cb_batch_name'];
		$coupon['coupon_name'] = $coupon_row['cb_coupon_name'];
		$coupon['coupon_distount_type'] = $coupon_row['cb_type'];
		$coupon['coupon_distount_value'] = $discount_value;
		$coupon['coupon_discount'] = $coupon_discount;
		$coupon['target'] = 1;
		$data = array('shopping_cart_id'=>$cart_id, 'cart_item'=>$coupon);
		$ret = $tripitta_api_client_service -> add_coupon_to_cart($data);
		$ret['data']['discount'] = $discount;
		$ret['data']['twd_discount'] = $twd_discount;
		$ret['data']['number'] = $number;
		if ($ret["code"] != 0000) {
			return ajax_get_result('9999', $ret["msg"]);
		} else {
			return ajax_get_result('0000', $ret["data"]);
		}
	} else {
		return ajax_get_result('9999', $rets['msg']);
	}
}

/**
 * 計算設定景點距離
 */
function ajax_my_recommend_content_distance() {
	$category = get_val('category');
	$id = get_val('id');
	$latitude = get_val('latitude');
	$longitude = get_val('longitude');
	$tripitta_service = new tripitta_service();
	$type = 8;
	$distance_spot = $tripitta_service->find_my_recommend_content_distance_by_type($category, $id, $type, $latitude, $longitude);
	$type = 7;
	$distance_food = $tripitta_service->find_my_recommend_content_distance_by_type($category, $id, $type, $latitude, $longitude);
	if (!empty($distance_spot) && !empty($distance_food)) {
		$ret = array();
		$distance_merge = array_merge($distance_spot, $distance_food);
		$ret["code"] = "0000";
		$ret["data"] = $distance_merge;
		return ajax_get_result('0000', '', $ret["data"]);
	} else {
		$ret["msg"] = "error";
		return ajax_get_result('9999', $ret["msg"]);
	}
}

/**
 * 計算附近景點距離
 */
function ajax_my_recommend_nearby_content_distance() {
	$latitude = get_val('latitude');
	$longitude = get_val('longitude');
	$gc_latitude = get_val('gc_latitude');
	$gc_longitude = get_val('gc_longitude');
	$tripitta_service = new tripitta_service();
	$type = 8;
	$distance_spot = $tripitta_service->find_my_recommend_nearby_content_distance_by_type($type, $gc_latitude, $gc_longitude, $latitude, $longitude);
	$type = 7;
	$distance_food = $tripitta_service->find_my_recommend_nearby_content_distance_by_type($type, $gc_latitude, $gc_longitude, $latitude, $longitude);
	if (!empty($distance_spot) && !empty($distance_food)) {
		$ret = array();
		$distance_merge = array_merge($distance_spot, $distance_food);
		$ret["code"] = "0000";
		$ret["data"] = $distance_merge;
		return ajax_get_result('0000', '', $ret["data"]);
	} else {
		$ret["msg"] = "error";
		return ajax_get_result('9999', $ret["msg"]);
	}
}

/**
 * 
 */
function ajax_proof_email(){
	$to_id = get_val('to_id');
	$tripitta_service = new tripitta_service();
	$job_order_notify_dao = Dao_loader::__get_travel_job_order_notify_dao();
	$proof_row = $tripitta_service->get_ticket_order_by_proof($to_id);
	if (!empty($proof_row)){
		$join_Id = $proof_row['jon_id'];
		$ret = array();
		$ret["jon_id"]=$join_Id;
		$ret["jon_status"]="1";
		$ret["jon_retry"]="0";
		$ret["jon_create_time"]=date('Y-m-d H:i:s');
		//update hf_job_order_notify jon_id , jon_status='1',jon_retry='0'
		$nodify_value = $job_order_notify_dao->updateJobOrderNotifyByItem($ret);
		return ajax_get_result('0000', '', $ret);
	}else {
		$ret["msg"] = "error";
		return ajax_get_result('9999', $ret["msg"]);
	}
}

/**
 *
 */
function ajax_ticket_order_refund_price(){
	$to_id = get_val('order_id');
	$cancel_ids = get_val('cancel_ids');
	$tol_ids = array();
	$tol_ids = explode(",", $cancel_ids);
	$tripitta_service = new tripitta_service();
	$ticket_order_refund_price_list = $tripitta_service->cal_ticket_order_refund_price($to_id, $tol_ids);
	if (!empty($ticket_order_refund_price_list)){
		$ret = array();
		$ret["data"] = $ticket_order_refund_price_list;
		return ajax_get_result('0000', '', $ret["data"]);
	} else {
		$ret["msg"] = "error";
		return ajax_get_result('9999', $ret["msg"]);
	}
}

function ajax_modify_passenger(){
	$copi_id = get_val('copi_id');
	$copi_name = get_val('copi_name');
	$copi_birthday = get_val('copi_birthday');
	$copi_country_id = get_val('copi_country_id');
	$copi_identity_number = get_val('copi_identity_number');
	$tripitta_service = new tripitta_service();
	$item = array(	 "copi_id" => $copi_id 
					,"copi_name" => $copi_name 
					,"copi_birthday" => $copi_birthday 
					,"copi_country_id" => $copi_country_id 
					,"copi_identity_number" => $copi_identity_number 
				);
	$update = $tripitta_service->save_or_update_car_order_passenger_insurance($item);
	if ($update > 0) {
		return ajax_get_result('0000', "", "");
	} else {
		$ret["msg"] = "error";
		return ajax_get_result('9999', $ret["msg"]);
	}
}
?>
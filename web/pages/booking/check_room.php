<?php
header("Content-Type: text/html; charset=utf-8");
require_once __DIR__ . '/../../config.php';
$tripitta_homestay_service = new tripitta_homestay_service();
$home_stay_promotion_service = new Home_stay_promotion_service();
$Home_stay_channel_project_service = new Home_stay_channel_project_service();
$tripitta_api_client_service = tripitta_api_client_service::__get_instance(tripitta_api_client_service::SITE_TRIPITTA_WEB_TW);
$tripitta_web_service = new tripitta_web_service();

$roomDao = Dao_loader::__get_room_dao();
$roomTypeDao = Dao_loader::__get_room_type_dao();
$room_price_Dao = Dao_loader::__get_room_type_price_dao();

$homeStayId = $_REQUEST['homeStayId'];
$area_code = $_REQUEST["area_code"];
$beginDate = $_REQUEST['beginDate'];
$endDate = $_REQUEST['endDate'];
$roomType = get_val('roomType');
$roomQuantity = $_REQUEST['roomQuantity'];
$selectRoom = $_REQUEST['selectRoom'];
// var_dump($selectRoom);
$currency_id = $tripitta_web_service->get_display_currency();
$currency_code = NULL;
$exchange_rate = 1;

// 取得匯率
if (1 == $currency_id) {
	$currency_code = 'NTD';
	$exchange_rate = 1;
	$exchange_rate_id = 0;
}
else {
	$exchange = $tripitta_homestay_service->get_exchange_by_currency_id($currency_id);
	$currency_code = $exchange['cr_code'];
	$exchange_rate = $exchange['erd_rate'];
	$exchange_rate_id = $exchange['erd_id'];
}

$ary = preg_split('/,/', $selectRoom);

// 重組獲得資訊
$temp_ary = array();
foreach ($ary as $k => $v) {
	if($k == 0) {
		array_push($temp_ary, $v);
	}else {
		$t1 = preg_split('/_/', $v);
		$is_same = 0;
		$delete_set = 0;
		foreach ($temp_ary as $k2 => $v2){
			$t2 = preg_split('/_/', $v2);
			// 有重複
			if($t1[0] == $t2[0] && $t1[2] == $t2[2] && $t1[3] == $t2[3]) {
				$is_same++;
				$delete_set = $k2;
				break;
			}
		}
		// 無重復
		if($is_same == 0) {
			array_push($temp_ary, $v);
		}else {
			$new_quantify = (int)$t1[1] + (int)$t2[1];
			$str = $t1[0].'_'.$new_quantify.'_'.$t1[2].'_'.$t2[3].'_'.$t2[4].'_'.$t2[5].'_'.$t2[6];
			array_push($temp_ary, $str);
			unset($temp_ary[$delete_set]);
		}
	}
}
$array = array();
foreach ($temp_ary as $ta) {
	array_push($array, $ta);
}
$new_selectRoom = implode(",",$array);

$room_type_ids = array();
$check_in_dates = array();
$room_qtys = array();
$roomer_qtys = array();
$sell_prices = array();
$orig_prices = array();
$cost_prices = array();
$promotion_types = array();
$promotion_config_ids = array();
$breakfasts = array();
foreach ($array as $a) {
	$t = preg_split('/_/', $a); // $t: rtId, booking room quantity, rtName
	if ($t[1] == 0) continue;

	/** 檢查是否還有空房 - start */
	$count = 0;
	$chkTime = 0;
	$roomList = $roomDao->findValidRoomListByHomeStayIdAndDate($homeStayId, $beginDate, $endDate, 0);
	$room_type_row = $roomTypeDao -> loadHfRoomType($t[0]);

	foreach ($roomList as $r) {
		if ($t[0] == $r['r_room_type_id']) {
			// 當日期改變時
			if ($chkTime < strtotime($r['r_date'])) {
				// 檢查保留的房間數是否與訂房數相等
				if ($chkTime > 0 && $count < intval($t[1], 10)) {
					$msg = '親愛的客戶，很抱歉，\n'
						. '您所選擇的房型 ' . $room_type_row['rt_name'] . ' 空房數量已不足 ' . $t[1] . ' 間\n'
						. '請重新選擇其他房型。\n'
						. '造成困擾深表抱歉!';

					alertmsg($msg, '/booking/'.$area_code.'/' . $homeStayId . '/?beginDate=' . $beginDate . '&endDate=' . $endDate . '&roomType=' . $roomType . '&roomQuantity=' . $roomQuantity);
				}

				$count = 0;
				$chkTime = strtotime($r['r_date']);
			}

			// r_room_status 狀態 1:空房, 2:保留, 3:滿房, 4:業者保留
			if ($r['r_room_status'] == 1) $count += 1;
		}
	}

	// 檢查保留的房間數是否與訂房數相等
	if ($count < intval($t[1], 10)) {
		$msg = '親愛的客戶，很抱歉，\n'
			. '您所選擇的房型 ' . $room_type_row['rt_name'] . ' 空房數量已不足 ' . $t[1] . ' 間\n'
			. '請重新選擇其他房型。\n'
			. '造成困擾深表抱歉!!';

		alertmsg($msg, '/booking/'.$area_code.'/' . $homeStayId . '/?beginDate=' . $beginDate . '&endDate=' . $endDate . '&roomType=' . $roomType . '&roomQuantity=' . $roomQuantity);
	}
	/** 檢查是否還有空房 - end */

	/** 檢查是否還有促銷活動資格 - start */
	if($t[2] == 1){
		$promotion_row = $home_stay_promotion_service -> find_promotion_config_by_promotion_ids_and_room_date_range($t[3], $beginDate, $endDate);
		foreach ($promotion_row as $p) {
			if($p['rt_id'] == $t[0]){
				if ($p['cnt'] < intval($t[1], 10)) {
					$msg = '親愛的客戶，很抱歉，\n'
					. '您所選擇的房型 ' . $room_type_row['rt_name'] . ' 空房數量已不足 ' . $t[1] . ' 間\n'
					. '請重新選擇其他房型。\n'
					. '造成困擾深表抱歉!!!';

					alertmsg($msg, '/booking/'.$area_code.'/' . $homeStayId . '/?beginDate=' . $beginDate . '&endDate=' . $endDate . '&roomType=' . $roomType . '&roomQuantity=' . $roomQuantity);
				}
			}
		}
	}
	/** 檢查是否還有促銷活動資格 - end */

	// 取得各房型定價
	$room_type_setting_price_list = $tripitta_homestay_service->get_home_stay_setting_price_by_home_stay_id($homeStayId);
	// 取得售價 & 成本價
	$roomTypePriceList = $room_price_Dao->findValidRoomTypePriceListByRoomTypeIdAndDate($t[0], $beginDate, $endDate, 1);
	// 每晚都顯示
	foreach ($roomTypePriceList as $rtp) {
		if($t[2] == 0){
			$sell_price = intval($rtp['rtp_price']);
			$promotion_type = 0;
		}else if($t[2] == 1){
			// 促銷模組
			$promotions = $home_stay_promotion_service -> load_promotion($t[3]);
			$promotion_type = $promotions['promo']['p_type'];
			$discount_type = $promotions['promo']['p_discount_type'];
			$discount_value = $promotions['promo']['p_discount_value'];
		    if (1 == $discount_type) {
	            $sell_price = floor(intval($rtp['rtp_price']) * (100 - $discount_value) / 100);
	        }
	        else if (2 == $discount_type) {
	            $sell_price = (intval($rtp['rtp_price']) - $discount_value);
	        }
		}else if($t[2] == 2){
			// 銀行優惠
			$promotion_type = 2;
			$project_price_list = $Home_stay_channel_project_service -> cal_sell_price($t[3], $homeStayId, $t[0], $rtp['rtp_date'], $rtp['rtp_price']);
			$pc_id = $project_price_list['pc_id'];
			$sell_price = $project_price_list['discont_price'];
		}
		// 一間一間顯示
		for($i = 0 ; $i < $t[1] ; $i++) {
			$promotion_config_id = 0;
			if($t[2] == 1){
				// 取得活動資格
				$promotion_config_row = $home_stay_promotion_service -> get_promotion_config_by_promotion_id_and_room_type_id_and_date($t[3], $t[0], $rtp['rtp_date']);
				$promotion_config_id = $promotion_config_row['pc_id'];
				// 保留活動資格
				// 走API
				// $home_stay_promotion_service -> keep_promotion($promotion_config_id);
			}else if($t[2] == 2){
				$promotion_config_id = $pc_id;
			}
			array_push($room_type_ids, $t[0]);
			array_push($room_qtys, 1);
			array_push($roomer_qtys, 0);
			array_push($check_in_dates, $rtp['rtp_date']);
			array_push($cost_prices, $rtp['rtp_cost_price']);
			array_push($orig_prices, $room_type_setting_price_list[$t[0]]);
			array_push($sell_prices, $sell_price);
			array_push($promotion_types, $promotion_type);
			array_push($promotion_config_ids, $promotion_config_id);
			array_push($breakfasts, $rtp['rtp_have_breakfast']);
		}
	}
}

// 保留房間
$item = array();
$item['store_id'] = $homeStayId;
$item['product_type'] = 1;
$item['homestay_id'] = $homeStayId;
$item['room_type_id'] = $room_type_ids;
$item['check_in_date'] = $check_in_dates;
$item['room_qty'] = $room_qtys;
$item['roomer_qty'] = $roomer_qtys;
$item['sell_price'] = $sell_prices;
$item['orig_price'] = $orig_prices;
$item['cost_price'] = $cost_prices;
$item['currency'] = $currency_code;
$item['exchange_rate'] = $exchange_rate;
$item['exchange_rate_id'] = $exchange_rate_id;
$item['promotion_type'] = $promotion_types;
$item['promotion_config_id'] = $promotion_config_ids;
$item['breakfast'] = $breakfasts;

$data = array('shopping_cart_id'=>'', 'cart_item'=>$item);
$ret = $tripitta_api_client_service -> reserve($data);
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset='utf-8'>
</head>
<body onload="document.form1.submit()">
	<form name="form1" method="post" action="booking_confirm.php">
	<input type="hidden" name="homeStayId" value="<?php echo $homeStayId?>"/>
	<input type="hidden" name="area_code" value="<?php echo $area_code ?>"/>
	<input type="hidden" name="roomType" value="<?php echo $roomType?>"/>
	<input type="hidden" name=roomQuantity value="<?php echo $roomQuantity?>"/>
	<input type="hidden" name="homeStayId" value="<?php echo $homeStayId?>"/>
	<input type="hidden" name="beginDate" value="<?php echo $beginDate?>"/>
	<input type="hidden" name="endDate" value="<?php echo $endDate?>"/>
	<input type="hidden" name="selectRoom" value="<?php echo $new_selectRoom ?>"/> <!-- 送重組後的資訊 -->
	<input type="hidden" name="pc_ids" value="<?php echo implode(",",$promotion_config_ids); ?>"/>
	<input type="hidden" name="shoppingCartId" value="<?php echo $ret['data']['data']['master_cart_id'] ?>"/>
	</form>
</body>
</html>
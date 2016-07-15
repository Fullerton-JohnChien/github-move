<?php
/**
 * 說明：交通 - 接送機 - 付款完成 E-Mail 頁
 * 作者：Bobby
 * 日期：2016年6月23日
 * 備註：
 */

require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$store_service = new store_service();
$partner_service = new partner_service();
$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();
$tripitta_car_service = new tripitta_car_service();

$order_id = get_val("order_id");
$order = $tripitta_service->get_car_order_by_order_id($order_id);
$co_user_id = $order['co_user_id'];
$user = $tripitta_service->get_user_by_user_id($co_user_id);
$co_buyer_name = $order['co_buyer_name'];
$co_transaction_id = $order['co_transaction_id'];
$gender = ($user['msg']['gender'] == "M") ? "先生" : "小姐";
$co_prod_name = $order['co_prod_name'];
$col_date = $order['col_date'];
$co_route_type = $order['co_route_type'];
$co_prod_id = $order['co_prod_id'];
$fleet_route_detail = $tripitta_service->get_fleet_route_detail($co_route_type, $co_prod_id);
$sml_name = $fleet_route_detail['sml_name'];
$c_name = $fleet_route_detail['c_name'];

$area_dao = Dao_loader::__get_area_dao();
$cr_boarding = $area_dao->loadHfArea($fleet_route_detail["cr_boarding"]);
$cr_boarding_name = $cr_boarding["a_name"];
$cr_get_off = $area_dao->loadHfArea($fleet_route_detail["cr_get_off"]);
$cr_get_off_name = $cr_get_off["a_name"];

$co_adult = $order['co_adult'];
$co_child = $order['co_child'];
$car_id = $fleet_route_detail['c_id'];
$luggage = $tripitta_service->find_car_luggage($car_id, $co_adult+$co_child);
$co_sell_price = number_format($order['co_sell_price']);
$co_bonus_discount = number_format($order['co_bonus_discount']);
$co_coupon_discount = $order['co_coupon_discount'];
$co_marking_campaign_discount = $order['co_marking_campaign_discount'];
$total = number_format($order['co_sell_price']);
$country_array = constants_user_center::$LIVING_COUNTRY_TEXT;
$co_buyer_country_id = $order['co_buyer_country_id'];
$buyer_country = "";
$buyer_c_tel_code = 0;
foreach ($country_array as $key => $value) {
	if ($key == $co_buyer_country_id) {
		$buyer_country = $value;
		$country_row = $tripitta_web_service->load_country($key);
		$buyer_c_tel_code = $country_row['c_tel_code'];
	}
}
$co_buyer_mobile = intval($order['co_buyer_mobile']);
$co_buyer_email = $order['co_buyer_email'];
$co_buyer_wechat_id = $order['co_buyer_wechat_id'];
$co_buyer_line_id = $order['co_buyer_line_id'];
$co_buyer_whats_app_id = $order['co_buyer_whats_app_id'];
$co_contact_name = $order['co_contact_name'];
$co_contact_country_id = $order['co_contact_country_id'];
$contact_country = "";
$contact_c_tel_code = 0;
foreach ($country_array as $key => $value) {
	if ($key == $co_contact_country_id) {
		$contact_country = $value;
		$country_row = $tripitta_web_service->load_country($key);
		$contact_c_tel_code = $country_row['c_tel_code'];
	}
}
$co_contact_mobile = intval($order['co_contact_mobile']);
$co_contact_email = $order['co_contact_email'];
$co_contact_wechat_id = $order['co_contact_wechat_id'];
$co_contact_line_id = $order['co_contact_line_id'];
$co_contact_whats_app_id = $order['co_contact_whats_app_id'];
$co_arrival_time = $order['co_arrival_time'];
$co_fly_no = $order['co_fly_no'];
$co_start_time = $order['co_start_time'];
$co_user_bording = $order['co_user_bording'];
$co_user_get_off = $order['co_user_get_off'];

$passenger = $tripitta_service->find_car_order_passenger($order['co_id']);
$facility = $tripitta_service->find_car_order_facility($order['co_id']);
$co_user_memo = $order['co_user_memo'];
$s_id = $order['co_store_id'];
$partner_row = $partner_service->find_partner_owner_by_store_id($s_id);
$pi_name = $partner_row[0]['pi_name'];
$pi_contact_email = $partner_row[0]['pi_contact_email'];
$pi_contact_phone = $partner_row[0]['pi_contact_phone'];
$pi_contact_address = $partner_row[0]['pi_contact_address'];

$fleet_rule = $tripitta_car_service->find_fleet_rule_by_store_id($s_id);
$fr_id = $fleet_rule['fr_id'];
$frd_category = "over.time.add_price"; //
$rule_detail = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, $frd_category);
$over_time = $rule_detail[0]['frd_category_value'];
$category = "midnight.add.price";
$midnight = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, $category);

$cancel_rule_array = $store_service->find_cancel_rule_by_store_id($s_id);
$fcrd_day = 0;
$cancel_rule = array();
$i = 1;
foreach ($cancel_rule_array as $value) {
	if ($value['fcrd_percent'] == 0) {
		$fcrd_day = $value['fcrd_day'];
	} else {
		$i++;
		$value['order'] = $i;
		$cancel_rule[] = $value;
	}
}

$this_day = date("m/d", strtotime($col_date));
$start_day = date("m/d", strtotime($col_date)-(86400*($fcrd_day+1)));
$ready_day = date("Y-m-d", strtotime($col_date)-(86400*($fcrd_day+1)));

$server_url = 'https://www.tripitta.com';
if(is_dev()) {
	$server_url= 'http://local.tw.tripitta.com';
} else if(is_alpha()) {
	$server_url = 'http://alpha.www.tripitta.com';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="zh-Hant">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	</head>
	<body style="font-family: Microsoft JhengHei;">
		<table width="640" align="center" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<table width="580" align="center" style="margin: 30px auto 0; color: black;">
						<tr>
							<td>
								<div>
									<img src="<?=$server_url?>/web/img/sec/transport/hsr/logo.png" width="142" height="50">
									<span style="font-size: 26px; font-weight: bold;vertical-align: super;margin-left: 10px;"><?php if ($co_route_type == 2) { ?>接<?php } else if ($co_route_type == 4) { ?>送<?php } ?>機訂單Email通知</span>
									<hr style="margin-top: 15px; color: lightgrey;">
								</div>
								<div style="font-size: 14px;">
									<p><span><?php echo $co_buyer_name; ?></span><span style="margin-left: 10px;"><?php echo $gender; ?></span> 您好：</p>
									<p>
										您已完成訂購，以下為您的訂購資料。
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">訂單編號</span>
										<span style="margin-left: 10px;color:#eb592c; display: inline-block;"><?php echo $co_transaction_id; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">行程</span>
										<span style="margin-left: 10px;"><?php echo $co_prod_name; ?></span>
									</p>
									<p>
										<?php if ($co_route_type == 2) { ?>
										<span style="width: 90px; display: inline-block;">接機日期</span>
										<?php } else if ($co_route_type == 4) { ?>
										<span style="width: 90px; display: inline-block;">送機日期</span>
										<?php } ?>
										<span style="margin-left: 10px;"><?php echo $col_date; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">車隊名稱</span>
										<span style="margin-left: 10px;"><?php echo $sml_name; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">車款</span>
										<span style="margin-left: 10px;">
											<?php echo $c_name; ?>
											<span style="margin-left: 10px; color: #666666;">( 或同級車款 )</span>
										</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">出發地</span>
										<span style="margin-left: 10px;"><?php echo $cr_boarding_name; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">目的地</span>
										<span style="margin-left: 10px;"><?php echo $cr_get_off_name; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">成人</span>
										<span style="margin-left: 10px;"><?php echo $co_adult; ?> 人</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">孩童</span>
										<span style="margin-left: 10px;"><?php echo $co_child; ?> 人<span style="margin-left: 10px; color: #666666;">(五歲以上)</span></span>
									</p>
									<?php
										if (!empty($luggage)) {
											$i = 0;
											foreach ($luggage as $l){
										?>
										<p>
											<span style="width: 90px; display: inline-block;"><?php if ($i == 0) { echo "行李"; } ?></span>
											<span style="margin-left: 10px;"><?php echo $l["ca_capacity_luggage"];?> 件<span style="margin-left: 10px; color: #666666;">(28吋以上)</span></span>
										</p>
										<?php
												$i++;
											}
										}
									?>
									<p>
										<span style="width: 90px; display: inline-block;">產品總額</span>
										<span style="margin-left: 10px;">NTD </span>
										<span style="margin-left: 10px;"><?php echo number_format($order['co_sell_price'] + ($co_coupon_discount + $co_marking_campaign_discount + $co_bonus_discount)); ?></span>
									</p>
									<!--
									<p>
										<span style="width: 90px; display: inline-block;">銀行紅利折點</span>
										<span style="margin-left: 10px;">NTD </span>
										<span style="margin-left: 10px;"><?php echo $co_bonus_discount; ?></span>
									</p>
									-->
									<p>
										<span style="width: 90px; display: inline-block;">優惠折扣</span>
										<span style="margin-left: 10px;">NTD </span>
										<span style="margin-left: 10px;">-<?php echo number_format($co_coupon_discount + $co_marking_campaign_discount); ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;color:#eb592c;">應付總額</span>
										<span style="margin-left: 10px;color:#eb592c;">NTD </span>
										<span style="margin-left: 10px;color:#eb592c;"><?php echo $total; ?></span>
										<span style="margin-left: 10px; color: #666666;">( 約 NTD <?php echo $total; ?> )</span>
									</p>
									<p>
										<span style="color:#eb592c;">
											此筆訂單金額不含附加服務，請於現場付款。本網站最後刷卡金額皆以台幣計算。
										</span>
									</p>
								</div>
								<div style="font-size: 14px;">
									<hr style="margin-top: 15px; color: lightgrey;">
									<p>
										<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">聯絡人資料</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">聯絡人</span>
										<span style="margin-left: 10px;"><?php echo $co_buyer_name; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">聯絡電話</span>
										<span style="margin-left: 10px;"><?php echo $buyer_country; ?></span>
										<span style="margin-left: 10px;"><?php echo $buyer_c_tel_code; ?></span>
										<span style="margin-left: 10px;"><?php echo $co_buyer_mobile; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">Email</span>
										<span style="margin-left: 10px;"><?php echo $co_buyer_email; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">聯絡資訊</span>
										<span style="margin-left: 10px; display: inline-block;">
											<span>Wechat：</span><span><?php echo $co_buyer_wechat_id; ?></span>
										</span>
										<br>
										<span style="width: 90px; display: inline-block;">
											&nbsp;
										</span>
										<span style="margin-left: 10px; display: inline-block;">
											<span>Line：</span><span><?php echo $co_buyer_line_id; ?></span>
										</span>
										<br>
										<span style="width: 90px; display: inline-block;">
											&nbsp;
										</span>
										<span style="margin-left: 10px; display: inline-block;">
											<span>Whatsapp：</span><span><?php echo $co_buyer_whats_app_id; ?></span>
										</span>
									</p>
								</div>
								<div style="margin-top: 30px; font-size: 14px;">
									<p style="margin: 0;">
										<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">乘車連絡人資料</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">聯絡人</span>
										<span style="margin-left: 10px;"><?php echo $co_contact_name; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">聯絡電話</span>
										<span style="margin-left: 10px;"><?php echo $contact_country; ?></span>
										<span style="margin-left: 10px;"><?php echo $contact_c_tel_code; ?></span>
										<span style="margin-left: 10px;"><?php echo $co_contact_mobile; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">Email</span>
										<span style="margin-left: 10px;"><?php echo $co_contact_email; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">聯絡資訊</span>
										<span style="margin-left: 10px; display: inline-block;">
											<span>Wechat：</span><span><?php echo $co_contact_wechat_id; ?></span>
										</span>
										<br>
										<span style="width: 90px; display: inline-block;">
											&nbsp;
										</span>
										<span style="margin-left: 10px; display: inline-block;">
											<span>Line：</span><span><?php echo $co_contact_line_id; ?></span>
										</span>
										<br>
										<span style="width: 90px; display: inline-block;">
											&nbsp;
										</span>
										<span style="margin-left: 10px; display: inline-block;">
											<span>Whatsapp：</span><span><?php echo $co_contact_whats_app_id; ?></span>
										</span>
									</p>
								</div>
								<div style="margin-top: 30px; font-size: 14px;">
									<p style="margin: 0;">
										<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">您的需求為</span>
									</p>
									<p>
										<?php if ($co_route_type == 2) { ?>
											<span style="width: 90px; display: inline-block;">抵達時間</span>
										<?php } else if ($co_route_type == 4) { ?>
											<span style="width: 90px; display: inline-block;">起飛時間</span>
										<?php } ?>
										<span style="margin-left: 10px;"><?php echo $co_arrival_time; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">航班編號</span>
										<span style="margin-left: 10px;"><?php echo $co_fly_no; ?></span>
									</p>
									<p>
										<?php if ($co_route_type == 2) { ?>
											<span style="width: 90px; display: inline-block;">接機時間</span>
										<?php } else if ($co_route_type == 4) { ?>
											<span style="width: 90px; display: inline-block;">送機時間</span>
										<?php } ?>
										<span style="margin-left: 10px;"><?php echo $co_start_time; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">出發地</span>
										<span style="margin-left: 10px;"><?php echo $co_user_bording; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">目的地</span>
										<span style="margin-left: 10px;"><?php echo $co_user_get_off; ?></span>
									</p>
								</div>
								<div style="margin-top: 30px; font-size: 14px;">
									<p style="margin: 0;">
										<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">乘車人保險用資料</span>
									</p>
									<?php
										foreach ($passenger as $value) {
											$copi_country_id = $value['copi_country_id'];
											$passenger_country = "";
											foreach ($country_array as $country_key => $country_value) {
												if ($country_key == $copi_country_id) {
													$passenger_country = $country_value;
												}
											}
									?>
									<div style="width: 47%; display: inline-block;">
										<p>
											<span style="width: 65px; display: inline-block; color: #666666; ">乘車人</span>
											<span style="margin-left: 10px;"><?php echo $value['copi_name']; ?></span>
										</p>
										<p>
											<span style="width: 65px; display: inline-block; color: #666666; ">出生日期</span>
											<span style="margin-left: 10px;"><?php echo $value['copi_birthday']; ?></span>
										</p>
										<p>
											<span style="width: 65px; display: inline-block; color: #666666; ">國籍</span>
											<span style="margin-left: 10px;"><?php echo $passenger_country; ?></span>
										</p>
										<p>
											<span style="width: 65px; display: inline-block; color: #666666; ">身分證號</span>
											<span style="margin-left: 10px;"><?php echo $value['copi_identity_number']; ?></span>
										</p>
									</div>
									<?php } ?>
								</div>
								<?php if (count($facility) > 0) { ?>
								<div style="margin-top: 30px; font-size: 14px;">
									<p style="margin: 0;">
										<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">附加服務</span>
									</p>
									<ul style="padding-left: 25px;">
										<?php
											foreach ($facility as $value) {
												switch ($value['cof_category']) {
													case "tour.guide.license" :
														$cof_category = "導遊執照司機";
														break;
													case "lang.support.en" :
														$cof_category = "外語司機(英語)";
														break;
													case "lang.support.jp" :
														$cof_category = "外語司機(日語)";
														break;
													case "lang.support.ct" :
														$cof_category = "外語司機(粵語)";
														break;
													case "lang.support.ko" :
														$cof_category = "外語司機(韓語)";
														break;
													case "baby.seat" :
														$cof_category = "嬰兒座椅(0-2歲)";
														break;
													case "child.seat" :
														$cof_category = "兒童座椅(2-12歲)";
														break;
													case "placard" :
														$cof_category = "舉牌接送";
														break;
													case "female.driver" :
														$cof_category = "女性司機";
														break;
													case "accessible.car" :
														$cof_category = "無障礙車種";
														break;
													case "wify" :
														$cof_category = "車上Wifi";
														break;
												}
										?>
										<li>
											<?php echo $cof_category; ?>
											<?php if (($value['cof_designation_price']) == 0) { ?>
											<span style="margin-left: 10px; color:#eb592c;">不加價</span>
											<?php } else { ?>
											<span style="margin-left: 10px; color:#eb592c;">現場付款</span>
											<span style="color:#eb592c;">NTD</span>
											<span style="color:#eb592c;"><?php echo number_format($value['cof_designation_price']); ?></span>
											<?php } ?>
										</li>
										<?php } ?>
									</ul>
									<p style="margin: 0;">
										<div style="margin: 0; color: #eb592c;">
											此筆訂單金額不含附加服務，請於現場付款
										</div>
										<div style="margin: 0;">
											(需求項目我們會盡量幫您安排，實際情況將於最晚出發前2天確認是否預定成功)。
										</div>
									</p>
								</div>
								<?php } ?>
								<!--
								<div style="margin-top: 30px; font-size: 14px;">
									<p style="margin: 0;">
										<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">亞洲萬里通卡號</span>
									</p>
									<p>
										<span style="width: 100%; display: inline-block;">1111-2222-3333-4444</span>
									</p>
								</div>
								-->
								<?php if (!empty($co_user_memo)) { ?>
								<div style="margin-top: 30px; font-size: 14px;">
									<p style="margin: 0;">
										<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">備註留言</span>
									</p>
									<p>
										<span style="width: 100%; display: inline-block;">
											<?php echo $co_user_memo; ?>
										</span>
									</p>
								</div>
								<?php } ?>
								<div style="margin-top: 30px; font-size: 14px;">
									<p style="margin: 0;">
										<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">車隊資訊</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block; color: #666666; ">車隊名稱</span>
										<span style="margin-left: 10px;"><?php echo $pi_name; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block; color: #666666; ">E-mail</span>
										<span style="margin-left: 10px;"><?php echo $pi_contact_email; ?></span>
									</p>
									<!--
									<p>
										<span style="width: 90px; display: inline-block; color: #666666; ">聯絡資訊</span>
										<span style="margin-left: 10px;">Wechat：henryynag</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block; color: #666666; ">&nbsp;</span>
										<span style="margin-left: 10px;">Line：henryynag</span>
									</p>
									-->
									<p>
										<span style="width: 90px; display: inline-block; color: #666666; ">聯絡電話</span>
										<span style="margin-left: 10px;"><?php echo $pi_contact_phone; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block; color: #666666; ">服務地址</span>
										<span style="margin-left: 10px;"><?php echo $pi_contact_address; ?></span>
									</p>
								</div>
								<hr style="margin-top: 15px; color: lightgrey;">
								<div style="margin-top: 30px; font-size: 14px;">
									<p style="margin: 0;">
										<span style="width: 100%; display: inline-block; font-size: 18px; font-weight: bold;">注意事項及相關規定</span>
									</p>
									<ol style="padding-left: 25px;">
										<li>
											本服務由 <?php echo $pi_name; ?> 提供。
										</li>
										<li>
											本訂單視同車輛租賃合約，請確認上述信息準確無誤。
										</li>
										<li>
											若有行程或車輛上的調動的問題，我們將於訂單成立後一個工作天內與您聯絡，如未能符合您的需求，我們將全額退費給您。
										</li>
										<li>
											最晚於出發日前24小時我們將會提供您車輛的車牌號碼、司機全名及手機號碼。
										</li>
										<li>
											超時，每小時加收NTD <?php echo number_format($over_time); ?> 元；
											<?php
												foreach ($midnight as $value) {
											?>
											深夜清晨( <?php echo $value['frd_begin_time']; ?> ~ <?php echo $value['frd_end_time']; ?> )，每小時加收NTD <?php echo number_format($value['frd_category_value']); ?> 元，
											<?php } ?>
											訂單價格不包含超    時、深夜清晨及附加服務的加價費，請於現場付予司機。
										</li>
										<li>
											若您有需求附加服務而需加價，實際情況我們最晚於出發前2天通知您是否預訂成功，屆時請於現場付予司機。
										</li>
										<li>
											送機時，請於預定時間及地點準時上車。
										</li>
										<li>
											接機時，請在班機抵達後立即打開手機以利聯繫；可快速通關或無託運行李者，預約時請先告知，以縮短候車時間。
										</li>
										<li>
											若接機航班延誤，在原定接機時間24小時前通知車隊可更改調度，24小時內則須請旅客自理，恕不另補償或退費。
										</li>
										<li>
											接送皆最多等候15分鐘，逾時則司機無法繼續等候，且視同已使用該項服務，不予退費。如因資料提供不完整而無法完成接送，恕不另補償或退費。
										</li>
										<?php
											foreach ($cancel_rule as $value) {
												if ($value['fcrd_day'] == 0) {
													$value['fcrd_day'] = "當";
													$zero_percent = $value['fcrd_percent'];
												} else {
													$part_percent = $value['fcrd_percent'];
												}
											}
										?>
										<table style="width:100%; margin: 20px 0; border: 1px solid black; border-collapse: collapse;">
											<tr>
												<th style="text-align: center; border: 1px solid black; border-collapse: collapse;"><?php echo $start_day; ?></th>
												<?php
													for ($i=1;$i<=$fcrd_day;$i++) {
														$day = date("m/d", strtotime($ready_day)+(86400*$i));
												?>
												<th style="text-align: center; border: 1px solid black; border-collapse: collapse;"><?php echo $day ?></th>
												<?php } ?>
												<th style="text-align: center; border: 1px solid black; border-collapse: collapse;"><?php echo $this_day ?></th>
											</tr>
											<tr>
												<td style="text-align: center; border: 1px solid black; border-collapse: collapse; background-color: #cccccc;">免費取消</td>
												<td style="text-align: center; border: 1px solid black; border-collapse: collapse; background-color: #cccccc;" colspan="<?php echo $fcrd_day; ?>">收 <?php echo $part_percent ?> %取消費</td>
												<td style="text-align: center; border: 1px solid black; border-collapse: collapse; background-color: #cccccc;">收 <?php echo $zero_percent ?> %取消費</div>
											</tr>
										</table>
										<li>
											取消規定：乘車日 <?php echo $fcrd_day; ?> 天前取消，全額退款。
											<?php
												foreach ($cancel_rule as $value) {
													if ($value['fcrd_day'] == 0) {
														$value['fcrd_day'] = "當";
														$zero_percent = $value['fcrd_percent'];
													} else {
														$part_percent = $value['fcrd_percent'];
													}
											?>
											乘車日 <span class="onge"><?php echo $value['fcrd_day']; ?>天</span> 內取消，收取全額的 <span class="onge"><?php echo $value['fcrd_percent']; ?></span> %取消費。
											<?php
												}
											?>
										</li>
										<li>
											如遇不可抗力之因素，如颱風、地震、交通中斷等導致您無法如期出發或車行不能發車，經查證屬實後將全額退款。
										</li>
										<li>
											本公司為代收代付平台（本服務恕無法開立發票），若需求乘車收據，請務必於乘車時向司機索取，若當日未索取，視為棄權，無法補發重寄。
										</li>
										<li>
											訂車後若有任何狀況或問題發生，請先行向業者反應，以獲得最即時的處理，如業者無法妥善處理，再向 Tripitta 客服人員反應。
										</li>
									</ol>
								</div>
							</td>
						</tr>
					</table><br>
				</td>
			</tr>
		</table>
	</body>
</html>
<?php
/**
 * 說明：交通 - 觀光巴士 - 付款完成 E-Mail 頁
 * 作者：Bobby
 * 日期：2016年6月24日
 * 備註：
 */

require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$store_service = new store_service();
$partner_service = new partner_service();
$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();

$order_id = get_val("order_id");
$order = $tripitta_service->get_car_order_by_order_id($order_id);
$order_line = $tripitta_service->find_car_order_by_order_id($order_id);
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
$up_name = $fleet_route_detail['up_name'];
$up_address = $fleet_route_detail['up_address'];
$down_name = $fleet_route_detail['down_name'];
$down_address = $fleet_route_detail['down_address'];
$co_adult = $order['co_adult'];
$co_child = $order['co_child'];
$co_price = number_format($order['co_sell_price']);

$adult_price = 0;
$child_price = 0;
foreach ($order_line as $ol) {
	if($ol["col_user_type"] == 1) $adult_price += $ol['col_price'];
	if($ol["col_user_type"] == 2) $child_price += $ol['col_price'];
}
$adult_total = number_format($co_adult*$adult_price);
$child_total = number_format($co_child*$child_price);
$adult_price = number_format($adult_price);
$child_price = number_format($child_price);

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

$co_user_bording_time = $order['col_date']." ".$order['co_user_bording_time'];
$passenger = $tripitta_service->find_car_order_passenger($order['co_id']);
$co_user_memo = $order['co_user_memo'];
$s_id = $order['co_store_id'];
$partner_row = $partner_service->get_store($s_id);
$pi_name = $partner_row['sml_name'];
$pi_contact_email = $partner_row['s_email'];
$pi_contact_phone = $partner_row['s_phone'];
$pi_contact_address = $partner_row['sml_address'];

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
									<span style="font-size: 26px; font-weight: bold;vertical-align: super;margin-left: 10px;">觀光巴士訂單Email通知</span>
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
										<span style="width: 90px; display: inline-block;">出發日期</span>
										<span style="margin-left: 10px;"><?php echo $col_date; ?></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">車隊名稱</span>
										<span style="margin-left: 10px;"><?php echo $sml_name; ?></span>
									</p>
									<!--
									<p>
										<span style="width: 90px; display: inline-block;">車款</span>
										<span style="margin-left: 10px;">Toyota Wish<span style="margin-left: 10px; color: #666666;">( 中巴或休旅車 )</span></span>
									</p>
									-->
									<p>
										<span style="width: 90px; display: inline-block; vertical-align: top;">上車地點</span>
										<span style="margin-left: 10px; display: inline-block;">
											<?php echo $up_name; ?>
											<br><?php echo $up_address; ?>
										</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block; vertical-align: top;">下車地點</span>
										<span style="margin-left: 10px; display: inline-block;">
											<?php echo $down_name; ?>
											<br><?php echo $down_address; ?>
										</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">發車時間</span>
										<span style="margin-left: 10px;"><?php echo $co_user_bording_time; ?></span>
									</p>
									<!--
									<p>
										<span style="width: 90px; display: inline-block;">基本時數</span>
										<span style="margin-left: 10px;">8小時</span>
									</p>
									-->
									<p>
										<span style="width: 90px; display: inline-block;">成人單價</span>
										<span style="margin-left: 10px;"><span>NTD</span> <span><?php echo $adult_price; ?></span></span>
										<span style="width: 65px; margin-left: 20px; display: inline-block;">成人人數</span>
										<span style="margin-left: 10px;"><?php echo $co_adult; ?> 人</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">成人小計</span>
										<span style="margin-left: 10px;"><span>NTD</span> <span><?php echo $adult_total; ?></span></span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">孩童單價</span>
										<span style="margin-left: 10px;"><span>NTD</span> <span><?php echo $child_price; ?></span></span>
										<span style="width: 65px; margin-left: 20px; display: inline-block;">孩童人數</span>
										<span style="margin-left: 10px;"><?php echo $co_child; ?> 人</span>
									</p>
									<p>
										<span style="width: 90px; display: inline-block;">孩童小計</span>
										<span style="margin-left: 10px;"><span>NTD</span> <span><?php echo $child_total; ?></span></span>
									</p>
									<p style="margin-top: 20px;">
										<span style="width: 90px; display: inline-block;">產品總額</span>
										<span style="margin-left: 10px;">NTD </span>
										<span style="margin-left: 10px;"><?php echo number_format($order['co_sell_price'] + ($co_coupon_discount + $co_marking_campaign_discount + $co_bonus_discount)); ?></span>
									</p>
									<!--
									<p>
										<span style="width: 90px; display: inline-block;">銀行紅利折點</span>
										<span style="margin-left: 10px;">NTD </span>
										<span style="margin-left: 10px;">-<?php echo $co_bonus_discount; ?></span>
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
											本網站最後刷卡金額皆以台幣計算。
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
											春節加價2,000元，若您是春節期間訂購，請於現場付予司機。
										</li>
										<li>
											若您有需求附加服務而需加價，實際情況我們最晚於出發前2天通知您是否預訂成功，訂單價格不包含附加服務的加價費，屆時請於現場付予司機。
										</li>
										<li>
											以上景點或使用時間及順序將有增減或調整之可能，實際當天氣候狀況為準。
										</li>
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
											如遇不可抗力之因素，如颱風、地震、交通中斷等導致您無法如期出發或車行不能發車，將全額退款或依您意願延後出發日期。
										</li>
										<li>
											本公司為代收代付平台（本服務恕無法開立發票），若需求乘車收據，請務必於乘車時向司機索取，若當日未索取，視為棄權，無法補發重寄。
										</li>
										<li>
											訂車後若有任何狀況或問題發生，請先行向業者反應，以獲得最即時的處理，如業者無法妥善處理，再向Tripitta客服人員反應。
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
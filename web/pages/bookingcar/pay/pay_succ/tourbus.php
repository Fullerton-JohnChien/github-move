<?php
/**
 * 說明：交通 - 觀光巴士 - 付款完成頁
 * 作者：Bobby
 * 日期：2016年6月15日
 * 備註：
 */

require_once __DIR__ . '/../../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$store_service = new store_service();
$partner_service = new partner_service();
$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();
$tripitta_car_service = new tripitta_car_service();

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
$c_name = $fleet_route_detail['c_name'];
$frd_category_value = $fleet_route_detail['frd_category_value'];
$co_adult = $order['co_adult'];
$co_child = $order['co_child'];
$car_id = $fleet_route_detail['c_id'];
$luggage = $tripitta_service->find_car_luggage($car_id, $co_adult+$co_child);
$co_price = number_format($order['co_sell_price']);

$adult_price = 0;
$child_price = 0;
foreach ($order_line as $ol) {
	if($ol["col_user_type"] == 1) $adult_price += $ol['col_price'];
	if($ol["col_user_type"] == 2) $child_price += $ol['col_price'];
}
$adult_total = number_format($co_adult*$adult_price);
$child_total = number_format($co_child*$child_price);
$pay_total = number_format($co_adult*$adult_price + $co_child*$child_price);
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
$passenger = $tripitta_service->find_car_order_passenger($order['co_id']);
$facility = $tripitta_service->find_car_order_facility($order['co_id']);
$co_user_memo = $order['co_user_memo'];
$s_id = $order['co_store_id'];
$partner_row = $partner_service->get_store($s_id);
$pi_name = $partner_row['sml_name'];
$pi_contact_email = $partner_row['s_email'];
$pi_contact_phone = $partner_row['s_phone'];
$pi_contact_address = $partner_row['sml_address'];

$fleet_rule = $tripitta_car_service->find_fleet_rule_by_store_id($s_id);
$fr_id = $fleet_rule['fr_id'];
$frd_category = "half.day.hour"; //
$rule_detail = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, $frd_category);
$half_day_hour = $rule_detail[0]['frd_category_value'];
$frd_category = "day.hour"; //
$rule_detail = $tripitta_car_service->find_fleet_rule_detail_by_fr_id_category($fr_id, $frd_category);;
$day_hour = $rule_detail[0]['frd_category_value'];
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

// 商品資訊
$fleet_rounte_row = $tripitta_car_service->find_fleet_route_price_by_frp_id($fr_id);
$fr_adult_price = $fleet_rounte_row["fr_adult_price"];
$fr_child_price = $fleet_rounte_row["fr_child_price"];


$this_day = date("m/d", strtotime($col_date));
$start_day = date("m/d", strtotime($col_date)-(86400*($fcrd_day+1)));
$ready_day = date("Y-m-d", strtotime($col_date)-(86400*($fcrd_day+1)));
?>
<div class="order">
	<ul class="orderInfo">
		<li>
			<span><?php echo $co_buyer_name; ?></span>
			<span><?php echo $gender; ?></span>
			您好
		</li>
		<li>
			您已完成訂購，以下為您的訂購資料~
		</li>
		<li>
			訂單編號 <span class="onge"><?php echo $co_transaction_id; ?></span>
		</li>
	</ul>
	<img src="https://chart.googleapis.com/chart?cht=qr&chl=1234&chs=120x120&choe=UTF-8" class="cardType">
</div>
<div class="twoBlockWrap fend">
	<div class="listWrap">
		<div class="lBlock mt10">
			<div class="lTitle">行程</div>
			<div class="lData"><?php echo $co_prod_name; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">出發日期</div>
			<div class="lData"><?php echo $col_date; ?></div>
		</div>
		<div class="lBlock mt10">
			<div class="lTitle">車隊名稱</div>
			<div class="lData"><?php echo $sml_name; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">車款</div>
			<div class="lData">觀光巴士<span class="gray">(中巴或休旅車)</span></div>
		</div>
		<!--
		<div class="lBlock">
			<div class="lTitle">基本時數</div>
			<div class="lData"><?php echo $frd_category_value; ?> 小時</div>
		</div>
		-->
		<div class="lBlock">
			<div class="lTitle">成人單價</div>
			<div class="lData">NTD <?php echo $fr_adult_price; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">成人人數</div>
			<div class="lData"><?php echo $co_adult; ?> 人</div>
		</div>
		<div class="lBlock">
			<div class="lTitle">成人小計</div>
			<div class="lData"><?php echo $adult_total; ?> 人</div>
		</div>
		<div class="lBlock">
			<div class="lTitle">孩童單價</div>
			<div class="lData">NTD <?php echo $fr_child_price; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">孩童人數</div>
			<div class="lData"><?php echo $co_child; ?> 人<span class="gray">(五歲以上)</span></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">孩童小計</div>
			<div class="lData">NTD <?php echo $child_total; ?></div>
		</div>
	</div>
	<div class="listWrap">
		<div class="lBlock">
			<div class="lTitle">產品總額</div>
			<div class="lData AliRight"><span>NTD</span><span class="priceNum"><?php echo $pay_total; ?></span></div>
		</div>
		<!--
		<div class="lBlock">
			<div class="lTitle">紅利折抵</div>
			<div class="lData AliRight"><span>NTD</span><span class="priceNum">-<?php echo $co_bonus_discount; ?></span></div>
		</div>
		-->
		<div class="lBlock">
			<div class="lTitle">優惠折扣</div>
			<div class="lData AliRight"><span>NTD</span><span class="priceNum">-<?php echo number_format($co_coupon_discount+$co_marking_campaign_discount); ?></span></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">應付總額</div>
			<div class="lData AliRight">NTD<span class="priceNum onge"><?php echo $total; ?></span></div>
		</div>
	</div>
</div>
<div class="listWrap mt10">
	<div class="onge">
		此筆訂單金額不含附加服務，請於現場付款本網站最後刷卡金額皆以台幣計算。
	</div>
</div>
<div class="listWrap">
	<h3>聯絡人資料</h3>
	<div class="lBlock">
		<div class="lTitle">聯絡人</div>
		<div class="lData"><?php echo $co_buyer_name; ?></div>
	</div>
	<div class="lBlock">
		<div class="lTitle">聯絡電話</div>
		<div class="lData">
			<span><?php echo $buyer_country; ?></span>
			<span class="ml10"><?php echo $buyer_c_tel_code; ?></span>
			<span class="ml10"><?php echo $co_buyer_mobile; ?></span>
		</div>
	</div>
	<div class="lBlock">
		<div class="lTitle">Email</div>
		<div class="lData"><?php echo $co_buyer_email; ?></div>
	</div>
	<div class="lBlock">
		<div class="lTitle">聯絡資訊</div>
		<div class="lData">
			<div class="socialMedia">
				<div class="wrap">
					<div>Wechat :</div>
					<div><?php echo $co_buyer_wechat_id; ?></div>
				</div>
				<div class="wrap">
					<div>Line :</div>
					<div><?php echo $co_buyer_line_id; ?></div>
				</div>
				<div class="wrap">
					<div>Whatsapp :</div>
					<div><?php echo $co_buyer_whats_app_id; ?></div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="listWrap" style="display:none">
	<h3>乘車聯絡人資料</h3>
	<div class="lBlock">
		<div class="lTitle">聯絡人</div>
		<div class="lData"><?php echo $co_contact_name; ?></div>
	</div>
	<div class="lBlock">
		<div class="lTitle">聯絡電話</div>
		<div class="lData">
			<span><?php echo $contact_country; ?></span>
			<span class="ml10"><?php echo $contact_c_tel_code; ?></span>
			<span class="ml10"><?php echo $co_contact_mobile; ?></span>
		</div>
	</div>
	<div class="lBlock">
		<div class="lTitle">Email</div>
		<div class="lData"><?php echo $co_contact_email; ?></div>
	</div>
	<div class="lBlock">
		<div class="lTitle">聯絡資訊</div>
		<div class="lData">
			<div class="socialMedia">
				<div class="wrap">
					<div>Wechat :</div>
					<div><?php echo $co_contact_wechat_id; ?></div>
				</div>
				<div class="wrap">
					<div>Line :</div>
					<div><?php echo $co_contact_line_id; ?></div>
				</div>
				<div class="wrap">
					<div>Whatsapp :</div>
					<div><?php echo $co_contact_whats_app_id; ?></div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="twoBlockWrap">
	<h3>乘車人保險用資料</h3>
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
	<div class="listWrap mt10">
		<div class="lBlock">
			<div class="lTitle">乘車人</div>
			<div class="lData"><?php echo $value['copi_name']; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">出生日期</div>
			<div class="lData"><?php echo $value['copi_birthday']; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">國籍</div>
			<div class="lData"><?php echo $passenger_country; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">身分證號</div>
			<div class="lData"><?php echo $value['copi_identity_number']; ?></div>
		</div>
	</div>
	<?php } ?>
</div>
<div class="requireWrap">
	<h3>附加服務</h3>
	<ul class="require">
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
				<span class="onge">不加價</span>
			<?php } else { ?>
				<span class="onge">現場付款NTD <?php echo number_format($value['cof_designation_price']); ?></span>
			<?php } ?>
		</li>
		<?php } ?>
	</ul>
	<div class="note onge">此筆訂單金額不含附加服務，請於現場付款。</div>
	<div class="note gray">(需求項目我們會盡量幫您安排，實際情況將於最晚出發前2天確認是否預定成功)</div>
</div>
<!--
<div class="listWrap">
	<h3>亞洲萬里通卡號</h3>
	<div class="lBlock">
		<div class="lData">1111-2222-3333-4444</div>
	</div>
</div>
-->
<?php if (!empty($co_user_memo)) { ?>
<div class="listWrap">
	<h3>備註留言</h3>
	<div class="lBlock">
		<div class="lData">
			<?php echo $co_user_memo; ?>
		</div>
	</div>
</div>
<?php } ?>
<div class="listWrap">
	<h3>車隊資訊</h3>
	<div class="lBlock">
		<div class="lTitle">車隊名稱</div>
		<div class="lData"><?php echo $pi_name; ?></div>
	</div>
	<div class="lBlock">
		<div class="lTitle">E-mail</div>
		<div class="lData"><?php echo $pi_contact_email; ?></div>
	</div>
	<!--
	<div class="lBlock">
		<div class="lTitle">聯絡資訊</div>
		<div class="lData">
			<div class="socialMedia">
				<div class="wrap">
					<div>Wechat:</div>
					<div>asdfasdfasdfasdfasdf</div>
				</div>
				<div class="wrap">
					<div>Line:</div>
					<div>asdfasdfasdfasdfasdf</div>
				</div>
				<div class="wrap">
					<div>Whatsapp:</div>
					<div>asdfasdfasdfasdfasdf</div>
				</div>
			</div>
		</div>
	</div>
	-->
	<div class="lBlock">
		<div class="lTitle">聯絡電話</div>
		<div class="lData"><?php echo $pi_contact_phone; ?></div>
	</div>
	<div class="lBlock">
		<div class="lTitle">服務地址</div>
		<div class="lData"><?php echo $pi_contact_address; ?></div>
	</div>
</div>
<div class="rulePolicy">
	<h3>注意事項及相關規定</h3>
	<div class="rtitle">
		訂車條款：
	</div>
	<ol class="rule">
		<li>
			本服務由 <span class="onge"><?php echo $pi_name; ?></span> 提供。
		</li>
		<li>
			本訂單視同車輛租賃合約，請確認上述信息準確無誤。
		</li>
		<li>
			若有行程或車輛上的調動的問題，我們將於訂單成立後12小時內與您聯絡，如未能符合您的需求，我們將全額退費給您。
		</li>
		<li>
			最晚於出發日前24小時我們將會提供您車輛的車牌號碼、司機全名及手機號碼。
		</li>
		<li>
			若您有需求附加服務而需加價，實際情況我們最晚於出發前2天通知您是否預訂成功，屆時請於現場付 予司機。
		</li>
		<li>
			以上景點或使用時間及順序將有增減或調整之可能，實際以出車行程表為準。
		</li>
		<li>
			取消規定：
			乘車日 <span class="onge"><?php echo $fcrd_day; ?>天</span> 前取消，全額退款。
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
			<br>
			<div class="expDate">
				<div class="date">
					<div><?php echo $start_day; ?></div>
					<?php
						for ($i=1;$i<=$fcrd_day;$i++) {
							$day = date("m/d", strtotime($ready_day)+(86400*$i));
					?>
					<div><?php echo $day ?></div>
					<?php } ?>
					<div><?php echo $this_day ?></div>
				</div>
				<div class="rul">
					<div>免費取消</div>
					<div>收 <?php echo $part_percent ?> %取消費</div>
					<div>收 <?php echo $zero_percent ?> %取消費</div>
				</div>
			</div>
		</li>
		<li>
			如遇不可抗力之因素，如颱風、地震、交通中斷等導致您無法如期出發或車行不能發車，將全額退款或依您意願延後包車日期。
		</li>
		<li>
			本公司為代收代付平台（本服務恕無法開立發票），若需求乘車收據，請務必於乘車時向司機索取，若當日未索取，視為棄權，無法補發重寄。
		</li>
		<li>
			訂車後若有任何狀況或問題發生，請先行向業者反應，以獲得最即時的處理，如業者無法妥善處理，再向Tripitta客服人員反應。
		</li>
	</ol>
	<div class="rtitle">
		訂單異動：
	</div>
	<ol class="rule">
		<li>
			旅客若需要對行程景點和乘車人數做變更時，請直接向包車業者聯繫，並於現場補足差額。
		</li>
		<li>
			訂單異動後，如果訂單費用總計小於原訂單費用總計，旅客不得要求退其差額。
		</li>
	</ol>
	<div class="rtitle">
		取消規定：
	</div>
	<ol class="rule">
		<li>
			預訂包車日 <?php echo $fcrd_day; ?>  日前取消包車，全額退費。
		</li>
		<?php
			foreach ($cancel_rule as $value) {
				if ($value['fcrd_day'] == 0) {
					$value['fcrd_day'] = "當";
					$zero_percent = $value['fcrd_percent'];
				} else {
					$part_percent = $value['fcrd_percent'];
				}
		?>
		<li>預訂包車日 <?php echo $value['fcrd_day']; ?> 日前取消包車，收取全額的 <?php echo $value['fcrd_percent']; ?> %取消費。</li>
		<?php
			}
		?>
		<li>
			<span class="onge">
				如遇農曆春節、寒暑假、特殊展期、國定假日及前夕或特殊專案商品，取消規定需依各商品內之說明公告為基準，取消費用將不適用前述規定，敬請見諒。
			</span>
		</li>
	</ol>
	<div class="rtitle">
		退費條件：
	</div>
	<ol class="rule">
		<li>
			遇到包車業者無法依訂單要求派車時，Tripitta將會全額退款及全力協助調度其他車隊。
		</li>
		<li>
			如遇不可抗力之因素，如颱風、地震、交通中斷等導致不能發車，將全額退款或依您意願，延後包車日期。
		</li>
	</ol>
</div>
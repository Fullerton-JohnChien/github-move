<?php
/**
 * 說明：交通 - 高鐵 - 付款完成頁
 * 作者：Casper
 * 日期：2016年6月20日
 * 備註：
 */

require_once __DIR__ . '/../../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$tripitta_service = new tripitta_service();
$tripitta_web_service = new tripitta_web_service();

$order_id = get_val("order_id");
// $order_id = 14;
$order = $tripitta_service->get_ticket_order_by_order_id($order_id);
$order_line = $tripitta_service->find_ticket_order_line($order_id);
$begin_area_id = $order["to_begin_area_id"];
$end_area_id = $order["to_end_area_id"];
$living_country_id = $order["to_country_id"];
$living_country_row = $tripitta_web_service->load_country($living_country_id);
$living_country = $living_country_row["c_name"];
$living_country_code = $living_country_row["c_tel_code"];

// 取得高鐵 - 票券類型資料
$ticket_id = $order['to_ticket_id'];
$ticket = $tripitta_service->get_ticket($ticket_id);
$tree_config = $tripitta_service->get_tree_config_by_id($ticket['t_tree_config_id']);
$tree_type_name = $tree_config['tc_name'];
// $tc_parent_id = $tree_config['tc_parent_id'];

// $category = 'tree.category';
// $tree_list = $tripitta_service->find_tree_config($category, $tc_parent_id);
// $tree_type = null;
// $tree_type_name = null;
// if(!empty($tree_list)){
// 	$tree_count = 1;
// 	foreach ($tree_list as $t){
// 		if($tree_count == 1){
// // 			$tree_type = $t["tc_id"];
// 			$tree_type_name = $t["tc_name"];
// 		}
// 		$tree_count++;
// 	}
// }
$ticket_type_price_list = $tripitta_service->find_ticket_type_price_by_ticket_id($ticket_id, $begin_area_id, $end_area_id);

$adult_price = 0;
$child_price = 0;
if(!empty($ticket_type_price_list)){
	foreach ($ticket_type_price_list as $tpl){
		if($tpl["tt_name"]=="成人"){
			$adult_price = $tpl["ttp_sell_price"];
		}
		if($tpl["tt_name"]=="小孩"){
			$child_price = $tpl["ttp_sell_price"];
		}
	}
}
$adult_count = 0;
$child_count = 0;
$adult_total = 0;
$child_total = 0;
if(!empty($order_line)){
	foreach ($order_line as $ol){
		if($ol["tol_ticket_type_name"]=="成人"){
			$adult_total += $ol["tol_sell_price"];
			$adult_count++;
		}
		if($ol["tol_ticket_type_name"]=="小孩"){
			$child_total += $ol["tol_sell_price"];
			$child_count++;
		}
	}
}

$to_user_id = $order['to_user_id'];
$user = $tripitta_service->get_user_by_user_id($to_user_id);
$to_user_name = $order['to_user_name'];
$to_transaction_id = $order['to_transaction_id'];
$gender = ($user['msg']['gender'] == "M") ? "先生" : "小姐";
$begin_area = $tripitta_service->get_area_by_id($begin_area_id);
$end_area = $tripitta_service->get_area_by_id($end_area_id);
$begin_name = $begin_area["a_name"];
$end_name = $end_area["a_name"];
$to_forecast_date = $order["to_forecast_date"];
$take_time = strtotime($to_forecast_date);

?>
<div class="pBlock-hsr">
	<div class="imgWrap">
		<img src="/web/img/sec/transport/hsr/hsr.png" class="logo">
		<div class="ticketType"><?php echo $tree_type_name; ?></div>
	</div>
	<div class="pbBlock">
		<div class="pbDeparture">
			<div class="pbLocation"><?php echo $begin_name; ?></div>
			<div class="pbText">出發地</div>
		</div>
		<div class="pbCalendar">
			<div class="pbYear"><?php echo date("Y", $take_time); ?></div>
			<div class="pbMD">
				<span class="pbMonth"><?php echo date("m", $take_time); ?></span>
				<span>.</span>
				<span class="pbDate"><?php echo date("d", $take_time); ?></span>
			</div>
			<div class="arrowWrap">
				<div class="arrowSign"></div>
			</div>
			<div class="pbText">乘坐日期</div>
		</div>
		<div class="pbDestination">
			<div class="pbLocation"><?php echo $end_name; ?></div>
			<div class="pbText">目的地</div>
		</div>
	</div>
</div>
<div class="order">
	<ul class="orderInfo">
		<li>
			<span><?php echo $to_user_name; ?></span>
			<span><?php echo $gender; ?></span>
			您好
		</li>
		<li>
			您已完成訂購， 在我們的服務時間內：一到六 09:00～19:00；日 09:00～16:00 訂購，訂購後一小時內我們會再寄出電子乘車券給您，非服務時間則為隔日出票 。以下為您的訂購資料。
		</li>
		<li class="mt10">
			訂單編號<span class="onge ml10"> <?php echo $to_transaction_id; ?></span>
		</li>
	</ul>
</div>
<div class="twoBlockWrap">
	<div class="listWrap ">
		<?php if($adult_count>0){ ?>
		<div class="lBlock between">
			<div class="lBlock">
				<div class="lTitle">成人單價</div>
				<div class="lData">
					<span class="currency">NTD</span>
					<span><?php echo number_format($adult_price); ?></span>
				</div>
			</div>
			<div class="lBlock">
				<div class="lTitle">成人人數</div>
				<div class="lData"><?php echo $adult_count; ?>人</div>
			</div>
		</div>
		<?php } ?>
		<?php if($child_count>0){ ?>
		<div class="lBlock between">
			<div class="lBlock">
				<div class="lTitle">孩童單價</div>
				<div class="lData">
					<span class="currency">NTD</span>
					<span><?php echo number_format($child_price); ?></span>
				</div>
			</div>
			<div class="lBlock">
				<div class="lTitle">孩童人數</div>
				<div class="lData"><?php echo $child_count; ?>人</div>
			</div>
		</div>
		<?php } ?>
		<div class="lBlock">
			<hr>
		</div>
	</div>
	<div class="listWrap">
		<?php if($adult_count>0){ ?>
		<div class="lBlock">
			<div class="lTitle">成人小計</div>
			<div class="lData AliRight"><span>NTD</span><span class="priceNum"><?php echo number_format($adult_total); ?></span></div>
		</div>
		<?php } ?>
		<?php if($child_count>0){ ?>
		<div class="lBlock">
			<div class="lTitle">孩童小計</div>
			<div class="lData AliRight"><span>NTD</span><span class="priceNum"><?php echo number_format($child_total); ?></span></div>
		</div>
		<?php } ?>
		<div class="lBlock">
			<hr>
		</div>
		<div class="lBlock">
			<div class="lTitle">產品總額</div>
			<div class="lData AliRight"><span>NTD</span><span class="priceNum"><?php echo number_format($adult_total + $child_total); ?></span></div>
		</div>
		<?php /*
		<div class="lBlock">
			<div class="lTitle">銀行紅利折點</div>
			<div class="lData AliRight"><span>NTD</span><span class="priceNum"><?php echo number_format($order["to_bonus_discount"]); ?></span></div>
		</div>
		*/ ?>
		<div class="lBlock">
			<div class="lTitle">優惠折扣</div>
			<div class="lData AliRight"><span>NTD</span><span class="priceNum">-<?php echo number_format($order["to_marking_campaign_discount"]); ?></span></div>
		</div>

		<div class="lBlock">
			<div class="lTitle">應付總額</div>
			<div class="lData AliRight">NTD<span class="priceNum onge"><?php echo number_format($order["to_sell_price"]); ?></span></div>
		</div>
	</div>
</div>
<div class="listWrap mt10">
	<div class="onge">
		成人定義(12歲以上)
		<br>孩童定義(6~11歲，未滿6歲或115cm以下免票)
	</div>
</div>
<div class="listWrap">
	<h3>聯絡人資料</h3>
	<div class="lBlock">
		<div class="lTitle">訂購人</div>
		<div class="lData"><?php echo $order["to_user_name"]; ?></div>
	</div>
	<div class="lBlock">
		<div class="lTitle">聯絡電話</div>
		<div class="lData">
			<span><?php echo $living_country; ?></span>
			<span class="ml10"><?php echo $living_country_code; ?></span>
			<span class="ml10"><?php echo $order["to_user_mobile"]; ?></span>
		</div>
	</div>
	<div class="lBlock">
		<div class="lTitle">Email</div>
		<div class="lData"><?php echo $order["to_user_email"]; ?></div>
	</div>

	<div class="lBlock">
		<div class="lTitle">聯絡資訊</div>
		<div class="lData">
			<div class="socialMedia">
				<div class="wrap">
					<div>Wechat:</div>
					<div><?php echo $order["to_wechat_id"]; ?></div>
				</div>
				<div class="wrap">
					<div>Line:</div>
					<div><?php echo $order["to_line_id"]; ?></div>
				</div>
				<div class="wrap">
					<div>Whatsapp:</div>
					<div><?php echo $order["to_whats_app_id"]; ?></div>
				</div>
			</div>
		</div>
	</div>

</div>
<div class="twoBlockWrap">
	<h3>乘客資料</h3>
	<?php
	if(!empty($order_line)){
		foreach ($order_line as $ol){
			if(!empty($ol["tol_user_country_id"])){
				$country_row = $tripitta_web_service->load_country($ol["tol_user_country_id"]);
			}
		?>
	<div class="listWrap mt10">
		<div class="lBlock">
			<div class="lTitle">英文姓名</div>
			<div class="lData"><?php echo $ol["tol_user_name"]; ?></div>
		</div>
		<?php /*
		<div class="lBlock">
			<div class="lTitle">英文名</div>
			<div class="lData">Henry</div>
		</div>
		<div class="lBlock">
			<div class="lTitle">英文姓</div>
			<div class="lData">Yang</div>
		</div>
		*/ ?>
		<div class="lBlock">
			<div class="lTitle">性別</div>
			<div class="lData"><?php echo $ol["tol_user_gender"]=="M" ? "男" : "女"; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">出生日期</div>
			<div class="lData"><?php echo $ol["tol_user_birthday"]; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">國籍</div>
			<div class="lData"><?php echo $country_row["c_name"]; ?></div>
		</div>
		<div class="lBlock">
			<div class="lTitle">身分證號</div>
			<div class="lData"><?php echo $ol["tol_user_passport_number"]; ?></div>
		</div>
	</div>
		<?php
		}
	}
	?>
</div>
<?php /*
<div class="listWrap">
	<h3>亞洲萬里通卡號</h3>
	<div class="lBlock">
		<div class="lData">1111-2222-3333-4444</div>
	</div>
</div>
*/ ?>
<div class="listWrap">
	<h3>備註留言</h3>
	<div class="lBlock">
		<div class="lData">
			<?php echo $order["to_memo"]; ?>
		</div>
	</div>
</div>
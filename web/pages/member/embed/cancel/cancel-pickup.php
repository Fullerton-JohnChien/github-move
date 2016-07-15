<?php
/**
 * 說明：交通 - 接送機 - 訂單明細頁
 * 作者：Bobby
 * 日期：2016年6月29日
 * 備註：
 */

require_once __DIR__ . '/../../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$tripitta_service = new tripitta_service();

$order_id = get_val("order_id");
$order = $tripitta_service->get_car_order_by_order_id($order_id);
$type = $order['co_route_type'];
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
// $co_sell_price = number_format($order['co_sell_price']);
$co_bonus_discount = number_format($order['co_bonus_discount']);
$co_coupon_discount = $order['co_coupon_discount'];
$co_marking_campaign_discount = $order['co_marking_campaign_discount'];
$total = number_format($order['co_sell_price']);
?>
<div class="member-cancel-pickup-container">
	<div class="order title">
		<span>取消訂單</span>
		<button class="order-back" data-type="<?php echo $type; ?>" data-devicetype="<?php echo $deviceType; ?>">回上頁</button>
	</div>
	<hr class="hidden">
	<input id="order_id" type="hidden" value="<?php echo $order_id; ?>">
	<input id="user_id" type="hidden" value="<?php echo $co_user_id; ?>">
	<div class="text">
		<span><?php echo $co_buyer_name; ?></span>
		<span><?php echo $gender; ?></span>
		您好
	</div>
	<div class="text">
		您已經完成取消訂單，以下為您的訂單資料~
	</div>
	<div class="text">
		訂單編號 <span class="onge"><?php echo $co_transaction_id; ?></span>
	</div>
	<div class="info">
		<?php if ($deviceType == "computer") { ?>
		<div class="left">
		<?php } else { ?>
		<div class="left" style="width:100%;">
		<?php } ?>
			<div class="row">
				<div class="meta">行程</div>
				<div class="data"><?php echo $co_prod_name; ?></div>
			</div>
			<div class="row">
				<?php if ($type == 2) { ?>
				<div class="meta">接機日期</div>
				<?php } else if ($type == 4) { ?>
				<div class="meta">送機日期</div>
				<?php } ?>
				<div class="data"><?php echo $col_date; ?></div>
			</div>
			<div class="row">
				<div class="meta">車隊名稱</div>
				<div class="data"><?php echo $sml_name; ?></div>
			</div>
			<div class="row">
				<div class="meta">車款</div>
				<div class="data"><?php echo $c_name; ?><span class="subscribe">( 或同級車款 )</span></div>
			</div>
			<div class="row">
				<div class="meta">出發地</div>
				<div class="data"><?php echo $cr_boarding_name; ?></div>
			</div>
			<div class="row">
				<div class="meta">目的地</div>
				<div class="data"><?php echo $cr_get_off_name; ?></div>
			</div>
			<div class="row">
				<div class="meta">成人</div>
				<div class="data"><?php echo $co_adult; ?> 人</div>
			</div>
			<div class="row">
				<div class="meta">孩童</div>
				<div class="data"><?php echo $co_child; ?> 人<span class="subscribe">(五歲以上)</span></div>
			</div>
			<?php
				if (!empty($luggage)) {
					$i = 0;
					foreach ($luggage as $l){
				?>
				<div class="row">
					<div class="meta"><?php if ($i == 0) { echo "行李"; } ?></div>
					<div class="data"><?php echo $l["ca_capacity_luggage"];?> 件<span class="subscribe">(28吋以上)</span></div>
				</div>
				<?php
						$i++;
					}
				}
			?>
			<div class="note">
				此筆訂單金額不含附加服務，請於現場付款本網站最後刷卡金額皆以台幣計算。
			</div>
		</div>
		<?php if ($deviceType == "computer") { ?>
		<div class="right">
		<?php } else { ?>
		<div class="right" style="width:100%;">
		<?php } ?>
			<div class="row">
				<div class="meta">產品總額</div>
				<div class="currency">NTD</div>
				<div class="price"><?php echo number_format($order['co_sell_price'] + ($co_coupon_discount + $co_marking_campaign_discount + $co_bonus_discount)); ?></div>
			</div>
			<!--
			<div class="row">
				<div class="meta">銀行紅利折點</div>
				<div class="currency">NTD</div>
				<div class="price">-<?php echo $co_bonus_discount; ?></div>
			</div>
			-->
			<div class="row">
				<div class="meta">優惠折扣</div>
				<div class="currency">NTD</div>
				<div class="price">-<?php echo number_format($co_coupon_discount + $co_marking_campaign_discount); ?></div>
			</div>
			<div class="row">
				<div class="meta">應付總額</div>
				<div class="currency onge">NTD</div>
				<div class="price onge"><?php echo $total; ?></div>
			</div>
			<div class="row">
				<div class="note">
					( 約 NTD <?php echo $total; ?> )
				</div>
			</div>
			<div class="note2-m">
				此筆訂單金額不含附加服務，請於現場付款本網站最後刷卡金額皆以台幣計算。
			</div>
		</div>
	</div>
	<hr>
	<div class="info1">
		<div class="left">
			<div>
				取消訂單明細
			</div>
		</div>
		<?php if ($deviceType == "computer") { ?>
		<div class="right">
		<?php } else { ?>
		<div class="right" style="width:100%;">
		<?php } ?>
			<div class="row">
				<div class="meta">已付金額</div>
				<div class="currency">NTD</div>
				<div class="price"><?php echo $total; ?></div>
			</div>
			<!--
			<div class="row">
				<div class="meta">銀行紅利折點</div>
				<div class="currency">NTD</div>
				<div class="price">-200</div>
			</div>
			<div class="row">
				<div class="meta">優惠折扣</div>
				<div class="currency">NTD</div>
				<div class="price">-200</div>
			</div>
			<div class="row">
				<div class="meta">取消費</div>
				<div class="currency">NTD</div>
				<div class="price">-200</div>
			</div>
			-->
		</div>
	</div>
	<hr>
	<div class="info2">
		<?php if ($deviceType == "computer") { ?>
		<div class="left">
		<?php } else { ?>
		<div class="left" style="width:100%;">
		<?php } ?>
			<ul class="policy">
				取消須知：
				<li>若您使用支付寶付款，則將會扣除 本訂單金額之4%，20元為手續費，恕無法全額退款。</li>
				<li>確定取消後，5日內將刷退至原付款之信用卡帳戶中。</li>
				<li>若您使用銀聯卡或支付寶之則將依中國各銀行之作業時間為準，約7~20個工作天不等。</li>
			</ul>
		</div>
		<?php if ($deviceType == "computer") { ?>
		<div class="right">
		<?php } else { ?>
		<div class="right" style="width:100%;">
		<?php } ?>
			<div class="row">
				<div class="meta onge">刷退金額</div>
				<div class="currency onge">NTD</div>
				<div class="price onge"><?php echo $total; ?></div>
			</div>
			<!--
			<div class="row">
				<div class="meta">紅利返還</div>
				<div class="payBack">1000</div>
			</div>
			-->
			<div class="row">
				<div class="note">( 約 NTD <?php echo $total; ?> )</div>
			</div>
		</div>
	</div>
	<div id="success" class="success" style="display:none;">
		<i class="img-member-check-big"></i>
		<div>
			成功完成取消！
		</div>
	</div>
	<button class="btn" onclick="cancel();">
		確定取消
	</button>
	<!--
	<button class="btn">
		回接送機訂單
	</button>
	 -->
	<div class="note1">
		確定取消後，將刷退至原付款之信用卡帳戶
	</div>
</div>
<script src="/web/js/orders.js"></script>
<script type="text/javascript">
	function cancel() {
		var order_id = $("#order_id").val();
		var user_id = $("#user_id").val();
		$.getJSON('/web/ajax/ajax.php',
			{func: 'cancel_car_order', 'order_id': order_id, 'user_id': user_id},
			function(data) {
				if (data.code == "0000") {
					$("#success").show();
					alert("取消此筆接送機訂單成功!");
				} else if (data.code == "9999") {
					alert(data.msg);
				}
				var url = "";
				var id_name = "";
				var deviceType = "<?php echo $deviceType; ?>";
				if (deviceType == "computer") {
					url = "orders-pickup.php";
					id_name = "#myOrders";
				} else {
					url = "orders-pickup-m.php";
					id_name = "#myOrders-m";						
				}
				var redirect_url = "/web/pages/member/embed/orders/" + url;
				$( id_name ).load( redirect_url );
			}
		);
	}
</script>
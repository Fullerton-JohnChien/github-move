<?php
/**
 * 說明：交通 - 修改訂單頁
 * 作者：Bobby
 * 日期：2016年07月13日
 * 備註：
 */

require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$tripitta_service = new tripitta_service();

$order_id = get_val("order_id");
$order = $tripitta_service->get_car_order_by_order_id($order_id);
$co_transaction_id = $order['co_transaction_id'];
$co_route_type = $order['co_route_type'];
$passenger = $tripitta_service->find_car_order_passenger($order['co_id']);

$min_birthday = "1/1/1920";
$max_birthday = date("m/d/Y", strtotime("-365 days"));
?>
<div class="member-insurance-container">
	<div class="title">
		<span>訂單明細</span>
		<button type="button" class="order-back" data-type="<?php echo $co_route_type; ?>" data-devicetype="<?php echo $deviceType; ?>">回上頁</button>
	</div>
	<hr>
	<div class="text">
		<span>訂單編號</span>
		<span class="orderNum"><?php echo $co_transaction_id; ?></span>
	</div>
	<div class="text">
		<span>請點選要修改的乘車人保險資料</span>
	</div>
	<div class="members">
		<?php
			$country_array = constants_user_center::$LIVING_COUNTRY_TEXT;
			foreach ($passenger as $value) {
				$copi_country_id = $value['copi_country_id'];
				$passenger_country = "";
				foreach ($country_array as $country_key => $country_value) {
					if ($country_key == $copi_country_id) {
						$passenger_country = $country_value;
					}
				}
		?>
		<div class="member">
			<div>
				<span>乘車人</span>
				<span><?php echo $value['copi_name']; ?></span>
			</div>
			<div>
				<span>出生日期</span>
				<span><?php echo $value['copi_birthday']; ?></span>
			</div>
			<div>
				<span>國籍</span>
				<span><?php echo $passenger_country; ?></span>
			</div>
			<div>
				<span>身分證號</span>
				<span><?php echo $value['copi_identity_number']; ?></span>
			</div>
			<div class="btnWrap">
				<button class="btn" data-id="<?php echo $value['copi_id']; ?>" data-name="<?php echo $value['copi_name']; ?>" data-birthday="<?php echo $value['copi_birthday']; ?>" data-country="<?php echo $copi_country_id; ?>" data-identity="<?php echo $value['copi_identity_number']; ?>">修改</button>
			</div>
		</div>
		<?php } ?>
		<div class="note">
			注意：
			<br>以上資料請正確填寫，以利保險使用。若提供錯誤請於出發前二日前，直接向車隊提出修正，若未提出導致無法投保或理賠，責任由預訂者承擔。若暫無入台證號、護照號…等可以填寫，可先跳過，先完成訂購，但請務必於出發前二日前，主動與車隊聯絡，提供給您的保險資料給車隊，以利保險。
		</div>
	</div>
	<div class="popupConnect">
		<div class="closeBtn">
			<i class="fa fa-times" aria-hidden="true"></i>
		</div>
		<input id="order_id" type="hidden" name="order_id" value="<?php echo $order_id ?>">
		<input id="copi_id" type="hidden" name="copi_id" value="">
		<input id="devicetype" type="hidden" name="devicetype" value="<?php echo $deviceType; ?>">
		<div class="blk" style="height: 50px;">
			<div class="text">
				姓名
			</div>
			<div class="blank">
				<input id="copi_name" type="text" name="copi_name" maxlength="20">
			</div>
		</div>
		<div class="blk" style="height: 50px;">
			<div class="text">
				生日
			</div>
			<div class="blank">
				<input id="copi_birthday" type="date" name="copi_birthday" maxlength="10">
			</div>
		</div>
		<div class="blk" style="height: 50px;">
			<div class="text">
				國籍
			</div>
			<div class="blank">
				<select id="copi_country_id" name="copi_country_id">
					<?php foreach(constants_user_center::$LIVING_COUNTRY_TEXT as $key => $value) { ?>
					<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
					<?php }	?>
				</select>
				<i class="fa fa-angle-down"></i>
			</div>
		</div>
		<div class="blk" style="height: 50px;">
			<div class="text">
				證號
			</div>
			<div class="blank">
				<input id="copi_identity_number" type="text" name="copi_identity_number" maxlength="20">
			</div>
		</div>
		<div class="blk">
			<div class="text">
				&nbsp;
			</div>
			<button class="btn">確定修改</button>
		</div>
	</div>
</div>
<script src="/web/js/orders.js"></script>
<script type="text/javascript">
	var caneldar_option = <?php echo json_encode(Constants::$CALENDAR_OPTIONS); ?>;
	$('input[name="copi_birthday"]').datepicker(caneldar_option).datepicker('option', {maxDate: new Date('<?php echo $max_birthday; ?>'), minDate: new Date('<?php echo $min_birthday; ?>'), defaultDate: new Date('<?php echo $max_birthday; ?>'), yearRange: '-100:+100'});
</script>
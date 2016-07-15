<?php
/**
 * 說明：
 * 作者：Bobby 
 * 日期：2016年6月29日
 * 備註：
 */
require_once __DIR__ . '/../../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$tripitta_service = new tripitta_service();
$user_id = $_SESSION['travel.ezding.user.data']['serialId'];
$order = $tripitta_service->find_user_ticket_order($user_id);
$type = 5;
$order_status = Constants::$TICKET_ORDER_PROCESS_STATUS;
?>
<?php
	$now = date("Y-m-d H:i:s");
	foreach ($order as $value) {
		$to_id = $value['to_id'];
		$cancel_count = $value['cancel_count'];
		$to_ticket_name = (mb_strlen($value['to_ticket_name'], 'UTF-8') > 12) ? mb_substr($value['to_ticket_name'], 0, 12, 'UTF-8')."..." : $value['to_ticket_name'];
		$to_transaction_id = $value['to_transaction_id'];
		$to_forecast_date = $value['to_forecast_date'];
		$to_cancel_status = $value['to_cancel_status'];
		$apply_cancel_count = $value['apply_cancel_count'];
		$to_thirdparty_proof_photo = $value['to_thirdparty_proof_photo'];
		if ($to_cancel_status == 0) { // 未取消
			if ($to_thirdparty_proof_photo == 0) {	// 待發憑證
				$order_status_idx = 0;
			} else {
				$order_status_idx = 1;  // 已發憑證
			}
		}
		if ($to_cancel_status == 2) { // 部分取消
			$order_status_idx = 3;
		}
		if ($apply_cancel_count > 0) { // 取消確認中
			$order_status_idx = 2;
		}
		if ($to_cancel_status == 1) { // 已取消
			$order_status_idx = 4;
		}
?>
	<div class="order status_<?php echo $order_status_idx?>">
		<div class="info">
			<h2 class="title">
				<div class="tag">高鐵</div>
				<div class="titleText"><?php echo $to_ticket_name; ?></div>
			</h2>
			<div class="ordnum">
				<span>訂單編號</span>
				<span><?php echo $to_transaction_id; ?></span>
			</div>
			<div class="startDate">
				<span>乘坐日期</span>
				<span><?php echo $to_forecast_date; ?></span>
			</div>
		</div>
		<label class="wrap">
			<div class="status nouse">
				<div class="comfirm"><?php echo $order_status[$order_status_idx]?></div>
				<!-- for hsr 記得要包comfirm這個div -->
			</div>
		</label>
		<div class="list">
		<?php if ($to_thirdparty_proof_photo > 0) {?>
			<a href="#" onclick="sendOrderNotify('<?=$to_thirdparty_proof_photo?>','<?=$to_id?>')">重寄憑證</a>
		<?php }?>
			<a href="javascript:void(0)" onclick="order_info('<?php echo $deviceType; ?>', '<?php echo $type; ?>', '<?php echo $to_id; ?>');">訂單明細</a>
		</div>
		<?php if ($to_cancel_status != 1 && $to_forecast_date > $now) {?>
		<a class="cancel" onclick="cancel('<?php echo $deviceType; ?>', '<?php echo $type; ?>', '<?php echo $to_id; ?>');" style="cursor:pointer;">
			取消訂單
		</a>
		<?php }?>
	</div>

<?php

	}
?>
<script src="/web/js/orders.js"></script>
<script type="text/javascript">
	order_search_results('<?php echo $order_count; ?>');
</script>
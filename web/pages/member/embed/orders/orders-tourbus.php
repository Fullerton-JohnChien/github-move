<?php
/**
 * 說明：
 * 作者：Bobby
 * 日期：2016年6月28日
 * 備註：
 */

require_once __DIR__ . '/../../../../config.php';

$tripitta_service = new tripitta_service();

$car_rounte_id = array("3");
$user_id = $_SESSION['travel.ezding.user.data']['serialId'];
$order = $tripitta_service->find_user_car_order($user_id, $car_rounte_id);
$order_count = count($order);
$type = 3;

// bobby 20160712 start
$area_dao = Dao_loader::__get_area_dao();
$lang = "tw";
// bobby 20160712 end
?>
<?php
	$now = date("Y-m-d H:i:s");
	foreach ($order as $value) {
		$co_id = $value['co_id'];
		$co_prod_name = $value['co_prod_name'];
		$co_transaction_id = $value['co_transaction_id'];
		$co_start_time = $value['co_start_time'];
		$co_cancel_status = $value['co_cancel_status'];
		// bobby 20160712 start
		$fr_id = $value['co_prod_id'];
		$cr_type = $value['co_route_type'];
		$fleet_route_detail = $tripitta_service->get_fleet_route_detail($cr_type, $fr_id);
		$co_start_date = substr($co_start_time, 0, 10);
		$people = $value['co_adult'] + $value['co_child'];
		if (!empty($fleet_route_detail)) {
			$cr_get_off = $fleet_route_detail['cr_get_off'];
			$area = $area_dao->loadAreaWithLang($cr_get_off, $lang);
		}
		// bobby 20160712 end
		if ($co_cancel_status == 0) {
			if ($co_start_time < $now) {	//已使用
?>
	<div class="order">
		<div class="info">
			<h2 class="title">
				<span class="tag">觀光巴士</span>
				<span class="titleText"><?php echo $co_prod_name; ?></span>
			</h2>
			<div class="ordnum">
				<span>訂單編號</span>
				<span><?php echo $co_transaction_id; ?></span>
			</div>
			<div class="startDate">
				<span>出發日期</span>
				<span><?php echo $co_start_time; ?></span>
			</div>
			<div class="list">
				<a href="javascript:void(0)" class="notification" data-id="<?php echo $value['co_id']; ?>" data-devicetype="<?php echo $deviceType; ?>">查看留言</a>
				<!--
				<a href="javascript:void(0)">給予評價</a>
				-->
				<a href="javascript:void(0)" class="ride-qrcode" data-server="<?php echo $serverName; ?>" data-type="<?php echo $type; ?>" data-id="<?php echo $co_id; ?>">乘車憑證</a>
				<a href="javascript:void(0)" onclick="order_info('<?php echo $deviceType; ?>', '<?php echo $type; ?>', '<?php echo $co_id; ?>');">訂單明細</a>
			</div>
		</div>
		<label class="wrap">
			<div class="status">
				已使用
			</div>
		</label>
	</div>
<?php 
			} else {	//未使用
?>
	<div class="order">
		<div class="info">
			<h2 class="title">
				<span class="tag">觀光巴士</span>
				<span class="titleText"><?php echo $co_prod_name; ?></span>
			</h2>
			<div class="ordnum">
				<span>訂單編號</span>
				<span><?php echo $co_transaction_id; ?></span>
			</div>
			<div class="startDate">
				<span>出發日期</span>
				<span><?php echo $co_start_time; ?></span>
			</div>
			<div class="list">
				<a href="javascript:void(0)" class="order-contactus" data-id="<?php echo $value['co_id']; ?>" data-name="<?php echo $fleet_route_detail['sml_name']; ?>" data-date="<?php echo $co_start_date; ?>" data-getoff="<?php echo $area['a_name']; ?>" data-days="<?php echo $value['co_days']; ?>" data-people="<?php echo $people; ?>">聯繫業者</a>
				<a href="javascript:void(0)" class="modify" data-devicetype="<?php echo $deviceType; ?>" data-orderid="<?php echo $value['co_id']; ?>">修改資料</a>
				<a href="javascript:void(0)" class="ride-qrcode" data-server="<?php echo $serverName; ?>" data-type="<?php echo $type; ?>" data-id="<?php echo $co_id; ?>">乘車憑證</a>
				<a href="javascript:void(0)" onclick="order_info('<?php echo $deviceType; ?>', '<?php echo $type; ?>', '<?php echo $co_id; ?>');">訂單明細</a>
			</div>
		</div>
		<label class="wrap">
			<div class="status nouse">
				未使用
			</div>
			<a class="cancel" onclick="cancel('<?php echo $deviceType; ?>', '<?php echo $type; ?>', '<?php echo $co_id; ?>');" style="cursor:pointer;">
				取消訂單
			</a>
		</label>
	</div>
<?php
			}
		} else {	//已取消
?>
	<div class="order">
		<div class="info">
			<h2 class="title">
				<span class="tag">觀光巴士</span>
				<span class="titleText"><?php echo $co_prod_name; ?></span>
			</h2>
			<div class="ordnum">
				<span>訂單編號</span>
				<span><?php echo $co_transaction_id; ?></span>
			</div>
			<div class="startDate">
				<span>出發日期</span>
				<span><?php echo $co_start_time; ?></span>
			</div>
			<div class="list">
				<a href="javascript:void(0)" class="order-contactus" data-id="<?php echo $value['co_id']; ?>" data-name="<?php echo $fleet_route_detail['sml_name']; ?>" data-date="<?php echo $co_start_date; ?>" data-getoff="<?php echo $area['a_name']; ?>" data-days="<?php echo $value['co_days']; ?>" data-people="<?php echo $people; ?>">聯繫業者</a>
				<a href="javascript:void(0)" onclick="order_info('<?php echo $deviceType; ?>', '<?php echo $type; ?>', '<?php echo $co_id; ?>');">訂單明細</a>
			</div>
		</div>
		<label class="wrap">
			<div class="status canceled">
				已取消
			</div>
		</label>
	</div>
<?php
		}
	}
?>
<script src="/web/js/orders.js"></script>
<script type="text/javascript">
	order_search_results('<?php echo $order_count; ?>');
</script>
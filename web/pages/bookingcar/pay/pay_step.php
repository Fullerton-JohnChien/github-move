<?php
/**
 * 說明：交通 - 付款步驟頁
 * 作者：Steak
 * 日期：2016年6月4日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");
if(!empty($_SESSION["pay_step"])){
	$p_step = $_SESSION["pay_step"];
	$type = $p_step["type"];
	$pickup_type = !empty($p_step["pickup_type"]) ? $p_step["pickup_type"] : null;
	$fr_id = !empty($p_step["fr_id"]) ? $p_step["fr_id"] : null;
	$begin_date = !empty($p_step["begin_date"]) ? $p_step["begin_date"] : null;
	if($type == 1 || $type == 2 || $type == 3 || $type == 4) {
		$car_adult = !empty($p_step["adult"]) ? $p_step["adult"] : null;
		$car_child = !empty($p_step["child"]) ? $p_step["child"] : null;
	}else{
		$car_adult = null;
		$car_child = null;
	}
	// 取得高鐵 - 票券類型資料
	$ticket_id = !empty($p_step["ticket_id"]) ? $p_step["ticket_id"] : null;
	$tree_type = !empty($p_step["tree_type"]) ? $p_step["tree_type"] : null;
	$take_date = !empty($p_step["take_date"]) ? $p_step["take_date"] : null;
	$start_area = !empty($p_step["start_area"]) ? $p_step["start_area"] : null;
	$end_area = !empty($p_step["end_area"]) ? $p_step["end_area"] : null;
	if($type == 5) {
		$ticket_adult = !empty($p_step["adult"]) ? $p_step["adult"] : null;
		$ticket_child = !empty($p_step["child"]) ? $p_step["child"] : null;
	}else{
		$ticket_adult = null;
		$ticket_child = null;
	}
	$coupon = $p_step["coupon"];
}else{
	$type = get_val("type");
	$pickup_type = get_val('pickup_type');
	$fr_id = get_val("fr_id");
	$begin_date = get_val("begin_date");
	$car_adult = get_val("car_adult");
	$car_child = get_val("car_child");
	// 取得高鐵 - 票券類型資料
	$ticket_id = get_val("ticket_id");
	$tree_type = get_val("tree_type");
	$take_date = get_val("take_date");
	$start_area = get_val("start_area");
	$end_area = get_val("end_area");
	$ticket_adult = get_val("ticket_adult");
	$ticket_child = get_val("ticket_child");
	$coupon = get_val("coupon");
}
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<?php include __DIR__ . "/../../common/head_new.php"; ?>
	<script src="/web/js/lib/jquery/jquery.js"></script>
	<script src="/web/js/main-min.js"></script>
	<script src="/web/js/lib/autogrow/autogrow.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css">
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" href="/web/css/main2.css">
</head>
<body>
	<header><?php include __DIR__ . "/../../common/header_new.php"; ?></header>
	<?php
	if($header_is_login == 0) {
		alertmsg("需登入才能訂購!", "/transport/");
	}
	?>
	<main class="transport-payStep-container">
		<h1 class="title">付款步驟</h1>

		<!-- mobile mode -->
		<div class="step-m">
			<!-- selected為該步驟進行中, done為已結束 -->
			<!-- 高鐵只有三個步驟 -->
			<div class="circle selected">
				1
			</div>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
			<div class="circle">
				2
			</div>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
			<div class="circle">
				3
			</div>
			<i id="i-step4" class="fa fa-arrow-right" aria-hidden="true"></i>
			<div id="div-step4" class="circle">
				4
			</div>
		</div>

		<!-- 將各htm 載入至當中 -->
		<div class="tile" id="stepFrame"></div>
	</main>
	<footer><? include __DIR__ . "/../../common/footer_new.php"; ?></footer>
	<script type="text/javascript">
		$(function(){
			<!-- 路線類型 1:包車 2:接機 3:拼車 4.送機 5:高鐵 -->
			var url = "?fr_id=<?php echo $fr_id; ?>&pickup_type=<?php echo $pickup_type; ?>";
			url += "&begin_date=<?php echo $begin_date; ?>&car_adult=<?php echo $car_adult; ?>";
			url += "&car_child=<?php echo $car_child; ?>&type=<?= $type ?>";
			switch(<?= $type ?>) {
				case 2:
					$( "#stepFrame" ).load( "pay_step/pickup.php" + url );
				  	break;
				case 3:
					$( "#stepFrame" ).load( "pay_step/tourbus.php" + url );
				  	break;
				case 4:
					$( "#stepFrame" ).load( "pay_step/pickup.php" + url );
				  	break;
				case 5:
					url = "?tree_type=<?php echo $tree_type; ?>&take_date=<?php echo $take_date; ?>&start_area=<?php echo $start_area; ?>";
					url += "&end_area=<?php echo $end_area;?>&ticket_adult=<?php echo $ticket_adult; ?>&ticket_child=<?php echo $ticket_child; ?>&type=<?= $type ?>&coupon=<?= $coupon ?>";
					url += '&ticket_id=<?php echo $ticket_id?>';

					$( "#stepFrame" ).load( "pay_step/hsr.php" + url );
				  	break;
				default:
					$( "#stepFrame" ).load( "pay_step/charter.php" + url );
			}
		});
	</script>
</body>
</html>
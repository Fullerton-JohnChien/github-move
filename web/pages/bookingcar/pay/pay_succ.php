<?php
/**
 * 說明：交通訂購完成頁
 * 作者：Steak
 * 日期：2016年6月8日
 * 備註：
 */
require_once __DIR__ . '/../../../config.php';
header("Content-Type:text/html; charset=utf-8");

$type = get_val("type");
$order_id = get_val("order_id");

//$type = 1;
//$order_id = 54;

//$type = 2;
//$order_id = 59;

//$type = 3;
//$order_id = 58;

// $type = 5;
// $order_id = 14;
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#">
<head>
	<?php include __DIR__ . "/../../common/head_new.php"; ?>
	<script src="/web/js/lib/jquery/jquery.js"></script>
	<script src="/web/js/main-min.js"></script>
	<script src="/web/js/lib/autogrow/autogrow.min.js"></script>
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" href="/web/css/main2.css">
    <title>交通預訂 - Tripitta 旅必達</title>
</head>
<body>
	<header><?php include __DIR__ . "/../../common/header_new.php"; ?></header>
	<div class="transport-paySucc-container">
		<h1 class="title">完成訂購</h1>
		<div id="infoFrame" class="tile"></div>
	</div>
	<footer><? include __DIR__ . "/../../common/footer_new.php"; ?></footer>
	<script type="text/javascript">
		$(function(){
			var url = "?order_id=<?php echo $order_id; ?>";
			switch(<?= $type ?>) {
				case 2:
					$( "#infoFrame" ).load( "pay_succ/pickup.php" + url );
				  	break;
				case 3:
					$( "#infoFrame" ).load( "pay_succ/tourbus.php" + url );
				  	break;
				case 4:
					$( "#infoFrame" ).load( "pay_succ/pickup.php" + url );
				  	break;
				case 5:
					$( "#infoFrame" ).load( "pay_succ/hsr.php" + url );
				  	break;
				default:
					$( "#infoFrame" ).load( "pay_succ/charter.php" + url );
			}
		})
	</script>
</body>
</html>
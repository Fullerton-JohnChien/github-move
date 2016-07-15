<?php
/**
 * 說明：4G卡 - 機場臨櫃 商品列表頁
 * 作者：Steak
 * 日期：2016年3月22日
 * 備註：
 */
include_once('../../../web/config.php');
$db = Dao_loader::__get_checked_db_reader();

// 取得商品列表
$sql = "SELECT * FROM hf_idealcard WHERE i_status  = 1 and i_lang = 'tw' ORDER BY i_display_order DESC";
$idealcard_list = $db -> executeReader($sql);

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<? include "../../../web/pages/common/head.php"; ?>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=1080">
	<title>【Tripitta】 - 中華電信4G預付卡訂購</title>
	<link rel="stylesheet" href="/event/card4g/css/main.css">
</head>
<body>
	<div class="cht-step1-container">
		<header>
			<img src="/event/card4g/img/logo.svg" alt="">
			<h4>中華電信4G預付卡訂購</h4>
		</header>
		<div class="products">
			<div class="pTitle">選擇商品</div>
			<div class="pList">
				<?php foreach ($idealcard_list as $dl) {?>
				<a href="/event/card4g/cht/booking_confirm.php?proId=<?= $dl["i_id"] ?>" class="product">
					<div class="imgWrap">
						<!--  <img src="/event/card4g/img/<?= $dl["i_days"] ?>day.svg"> -->
						<img src="<?php echo get_config_image_server() . '/photos/' . (is_production() ? 'idealcard' : 'idealcard_alpha') . '/prod/'. $dl['i_photo']. '.svg'?>">
					</div>
					<div class="detail">
						<h2><span class="mark"><?= $dl["i_days"] ?>日</span>無限上網</h2>
						<h3>含 TWD <?= $dl["i_call_amount"] ?> 通話金</h3>
						<div class="price">
							<div class="text">售價</div>
							<div class="currency">TWD</div>
							<div class="num"><?= $dl["i_price"] ?> </div>
						</div>
					</div>
				</a>
				<?php } ?>
			</div>
		</div>
	</div>
</body>
</html>
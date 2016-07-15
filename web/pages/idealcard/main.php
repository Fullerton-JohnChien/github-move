<?
/**
 *  說明：Tripitta 4G 網卡預訂 - 商品列表頁
 *  作者：tim.chang <tim.chang@fullerton.com.tw>
 *  日期：2016年03月18日
 *  備註：
 */
require_once __DIR__ . '/../../config.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant" prefix="og: http://ogp.me/ns#" >
<head>
	<meta charset="UTF-8">
	<? include __DIR__ . "/../common/head.php";?>
	<title>4G 網卡預訂 - Tripitta 旅必達</title>
	<link rel="stylesheet" href="/web/css/main.css">
	<link rel="stylesheet" href="/web/css/main2.css">
	
	<script src="../../js/main-min.js"></script>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
</head>
<body>
<?
	$idealcard_service = new idealcard_service();
	$tripitta_web_service = new tripitta_web_service();
	$tripitta_homestay_service = new tripitta_homestay_service();
	
	$status = 1;
	$idealcard_list = $idealcard_service->find_prod_by_status($status);
	//print_r($idealcard_list); exit;
?>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="card4GProducts-container">
		<div class="bannerFrame">
			<div class="banner">
				<img src="/web/img/sec/card4G/4gBanner.png" class="bannerImg">
			</div>
			<div class="dropFrame">
				<i class="fa fa-angle-down"></i>
			</div>
		</div>
		<section class="productsListFrame">
			<h1>4G計日型預付卡</h1>
<?
	$currency_id = $tripitta_web_service->get_display_currency();
	$currency_code = NULL;
	$exchange_rate = 1;

	// 取得匯率
	if (1 == $currency_id) {
    	$currency_code = 'NTD';
    	$exchange_rate = 1;
	} else {
    	$exchange = $tripitta_homestay_service->get_exchange_by_currency_id($currency_id);
    	$currency_code = $exchange['cr_code'];
    	$exchange_rate = $exchange['erd_rate'];
	}
	
	$photo_url = get_config_image_server();
	if (is_production()) {
		$photo_url .= '/photos/idealcard/prod/';
	} else {
		$photo_url .= '/photos/idealcard_alpha/prod/';
	}
    $photo_ext = ".svg";

	foreach($idealcard_list as $idealcard){
		$img_src = $photo_url . $idealcard['i_photo'] . $photo_ext;
?>
			<div class="productsList">
				<figure class="productFrame">
					<img src="<?= $img_src ?>" class="productImg" alt="<?= $idealcard['i_remark'] ?>">
					<figcaption class="productInfoFrame">
						<h2><?= $idealcard['i_name'] ?><span class="mark"><?= $idealcard['i_days'] ?>日</span>無限上網</h2>
						<h3>含數據 / Wi-Fi無限上網<span class="mark"><?= $currency_code ?> 
						<?= number_format($idealcard['i_call_amount'] / $exchange_rate) ?>
						</span>通話金</h3>
						<div class="priceFrame">
							<div class="priceTitle">售價 <?= $currency_code ?></div>
							<div class="price"><?= number_format($idealcard['i_price'] / $exchange_rate) ?></div>
						</div>
						<label class="btnWrap">
							<button id="<?= $idealcard['i_id'] ?>" class="submit">立即預定</button>
						</label>
					</figcaption>
				</figure>
			</div>
<?
	}
?>
		</section>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
<script>

$(function(){
	$('.dropFrame').children().click(function(){
		scrollToConvas('.productsListFrame')
	});

	$("button.submit").click(function(){
		var i_id = $(this).attr("id");
		location.href = "/wifi/" + i_id + "/";
	});
});

</script>
</body>
</html>
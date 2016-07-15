<?
/**
 *  說明：Tripitta 4G 網卡預訂 - 商品介紹頁
 *  作者：tim.chang <tim.chang@fullerton.com.tw>
 *  日期：2016年03月22日
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
	$photo_url = get_config_image_server();
	if (is_production()) {
		$photo_url .= '/photos/idealcard/';
	} else {
		$photo_url .= '/photos/idealcard_alpha/';
	}
    
	$idealcard_service = new idealcard_service();
	$idealcard_setupspot_service = new idealcard_setupspot_service();
	$tripitta_web_service = new tripitta_web_service();
	$tripitta_homestay_service = new tripitta_homestay_service();
	
	$i_id = $_GET['i_id'];
	$idealcard = $idealcard_service->get_prod_by_id($i_id);
	$idealcard_setupspot = $idealcard_setupspot_service->get_valid_setupspot_by_type(1);
	//var_dump($idealcard); exit;
	
	if(count($idealcard) == 0) {
		alertmsg('產品錯誤', '/wifi/');
	}
	

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
?>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="card4GProductIntro-container">
		<section>
			<figure class="card">
				<img src="<?= ($photo_url . "prod/" . $idealcard['i_photo'] . ".svg") ?>" class="cardImg">
				<figcaption>
					<h1><?= $idealcard['i_name'] ?></h1>
					<h2><span class="mark"><?= $idealcard['i_days'] ?>日</span>無限上網</h2>
					<h3>含數據/Wi-Fi無限上網及<span class="mark"><?= $currency_code ?> 
					<?= number_format($idealcard['i_call_amount'] / $exchange_rate) ?></span>通話金</h3>
					<div class="priceFrame">
						<div class="priceText">售價</div>
						<div class="currency"><?= $currency_code ?></div>
						<div class="price"><?= number_format($idealcard['i_price'] / $exchange_rate) ?></div>
						<label class="btnWrap">
							<button class="submit" id="<?= $idealcard['i_id'] ?>">立即預訂</button>
						</label>
					</div>
				</figcaption>
				<div class="title">中華電信預付卡介紹</div>
				<div class="content">
					去台灣旅遊，沒流量，找不到Wifi。無法即時傳照片，分享美食；聽不了音樂，看不了電影。怎麼辦？ 
					別擔心！去台灣竭誠為您提供最贊通訊選擇—中華電信4G計日型預付卡。
					您只要提前預定，到達後即可取卡，馬上可以開通使用，中華電信4G計日型預付卡。 
					無論您是去度小長假，中長假，還是足足一禮拜的黃金大假期，我們都有符合你需求的套餐。
					<?= $idealcard['i_days'] ?>日預付卡 您可以在臺灣享受【數據+Wi-Fi 】無限制上網
					<?= ($idealcard['i_days'] * 24) ?>小時（<?= $idealcard['i_days'] ?>天），沒有流量限制；
					另贈送台幣<?= $idealcard['i_call_amount'] ?>元的通話金，讓您可以上網也可以通話及發送簡訊。
				</div>
			</figure>
		</section>
		<section>
			<div class="step">
				<h2>申辦步驟</h2>
				<ul class="bulletin">
					<li>上網進行線上預約 &amp; 付費購買預付卡。</li>
					<li>前住台灣旅遊。</li>
					<li>本人 親自至機場指定取卡地點，出示護照正本或大陸居民往來台灣通行證及第二證件正本。</li>
					<li>SIM卡裝入手機開機後即可使用 。</li>
				</ul>
				<h2>注意事項</h2>
				<div class="content">
					領取當日的「60天前」開放預約，最遲於領取「3個工作天前 」完成預約，逾期不再開放預約申請或修改。第二證件如下所列，擇一即可：台灣移民署發行之入境證 (入台證)、簽證、觀光證、居留證、國際學生證。若 以上證件皆無，則持申請人本國之有照片的官方證件(社會安全卡、身份證、駕照等)。支付方法：預約時需付款完成(皆新台幣計價) 。
				</div>
			</div>
		</section>
		<section>
			<div class="location">
				<h2>
					機場取卡位置
				</h2>
<?	
	foreach($idealcard_setupspot as $setupspot){
?>
				<div class="subtitle">
					<?= $setupspot['iss_name'] ?> (服務時間: <?= $setupspot['iss_business_hours'] ?>)。
				</div>
				<img src="<?= ($photo_url . "setup_spot/" . $setupspot['iss_photo'] . ".jpg") ?>">
<?
	}
?>
			</div>
		</section>
	</div>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
<script>
</script>
</body>
</html>
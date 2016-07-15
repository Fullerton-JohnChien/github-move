<?php
/**
 * 說明：4G卡 - 機場臨櫃 首頁
 * 作者：Steak
 * 日期：2016年4月20日
 * 備註：
 */
include_once('../../web/config.php');
?>



<!DOCTYPE html>
<html lang="zh-Hant">
<? include "../../web/pages/common/head.php"; ?>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=1080">
	<title>【Tripitta】 - 中華電信4G預付卡訂購</title>
	<link rel="stylesheet" href="/event/card4g/css/main.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">
	$(function(){
		$('#chtgo').click(function(){ location.href = "/event/card4g/cht/card4g.php"; });
		$('#enggo').click(function(){ location.href = "/event/card4g/eng/card4g.php"; });
	});
	</script>
</head>
<body>
	<main class="airportCard4G-container">
		<header>
			<img src="/event/card4g/img/logo.svg" alt="">
		</header>
		<section>
			<div class="imgWrap">
				<img src="/event/card4g/img/cht.svg" alt="">
			</div>
			<h1>4G預付卡</h1>
			<h2>4G Pre-Paid Card</h2>
			<div class="language">請選擇語言</div>
			<div class="languageEng">Please select language</div>
			<div class="btnWrap">
				<button class="submit" id="chtgo">中文</button>
				<button class="submit" id="enggo">English</button>
			</div>
		</section>
	</main>
</body>
</html>
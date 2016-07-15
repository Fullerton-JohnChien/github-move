<?php
/**
 * 說明：
 * 作者：Steak
 * 日期：2016年3月23日
 * 備註：
 */
include_once('../../../web/config.php');
$proId = get_val("proId");
$userEmail = get_val("userEmail");
$lang = get_val("lang");
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<? include "../../../web/pages/common/head.php"; ?>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=1080">
	<title>【Tripitta】 - 中華電信4G預付卡訂購</title>
	<link rel="stylesheet" href="/event/card4g/css/main.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">
	$(function(){
		$('#back').click(function(){ location.href = '/event/card4g/<?= $lang ?>/booking_confirm.php?proId=<?= $proId ?>&email=<?= $userEmail ?>'; });
		//$('#btnRemitDetail').click(function() { $('#exportAct').val('export.allocate.detail'); $('#form2').submit();  });
	});
	</script>
</head>
<body>
	<div class="cht-step4-container">
		<header>
			<img src="/event/card4g/img/logo.svg" alt="">
			<h4>中華電信4G預付卡訂購</h4>
		</header>
		<div class="content">
			<h1>付款認證失敗</h1>
			<div class="fail">很抱歉，你此次的訂購交易失敗</div>
			<div class="cause">
				可能的原因：
				<ol class="causeList">
					<li>您使用的信用卡無法授權，可能需要詢問信用卡行。</li>
					<li>銀行授權時間過長，未接收到授權回覆。</li>
					<li>您使用的VISA金融卡/支付寶餘額不足導致</li>
				</ol>
			</div>
			<button class="submit" id="back">更換卡片重填</button>
		</div>
	</div>
</body>
</html>
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
	<title>【Tripitta】 - 4G Pre-Paid Card</title>
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
			<h4>
				<div class="telecom">Chunghwa Telecom</div>
				<div class="cardName">4G Pre-Paid Card</div>
			</h4>
		</header>
		<div class="content">
			<h1 class="h1Eng">Unsuccessful</h1>
			<div class="failEng">Sorry,Order unsuccessful</div>
			<div class="cause">
				Possible reason:
				<ol class="causeList">
					<li>Credit card Authorization time out.</li>
					<li>Credit card can't be Authorization.</li>
					<li>Debit card account is less than the amount can't be debited.</li>
				</ol>
			</div>
			<button class="submit" id="back">Change card</button>
		</div>
	</div>
</body>
</html>
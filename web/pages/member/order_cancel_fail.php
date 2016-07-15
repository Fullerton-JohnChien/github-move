<?
require_once __DIR__ . '/../../config.php';
error_reporting(E_ALL);
?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
	<? include __DIR__ . "/../common/head_new.php"; ?>>
	<title>tripitta 旅必達 會員中心 - 我的訂單 - 取消訂單失敗</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/member.css">
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="/web/js/jquery.twbsPagination.js" type="text/javascript"></script>
</head>
<body>
	<header><? include __DIR__ . "/../common/header.php"; ?></header>
	<div class="orderCancelFail-container">
		<h1 class="title">取消訂單失敗</h1>
		<div class="tile">
			<? include 'order_function_menu.php'; ?>
			<article>
				<div class="wrapper">
					<section>
						<i class="img-member-cross-big"></i>
						<h1>取消訂單失敗</h1>
						<h2>此訊息的拒絕交易原因僅有發卡銀行端知悉，建議會員可向發卡銀行查詢或請改以其他信用卡訂購。</h2>
						<a href="javascript:history.back(-1);" class="goBack">返回上一頁</a>
					</section>
				</div>
			</article>
		</div>
	</div>
	<footer><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<?php include __DIR__ . '/../common/ga.php';?>
</body>
</html>
<?php
/**
 * 說明：404
 * 作者：bobby-luo <bobby.luo@fullerton.com.tw>
 * 日期：2016年01月07日
 * 備註：
 */
include_once('../../config.php');
$url = "";
switch ($_SERVER['HTTP_HOST']) {
	case "local.tw.tripitta.com" :
		$url = "http://local.tw.tripitta.com";
		break;
	case "alpha.www.tripitta.com" :
		$url = "http://alpha.www.tripitta.com";
		break;
	default :
		$url = "https://www.tripitta.com";
		break;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
	<meta charset="UTF-8">
	<title>Error 404</title>
	<link rel="stylesheet" href="/web/css/main.css?01121536">
	<link rel="stylesheet" href="/web/css/banner.css">
	<? include __DIR__ . "/../common/head.php"; ?>
</head>

<body>
	<header class="header"><? include __DIR__ . "/../common/header.php"; ?></header>
	<a href="<?php echo $url; ?>" class="error404_banner"></a>
	<footer class="footer"><? include __DIR__ . "/../common/footer.php"; ?></footer>
	<script src="/web/js/lib/jquery/jquery.js"></script>
	<script src="/web/js/embed.js"></script>
</body>

</html>
